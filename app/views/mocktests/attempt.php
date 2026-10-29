<?php $pageTitle = 'Attempt Mock Test'; require $viewPath . 'layouts/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div class="d-flex align-items-start gap-3">
        <a href="<?= BASE_URL ?>/mock-tests/list" class="btn btn-outline-secondary btn-sm rounded-circle mt-1">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h2 class="fw-bold mb-1"><?= htmlspecialchars($test['title'] ?? 'Mock Test') ?></h2>
            <div class="text-muted">
                Skill: <strong><?= htmlspecialchars($test['skill_name'] ?? '-') ?></strong>
                <span class="mx-2">•</span>
                Max Score: <strong>100</strong>
                <span class="mx-2">•</span>
                10 Questions
            </div>
        </div>
    </div>
    <a href="<?= BASE_URL ?>/mock-tests/history" class="btn btn-outline-primary">
        <i class="bi bi-clock-history me-2"></i>History
    </a>
</div>

<?php if ((int)($test['duration_mins'] ?? 0) > 0 && $test['started_at']): 
    $expiryTime = strtotime($test['started_at']) + ($test['duration_mins'] * 60);
    $remainingSecs = $expiryTime - time();
?>
    <div id="timerCard" class="card border-primary shadow-lg position-fixed top-0 end-0 m-4" style="z-index: 3000; width: 180px; display: none;">
        <div class="card-body p-3 text-center">
            <div class="small fw-bold text-muted text-uppercase letter-spacing-1 mb-1">Time Remaining</div>
            <div id="countdown" class="fs-3 fw-black text-primary">00:00</div>
        </div>
    </div>

    <script>
        (function() {
            let remaining = <?= (int)$remainingSecs ?>;
            const display = document.getElementById('countdown');
            const card = document.getElementById('timerCard');
            const form = document.querySelector('form');

            if (remaining <= 0) {
                alert('Time is up!');
                form.submit();
                return;
            }

            card.style.display = 'block';

            const timer = setInterval(() => {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(timer);
                    display.innerText = '00:00';
                    alert('Time is up! Your answers will be submitted automatically.');
                    form.submit();
                } else {
                    const mins = Math.floor(remaining / 60);
                    const secs = remaining % 60;
                    display.innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    
                    if (remaining < 60) {
                        display.classList.remove('text-primary');
                        display.classList.add('text-danger');
                    }
                }
            }, 1000);
        })();
    </script>
<?php endif; ?>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-sm mb-4">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/mock-tests/submit/<?= (int)$test['id'] ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <?php if (!empty($test['description'])): ?>
                <div class="alert alert-info border-0 small mb-4">
                    <i class="bi bi-info-circle me-1"></i> <?= htmlspecialchars($test['description']) ?>
                </div>
            <?php endif; ?>

            <?php foreach (($questions ?? []) as $i => $q): ?>
                <div class="border rounded-4 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold text-primary">Q<?= $i + 1 ?>.</div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">10 points</span>
                    </div>
                    <div class="fw-bold mb-3"><?= htmlspecialchars($q['question_text']) ?></div>

                    <?php
                    $qid = (int)$q['id'];
                    $opts = [
                        'A' => $q['option_a'] ?? '',
                        'B' => $q['option_b'] ?? '',
                        'C' => $q['option_c'] ?? '',
                        'D' => $q['option_d'] ?? '',
                    ];
                    ?>

                    <div class="row g-2">
                        <?php foreach ($opts as $k => $v): ?>
                            <div class="col-md-6">
                                <label class="w-100 border rounded-4 p-3 d-flex gap-2 align-items-start hover-shadow-sm option-label" style="cursor:pointer;">
                                    <input class="form-check-input mt-1" type="radio" name="q_<?= $qid ?>" value="<?= $k ?>">
                                    <div class="option-content">
                                        <div class="small text-muted fw-bold"><?= $k ?></div>
                                        <div class="option-text"><?= htmlspecialchars($v) ?></div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/mock-tests/list" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-success fw-bold ms-auto px-5">
            <i class="bi bi-send-check me-2"></i>Submit Test
        </button>
    </div>
</form>

<?php require $viewPath . 'layouts/footer.php'; ?>

