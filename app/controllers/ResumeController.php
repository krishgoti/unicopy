<?php
/**
 * UniHelp - Resume Controller v2
 * Smart Resume Analysis & Feedback System (AI + Faculty Manual Review)
 */

class ResumeController extends Controller {

    // ─────────────────────────────────────────────
    // Student Dashboard
    // ─────────────────────────────────────────────
    public function index(): void {
        Auth::requireLogin();
        Auth::requireApproved();

        $userId = Auth::id();
        $role   = Auth::role();

        if ($role === 'student') {
            // Fetch student's review history
            $stmt = $this->db->prepare(
                "SELECT rr.*, up.full_name AS faculty_name
                 FROM resume_reviews rr
                 LEFT JOIN user_profiles up ON rr.faculty_id = up.user_id
                 WHERE rr.user_id = ?
                 ORDER BY rr.created_at DESC"
            );
            $stmt->execute([$userId]);
            $reviews = $stmt->fetchAll();

            $this->view('resume/student', ['reviews' => $reviews]);

        } elseif (in_array($role, ['faculty', 'admin'])) {
            $this->facultyQueue();
        } else {
            $this->redirect(BASE_URL . '/dashboard');
        }
    }

    // ─────────────────────────────────────────────
    // Upload Resume (Student)
    // ─────────────────────────────────────────────
    public function upload(): void {
        Auth::requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        // Validate file
        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Please select a valid PDF or DOCX file.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        $file = $_FILES['resume'];

        // Size check
        if ($file['size'] > MAX_RESUME_SIZE) {
            $this->flash('error', 'File size must not exceed 5MB.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        // Extension check
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_RESUME_EXTENSIONS)) {
            $this->flash('error', 'Only PDF and DOCX files are allowed.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        // MIME type check
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, ALLOWED_RESUME_TYPES)) {
            $this->flash('error', 'Invalid file type detected. Only PDF and DOCX are allowed.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        // Review type check
        $reviewType = $_POST['review_type'] ?? '';
        if (!in_array($reviewType, ['ai', 'faculty'])) {
            $this->flash('error', 'Please select a review type: AI or Faculty.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        // Ensure directory exists
        if (!is_dir(RESUME_PATH)) {
            mkdir(RESUME_PATH, 0755, true);
        }

        // Save file
        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        $filename     = 'resume_' . Auth::id() . '_' . time() . '.' . $ext;
        $filepath     = RESUME_PATH . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->flash('error', 'Failed to save the file. Please try again.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        // Insert into resume_reviews
        $stmt = $this->db->prepare(
            "INSERT INTO resume_reviews (user_id, file_name, file_path, file_type, review_type, status)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $status = ($reviewType === 'ai') ? 'processing' : 'pending';
        $stmt->execute([Auth::id(), $file['name'], $filename, $ext, $reviewType, $status]);
        $reviewId = $this->db->lastInsertId();

        // Also update legacy student_profiles (backward compat)
        $this->db->prepare("UPDATE student_profiles SET resume_path = ? WHERE user_id = ?")
                 ->execute([$filename, Auth::id()]);

        // AI Auto Analysis
        if ($reviewType === 'ai') {
            $this->runAiAnalysis($reviewId, $filepath, $ext);
        } else {
            $this->flash('success', 'Resume successfully submitted for faculty review! You will be notified once it is reviewed.');
            $this->redirect(BASE_URL . '/resume');
        }
    }

    // ─────────────────────────────────────────────
    // Run AI Analysis
    // ─────────────────────────────────────────────
    private function runAiAnalysis(int $reviewId, string $filepath, string $ext): void {
        // Extract text (fallback if file send fails)
        $text = ResumeParser::extractText($filepath, $ext);

        // Call Gemini — pass file path for direct PDF analysis
        $feedback = GeminiResumeAnalyzer::analyze($text, $filepath, $ext);

        // Update DB
        $stmt = $this->db->prepare(
            "UPDATE resume_reviews
             SET status = ?, ai_score = ?, ai_feedback = ?, reviewed_at = NOW()
             WHERE id = ?"
        );
        $reviewId = $reviewId;
        $status = isset($feedback['error']) ? 'failed' : 'completed';
        $stmt->execute([
            $status,
            $feedback['overall_score'] ?? 0,
            json_encode($feedback),
            $reviewId
        ]);

        // ✨ Phase 2: Sync extracted skills with user profile
        if ($status === 'completed' && !empty($feedback['skills_found'])) {
            $this->syncAiSkills(Auth::id(), $feedback['skills_found']);
        }

        $this->redirect(BASE_URL . '/resume/ai-result/' . $reviewId);
    }

    /**
     * Phase 2: Sync AI-found skills with the user_skills table
     */
    private function syncAiSkills(int $userId, array $skills): void {
        foreach ($skills as $skillName) {
            $skillName = trim($skillName);
            if (empty($skillName)) continue;

            // 1. Find or Create Skill in master table
            $stmt = $this->db->prepare("SELECT id FROM skills WHERE name LIKE ?");
            $stmt->execute([$skillName]);
            $skillId = $stmt->fetchColumn();

            if (!$skillId) {
                // Create if missing (default to technical)
                $stmt = $this->db->prepare("INSERT INTO skills (name, category) VALUES (?, 'technical')");
                $stmt->execute([$skillName]);
                $skillId = $this->db->lastInsertId();
            }

            // 2. Upsert into user_skills
            $stmt = $this->db->prepare(
                "INSERT INTO user_skills (user_id, skill_id, level, progress, updated_at)
                 VALUES (?, ?, 'intermediate', 100, NOW())
                 ON DUPLICATE KEY UPDATE updated_at = NOW()"
            );
            $stmt->execute([$userId, $skillId]);
        }
    }

    // ─────────────────────────────────────────────
    // AI Feedback Result Page (Student)
    // ─────────────────────────────────────────────
    public function aiResult(int $reviewId = 0): void {
        Auth::requireStudent();

        $stmt = $this->db->prepare(
            "SELECT * FROM resume_reviews WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$reviewId, Auth::id()]);
        $review = $stmt->fetch();

        if (!$review) {
            $this->flash('error', 'Review not found.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        $feedback = is_string($review['ai_feedback'])
            ? json_decode($review['ai_feedback'], true)
            : $review['ai_feedback'];

        $this->view('resume/ai_feedback', ['review' => $review, 'feedback' => $feedback ?? []]);
    }

    // ─────────────────────────────────────────────
    // Resume Review History (Student)
    // ─────────────────────────────────────────────
    public function history(): void {
        Auth::requireStudent();

        $stmt = $this->db->prepare(
            "SELECT rr.*, up.full_name AS faculty_name
             FROM resume_reviews rr
             LEFT JOIN user_profiles up ON rr.faculty_id = up.user_id
             WHERE rr.user_id = ?
             ORDER BY rr.created_at DESC"
        );
        $stmt->execute([Auth::id()]);
        $reviews = $stmt->fetchAll();

        $this->view('resume/history', ['reviews' => $reviews]);
    }

    // ─────────────────────────────────────────────
    // Faculty Queue — Pending Reviews
    // ─────────────────────────────────────────────
    public function facultyQueue(): void {
        Auth::requireRole(['faculty', 'admin']);

        $stmt = $this->db->query(
            "SELECT rr.*, up.full_name, up.department
             FROM resume_reviews rr
             JOIN user_profiles up ON rr.user_id = up.user_id
             WHERE rr.review_type = 'faculty'
             ORDER BY FIELD(rr.status, 'pending', 'processing', 'completed', 'failed'), rr.created_at DESC"
        );
        $reviews = $stmt->fetchAll();

        $this->view('resume/faculty_queue', ['reviews' => $reviews]);
    }

    // ─────────────────────────────────────────────
    // Faculty Review Form
    // ─────────────────────────────────────────────
    public function facultyReview(int $reviewId = 0): void {
        Auth::requireRole(['faculty', 'admin']);

        $stmt = $this->db->prepare(
            "SELECT rr.*, up.full_name, up.department
             FROM resume_reviews rr
             JOIN user_profiles up ON rr.user_id = up.user_id
             WHERE rr.id = ?"
        );
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch();

        if (!$review) {
            $this->flash('error', 'Review not found.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        $this->view('resume/faculty_review', ['review' => $review]);
    }

    // ─────────────────────────────────────────────
    // Submit Faculty Feedback
    // ─────────────────────────────────────────────
    public function submitFeedback(): void {
        Auth::requireRole(['faculty', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        $reviewId  = (int)($_POST['review_id'] ?? 0);
        $feedback  = trim($_POST['faculty_feedback'] ?? '');
        $score     = isset($_POST['faculty_score']) && $_POST['faculty_score'] !== ''
                     ? (int)$_POST['faculty_score'] : null;

        if (empty($feedback)) {
            $this->flash('error', 'Feedback is required.');
            $this->redirect(BASE_URL . '/resume/review/' . $reviewId);
            return;
        }

        // Fetch review to get student ID
        $stmt = $this->db->prepare("SELECT user_id FROM resume_reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch();

        if (!$review) {
            $this->flash('error', 'Review not found.');
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        // Update review
        $stmt = $this->db->prepare(
            "UPDATE resume_reviews
             SET faculty_feedback = ?, faculty_score = ?, faculty_id = ?,
                 status = 'completed', reviewed_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$feedback, $score, Auth::id(), $reviewId]);

        // Notification to student
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, title, message, type, link)
             VALUES (?, 'Resume Review Complete ✅', 'Your resume faculty review is complete! Click here to view the feedback.', 'feedback', ?)"
        );
        $stmt->execute([$review['user_id'], BASE_URL . '/resume/history']);

        $this->flash('success', 'Feedback successfully submitted! The student has been notified.');
        $this->redirect(BASE_URL . '/resume');
    }

    // ─────────────────────────────────────────────
    // Delete a Review Entry (Student)
    // ─────────────────────────────────────────────
    public function delete(): void {
        Auth::requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/resume');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/resume/history');
            return;
        }

        $reviewId = (int)($_POST['review_id'] ?? 0);

        $stmt = $this->db->prepare(
            "SELECT file_path FROM resume_reviews WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$reviewId, Auth::id()]);
        $review = $stmt->fetch();

        if ($review) {
            $filepath = RESUME_PATH . '/' . $review['file_path'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            $this->db->prepare("DELETE FROM resume_reviews WHERE id = ? AND user_id = ?")
                     ->execute([$reviewId, Auth::id()]);
            $this->flash('success', 'Resume review deleted successfully.');
        } else {
            $this->flash('error', 'Review not found.');
        }

        $this->redirect(BASE_URL . '/resume/history');
    }

    // ─────────────────────────────────────────────
    // Legacy: old feedback route (faculty)
    // ─────────────────────────────────────────────
    public function feedback(): void {
        $this->submitFeedback();
    }
}
