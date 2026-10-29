<?php $pageTitle = 'Student Dashboard'; require $viewPath . 'layouts/header.php'; ?>

<style>
/* ── Glossy Dashboard Aesthetics ── */
.dash-hero {
    background: var(--hero-bg-gradient);
    border-radius: 2rem;
    padding: 3rem;
    color: white;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(var(--dark-rgb), 0.3);
}

.stat-card-premium {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-top: 4px solid transparent;
    border-image: var(--gold-gradient) 1;
    border-radius: var(--radius);
    transition: var(--transition);
    position: relative;
}
.stat-card-premium:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.text-gradient {
    background: var(--gold-gradient);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.recommendation-alert {
    background: rgba(var(--primary-rgb), 0.05);
    border-left: 4px solid var(--primary);
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.task-item-professional {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s;
}
.task-item-professional:hover {
    border-color: var(--primary);
    background: rgba(var(--primary-rgb), 0.05);
}
</style>

<div class="fade-in">
    <!-- Premium Glossy Hero -->
    <div class="dash-hero">
        <div class="row align-items-center position-relative z-1">
            <div class="col-lg-7">
                <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25 px-3 py-2 rounded-pill mb-3 shadow-sm d-inline-flex align-items-center gap-2">
                    <i data-lucide="sparkles" style="width: 14px; height: 14px;"></i> Student Premium Dashboard
                </span>
                <h1 class="display-5 fw-black mb-2 text-white" style="letter-spacing: -1px;">Welcome back, <span class="text-gradient-gold"><?= explode(' ', htmlspecialchars($profile['full_name'] ?? 'Student'))[0] ?>!</span></h1>
                <p class="text-white opacity-75 lead mb-4 fw-normal">Track your career progress, complete assessments, and explore top-tier opportunities.</p>
                
                <div class="d-flex flex-wrap gap-4 align-items-center">
                    <span class="badge bg-<?= $statusColor ?> px-4 py-2 fs-6 shadow-lg rounded-pill border border-white border-opacity-50 d-inline-flex align-items-center gap-2">
                        STATUS: <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i> <?= strtoupper($eligibility) ?>
                    </span>
                    <div class="ms-md-2">
                        <span class="text-white opacity-75 small d-block mb-1 text-uppercase fw-bold letter-spacing-1">Readiness Score</span>
                        <h3 class="fw-black mb-0 display-6 text-white"><?= $overallProgress ?>%</h3>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5 ps-lg-5 mt-4 mt-lg-0">
                <div class="bg-white bg-opacity-10 p-4 rounded-4 border border-white border-opacity-25 shadow-sm">
                    <div class="d-flex justify-content-between mb-2 text-white small fw-bold">
                        <span>LEARNING</span>
                        <span>INTERVIEW READY</span>
                    </div>
                    <div class="progress bg-white bg-opacity-20 mb-3" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: <?= $overallProgress ?>%;"></div>
                    </div>
                    <p class="text-white-50 small mb-0">You're in the top <span class="text-white fw-bold">15%</span> of your batch!</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!Auth::isVerified()): ?>
    <!-- Verification Alert -->
    <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 mb-5 d-flex align-items-center justify-content-between fade-in">
        <div class="d-flex align-items-center">
            <div class="bg-warning bg-opacity-20 text-warning rounded-circle p-3 me-3 d-flex align-items-center justify-content-center">
                <i data-lucide="mail-warning" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <h6 class="fw-black mb-1">Verify Your Email Address</h6>
                <p class="small mb-0 opacity-75 fw-semibold">Please check your inbox (<?= htmlspecialchars($_SESSION['user']['email']) ?>) and click the verification link.</p>
            </div>
        </div>
        <div class="ms-3">
            <button class="btn btn-warning btn-sm rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2" onclick="location.reload()">
                I've Verified <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats Grid with Midnight Gold Gradient -->
    <div class="row g-4 mb-5">
        <div class="col-6 col-lg-3 text-center">
            <div class="glass-card stat-card-premium p-4 overflow-hidden reveal-on-scroll">
                <div class="mb-3 d-flex justify-content-center">
                    <div class="p-3 bg-main bg-opacity-10 rounded-4 text-gradient shadow-sm">
                        <i data-lucide="line-chart" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
                <div class="small text-muted fw-bold">SKILL READINESS</div>
                <div class="h3 fw-black mb-0 text-main"><?= $skillReadiness ?>%</div>
            </div>
        </div>
        <div class="col-6 col-lg-3 text-center">
            <div class="glass-card stat-card-premium p-4 overflow-hidden reveal-on-scroll">
                <div class="mb-3 d-flex justify-content-center">
                    <div class="p-3 bg-main bg-opacity-10 rounded-4 text-gradient shadow-sm">
                        <i data-lucide="briefcase" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
                <div class="small text-muted fw-bold">APPLICATIONS</div>
                <div class="h3 fw-black mb-0 text-main"><?= $applicationCount ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3 text-center">
            <div class="glass-card stat-card-premium p-4 overflow-hidden reveal-on-scroll">
                <div class="mb-3 d-flex justify-content-center">
                    <div class="p-3 bg-main bg-opacity-10 rounded-4 text-gradient shadow-sm">
                        <i data-lucide="message-square" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
                <div class="small text-muted fw-bold">PENDING MENTORS</div>
                <div class="h3 fw-black mb-0 text-main"><?= $pendingMentorship ?></div>
            </div>
        </div>
        <div class="col-6 col-lg-3 text-center">
            <div class="glass-card stat-card-premium p-4 overflow-hidden reveal-on-scroll">
                <div class="mb-3 d-flex justify-content-center">
                    <div class="p-3 bg-main bg-opacity-10 rounded-4 text-gradient shadow-sm">
                        <i data-lucide="clipboard-list" style="width: 24px; height: 24px;"></i>
                    </div>
                </div>
                <div class="small text-muted fw-bold">PENDING TASKS</div>
                <div class="h3 fw-black mb-0 text-main"><?= count($pendingTasks) ?></div>
            </div>
        </div>
    </div>
    </div>

    <!-- Daily Smart AI Suggestions -->
    <div class="row mb-5">
        <div class="col-12">
            <h5 class="section-title fw-bold mb-4 d-flex align-items-center gap-2">
                <i data-lucide="brain-circuit" class="text-primary"></i> What you do today?
            </h5>
            <div class="row g-4">
                <?php foreach($dailySuggestions as $ds): ?>
                <div class="col-md-6">
                    <div class="glass-card p-4 border-start border-<?= htmlspecialchars($ds['color'] ?? 'primary') ?> border-4 shadow-sm" style="border-radius: 20px;">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-<?= htmlspecialchars($ds['color'] ?? 'primary') ?> bg-opacity-10 text-<?= htmlspecialchars($ds['color'] ?? 'primary') ?> rounded-circle me-3 d-flex align-items-center justify-content-center">
                                <i data-lucide="<?= htmlspecialchars($ds['icon'] ?? 'zap') ?>" style="width: 24px; height: 24px;"></i>
                            </div>
                            <div>
                                <h6 class="fw-black mb-1"><?= htmlspecialchars($ds['title']) ?></h6>
                                <p class="text-muted small mb-0 fw-semibold"><?= htmlspecialchars($ds['desc']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Left: Intelligence Section -->
        <div class="col-lg-8">
            <div class="row g-4">
                <!-- Task List Premium -->
                <div class="col-md-6">
                    <h5 class="section-title fw-bold mb-4 d-flex align-items-center gap-2">
                        <i data-lucide="layers" class="text-primary" style="width: 20px; height: 20px;"></i> Priority Tasks
                    </h5>
                    <div class="glass-card p-4">
                        <?php if (empty($pendingTasks)): ?>
                            <div class="text-center py-5">
                                <i data-lucide="shield-check" class="text-success mb-3" style="width: 48px; height: 48px;"></i>
                                <p class="fw-bold mb-0">Excellent job!</p>
                                <span class="small text-muted">You've completed all priority tasks.</span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($pendingTasks as $task): ?>
                                <a href="<?= $task['link'] ?>" class="text-decoration-none text-dark">
                                    <div class="task-item-professional d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center">
                                            <i data-lucide="chevron-right" style="width: 18px; height: 18px;"></i>
                                        </div>
                                        <span class="small fw-black"><?= htmlspecialchars($task['title']) ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Insights & Recommendations -->
                <div class="col-md-6">
                    <h5 class="section-title fw-bold mb-4 d-flex align-items-center gap-2">
                        <i data-lucide="cpu" class="text-primary" style="width: 20px; height: 20px;"></i> Smart Insights
                    </h5>
                    <div class="glass-card p-4 h-100">
                        <?php if (empty($recommendations)): ?>
                            <div class="p-3 text-center">
                                <i data-lucide="trending-up" class="text-primary opacity-50 mb-3" style="width: 48px; height: 48px;"></i>
                                <p class="small text-muted">The AI has observed your trajectory and it's positive. Consistency is key.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recommendations as $rec): ?>
                                <div class="recommendation-alert d-flex gap-3">
                                    <div class="text-primary"><i data-lucide="target" style="width: 18px; height: 18px;"></i></div>
                                    <p class="mb-0 small fw-semibold text-dark"><?= htmlspecialchars($rec) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Job Opportunities -->
                <div class="col-12 mt-5 pt-3">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <h5 class="section-title fw-bold mb-0 d-flex align-items-center gap-2">
                            <i data-lucide="sparkles" class="text-primary" style="width: 20px; height: 20px;"></i> Career Opportunities
                        </h5>
                        <a href="<?= BASE_URL ?>/opportunities" class="btn btn-sm btn-outline-primary px-4 rounded-pill fw-bold d-flex align-items-center gap-2">
                            GO TO MARKET <i data-lucide="arrow-up-right" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                    <div class="glass-card p-1 overflow-hidden">
                        <?php foreach ($opportunities as $opp): ?>
                            <a href="<?= BASE_URL ?>/opportunities" class="list-group-item list-group-item-action border-0 py-4 px-4 m-0 transition-all border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="p-3 bg-primary bg-opacity-10 rounded-4 me-3 text-primary d-flex align-items-center justify-content-center">
                                            <i data-lucide="building-2" style="width: 24px; height: 24px;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-black h6 mb-0"><?= htmlspecialchars($opp['company_name']) ?></div>
                                            <div class="small text-muted fw-bold"><?= htmlspecialchars($opp['role']) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary bg-opacity-20 text-primary-dark border border-primary border-opacity-25 rounded-pill px-4 py-2 small fw-bold mb-1 d-inline-block">
                                            <?= strtoupper($opp['type']) ?>
                                        </span>
                                        <div class="small text-muted" style="font-size: 0.7rem;">POSTED RECENTLY</div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Live Updates / Notifications (Moved here) -->
                <div class="col-12 mt-5 pt-4">
                    <h5 class="section-title fw-bold mb-4 d-flex align-items-center gap-2">
                        <i data-lucide="activity" class="text-primary" style="width: 20px; height: 20px;"></i> Live Activity Feed
                    </h5>
                    <div class="glass-card p-4">
                        <div class="row g-4">
                            <?php if (empty($latestNotifications)): ?>
                                <div class="col-12 text-center py-4">
                                    <p class="small text-muted mb-0">The feed is quiet. We'll alert you when something happens.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($latestNotifications as $notif): ?>
                                    <div class="col-md-4">
                                        <div class="h-100 p-3 bg-light bg-opacity-50 rounded-4 border-start border-primary border-4 shadow-sm transition-all hover-translate-y">
                                            <a href="<?= htmlspecialchars($notif['link'] ?? '#') ?>" class="text-decoration-none d-flex align-items-center">
                                                <div class="action-icon-box bg-card shadow-sm me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; border-radius: 50%;">
                                                    <i data-lucide="zap" style="width: 16px; height: 16px;" class="text-primary"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <div class="small fw-black text-truncate text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;"><?= htmlspecialchars($notif['title']) ?></div>
                                                    <div class="text-muted text-wrap" style="font-size: 0.7rem; line-height: 1.1;"><?= htmlspecialchars($notif['message']) ?></div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Micro Utilities -->
        <div class="col-lg-4">
            <!-- Priority Quick Links Grid -->
            <h5 class="section-title fw-bold mb-4 d-flex align-items-center gap-2">
                <i data-lucide="compass" class="text-primary" style="width: 20px; height: 20px;"></i> Navigation Hub
            </h5>
            <div class="d-flex flex-column gap-3 mb-5">
                <a href="<?= BASE_URL ?>/mock-tests/list" class="glass-card text-decoration-none p-3 d-flex align-items-center transition-all hover-translate-x border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="text-primary me-3">
                        <i data-lucide="file-question" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: var(--text-main);">Mock Tests</h6>
                        <span class="small text-muted" style="font-size: 0.75rem;">Practice & Assess</span>
                    </div>
                    <i data-lucide="chevron-right" class="ms-auto text-muted opacity-50" style="width: 18px; height: 18px;"></i>
                </a>

                <a href="<?= BASE_URL ?>/career-roadmap" class="glass-card text-decoration-none p-3 d-flex align-items-center transition-all hover-translate-x border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="text-primary me-3">
                        <i data-lucide="map" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: var(--text-main);">Career Pathway</h6>
                        <span class="small text-muted" style="font-size: 0.75rem;">Track your long-term goals</span>
                    </div>
                    <i data-lucide="chevron-right" class="ms-auto text-muted opacity-50" style="width: 18px; height: 18px;"></i>
                </a>

                <a href="<?= BASE_URL ?>/contests" class="glass-card text-decoration-none p-3 d-flex align-items-center transition-all hover-translate-x border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="text-primary me-3">
                        <i data-lucide="award" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: var(--text-main);">Contest Arena</h6>
                        <span class="small text-muted" style="font-size: 0.75rem;">Compete with peers</span>
                    </div>
                    <i data-lucide="chevron-right" class="ms-auto text-muted opacity-50" style="width: 18px; height: 18px;"></i>
                </a>

                <a href="<?= BASE_URL ?>/resume" class="glass-card text-decoration-none p-3 d-flex align-items-center transition-all hover-translate-x border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="text-primary me-3">
                        <i data-lucide="file-user" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: var(--text-main);">Resume Builder</h6>
                        <span class="small text-muted" style="font-size: 0.75rem;">Craft & get reviewed</span>
                    </div>
                    <i data-lucide="chevron-right" class="ms-auto text-muted opacity-50" style="width: 18px; height: 18px;"></i>
                </a>

                <a href="<?= BASE_URL ?>/mentorship" class="glass-card text-decoration-none p-3 d-flex align-items-center transition-all hover-translate-x border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="text-primary me-3">
                        <i data-lucide="users" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: var(--text-main);">Mentorship</h6>
                        <span class="small text-muted" style="font-size: 0.75rem;">Connect with experts</span>
                    </div>
                    <i data-lucide="chevron-right" class="ms-auto text-muted opacity-50" style="width: 18px; height: 18px;"></i>
                </a>

                <a href="<?= BASE_URL ?>/projects" class="glass-card text-decoration-none p-3 d-flex align-items-center transition-all hover-translate-x border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="text-primary me-3">
                        <i data-lucide="layout-dashboard" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" style="color: var(--text-main);">Projects</h6>
                        <span class="small text-muted" style="font-size: 0.75rem;">Showcase your portfolio</span>
                    </div>
                    <i data-lucide="chevron-right" class="ms-auto text-muted opacity-50" style="width: 18px; height: 18px;"></i>
                </a>
                
                <a href="<?= BASE_URL ?>/dashboard/profile" class="glass-stat-card text-decoration-none p-3 d-flex align-items-center transition-all hover-translate-x border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="text-primary me-3">
                        <i data-lucide="user-cog" style="width: 24px; height: 24px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark-mode-light" style="color: var(--text-main);">Edit Profile</h6>
                        <span class="small text-muted" style="font-size: 0.75rem;">Update your details</span>
                    </div>
                    <i data-lucide="chevron-right" class="ms-auto text-muted opacity-50" style="width: 18px; height: 18px;"></i>
                </a>
            </div>

            <!-- Promotion Card -->
            <div class="glass-card p-4 rounded-4 shadow-sm text-center border-0 mt-2 hover-lift reveal-on-scroll">
                <i data-lucide="rocket" class="text-gradient-gold mb-3 d-block mx-auto" style="width: 64px; height: 64px;"></i>
                <h6 class="fw-black mb-2 text-main">Campus Leaderboard</h6>
                <p class="small text-muted mb-3 fw-semibold">Join the arena and compete with top students across campus.</p>
                <a href="<?= BASE_URL ?>/contests/leaderboard" class="btn btn-gold rounded-pill py-2 px-4 shadow-sm w-100 fw-bold">CHECK RANKINGS</a>
            </div>
        </div>
    </div>

<?php require $viewPath . 'layouts/footer.php'; ?>

