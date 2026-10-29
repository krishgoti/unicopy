<?php if (Auth::check()): ?>
<div id="rag-chatbot-widget" class="rag-chatbot-widget">
    <!-- Chat Window Content -->
    <div id="rag-chat-window" class="rag-chat-window d-none shadow-lg glassmorphic-window">
        
        <!-- Header -->
        <div class="rag-chat-header d-flex justify-content-between align-items-center p-3 text-white glass-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-robot fs-4"></i>
                <div class="d-flex flex-column">
                    <span class="fw-bold mb-0 lh-1">UniHelp AI</span>
                    <small class="text-white-50" style="font-size: 0.70rem;">Always ready to help</small>
                </div>
            </div>
            <button type="button" id="rag-close-btn" class="btn btn-sm text-white fs-5 border-0 bg-transparent opacity-75 hover-opacity-100">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>

        <!-- Chat Body -->
        <div id="rag-chat-body" class="rag-chat-body p-3">
            <div class="rag-message bot mb-3 d-flex flex-column align-items-start">
                <div class="msg-content p-2 px-3 rounded-4 bg-light shadow-sm text-dark d-inline-block">
                    Hello! I'm your digital career assistant answering natively from UniHelp's real-time knowledge base. How can I guide you today?
                </div>
            </div>
        </div>

        <!-- Quick FAQs -->
        <div class="rag-faqs px-3 py-2 border-top border-bottom border-secondary border-opacity-10 flex-shrink-0" id="rag-faqs-container">
            <div class="d-flex gap-2 overflow-auto hide-scrollbar pb-1">
                <button class="btn btn-sm btn-outline-primary rounded-pill rag-faq-btn flex-shrink-0 text-nowrap">What are my priority tasks?</button>
                <button class="btn btn-sm btn-outline-primary rounded-pill rag-faq-btn flex-shrink-0 text-nowrap">Check my placement readiness</button>
                <button class="btn btn-sm btn-outline-primary rounded-pill rag-faq-btn flex-shrink-0 text-nowrap">How do I improve my score?</button>
                <button class="btn btn-sm btn-outline-primary rounded-pill rag-faq-btn flex-shrink-0 text-nowrap">Give me interview tips</button>
                <button class="btn btn-sm btn-outline-primary rounded-pill rag-faq-btn flex-shrink-0 text-nowrap">Show me some project ideas</button>
            </div>
        </div>

        <!-- Input Area -->
        <div class="rag-chat-footer p-3 bg-white border-top border-secondary border-opacity-10 rounded-bottom-4">
            <form id="rag-chat-form" class="input-group">
                <input type="text" id="rag-chat-input" class="form-control rounded-pill rounded-end-0 border-primary shadow-none ps-3 border-opacity-50" placeholder="Ask a question..." autocomplete="off">
                <button type="submit" id="rag-send-btn" class="btn btn-primary rounded-pill rounded-start-0 px-3 shadow-sm transition-all text-white"><i class="bi bi-send-fill"></i></button>
            </form>
        </div>
        
    </div>

    <!-- Floating Toggle Button -->
    <button type="button" id="rag-toggle-btn" class="rag-toggle-btn shadow-lg btn btn-primary rounded-circle d-flex align-items-center justify-content-center pulse-animation transition-all">
        <i class="bi bi-chat-dots-fill fs-3"></i>
    </button>
</div>

<!-- Inject backend env vars + live student context directly to JS context securely -->
<script>
<?php
    $user = Auth::user();
    $ctx_sections = [
        'basic' => '',
        'academics' => '',
        'skills' => '',
        'projects' => '',
        'tests' => '',
        'readiness' => '',
        'applications' => '',
        'mentorship' => '',
        'tasks' => ''
    ];

    // Basic identity
    $basic_parts = [];
    if (!empty($user['name'])) {
        $basic_parts[] = "Student Name: " . htmlspecialchars($user['name']);
    }
    if (!empty($user['role'])) {
        $basic_parts[] = "Role: " . htmlspecialchars($user['role']);
    }
    $ctx_sections['basic'] = implode("\n", $basic_parts);

    // Pull fresh profile data for context
    try {
        global $db;
        if (!isset($db)) {
            require_once BASE_PATH . '/config/database.php';
            $db = Database::getInstance()->getConnection();
        }
    } catch (Exception $e) {
        // base DB connection failed
    }

    $uid = Auth::id();

    if (isset($db) && !empty($uid)) {
        // 1. Academic & Profile Info
        try {
            $s = $db->prepare("SELECT up.full_name, up.department, sp.career_goal, sp.cgpa, sp.current_semester, sp.degree_level, sp.tenth_percentage, sp.twelfth_percentage
                FROM user_profiles up
                LEFT JOIN student_profiles sp ON sp.user_id = up.user_id
                WHERE up.user_id = ?");
            $s->execute([$uid]);
            $p = $s->fetch(PDO::FETCH_ASSOC);
            if ($p) {
                $acad = [];
                if (!empty($p['full_name']))         $acad[] = "Full Name: " . $p['full_name'];
                if (!empty($p['department']))        $acad[] = "Department: " . $p['department'];
                if (!empty($p['career_goal']))       $acad[] = "Career Goal: " . $p['career_goal'];
                $acad[] = "CGPA: " . (!empty($p['cgpa']) ? $p['cgpa'] : "0.0");
                $acad[] = "Current Semester: " . (!empty($p['current_semester']) ? $p['current_semester'] : "0");
                if (!empty($p['degree_level']))      $acad[] = "Degree Level: " . $p['degree_level'];
                $acad[] = "10th Percentage: " . (!empty($p['tenth_percentage']) ? $p['tenth_percentage'] . "%" : "0%");
                $acad[] = "12th Percentage: " . (!empty($p['twelfth_percentage']) ? $p['twelfth_percentage'] . "%" : "0%");
                $ctx_sections['academics'] = implode("\n", $acad);
            } else {
                $ctx_sections['academics'] = "CGPA: 0.0\nCurrent Semester: 0\n10th Percentage: 0%\n12th Percentage: 0%";
            }
        } catch (Exception $e) {
            $ctx_sections['academics'] = "CGPA: 0.0\nCurrent Semester: 0\n10th Percentage: 0%\n12th Percentage: 0%";
        }

        // 2. Skills Info
        $skillScore = 0.0;
        try {
            $s = $db->prepare("SELECT s.name, us.progress FROM user_skills us JOIN skills s ON us.skill_id = s.id WHERE us.user_id = ? ORDER BY us.progress DESC");
            $s->execute([$uid]);
            $skills = $s->fetchAll(PDO::FETCH_ASSOC);
            if ($skills) {
                $skill_str = implode(", ", array_map(fn($sk) => $sk['name'] . " (" . $sk['progress'] . "%)", $skills));
                $ctx_sections['skills'] = "User Skills: " . $skill_str;
                
                // Calculate skill average
                $skill_progresses = array_map(fn($sk) => (float)$sk['progress'], $skills);
                $skillScore = round(array_sum($skill_progresses) / count($skill_progresses), 1);
            } else {
                $ctx_sections['skills'] = "User Skills: None (0 skills added)";
            }
        } catch (Exception $e) {
            $ctx_sections['skills'] = "User Skills: None (0 skills added)";
        }

        // 3. Projects Info (Fix column name from title to project_name)
        $projectCount = 0;
        $projectScore = 0.0;
        try {
            $s = $db->prepare("SELECT project_name FROM student_projects WHERE user_id = ? ORDER BY created_at DESC");
            $s->execute([$uid]);
            $projects = $s->fetchAll(PDO::FETCH_COLUMN);
            $projectCount = count($projects);
            
            $proj_parts = [];
            $proj_parts[] = "Projects Submitted: " . $projectCount;
            if ($projectCount > 0) {
                $proj_parts[] = "Submitted Projects List: " . implode(" | ", $projects);
            } else {
                $proj_parts[] = "Submitted Projects List: None";
            }
            $ctx_sections['projects'] = implode("\n", $proj_parts);
            $projectScore = min($projectCount * 5, 20);
        } catch (Exception $e) {
            $ctx_sections['projects'] = "Projects Submitted: 0\nSubmitted Projects List: None";
        }

        // 4. Mock Tests Info
        $testScore = 0;
        $avgMockScore = 0;
        $mockAttemptsCount = 0;
        try {
            $s = $db->prepare("SELECT COUNT(*), AVG((score / max_score) * 100) FROM mock_test_attempts WHERE user_id = ?");
            $s->execute([$uid]);
            $testRow = $s->fetch();
            $mockAttemptsCount = (int)($testRow[0] ?? 0);
            $rawTest = $testRow[1] ?? 0;
            $testScore = round(($rawTest / 100) * 20, 1);
            $avgMockScore = round($rawTest, 1);
        } catch (Exception $e) {}
        
        $test_parts = [];
        $test_parts[] = "Mock Tests Attempted: " . $mockAttemptsCount;
        $test_parts[] = "Mock Tests Avg Score: " . ($mockAttemptsCount > 0 ? $avgMockScore . "%" : "0%");

        // 5. Weekly Contests Info
        $contestScore = 0;
        $avgContestScore = 0;
        $contestAttemptsCount = 0;
        try {
            $s = $db->prepare("SELECT COUNT(*), AVG((score / total_marks) * 100) FROM contest_attempts WHERE student_id = ?");
            $s->execute([$uid]);
            $contestRow = $s->fetch();
            $contestAttemptsCount = (int)($contestRow[0] ?? 0);
            $rawContest = $contestRow[1] ?? 0;
            $contestScore = round(($rawContest / 100) * 20, 1);
            $avgContestScore = round($rawContest, 1);
        } catch (Exception $e) {}

        $test_parts[] = "Weekly Contests Attempted: " . $contestAttemptsCount;
        $test_parts[] = "Weekly Contests Avg Score: " . ($contestAttemptsCount > 0 ? $avgContestScore . "%" : "0%");
        $ctx_sections['tests'] = implode("\n", $test_parts);

        // 6. Calculate Placement Readiness Score & Eligibility
        $overallProgress = round(($skillScore * 0.4) + $projectScore + $testScore + $contestScore, 1);
        $overallProgress = min($overallProgress, 100);
        
        $eligibility = 'Not Eligible';
        if ($overallProgress >= 75) {
            $eligibility = 'Ready for Interviews';
        } elseif ($overallProgress >= 50) {
            $eligibility = 'Near Ready (Keep improving)';
        }

        $readiness_parts = [];
        $readiness_parts[] = "Placement Readiness Score: " . $overallProgress . "%";
        $readiness_parts[] = "Placement Interview Eligibility: " . $eligibility;
        $readiness_parts[] = "Skills Score Contribution: " . round($skillScore * 0.4, 1) . "% (out of 40% max)";
        $readiness_parts[] = "Projects Score Contribution: " . $projectScore . "% (out of 20% max)";
        $readiness_parts[] = "Mock Tests Score Contribution: " . $testScore . "% (out of 20% max)";
        $readiness_parts[] = "Weekly Contests Score Contribution: " . $contestScore . "% (out of 20% max)";
        $ctx_sections['readiness'] = implode("\n", $readiness_parts);

        // 7. Job/Placement Applications Info (Fix column name from o.title to o.role)
        try {
            $s = $db->prepare("SELECT o.company_name, o.role, a.status FROM applications a JOIN opportunities o ON a.opportunity_id = o.id WHERE a.user_id = ? ORDER BY a.applied_at DESC LIMIT 3");
            $s->execute([$uid]);
            $apps = $s->fetchAll(PDO::FETCH_ASSOC);
            $app_parts = [];
            $app_parts[] = "Job Applications Submitted: " . count($apps);
            if ($apps) {
                $app_strs = [];
                foreach ($apps as $app) {
                    $app_strs[] = htmlspecialchars($app['company_name']) . " - " . htmlspecialchars($app['role']) . " (Status: " . htmlspecialchars($app['status']) . ")";
                }
                $app_parts[] = "Recent Applications: " . implode(" | ", $app_strs);
            } else {
                $app_parts[] = "Recent Applications: None";
            }
            $ctx_sections['applications'] = implode("\n", $app_parts);
        } catch (Exception $e) {
            $ctx_sections['applications'] = "Job Applications Submitted: 0\nRecent Applications: None";
        }

        // 8. Mentorship Connections Info
        try {
            $s = $db->prepare("SELECT up.full_name, mr.status FROM mentorship_requests mr JOIN user_profiles up ON mr.mentor_id = up.user_id WHERE mr.student_id = ? ORDER BY mr.created_at DESC LIMIT 2");
            $s->execute([$uid]);
            $mentors = $s->fetchAll(PDO::FETCH_ASSOC);
            $ment_parts = [];
            $ment_parts[] = "Mentors Engaged: " . count($mentors);
            if ($mentors) {
                $mentor_strs = [];
                foreach ($mentors as $m) {
                    $mentor_strs[] = htmlspecialchars($m['full_name']) . " (Status: " . htmlspecialchars($m['status']) . ")";
                }
                $ment_parts[] = "Mentorship Status: " . implode(" | ", $mentor_strs);
            } else {
                $ment_parts[] = "Mentorship Status: None";
            }
            $ctx_sections['mentorship'] = implode("\n", $ment_parts);
        } catch (Exception $e) {
            $ctx_sections['mentorship'] = "Mentors Engaged: 0\nMentorship Status: None";
        }

        // 9. Pending / Priority Tasks
        $pending = [];
        try {
            // Profile task
            $s = $db->prepare("SELECT bio FROM user_profiles WHERE user_id = ?"); $s->execute([$uid]);
            if (empty($s->fetchColumn())) $pending[] = "Complete your Profile Details";
            // Roadmap task
            $s = $db->prepare("SELECT career_goal FROM student_profiles WHERE user_id = ?"); $s->execute([$uid]);
            if (empty($s->fetchColumn())) $pending[] = "Select your Career Roadmap"; 
            // Project task
            if ($projectCount === 0) $pending[] = "Submit your first Project";
            // Unattempted mock test
            $s = $db->prepare("SELECT title FROM mock_tests WHERE is_active = 1 AND id NOT IN (SELECT mock_test_id FROM mock_test_attempts WHERE user_id = ?) LIMIT 1");
            $s->execute([$uid]);
            if ($t = $s->fetchColumn()) $pending[] = "Take Mock Test: " . $t;
            // Contest
            $s = $db->prepare("SELECT title FROM contests WHERE end_time > NOW() AND id NOT IN (SELECT contest_id FROM contest_attempts WHERE student_id = ?) LIMIT 1");
            $s->execute([$uid]);
            if ($c = $s->fetchColumn()) $pending[] = "Participate in Contest: " . $c;
        } catch (Exception $e) {}

        if ($pending) {
            $ctx_sections['tasks'] = "Current Priority Tasks: " . implode(" | ", $pending);
        } else {
            $ctx_sections['tasks'] = "Current Priority Tasks: All tasks are up to date. Great work!";
        }
    }

    // Fallback: merge all non-empty sections into a single string for backward compatibility
    $user_context_str = implode("\n\n", array_filter(array_values($ctx_sections)));
?>
    window.RAG_CONFIG = {
        api_url: "<?= defined('RAG_API_URL') ? RAG_API_URL : 'http://localhost:8000/api/chat'; ?>",
        token: "<?= defined('RAG_STUDENT_TOKEN') ? RAG_STUDENT_TOKEN : '' ?>",
        user_role: "<?= Auth::role() ?>",
        user_context: <?= json_encode($user_context_str) ?>,
        user_context_sections: <?= json_encode($ctx_sections) ?>
    };
</script>
<?php endif; ?>
