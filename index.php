<?php
/**
 * UniHelp - Front Controller
 * REST-style routing for the application
 */

require_once __DIR__ . '/config/config.php';

// Parse URL for routing
$url = $_GET['url'] ?? 'home';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = $url ? explode('/', $url) : ['home'];

// System Lockdown Interceptor
if (file_exists(BASE_PATH . '/.lockdown')) {
    if (!Auth::check() || Auth::role() !== 'admin') {
        $allowedLockdownUrls = ['login', 'logout', 'verify-email'];
        if (!in_array($url, $allowedLockdownUrls)) {
            http_response_code(503);
            require_once BASE_PATH . '/app/views/errors/lockdown.php';
            exit;
        }
    }
}


// Route mapping: controller/action/params
$controllerName = ucfirst($urlParts[0] ?? 'home') . 'Controller';
$action = $urlParts[1] ?? 'index';
$params = array_slice($urlParts, 2);

// Special routes
$routeMap = [
    '' => ['HomeController', 'index'],
    'home' => ['HomeController', 'index'],
    'login' => ['AuthController', 'login'],
    'logout' => ['AuthController', 'logout'],
    'register' => ['AuthController', 'register'],
    'verify-email' => ['AuthController', 'verify'],
    'verify-otp' => ['AuthController', 'verifyOtp'],
    'send-otp' => ['AuthController', 'sendLoginOtp'],
    'resend-otp' => ['AuthController', 'resendOtp'],
    'resend-verification' => ['AuthController', 'resendVerification'],
    'forgot-password' => ['PasswordController', 'forgot'],
    'forgot-password/send' => ['PasswordController', 'sendResetLink'],
    'reset-password' => ['PasswordController', 'reset'],
    'reset-password/update' => ['PasswordController', 'update'],
    'dashboard' => ['DashboardController', 'index'],
    'dashboard/student' => ['DashboardController', 'student'],
    'dashboard/faculty' => ['DashboardController', 'faculty'],
    'dashboard/alumni' => ['DashboardController', 'alumni'],
    'dashboard/admin' => ['DashboardController', 'admin'],
    'dashboard/profile' => ['DashboardController', 'profile'],
    'career-roadmap' => ['CareerController', 'index'],
    'career-roadmap/update-goal' => ['CareerController', 'updateGoal'],
    'skill-tracker' => ['SkillController', 'index'],
    'skill-tracker/update' => ['SkillController', 'update'],
    'resume' => ['ResumeController', 'index'],
    'resume/upload' => ['ResumeController', 'upload'],
    'resume/delete' => ['ResumeController', 'delete'],
    'resume/feedback' => ['ResumeController', 'feedback'],
    'resume/ai-result' => ['ResumeController', 'aiResult'],
    'resume/faculty-queue' => ['ResumeController', 'facultyQueue'],
    'resume/review' => ['ResumeController', 'facultyReview'],
    'resume/faculty-feedback' => ['ResumeController', 'submitFeedback'],
    'resume/history' => ['ResumeController', 'history'],
    'opportunities' => ['OpportunityController', 'index'],
    'opportunities/post' => ['OpportunityController', 'post'],
    'opportunities/apply' => ['OpportunityController', 'apply'],
    'opportunities/applications' => ['OpportunityController', 'viewApplications'],
    'opportunities/update-status' => ['OpportunityController', 'updateStatus'],
    'interview-experiences' => ['InterviewController', 'index'],
    'interview-experiences/create' => ['InterviewController', 'create'],
    'interview-experiences/post' => ['InterviewController', 'post'],
    'interview-experiences/show' => ['InterviewController', 'show'],
    'interview-experiences/approve' => ['InterviewController', 'approve'],
    'interview-experiences/reject' => ['InterviewController', 'reject'],
    'mentorship' => ['MentorshipController', 'index'],
    'test-scores' => ['TestController', 'index'],
    'test-scores/add' => ['TestController', 'add'],
    'projects' => ['ProjectController', 'index'],
    'projects/add' => ['ProjectController', 'add'],
    'projects/delete' => ['ProjectController', 'delete'],
    'mentorship/request' => ['MentorshipController', 'request'],
    'mentorship/respond' => ['MentorshipController', 'respond'],
    'mentorship/profile' => ['MentorshipController', 'profile'],
    'mentorship/save-profile' => ['MentorshipController', 'saveProfile'],
    'mentorship/toggle-availability' => ['MentorshipController', 'toggleAvailability'],
    'mentorship/add-slot' => ['MentorshipController', 'addSlot'],
    'mentorship/delete-slot' => ['MentorshipController', 'deleteSlot'],
    'mentorship/book-slot' => ['MentorshipController', 'bookSlot'],
    'mentorship/cancel-booking' => ['MentorshipController', 'cancelBooking'],
    'admin/users' => ['AdminController', 'users'],
    'admin/approve-alumni' => ['AdminController', 'approveAlumni'],
    'admin/delete-user' => ['AdminController', 'deleteUser'],
    'admin/roadmaps' => ['AdminController', 'roadmaps'],
    'admin/roadmap/save' => ['AdminController', 'saveRoadmap'],
    'admin/roadmap/delete' => ['AdminController', 'deleteRoadmap'],
    'admin/analytics' => ['AdminController', 'analytics'],
    'admin/settings' => ['AdminController', 'settings'],
    'admin/utility/resumes' => ['AdminController', 'utilityResumes'],
    'admin/utility/delete-resumes' => ['AdminController', 'utilityDeleteResumes'],
    'admin/utility/db-stats' => ['AdminController', 'utilityDbStats'],
    'admin/utility/optimize-db' => ['AdminController', 'utilityOptimizeDb'],
    'admin/utility/toggle-lockdown' => ['AdminController', 'utilityToggleLockdown'],
    'api/notifications' => ['ApiController', 'notifications'],
    'contests' => ['ContestController', 'index'],
    'contests/create' => ['ContestController', 'create'],
    'contests/show' => ['ContestController', 'show'],
    'contests/attempt' => ['ContestController', 'attempt'],
    'contests/submit' => ['ContestController', 'submit'],
    'contests/leaderboard' => ['ContestController', 'leaderboard'],
    'contests/history' => ['ContestController', 'history'],
    'contests/delete' => ['ContestController', 'delete'],

    // Skill-based mock tests
    'mock-tests' => ['MockTestController', 'index'],
    'mock-tests/faculty' => ['MockTestController', 'faculty'],
    'mock-tests/create' => ['MockTestController', 'create'],
    'mock-tests/faculty/results' => ['MockTestController', 'facultyResults'],
    'mock-tests/list' => ['MockTestController', 'list'],
    'mock-tests/attempt' => ['MockTestController', 'attempt'],
    'mock-tests/submit' => ['MockTestController', 'submit'],
    'mock-tests/edit' => ['MockTestController', 'edit'],
    'mock-tests/delete' => ['MockTestController', 'delete'],
    'mock-tests/history' => ['MockTestController', 'history'],
    'mock-tests/start' => ['MockTestController', 'start'],
];

// Check route map first
$routeKey = implode('/', array_slice($urlParts, 0, 2));
if (isset($routeMap[$url])) {
    $controllerName = $routeMap[$url][0];
    $action = $routeMap[$url][1];
    $params = array_slice($urlParts, 2);
} elseif (isset($routeMap[$routeKey])) {
    $controllerName = $routeMap[$routeKey][0];
    $action = $routeMap[$routeKey][1];
    $params = array_slice($urlParts, 2);
}

// Load and execute controller
$controllerFile = __DIR__ . '/app/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();
    
    if (method_exists($controller, $action)) {
        call_user_func_array([$controller, $action], $params);
    } else {
        http_response_code(404);
        require_once __DIR__ . '/app/views/errors/404.php';
    }
} else {
    http_response_code(404);
    require_once __DIR__ . '/app/views/errors/404.php';
}
