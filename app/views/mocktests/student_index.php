<?php $pageTitle = 'Mock Tests'; require $viewPath . 'layouts/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1 fw-bold">Mock Tests</h2>
        <p class="text-muted mb-0">Skill-wise categorized tests. Each test: 10 questions, max score 100.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/mock-tests/history" class="btn btn-outline-primary fw-bold">
            <i class="bi bi-clock-history me-2"></i>Test History
        </a>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-sm">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<?php
$grouped = [];
foreach (($tests ?? []) as $t) {
    $key = ($t['skill_category'] ?? 'General') . '||' . ($t['skill_name'] ?? 'Skill');
    $grouped[$key][] = $t;
}
?>

<?php if (empty($tests)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-clipboard2-x fs-1 opacity-25"></i>
        <p class="small mb-0 mt-2">No mock tests available right now.</p>
    </div>
<?php else: ?>
    <?php foreach ($grouped as $key => $rows): ?>
        <?php
        [$cat, $skill] = explode('||', $key, 2);
        ?>
        <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
            <h6 class="text-primary fw-bold text-uppercase small l-spacing-1 mb-0">
                <?= htmlspecialchars($cat) ?> · <?= htmlspecialchars($skill) ?>
            </h6>
            <span class="small text-muted"><?= count($rows) ?> test(s)</span>
        </div>

        <div class="row g-3">
            <?php foreach ($rows as $t): ?>
                <?php
                $attempt = $attempts[(int)$t['id']] ?? null;
                $qCount = (int)($t['question_count'] ?? 0);
                ?>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-bold fs-5 text-dark-mode-light"><?= htmlspecialchars($t['title']) ?></div>
                                    <div class="small text-muted">
                                        By <?= htmlspecialchars($t['faculty_name'] ?? 'Faculty') ?>
                                    </div>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success">Max 100</span>
                            </div>

                            <?php if (!empty($t['description'])): ?>
                                <p class="text-muted small mb-3"><?= htmlspecialchars($t['description']) ?></p>
                            <?php else: ?>
                                <p class="text-muted small mb-3">Interview-oriented conceptual MCQs.</p>
                            <?php endif; ?>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-info bg-opacity-10 text-info"><?= $qCount ?>/10 questions</span>
                                <?php if ((int)($t['single_attempt'] ?? 1) === 1): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Single attempt</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Multiple attempts</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($attempt): ?>
                                <div class="alert alert-success border-0 small mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Attempted</strong>
                                            <div class="text-muted">Score: <?= (int)$attempt['score'] ?> / <?= (int)$attempt['max_score'] ?></div>
                                        </div>
                                        <div class="text-muted">
                                            <?= date('M d, Y', strtotime($attempt['attempted_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <a href="<?= BASE_URL ?>/mock-tests/history" class="btn btn-outline-primary w-100">
                                    View in History
                                </a>
                            <?php else: ?>
                                <?php if ($qCount !== 10): ?>
                                    <div class="alert alert-warning border-0 small mb-3">
                                        This test is not ready yet (needs exactly 10 questions).
                                    </div>
                                    <button class="btn btn-outline-secondary w-100" disabled>Not Available</button>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/mock-tests/attempt/<?= (int)$t['id'] ?>" class="btn btn-primary fw-bold w-100">
                                        <i class="bi bi-play-circle me-2"></i>Attempt Test
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require $viewPath . 'layouts/footer.php'; ?>

