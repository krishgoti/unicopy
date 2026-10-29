<?php $pageTitle = 'Resume History'; require $viewPath . 'layouts/header.php'; ?>

<style>
/* ── Hero ── */
.hist-hero {
    background: var(--hero-bg-gradient);
    border-radius: 24px;
    padding: 3rem;
    color: white;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}
.hist-hero-icon {
    width: 64px; height: 64px;
    background: rgba(255,255,255,0.1);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem;
    border: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}
.new-upload-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--gold-gradient);
    border-radius: 12px;
    padding: 10px 22px;
    color: white;
    font-size: 0.9rem;
    font-weight: 700;
    text-decoration: none;
    transition: var(--transition);
    box-shadow: 0 10px 20px rgba(89, 74, 56, 0.3);
}
.new-upload-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(89, 74, 56, 0.4); color: white; }

/* ── Filter Bar ── */
.filter-bar {
    background: var(--card-bg);
    border-radius: 20px;
    border: 1px solid var(--border-color);
    padding: 1rem 1.5rem;
    display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
    margin-bottom: 2rem;
}
.filter-btn {
    border: 1px solid var(--border-color);
    border-radius: 50px;
    padding: 8px 20px;
    font-size: 0.85rem;
    font-weight: 700;
    background: transparent;
    color: var(--text-muted);
    cursor: pointer;
    transition: var(--transition);
}
.filter-btn:hover, .filter-btn.active { border-color: var(--primary); color: var(--primary); }
.filter-btn.active { background: var(--gold-gradient); color: white !important; border-color: transparent; }

/* ── History Card ── */
.hist-card {
    background: var(--card-bg);
    border-radius: 20px;
    border: 1px solid var(--border-color);
    margin-bottom: 1.25rem;
    overflow: hidden;
    transition: var(--transition);
}
.hist-card:hover { transform: translateY(-5px); border-color: var(--primary); box-shadow: var(--shadow-lg); }
.hist-card-body { padding: 1.5rem; }

/* File icon wrappers */
.file-icon-wrap {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
    background: rgba(var(--primary-rgb), 0.1);
}

/* Badges */
.badge-ai      { background: var(--gold-gradient); color: white; }
.badge-faculty { background: var(--secondary); color: white; border: 1px solid var(--primary); }
.status-pending    { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
.status-processing { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
.status-completed  { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
.status-failed     { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

/* Score display */
.score-chip {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 70px; padding: 6px 15px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 800;
    background: var(--gold-gradient);
    color: white;
}

/* Action buttons */
.act-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 18px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    transition: var(--transition);
    border: 1px solid var(--border-color);
    background: transparent;
    color: var(--text-main);
}
.act-ai      { border-color: var(--primary); color: var(--primary); }
.act-ai:hover { background: var(--primary); color: white; }
.act-faculty { border-color: var(--primary); color: var(--primary); }
.act-faculty:hover { background: var(--primary); color: white; }
.act-view    { border-color: var(--border-color); }
.act-view:hover { border-color: var(--primary); color: var(--primary); }
.act-del     { border-color: rgba(239, 68, 68, 0.2); color: #ef4444; }
.act-del:hover { background: #ef4444; color: white; border-color: #ef4444; }

/* Faculty Feedback Expand */
.feedback-expand {
    border-top: 1px solid var(--border-color);
    padding: 1.5rem;
    background: rgba(var(--primary-rgb), 0.03);
    animation: fadeSlideIn 0.3s ease;
}

/* Empty State */
.empty-state {
    background: var(--card-bg);
    border-radius: 24px;
    border: 1px solid var(--border-color);
    padding: 5rem 2rem;
    text-align: center;
}
.empty-icon {
    width: 100px; height: 100px;
    background: rgba(var(--primary-rgb), 0.1);
    border-radius: 30px;
    display: flex; align-items: center; justify-content: center;
    font-size: 3rem;
    margin: 0 auto 2rem;
}
.upload-cta-btn {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--gold-gradient);
    color: white;
    border: none;
    border-radius: 14px;
    padding: 12px 30px;
    font-size: 1rem;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 10px 25px rgba(89, 74, 56, 0.4);
    transition: var(--transition);
}
.upload-cta-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(89, 74, 56, 0.5); color: white; }

/* Animations */
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
.an1 { animation: fadeSlideIn 0.5s ease forwards; }
.an2 { animation: fadeSlideIn 0.5s 0.1s ease both; opacity: 0; }
.an3 { animation: fadeSlideIn 0.5s 0.2s ease both; opacity: 0; }

/* Divider */
.info-divider { width: 1px; height: 36px; background: var(--border-color); }
</style>

<!-- Hero -->
<div class="hist-hero an1">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="hist-hero-icon">📂</div>
        <div class="hist-hero-content flex-grow-1">
            <div class="fw-black mb-1" style="font-size:1.7rem; letter-spacing:-0.5px;">Resume Review History</div>
            <p class="mb-0 opacity-80" style="font-size:0.9rem;">All your resume submissions and their review status</p>
        </div>
        <a href="<?= BASE_URL ?>/resume" class="new-upload-btn">
            <i class="bi bi-cloud-upload-fill"></i> + New Upload
        </a>
    </div>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show rounded-4 border-0 shadow-sm an1">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (empty($reviews)): ?>
<!-- Empty State -->
<div class="empty-state an2">
    <div class="empty-icon">📄</div>
    <h5 class="fw-bold mb-2">No Reviews Found</h5>
    <p class="text-muted mb-4" style="font-size:0.9rem;">You haven't submitted any resume yet. Upload your first one!</p>
    <a href="<?= BASE_URL ?>/resume" class="upload-cta-btn">
        <i class="bi bi-rocket-takeoff-fill"></i> Upload Resume Now 🚀
    </a>
</div>

<?php else: ?>

<!-- Filter Bar -->
<div class="filter-bar an2">
    <span style="font-size:0.78rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px; margin-right:4px;">Filter:</span>
    <button class="filter-btn active" onclick="filterCards('all', this)">All (<?= count($reviews) ?>)</button>
    <button class="filter-btn" onclick="filterCards('ai', this)">🤖 AI Analysis</button>
    <button class="filter-btn" onclick="filterCards('faculty', this)">👨‍🏫 Faculty</button>
    <div class="info-divider mx-1"></div>
    <button class="filter-btn" onclick="filterCards('pending', this)">⏳ Pending</button>
    <button class="filter-btn" onclick="filterCards('completed', this)">✅ Completed</button>
</div>

<!-- Cards -->
<div id="historyContainer" class="an3">
    <?php foreach ($reviews as $r):
        $fb    = is_string($r['ai_feedback']) ? json_decode($r['ai_feedback'], true) : [];
        $score = $r['review_type'] === 'ai' ? ($r['ai_score'] ?? null) : ($r['faculty_score'] ?? null);
        $scoreColor = $score >= 75 ? '#16a34a' : ($score >= 50 ? '#d97706' : '#dc2626');
        $scoresBg   = $score >= 75 ? '#f0fdf4' : ($score >= 50 ? '#fffbeb' : '#fef2f2');
    ?>
    <div class="hist-card" data-type="<?= $r['review_type'] ?>" data-status="<?= $r['status'] ?>">
        <div class="hist-card-body">
            <div class="row align-items-center g-3">

                <!-- File Info -->
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="file-icon-wrap <?= $r['file_type'] === 'pdf' ? 'file-pdf' : 'file-docx' ?>">
                            <i class="bi bi-file-earmark-<?= $r['file_type'] === 'pdf' ? 'pdf text-danger' : 'word text-primary' ?>-fill"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size:0.875rem; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?= htmlspecialchars($r['file_name']) ?>
                            </div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                <i class="bi bi-calendar3 me-1"></i><?= date('d M Y, h:i A', strtotime($r['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Type & Status -->
                <div class="col-md-3">
                    <div class="mb-1">
                        <span class="badge rounded-pill <?= $r['review_type'] === 'ai' ? 'badge-ai' : 'badge-faculty' ?>" style="font-size:0.75rem;">
                            <?= $r['review_type'] === 'ai' ? '🤖 AI Analysis' : '👨‍🏫 Faculty Review' ?>
                        </span>
                    </div>
                    <span class="badge rounded-pill status-<?= $r['status'] ?>" style="font-size:0.72rem;">
                        <?= $r['status'] === 'processing' ? '⏳ Processing' : ucfirst($r['status']) ?>
                    </span>
                </div>

                <!-- Score -->
                <div class="col-md-2 text-center">
                    <?php if ($score !== null): ?>
                    <div class="score-chip" style="background:<?= $scoresBg ?>; color:<?= $scoreColor ?>;">
                        <?= $score ?>/100
                    </div>
                    <?php else: ?>
                    <span class="text-muted" style="font-size:0.85rem;">—</span>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="col-md-3">
                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                        <?php if ($r['review_type'] === 'ai' && $r['status'] === 'completed'): ?>
                            <a href="<?= BASE_URL ?>/resume/ai-result/<?= $r['id'] ?>" class="act-btn act-ai">
                                <i class="bi bi-bar-chart-fill"></i>AI Report
                            </a>
                        <?php elseif ($r['review_type'] === 'faculty' && $r['status'] === 'completed'): ?>
                            <button class="act-btn act-faculty" onclick="toggleFeedback(<?= $r['id'] ?>)">
                                <i class="bi bi-chat-left-text-fill"></i>Feedback
                            </button>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.8rem; padding:6px 0;">Awaiting review...</span>
                        <?php endif; ?>

                        <a href="<?= BASE_URL ?>/public/uploads/resumes/<?= htmlspecialchars($r['file_path']) ?>" target="_blank" class="act-btn act-view">
                            <i class="bi bi-eye-fill"></i>
                        </a>

                        <?php if ($r['status'] !== 'processing'): ?>
                        <form method="POST" action="<?= BASE_URL ?>/resume/delete" class="mb-0"
                              onsubmit="return confirm('Are you sure you want to delete this review?');">
                            <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="act-btn act-del">
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Faculty Feedback Expand -->
        <?php if ($r['review_type'] === 'faculty' && $r['status'] === 'completed' && !empty($r['faculty_feedback'])): ?>
        <div id="feedbackExpand<?= $r['id'] ?>" class="feedback-expand d-none">
            <div class="d-flex align-items-start gap-3">
                <div style="width:40px;height:40px;background:linear-gradient(135deg,#11998e,#38ef7d);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;flex-shrink:0;">
                    <i class="bi bi-chat-quote-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="mb-2" style="font-style:italic; color:#374151; font-size:0.875rem; line-height:1.7;">
                        "<?= nl2br(htmlspecialchars($r['faculty_feedback'])) ?>"
                    </p>
                    <div class="mt-2 d-flex gap-3 flex-wrap" style="font-size:0.78rem;">
                        <?php if (!empty($r['faculty_name'])): ?>
                        <span class="text-muted"><i class="bi bi-person-fill me-1 text-success"></i><strong>Reviewed by:</strong> <?= htmlspecialchars($r['faculty_name']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($r['reviewed_at'])): ?>
                        <span class="text-muted"><i class="bi bi-calendar3 me-1"></i><strong>On:</strong> <?= date('d M Y', strtotime($r['reviewed_at'])) ?></span>
                        <?php endif; ?>
                        <?php if ($r['faculty_score'] !== null): ?>
                        <span style="color:#059669; font-weight:700;"><i class="bi bi-star-fill me-1"></i>Score: <?= $r['faculty_score'] ?>/100</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
function filterCards(type, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    document.querySelectorAll('.hist-card').forEach(card => {
        let show = false;
        if (type === 'all') show = true;
        else if (type === 'ai' || type === 'faculty') show = card.dataset.type === type;
        else show = card.dataset.status === type;
        card.style.display = show ? '' : 'none';
    });
}
function toggleFeedback(id) {
    const el = document.getElementById('feedbackExpand' + id);
    if (el) el.classList.toggle('d-none');
}
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
