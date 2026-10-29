<?php
/**
 * UniHelp - Contest Controller
 * Handles Weekly Contests Creation, Participation, and Leaderboard
 */

class ContestController extends Controller {

    private function isoWeekKeyFromDateTime(string $dt): ?string {
        $ts = strtotime($dt);
        if (!$ts) return null;
        $isoYear = (int)date('o', $ts);
        $isoWeek = (int)date('W', $ts);
        return sprintf('%04d-W%02d', $isoYear, $isoWeek);
    }

    private function normalizeAnswer(string $s): string {
        $s = trim($s);
        if ($s === '') return '';
        $s = preg_replace('/\r\n?/', "\n", $s);
        $s = preg_replace('/[ \t]+/', ' ', $s);
        $s = preg_replace('/\n{3,}/', "\n\n", $s);
        return strtolower(trim($s));
    }

    private function textAnswerMatches(string $student, ?string $correctText): bool {
        if ($correctText === null) return false;
        $studentNorm = $this->normalizeAnswer($student);
        if ($studentNorm === '') return false;

        // Multiple accepted answers supported: one per non-empty line
        $lines = preg_split('/\r\n|\r|\n/', (string)$correctText);
        $accepted = [];
        foreach ($lines as $line) {
            $line = $this->normalizeAnswer($line);
            if ($line !== '') $accepted[] = $line;
        }
        if (empty($accepted)) {
            $correctNorm = $this->normalizeAnswer((string)$correctText);
            return $correctNorm !== '' && $studentNorm === $correctNorm;
        }
        return in_array($studentNorm, $accepted, true);
    }

    public function index(): void {
        if (!Auth::check()) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        // Fetch all active or upcoming contests
        $stmt = $this->db->query("SELECT c.*, u.id as faculty_id, up.full_name as faculty_name,
                                  (SELECT COUNT(*) FROM contest_questions cq WHERE cq.contest_id = c.id) as question_count
                                  FROM contests c
                                  JOIN users u ON c.created_by = u.id
                                  JOIN user_profiles up ON u.id = up.user_id
                                  ORDER BY c.end_time DESC");
        $contests = $stmt->fetchAll();

        // If student, check which contests they have attempted
        $attempts = [];
        if (Auth::role() === 'student') {
            $stmt = $this->db->prepare("SELECT contest_id, score, total_marks FROM contest_attempts WHERE student_id = ?");
            $stmt->execute([Auth::user()['id']]);
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $attempts[$row['contest_id']] = $row;
            }
        }

        $this->view('contests/index', [
            'contests' => $contests,
            'attempts' => $attempts
        ]);
    }

    public function create(): void {
        if (!Auth::check() || Auth::role() !== 'faculty') {
            $this->flash('error', 'Only faculty can create contests.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid session token. Please try again.');
                $this->redirect(BASE_URL . '/contests/create');
                return;
            }

            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';

            if (empty($title) || empty($startTime) || empty($endTime)) {
                $this->flash('error', 'Title, Start Time, and End Time are required.');
                $this->redirect(BASE_URL . '/contests/create');
                return;
            }

            $weekKey = $this->isoWeekKeyFromDateTime($startTime);
            if (!$weekKey) {
                $this->flash('error', 'Invalid start time.');
                $this->redirect(BASE_URL . '/contests/create');
                return;
            }

            // Enforce 1 contest per week (based on start date)
            $stmtDup = $this->db->prepare("SELECT id FROM contests WHERE week_key = ? LIMIT 1");
            $stmtDup->execute([$weekKey]);
            if ($stmtDup->fetch()) {
                $this->flash('error', "A contest for week $weekKey already exists. You can only create one contest per week.");
                $this->redirect(BASE_URL . '/contests');
                return;
            }

            // Save contest
            $stmt = $this->db->prepare("INSERT INTO contests (title, description, created_by, start_time, end_time, week_key) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, Auth::user()['id'], $startTime, $endTime, $weekKey]);
            $contestId = $this->db->lastInsertId();

            // Save questions
            if (!empty($_POST['questions']) && is_array($_POST['questions'])) {
                foreach ($_POST['questions'] as $q) {
                    $qText = trim($q['text'] ?? '');
                    if (empty($qText)) continue;

                    $qType = $q['type'] ?? 'mcq';
                    if (!in_array($qType, ['mcq', 'short', 'coding'], true)) {
                        $qType = 'mcq';
                    }

                    $marks = (int)($q['marks'] ?? 1);
                    if ($marks < 1) $marks = 1;

                    $optA = null;
                    $optB = null;
                    $optC = null;
                    $optD = null;
                    $correctOption = null;
                    $correctText = null;

                    if ($qType === 'mcq') {
                        $optA = trim($q['option_a'] ?? '');
                        $optB = trim($q['option_b'] ?? '');
                        $optC = trim($q['option_c'] ?? '');
                        $optD = trim($q['option_d'] ?? '');
                        $correctOption = $q['correct_option'] ?? 'A';
                        if (!in_array($correctOption, ['A', 'B', 'C', 'D'], true)) $correctOption = 'A';

                        if ($optA === '' || $optB === '' || $optC === '' || $optD === '') {
                            continue;
                        }
                    } else {
                        $correctText = trim($q['correct_text'] ?? '');
                        if ($correctText === '') {
                            continue;
                        }
                    }

                    $stmtQ = $this->db->prepare("INSERT INTO contest_questions (contest_id, question_text, question_type, option_a, option_b, option_c, option_d, correct_option, correct_text, marks)
                                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtQ->execute([$contestId, $qText, $qType, $optA, $optB, $optC, $optD, $correctOption, $correctText, $marks]);
                }
            }

            $this->flash('success', 'Contest created successfully.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $defaultStart = $weekStart . 'T00:00';
        $defaultEnd = $weekEnd . 'T23:59';
        $weekKey = sprintf('%04d-W%02d', (int)date('o'), (int)date('W'));

        $this->view('contests/create', [
            'defaultStart' => $defaultStart,
            'defaultEnd' => $defaultEnd,
            'weekKey' => $weekKey,
        ]);
    }

    public function show($id): void {
        if (!Auth::check()) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM contests WHERE id = ?");
        $stmt->execute([$id]);
        $contest = $stmt->fetch();

        if (!$contest) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        $stmtQ = $this->db->prepare("SELECT * FROM contest_questions WHERE contest_id = ?");
        $stmtQ->execute([$id]);
        $questions = $stmtQ->fetchAll();

        $attempt = null;
        if (Auth::role() === 'student') {
            $stmtA = $this->db->prepare("SELECT * FROM contest_attempts WHERE contest_id = ? AND student_id = ?");
            $stmtA->execute([$id, Auth::user()['id']]);
            $attempt = $stmtA->fetch();
        }

        $this->view('contests/show', [
            'contest' => $contest,
            'questions' => $questions,
            'attempt' => $attempt
        ]);
    }

    public function attempt($id): void {
        if (!Auth::check() || Auth::role() !== 'student') {
            $this->flash('error', 'Only students can attempt contests.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM contests WHERE id = ?");
        $stmt->execute([$id]);
        $contest = $stmt->fetch();

        if (!$contest) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        $now = date('Y-m-d H:i:s');
        if ($now < $contest['start_time'] || $now > $contest['end_time']) {
            $this->flash('error', 'This contest is not currently active.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        $stmtA = $this->db->prepare("SELECT id FROM contest_attempts WHERE contest_id = ? AND student_id = ?");
        $stmtA->execute([$id, Auth::user()['id']]);
        if ($stmtA->fetch()) {
            $this->flash('error', 'You have already attempted this contest.');
            $this->redirect(BASE_URL . '/contests/show/' . $id);
            return;
        }

        $stmtQ = $this->db->prepare("SELECT * FROM contest_questions WHERE contest_id = ?");
        $stmtQ->execute([$id]);
        $questions = $stmtQ->fetchAll();

        $this->view('contests/attempt', [
            'contest' => $contest,
            'questions' => $questions
        ]);
    }

    public function submit($id): void {
        if (!Auth::check() || Auth::role() !== 'student' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/contests/attempt/' . $id);
            return;
        }

        $stmtC = $this->db->prepare("SELECT * FROM contests WHERE id = ?");
        $stmtC->execute([$id]);
        $contest = $stmtC->fetch();
        if (!$contest) {
            $this->flash('error', 'Contest not found.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }
        $now = date('Y-m-d H:i:s');
        if ($now < $contest['start_time'] || $now > $contest['end_time']) {
            $this->flash('error', 'This contest is not currently active.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        $stmtA = $this->db->prepare("SELECT id FROM contest_attempts WHERE contest_id = ? AND student_id = ?");
        $stmtA->execute([$id, Auth::user()['id']]);
        if ($stmtA->fetch()) {
            $this->flash('error', 'You have already submitted this contest.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        $stmtQ = $this->db->prepare("SELECT * FROM contest_questions WHERE contest_id = ?");
        $stmtQ->execute([$id]);
        $questions = $stmtQ->fetchAll();

        $score = 0;
        $totalMarks = 0;
        $answersToInsert = [];

        foreach ($questions as $q) {
            $marks = (int)($q['marks'] ?? 0);
            $totalMarks += $marks;

            $qType = $q['question_type'] ?? 'mcq';
            $selectedOpt = null;
            $textAnswer = null;
            $isCorrect = 0;
            $marksAwarded = 0;

            $raw = $_POST['q_' . $q['id']] ?? null;

            if ($qType === 'mcq') {
                $selectedOpt = is_string($raw) ? strtoupper(trim($raw)) : null;
                if (!in_array($selectedOpt, ['A', 'B', 'C', 'D'], true)) {
                    $selectedOpt = null;
                }
                if ($selectedOpt !== null && $selectedOpt === ($q['correct_option'] ?? null)) {
                    $isCorrect = 1;
                    $marksAwarded = $marks;
                    $score += $marks;
                }
            } else {
                $textAnswer = is_string($raw) ? trim($raw) : '';
                if ($this->textAnswerMatches($textAnswer, $q['correct_text'] ?? null)) {
                    $isCorrect = 1;
                    $marksAwarded = $marks;
                    $score += $marks;
                }
                if ($textAnswer === '') {
                    $textAnswer = null;
                }
            }

            $answersToInsert[] = [
                'question_id' => $q['id'],
                'selected_option' => $selectedOpt,
                'text_answer' => $textAnswer,
                'is_correct' => $isCorrect,
                'marks_awarded' => $marksAwarded,
            ];
        }

        try {
            $this->db->beginTransaction();

            $stmtAttempt = $this->db->prepare("INSERT INTO contest_attempts (contest_id, student_id, score, total_marks) VALUES (?, ?, ?, ?)");
            $stmtAttempt->execute([$id, Auth::user()['id'], $score, $totalMarks]);
            $attemptId = $this->db->lastInsertId();

            $stmtAns = $this->db->prepare("INSERT INTO contest_answers (attempt_id, question_id, selected_option, text_answer, is_correct, marks_awarded)
                                           VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($answersToInsert as $ans) {
                $stmtAns->execute([
                    $attemptId,
                    $ans['question_id'],
                    $ans['selected_option'],
                    $ans['text_answer'],
                    $ans['is_correct'],
                    $ans['marks_awarded'],
                ]);
            }

            $this->db->commit();
            $this->flash('success', 'Contest submitted successfully! You scored ' . $score . ' out of ' . $totalMarks);
            $this->redirect(BASE_URL . '/contests/leaderboard/' . $id);
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flash('error', 'Failed to submit contest. Please try again.');
            $this->redirect(BASE_URL . '/contests');
        }
    }

    public function leaderboard($id = null): void {
        if (!Auth::check()) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        // Output leaderboard for a specific contest
        if (!$id) {
            // Get latest contest
            $stmt = $this->db->query("SELECT id, title FROM contests ORDER BY end_time DESC LIMIT 1");
            $latest = $stmt->fetch();
            if ($latest) {
                $this->redirect(BASE_URL . '/contests/leaderboard/' . $latest['id']);
                return;
            } else {
                $this->flash('error', 'No contests found.');
                $this->redirect(BASE_URL . '/contests');
                return;
            }
        }

        $stmt = $this->db->prepare("SELECT * FROM contests WHERE id = ?");
        $stmt->execute([$id]);
        $contest = $stmt->fetch();

        if (!$contest) {
            $this->flash('error', 'Contest not found.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        $stmtAll = $this->db->prepare("SELECT ca.student_id, ca.score, ca.total_marks, ca.completed_at,
                                              up.full_name as student_name, up.department
                                       FROM contest_attempts ca
                                       JOIN users u ON ca.student_id = u.id
                                       JOIN user_profiles up ON u.id = up.user_id
                                       WHERE ca.contest_id = ?
                                       ORDER BY ca.score DESC, ca.completed_at ASC, up.full_name ASC");
        $stmtAll->execute([$id]);
        $allAttempts = $stmtAll->fetchAll();

        // Dense ranks (ties share rank; next distinct score increments by 1)
        $rank = 0;
        $prevScore = null;
        foreach ($allAttempts as $i => $row) {
            $scoreVal = (int)$row['score'];
            if ($prevScore === null || $scoreVal !== $prevScore) {
                $rank++;
                $prevScore = $scoreVal;
            }
            $allAttempts[$i]['rank'] = $rank;
        }

        $leaderboard = array_slice($allAttempts, 0, 10);
        $participantCount = count($allAttempts);

        $userRank = null;
        if (Auth::role() === 'student') {
            foreach ($allAttempts as $row) {
                if ((int)$row['student_id'] === (int)Auth::user()['id']) {
                    $userRank = [
                        'rank' => (int)$row['rank'],
                        'score' => (int)$row['score'],
                        'total_marks' => (int)$row['total_marks'],
                    ];
                    break;
                }
            }
        }

        $this->view('contests/leaderboard', [
            'contest' => $contest,
            'leaderboard' => $leaderboard,
            'allAttempts' => $allAttempts,
            'participantCount' => $participantCount,
            'userRank' => $userRank
        ]);
    }

    public function history(): void {
        if (!Auth::check()) {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("SELECT c.*, up.full_name as faculty_name,
                                   (SELECT COUNT(*) FROM contest_questions cq WHERE cq.contest_id = c.id) as question_count
                                   FROM contests c
                                   JOIN users u ON c.created_by = u.id
                                   JOIN user_profiles up ON u.id = up.user_id
                                   WHERE c.end_time < ?
                                   ORDER BY c.end_time DESC");
        $stmt->execute([$now]);
        $contests = $stmt->fetchAll();

        $this->view('contests/history', [
            'contests' => $contests,
        ]);
    }

    public function delete($id): void {
        if (!Auth::check() || !in_array(Auth::role(), ['faculty', 'admin'])) {
            $this->flash('error', 'Unauthorized access.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        $stmt = $this->db->prepare("SELECT created_by FROM contests WHERE id = ?");
        $stmt->execute([$id]);
        $contest = $stmt->fetch();

        if (!$contest) {
            $this->flash('error', 'Contest not found.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        // Faculty can only delete their own; Admin can delete any
        if (Auth::role() === 'faculty' && (int)$contest['created_by'] !== (int)Auth::user()['id']) {
            $this->flash('error', 'You can only delete your own contests.');
            $this->redirect(BASE_URL . '/contests');
            return;
        }

        $stmtDel = $this->db->prepare("DELETE FROM contests WHERE id = ?");
        if ($stmtDel->execute([$id])) {
            $this->flash('success', 'Contest deleted successfully.');
        } else {
            $this->flash('error', 'Failed to delete contest.');
        }

        $this->redirect(BASE_URL . '/contests');
    }
}
