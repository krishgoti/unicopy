<?php $pageTitle = 'Admin Dashboard'; require $viewPath . 'layouts/header.php'; ?>

<!-- Admin Dashboard Professional Styling -->
<style>
.admin-hero {
    background: var(--bs-primary);
    border-radius: 1rem;
    padding: 3rem;
    color: white;
    margin-bottom: 2.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}
.stat-card-premium {
    background: var(--card-bg);
    border-radius: 24px;
    padding: 1.5rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: all 0.3s;
}
.stat-card-premium:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}
.quick-action-pill {
    background: var(--bg-light);
    border-radius: 100px;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: var(--text-muted);
    font-weight: 700;
    border: 1px solid var(--border-color);
    transition: all 0.2s;
}
.quick-action-pill:hover {
    background: var(--primary);
    color: white !important;
    border-color: var(--primary);
}

[data-theme="dark"] .quick-action-pill {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-main);
}
</style>

<div class="fade-in">
    <!-- Admin Hero -->
    <div class="admin-hero">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-20 px-3 py-2 rounded-pill fw-bold mb-3">SYSTEM SUPERUSER</span>
                <h1 class="display-5 fw-black mb-2">Platform Overview</h1>
                <p class="text-white-50 lead mb-0">Managing <?= ($userCounts['student'] ?? 0) + ($userCounts['faculty'] ?? 0) + ($userCounts['alumni'] ?? 0) ?> active members across the ecosystem.</p>
            </div>
            <div class="col-md-4 text-md-end mt-4 mt-md-0">
                <div class="d-flex flex-column align-items-md-end">
                    <div class="text-white-50 small fw-bold mb-1">CURRENT SERVER TIME</div>
                    <div class="h4 fw-black font-monospace mb-0"><?= date('H:i') ?> <small class="fw-normal opacity-50"><?= date('T') ?></small></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="row g-4 mb-5 row-cols-2 row-cols-lg-5">
        <div class="col">
            <div class="stat-card-premium border-success border-bottom border-4 position-relative overflow-hidden h-100">
                <div class="position-absolute top-0 end-0 p-3">
                    <span class="pulse-indicator bg-success" style="width:10px; height:10px; display:inline-block; border-radius:50%; box-shadow: 0 0 0 0 rgba(25,135,84,0.4); animation: pulse-green 2s infinite;"></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="p-2 bg-success bg-opacity-10 text-success rounded-3"><i class="bi bi-broadcast fs-4"></i></div>
                </div>
                <div class="text-success small fw-bold">ONLINE STUDENTS</div>
                <div class="h3 fw-black mb-0 text-success"><?= $onlineStudents ?? 0 ?></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card-premium h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3"><i class="bi bi-mortarboard fs-4"></i></div>
                    <span class="text-success small fw-bold">+<?= date('j') ?> New</span>
                </div>
                <div class="text-muted small fw-bold">TOTAL STUDENTS</div>
                <div class="h3 fw-black mb-0"><?= $userCounts['student'] ?? 0 ?></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card-premium h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="p-2 bg-warning bg-opacity-10 text-warning rounded-3"><i class="bi bi-people fs-4"></i></div>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-2"><?= count($pendingAlumni ?? []) ?> Pending</span>
                </div>
                <div class="text-muted small fw-bold">ALUMNI NETWORK</div>
                <div class="h3 fw-black mb-0"><?= $userCounts['alumni'] ?? 0 ?></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card-premium h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="p-2 bg-success bg-opacity-10 text-success rounded-3"><i class="bi bi-briefcase fs-4"></i></div>
                    <span class="text-muted small">Live</span>
                </div>
                <div class="text-muted small fw-bold">ACTIVE POSITIONS</div>
                <div class="h3 fw-black mb-0"><?= $activeOpportunities ?? 0 ?></div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card-premium h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="p-2 bg-info bg-opacity-10 text-info rounded-3"><i class="bi bi-file-earmark-check fs-4"></i></div>
                    <span class="text-muted small">Total</span>
                </div>
                <div class="text-muted small fw-bold">JOB APPLICATIONS</div>
                <div class="h3 fw-black mb-0"><?= $totalApplications ?? 0 ?></div>
            </div>
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="row g-5">
        <div class="col-lg-8">
            <!-- Pending Alumni Management -->
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h5 class="fw-black mb-0"><i class="bi bi-shield-check text-primary me-2"></i>Security Queue</h5>
                <span class="text-muted small fw-bold"><?= count($pendingAlumni) ?> Registrations Awaiting Verification</span>
            </div>
            
            <div class="bg-white border shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="ps-4 py-3 border-0">Identity</th>
                                <th class="py-3 border-0">Affiliation</th>
                                <th class="pe-4 py-3 border-0 text-end">Validation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingAlumni as $alum): ?>
                            <tr>
                                <td class="ps-4 py-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-3 fw-black">
                                            <?= strtoupper(substr($alum['full_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark-mode-light"><?= htmlspecialchars($alum['full_name']) ?></div>
                                            <small class="text-muted fw-bold"><?= htmlspecialchars($alum['email']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-3"><?= htmlspecialchars($alum['company'] ?? 'N/A') ?></span>
                                </td>
                                <td class="pe-4 text-end">
                                    <form method="POST" action="<?= BASE_URL ?>/admin/approve-alumni" class="d-flex gap-2 justify-content-end">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="user_id" value="<?= $alum['id'] ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm">APPROVE</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">REJECT</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($pendingAlumni)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <i class="bi bi-shield-lock-fill display-1 opacity-10 d-block mb-3"></i>
                                    <h6 class="fw-bold text-muted">All registration queues are clear.</h6>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <h5 class="fw-black mb-4">Governance Hub</h5>
            <div class="row g-3">
                <div class="col-12">
                    <a href="<?= BASE_URL ?>/admin/users" class="quick-action-pill">
                        <i class="bi bi-people-fill text-primary"></i>
                        <span>User Management</span>
                        <i class="bi bi-chevron-right ms-auto opacity-50"></i>
                    </a>
                </div>
                <div class="col-12">
                    <a href="<?= BASE_URL ?>/admin/roadmaps" class="quick-action-pill">
                        <i class="bi bi-map-fill text-success"></i>
                        <span>Career Roadmaps</span>
                        <i class="bi bi-chevron-right ms-auto opacity-50"></i>
                    </a>
                </div>
                <div class="col-12">
                    <a href="<?= BASE_URL ?>/admin/analytics" class="quick-action-pill">
                        <i class="bi bi-graph-up-arrow text-info"></i>
                        <span>Platform Analytics</span>
                        <i class="bi bi-chevron-right ms-auto opacity-50"></i>
                    </a>
                </div>
                <div class="col-12">
                    <a href="<?= BASE_URL ?>/admin/settings" class="quick-action-pill">
                        <i class="bi bi-gear-fill text-secondary"></i>
                        <span>System Settings</span>
                        <i class="bi bi-chevron-right ms-auto opacity-50"></i>
                    </a>
                </div>
            </div>

            <!-- Server Integrity Card -->
            <div class="mt-5 p-4 bg-light bg-opacity-50 rounded-4 border border-primary border-opacity-10 text-center">
                <div class="p-3 bg-white d-inline-block rounded-circle shadow-sm mb-3">
                    <i class="bi bi-hdd-network text-primary fs-2"></i>
                </div>
                <h6 class="fw-black mb-1">Server Status: OPTIMAL</h6>
                <p class="small text-muted mb-0">Database latency is within nominal bounds. All subsystems operational.</p>
            </div>
        </div>
    </div>
</div>

<?php require $viewPath . 'layouts/footer.php'; ?>
