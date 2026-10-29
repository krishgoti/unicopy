<?php $pageTitle = 'Leaderboard - ' . htmlspecialchars($contest['title']); require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold"><i class="bi bi-bar-chart-line text-info me-2"></i>Contest Leaderboard</h2>
        <p class="text-muted"><?= htmlspecialchars($contest['title']) ?></p>
    </div>
    <div class="col-md-6 text-md-end text-start">
        <a href="<?= BASE_URL ?>/contests" class="btn btn-outline-secondary fw-bold px-4 py-2">
            <i class="bi bi-arrow-left me-1"></i> Back to Contests
        </a>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm mb-4">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="bg-primary bg-opacity-10 p-4 border-bottom">
                <h5 class="fw-bold mb-0 text-primary">🏆 Top Performers</h5>
                <p class="small text-muted mb-0">The top 10 students based on accuracy and completion time.</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 fw-bold small text-uppercase text-muted" style="width: 80px;">Rank</th>
                            <th class="px-3 py-3 fw-bold small text-uppercase text-muted">Player</th>
                            <th class="px-3 py-3 fw-bold small text-uppercase text-muted">Accuracy</th>
                            <th class="px-3 py-3 fw-bold small text-uppercase text-muted">Score</th>
                            <th class="px-4 py-3 fw-bold small text-uppercase text-muted text-end">Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaderboard)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted fst-italic">No attempts recorded yet for this contest.</td></tr>
                        <?php else: ?>
                            <?php foreach ($leaderboard as $row): 
                                $isMe = (Auth::role() === 'student' && (int)$row['student_id'] === (int)Auth::user()['id']);
                                $acc = $row['total_marks'] > 0 ? round(($row['score'] / $row['total_marks']) * 100) : 0;
                            ?>
                                <tr class="<?= $isMe ? 'bg-primary bg-opacity-10' : '' ?>">
                                    <td class="px-4 py-3">
                                        <?php if ($row['rank'] == 1): ?>
                                            <span class="fs-4">🥇</span>
                                        <?php elseif ($row['rank'] == 2): ?>
                                            <span class="fs-4">🥈</span>
                                        <?php elseif ($row['rank'] == 3): ?>
                                            <span class="fs-4">🥉</span>
                                        <?php else: ?>
                                            <span class="fw-bold text-muted ps-1">#<?= $row['rank'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['student_name']) ?></div>
                                                <small class="text-muted d-block" style="font-size: 0.75rem;"><?= htmlspecialchars($row['department']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="progress" style="height: 6px; width: 80px;">
                                            <div class="progress-bar bg-<?= $acc >= 80 ? 'success' : ($acc >= 40 ? 'warning' : 'danger') ?>" 
                                                 role="progressbar" style="width: <?= $acc ?>%"></div>
                                        </div>
                                        <small class="text-muted mt-1 d-block"><?= $acc ?>%</small>
                                    </td>
                                    <td class="px-3 py-3 fw-bold text-primary">
                                        <?= $row['score'] ?> <span class="text-muted small fw-normal">/ <?= $row['total_marks'] ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <small class="text-muted"><?= date('H:i:s', strtotime($row['completed_at'])) ?></small>
                                        <small class="text-muted d-block opacity-75" style="font-size: 0.7rem;"><?= date('d M', strtotime($row['completed_at'])) ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($participantCount > 10): ?>
                <div class="card-footer bg-light p-3 text-center border-top-0">
                    <small class="text-muted">Currently tracking <?= $participantCount ?> participants. Only the top 10 are displayed.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <?php if ($userRank): ?>
            <div class="card shadow-sm border-0 rounded-4 bg-primary text-white mb-4">
                <div class="card-body p-4 text-center">
                    <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-person-badge fs-2"></i>
                    </div>
                    <h5 class="fw-bold mb-1 text-white">Your Performance</h5>
                    <p class="small text-white opacity-75 mb-4">Nice effort in this challenge!</p>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 p-3 rounded-3 h-100">
                                <small class="d-block opacity-75 text-uppercase">Rank</small>
                                <span class="fs-2 fw-bold">#<?= $userRank['rank'] ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 p-3 rounded-3 h-100">
                                <small class="d-block opacity-75 text-uppercase">Score</small>
                                <span class="fs-2 fw-bold"><?= $userRank['score'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="bi bi-lightbulb-fill text-warning me-2"></i> Ranking Logic
                </h6>
                <p class="small text-muted mb-0">Participants are ranked primarily by their **Total Score**. In case of a tie, the student who **completed the contest earlier** receives the higher rank.</p>
                <hr>
                <a href="<?= BASE_URL ?>/contests/history" class="btn btn-outline-primary w-100 btn-sm fw-bold py-2 rounded-3">
                    <i class="bi bi-clock-history me-1"></i> View Past Contests
                </a>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
