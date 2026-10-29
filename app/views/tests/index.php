<?php $pageTitle = 'Test Scores'; require $viewPath . 'layouts/header.php'; ?>

<h2 class="mb-4">Aptitude & Coding Test Score History</h2>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 fw-bold">My Test Scores</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="bg-light"><tr><th class="ps-4">Date</th><th>Type</th><th>Score</th><th>Remarks</th></tr></thead>
                        <tbody>
                            <?php foreach ($scores ?? [] as $s): ?>
                            <tr>
                                <td class="ps-4"><?= date('M d, Y', strtotime($s['test_date'])) ?></td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= ucfirst($s['test_type']) ?></span></td>
                                <td><strong class="text-dark-mode-light"><?= $s['score'] ?></strong> / <?= $s['max_score'] ?></td>
                                <td class="text-muted small fst-italic"><?= htmlspecialchars($s['remarks'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (empty($scores)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-clipboard-data fs-1 opacity-25"></i>
                    <p class="small mb-0 mt-2">No test scores recorded yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 fw-bold">Add Test Score</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/test-scores/add">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="mb-3">
                        <label class="form-label">Test Type</label>
                        <select name="test_type" class="form-select" required>
                            <option value="aptitude">Aptitude</option>
                            <option value="coding">Coding</option>
                            <option value="technical">Technical</option>
                            <option value="mock">Mock</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Score</label>
                            <input type="number" name="score" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Max Score</label>
                            <input type="number" name="max_score" class="form-control" value="100" step="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Test Date</label>
                        <input type="date" name="test_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="e.g. LeetCode Weekly">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Add Score</button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php require $viewPath . 'layouts/footer.php'; ?>
