<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'UniHelp' ?> - Smart Career Guidance</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css?v=<?= time() ?>" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/skeleton.css?v=<?= time() ?>" rel="stylesheet">
    <!-- Icon Systems -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Check saved theme and apply immediately to prevent flicker
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>">
                <i class="bi bi-mortarboard-fill"></i>
                <span>UniHelp</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">


<?php 
// Helper to determine active link
if (!function_exists('isActive')) {
    function isActive($path, $current) {
        // Exact match for 'home'
        if ($path === 'home') {
            return ($current === 'home' || $current === '') ? 'active fw-black' : '';
        }
        // Starts with match for others (e.g., 'dashboard', 'opportunities')
        return (strpos($current, $path) === 0) ? 'active fw-black' : '';
    }
}
$currentUrl = $_GET['url'] ?? 'home';
?>

                    <?php if (Auth::check()): ?>
                        <?php $role = Auth::role(); ?>
                        <li class="nav-item"><a class="nav-link px-3 <?= isActive('dashboard', $currentUrl) ?>" href="<?= BASE_URL ?>/dashboard/<?= $role ?>">Dashboard</a></li>
                        
                        <?php if ($role === 'student'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 <?= (isActive('career-roadmap', $currentUrl) || isActive('skill-tracker', $currentUrl) || isActive('projects', $currentUrl)) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Career
                                </a>
                                <ul class="dropdown-menu border-0 shadow-lg">
                                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/career-roadmap"><i data-lucide="map" style="width: 16px; height: 16px;" class="text-primary"></i> Roadmap</a></li>
                                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/skill-tracker"><i data-lucide="trending-up" style="width: 16px; height: 16px;" class="text-success"></i> Skills</a></li>
                                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/projects"><i data-lucide="layout-dashboard" style="width: 16px; height: 16px;" class="text-warning"></i> Projects</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 <?= isActive('opportunities', $currentUrl) ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Placements
                                </a>
                                <ul class="dropdown-menu border-0 shadow-lg">
                                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/opportunities"><i data-lucide="search" style="width: 16px; height: 16px;" class="text-primary"></i> Find Jobs</a></li>
                                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/opportunities?type=my_applications"><i data-lucide="file-check-2" style="width: 16px; height: 16px;" class="text-success"></i> My Applications</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 <?= (isActive('resume', $currentUrl) || isActive('mock-tests', $currentUrl) || isActive('contests', $currentUrl) || isActive('interview-experiences', $currentUrl)) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Resources
                                </a>
                                <ul class="dropdown-menu border-0 shadow-lg">
                                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/resume"><i data-lucide="file-user" style="width: 16px; height: 16px;" class="text-info"></i> Resume Builder</a></li>
                                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/mock-tests/list"><i data-lucide="help-circle" style="width: 16px; height: 16px;" class="text-primary"></i> Mock Tests</a></li>
                                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/contests"><i data-lucide="award" style="width: 16px; height: 16px;" class="text-warning"></i> Weekly Contests</a></li>
                                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/interview-experiences"><i data-lucide="message-square-quote" style="width: 16px; height: 16px;" class="text-secondary"></i> Interview Experiences</a></li>
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link px-3 <?= isActive('mentorship', $currentUrl) ?>" href="<?= BASE_URL ?>/mentorship">Mentorship</a></li>

                        <?php elseif ($role === 'faculty'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 <?= (isActive('resume', $currentUrl) || isActive('mentorship', $currentUrl) || isActive('mock-tests', $currentUrl)) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                     Students
                                 </a>
                                 <ul class="dropdown-menu border-0 shadow-lg">
                                     <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/resume"><i class="bi bi-file-earmark-check me-2 text-warning"></i>Review Resumes</a></li>
                                     <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/mentorship"><i class="bi bi-people me-2 text-info"></i>Mentorship Requests</a></li>
                                     <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/mock-tests/faculty"><i class="bi bi-patch-question me-2 text-primary"></i>Mock Tests</a></li>
                                 </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link px-3 <?= isActive('contests', $currentUrl) ?>" href="<?= BASE_URL ?>/contests">Weekly Contests</a></li>
                            <li class="nav-item"><a class="nav-link px-3 <?= isActive('interview-experiences', $currentUrl) ?>" href="<?= BASE_URL ?>/interview-experiences"><i class="bi bi-chat-square-quote me-1"></i> Share Experience</a></li>

                        <?php elseif ($role === 'alumni'): ?>
                            <li class="nav-item"><a class="nav-link px-3 <?= isActive('mentorship', $currentUrl) ?>" href="<?= BASE_URL ?>/mentorship">Mentorship Requests</a></li>
                            <li class="nav-item"><a class="nav-link px-3 <?= isActive('interview-experiences', $currentUrl) ?>" href="<?= BASE_URL ?>/interview-experiences"><i class="bi bi-chat-square-quote me-1"></i> Share Experience</a></li>
                            <li class="nav-item"><a class="nav-link px-3 <?= isActive('opportunities', $currentUrl) ?>" href="<?= BASE_URL ?>/opportunities">Jobs</a></li>

                        <?php elseif ($role === 'admin'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 <?= isActive('admin', $currentUrl) ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Administration
                                </a>
                                <ul class="dropdown-menu border-0 shadow-lg">
                                    <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people me-2"></i>Manage Users</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/roadmaps"><i class="bi bi-map me-2"></i>Manage Roadmaps</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/analytics"><i class="bi bi-graph-up me-2"></i>Analytics</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle px-3 <?= isActive('opportunities', $currentUrl) ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Placements
                                </a>
                                <ul class="dropdown-menu border-0 shadow-lg">
                                    <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/opportunities/post"><i class="bi bi-plus-circle me-2 text-primary"></i>Post Opportunity</a></li>
                                    <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/opportunities"><i class="bi bi-list-ul me-2 text-secondary"></i>Manage Opportunities</a></li>
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link px-3 <?= isActive('interview-experiences', $currentUrl) ?>" href="<?= BASE_URL ?>/interview-experiences"><i class="bi bi-chat-square-quote me-1"></i> Share Experience</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item d-flex align-items-center me-2">
                        <button id="theme-toggle" class="btn btn-link nav-link px-2 d-flex align-items-center" aria-label="Toggle theme">
                            <i data-lucide="moon-stars" style="width: 20px; height: 20px;"></i>
                        </button>
                    </li>
                    <?php if (Auth::check()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="avatar-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: var(--dark);">
                                    <i class="bi bi-person-fill" style="font-size: 1.1rem;"></i>
                                </div>
                                <?= htmlspecialchars($user['full_name'] ?? 'Account') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg glass-card" style="padding: 0.5rem;">
                                <li><a class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/dashboard/profile"><i data-lucide="user-cog" style="width: 16px; height: 16px;"></i> Profile Settings</a></li>
                                <li><a class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center gap-2" href="<?= BASE_URL ?>/dashboard/<?= Auth::role() ?>"><i data-lucide="layout-dashboard" style="width: 16px; height: 16px;"></i> Dashboard</a></li>
                                <li><hr class="dropdown-divider opacity-10"></li>
                                <li><a class="dropdown-item py-2 px-3 rounded-3 text-danger d-flex align-items-center gap-2" href="<?= BASE_URL ?>/logout"><i data-lucide="log-out" style="width: 16px; height: 16px;"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login">Login</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-primary fw-bold px-4" style="border-radius: 50px;" href="<?= BASE_URL ?>/register">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
</nav>
    
    <div id="skeleton-loader" class="container-fluid py-5">
        <div class="sk-container">
            <div class="skeleton-shimmer sk-header"></div>
            <div class="skeleton-shimmer sk-sub"></div>
            
            <div class="sk-grid d-lg-grid d-none animate-pulse">
                <div class="sk-card">
                    <div class="skeleton-shimmer sk-card-title"></div>
                    <div class="skeleton-shimmer sk-card-text"></div>
                    <div class="skeleton-shimmer sk-card-text"></div>
                    <div class="skeleton-shimmer sk-card-text-short"></div>
                </div>
                <div class="sk-card">
                    <div class="skeleton-shimmer sk-card-title"></div>
                    <div class="skeleton-shimmer sk-card-text"></div>
                    <div class="skeleton-shimmer sk-card-text"></div>
                    <div class="skeleton-shimmer sk-card-text-short"></div>
                </div>
                <div class="sk-card">
                    <div class="skeleton-shimmer sk-card-title"></div>
                    <div class="skeleton-shimmer sk-card-text"></div>
                    <div class="skeleton-shimmer sk-card-text"></div>
                    <div class="skeleton-shimmer sk-card-text-short"></div>
                </div>
            </div>
            
            <div class="sk-large skeleton-shimmer mt-4"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const skeleton = document.getElementById('skeleton-loader');
            
            let loaded = false;
            let minTimePassed = false;

            const hideSkeleton = () => {
                if (skeleton && !skeleton.classList.contains('hidden')) {
                    skeleton.classList.add('hidden');
                    // Force immediate non-blocking state
                    skeleton.style.pointerEvents = 'none';
                    skeleton.style.opacity = '0';
                    
                    // Remove from DOM after transition
                    setTimeout(() => {
                        skeleton.style.display = 'none';
                        skeleton.remove(); 
                    }, 400);
                }
            };

            // Force hide after 2.5 seconds anyway (Safety Fallback)
            setTimeout(hideSkeleton, 2500);

            setTimeout(() => {
                minTimePassed = true;
                if(loaded) hideSkeleton();
            }, 600); // Reduced min time for snappy feel
            
            window.addEventListener('load', () => {
                 loaded = true;
                 if(minTimePassed) hideSkeleton();
            });

            // Dark Mode Toggle Logic
            const themeToggle = document.getElementById('theme-toggle');
            const html = document.documentElement;

            function updateThemeIcon(theme) {
                if (!themeToggle) return;
                const icon = themeToggle.querySelector('i');
                if (theme === 'dark') {
                    icon.className = 'bi bi-sun-fill fs-5 text-warning';
                } else {
                    icon.className = 'bi bi-moon-stars-fill fs-5';
                }
            }

            // Init icon
            updateThemeIcon(html.getAttribute('data-theme'));

            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const currentTheme = html.getAttribute('data-theme');
                    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                    html.setAttribute('data-theme', newTheme);
                    localStorage.setItem('theme', newTheme);
                    updateThemeIcon(newTheme);
                });
            }

            // Global Reveal on Scroll Observer
            const revealCallback = (entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            };

            const revealObserver = new IntersectionObserver(revealCallback, {
                threshold: 0.15
            });

            document.querySelectorAll('.reveal-on-scroll').forEach(el => {
                revealObserver.observe(el);
            });
        });
    </script>

    <main class="container py-5 fade-in">
