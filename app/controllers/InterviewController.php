<?php
/**
 * UniHelp - Interview Experience Wall Controller
 */

class InterviewController extends Controller {

    private function ensureInterviewUploadDir(): void {
        if (!is_dir(INTERVIEW_UPLOAD_PATH)) {
            @mkdir(INTERVIEW_UPLOAD_PATH, 0775, true);
        }
    }

    private function canPostRole(): bool {
        return in_array(Auth::role(), ['alumni', 'faculty', 'admin', 'student'], true);
    }

    private function normalizeDifficulty(string $d): string {
        $d = strtolower(trim($d));
        return in_array($d, ['easy', 'medium', 'hard'], true) ? $d : 'medium';
    }

    private function normalizeExperienceType(string $t): string {
        $t = strtolower(trim($t));
        return in_array($t, ['placement', 'internship'], true) ? $t : 'placement';
    }


    private function handleMissingSchema(PDOException $e): void {
        if (($e->getCode() ?? '') === '42S22' || ($e->getCode() ?? '') === '42S02') {
            $this->flash('error', 'Interview Experiences schema is outdated/missing. Run: c:\\xampp\\php\\php.exe c:\\xampp\\htdocs\\unicopy\\migrate_interview_experiences_v2.php');
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }
        throw $e;
    }

    public function index(): void {
        Auth::requireLogin();
        Auth::requireApproved();

        $search = $_GET['search'] ?? '';
        $company = $_GET['company'] ?? '';
        $roleFilter = $_GET['role'] ?? '';
        $difficulty = $_GET['difficulty'] ?? '';

        $sql = "SELECT ie.*, u.email, up.full_name, up.company as user_company, up.designation, u.role as user_role 
            FROM interview_experiences ie 
            JOIN users u ON ie.user_id = u.id 
            LEFT JOIN user_profiles up ON ie.user_id = up.user_id 
            WHERE 1=1";
        $params = [];


        if ($search) {
            $sql .= " AND (ie.company_name LIKE ? OR ie.role LIKE ? OR ie.description LIKE ? OR ie.questions LIKE ? OR ie.tips LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }
        if ($company) {
            $sql .= " AND ie.company_name LIKE ?";
            $params[] = "%{$company}%";
        }
        if ($roleFilter) {
            $sql .= " AND ie.role LIKE ?";
            $params[] = "%{$roleFilter}%";
        }
        if ($difficulty) {
            $diff = $this->normalizeDifficulty((string)$difficulty);
            $sql .= " AND ie.difficulty = ?";
            $params[] = $diff;
        }

        $sql .= " ORDER BY ie.created_at DESC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $experiences = $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleMissingSchema($e);
            return;
        }

        $canPost = $this->canPostRole();

        $this->view('interview/index', [
            'experiences' => $experiences,
            'canPost' => $canPost,
        ]);
    }

    public function create(): void {
        Auth::requireRole(['alumni', 'faculty', 'admin', 'student']);
        $this->view('interview/create', [
            'canPost' => true
        ]);
    }

    public function post(): void {
        Auth::requireRole(['alumni', 'faculty', 'admin', 'student']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }

        $companyName = trim($_POST['company_name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $experienceType = $this->normalizeExperienceType($_POST['experience_type'] ?? 'placement');
        $questions = $_POST['questions'] ?? [];
        $questionsTextFallback = trim($_POST['questions_text'] ?? '');
        $tips = trim($_POST['tips'] ?? '');
        $difficulty = $this->normalizeDifficulty($_POST['difficulty'] ?? 'medium');

        $contributorName = trim($_POST['contributor_name'] ?? '');
        $contributorEmail = trim($_POST['contributor_email'] ?? '');
        $contributorPhone = trim($_POST['contributor_phone'] ?? '');

        if (empty($companyName) || empty($role)) {
            $this->flash('error', 'Company name and role are required.');
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }

        if ($contributorName === '' || $contributorEmail === '' || !filter_var($contributorEmail, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Contributor name and a valid email are required.');
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }
        if ($contributorPhone === '') {
            $this->flash('error', 'Contributor phone number is required.');
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }

        $questionsList = [];
        if (is_array($questions)) {
            foreach ($questions as $q) {
                $q = trim((string)$q);
                if ($q !== '') $questionsList[] = $q;
            }
        }
        if (empty($questionsList) && $questionsTextFallback !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $questionsTextFallback);
            foreach ($lines as $line) {
                $line = trim((string)$line);
                if ($line !== '') $questionsList[] = $line;
            }
        }

        // Optional image upload
        $imagePath = null;
        if (!empty($_FILES['contributor_image']['name'] ?? '')) {
            $file = $_FILES['contributor_image'];
            if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $this->flash('error', 'Image upload failed. Please try again.');
                $this->redirect(BASE_URL . '/interview-experiences');
                return;
            }
            if (($file['size'] ?? 0) > MAX_INTERVIEW_IMAGE_SIZE) {
                $this->flash('error', 'Image too large. Max size is 2MB.');
                $this->redirect(BASE_URL . '/interview-experiences');
                return;
            }

            $tmp = $file['tmp_name'] ?? '';
            $mime = $tmp ? @mime_content_type($tmp) : '';
            if (!in_array($mime, ALLOWED_INTERVIEW_IMAGE_TYPES, true)) {
                $this->flash('error', 'Invalid image type. Only JPG/PNG allowed.');
                $this->redirect(BASE_URL . '/interview-experiences');
                return;
            }

            $ext = $mime === 'image/png' ? 'png' : 'jpg';
            $this->ensureInterviewUploadDir();
            $safe = 'interview_' . Auth::id() . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destAbs = rtrim(INTERVIEW_UPLOAD_PATH, '/\\') . DIRECTORY_SEPARATOR . $safe;
            if (!@move_uploaded_file($tmp, $destAbs)) {
                $this->flash('error', 'Failed to save uploaded image.');
                $this->redirect(BASE_URL . '/interview-experiences');
                return;
            }
            $imagePath = 'public/uploads/interviews/' . $safe;
        }

        $status = 'approved';

        try {
            $stmt = $this->db->prepare("INSERT INTO interview_experiences
                (user_id, company_name, role, description, experience_type, questions, questions_json, tips, difficulty, outcome, status, contributor_name, contributor_email, contributor_phone, contributor_image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'selected', ?, ?, ?, ?, ?)");
            $stmt->execute([
                Auth::id(),
                $companyName,
                $role,
                ($description === '' ? null : $description),
                $experienceType,
                $questionsTextFallback !== '' ? $questionsTextFallback : (empty($questionsList) ? null : implode("\n", $questionsList)),
                empty($questionsList) ? null : json_encode($questionsList, JSON_UNESCAPED_UNICODE),
                ($tips === '' ? null : $tips),
                $difficulty,
                $status,
                $contributorName,
                $contributorEmail,
                $contributorPhone,
                $imagePath,
            ]);
        } catch (PDOException $e) {
            $this->handleMissingSchema($e);
            return;
        }

        $this->flash('success', 'Interview experience shared successfully.');
        $this->redirect(BASE_URL . '/interview-experiences');
    }

    public function show($id): void {
        Auth::requireLogin();
        Auth::requireApproved();

        $id = (int)$id;
        if ($id <= 0) {
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }

        try {
            $stmt = $this->db->prepare("SELECT ie.*, u.email, up.full_name, up.company as user_company, up.designation, u.role as user_role
                                        FROM interview_experiences ie
                                        JOIN users u ON ie.user_id = u.id
                                        LEFT JOIN user_profiles up ON ie.user_id = up.user_id
                                        WHERE ie.id = ?
                                        LIMIT 1");
            $stmt->execute([$id]);
            $exp = $stmt->fetch();
        } catch (PDOException $e) {
            $this->handleMissingSchema($e);
            return;
        }

        if (!$exp) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }


        $questionsList = [];
        if (!empty($exp['questions_json'])) {
            $decoded = json_decode((string)$exp['questions_json'], true);
            if (is_array($decoded)) $questionsList = $decoded;
        } elseif (!empty($exp['questions'])) {
            $lines = preg_split('/\r\n|\r|\n/', (string)$exp['questions']);
            foreach ($lines as $line) {
                $line = trim((string)$line);
                if ($line !== '') $questionsList[] = $line;
            }
        }

        $this->view('interview/show', [
            'exp' => $exp,
            'questionsList' => $questionsList,
        ]);
    }

    public function approve($id): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }

        $id = (int)$id;
        if ($id <= 0) {
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }

        try {
            $stmt = $this->db->prepare("UPDATE interview_experiences SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
            $stmt->execute([Auth::id(), $id]);
        } catch (PDOException $e) {
            $this->handleMissingSchema($e);
            return;
        }

        $this->flash('success', 'Submission approved.');
        $this->redirect(BASE_URL . '/interview-experiences');
    }

    public function reject($id): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }

        $id = (int)$id;
        if ($id <= 0) {
            $this->redirect(BASE_URL . '/interview-experiences');
            return;
        }

        try {
            $stmt = $this->db->prepare("UPDATE interview_experiences SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
            $stmt->execute([Auth::id(), $id]);
        } catch (PDOException $e) {
            $this->handleMissingSchema($e);
            return;
        }

        $this->flash('success', 'Submission rejected.');
        $this->redirect(BASE_URL . '/interview-experiences');
    }
}
