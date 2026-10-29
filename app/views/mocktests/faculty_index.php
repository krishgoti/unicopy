<?php $pageTitle = 'My Mock Tests'; require $viewPath . 'layouts/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1 fw-bold">Mock Tests</h2>
        <p class="text-muted mb-0">Create skill-based interview mock tests (10 questions, 10 points each).</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/mock-tests/create" class="btn btn-primary fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Create Mock Test
        </a>
        <a href="<?= BASE_URL ?>/mock-tests/faculty/results" class="btn btn-outline-secondary">
            <i class="bi bi-people me-2"></i>View Student List
        </a>
        <a href="<?= BASE_URL ?>/mock-tests/faculty/results" class="btn btn-outline-primary">
            <i class="bi bi-clock-history me-2"></i>History
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
        <h5 class="mb-0 fw-bold">My Created Tests</h5>
        <span class="badge bg-primary bg-opacity-10 text-primary"><?= count($tests ?? []) ?> total</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="bg-light">
                <tr>
                    <th class="ps-4">Test</th>
                    <th>Skill</th>
                    <th>Questions</th>
                    <th>Duration</th>
                    <th>Status / Timer</th>
                    <th>Created</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($tests ?? []) as $t): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark-mode-light"><?= htmlspecialchars($t['title']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($t['description'] ?? '-') ?></div>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                <?= htmlspecialchars($t['skill_name'] ?? '-') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info">
                                <?= (int)($t['question_count'] ?? 0) ?>/10
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <?= (int)($t['duration_mins'] ?? 0) ?> min
                            </span>
                        </td>
                        <td>
                            <?php if ((int)($t['is_active'] ?? 0) === 1): ?>
                                <span class="badge bg-success bg-opacity-10 text-success mb-1">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary mb-1">Inactive</span>
                            <?php endif; ?>

                            <?php if ((int)($t['duration_mins'] ?? 0) > 0): ?>
                                <?php if ($t['started_at']): ?>
                                    <div class="small text-success fw-bold">
                                        <i class="bi bi-play-circle me-1"></i>Started
                                    </div>
                                    <div class="x-small text-muted"><?= date('H:i', strtotime($t['started_at'])) ?></div>
                                <?php else: ?>
                                    <div class="small text-warning fw-bold">
                                        <i class="bi bi-pause-circle me-1"></i>Waiting
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="small text-muted"><?= date('M d, Y', strtotime($t['created_at'])) ?></div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <?php if ((int)($t['duration_mins'] ?? 0) > 0 && !$t['started_at']): ?>
                                    <a href="<?= BASE_URL ?>/mock-tests/start/<?= (int)$t['id'] ?>" class="btn btn-sm btn-success" title="Start Test Session" onclick="return confirm('Start this test? Students can attempt it for the next <?= (int)$t['duration_mins'] ?> minutes.')">
                                        <i class="bi bi-play-fill"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/mock-tests/edit/<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Test">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/mock-tests/delete/<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete Test" onclick="return confirm('Are you sure you want to delete this mock test? This action cannot be undone.')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($tests)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-clipboard2-plus fs-1 opacity-25"></i>
                <p class="small mb-0 mt-2">No mock tests created yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require $viewPath . 'layouts/footer.php'; ?>

