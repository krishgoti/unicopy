<?php $pageTitle = 'Career Roadmap'; require $viewPath . 'layouts/header.php'; ?>

<div class="container py-4 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Career Roadmap <i class="bi bi-compass text-primary ms-2"></i></h2>
            <p class="text-muted">Define your goal and follow a structured path to success.</p>
        </div>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> shadow-sm border-0 d-flex align-items-center mb-4">
        <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle' : 'check-circle' ?>-fill me-2 fs-5"></i>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Goal Selection & Current Roadmap -->
        <div class="col-lg-8">
            <?php if ($personalizedRoadmap): ?>
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 text-white"><i class="bi bi-flag-fill me-2"></i>My Roadmap: <?= htmlspecialchars($personalizedRoadmap['title']) ?></h5>
                    <span class="badge bg-white text-primary rounded-pill">Active</span>
                </div>
                <div class="card-body">
                    <?php if (isset($gapAnalysis)): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="text-uppercase text-muted fw-bold mb-0 small tracking-wider">Readiness Score</h6>
                            <span class="fw-bold text-primary"><?= $gapAnalysis['readiness'] ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: <?= $gapAnalysis['readiness'] ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php 
                    $steps = json_decode($personalizedRoadmap['steps'] ?? '[]', true);
                    if (is_array($steps) && !empty($steps)): ?>
                    <h6 class="text-uppercase text-muted fw-bold mb-3 small tracking-wider">Your Path</h6>
                    <div class="roadmap-steps">
                        <?php foreach ($steps as $i => $step): ?>
                        <div class="roadmap-step">
                            <span class="step-number shadow-sm"><?= $i + 1 ?></span>
                            <div class="ms-4">
                                <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($step) ?></h6>
                                <p class="small text-muted mb-0">Step <?= $i + 1 ?> of <?= count($steps) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($gapAnalysis)): ?>
                    <div class="mt-4 pt-4 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-uppercase text-muted fw-bold mb-0 small tracking-wider">Skill Match Analysis</h6>
                            <button class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm" id="getAiAdviceBtn">
                                ✨ Get AI Expert Advice
                            </button>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p class="small fw-bold text-success mb-2"><i class="bi bi-check-circle-fill me-1"></i> Acquired</p>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if (!empty($gapAnalysis['acquired'])): ?>
                                        <?php foreach ($gapAnalysis['acquired'] as $skill): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><?= htmlspecialchars($skill) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted small italic">None detected yet. Try uploading your resume!</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <p class="small fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Missing / In Progress</p>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if (!empty($gapAnalysis['missing'])): ?>
                                        <?php foreach ($gapAnalysis['missing'] as $skill): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><?= htmlspecialchars($skill) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-success small fw-bold">All set! You have all the required skills.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="card mb-4 border-0 shadow-sm border-start border-4 border-warning">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-lightbulb fs-1 text-warning me-3"></i>
                        <div>
                            <h5 class="fw-bold">No Goal Selected Yet</h5>
                            <p class="text-muted mb-0">Select a career goal from the sidebar to generate your personalized roadmap.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-collection me-2 text-primary"></i>Explore All Roadmaps</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($roadmaps ?? [] as $rm): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border bg-light hover-shadow transition-all">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-2"><?= htmlspecialchars($rm['title']) ?></h6>
                                    <p class="small text-muted mb-3 line-clamp-2"><?= htmlspecialchars($rm['description']) ?></p>
                                    <form method="POST" action="<?= BASE_URL ?>/career-roadmap/update-goal">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                        <input type="hidden" name="career_goal" value="<?= htmlspecialchars($rm['career_goal']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">Select Path</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Goal Selector -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 6rem; z-index: 900;">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="mb-0 fw-bold">Update Goal</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= BASE_URL ?>/career-roadmap/update-goal">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Select Target Career</label>
                            <select name="career_goal" class="form-select form-select-lg" required>
                                <option value="">-- Choose Path --</option>
                                <?php foreach ($roadmaps as $rm): ?>
                                <option value="<?= htmlspecialchars($rm['career_goal']) ?>" <?= ($careerGoal ?? '') === $rm['career_goal'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($rm['title']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small">Changing this will update your personalized roadmap.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Update Goal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✨ AI Advisor Modal -->
<div class="modal fade" id="aiAdvisorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title"><i class="bi bi-stars me-2"></i>AI Career Consultant Advice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="aiAdviceContent">
                <div class="text-center py-5" id="aiLoader">
                    <div class="spinner-grow text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Analyzing your gap and generating personalized tips...</p>
                </div>
                <div id="aiResponseText" class="lh-lg" style="white-space: pre-line;"></div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('getAiAdviceBtn')?.addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('aiAdvisorModal'));
    const content = document.getElementById('aiResponseText');
    const loader = document.getElementById('aiLoader');
    
    content.innerHTML = '';
    loader.classList.remove('d-none');
    modal.show();

    fetch('<?= BASE_URL ?>/career-roadmap/get-ai-advice', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'csrf_token': '<?= $csrf_token ?? "" ?>',
            'goal': '<?= htmlspecialchars($personalizedRoadmap["title"] ?? "Software Engineering") ?>',
            'missing': <?= json_encode($gapAnalysis["missing"] ?? []) ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        loader.classList.add('d-none');
        if (data.advice) {
            content.innerHTML = data.advice;
        } else {
            content.innerHTML = '<div class="alert alert-danger">Error: ' + (data.error || "Unknown error") + '</div>';
        }
    })
    .catch(error => {
        loader.classList.add('d-none');
        content.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
    });
});
</script>


<?php require $viewPath . 'layouts/footer.php'; ?>
