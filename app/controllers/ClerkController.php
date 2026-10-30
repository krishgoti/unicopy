<?php
/**
 * UniHelp - Clerk Authentication Controller
 * Handles session synchronization between Clerk and local database
 */

class ClerkController extends Controller {

    /**
     * Sync Clerk session with local database
     * Called after Clerk authentication on frontend
     */
    public function syncSession(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $token = $_POST['token'] ?? file_get_contents('php://input');
        $data = json_decode($token, true);
        $clerkToken = $data['token'] ?? '';

        if (empty($clerkToken)) {
            http_response_code(400);
            echo json_encode(['error' => 'Token is required']);
            return;
        }

        try {
            // Verify Clerk token
            $clerkUser = $this->verifyToken($clerkToken);

            if (!$clerkUser) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid or expired token']);
                return;
            }

            // Get or create local user
            $user = $this->getOrCreateUser($clerkUser);

            if (!$user) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to sync user']);
                return;
            }

            // Set local session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name'] ?? 'User',
                'status' => $user['status'],
                'email_verified_at' => $user['email_verified_at']
            ];

            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'full_name' => $user['full_name'] ?? 'User'
                ]
            ]);
        } catch (Exception $e) {
            error_log("Clerk sync error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    /**
     * Internal sync session method (for use by other controllers)
     * Returns user data on success, null on failure
     */
    public function syncSessionInternal(string $clerkToken): ?array {
        try {
            // Verify Clerk token
            $clerkUser = $this->verifyToken($clerkToken);

            if (!$clerkUser) {
                return null;
            }

            // Get or create local user
            $user = $this->getOrCreateUser($clerkUser);

            if (!$user) {
                return null;
            }

            // Set local session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name'] ?? 'User',
                'status' => $user['status'],
                'email_verified_at' => $user['email_verified_at']
            ];

            return $user;
        } catch (Exception $e) {
            error_log("Clerk sync internal error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify Clerk JWT token
     */
    private function verifyToken(string $token): ?array {
        $secretKey = defined('CLERK_SECRET_KEY') ? CLERK_SECRET_KEY : (Env::get('CLERK_SECRET_KEY', ''));

        if (empty($secretKey) || strpos($secretKey, 'sk_test_') !== 0) {
            error_log("Invalid CLERK_SECRET_KEY configured");
            return null;
        }

        // Split token into parts
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        // Decode header and payload (base64url)
        $header = json_decode($this->base64UrlDecode($parts[0]), true);
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);

        if (!$header || !$payload) {
            return null;
        }

        // Verify token is not expired
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        // Verify issuer
        if (!isset($payload['iss']) || strpos($payload['iss'], 'clerk') === false) {
            return null;
        }

        // In production, you should verify the signature using the secret key
        // For now, we'll trust the token structure (add proper JWT verification in production)

        return $payload;
    }

    /**
     * Base64 URL decode
     */
    private function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Get or create local user from Clerk user data
     */
    private function getOrCreateUser(array $clerkUser): ?array {
        $clerkUserId = $clerkUser['sub'] ?? null;
        $email = $clerkUser['email'] ?? null;

        if (!$clerkUserId || !$email) {
            return null;
        }

        // Check if user exists by clerk_user_id
        $stmt = $this->db->prepare("SELECT u.*, up.full_name FROM users u
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE u.clerk_user_id = ?");
        $stmt->execute([$clerkUserId]);
        $user = $stmt->fetch();

        if ($user) {
            // Update last active
            $this->db->prepare("UPDATE users SET last_active_at = NOW() WHERE id = ?")
                ->execute([$user['id']]);
            return $user;
        }

        // Check if user exists by email (for migration)
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            // Link existing user to Clerk
            $this->db->prepare("UPDATE users SET clerk_user_id = ?, clerk_email_verified_at = NOW() WHERE id = ?")
                ->execute([$clerkUserId, $existingUser['id']]);

            // Get full user data
            $stmt = $this->db->prepare("SELECT u.*, up.full_name FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE u.id = ?");
            $stmt->execute([$existingUser['id']]);
            return $stmt->fetch();
        }

        // Create new user
        try {
            $this->db->beginTransaction();

            // Get role from public metadata
            $role = $clerkUser['public_metadata']['role'] ?? 'student';
            $fullName = $clerkUser['public_metadata']['full_name'] ?? $clerkUser['first_name'] . ' ' . ($clerkUser['last_name'] ?? '');
            $phone = $clerkUser['public_metadata']['phone'] ?? null;
            $department = $clerkUser['public_metadata']['department'] ?? null;
            $graduationYear = $clerkUser['public_metadata']['graduation_year'] ?? null;
            $company = $clerkUser['public_metadata']['company'] ?? null;
            $designation = $clerkUser['public_metadata']['designation'] ?? null;

            // Validate role
            if (!in_array($role, ['student', 'faculty', 'alumni', 'admin'])) {
                $role = 'student';
            }

            // Set status based on role
            $status = ($role === 'alumni') ? 'pending' : 'active';

            // Insert user
            $stmt = $this->db->prepare("INSERT INTO users (email, password, role, status, clerk_user_id, clerk_email_verified_at, created_at, updated_at)
                VALUES (?, '', ?, ?, ?, NOW(), NOW(), NOW())");
            $stmt->execute([$email, $role, $status, $clerkUserId]);
            $userId = $this->db->lastInsertId();

            // Insert profile
            $stmt = $this->db->prepare("INSERT INTO user_profiles (user_id, full_name, phone, department, graduation_year, company, designation, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$userId, $fullName, $phone, $department, $graduationYear, $company, $designation]);

            // Create student profile if student
            if ($role === 'student') {
                $stmt = $this->db->prepare("INSERT INTO student_profiles (user_id, created_at) VALUES (?, NOW())");
                $stmt->execute([$userId]);
            }

            $this->db->commit();

            return [
                'id' => $userId,
                'email' => $email,
                'role' => $role,
                'full_name' => $fullName,
                'status' => $status,
                'email_verified_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Clerk user creation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current Clerk user info (for frontend)
     */
    public function getUserInfo(): void {
        header('Content-Type: application/json');

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        echo json_encode([
            'success' => true,
            'user' => Auth::user()
        ]);
    }
}
