<?php
/**
 * UniHelp - Skill-based Mock Tests Controller
 * Faculty creates 10-question skill-linked mock tests (MCQ).
 * Students attempt, auto-evaluated (+10 per correct), history tracked, skill points updated.
 */
class MockTestController extends Controller {

    private function handleMissingTables(PDOException $e): void {
        // 42S02 = base table or view not found
        if (($e->getCode() ?? '') === '42S02') {
            $this->flash('error', 'Mock Tests tables are missing in your database. Please import the latest database schema (database/unihelp.sql).');
            $this->redirect(BASE_URL . '/dashboard');
            return;
        }
        throw $e;
    }

    private function requireFacultyApproved(): void {
        Auth::requireFaculty();
        Auth::requireApproved();
    }

    private function requireStudentApproved(): void {
        Auth::requireStudent();
        Auth::requireApproved();
    }

    private function fetchSkills(): array {
        $stmt = $this->db->query("SELECT id, name, category FROM skills ORDER BY category, name");
        return $stmt->fetchAll() ?: [];
    }

    private function fetchTestOr404(int $testId): array {
        $stmt = $this->db->prepare("SELECT mt.*, s.name AS skill_name, s.category AS skill_category,
                                           up.full_name AS faculty_name
                                    FROM mock_tests mt
                                    JOIN skills s ON mt.skill_id = s.id
                                    JOIN users u ON mt.created_by = u.id
                                    JOIN user_profiles up ON u.id = up.user_id
                                    WHERE mt.id = ?");
        $stmt->execute([$testId]);
        $test = $stmt->fetch();
        if (!$test) {
            http_response_code(404);
            $this->view('errors/404');
            exit;
        }
        return $test;
    }

    private function fetchQuestions(int $testId): array {
        $stmt = $this->db->prepare("SELECT * FROM mock_test_questions WHERE mock_test_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$testId]);
        return $stmt->fetchAll() ?: [];
    }

    private function alreadyAttempted(int $testId, int $userId): bool {
        $stmt = $this->db->prepare("SELECT id FROM mock_test_attempts WHERE mock_test_id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$testId, $userId]);
        return (bool)$stmt->fetch();
    }

    public function index(): void {
        Auth::requireLogin();
        Auth::requireApproved();

        if (Auth::role() === 'faculty') {
            $this->redirect(BASE_URL . '/mock-tests/faculty');
            return;
        }

        if (Auth::role() !== 'student') {
            $this->flash('error', 'Only students can access mock tests.');
            $this->redirect(BASE_URL . '/dashboard');
            return;
        }

        $this->redirect(BASE_URL . '/mock-tests/list');
    }

    // ---------------- Faculty ----------------

    public function faculty(): void {
        $this->requireFacultyApproved();

        try {
            $stmt = $this->db->prepare("SELECT mt.*, s.name AS skill_name, s.category AS skill_category,
                                               (SELECT COUNT(*) FROM mock_test_questions q WHERE q.mock_test_id = mt.id) AS question_count
                                        FROM mock_tests mt
                                        JOIN skills s ON mt.skill_id = s.id
                                        WHERE mt.created_by = ?
                                        ORDER BY mt.created_at DESC");
            $stmt->execute([Auth::id()]);
            $tests = $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            $this->handleMissingTables($e);
            return;
        }

        $this->view('mocktests/faculty_index', [
            'tests' => $tests,
        ]);
    }

    public function facultyResults(): void {
        $this->requireFacultyApproved();

        try {
            $stmt = $this->db->prepare("SELECT a.score, a.max_score, a.attempted_at,
                                               mt.id AS test_id, mt.title AS test_title,
                                               s.name AS skill_name,
                                               up.full_name AS student_name, up.department
                                        FROM mock_test_attempts a
                                        JOIN mock_tests mt ON a.mock_test_id = mt.id
                                        JOIN skills s ON mt.skill_id = s.id
                                        JOIN users u ON a.user_id = u.id
                                        JOIN user_profiles up ON u.id = up.user_id
                                        WHERE mt.created_by = ?
                                        ORDER BY a.attempted_at DESC, mt.title ASC, up.full_name ASC");
            $stmt->execute([Auth::id()]);
            $rows = $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            $this->handleMissingTables($e);
            return;
        }

        $this->view('mocktests/faculty_results', [
            'rows' => $rows,
        ]);
    }

    public function create(): void {
        $this->requireFacultyApproved();

        $skills = $this->fetchSkills();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid session token. Please try again.');
                $this->redirect(BASE_URL . '/mock-tests/create');
                return;
            }

            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $skillId = (int)($_POST['skill_id'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $singleAttempt = isset($_POST['single_attempt']) ? 1 : 0;
            $durationMins = (int)($_POST['duration_mins'] ?? 0);

            if ($title === '' || $skillId <= 0) {
                $this->flash('error', 'Title and Skill are required.');
                $this->redirect(BASE_URL . '/mock-tests/create');
                return;
            }

            $questions = $_POST['questions'] ?? [];
            if (!is_array($questions) || count($questions) !== 10) {
                $this->flash('error', 'Exactly 10 questions are required.');
                $this->redirect(BASE_URL . '/mock-tests/create');
                return;
            }

            $normalized = [];
            foreach ($questions as $i => $q) {
                $qText = trim((string)($q['text'] ?? ''));
                $a = trim((string)($q['a'] ?? ''));
                $b = trim((string)($q['b'] ?? ''));
                $c = trim((string)($q['c'] ?? ''));
                $d = trim((string)($q['d'] ?? ''));
                $correct = strtoupper(trim((string)($q['correct'] ?? 'A')));

                if ($qText === '' || $a === '' || $b === '' || $c === '' || $d === '') {
                    $this->flash('error', 'All questions and all 4 options are required.');
                    $this->redirect(BASE_URL . '/mock-tests/create');
                    return;
                }
                if (!in_array($correct, ['A', 'B', 'C', 'D'], true)) {
                    $correct = 'A';
                }

                $normalized[] = [
                    'question_text' => $qText,
                    'option_a' => $a,
                    'option_b' => $b,
                    'option_c' => $c,
                    'option_d' => $d,
                    'correct_option' => $correct,
                    'points' => 10,
                    'sort_order' => (int)$i,
                ];
            }

            try {
                $this->db->beginTransaction();

                $stmtTest = $this->db->prepare("INSERT INTO mock_tests (title, description, skill_id, created_by, is_active, single_attempt, duration_mins)
                                                VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtTest->execute([$title, ($description === '' ? null : $description), $skillId, Auth::id(), $isActive, $singleAttempt, $durationMins]);
                $testId = (int)$this->db->lastInsertId();

                $stmtQ = $this->db->prepare("INSERT INTO mock_test_questions
                    (mock_test_id, question_text, option_a, option_b, option_c, option_d, correct_option, points, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                foreach ($normalized as $q) {
                    $stmtQ->execute([
                        $testId,
                        $q['question_text'],
                        $q['option_a'],
                        $q['option_b'],
                        $q['option_c'],
                        $q['option_d'],
                        $q['correct_option'],
                        $q['points'],
                        $q['sort_order'],
                    ]);
                }

                $this->db->commit();
                $this->flash('success', 'Mock test created successfully.');
                $this->redirect(BASE_URL . '/mock-tests/faculty');
                return;
            } catch (PDOException $e) {
                $this->db->rollBack();
                $this->handleMissingTables($e);
                return;
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->flash('error', 'Failed to create mock test. Please try again.');
                $this->redirect(BASE_URL . '/mock-tests/create');
                return;
            }
        }

        $this->view('mocktests/faculty_create', [
            'skills' => $skills,
        ]);
    }

    public function edit($id): void {
        $this->requireFacultyApproved();

        $testId = (int)$id;
        if ($testId <= 0) {
            $this->redirect(BASE_URL . '/mock-tests/faculty');
            return;
        }

        $test = $this->fetchTestOr404($testId);
        if ((int)$test['created_by'] !== Auth::id()) {
            $this->flash('error', 'You do not have permission to edit this test.');
            $this->redirect(BASE_URL . '/mock-tests/faculty');
            return;
        }

        $questions = $this->fetchQuestions($testId);
        $skills = $this->fetchSkills();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid session token. Please try again.');
                $this->redirect(BASE_URL . '/mock-tests/edit/' . $testId);
                return;
            }

            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $skillId = (int)($_POST['skill_id'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $singleAttempt = isset($_POST['single_attempt']) ? 1 : 0;
            $durationMins = (int)($_POST['duration_mins'] ?? 0);

            if ($title === '' || $skillId <= 0) {
                $this->flash('error', 'Title and Skill are required.');
                $this->redirect(BASE_URL . '/mock-tests/edit/' . $testId);
                return;
            }

            $inputQuestions = $_POST['questions'] ?? [];
            if (!is_array($inputQuestions) || count($inputQuestions) !== 10) {
                $this->flash('error', 'Exactly 10 questions are required.');
                $this->redirect(BASE_URL . '/mock-tests/edit/' . $testId);
                return;
            }

            $normalized = [];
            foreach ($inputQuestions as $i => $q) {
                $qText = trim((string)($q['text'] ?? ''));
                $a = trim((string)($q['a'] ?? ''));
                $b = trim((string)($q['b'] ?? ''));
                $c = trim((string)($q['c'] ?? ''));
                $d = trim((string)($q['d'] ?? ''));
                $correct = strtoupper(trim((string)($q['correct'] ?? 'A')));

                if ($qText === '' || $a === '' || $b === '' || $c === '' || $d === '') {
                    $this->flash('error', 'All questions and all 4 options are required.');
                    $this->redirect(BASE_URL . '/mock-tests/edit/' . $testId);
                    return;
                }
                $normalized[] = [
                    'question_text' => $qText,
                    'option_a' => $a,
                    'option_b' => $b,
                    'option_c' => $c,
                    'option_d' => $d,
                    'correct_option' => $correct,
                    'points' => 10,
                    'sort_order' => (int)$i,
                ];
            }

            try {
                $this->db->beginTransaction();

                $stmtUpdate = $this->db->prepare("UPDATE mock_tests SET title = ?, description = ?, skill_id = ?, is_active = ?, single_attempt = ?, duration_mins = ? WHERE id = ?");
                $stmtUpdate->execute([$title, ($description === '' ? null : $description), $skillId, $isActive, $singleAttempt, $durationMins, $testId]);

                // Simple approach: delete existing questions and re-insert
                $this->db->prepare("DELETE FROM mock_test_questions WHERE mock_test_id = ?")->execute([$testId]);

                $stmtQ = $this->db->prepare("INSERT INTO mock_test_questions 
                    (mock_test_id, question_text, option_a, option_b, option_c, option_d, correct_option, points, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                foreach ($normalized as $q) {
                    $stmtQ->execute([
                        $testId,
                        $q['question_text'],
                        $q['option_a'],
                        $q['option_b'],
                        $q['option_c'],
                        $q['option_d'],
                        $q['correct_option'],
                        $q['points'],
                        $q['sort_order'],
                    ]);
                }

                $this->db->commit();
                $this->flash('success', 'Mock test updated successfully.');
                $this->redirect(BASE_URL . '/mock-tests/faculty');
                return;
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->flash('error', 'Failed to update mock test.');
                $this->redirect(BASE_URL . '/mock-tests/edit/' . $testId);
                return;
            }
        }

        $this->view('mocktests/faculty_edit', [
            'test' => $test,
            'questions' => $questions,
            'skills' => $skills,
        ]);
    }

    public function delete($id): void {
        $this->requireFacultyApproved();

        $testId = (int)$id;
        if ($testId <= 0) {
            $this->redirect(BASE_URL . '/mock-tests/faculty');
            return;
        }

        $test = $this->fetchTestOr404($testId);
        if ((int)$test['created_by'] !== Auth::id()) {
            $this->flash('error', 'You do not have permission to delete this test.');
            $this->redirect(BASE_URL . '/mock-tests/faculty');
            return;
        }

        try {
            $this->db->beginTransaction();
            // Delete constraints (cascade might be missing in DB, so let's be safe)
            // But usually mock_tests is the parent. We need to delete attempts, questions, etc if no cascade.
            $this->db->prepare("DELETE FROM mock_test_questions WHERE mock_test_id = ?")->execute([$testId]);
            $this->db->prepare("DELETE FROM mock_tests WHERE id = ?")->execute([$testId]);
            $this->db->commit();

            $this->flash('success', 'Mock test deleted successfully.');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flash('error', 'Failed to delete mock test.');
        }

        $this->redirect(BASE_URL . '/mock-tests/faculty');
    }

    public function start($id): void {
        $this->requireFacultyApproved();

        $testId = (int)$id;
        if ($testId <= 0) {
            $this->redirect(BASE_URL . '/mock-tests/faculty');
            return;
        }

        $test = $this->fetchTestOr404($testId);
        if ((int)$test['created_by'] !== Auth::id()) {
            $this->flash('error', 'You do not have permission to start this test.');
            $this->redirect(BASE_URL . '/mock-tests/faculty');
            return;
        }

        if ($test['started_at']) {
            $this->flash('warning', 'This test has already been started.');
            $this->redirect(BASE_URL . '/mock-tests/faculty');
            return;
        }

        try {
            $stmt = $this->db->prepare("UPDATE mock_tests SET started_at = NOW() WHERE id = ?");
            $stmt->execute([$testId]);
            $this->flash('success', 'Mock test started! Students can now attempt it for the next ' . (int)$test['duration_mins'] . ' minutes.');
        } catch (Exception $e) {
            $this->flash('error', 'Failed to start test.');
        }

        $this->redirect(BASE_URL . '/mock-tests/faculty');
    }

    // ---------------- Student ----------------

    public function list(): void {
        $this->requireStudentApproved();

        $userId = Auth::id();

        try {
            $stmt = $this->db->query("SELECT mt.*, s.name AS skill_name, s.category AS skill_category,
                                             up.full_name AS faculty_name,
                                             (SELECT COUNT(*) FROM mock_test_questions q WHERE q.mock_test_id = mt.id) AS question_count
                                      FROM mock_tests mt
                                      JOIN skills s ON mt.skill_id = s.id
                                      JOIN users u ON mt.created_by = u.id
                                      JOIN user_profiles up ON u.id = up.user_id
                                      WHERE mt.is_active = 1
                                      ORDER BY s.category, s.name, mt.created_at DESC");
            $tests = $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            $this->handleMissingTables($e);
            return;
        }

        // Attempt map
        try {
            $stmtA = $this->db->prepare("SELECT mock_test_id, score, max_score, attempted_at FROM mock_test_attempts WHERE user_id = ?");
            $stmtA->execute([$userId]);
            $attemptRows = $stmtA->fetchAll() ?: [];
        } catch (PDOException $e) {
            $this->handleMissingTables($e);
            return;
        }
        $attempts = [];
        foreach ($attemptRows as $r) {
            $attempts[(int)$r['mock_test_id']] = $r;
        }

        $this->view('mocktests/student_index', [
            'tests' => $tests,
            'attempts' => $attempts,
        ]);
    }

    public function attempt($id): void {
        $this->requireStudentApproved();

        $testId = (int)$id;
        if ($testId <= 0) {
            $this->redirect(BASE_URL . '/mock-tests/list');
            return;
        }

        $test = $this->fetchTestOr404($testId);
        if ((int)$test['is_active'] !== 1) {
            $this->flash('error', 'This mock test is not active.');
            $this->redirect(BASE_URL . '/mock-tests/list');
            return;
        }

        $userId = Auth::id();
        $singleAttempt = (int)($test['single_attempt'] ?? 1) === 1;

        // Optional global override: allow multiple attempts
        if (defined('MOCK_TEST_SINGLE_ATTEMPT') && MOCK_TEST_SINGLE_ATTEMPT === false) {
            $singleAttempt = false;
        }

        if ($singleAttempt && $this->alreadyAttempted($testId, $userId)) {
            $this->flash('error', 'You have already attempted this mock test.');
            $this->redirect(BASE_URL . '/mock-tests/history');
            return;
        }

        $questions = $this->fetchQuestions($testId);
        if (count($questions) !== 10) {
            $this->flash('error', 'This test is not configured properly (needs exactly 10 questions).');
            $this->redirect(BASE_URL . '/mock-tests/list');
            return;
        }

        $this->view('mocktests/attempt', [
            'test' => $test,
            'questions' => $questions,
        ]);
    }

    public function submit($id): void {
        $this->requireStudentApproved();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/mock-tests/list');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/mock-tests/attempt/' . (int)$id);
            return;
        }

        $testId = (int)$id;
        if ($testId <= 0) {
            $this->redirect(BASE_URL . '/mock-tests/list');
            return;
        }

        $test = $this->fetchTestOr404($testId);
        if ((int)$test['is_active'] !== 1) {
            $this->flash('error', 'This mock test is not active.');
            $this->redirect(BASE_URL . '/mock-tests/list');
            return;
        }

        $userId = Auth::id();

        $singleAttempt = (int)($test['single_attempt'] ?? 1) === 1;
        if (defined('MOCK_TEST_SINGLE_ATTEMPT') && MOCK_TEST_SINGLE_ATTEMPT === false) {
            $singleAttempt = false;
        }

        if ($singleAttempt && $this->alreadyAttempted($testId, $userId)) {
            $this->flash('error', 'Duplicate submission blocked: you have already submitted this mock test.');
            $this->redirect(BASE_URL . '/mock-tests/history');
            return;
        }

        $questions = $this->fetchQuestions($testId);
        if (count($questions) !== 10) {
            $this->flash('error', 'This test is not configured properly (needs exactly 10 questions).');
            $this->redirect(BASE_URL . '/mock-tests/list');
            return;
        }

        $score = 0;
        $maxScore = 100;
        $answersToInsert = [];

        foreach ($questions as $q) {
            $qid = (int)$q['id'];
            $selected = $_POST['q_' . $qid] ?? null;
            $selected = is_string($selected) ? strtoupper(trim($selected)) : null;
            if (!in_array($selected, ['A', 'B', 'C', 'D'], true)) {
                $selected = null;
            }

            $isCorrect = 0;
            $pointsAwarded = 0;
            if ($selected !== null && $selected === ($q['correct_option'] ?? null)) {
                $isCorrect = 1;
                $pointsAwarded = 10;
                $score += 10;
            }

            $answersToInsert[] = [
                'question_id' => $qid,
                'selected_option' => $selected,
                'is_correct' => $isCorrect,
                'points_awarded' => $pointsAwarded,
            ];
        }

        try {
            $this->db->beginTransaction();

            // Double-guard inside transaction
            if ($singleAttempt) {
                $stmtDupe = $this->db->prepare("SELECT id FROM mock_test_attempts WHERE mock_test_id = ? AND user_id = ? LIMIT 1");
                $stmtDupe->execute([$testId, $userId]);
                if ($stmtDupe->fetch()) {
                    $this->db->rollBack();
                    $this->flash('error', 'Duplicate submission blocked.');
                    $this->redirect(BASE_URL . '/mock-tests/history');
                    return;
                }
            }

            $stmtAttempt = $this->db->prepare("INSERT INTO mock_test_attempts (mock_test_id, user_id, score, max_score) VALUES (?, ?, ?, ?)");
            $stmtAttempt->execute([$testId, $userId, $score, $maxScore]);
            $attemptId = (int)$this->db->lastInsertId();

            $stmtAns = $this->db->prepare("INSERT INTO mock_test_attempt_answers (attempt_id, question_id, selected_option, is_correct, points_awarded)
                                           VALUES (?, ?, ?, ?, ?)");
            foreach ($answersToInsert as $a) {
                $stmtAns->execute([
                    $attemptId,
                    $a['question_id'],
                    $a['selected_option'],
                    $a['is_correct'],
                    $a['points_awarded'],
                ]);
            }

            // Update skill-based score (cumulative)
            $skillId = (int)$test['skill_id'];
            $stmtSkill = $this->db->prepare("INSERT INTO user_skill_points (user_id, skill_id, total_points, total_attempts, last_score)
                                             VALUES (?, ?, ?, 1, ?)
                                             ON DUPLICATE KEY UPDATE
                                               total_points = total_points + VALUES(total_points),
                                               total_attempts = total_attempts + 1,
                                               last_score = VALUES(last_score)");
            $stmtSkill->execute([$userId, $skillId, $score, $score]);

            // ── Auto-update user_skills (progress + level) based on mock test score ──
            // Score is out of 100. Derive level from score percentage.
            $scorePercent = (int)round(($score / $maxScore) * 100);

            if ($scorePercent >= 80) {
                $autoLevel = 'expert';
            } elseif ($scorePercent >= 60) {
                $autoLevel = 'advanced';
            } elseif ($scorePercent >= 40) {
                $autoLevel = 'intermediate';
            } else {
                $autoLevel = 'beginner';
            }

            // Fetch existing skill record (if any) to apply best-score logic
            $stmtExisting = $this->db->prepare(
                "SELECT progress, level FROM user_skills WHERE user_id = ? AND skill_id = ? LIMIT 1"
            );
            $stmtExisting->execute([$userId, $skillId]);
            $existingSkill = $stmtExisting->fetch();

            $levelRank = ['beginner' => 1, 'intermediate' => 2, 'advanced' => 3, 'expert' => 4];

            $newProgress = $scorePercent;
            $newLevel    = $autoLevel;

            if ($existingSkill) {
                // Keep whichever progress is higher
                if ((int)$existingSkill['progress'] > $newProgress) {
                    $newProgress = (int)$existingSkill['progress'];
                }
                // Keep whichever level is higher
                $existingRank = $levelRank[$existingSkill['level']] ?? 1;
                $newRank      = $levelRank[$newLevel] ?? 1;
                if ($existingRank > $newRank) {
                    $newLevel = $existingSkill['level'];
                }
            }

            $stmtUserSkill = $this->db->prepare(
                "INSERT INTO user_skills (user_id, skill_id, level, progress)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   level    = VALUES(level),
                   progress = VALUES(progress)"
            );
            $stmtUserSkill->execute([$userId, $skillId, $newLevel, $newProgress]);

            $this->db->commit();

            $this->flash('success', 'Test submitted! Score: ' . $score . ' / ' . $maxScore . ' (' . $scorePercent . '%). Your "' . ($test['skill_name'] ?? 'skill') . '" skill has been automatically updated to <strong>' . ucfirst($newLevel) . '</strong> level (' . $newProgress . '%). View your updated skills in Skill Tracker.');
            $this->redirect(BASE_URL . '/mock-tests/history');
            return;
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flash('error', 'Failed to submit test. Please try again.');
            $this->redirect(BASE_URL . '/mock-tests/list');
            return;
        }
    }

    public function history(): void {
        $this->requireStudentApproved();

        $userId = Auth::id();
        try {
            $stmt = $this->db->prepare("SELECT a.*, mt.title AS test_title, s.name AS skill_name, s.category AS skill_category
                                        FROM mock_test_attempts a
                                        JOIN mock_tests mt ON a.mock_test_id = mt.id
                                        JOIN skills s ON mt.skill_id = s.id
                                        WHERE a.user_id = ?
                                        ORDER BY a.attempted_at DESC");
            $stmt->execute([$userId]);
            $attempts = $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            $this->handleMissingTables($e);
            return;
        }

        $this->view('mocktests/history', [
            'attempts' => $attempts,
        ]);
    }
}

