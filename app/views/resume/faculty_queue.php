<?php $pageTitle = 'Resume Review Queue'; require $viewPath . 'layouts/header.php'; ?>

<style>
* { font-family: 'Outfit', sans-serif; }

/* ── Hero ── */
.queue-hero {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    border-radius: 24px;
    padding: 2.25rem 2.5rem;
    color: white;
    margin-bottom: 1.75rem;
    position: relative;
    overflow: hidden;
}
.queue-hero::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}
.queue-hero::after {
    content: '';
    position: absolute; bottom: -80px; left: -30px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.q-icon {
    width: 65px; height: 65px;
    background: rgba(255,255,255,0.15);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
    border: 1.5px solid rgba(255,255,255,0.25);
    flex-shrink: 0;
    position: relative; z-index: 1;
}
.hero-text { position: relative; z-index: 1; }

/* ── Stat Cards ── */
.stat-card {
    background: white;
    border-radius: 18px;
    border: 1.5px solid #f0f0f8;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 2px 14px rgba(0,0,0,0.04);
    transition: all 0.25s ease;
}
.stat-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,0.09); transform: translateY(-3px); }
.stat-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    margin: 0 auto 0.75rem;
}
.stat-num { font-size: 2.2rem; font-weight: 900; line-height: 1; margin-bottom: 4px; }

/* ── Table Card ── */
.table-card {
    background: white;
    border-radius: 20px;
    border: 1.5px solid #f0f0f8;
    box-shadow: 0 2px 16px rgba(0,0,0,0.04);
    overflow: hidden;
}
.table-card-header {
    padding: 1.25rem 1.75rem;
    border-bottom: 1.5px solid #f5f5fc;
    display: flex; align-items: center; justify-content: between; gap: 12px;
    background: linear-gradient(90deg, #f8f9ff, #f5fffe);
}
.queue-table thead th {
    background: transparent;
    border-bottom: 1.5px solid #f0f0f8;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #9ca3af;
    padding: 14px 16px;
    white-space: nowrap;
}
.queue-table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f8f8fc;
}
.queue-table tbody tr:hover { background: #fafaff; }
.queue-table tbody tr:last-child td { border-bottom: none; }

/* Badges */
.status-pending    { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
.status-processing { background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe; }
.status-completed  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.status-failed     { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }

/* Student avatar */
.stu-avatar {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem;
    font-weight: 700;
    flex-shrink: 0;
}

/* Buttons */
.review-btn {
    display: inline-flex; align-items: center; gap: 5px;
    background: linear-gradient(135deg, #11998e, #38ef7d);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 3px 10px rgba(17,153,142,0.25);
}
.review-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(17,153,142,0.35); color: white; }
.view-file-btn {
    display: inline-flex; align-items: center; gap: 5px;
    border: 1.5px solid #e5e7eb;
    background: white;
    color: #6b7280;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}
.view-file-btn:hover { border-color: #667eea; color: #5a6acf; background: #f0f0ff; }

/* Empty */
.empty-box {
    padding: 4rem 2rem;
    text-align: center;
}
.empty-icon {
    width: 80px; height: 80px;
    background: linear-gradient(135deg, #f0fff8, #e0fff0);
    border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.2rem;
    margin: 0 auto 1.5rem;
}

/* Animations */
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}
.an1 { animation: fadeSlideIn 0.5s ease; }
.an2 { animation: fadeSlideIn 0.5s 0.1s ease both; }
.an3 { animation: fadeSlideIn 0.5s 0.2s ease both; }
</style>

<?php
$total     = count($reviews);
$pending   = count(array_filter($reviews, fn($r) => $r['status'] === 'pending'));
$completed = count(array_filter($reviews, fn($r) => $r['status'] === 'completed'));
?>

<!-- Hero -->
<div class="queue-hero an1">
    <div class="d-flex align-items-center gap-4 flex-wrap">
        <div class="q-icon">📋</div>
        <div class="hero-text flex-grow-1">
    <div class="fw-black mb-1" style="font-size:1.7rem; letter-spacing:-0.5px;">Resume Review Queue</div>
            <p class="mb-0 opacity-80" style="font-size:0.9rem;">Review submitted resumes from students and provide detailed feedback!</p>
        </div>
        <div class="d-flex gap-2 flex-wrap" style="position:relative;z-index:1;">
            <div style="background:rgba(255,255,255,0.15);border-radius:12px;padding:8px 16px;backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);text-align:center;">
                <div class="fw-black" style="font-size:1.5rem;"><?= $total ?></div>
                <div style="font-size:0.73rem;opacity:0.8;">Total</div>
            </div>
            <div style="background:rgba(255,255,255,0.15);border-radius:12px;padding:8px 16px;backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);text-align:center;">
                <div class="fw-black" style="font-size:1.5rem;"><?= $pending ?></div>
                <div style="font-size:0.73rem;opacity:0.8;">Pending</div>
            </div>
            <div style="background:rgba(255,255,255,0.15);border-radius:12px;padding:8px 16px;backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);text-align:center;">
                <div class="fw-black" style="font-size:1.5rem;"><?= $completed ?></div>
                <div style="font-size:0.73rem;opacity:0.8;">Done</div>
            </div>
        </div>
    </div>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show rounded-4 border-0 shadow-sm an1">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats Row -->
<div class="row g-3 mb-4 an2">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#667eea20,#764ba220);">📨</div>
            <div class="stat-num" style="color:#667eea;"><?= $total ?></div>
            <div class="text-muted" style="font-size:0.82rem; font-weight:600;">Total Submitted</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b20,#fbbf2420);">⏳</div>
            <div class="stat-num" style="color:#d97706;"><?= $pending ?></div>
            <div class="text-muted" style="font-size:0.82rem; font-weight:600;">Awaiting Review</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#11998e20,#38ef7d20);">✅</div>
            <div class="stat-num" style="color:#11998e;"><?= $completed ?></div>
            <div class="text-muted" style="font-size:0.82rem; font-weight:600;">Reviews Completed</div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="table-card an3">
    <div class="table-card-header">
        <div style="width:36px;height:36px;background:linear-gradient(135deg,#11998e20,#38ef7d20);border-radius:10px;display:flex;align-items:center;justify-content:center;">
            <i class="bi bi-list-check" style="color:#11998e;"></i>
        </div>
        <div>
            <div class="fw-bold" style="font-size:0.95rem;">All Faculty Review Submissions</div>
            <div class="text-muted" style="font-size:0.77rem;"><?= $pending ?> submission(s) awaiting review</div>
        </div>
        <?php if ($pending > 0): ?>
        <span class="ms-auto badge rounded-pill" style="background:#fffbeb;color:#d97706;border:1px solid #fde68a;font-size:0.78rem;padding:6px 12px;">
            <i class="bi bi-exclamation-circle me-1"></i><?= $pending ?> Pending
        </span>
        <?php endif; ?>
    </div>

    <?php if (empty($reviews)): ?>
    <div class="empty-box">
        <div class="empty-icon">📬</div>
        <h6 class="fw-bold mb-2">No Review Requests</h6>
        <p class="text-muted" style="font-size:0.88rem;">No students have submitted resumes for faculty review yet.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table queue-table mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Student</th>
                    <th>Department</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $r): ?>
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stu-avatar">
                                <?= strtoupper(substr($r['full_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-semibold" style="font-size:0.875rem;"><?= htmlspecialchars($r['full_name']) ?></div>
                                <?php if ($r['status'] === 'pending'): ?>
                                <div style="font-size:0.72rem;color:#d97706;font-weight:600;"><i class="bi bi-clock me-1"></i>Needs Review</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-size:0.82rem; color:#6b7280;"><?= htmlspecialchars($r['department'] ?? '—') ?></span>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>/public/uploads/resumes/<?= htmlspecialchars($r['file_path']) ?>"
                           target="_blank" class="view-file-btn">
                            <i class="bi bi-file-earmark-<?= $r['file_type'] === 'pdf' ? 'pdf text-danger' : 'word text-primary' ?>"></i>
                            <?= strtoupper($r['file_type']) ?>
                        </a>
                    </td>
                    <td>
                        <span class="badge rounded-pill status-<?= $r['status'] ?>" style="font-size:0.75rem;">
                            <?= $r['status'] === 'processing' ? '⏳ Processing' : ucfirst($r['status']) ?>
                        </span>
                    </td>
                    <td style="font-size:0.8rem; color:#6b7280;"><?= date('d M Y, h:i A', strtotime($r['created_at'])) ?></td>
                    <td>
                        <?php if ($r['status'] === 'completed'): ?>
                            <span style="color:#16a34a; font-size:0.82rem; font-weight:600;">
                                <i class="bi bi-check-all me-1"></i>Reviewed ✓
                            </span>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/resume/review/<?= $r['id'] ?>" class="review-btn">
                                <i class="bi bi-pencil-square"></i>Review Now
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require $viewPath . 'layouts/footer.php'; ?>
