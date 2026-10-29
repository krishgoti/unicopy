<?php
/**
 * UniHelp - Test Scores Controller
 * Aptitude & coding test score history
 */

class TestController extends Controller {

    public function index(): void {
        Auth::requireStudent();
        Auth::requireApproved();

        $userId = Auth::id();
        $stmt = $this->db->prepare("SELECT * FROM test_scores WHERE user_id = ? ORDER BY test_date DESC");
        $stmt->execute([$userId]);
        $scores = $stmt->fetchAll();

        $this->view('tests/index', ['scores' => $scores]);
    }

    public function add(): void {
        Auth::requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/test-scores');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/test-scores');
            return;
        }

        $type = $_POST['test_type'] ?? '';
        $score = (float)($_POST['score'] ?? 0);
        $maxScore = (float)($_POST['max_score'] ?? 100);
        $testDate = $_POST['test_date'] ?? date('Y-m-d');
        $remarks = trim($_POST['remarks'] ?? '');

        if (!in_array($type, ['aptitude', 'coding', 'technical', 'mock'])) {
            $this->flash('error', 'Invalid test type.');
            $this->redirect(BASE_URL . '/test-scores');
            return;
        }

        $stmt = $this->db->prepare("INSERT INTO test_scores (user_id, test_type, score, max_score, test_date, remarks) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([Auth::id(), $type, $score, $maxScore, $testDate, $remarks]);
        $this->flash('success', 'Test score added successfully.');
        $this->redirect(BASE_URL . '/test-scores');
    }
}
