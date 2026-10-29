<?php
/**
 * UniHelp - API Controller
 * Handles AJAX/API requests
 */

class ApiController extends Controller {

    public function notifications(): void {
        Auth::requireLogin();

        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([Auth::id()]);
        $notifications = $stmt->fetchAll();

        $this->json(['notifications' => $notifications]);
    }

    public function markRead(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Invalid request'], 400);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, Auth::id()]);
        }
        $this->json(['success' => true]);
    }
}
