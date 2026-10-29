<?php
/**
 * UniHelp - Base Controller
 * Provides common functionality for all controllers
 */

class Controller {
    protected $db;
    protected $viewPath;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->viewPath = BASE_PATH . '/app/views/';

        // Track user activity
        if (isset($_SESSION['user_id'])) {
            try {
                $stmt = $this->db->prepare("UPDATE users SET last_active_at = NOW() WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
            } catch (Exception $e) {}
        }
    }

    /**
     * Render a view with optional data
     */
    protected function view(string $view, array $data = []): void {
        $data['baseUrl'] = BASE_URL;
        $data['user'] = Auth::user();
        $data['viewPath'] = $this->viewPath;
        $data['flash'] = $this->getFlash();
        $data['csrf_token'] = $this->csrfToken();
        extract($data);
        require $this->viewPath . $view . '.php';
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $statusCode = 302): void {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Set flash message
     */
    protected function flash(string $type, string $message): void {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Get and clear flash message
     */
    protected function getFlash(): ?array {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): bool {
        $token = $_POST['csrf_token'] ?? '';
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate CSRF token
     */
    protected function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
