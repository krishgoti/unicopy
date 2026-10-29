<?php $pageTitle = 'Alumni Dashboard'; require $viewPath . 'layouts/header.php'; ?>

<div class="container py-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Alumni Dashboard</h2>
            <p class="text-muted">Connecting you with your alma mater and students.</p>
        </div>
    </div>

    <?php if (isset($pending) || isset($_GET['pending'])): ?>
    <div class="alert alert-warning shadow-sm border-0 d-flex align-items-center mb-4">
        <i class="bi bi-clock-history me-3 fs-3 text-warning"></i>
        <div>
            <h5 class="alert-heading fw-bold mb-1">Account Pending Approval</h5>
            <p class="mb-0 opacity-75">Your alumni registration is pending admin approval. You will receive valid access once approved.</p>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> shadow-sm border-0 d-flex align-items-center mb-4">
        <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle' : 'check-circle' ?>-fill me-2 fs-5"></i>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Pending Mentorships</p>
                        <h3 class="fw-bold mb-0"><?= $pendingMentorship ?? 0 ?></h3>
                    </div>
                    <span class="bg-warning bg-opacity-10 text-warning rounded p-2"><i class="bi bi-people"></i></span>
                </div>
                <a href="<?= BASE_URL ?>/mentorship" class="small text-decoration-none text-muted stretched-link">View & respond</a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Shared Experiences</p>
                        <h3 class="fw-bold mb-0"><?= $experiencesPosted ?? 0 ?></h3>
                    </div>
                    <span class="bg-success bg-opacity-10 text-success rounded p-2"><i class="bi bi-chat-quote"></i></span>
                </div>
                <a href="<?= BASE_URL ?>/interview-experiences" class="small text-decoration-none text-muted stretched-link">Share more</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                            <i class="bi bi-stars fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">Your Impact</h5>
                            <p class="text-muted mb-0">As an alumnus, you play a crucial role in guiding current students.</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center h-100">
                                <i class="bi bi-lightbulb text-primary fs-2 mb-2"></i>
                                <h6 class="fw-bold">Mentorship</h6>
                                <p class="small text-muted mb-0">Guide students with career advice and industry insights.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center h-100">
                                <i class="bi bi-chat-left-text text-success fs-2 mb-2"></i>
                                <h6 class="fw-bold">Interviews</h6>
                                <p class="small text-muted mb-0">Share your interview journey to help others prepare.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center h-100">
                                <i class="bi bi-compass text-warning fs-2 mb-2"></i>
                                <h6 class="fw-bold">Guidance</h6>
                                <p class="small text-muted mb-0">Provide guidance based on your professional experience.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top text-center">
                        <a href="<?= BASE_URL ?>/mentorship" class="btn btn-primary me-2 shadow-sm"><i class="bi bi-people me-2"></i>View Requests</a>
                        <a href="<?= BASE_URL ?>/interview-experiences" class="btn btn-outline-primary"><i class="bi bi-chat-quote me-2"></i>Share Experience</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0 fw-bold">Quick Actions</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/mentorship" class="btn btn-outline-primary text-start"><i class="bi bi-people me-2"></i>Mentorship</a>
                        <a href="<?= BASE_URL ?>/interview-experiences" class="btn btn-outline-primary text-start"><i class="bi bi-chat-quote me-2"></i>Interview Experiences</a>
                        <a href="<?= BASE_URL ?>/opportunities" class="btn btn-outline-primary text-start"><i class="bi bi-briefcase me-2"></i>Opportunities</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require $viewPath . 'layouts/footer.php'; ?>
