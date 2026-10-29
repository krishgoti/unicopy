<?php $pageTitle = 'Contest Details'; require BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php
    $now = date('Y-m-d H:i:s');
    $isActive = ($now >= $contest['start_time'] && $now <= $contest['end_time']);
    $isUpcoming = ($now < $contest['start_time']);
    $isPast = ($now > $contest['end_time']);
?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-trophy text-warning me-2"></i><?= htmlspecialchars($contest['title']) ?></h2>
        <div class="text-muted d-flex flex-wrap gap-3">
            <span><i class="bi bi-calendar-event me-1 text-primary"></i><?= date('d M Y, h:i A', strtotime($contest['start_time'])) ?></span>
            <span><i class="bi bi-calendar-x me-1 text-danger"></i><?= date('d M Y, h:i A', strtotime($contest['end_time'])) ?></span>
            <?php if (!empty($contest['week_key'])): ?>
                <span><i class="bi bi-hash me-1"></i><?= htmlspecialchars($contest['week_key']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/contests" class="btn btn-outline-secondary fw-bold"><i class="bi bi-arrow-left"></i> Back</a>
        <a href="<?= BASE_URL ?>/contests/leaderboard/<?= (int)$contest['id'] ?>" class="btn btn-outline-warning fw-bold"><i class="bi bi-bar-chart-line"></i> Leaderboard</a>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>-fill me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-2">About</h5>
                <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($contest['description'] ?? '')) ?></p>

                <div id="badge-container">
                    <?php if ($isActive): ?>
                        <div class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill mb-3"><i class="bi bi-record-circle-fill me-1"></i> Active Now</div>
                    <?php elseif ($isUpcoming): ?>
                        <div class="badge bg-info bg-opacity-10 text-info border border-info px-3 py-2 rounded-pill mb-3"><i class="bi bi-clock me-1"></i> Upcoming</div>
                    <?php else: ?>
                        <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-2 rounded-pill mb-3"><i class="bi bi-archive me-1"></i> Completed</div>
                    <?php endif; ?>
                </div>

                <div id="action-container">
                    <?php if (Auth::role() === 'student'): ?>
                        <?php if ($attempt): ?>
                            <div class="alert alert-success border-0 shadow-sm mb-3">
                                <div class="fw-bold">You already attempted this contest.</div>
                                <div class="small">Score: <?= (int)$attempt['score'] ?> / <?= (int)$attempt['total_marks'] ?></div>
                            </div>
                            <a href="<?= BASE_URL ?>/contests/leaderboard/<?= (int)$contest['id'] ?>" class="btn btn-primary fw-bold w-100"><i class="bi bi-bar-chart"></i> View Leaderboard</a>
                        <?php elseif ($isActive): ?>
                            <a href="<?= BASE_URL ?>/contests/attempt/<?= (int)$contest['id'] ?>" class="btn btn-primary fw-bold w-100"><i class="bi bi-play-fill"></i> Participate Now</a>
                        <?php elseif ($isUpcoming): ?>
                            <a href="<?= BASE_URL ?>/contests/attempt/<?= (int)$contest['id'] ?>" 
                               id="contest-countdown-btn" 
                               class="btn btn-secondary fw-bold w-100 disabled" 
                               style="pointer-events: none;"
                               data-start-ms="<?= strtotime($contest['start_time']) * 1000 ?>">
                                <i class="bi bi-lock"></i> Starts Soon
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary fw-bold w-100" disabled><i class="bi bi-lock"></i> Not Active</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-list-check me-2 text-primary"></i>Questions</h5>
                <?php if (empty($questions)): ?>
                    <div class="text-muted">No questions added yet.</div>
                <?php else: ?>
                    <div class="d-grid gap-3">
                        <?php foreach ($questions as $idx => $q): ?>
                            <div class="p-3 rounded-4 border bg-light">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="fw-semibold">Q<?= $idx + 1 ?>. <?= htmlspecialchars($q['question_text']) ?></div>
                                    <div class="text-muted small text-end">
                                        <div class="badge bg-primary bg-opacity-10 text-primary border border-primary"><?= htmlspecialchars(strtoupper($q['question_type'] ?? 'MCQ')) ?></div>
                                        <div class="mt-1">Marks: <?= (int)$q['marks'] ?></div>
                                    </div>
                                </div>
                                <?php if (($q['question_type'] ?? 'mcq') === 'mcq'): ?>
                                    <div class="row g-2 mt-2">
                                        <div class="col-md-6"><span class="text-muted">A.</span> <?= htmlspecialchars($q['option_a'] ?? '') ?></div>
                                        <div class="col-md-6"><span class="text-muted">B.</span> <?= htmlspecialchars($q['option_b'] ?? '') ?></div>
                                        <div class="col-md-6"><span class="text-muted">C.</span> <?= htmlspecialchars($q['option_c'] ?? '') ?></div>
                                        <div class="col-md-6"><span class="text-muted">D.</span> <?= htmlspecialchars($q['option_d'] ?? '') ?></div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small mt-2">Answer type: text</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Global offset for server time accuracy
const serverTimeOffset = <?= (time() * 1000) ?> - Date.now();

function updateContestTimer() {
    const btn = document.getElementById('contest-countdown-btn');
    if (!btn) return;

    const startTime = parseInt(btn.dataset.startMs);
    if (isNaN(startTime)) return;
    
    const currentServerTime = Date.now() + serverTimeOffset;
    
    if (currentServerTime >= startTime) {
        // Update Button: Make it a proper active link
        btn.classList.remove('btn-secondary', 'disabled');
        btn.classList.add('btn-primary');
        btn.style.pointerEvents = 'auto';
        btn.innerHTML = '<i class="bi bi-play-fill"></i> Participate Now';
        
        // Update Badge
        const badgeContainer = document.getElementById('badge-container');
        if (badgeContainer) {
            badgeContainer.innerHTML = '<div class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill mb-3"><i class="bi bi-record-circle-fill me-1"></i> Active Now</div>';
        }
        
        // Stop timer
        clearInterval(timerInterval);
    } else {
        const diff = startTime - currentServerTime;
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((diff % (1000 * 60)) / 1000);
        
        let timeStr = "";
        if (hours > 0) timeStr += hours + "h ";
        if (mins > 0 || hours > 0) timeStr += mins + "m ";
        timeStr += secs + "s";
        
        btn.innerHTML = '<i class="bi bi-clock"></i> Starts in ' + timeStr;
    }
}

const timerInterval = setInterval(updateContestTimer, 1000);
updateContestTimer();
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
