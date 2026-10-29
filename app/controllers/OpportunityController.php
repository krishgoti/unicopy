<?php
/**
 * UniHelp - Placement & Internship Opportunities Controller
 */

class OpportunityController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        Auth::requireApproved();

        $userId = Auth::id();
        $role = Auth::role();

        $type = $_GET['type'] ?? null;
        $search = $_GET['search'] ?? '';

        // Fetch student profile for eligibility check
        $studentProfile = null;
        $userSkills = [];
        if ($role === 'student' || $role === 'alumni') {
            $stmt = $this->db->prepare("SELECT sp.*, up.department FROM user_profiles up LEFT JOIN student_profiles sp ON up.user_id = sp.user_id WHERE up.user_id = ?");
            $stmt->execute([$userId]);
            $studentProfile = $stmt->fetch();

            $stmt = $this->db->prepare("SELECT s.name FROM user_skills us JOIN skills s ON us.skill_id = s.id WHERE us.user_id = ?");
            $stmt->execute([$userId]);
            $userSkills = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $sql = "SELECT o.*, up.full_name as poster_name, 
            (SELECT status FROM applications WHERE user_id = ? AND opportunity_id = o.id) as application_status
            FROM opportunities o 
            JOIN user_profiles up ON o.posted_by = up.user_id 
            WHERE o.status = 'active'";
        $params = [$userId];

        if ($type && in_array($type, ['placement', 'internship'])) {
            $sql .= " AND o.type = ?";
            $params[] = $type;
        }
        if ($search) {
            $sql .= " AND (o.company_name LIKE ? OR o.role LIKE ? OR o.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $opportunities = $stmt->fetchAll();

        // Process eligibility and skill match for each opportunity
        foreach ($opportunities as &$opp) {
            $opp['is_eligible'] = true;
            $opp['reasons'] = [];
            $opp['match_score'] = 0;

            if ($role === 'student' && $studentProfile) {
                // 1. CGPA Check
                if ($opp['min_cgpa'] > 0 && ($studentProfile['cgpa'] ?? 0) < $opp['min_cgpa']) {
                    $opp['is_eligible'] = false;
                    $opp['reasons'][] = "Min CGPA required: " . $opp['min_cgpa'];
                }
                // 2. 10th Check
                if ($opp['min_10th'] > 0 && ($studentProfile['tenth_percentage'] ?? 0) < $opp['min_10th']) {
                    $opp['is_eligible'] = false;
                    $opp['reasons'][] = "Min 10th% required: " . $opp['min_10th'];
                }
                // 3. 12th Check
                if ($opp['min_12th'] > 0 && ($studentProfile['twelfth_percentage'] ?? 0) < $opp['min_12th']) {
                    $opp['is_eligible'] = false;
                    $opp['reasons'][] = "Min 12th% required: " . $opp['min_12th'];
                }
                // 4. Dept Check
                if (!empty($opp['allowed_depts'])) {
                    $allowedDepts = array_map('trim', explode(',', $opp['allowed_depts']));
                    if (!in_array($studentProfile['department'], $allowedDepts)) {
                        $opp['is_eligible'] = false;
                        $opp['reasons'][] = "Restricted to Departments: " . $opp['allowed_depts'];
                    }
                }
            }

            // 5. Skill Matching (Visible for both student and alumni)
            if (($role === 'student' || $role === 'alumni') && !empty($opp['required_skills'])) {
                $reqSkills = array_map('trim', array_map('strtolower', explode(',', $opp['required_skills'])));
                $stdSkills = array_map('strtolower', $userSkills);
                $matches = array_intersect($reqSkills, $stdSkills);
                $opp['match_score'] = count($reqSkills) > 0 ? round((count($matches) / count($reqSkills)) * 100) : 100;
            } else {
                $opp['match_score'] = 100;
            }
        }

        if ($role === 'admin') {
            $stmt = $this->db->prepare("SELECT * FROM opportunities WHERE posted_by = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            $myOpportunities = $stmt->fetchAll();
            $this->view('opportunities/faculty', ['opportunities' => $opportunities, 'myOpportunities' => $myOpportunities]);
        } else {
            $stmt = $this->db->prepare("SELECT a.*, o.company_name, o.role, o.type FROM applications a 
                JOIN opportunities o ON a.opportunity_id = o.id WHERE a.user_id = ? ORDER BY a.applied_at DESC");
            $stmt->execute([$userId]);
            $myApplications = $stmt->fetchAll();

            $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
            $stmt->execute([$userId]);
            $latestNotifications = $stmt->fetchAll();

            $this->view('opportunities/index', [
                'opportunities' => $opportunities, 
                'myApplications' => $myApplications ?? [],
                'student' => $studentProfile,
                'latestNotifications' => $latestNotifications,
                'isMyApplications' => ($type === 'my_applications')
            ]);
        }
    }

    public function post(): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view('opportunities/post');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/opportunities/post');
            return;
        }

        $companyName = trim($_POST['company_name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $type = $_POST['type'] ?? 'placement';
        $package = trim($_POST['package'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $minCgpa = (float)($_POST['min_cgpa'] ?? 0);
        $min10th = (float)($_POST['min_10th'] ?? 0);
        $min12th = (float)($_POST['min_12th'] ?? 0);
        $allowedDepts = trim($_POST['allowed_depts'] ?? '');
        $requiredSkills = trim($_POST['required_skills'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $deadline = $_POST['apply_deadline'] ?? null;

        if (empty($companyName) || empty($role)) {
            $this->flash('error', 'Company name and role are required.');
            $this->view('opportunities/post', $_POST);
            return;
        }

        $stmt = $this->db->prepare("INSERT INTO opportunities (posted_by, type, company_name, role, package, description, requirements, min_cgpa, min_10th, min_12th, allowed_depts, required_skills, location, apply_deadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([Auth::id(), $type, $companyName, $role, $package, $description, $requirements, $minCgpa, $min10th, $min12th, $allowedDepts, $requiredSkills, $location, $deadline ?: null]);

        // Notify all eligible students
        $opportunityId = $this->db->lastInsertId();
        $stmt = $this->db->query("SELECT id FROM users WHERE role = 'student' AND status = 'active'");
        $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($students as $sId) {
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'opportunity', ?)");
            $stmt->execute([$sId, "New Job: $companyName", "New $type opportunity for $role role. Check eligibility and apply now!", BASE_URL . '/opportunities']);
        }

        $this->flash('success', 'Opportunity posted successfully.');
        $this->redirect(BASE_URL . '/opportunities');
    }

    public function apply(): void {
        Auth::requireRole(['student', 'alumni']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/opportunities');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/opportunities');
            return;
        }

        $opportunityId = (int)($_POST['opportunity_id'] ?? 0);
        if (!$opportunityId) {
            $this->flash('error', 'Invalid opportunity.');
            $this->redirect(BASE_URL . '/opportunities');
            return;
        }

        // Resume Upload & Validation
        $resumePath = null;
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['resume'];
            
            // Size check
            if ($file['size'] > MAX_RESUME_SIZE) {
                $this->flash('error', 'Resume size should not exceed ' . (MAX_RESUME_SIZE / 1024 / 1024) . 'MB.');
                $this->redirect(BASE_URL . '/opportunities');
                return;
            }

            // Extension check
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_RESUME_EXTENSIONS)) {
                $this->flash('error', 'Invalid file format. Allowed formats: ' . strtoupper(implode(', ', ALLOWED_RESUME_EXTENSIONS)));
                $this->redirect(BASE_URL . '/opportunities');
                return;
            }

            // MIME type check
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            if (!in_array($mimeType, ALLOWED_RESUME_TYPES)) {
                $this->flash('error', 'Invalid file type. Please upload a valid resume.');
                $this->redirect(BASE_URL . '/opportunities');
                return;
            }

            $uploadDir = RESUME_PATH . '/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $fileName = 'job_app_' . Auth::id() . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $targetPath)) {
                $resumePath = $fileName;
            }
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO applications (user_id, opportunity_id, resume_path) VALUES (?, ?, ?)");
            $stmt->execute([Auth::id(), $opportunityId, $resumePath]);
            $this->flash('success', 'Application submitted successfully.');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $this->flash('error', 'You have already applied for this opportunity.');
            } else {
                $this->flash('error', 'Failed to submit application.');
            }
        }
        $this->redirect(BASE_URL . '/opportunities');
    }

    public function viewApplications(): void {
        Auth::requireAdmin();
        $opportunityId = (int)($_GET['id'] ?? 0);
        if (!$opportunityId) {
            $this->redirect(BASE_URL . '/opportunities');
            return;
        }

        $stmt = $this->db->prepare("SELECT a.*, up.full_name, u.email, up.phone, up.department FROM applications a 
            JOIN user_profiles up ON a.user_id = up.user_id 
            JOIN users u ON a.user_id = u.id 
            WHERE a.opportunity_id = ? ORDER BY a.applied_at DESC");
        $stmt->execute([$opportunityId]);
        $applications = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT * FROM opportunities WHERE id = ?");
        $stmt->execute([$opportunityId]);
        $opportunity = $stmt->fetch();

        $this->view('opportunities/applications', ['applications' => $applications, 'opportunity' => $opportunity]);
    }

    public function updateStatus(): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/opportunities'));
            return;
        }

        $applicationId = (int)$_POST['application_id'];
        $status = $_POST['status'];

        $stmt = $this->db->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->execute([$status, $applicationId]);

        // Notify student
        $stmt = $this->db->prepare("SELECT a.user_id, o.company_name FROM applications a JOIN opportunities o ON a.opportunity_id = o.id WHERE a.id = ?");
        $stmt->execute([$applicationId]);
        $data = $stmt->fetch();
        
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'application')");
        $stmt->execute([$data['user_id'], "Application Update", "Your application for {$data['company_name']} has been updated to: " . strtoupper($status)]);

        $this->flash('success', 'Status updated.');
        $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
