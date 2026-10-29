<?php
/**
 * UniHelp - Career Roadmap Controller
 */

class CareerController extends Controller {

    public function index(): void {
        Auth::requireStudent();
        Auth::requireApproved();

        $userId = Auth::id();
        
        $stmt = $this->db->prepare("SELECT career_goal FROM student_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        $careerGoal = $profile['career_goal'] ?? null;

        $stmt = $this->db->query("SELECT * FROM career_roadmaps ORDER BY order_index");
        $roadmaps = $stmt->fetchAll();

        // ✨ Phase 2: Skill Gap Analysis
        $userSkills = [];
        $stmt = $this->db->prepare("SELECT s.name FROM user_skills us JOIN skills s ON us.skill_id = s.id WHERE us.user_id = ?");
        $stmt->execute([$userId]);
        $userSkills = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $personalizedRoadmap = null;
        $gapAnalysis = ['acquired' => [], 'missing' => [], 'readiness' => 0];

        if ($careerGoal) {
            $stmt = $this->db->prepare("SELECT * FROM career_roadmaps WHERE career_goal = ? LIMIT 1");
            $stmt->execute([$careerGoal]);
            $personalizedRoadmap = $stmt->fetch();

            if ($personalizedRoadmap) {
                $required = json_decode($personalizedRoadmap['skills_required'], true) ?? [];
                foreach ($required as $skill) {
                    // Simple case-insensitive match
                    $found = false;
                    foreach ($userSkills as $us) {
                        if (strcasecmp($us, $skill) === 0) {
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        $gapAnalysis['acquired'][] = $skill;
                    } else {
                        $gapAnalysis['missing'][] = $skill;
                    }
                }
                $totalCount = count($required);
                if ($totalCount > 0) {
                    $gapAnalysis['readiness'] = round((count($gapAnalysis['acquired']) / $totalCount) * 100);
                }
            }
        }

        $this->view('career/index', [
            'careerGoal' => $careerGoal,
            'roadmaps' => $roadmaps,
            'personalizedRoadmap' => $personalizedRoadmap,
            'gapAnalysis' => $gapAnalysis,
            'userSkills' => $userSkills
        ]);
    }

    /**
     * AJAX: Get AI Career Advice based on skill gap
     */
    public function getAiAdvice(): void {
        Auth::requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        if (!$this->validateCsrf()) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            return;
        }

        $goal = $_POST['goal'] ?? 'Software Engineering';
        $missing = $_POST['missing'] ?? [];

        if (empty($missing)) {
            echo json_encode(['advice' => 'All your required skills are already in your profile! Keep practicing and build a strong portfolio.']);
            return;
        }

        $advice = GeminiAdvisor::getAdvice($goal, $missing);
        echo json_encode(['advice' => $advice]);
    }

    public function updateGoal(): void {
        Auth::requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/career-roadmap');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token.');
            $this->redirect(BASE_URL . '/career-roadmap');
            return;
        }

        $goal = $_POST['career_goal'] ?? null;
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM career_roadmaps WHERE career_goal = ?");
        $stmt->execute([$goal]);
        if ($stmt->fetchColumn() == 0) {
            $this->flash('error', 'Invalid career goal.');
            $this->redirect(BASE_URL . '/career-roadmap');
            return;
        }

        $stmt = $this->db->prepare("UPDATE student_profiles SET career_goal = ? WHERE user_id = ?");
        $stmt->execute([$goal, Auth::id()]);
        $this->flash('success', 'Career goal updated successfully.');
        $this->redirect(BASE_URL . '/career-roadmap');
    }
}
