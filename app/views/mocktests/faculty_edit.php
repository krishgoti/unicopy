<?php $pageTitle = 'Edit Mock Test'; require $viewPath . 'layouts/header.php'; ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= BASE_URL ?>/mock-tests/faculty" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="fw-bold mb-1">Edit Mock Test</h2>
        <p class="text-muted mb-0">Modify your 10 interview-oriented MCQs.</p>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-sm mb-4">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/mock-tests/edit/<?= (int)$test['id'] ?>" method="POST" id="mockTestForm">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4 border-bottom pb-2">Test Details</h5>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Title *</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. DSA Conceptual Mock Test" value="<?= htmlspecialchars($test['title']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Skill *</label>
                    <select name="skill_id" class="form-select" required>
                        <option value="">Select skill</option>
                        <?php
                        $byCat = [];
                        foreach (($skills ?? []) as $s) {
                            $byCat[$s['category'] ?? 'General'][] = $s;
                        }
                        ?>
                        <?php foreach ($byCat as $cat => $items): ?>
                            <optgroup label="<?= htmlspecialchars(ucfirst((string)$cat)) ?>">
                                <?php foreach ($items as $s): ?>
                                    <option value="<?= (int)$s['id'] ?>" <?= (int)$test['skill_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Short instructions for students (optional)."><?= htmlspecialchars($test['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="isActive" name="is_active" <?= (int)($test['is_active'] ?? 0) === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="isActive">Active (visible to students)</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="singleAttempt" name="single_attempt" <?= (int)($test['single_attempt'] ?? 1) === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="singleAttempt">Single attempt only</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Duration (minutes)</label>
                    <input type="number" name="duration_mins" class="form-control" placeholder="e.g. 10 (0 for no limit)" min="0" value="<?= (int)($test['duration_mins'] ?? 0) ?>">
                    <div class="form-text text-muted small">0 means no time limit.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Questions (10)</h5>
                <span class="badge bg-success bg-opacity-10 text-success">Each correct: +10</span>
            </div>
            <div class="alert alert-info border-0 small mb-4">
                <i class="bi bi-info-circle me-1"></i>
                Tip: Keep them conceptual/interview-oriented (no coding editor needed).
            </div>

            <?php for ($i = 0; $i < 10; $i++): 
                $q = $questions[$i] ?? null;
            ?>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold text-primary">Question #<?= $i + 1 ?></div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">10 points</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Question Text *</label>
                        <textarea name="questions[<?= $i ?>][text]" class="form-control" rows="2" required><?= htmlspecialchars($q['question_text'] ?? '') ?></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Option A *</label>
                            <input type="text" name="questions[<?= $i ?>][a]" class="form-control" value="<?= htmlspecialchars($q['option_a'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Option B *</label>
                            <input type="text" name="questions[<?= $i ?>][b]" class="form-control" value="<?= htmlspecialchars($q['option_b'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Option C *</label>
                            <input type="text" name="questions[<?= $i ?>][c]" class="form-control" value="<?= htmlspecialchars($q['option_c'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Option D *</label>
                            <input type="text" name="questions[<?= $i ?>][d]" class="form-control" value="<?= htmlspecialchars($q['option_d'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Correct Option *</label>
                            <select name="questions[<?= $i ?>][correct]" class="form-select" required>
                                <option value="A" <?= ($q['correct_option'] ?? '') === 'A' ? 'selected' : '' ?>>A</option>
                                <option value="B" <?= ($q['correct_option'] ?? '') === 'B' ? 'selected' : '' ?>>B</option>
                                <option value="C" <?= ($q['correct_option'] ?? '') === 'C' ? 'selected' : '' ?>>C</option>
                                <option value="D" <?= ($q['correct_option'] ?? '') === 'D' ? 'selected' : '' ?>>D</option>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <a href="<?= BASE_URL ?>/mock-tests/faculty" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary fw-bold ms-auto px-5">
            <i class="bi bi-check2-circle me-2"></i>Update Test
        </button>
    </div>
</form>

<?php require $viewPath . 'layouts/footer.php'; ?>
