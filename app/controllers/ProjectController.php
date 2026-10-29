<?php
/**
 * UniHelp - Student Projects Controller
 * Platform for students to share their projects
 */

class ProjectController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        Auth::requireApproved();

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS student_projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                project_name VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                skills VARCHAR(255) NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                github_link VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $stmt = $this->db->query("
            SELECT sp.*, up.full_name as student_name 
            FROM student_projects sp
            JOIN user_profiles up ON sp.user_id = up.user_id
            ORDER BY sp.created_at DESC
        ");
        $projects = $stmt->fetchAll();

        $this->view('projects/index', ['projects' => $projects]);
    }

    public function add(): void {
        Auth::requireLogin();
        Auth::requireApproved();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/projects');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/projects');
            return;
        }

        $userId = Auth::id();
        $projectName = trim($_POST['project_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $skills = trim($_POST['skills'] ?? '');
        $githubLink = trim($_POST['github_link'] ?? '');

        if (empty($projectName) || empty($description) || empty($skills) || empty($_FILES['project_image']['name'])) {
            $this->flash('error', "Project name, description, skills, and image are required.");
            $this->redirect(BASE_URL . '/projects');
            return;
        }

        // Handle image upload
        $imagePath = '';
        if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_PATH . '/projects/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            // Size check
            if ($_FILES['project_image']['size'] > MAX_IMAGE_SIZE) {
                $this->flash('error', "Image size should not exceed " . (MAX_IMAGE_SIZE / 1024 / 1024) . "MB.");
                $this->redirect(BASE_URL . '/projects');
                return;
            }

            $fileInfo = pathinfo($_FILES['project_image']['name']);
            $extension = strtolower($fileInfo['extension']);
            
            if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
                $this->flash('error', "Invalid image format. Allowed formats: " . strtoupper(implode(', ', ALLOWED_IMAGE_EXTENSIONS)));
                $this->redirect(BASE_URL . '/projects');
                return;
            }

            // MIME type check
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($_FILES['project_image']['tmp_name']);
            if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
                $this->flash('error', "Invalid file type. Please upload a valid image.");
                $this->redirect(BASE_URL . '/projects');
                return;
            }

            $fileName = md5(uniqid()) . '.' . $extension;
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['project_image']['tmp_name'], $targetFile)) {
                $imagePath = 'public/uploads/projects/' . $fileName;
            } else {
                $this->flash('error', "Failed to upload image.");
                $this->redirect(BASE_URL . '/projects');
                return;
            }
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO student_projects (user_id, project_name, description, skills, image_path, github_link)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $projectName, $description, $skills, $imagePath, $githubLink]);
            $this->flash('success', "Project added successfully!");
        } catch (PDOException $e) {
            $this->flash('error', "Failed to save project. Please try again.");
        }

        $this->redirect(BASE_URL . '/projects');
        return;
    }

    public function delete(): void {
        Auth::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/projects');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/projects');
            return;
        }

        $projectId = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);
        if (!$projectId) {
            $this->flash('error', "Invalid project ID.");
            $this->redirect(BASE_URL . '/projects');
            return;
        }

        $userId = Auth::id();
        $isAdmin = Auth::user()['role'] === 'admin';

        try {
            if ($isAdmin) {
                $stmt = $this->db->prepare("SELECT image_path FROM student_projects WHERE id = ?");
                $stmt->execute([$projectId]);
            } else {
                $stmt = $this->db->prepare("SELECT image_path FROM student_projects WHERE id = ? AND user_id = ?");
                $stmt->execute([$projectId, $userId]);
            }
            
            $project = $stmt->fetch();

            if ($project) {
                // Delete image file if it exists
                $imageFile = BASE_PATH . '/' . $project['image_path'];
                if (file_exists($imageFile) && !is_dir($imageFile)) {
                    unlink($imageFile);
                }

                if ($isAdmin) {
                    $delStmt = $this->db->prepare("DELETE FROM student_projects WHERE id = ?");
                    $delStmt->execute([$projectId]);
                } else {
                    $delStmt = $this->db->prepare("DELETE FROM student_projects WHERE id = ? AND user_id = ?");
                    $delStmt->execute([$projectId, $userId]);
                }

                $this->flash('success', "Project deleted successfully.");
            } else {
                $this->flash('error', "Project not found or you don't have permission to delete it.");
            }
        } catch (PDOException $e) {
            $this->flash('error', "Failed to delete project.");
        }

        $this->redirect(BASE_URL . '/projects');
        return;
    }
}
