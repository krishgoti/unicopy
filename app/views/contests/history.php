<?php $pageTitle = 'Past Contests History'; require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <a href="<?= BASE_URL ?>/contests" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0">Contest History</h2>
                <p class="text-muted mb-0">Review past weekly challenges and leaderboards.</p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($contests)): ?>
    <div class="text-center py-5 bg-light rounded-4 border-0">
        <div class="display-1 text-muted opacity-50 mb-3"><i class="bi bi-calendar-x"></i></div>
        <h4 class="text-secondary fw-bold">No past contests found</h4>
        <p class="text-muted">Once a weekly contest ends, it will appear here in the history.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($contests as $contest): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-1 rounded-pill small">Completed</span>
                        <small class="text-muted fw-bold"><?= $contest['week_key'] ?></small>
                    </div>
                    
                    <h5 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($contest['title']) ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($contest['description']) ?></p>
                    
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row text-center g-0">
                            <div class="col-6 border-end">
                                <small class="text-muted d-block small">Questions</small>
                                <span class="fw-bold"><?= $contest['question_count'] ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block small">Faculty</small>
                                <span class="fw-bold text-truncate d-block px-1 small"><?= htmlspecialchars($contest['faculty_name']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <a href="<?= BASE_URL ?>/contests/leaderboard/<?= $contest['id'] ?>" class="btn btn-outline-primary fw-bold rounded-3">
                            <i class="bi bi-bar-chart-line me-2"></i> View Leaderboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
