<?php $pageTitle = 'Career Opportunities';
require $viewPath . 'layouts/header.php'; ?>
<div class="container-fluid pb-5">
    <div class="dash-hero p-5 rounded-4 shadow-lg mb-5 border-0 overflow-hidden reveal-on-scroll">
        <div class="row align-items-center position-relative z-1">
            <div class="col-md-7">
                <h1 class="fw-black text-white mb-2 display-5">Career Opportunities</h1>
                <p class="text-white-50 lead mb-4 opacity-75">Connect with the world's leading companies and launch your professional journey.</p>
                <div class="d-flex gap-3">
                    <div class="glass-pill px-3 py-2 d-flex align-items-center gap-2">
                        <i data-lucide="award" class="text-gradient" style="width: 18px; height: 18px;"></i>
                        <span class="text-white small fw-bold">CGPA: <?= $student['cgpa'] ?? 'N/A' ?></span>
                    </div>
                    <div class="glass-pill px-3 py-2 d-flex align-items-center gap-2">
                        <i data-lucide="building-2" class="text-gradient" style="width: 18px; height: 18px;"></i>
                        <span class="text-white small fw-bold"><?= $student['department'] ?? 'N/A' ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-5 text-md-end d-none d-md-block">
                <i data-lucide="briefcase" class="text-white opacity-10" style="width: 128px; height: 128px;"></i>
            </div>
        </div>
    </div>

    <!-- Smart Filters & Search -->
    <div class="glass-card p-4 mb-5 border-0 shadow-sm rounded-4 reveal-on-scroll">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0 ps-3"><i data-lucide="filter" class="text-muted" style="width: 18px; height: 18px;"></i></span>
                    <select name="type" class="form-select bg-transparent border-0 fw-bold text-main">
                        <option value="" class="bg-card">All Types</option>
                        <option value="placement" class="bg-card" <?= ($_GET['type'] ?? '') === 'placement' ? 'selected' : '' ?>>Placement</option>
                        <option value="internship" class="bg-card" <?= ($_GET['type'] ?? '') === 'internship' ? 'selected' : '' ?>>Internship</option>
                    </select>
                </div>
            </div>
            <div class="col-md-7">
                <div class="input-group glass-pill ps-2">
                    <span class="input-group-text bg-transparent border-0 ps-3"><i data-lucide="search" class="text-muted" style="width: 18px; height: 18px;"></i></span>
                    <input type="text" name="search" class="form-control bg-transparent border-0 py-2 text-main" placeholder="Search by company, role, or technology..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-gold w-100 rounded-pill fw-bold py-2 shadow-sm">SEARCH</button>
            </div>
        </form>
    </div>

    <!-- Live Activity Feed -->
    <div class="row mb-5">
        <div class="col-12">
            <h5 class="fw-black mb-3 small opacity-75 text-main d-flex align-items-center gap-2">
                <i data-lucide="radio" class="text-gradient" style="width: 18px; height: 18px;"></i> LIVE UPDATES
            </h5>
            <div class="glass-card p-3 border-0 shadow-sm rounded-4 overflow-hidden reveal-on-scroll">
                <div class="row g-3">
                    <?php if (empty($latestNotifications)): ?>
                        <div class="col-12 text-center py-2"><small class="text-muted">No new alerts at the moment.</small></div>
                    <?php else: ?>
                        <?php foreach (array_slice($latestNotifications, 0, 3) as $notif): ?>
                            <div class="col-md-4">
                                <div class="glass-pill p-3 border-start border-gold border-4 shadow-sm h-100 d-flex align-items-center">
                                    <div class="me-3 p-2 bg-main bg-opacity-10 rounded-3 text-gradient d-flex align-items-center justify-content-center">
                                        <i data-lucide="megaphone" style="width: 18px; height: 18px; transform: rotate(-20deg);"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="small fw-black text-uppercase text-gradient mb-1" style="font-size: 0.6rem;"><?= htmlspecialchars($notif['title']) ?></div>
                                        <div class="text-muted text-truncate" style="font-size: 0.75rem;"><?= htmlspecialchars($notif['message']) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Main Content: Job Listings or My Applications -->
        <div class="col-lg-8">
            <?php if ($isMyApplications ?? false): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-black mb-0"><i class="bi bi-file-earmark-check-fill text-success me-2"></i>My Submitted Applications</h5>
                    <span class="text-muted small fw-bold"><?= count($myApplications) ?> Submissions</span>
                </div>

                <?php foreach ($myApplications ?? [] as $app): ?>
                    <div class="glass-card p-4 mb-4 border-start border-gold border-5 shadow-sm transition-all hover-lift reveal-on-scroll">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center">
                                    <div class="p-3 glass-pill rounded-4 me-3 text-gradient d-flex align-items-center justify-content-center">
                                        <i data-lucide="building-2" style="width: 32px; height: 32px;"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-black mb-1 text-main"><?= htmlspecialchars($app['company_name']) ?></h5>
                                        <div class="small text-muted fw-bold text-uppercase"><?= htmlspecialchars($app['role']) ?> • <?= $app['type'] ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                <span class="badge bg-gold-gradient px-4 py-2 rounded-pill fw-bold small text-uppercase shadow-sm mb-2 d-inline-block text-white">
                                    <i class="bi bi-info-circle me-1"></i> <?= $app['status'] ?>
                                </span>
                                <div class="text-muted small fw-bold"><i class="bi bi-calendar-check me-1"></i> Applied on <?= date('M d, Y', strtotime($app['applied_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($myApplications)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted opacity-25 d-block mb-3"></i>
                        <h5 class="fw-black text-main">No applications yet</h5>
                        <p class="text-muted">Explore the job market and start applying!</p>
                        <a href="<?= BASE_URL ?>/opportunities" class="btn btn-gold rounded-pill px-5 fw-black mt-2 shadow">BROWSE JOBS</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-black mb-0"><i class="bi bi-stars text-warning me-2"></i>Job Market Activity</h5>
                    <span class="text-muted small fw-bold"><?= count($opportunities) ?> Positions Found</span>
                </div>

                <?php foreach ($opportunities ?? [] as $opp): ?>
                    <div class="glass-card p-0 mb-4 overflow-hidden border-0 shadow-sm transition-all hover-lift reveal-on-scroll">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="p-3 glass-pill rounded-4 me-3 text-gradient shadow-sm">
                                        <i class="bi bi-building fs-3"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-black mb-0 text-main"><?= htmlspecialchars($opp['company_name']) ?></h5>
                                        <div class="d-flex gap-2 mt-1">
                                            <span class="badge glass-pill text-muted small rounded-pill px-3"><?= strtoupper($opp['type']) ?></span>
                                            <?php if ($opp['package']): ?>
                                                <span class="badge bg-gold-gradient text-white small rounded-pill px-3"><i class="bi bi-cash-stack me-1"></i><?= $opp['package'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <?php if ($opp['match_score'] >= 70): ?>
                                        <span class="badge bg-gold-gradient text-white px-3 py-2 rounded-pill small fw-bold mb-2 d-block">
                                            <i class="bi bi-magic me-1"></i> RECOMMENDED (<?= $opp['match_score'] ?>%)
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($opp['is_eligible']): ?>
                                        <span class="badge bg-gold-gradient px-3 py-2 rounded-pill small fw-bold text-uppercase shadow-sm text-white">
                                            <i class="bi bi-check-circle-fill me-1"></i> ELIGIBLE
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-3 py-2 rounded-pill small fw-bold text-uppercase shadow-sm text-white" title="<?= implode(', ', $opp['reasons']) ?>">
                                            <i class="bi bi-x-circle-fill me-1"></i> NOT ELIGIBLE
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold text-main mb-2"><?= htmlspecialchars($opp['role']) ?></h6>
                                <p class="text-muted small mb-3"><?= nl2br(htmlspecialchars(substr($opp['description'] ?? '', 0, 200))) ?>...</p>

                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php if ($opp['required_skills']): ?>
                                        <?php foreach (explode(',', $opp['required_skills']) as $skill): ?>
                                            <span class="badge glass-pill text-muted fw-bold" style="font-size: 0.65rem;">#<?= trim($skill) ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="row g-2 text-muted small fw-semibold">
                                    <div class="col-md-4"><i class="bi bi-geo-alt me-1 text-gradient"></i> <?= htmlspecialchars($opp['location']) ?></div>
                                    <div class="col-md-4"><i class="bi bi-calendar-event me-1 text-gradient"></i> APPLY BY: <?= !empty($opp['apply_deadline']) ? date('M d, Y', strtotime($opp['apply_deadline'])) : 'Ongoing' ?></div>
                                </div>
                            </div>

                            <hr class="opacity-10">

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-danger small fw-bold">
                                    <?php if (!$opp['is_eligible']): ?>
                                        <i class="bi bi-info-circle me-1"></i> <?= $opp['reasons'][0] ?? 'Check criteria' ?>
                                    <?php endif; ?>
                                </div>

                                <?php if ($opp['application_status']): ?>
                                    <button class="btn btn-secondary rounded-pill px-4 btn-sm fw-bold disabled opacity-75 text-white">
                                        <i class="bi bi-check2-all me-1"></i> APPLIED (<?= strtoupper($opp['application_status']) ?>)
                                    </button>
                                <?php elseif ($opp['is_eligible']): ?>
                                    <button class="btn btn-gold rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="collapse" data-bs-target="#apply-<?= $opp['id'] ?>">
                                        APPLY NOW <i class="bi bi-arrow-right-short ms-1"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary rounded-pill px-4 btn-sm fw-bold disabled opacity-50" style="cursor: not-allowed;">
                                        NOT ELIGIBLE
                                    </button>
                                <?php endif; ?>
                            </div>

                            <!-- Apply Form Collapse -->
                            <div class="collapse mt-4" id="apply-<?= $opp['id'] ?>">
                                <div class="p-4 glass-card rounded-4 border border-primary border-opacity-10 bg-main">
                                    <form method="POST" action="<?= BASE_URL ?>/opportunities/apply" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="opportunity_id" value="<?= $opp['id'] ?>">

                                        <div class="row align-items-end g-3">
                                            <div class="col-md-9 text-start">
                                                <label class="form-label small fw-bold text-main"><i class="bi bi-file-earmark-pdf text-gradient me-2"></i>Upload Current Resume (PDF Only)</label>
                                                <input type="file" name="resume" class="form-control rounded-4 bg-glass border-0 text-main" required accept=".pdf">
                                            </div>
                                            <div class="col-md-3">
                                                <button type="submit" class="btn btn-gold w-100 rounded-pill fw-bold py-2">SUBMIT APP</button>
                                            </div>
                                        </div>
                                        <p class="small text-muted mt-2 mb-0"><i class="bi bi-shield-lock me-1"></i> Your profile data will be shared with <?= htmlspecialchars($opp['company_name']) ?>'s recruitment team.</p>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($opportunities)): ?>
                    <div class="text-center py-5">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/searching-no-result-found-5100342-4256259.png" style="width: 200px; opacity: 0.6;">
                        <h5 class="fw-bold mt-4">No vacancies matching your search</h5>
                        <p class="text-muted">Try removing filters or checking back later.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar: Application Tracking -->
        <div class="col-lg-4">
            <h5 class="fw-black mb-4 text-main">Application Tracker</h5>
            <div class="glass-card p-4 border-0 shadow-sm rounded-4 reveal-on-scroll">
                <?php if (empty($myApplications)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted opacity-25"></i>
                        <p class="small text-muted mt-2">You haven't applied to any roles yet. Start your journey today!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($myApplications as $app): ?>
                        <div class="p-3 glass-pill rounded-4 mb-3 border transition-all hover-translate-x">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-black small text-gradient mb-0 d-block"><?= htmlspecialchars($app['company_name']) ?></span>
                                <span class="badge bg-gold-gradient px-2 py-1 rounded-pill text-white" style="font-size: 0.6rem;">
                                    <?= strtoupper($app['status']) ?>
                                </span>
                            </div>
                            <div class="small fw-bold mb-1 text-main" style="font-size: 0.75rem;"><?= htmlspecialchars($app['role']) ?></div>
                            <div class="text-muted" style="font-size: 0.65rem;"><i class="bi bi-clock me-1 text-gradient"></i> Applied <?= date('M d, H:i', strtotime($app['applied_at'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Career Tip Card -->
            <div class="dash-hero mt-5 p-4 rounded-4 shadow-lg text-center border-0 overflow-hidden reveal-on-scroll">
                <i class="bi bi-lightbulb-fill text-gradient display-4 mb-3 d-block"></i>
                <h6 class="fw-bold text-white mb-2">Resume Pro-Tip</h6>
                <p class="small text-white-50 mb-0 opacity-75">Tailor your resume skills to match the job description for a higher match score!</p>
            </div>
        </div>
    </div>
</div>

<?php require $viewPath . 'layouts/footer.php'; ?>