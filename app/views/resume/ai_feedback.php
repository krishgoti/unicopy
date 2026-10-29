<?php $pageTitle = 'AI Resume Feedback'; require $viewPath . 'layouts/header.php'; ?>

<?php
$score    = (int)($feedback['overall_score'] ?? 0);
$atsScore = (int)($feedback['ats_score'] ?? 0);
$level    = $feedback['experience_level'] ?? 'fresher';
$summary  = $feedback['summary_feedback'] ?? '';

$scorePct   = $score . '%';
$atsPct     = $atsScore . '%';
$scoreLabel = $score >= 75 ? 'Excellent 🏆' : ($score >= 60 ? 'Good 👍' : ($score >= 40 ? 'Average ⚡' : 'Needs Work 🔧'));
$scoreGrad  = $score >= 75
    ? 'linear-gradient(135deg,#11998e,#38ef7d)'
    : ($score >= 50
        ? 'linear-gradient(135deg,#f093fb,#f5576c)'
        : 'linear-gradient(135deg,#fc4a1a,#f7b733)');
?>

<style>
/* ─── Page Wrapper ─── */
.ai-page { max-width: 1050px; margin: 0 auto; }

/* ─── Back Button ─── */
.back-btn {
    display: inline-flex; align-items: center; gap: 10px;
    background: rgba(var(--primary-rgb), 0.1);
    color: var(--primary);
    border-radius: 12px;
    padding: 10px 20px;
    font-size: 0.9rem;
    font-weight: 700;
    text-decoration: none;
    transition: var(--transition);
    border: 1px solid rgba(var(--primary-rgb), 0.2);
    margin-bottom: 2rem;
}
.back-btn:hover { background: var(--primary); color: white; transform: translateX(-5px); }

/* ─── Hero Score Banner ─── */
.score-banner {
    background: var(--hero-bg-gradient);
    border-radius: 24px;
    padding: 3rem;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 2rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}
.score-ring-outer {
    position: relative; z-index: 1;
    width: 150px; height: 150px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(12px);
    flex-shrink: 0;
}
.score-num-big { font-size: 3rem; font-weight: 900; line-height: 1; }
.score-sub     { font-size: 1rem; opacity: 0.7; }
.score-label-pill {
    display: inline-block;
    background: var(--gold-gradient);
    border-radius: 50px;
    padding: 6px 20px;
    font-size: 0.9rem;
    font-weight: 800;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    margin-bottom: 1rem;
}

/* ─── Section Cards ─── */
.sec-card {
    background: var(--card-bg);
    border-radius: 20px;
    border: 1px solid var(--border-color);
    overflow: hidden;
    margin-bottom: 1.5rem;
    transition: var(--transition);
}
.sec-card:hover { transform: translateY(-5px); border-color: var(--primary); }
.sec-card-header {
    padding: 1.25rem 1.5rem;
    display: flex; align-items: center; gap: 15px;
    border-bottom: 1px solid var(--border-color);
}
.sec-icon {
    width: 40px; height: 40px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    background: rgba(var(--primary-rgb), 0.1);
    color: var(--primary);
    flex-shrink: 0;
}
.sec-card-body {
    padding: 1.25rem 1.5rem;
}

/* ─── List items ─── */
.check-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.9rem;
}
.check-item:last-child { border-bottom: none; }
.ci-dot {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem;
    flex-shrink: 0;
    background: rgba(var(--primary-rgb), 0.1);
    color: var(--primary);
}

/* ─── Tags ─── */
.tag-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 15px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    margin: 4px;
    background: rgba(var(--primary-rgb), 0.1);
    border: 1px solid rgba(var(--primary-rgb), 0.2);
    color: var(--text-main);
}
.tag-found   { border-color: #10b98160; color: #10b981; }
.tag-missing { border-color: #ef444460; color: #ef4444; }

/* ─── Suggestions Layout ─── */
.sug-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.9rem;
}
.sug-item:last-child { border-bottom: none; }
.sug-num {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; font-weight: 800;
    flex-shrink: 0;
    background: #eff6ff;
    color: #1d4ed8;
}

/* ─── Section Checklist Row ─── */
.section-row {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.9rem;
}
.section-row:last-child { border-bottom: none; }

/* ─── Action Buttons ─── */
.action-btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 12px 24px;
    border-radius: 14px;
    font-size: 0.9rem;
    font-weight: 700;
    text-decoration: none;
    transition: var(--transition);
}
.action-btn-primary { background: var(--gold-gradient); color: white; box-shadow: 0 10px 20px rgba(89, 74, 56, 0.3); }
.action-btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(89, 74, 56, 0.4); }
.action-btn-outline { background: transparent; border: 1px solid var(--primary); color: var(--primary); }
.action-btn-outline:hover { background: var(--primary); color: white; transform: translateY(-3px); }

/* ─── Error Banner ─── */
.error-banner {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    display: flex; align-items: flex-start; gap: 15px;
    color: #ef4444;
}

/* ─── Animations ─── */
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.anim-1 { animation: fadeSlideIn 0.5s ease forwards; }
.anim-2 { animation: fadeSlideIn 0.5s 0.1s ease forwards; opacity: 0; }
.anim-3 { animation: fadeSlideIn 0.5s 0.2s ease forwards; opacity: 0; }
.anim-4 { animation: fadeSlideIn 0.5s 0.3s ease forwards; opacity: 0; }
</style>

<div class="ai-page">

    <a href="<?= BASE_URL ?>/resume" class="back-btn anim-1">
        <i class="bi bi-arrow-left"></i> Back to Resume
    </a>

    <?php if (!empty($feedback['error'])): ?>
    <div class="error-banner anim-1">
        <div style="font-size:1.5rem;">⚠️</div>
        <div>
            <div class="fw-bold mb-1" style="color:#92400e;">Analysis Incomplete</div>
            <div style="font-size:0.88rem; color:#78350f;"><?= htmlspecialchars($summary) ?></div>
            <div class="mt-1" style="font-size:0.8rem; color:#a16207;">Tip: Upload a DOCX or text-based PDF for best AI analysis results.</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Score Hero Banner ── -->
    <div class="score-banner anim-1" style="background: <?= $scoreGrad ?>;">
        <div class="row align-items-center g-4" style="position:relative;z-index:1;">
            <!-- Overall Score -->
            <div class="col-md-4 text-center">
                <div class="score-ring-outer mx-auto mb-3">
                    <div>
                        <div class="score-num-big" id="scoreCountUp">0</div>
                        <div class="score-sub">/100</div>
                    </div>
                </div>
                <div class="score-label-pill"><?= $scoreLabel ?></div>
                <div><span class="level-badge">📊 Level: <?= ucfirst($level) ?></span></div>
            </div>

            <!-- Divider -->
            <div class="col-md-1 d-none d-md-flex justify-content-center">
                <div style="width:1px; height:120px; background:rgba(255,255,255,0.2);"></div>
            </div>

            <!-- ATS + Summary -->
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="background:rgba(255,255,255,0.15); border-radius:14px; padding:12px 20px; backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.2);">
                <div class="fw-semibold" style="font-size:0.75rem; opacity:0.75; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">ATS Score</div>
                        <div class="d-flex align-items-baseline gap-1">
                            <span style="font-size:2rem; font-weight:900;" id="atsCountUp">0</span>
                            <span style="opacity:0.7;">/100</span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:0.78rem; opacity:0.75; margin-bottom:6px; font-weight:600;">ATS Compatibility</div>
                        <div class="ats-bar-bg">
                            <div class="ats-bar-fill" style="width:<?= $atsScore ?>%;"></div>
                        </div>
                        <div style="font-size:0.72rem; opacity:0.65; margin-top:4px;"><?= $atsScore ?>% match</div>
                    </div>
                </div>
                <?php if ($summary): ?>
                <div style="background:rgba(255,255,255,0.12); border-radius:12px; padding:12px 16px; backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,0.15); font-size:0.855rem; opacity:0.9; line-height:1.6;">
                    <?= htmlspecialchars($summary) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Main Grid ── -->
    <div class="row g-4">
        <!-- LEFT column -->
        <div class="col-lg-6">

            <!-- Strengths -->
            <?php if (!empty($feedback['strengths'])): ?>
            <div class="sec-card anim-2">
                <div class="sec-card-header">
                    <div class="sec-icon" style="background:#f0fdf4;">✅</div>
                    <div>
                        <div class="fw-bold" style="font-size:0.92rem; color:#15803d;">Strengths</div>
                        <div class="text-muted" style="font-size:0.75rem;"><?= count($feedback['strengths']) ?> points identified</div>
                    </div>
                </div>
                <div class="sec-card-body">
                    <?php foreach ($feedback['strengths'] as $item): ?>
                    <div class="check-item">
                        <div class="ci-dot" style="background:#f0fdf4;">
                            <i class="bi bi-check-lg" style="color:#16a34a;"></i>
                        </div>
                        <span><?= htmlspecialchars($item) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Weaknesses -->
            <?php if (!empty($feedback['weaknesses'])): ?>
            <div class="sec-card anim-2">
                <div class="sec-card-header">
                    <div class="sec-icon" style="background:#fffbeb;">⚠️</div>
                    <div>
                        <div class="fw-bold" style="font-size:0.92rem; color:#b45309;">Weaknesses</div>
                        <div class="text-muted" style="font-size:0.75rem;"><?= count($feedback['weaknesses']) ?> areas to improve</div>
                    </div>
                </div>
                <div class="sec-card-body">
                    <?php foreach ($feedback['weaknesses'] as $item): ?>
                    <div class="check-item">
                        <div class="ci-dot" style="background:#fffbeb;">
                            <i class="bi bi-dash-circle" style="color:#d97706;"></i>
                        </div>
                        <span><?= htmlspecialchars($item) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Grammar & Formatting -->
            <?php if (!empty($feedback['grammar_issues']) || !empty($feedback['formatting_notes'])): ?>
            <?php $grammarAll = array_merge($feedback['grammar_issues'] ?? [], $feedback['formatting_notes'] ?? []); ?>
            <div class="sec-card anim-3">
                <div class="sec-card-header">
                    <div class="sec-icon" style="background:#fef2f2;">🔤</div>
                    <div>
                        <div class="fw-bold" style="font-size:0.92rem; color:#dc2626;">Grammar & Formatting</div>
                        <div class="text-muted" style="font-size:0.75rem;"><?= count($grammarAll) ?> issues detected</div>
                    </div>
                </div>
                <div class="sec-card-body">
                    <?php foreach ($grammarAll as $item): ?>
                    <div class="check-item">
                        <div class="ci-dot" style="background:#fef2f2;">
                            <i class="bi bi-x-circle" style="color:#ef4444;"></i>
                        </div>
                        <span><?= htmlspecialchars($item) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- RIGHT column -->
        <div class="col-lg-6">

            <!-- Suggestions -->
            <?php if (!empty($feedback['suggestions'])): ?>
            <div class="sec-card anim-2">
                <div class="sec-card-header">
                    <div class="sec-icon" style="background:#eff6ff;">💡</div>
                    <div>
                        <div class="fw-bold" style="font-size:0.92rem; color:#1d4ed8;">Improvement Suggestions</div>
                        <div class="text-muted" style="font-size:0.75rem;"><?= count($feedback['suggestions']) ?> actionable tips</div>
                    </div>
                </div>
                <div class="sec-card-body">
                    <?php foreach ($feedback['suggestions'] as $i => $item): ?>
                    <div class="sug-item">
                        <div class="sug-num"><?= $i + 1 ?></div>
                        <span><?= htmlspecialchars($item) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Skills Found -->
            <?php if (!empty($feedback['skills_found'])): ?>
            <div class="sec-card anim-3">
                <div class="sec-card-header">
                    <div class="sec-icon" style="background:#f0fdf4;">🛠️</div>
                    <div>
                        <div class="fw-bold" style="font-size:0.92rem; color:#15803d;">Skills Detected</div>
                        <div class="text-muted" style="font-size:0.75rem;"><?= count($feedback['skills_found']) ?> skills identified in your resume</div>
                    </div>
                </div>
                <div class="sec-card-body">
                    <?php foreach ($feedback['skills_found'] as $skill): ?>
                    <span class="tag-pill tag-found">
                        <i class="bi bi-check-lg"></i><?= htmlspecialchars($skill) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Missing Keywords -->
            <?php if (!empty($feedback['missing_keywords'])): ?>
            <div class="sec-card anim-3">
                <div class="sec-card-header">
                    <div class="sec-icon" style="background:#fef2f2;">🏷️</div>
                    <div>
                        <div class="fw-bold" style="font-size:0.92rem; color:#dc2626;">Missing Keywords</div>
                        <div class="text-muted" style="font-size:0.75rem;">Add these keywords to improve your ATS score</div>
                    </div>
                </div>
                <div class="sec-card-body">
                    <?php foreach ($feedback['missing_keywords'] as $kw): ?>
                    <span class="tag-pill tag-missing">
                        <i class="bi bi-plus-lg"></i><?= htmlspecialchars($kw) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Resume Sections -->
            <div class="sec-card anim-4">
                <div class="sec-card-header">
                    <div class="sec-icon" style="background:#eff6ff;">📋</div>
                    <div>
                        <div class="fw-bold" style="font-size:0.92rem; color:#1d4ed8;">Resume Sections Checklist</div>
                        <div class="text-muted" style="font-size:0.75rem;">Status of important resume sections</div>
                    </div>
                </div>
                <div class="sec-card-body">
                    <?php
                    $found   = array_map('strtolower', $feedback['sections_found']   ?? []);
                    $missing = array_map('strtolower', $feedback['missing_sections'] ?? []);
                    $all     = array_unique(array_merge($found, $missing));
                    foreach ($all as $sec):
                        $isFound = in_array($sec, $found);
                    ?>
                    <div class="section-row">
                        <div class="ci-dot" style="background:<?= $isFound ? '#f0fdf4' : '#fef2f2' ?>;">
                            <i class="bi bi-<?= $isFound ? 'check-lg' : 'x-lg' ?>"
                               style="color:<?= $isFound ? '#16a34a' : '#ef4444' ?>;"></i>
                        </div>
                        <span class="<?= $isFound ? 'fw-semibold' : 'text-muted' ?>" style="flex:1;">
                            <?= htmlspecialchars(ucfirst($sec)) ?>
                        </span>
                        <?php if (!$isFound): ?>
                        <span class="badge rounded-pill" style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca; font-size:0.7rem;">Missing</span>
                        <?php else: ?>
                        <span class="badge rounded-pill" style="background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; font-size:0.7rem;">✓ Found</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-3 mt-4 flex-wrap anim-4">
        <a href="<?= BASE_URL ?>/resume" class="action-btn action-btn-primary">
            <i class="bi bi-arrow-repeat"></i>Upload Improved Resume
        </a>
        <a href="<?= BASE_URL ?>/resume/history" class="action-btn action-btn-outline">
            <i class="bi bi-clock-history"></i>View All History
        </a>
        <a href="<?= BASE_URL ?>/public/uploads/resumes/<?= htmlspecialchars($review['file_path']) ?>" target="_blank" class="action-btn action-btn-outline">
            <i class="bi bi-eye"></i>View Resume File
        </a>
    </div>
</div>

<script>
function countUp(elId, target, duration = 1500) {
    const el = document.getElementById(elId);
    if (!el || target === 0) { if(el) el.textContent = 0; return; }
    let start = 0;
    const step = target / (duration / 16);
    const timer = setInterval(() => {
        start += step;
        if (start >= target) { start = target; clearInterval(timer); }
        el.textContent = Math.round(start);
    }, 16);
}
document.addEventListener('DOMContentLoaded', () => {
    countUp('scoreCountUp', <?= $score ?>);
    countUp('atsCountUp',   <?= $atsScore ?>);
});
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
