<?php
/**
 * UniHelp - Skill Tracker Controller
 */

class SkillController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        Auth::requireApproved();

        $userId = Auth::id();

        $stmt = $this->db->query("SELECT * FROM skills ORDER BY category, name");
        $allSkills = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT us.*, s.name, s.category FROM user_skills us 
            JOIN skills s ON us.skill_id = s.id WHERE us.user_id = ?");
        $stmt->execute([$userId]);
        $userSkillsRows = $stmt->fetchAll();
        $skillMap = [];
        foreach ($userSkillsRows as $row) {
            $skillMap[$row['skill_id']] = $row;
        }

        $stmt = $this->db->prepare("SELECT AVG(progress) as readiness FROM user_skills WHERE user_id = ?");
        $stmt->execute([$userId]);
        $readiness = round($stmt->fetchColumn() ?? 0, 1);

        $this->view('skills/index', [
            'allSkills'  => $allSkills,
            'userSkills' => $skillMap,
            'readiness'  => $readiness,
            'userId'     => $userId,
            'db'         => $this->db,
        ]);
    }

    public function update(): void {
        // Manual skill updates are disabled.
        // Skills are automatically updated based on Mock Test scores only.
        Auth::requireLogin();
        $this->flash('error', 'Manual skill updates are disabled. Complete mock tests to automatically update your skill levels.');
        $this->redirect(BASE_URL . '/skill-tracker');
    }
}
