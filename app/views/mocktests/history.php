<?php $pageTitle = 'Test History'; require $viewPath . 'layouts/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1 fw-bold">Test History</h2>
        <p class="text-muted mb-0">Your mock test attempts with skill-wise performance tracking.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/mock-tests/list" class="btn btn-primary fw-bold">
            <i class="bi bi-list-check me-2"></i>Available Tests
        </a>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-sm">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Attempts</h5>
        <span class="badge bg-primary bg-opacity-10 text-primary"><?= count($attempts ?? []) ?> total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="bg-light">
                <tr>
                    <th class="ps-4">Test</th>
                    <th>Skill</th>
                    <th>Score</th>
                    <th class="text-end pe-4">Attempted On</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($attempts ?? []) as $a): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark-mode-light"><?= htmlspecialchars($a['test_title'] ?? '-') ?></div>
                            <div class="small text-muted">Mock Test</div>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                <?= htmlspecialchars($a['skill_name'] ?? '-') ?>
                            </span>
                        </td>
                        <td>
                            <strong class="text-dark-mode-light"><?= (int)($a['score'] ?? 0) ?></strong>
                            <span class="text-muted">/ <?= (int)($a['max_score'] ?? 100) ?></span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="small text-muted"><?= date('M d, Y', strtotime($a['attempted_at'])) ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($attempts)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-clock-history fs-1 opacity-25"></i>
                <p class="small mb-0 mt-2">No mock test attempts yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require $viewPath . 'layouts/footer.php'; ?>

