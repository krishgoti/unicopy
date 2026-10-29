<?php $pageTitle = 'Weekly Contests'; require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold"><i class="bi bi-trophy text-warning me-2"></i>Weekly Contests</h2>
        <p class="text-muted">Participate in challenges, test your skills, and climb the leaderboard.</p>
    </div>
    <div class="col-md-6 text-md-end text-start">
        <?php if (Auth::role() === 'faculty'): ?>
            <a href="<?= BASE_URL ?>/contests/create" class="btn btn-primary fw-bold px-4 py-2">
                <i class="bi bi-plus-circle me-1"></i> Create Contest
            </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/contests/leaderboard" class="btn btn-outline-warning fw-bold px-4 py-2 ms-2">
            <i class="bi bi-bar-chart-line me-1"></i> Leaderboard
        </a>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>-fill me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="row g-4 pt-2">
    <?php if (empty($contests)): ?>
        <div class="col-12 text-center py-5 bg-light rounded-4 border-0">
            <div class="display-1 text-muted opacity-50 mb-3"><i class="bi bi-inbox"></i></div>
            <h4 class="text-secondary fw-bold">No contests found</h4>
            <p class="text-muted">Check back later for new weekly challenges.</p>
        </div>
    <?php else: ?>
        <?php foreach ($contests as $contest): 
            $now = time();
            $startTime = strtotime($contest['start_time']);
            $endTime = strtotime($contest['end_time']);
            
            $isActive = ($now >= $startTime && $now <= $endTime);
            $isUpcoming = ($now < $startTime);
            $isPast = ($now > $endTime);

            $statusBadge = '';
            if ($isActive) $statusBadge = '<span id="status-badge-'.$contest['id'].'" class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill"><i class="bi bi-record-circle-fill me-1"></i> Active Now</span>';
            elseif ($isUpcoming) $statusBadge = '<span id="status-badge-'.$contest['id'].'" class="badge bg-info bg-opacity-10 text-info border border-info px-3 py-2 rounded-pill"><i class="bi bi-clock me-1"></i> Upcoming</span>';
            else $statusBadge = '<span id="status-badge-'.$contest['id'].'" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-2 rounded-pill"><i class="bi bi-archive me-1"></i> Completed</span>';

            $attempt = $attempts[$contest['id']] ?? null;
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 transition-hover">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bi bi-laptop fs-4"></i>
                        </div>
                        <div id="badge-container-<?= $contest['id'] ?>"><?= $statusBadge ?></div>
                    </div>
                    
                    <h5 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($contest['title']) ?></h5>
                    <p class="text-muted small mb-3 flex-grow-1 text-truncate-2"><?= htmlspecialchars($contest['description']) ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center bg-light rounded px-3 py-2 mb-4">
                        <div class="text-center">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Questions</small>
                            <span class="fw-bold fs-5 text-dark"><?= $contest['question_count'] ?></span>
                        </div>
                        <div class="vr bg-secondary opacity-25"></div>
                        <div class="text-center">
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">Created By</small>
                            <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($contest['faculty_name']) ?></span>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="d-flex flex-column gap-2 text-muted small mb-4">
                            <span><i class="bi bi-calendar-event me-2 text-primary"></i> <b>Starts:</b> <?= date('d M Y, h:i A', $startTime) ?></span>
                            <span><i class="bi bi-calendar-x me-2 text-danger"></i> <b>Ends:</b> <?= date('d M Y, h:i A', $endTime) ?></span>
                        </div>

                        <div id="action-container-<?= $contest['id'] ?>">
                            <?php if (Auth::role() === 'student'): ?>
                                <?php if ($attempt): ?>
                                    <div class="bg-success bg-opacity-10 border border-success text-success p-2 rounded text-center fw-bold small mb-2">
                                        <i class="bi bi-check-circle-fill me-1"></i> Attempted (Scored: <?= $attempt['score'] ?>/<?= $attempt['total_marks'] ?>)
                                    </div>
                                    <a href="<?= BASE_URL ?>/contests/leaderboard/<?= $contest['id'] ?>" class="btn btn-outline-primary w-100 py-2 fw-bold rounded-3">View Leaderboard</a>
                                <?php elseif ($isActive): ?>
                                    <a href="<?= BASE_URL ?>/contests/attempt/<?= $contest['id'] ?>" class="btn btn-primary w-100 py-2 fw-bold rounded-3">Participate Now</a>
                                <?php elseif ($isPast): ?>
                                    <a href="<?= BASE_URL ?>/contests/leaderboard/<?= $contest['id'] ?>" class="btn btn-outline-secondary w-100 py-2 fw-bold rounded-3">View Results</a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/contests/attempt/<?= $contest['id'] ?>" 
                                       id="btn-<?= $contest['id'] ?>" 
                                       class="btn btn-secondary w-100 py-2 fw-bold rounded-3 contest-countdown-btn disabled" 
                                       style="pointer-events: none;"
                                       data-start-ms="<?= $startTime * 1000 ?>"
                                       data-id="<?= $contest['id'] ?>">
                                        <i class="bi bi-clock me-1"></i> Starts Soon
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="d-flex gap-2">
                                    <a href="<?= BASE_URL ?>/contests/show/<?= $contest['id'] ?>" class="btn btn-outline-primary w-100 py-2 fw-bold rounded-3 text-truncate">View Details</a>
                                    <?php if (Auth::role() === 'admin' || (Auth::role() === 'faculty' && (int)$contest['faculty_id'] === (int)Auth::user()['id'])): ?>
                                        <button class="btn btn-outline-danger btn-sm px-3 rounded-3" onclick="confirmDelete(<?= $contest['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this contest? All student attempts and ranking data will be permanently removed.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/contests/delete/' + id;
        document.body.appendChild(form);
        form.submit();
    }
}

// Global offset for server time accuracy
const serverTimeOffset = <?= (time() * 1000) ?> - Date.now();

function updateContestTimers() {
    const currentServerTime = Date.now() + serverTimeOffset;
    
    document.querySelectorAll('.contest-countdown-btn').forEach(btn => {
        const startTime = parseInt(btn.dataset.startMs);
        if (isNaN(startTime)) return;
        
        const contestId = btn.dataset.id;
        
        if (currentServerTime >= startTime) {
            // Update Button: Make it a proper active link
            btn.classList.remove('btn-secondary', 'disabled');
            btn.classList.add('btn-primary');
            btn.style.pointerEvents = 'auto';
            btn.innerHTML = 'Participate Now';
            
            // Update Badge
            const badgeContainer = document.getElementById('badge-container-' + contestId);
            if (badgeContainer) {
                badgeContainer.innerHTML = '<span id="status-badge-' + contestId + '" class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill"><i class="bi bi-record-circle-fill me-1"></i> Active Now</span>';
            }
            
            // Remove from countdown processing
            btn.classList.remove('contest-countdown-btn');
        } else {
            const diff = startTime - currentServerTime;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const secs = Math.floor((diff % (1000 * 60)) / 1000);
            
            let timeStr = "";
            if (hours > 0) timeStr += hours + "h ";
            if (mins > 0 || hours > 0) timeStr += mins + "m ";
            timeStr += secs + "s";
            
            btn.innerHTML = '<i class="bi bi-clock me-1"></i> ' + timeStr;
        }
    });
}

// Run every second
setInterval(updateContestTimers, 1000);
updateContestTimers(); // Initial run
</script>

<style>
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
