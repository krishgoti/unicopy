<?php $pageTitle = 'Student List & Results'; require $viewPath . 'layouts/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1 fw-bold">Student List & Results</h2>
        <p class="text-muted mb-0">All attempts on mock tests created by you.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/mock-tests/faculty" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>My Tests
        </a>
        <a href="<?= BASE_URL ?>/mock-tests/create" class="btn btn-primary fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Create Test
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
        <span class="badge bg-primary bg-opacity-10 text-primary"><?= count($rows ?? []) ?> total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="bg-light">
                <tr>
                    <th class="ps-4">Student</th>
                    <th>Test</th>
                    <th>Skill</th>
                    <th>Score</th>
                    <th class="text-end pe-4">Date</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($rows ?? []) as $r): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark-mode-light"><?= htmlspecialchars($r['student_name'] ?? '-') ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($r['department'] ?? '-') ?></div>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($r['test_title'] ?? '-') ?></div>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= htmlspecialchars($r['skill_name'] ?? '-') ?></span>
                        </td>
                        <td>
                            <strong class="text-dark-mode-light"><?= (int)($r['score'] ?? 0) ?></strong>
                            <span class="text-muted">/ <?= (int)($r['max_score'] ?? 100) ?></span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="small text-muted"><?= date('M d, Y', strtotime($r['attempted_at'])) ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($rows)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 opacity-25"></i>
                <p class="small mb-0 mt-2">No student has attempted your mock tests yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require $viewPath . 'layouts/footer.php'; ?>

