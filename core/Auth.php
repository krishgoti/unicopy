<?php
/**
 * UniHelp - Authentication Middleware
 * Handles session-based auth and role-based access control
 */

class Auth {
    
    /**
     * Check if user is logged in
     */
    public static function check(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     */
    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user data
     */
    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get current user role
     */
    public static function role(): ?string {
        return $_SESSION['user']['role'] ?? null;
    }

    /**
     * Require login - redirect to login if not authenticated
     */
    public static function requireLogin(): void {
        if (!self::check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Require specific role(s)
     */
    public static function requireRole(string|array $roles): void {
        self::requireLogin();
        $userRole = self::role();
        $allowed = is_array($roles) ? $roles : [$roles];
        
        if (!in_array($userRole, $allowed)) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    /**
     * Require student role
     */
    public static function requireStudent(): void {
        self::requireRole('student');
    }

    /**
     * Require faculty role
     */
    public static function requireFaculty(): void {
        self::requireRole('faculty');
    }

    /**
     * Require alumni role
     */
    public static function requireAlumni(): void {
        self::requireRole('alumni');
    }

    /**
     * Require admin role
     */
    public static function requireAdmin(): void {
        self::requireRole('admin');
    }

    /**
     * Require faculty or alumni (mentors)
     */
    public static function requireMentor(): void {
        self::requireRole(['faculty', 'alumni']);
    }

    /**
     * Require approved status (for alumni)
     */
    public static function requireApproved(): void {
        self::requireLogin();
        $status = $_SESSION['user']['status'] ?? '';
        if ($status === 'pending' && self::role() === 'alumni') {
            header('Location: ' . BASE_URL . '/dashboard/alumni?pending=1');
            exit;
        }
        if ($status === 'rejected') {
            header('Location: ' . BASE_URL . '/login?rejected=1');
            exit;
        }
    }
    /**
     * Check if user is email verified
     */
    public static function isVerified(): bool {
        return !empty($_SESSION['user']['email_verified_at']);
    }

    /**
     * Refresh user session data from the database
     */
    public static function refreshSession(PDO $db): void {
        if (!self::check()) return;
        
        $userId = self::id();
        $stmt = $db->prepare("SELECT email, role, status, email_verified_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Get full name from user_profile
            $stmt = $db->prepare("SELECT full_name FROM user_profiles WHERE user_id = ?");
            $stmt->execute([$userId]);
            $fullName = $stmt->fetchColumn();
            
            $_SESSION['user'] = [
                'id' => $userId,
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $fullName ?? ($_SESSION['user']['full_name'] ?? 'User'),
                'status' => $user['status'],
                'email_verified_at' => $user['email_verified_at']
            ];
        }
    }
}
