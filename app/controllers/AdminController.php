<?php
/**
 * UniHelp - Admin Panel Controller
 */

class AdminController extends Controller {

    public function users(): void {
        Auth::requireAdmin();

        $stmt = $this->db->query("SELECT u.*, up.full_name, up.department, up.phone FROM users u 
            LEFT JOIN user_profiles up ON u.id = up.user_id 
            WHERE u.role != 'admin' ORDER BY u.created_at DESC");
        $users = $stmt->fetchAll();

        $this->view('admin/users', ['users' => $users]);
    }

    public function approveAlumni(): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/dashboard/admin');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/dashboard/admin');
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if (!in_array($action, ['approve', 'reject'])) {
            $this->redirect(BASE_URL . '/dashboard/admin');
            return;
        }

        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'alumni'");
        $stmt->execute([$status, $userId]);

        if ($action === 'approve') {
            $stmt = $this->db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Account Approved', 'Your alumni registration has been approved. You can now access all features.', 'success')");
            $stmt->execute([$userId]);
        }

        $this->flash('success', 'Alumni ' . $action . 'd successfully.');
        $this->redirect(BASE_URL . '/dashboard/admin');
    }

    public function roadmaps(): void {
        Auth::requireAdmin();

        $stmt = $this->db->query("SELECT * FROM career_roadmaps ORDER BY order_index");
        $roadmaps = $stmt->fetchAll();

        // Pass simple text versions of JSON fields for editing if needed, 
        // though strictly the view can handle decoding. 
        // We will handle JSON parsing in the save method.
        
        $this->view('admin/roadmaps', ['roadmaps' => $roadmaps]);
    }

    public function saveRoadmap(): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/roadmaps');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/admin/roadmaps');
            return;
        }

        $id = (int)($_POST['roadmap_id'] ?? 0);
        $career_goal = $_POST['career_goal'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $stepsStr = $_POST['steps'] ?? '';
        $skillsStr = $_POST['skills_required'] ?? '';
        $order_index = (int)($_POST['order_index'] ?? 0);

        // Convert comma-separated strings to JSON arrays
        $steps = json_encode(array_map('trim', explode(',', $stepsStr)));
        $skills = json_encode(array_map('trim', explode(',', $skillsStr)));

        if ($id > 0) {
            // Update
            $stmt = $this->db->prepare("UPDATE career_roadmaps SET career_goal=?, title=?, description=?, steps=?, skills_required=?, order_index=? WHERE id=?");
            $stmt->execute([$career_goal, $title, $description, $steps, $skills, $order_index, $id]);
            $this->flash('success', 'Roadmap updated successfully.');
        } else {
            // Create
            $stmt = $this->db->prepare("INSERT INTO career_roadmaps (career_goal, title, description, steps, skills_required, order_index) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$career_goal, $title, $description, $steps, $skills, $order_index]);
            $this->flash('success', 'Roadmap created successfully.');
        }

        $this->redirect(BASE_URL . '/admin/roadmaps');
    }

    public function deleteRoadmap(): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/roadmaps');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/admin/roadmaps');
            return;
        }

        $id = (int)($_POST['roadmap_id'] ?? 0);
        if ($id > 0) {
            $stmt = $this->db->prepare("DELETE FROM career_roadmaps WHERE id = ?");
            $stmt->execute([$id]);
            $this->flash('success', 'Roadmap deleted successfully.');
        }
        
        $this->redirect(BASE_URL . '/admin/roadmaps');
    }

    public function analytics(): void {
        Auth::requireAdmin();

        $stmt = $this->db->query("SELECT role, COUNT(*) as count FROM users WHERE role != 'admin' GROUP BY role");
        $userStats = $stmt->fetchAll();

        // Improved query to handle cases with no user skills
        $stmt = $this->db->query("SELECT s.name, AVG(us.progress) as avg_progress FROM user_skills us 
            JOIN skills s ON us.skill_id = s.id 
            JOIN users u ON us.user_id = u.id 
            WHERE u.role = 'student' GROUP BY s.id ORDER BY avg_progress DESC LIMIT 10");
        $skillStats = $stmt->fetchAll();

        $stmt = $this->db->query("SELECT DATE(applied_at) as date, COUNT(*) as count FROM applications GROUP BY DATE(applied_at) ORDER BY date DESC LIMIT 30");
        $applicationTrends = $stmt->fetchAll();

        $this->view('admin/analytics', [
            'userStats' => $userStats,
            'skillStats' => $skillStats,
            'applicationTrends' => $applicationTrends
        ]);
    }

    public function deleteUser(): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/users');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/admin/users');
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId && $userId != Auth::id()) {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            $stmt->execute([$userId]);
            $this->flash('success', 'User deleted successfully.');
        }
        $this->redirect(BASE_URL . '/admin/users');
    }
    public function settings(): void {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid session token.');
                $this->redirect(BASE_URL . '/admin/settings');
                return;
            }

            $apiKey = trim($_POST['gemini_api_key'] ?? '');
            $model  = trim($_POST['gemini_model']   ?? '');

            if (empty($apiKey) || empty($model)) {
                $this->flash('error', 'Gemini API Key and Model are required.');
                $this->redirect(BASE_URL . '/admin/settings');
                return;
            }

            // --- Save to main .env (Gemini only) ---
            $envPath    = BASE_PATH . '/.env';
            $mainFields = [
                'GEMINI_API_KEY' => $apiKey,
                'GEMINI_MODEL'   => $model,
                'GEMINI_API_URL' => 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent',
            ];

            $successMain = $this->updateEnvFile($envPath, $mainFields);

            // --- Save to RAG/.env ---
            $ragEnvPath = BASE_PATH . '/RAG/.env';
            $ragFields  = [];

            $pineconeKey   = trim($_POST['rag_pinecone_api_key']   ?? '');
            $pineconeEnv   = trim($_POST['rag_pinecone_env']       ?? '');
            $pineconeIndex = trim($_POST['rag_pinecone_index']     ?? '');
            $mistralKey    = trim($_POST['rag_mistral_api_key']    ?? '');
            $mistralMdl    = trim($_POST['rag_mistral_model']      ?? '');
            $openRouterKey = trim($_POST['rag_openrouter_api_key'] ?? '');
            $openRouterMdl = trim($_POST['rag_openrouter_model']   ?? '');
            $tracingV2     = trim($_POST['rag_langchain_tracing']  ?? 'false');
            $langsmithKey  = trim($_POST['rag_langsmith_api_key']  ?? '');
            $langchainEP   = trim($_POST['rag_langchain_endpoint'] ?? '');
            $langchainProj = trim($_POST['rag_langchain_project']  ?? '');
            $jwtSecret     = trim($_POST['rag_jwt_secret_key']     ?? '');

            if (!empty($pineconeKey))   $ragFields['PINECONE_API_KEY']    = $pineconeKey;
            if (!empty($pineconeEnv))   $ragFields['PINECONE_ENV']        = $pineconeEnv;
            if (!empty($pineconeIndex)) $ragFields['PINECONE_INDEX']      = $pineconeIndex;
            if (!empty($mistralKey))    $ragFields['MISTRAL_API_KEY']     = $mistralKey;
            if (!empty($mistralMdl))    $ragFields['MISTRAL_MODEL']       = $mistralMdl;
            if (!empty($openRouterKey)) $ragFields['OPENROUTER_API_KEY']  = $openRouterKey;
            if (!empty($openRouterMdl)) $ragFields['OPENROUTER_MODEL']    = $openRouterMdl;
            $ragFields['LANGCHAIN_TRACING_V2']  = $tracingV2;
            if (!empty($langsmithKey))  $ragFields['LANGSMITH_API_KEY']   = $langsmithKey;
            if (!empty($langchainEP))   $ragFields['LANGCHAIN_ENDPOINT']  = $langchainEP;
            if (!empty($langchainProj)) $ragFields['LANGCHAIN_PROJECT']   = $langchainProj;
            if (!empty($jwtSecret))     $ragFields['JWT_SECRET_KEY']      = $jwtSecret;

            $successRag = empty($ragFields) ? true : $this->updateEnvFile($ragEnvPath, $ragFields);

            if ($successMain && $successRag) {
                $this->flash('success', 'System settings updated successfully.');
            } else {
                $this->flash('error', 'Failed to update one or more config files. Check file permissions.');
            }

            $this->redirect(BASE_URL . '/admin/settings');
            return;
        }

        $this->view('admin/settings');
    }

    /**
     * Get list of all uploaded resumes with user details & sizes.
     */
    public function utilityResumes(): void {
        Auth::requireAdmin();
        
        $stmt = $this->db->query("
            SELECT rr.id, rr.file_name, rr.file_path, rr.created_at, up.full_name, u.email
            FROM resume_reviews rr
            JOIN users u ON rr.user_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            ORDER BY rr.created_at DESC
        ");
        $resumes = $stmt->fetchAll();
        
        $result = [];
        foreach ($resumes as $r) {
            $path = RESUME_PATH . '/' . $r['file_path'];
            $size = 0;
            $exists = false;
            if (!empty($r['file_path']) && file_exists($path)) {
                $size = filesize($path);
                $exists = true;
            }
            
            $result[] = [
                'id' => (int)$r['id'],
                'file_name' => $r['file_name'],
                'file_path' => $r['file_path'],
                'created_at' => $r['created_at'],
                'student_name' => $r['full_name'] ?? $r['email'],
                'size' => $size,
                'exists' => $exists
            ];
        }
        
        $this->json($result);
    }

    /**
     * Delete specific resumes or all resumes at once.
     */
    public function utilityDeleteResumes(): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid request method.'], 405);
        }
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid session token.'], 403);
        }
        
        $all = isset($_POST['all']) && $_POST['all'] === 'true';
        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids) && !empty($ids)) {
            $ids = json_decode($ids, true) ?: [$ids];
        }
        
        if ($all) {
            $stmt = $this->db->query("SELECT id, file_path, user_id FROM resume_reviews");
            $resumes = $stmt->fetchAll();
        } else {
            if (empty($ids)) {
                $this->json(['error' => 'No resumes selected for deletion.'], 400);
            }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("SELECT id, file_path, user_id FROM resume_reviews WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $resumes = $stmt->fetchAll();
        }
        
        $deletedCount = 0;
        foreach ($resumes as $r) {
            if (!empty($r['file_path'])) {
                $path = RESUME_PATH . '/' . $r['file_path'];
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            
            $stmtUpdate = $this->db->prepare("UPDATE student_profiles SET resume_path = NULL WHERE user_id = ?");
            $stmtUpdate->execute([$r['user_id']]);
            
            $stmtDel = $this->db->prepare("DELETE FROM resume_reviews WHERE id = ?");
            $stmtDel->execute([$r['id']]);
            
            $deletedCount++;
        }
        
        $this->json([
            'success' => true,
            'deleted_count' => $deletedCount,
            'message' => "Successfully deleted $deletedCount resume(s)."
        ]);
    }

    /**
     * Get live MySQL table statistics (rows, data size, index size, free space).
     */
    public function utilityDbStats(): void {
        Auth::requireAdmin();
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    TABLE_NAME AS name, 
                    TABLE_ROWS AS `rows`, 
                    DATA_LENGTH AS data_size, 
                    INDEX_LENGTH AS index_size, 
                    DATA_FREE AS unused_space 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = ?
            ");
            $stmt->execute([DB_NAME]);
            $stats = $stmt->fetchAll();
            
            // Convert to ints
            foreach ($stats as &$s) {
                $s['rows'] = (int)$s['rows'];
                $s['data_size'] = (int)$s['data_size'];
                $s['index_size'] = (int)$s['index_size'];
                $s['unused_space'] = (int)$s['unused_space'];
            }
            
            $this->json($stats);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Dynamically run OPTIMIZE TABLE on all database tables.
     */
    public function utilityOptimizeDb(): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid request method.'], 405);
        }
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid session token.'], 403);
        }
        
        try {
            // Get before optimization stats
            $stmt = $this->db->prepare("
                SELECT TABLE_NAME, DATA_FREE 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = ?
            ");
            $stmt->execute([DB_NAME]);
            $beforeStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $tables = array_keys($beforeStats);
            $optimized = [];
            $savedBytes = 0;
            
            foreach ($tables as $table) {
                try {
                    $this->db->query("OPTIMIZE TABLE `$table`")->execute();
                    $freed = (int)($beforeStats[$table] ?? 0);
                    $savedBytes += $freed;
                    $optimized[] = [
                        'table' => $table,
                        'freed' => $freed,
                        'status' => 'Optimized'
                    ];
                } catch (Exception $e) {
                    $optimized[] = [
                        'table' => $table,
                        'freed' => 0,
                        'status' => 'Error: ' . $e->getMessage()
                    ];
                }
            }
            
            $this->json([
                'success' => true,
                'optimized_tables' => $optimized,
                'freed_bytes' => $savedBytes,
                'message' => 'Database optimized and defragmented successfully!'
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle system lockdown maintenance mode.
     */
    public function utilityToggleLockdown(): void {
        Auth::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid request method.'], 405);
        }
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Invalid session token.'], 403);
        }
        
        $lockdownFile = BASE_PATH . '/.lockdown';
        $isLocked = file_exists($lockdownFile);
        
        if ($isLocked) {
            unlink($lockdownFile);
            $newStatus = false;
            $message = "System lockdown disabled. Platform access restored.";
        } else {
            touch($lockdownFile);
            $newStatus = true;
            $message = "System lockdown activated! All non-admin sessions are blocked.";
        }
        
        $this->json([
            'success' => true,
            'lockdown_active' => $newStatus,
            'message' => $message
        ]);
    }

    /**
     * Helper to update values in the .env file.
     */
    private function updateEnvFile(string $path, array $data): bool {
        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return false;
        }

        foreach ($data as $key => $value) {
            $escapedKey = preg_quote($key, '/');
            if (preg_match('/^' . $escapedKey . '=.*/m', $content)) {
                $content = preg_replace('/^' . $escapedKey . '=.*/m', $key . '=' . $value, $content);
            } else {
                $content = rtrim($content) . "\n" . $key . '=' . $value . "\n";
            }
        }

        return file_put_contents($path, $content) !== false;
    }
}
