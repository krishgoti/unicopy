<?php $pageTitle = 'Attempt Contest - ' . htmlspecialchars($contest['title']); require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center bg-primary text-white">
                <div>
                    <h2 class="fw-bold mb-1"><?= htmlspecialchars($contest['title']) ?></h2>
                    <p class="mb-0 opacity-75"><?= htmlspecialchars($contest['description']) ?></p>
                </div>
                <div class="text-end">
                    <div id="countdown" class="fs-4 fw-bold">Ends in: --:--:--</div>
                    <small class="opacity-75">Time remaining until contest ends</small>
                </div>
            </div>
        </div>

        <form action="<?= BASE_URL ?>/contests/submit/<?= $contest['id'] ?>" method="POST" id="attemptForm">
            <input type="hidden" name="csrf_token" value="<?= $this->csrfToken() ?>">
            
            <?php foreach ($questions as $i => $q): ?>
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="fw-bold text-primary mb-0">Question <?= $i + 1 ?></h5>
                            <span class="badge bg-light text-muted border px-2 py-1"><?= (int)($q['marks']) ?> Marks</span>
                        </div>
                        
                        <div class="mb-4 fs-5 fw-medium">
                            <?= nl2br(htmlspecialchars($q['question_text'])) ?>
                        </div>

                        <?php if ($q['question_type'] === 'mcq'): ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="q_<?= $q['id'] ?>" id="q_<?= $q['id'] ?>_A" value="A">
                                    <label class="btn btn-outline-secondary w-100 text-start py-3 px-4 border shadow-sm transition-all" for="q_<?= $q['id'] ?>_A">
                                        <span class="fw-bold me-2">A.</span> <?= htmlspecialchars($q['option_a']) ?>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="q_<?= $q['id'] ?>" id="q_<?= $q['id'] ?>_B" value="B">
                                    <label class="btn btn-outline-secondary w-100 text-start py-3 px-4 border shadow-sm transition-all" for="q_<?= $q['id'] ?>_B">
                                        <span class="fw-bold me-2">B.</span> <?= htmlspecialchars($q['option_b']) ?>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="q_<?= $q['id'] ?>" id="q_<?= $q['id'] ?>_C" value="C">
                                    <label class="btn btn-outline-secondary w-100 text-start py-3 px-4 border shadow-sm transition-all" for="q_<?= $q['id'] ?>_C">
                                        <span class="fw-bold me-2">C.</span> <?= htmlspecialchars($q['option_c']) ?>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="q_<?= $q['id'] ?>" id="q_<?= $q['id'] ?>_D" value="D">
                                    <label class="btn btn-outline-secondary w-100 text-start py-3 px-4 border shadow-sm transition-all" for="q_<?= $q['id'] ?>_D">
                                        <span class="fw-bold me-2">D.</span> <?= htmlspecialchars($q['option_d']) ?>
                                    </label>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="bg-light p-3 rounded-4 border">
                                <label class="form-label small fw-bold text-muted mb-2"><i class="bi bi-pencil-square me-1"></i> Your Answer</label>
                                <textarea name="q_<?= $q['id'] ?>" class="form-control" rows="3" placeholder="Type your answer here..."></textarea>
                                <small class="text-muted mt-2 d-block">This question is automatically evaluated based on standard logic checks.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="card shadow-sm border-0 rounded-4 mb-5">
                <div class="card-body p-4 text-center">
                    <p class="text-muted mb-4">Make sure you have answered all questions. All progress is lost if the timer reaches zero.</p>
                    <button type="submit" class="btn btn-success fw-bold px-5 py-3 rounded-pill shadow" id="submitBtn">
                        <i class="bi bi-send-fill me-2"></i> Submit Contest Answers
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const endTime = new Date('<?= $contest['end_time'] ?>').getTime();
    const countdown = document.getElementById('countdown');
    const form = document.getElementById('attemptForm');

    const updateTimer = setInterval(() => {
        let now = new Date().getTime();
        let distance = endTime - now;

        if (distance < 0) {
            clearInterval(updateTimer);
            countdown.innerHTML = "Contest Expired";
            countdown.classList.add('text-danger');
            // Force submit if not submitted
            form.submit();
            return;
        }

        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdown.innerHTML = `Ends in: ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }, 1000);

    form.addEventListener('submit', function() {
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';
    });
});
</script>

<style>
.transition-all { transition: all 0.2s ease; }
.btn-check:checked + .btn-outline-secondary {
    background-color: var(--bs-primary) !important;
    border-color: var(--bs-primary) !important;
    color: #fff !important;
    transform: scale(0.98);
}
</style>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
