<?php
/**
 * UniHelp - Dashboard Controller
 * Role-based dashboard routing and data
 */

class DashboardController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        Auth::requireApproved();
        $role = Auth::role();
        $this->redirect(BASE_URL . '/dashboard/' . $role);
    }

    public function student(): void {
        Auth::requireStudent();
        Auth::requireApproved();
        Auth::refreshSession($this->db);

        $userId = Auth::id();
        
        // 1. Get student profile & basic info
        $stmt = $this->db->prepare("SELECT sp.*, up.full_name, up.department, up.bio FROM student_profiles sp 
            JOIN user_profiles up ON sp.user_id = up.user_id WHERE sp.user_id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM applications WHERE user_id = ?");
        $stmt->execute([$userId]);
        $applicationCount = $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM mentorship_requests WHERE student_id = ? AND status = 'pending'");
        $stmt->execute([$userId]);
        $pendingMentorship = $stmt->fetchColumn();

        // 2. Fetch all components for progress calculation
        // -- Skills (40% weight) --
        $stmt = $this->db->prepare("SELECT AVG(progress) as readiness FROM user_skills WHERE user_id = ?");
        $stmt->execute([$userId]);
        $skillScore = round($stmt->fetchColumn() ?? 0, 1);

        // -- Projects (20% weight: 5 points per project, max 20) --
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM student_projects WHERE user_id = ?");
        $stmt->execute([$userId]);
        $projectCount = (int)$stmt->fetchColumn();
        $projectScore = min($projectCount * 5, 20);

        // -- Mock Tests (20% weight: avg score out of 100) --
        $testScore = 0;
        try {
            $stmt = $this->db->prepare("SELECT AVG((score / max_score) * 100) FROM mock_test_attempts WHERE user_id = ?");
            $stmt->execute([$userId]);
            $rawTest = $stmt->fetchColumn();
            $testScore = round(($rawTest / 100) * 20, 1);
        } catch (Exception $e) { /* Table may not exist yet */ }

        // -- Contests (20% weight: weekly contest performance) --
        $contestScore = 0;
        try {
            $stmt = $this->db->prepare("SELECT AVG((score / total_marks) * 100) FROM contest_attempts WHERE student_id = ?");
            $stmt->execute([$userId]);
            $rawContest = $stmt->fetchColumn();
            $contestScore = round(($rawContest / 100) * 20, 1);
        } catch (Exception $e) { }

        // 3. Final Percentage Calculation
        $overallProgress = round(($skillScore * 0.4) + $projectScore + $testScore + $contestScore, 1);
        $overallProgress = min($overallProgress, 100);

        // 4. Eligibility Status
        $eligibility = 'Not Eligible';
        $statusColor = 'danger';
        if ($overallProgress >= 75) {
            $eligibility = 'Ready for Interviews';
            $statusColor = 'success';
        } elseif ($overallProgress >= 50) {
            $eligibility = 'Near Ready (Keep improving)';
            $statusColor = 'warning';
        }

        // 5. Recommendations & Intelligence Engine
        $recommendations = [];
        
        // --- Skill-based Insights ---
        $stmt = $this->db->prepare("SELECT s.name, us.progress 
            FROM user_skills us 
            JOIN skills s ON us.skill_id = s.id 
            WHERE us.user_id = ? AND us.progress < 60 
            ORDER BY us.progress ASC LIMIT 2");
        $stmt->execute([$userId]);
        while ($weakSkill = $stmt->fetch()) {
            $recommendations[] = "Low Readiness in " . $weakSkill['name'] . ". Practice more mock tests to boost your score.";
        }

        // --- Goal Alignment Insight ---
        if (!empty($profile['career_goal'])) {
            $recommendations[] = "Stay focused on " . ucfirst($profile['career_goal']) . " roles. Completing relevant projects will help.";
        } else {
            $recommendations[] = "Define your Career Goal in settings to get more tailored opportunities.";
        }

        // --- Mock Test Insight ---
        if ($testScore < 5) {
            $recommendations[] = "Practice Makes Perfect: Take your first technical mock test today.";
        }

        // 6. Pending / Priority Tasks
        $pendingTasks = [];
        
        // - Profile Task
        if (empty($profile['bio'])) {
            $pendingTasks[] = ['title' => 'Complete your Profile Details', 'link' => BASE_URL . '/dashboard/profile'];
        }
        
        // - Roadmap Task
        if (empty($profile['career_goal'])) {
            $pendingTasks[] = ['title' => 'Select your Career Roadmap', 'link' => BASE_URL . '/career-roadmap'];
        }
        
        // - Mock Test Task (Specific)
        try {
            $stmt = $this->db->prepare("SELECT title FROM mock_tests WHERE is_active = 1 AND id NOT IN (SELECT mock_test_id FROM mock_test_attempts WHERE user_id = ?) LIMIT 1");
            $stmt->execute([$userId]);
            if ($testTitle = $stmt->fetchColumn()) {
                $pendingTasks[] = ['title' => "Upcoming: " . $testTitle, 'link' => BASE_URL . '/mock-tests/list'];
            }
        } catch (Exception $e) {}

        // - Mentorship Task
        if ($pendingMentorship > 0) {
            $pendingTasks[] = ['title' => "Review $pendingMentorship Pending Mentorship Requests", 'link' => BASE_URL . '/mentorship'];
        }

        // - Project Task
        if ($projectCount === 0) {
            $pendingTasks[] = ['title' => 'Submit your first Project', 'link' => BASE_URL . '/projects'];
        }

        // - Contest Task (Weekly)
        $stmtContest = $this->db->prepare("SELECT id, title FROM contests WHERE end_time > NOW() AND id NOT IN (SELECT contest_id FROM contest_attempts WHERE student_id = ?) LIMIT 1");
        $stmtContest->execute([$userId]);
        if ($activeContest = $stmtContest->fetch()) {
            $pendingTasks[] = ['title' => "Participate in " . $activeContest['title'], 'link' => BASE_URL . '/contests'];
        }

        // Radar Chart Data
        $stmtRadar = $this->db->prepare("SELECT s.name, us.progress FROM user_skills us JOIN skills s ON us.skill_id = s.id WHERE us.user_id = ? ORDER BY us.progress DESC LIMIT 6");
        $stmtRadar->execute([$userId]);
        $radarSkills = $stmtRadar->fetchAll();
        if (empty($radarSkills)) {
            // Default placeholder if no skills
            $radarSkills = [
                ['name' => 'Algorithms', 'progress' => 10],
                ['name' => 'Data Structures', 'progress' => 10],
                ['name' => 'System Design', 'progress' => 10],
                ['name' => 'Communication', 'progress' => 10],
                ['name' => 'Problem Solving', 'progress' => 10]
            ];
        }

        // 6.5 Daily Smart Suggestions (AI-like what to do today)
        $dailySuggestions = [];
        $dayOfWeek = date('w'); // 0 (Sun) to 6 (Sat)
        
        $dailyTips = [
            0 => "Sunday Planning: Review your Career Pathway goals for the upcoming week.",
            1 => "Momentum Monday: Complete one new Mock Test today to start strong.",
            2 => "Tech Tuesday: Spend 30 minutes updating your Resume with new skills.",
            3 => "Mid-week Check-in: Browse recent Job Opportunities tailored to your profile.",
            4 => "Throwback Thursday: Review previous contest solutions to understand where you can improve.",
            5 => "Focus Friday: Reach out to a Mentor. Networking is just as important as coding.",
            6 => "Skill Saturday: Work on your personal project and submit it to the platform."
        ];
        
        $dailySuggestions[] = [
            'type' => 'tip',
            'title' => 'Daily AI Tip',
            'desc' => $dailyTips[$dayOfWeek],
            'icon' => 'lightbulb',
            'color' => 'warning'
        ];

        if ($skillScore < 50) {
            $dailySuggestions[] = [
                'type' => 'action',
                'title' => 'Action Required',
                'desc' => 'Your skill readiness is below 50%. Focus your time today on taking Mock Tests.',
                'icon' => 'alert-triangle',
                'color' => 'danger'
            ];
        } elseif ($projectCount == 0) {
            $dailySuggestions[] = [
                'type' => 'action',
                'title' => 'Next Step',
                'desc' => 'You haven\'t uploaded any projects yet. Add one today to boost your profile visibility by 40%.',
                'icon' => 'layout-dashboard',
                'color' => 'info'
            ];
        } else {
            $dailySuggestions[] = [
                'type' => 'growth',
                'title' => 'Growth Opportunity',
                'desc' => 'Your profile is looking great! Apply for at least one internship or job today.',
                'icon' => 'rocket',
                'color' => 'success'
            ];
        }

        // 7. Get standard dashboard data
        $stmt = $this->db->query("SELECT o.*, u.email as poster_email FROM opportunities o 
            JOIN users u ON o.posted_by = u.id WHERE o.status = 'active' ORDER BY o.created_at DESC LIMIT 4");
        $opportunities = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        $unreadNotificationsCount = $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
        $stmt->execute([$userId]);
        $latestNotifications = $stmt->fetchAll();

        // 8. Render View
        $this->view('dashboard/student', [
            'profile' => $profile,
            'skillReadiness' => $skillScore,
            'overallProgress' => $overallProgress,
            'eligibility' => $eligibility,
            'statusColor' => $statusColor,
            'recommendations' => $recommendations,
            'pendingTasks' => $pendingTasks,
            'opportunities' => $opportunities,
            'applicationCount' => $applicationCount,
            'pendingMentorship' => $pendingMentorship,
            'unreadNotifications' => $unreadNotificationsCount,
            'latestNotifications' => $latestNotifications,
            'dailySuggestions' => $dailySuggestions,
            'radarSkills' => $radarSkills
        ]);
    }

    public function faculty(): void {
        Auth::requireFaculty();
        Auth::requireApproved();
        Auth::refreshSession($this->db);

        $userId = Auth::id();

        // Initialize defaults to avoid errors if queries fail
        $resumeReviews = 0;
        $pendingMentorship = 0;
        $skillAnalytics = [];

        try {
            // Correct logic: Count completed reviews by this faculty member
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM resume_reviews WHERE status = 'completed' AND faculty_id = ?");
            $stmt->execute([$userId]);
            $resumeReviews = $stmt->fetchColumn() ?: 0;
        } catch (Exception $e) { /* Table/column might not exist yet */ }

        try {
            // Get pending mentorship requests for this faculty/mentor
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM mentorship_requests WHERE mentor_id = ? AND status = 'pending'");
            $stmt->execute([$userId]);
            $pendingMentorship = $stmt->fetchColumn() ?: 0;
        } catch (Exception $e) { }

        try {
            // Fetch aggregate skill analytics for the campus (Top 6 skills)
            $stmt = $this->db->query("
                SELECT s.name, AVG(us.progress) as avg_progress 
                FROM user_skills us 
                JOIN skills s ON us.skill_id = s.id 
                GROUP BY s.id 
                ORDER BY avg_progress DESC 
                LIMIT 6
            ");
            $skillAnalytics = $stmt->fetchAll();
        } catch (Exception $e) { }

        $this->view('dashboard/faculty', [
            'resumeReviews' => $resumeReviews,
            'pendingMentorship' => $pendingMentorship,
            'skillAnalytics' => $skillAnalytics
        ]);
    }

    public function alumni(): void {
        Auth::requireAlumni();
        Auth::refreshSession($this->db);

        $userId = Auth::id();

        // Get mentorship requests
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM mentorship_requests WHERE mentor_id = ? AND status = 'pending'");
        $stmt->execute([$userId]);
        $pendingMentorship = $stmt->fetchColumn();

        // Get interview experiences posted
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM interview_experiences WHERE user_id = ?");
        $stmt->execute([$userId]);
        $experiencesPosted = $stmt->fetchColumn();

        $this->view('dashboard/alumni', [
            'pendingMentorship' => $pendingMentorship,
            'experiencesPosted' => $experiencesPosted
        ]);
    }

    public function admin(): void {
        Auth::requireAdmin();
        Auth::refreshSession($this->db);

        // Pending alumni approvals
        $stmt = $this->db->query("SELECT u.*, up.full_name, up.company FROM users u 
            JOIN user_profiles up ON u.id = up.user_id 
            WHERE u.role = 'alumni' AND u.status = 'pending'");
        $pendingAlumni = $stmt->fetchAll();

        // User counts
        $stmt = $this->db->query("SELECT role, COUNT(*) as cnt FROM users WHERE role != 'admin' GROUP BY role");
        $userCounts = [];
        while ($row = $stmt->fetch()) {
            $userCounts[$row['role']] = $row['cnt'];
        }

        // Platform stats
        $stmt = $this->db->query("SELECT COUNT(*) FROM opportunities WHERE status = 'active'");
        $activeOpportunities = $stmt->fetchColumn();
        $stmt = $this->db->query("SELECT COUNT(*) FROM applications");
        $totalApplications = $stmt->fetchColumn();
        $stmt = $this->db->query("SELECT COUNT(*) FROM mentorship_requests WHERE status = 'accepted'");
        $activeMentorships = $stmt->fetchColumn();

        // Count online students (active in the last 5 minutes)
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND last_active_at >= NOW() - INTERVAL 5 MINUTE");
            $onlineStudents = $stmt->fetchColumn();
        } catch (Exception $e) {
            $onlineStudents = 0;
        }

        $this->view('dashboard/admin', [
            'pendingAlumni' => $pendingAlumni,
            'userCounts' => $userCounts ?? [],
            'activeOpportunities' => $activeOpportunities,
            'totalApplications' => $totalApplications,
            'activeMentorships' => $activeMentorships,
            'onlineStudents' => $onlineStudents
        ]);
    }
    public function profile(): void {
        Auth::requireLogin();
        Auth::requireApproved();
        $userId = Auth::id();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid session token. Please try again.');
                $this->redirect(BASE_URL . '/dashboard/profile');
                return;
            }
            $fullName = $_POST['full_name'];
            $phone = $_POST['phone'];
            $department = $_POST['department'];
            $bio = $_POST['bio'];
            $careerGoal = $_POST['career_goal'] ?? null;
            $cgpa = (float)($_POST['cgpa'] ?? 0);
            $tenth = (float)($_POST['tenth_percentage'] ?? 0);
            $twelfth = (float)($_POST['twelfth_percentage'] ?? 0);
            $degreeLevel = $_POST['degree_level'] ?? null;
            $currentSemester = (int)($_POST['current_semester'] ?? 0);

            // Fetch current profile image to retain if no new one is uploaded
            $stmt = $this->db->prepare("SELECT profile_image FROM user_profiles WHERE user_id = ?");
            $stmt->execute([$userId]);
            $currentProfile = $stmt->fetch();
            $profileImage = $currentProfile['profile_image'] ?? null;

            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'public/uploads/profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $fileName = time() . '_' . basename($_FILES['profile_image']['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                    $profileImage = $fileName;
                }
            }

            try {
                $this->db->beginTransaction();
                
                $stmt = $this->db->prepare("UPDATE user_profiles SET full_name = ?, phone = ?, department = ?, bio = ?, profile_image = ? WHERE user_id = ?");
                $stmt->execute([$fullName, $phone, $department, $bio, $profileImage, $userId]);

                if (Auth::role() === 'student') {
                    $stmt = $this->db->prepare("UPDATE student_profiles SET career_goal = ?, cgpa = ?, tenth_percentage = ?, twelfth_percentage = ?, degree_level = ?, current_semester = ? WHERE user_id = ?");
                    $stmt->execute([$careerGoal, $cgpa, $tenth, $twelfth, $degreeLevel, $currentSemester, $userId]);
                }

                $this->db->commit();
                $this->flash('success', 'Profile updated successfully.');
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->flash('error', 'Failed to update profile.');
            }
            $this->redirect(BASE_URL . '/dashboard/profile');
            return;
        }

        $stmt = $this->db->prepare("SELECT u.email, u.role, up.*, sp.career_goal, sp.cgpa, sp.tenth_percentage, sp.twelfth_percentage, sp.degree_level, sp.current_semester 
            FROM users u 
            JOIN user_profiles up ON u.id = up.user_id 
            LEFT JOIN student_profiles sp ON u.id = sp.user_id 
            WHERE u.id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();

        $this->view('dashboard/profile', ['profile' => $profile]);
    }
}
