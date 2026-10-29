<?php $pageTitle = 'Review Resume — ' . htmlspecialchars($review['full_name']); require $viewPath . 'layouts/header.php'; ?>

<style>
* { font-family: 'Outfit', sans-serif; }

/* ── Review Hero ── */
.review-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 22px;
    padding: 1.75rem 2rem;
    color: white;
    margin-bottom: 1.75rem;
    position: relative;
    overflow: hidden;
}
.review-hero::before {
    content: '';
    position: absolute; top: -50px; right: -50px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.07);
    border-radius: 50%;
}
.review-hero::after {
    content: '';
    position: absolute; bottom: -60px; left: -30px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}
.rh-icon {
    width: 58px; height: 58px;
    background: rgba(255,255,255,0.15);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    border: 1.5px solid rgba(255,255,255,0.2);
    flex-shrink: 0;
    position: relative; z-index: 1;
}
.rh-content { position: relative; z-index: 1; }
.rh-meta-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 8px;
    padding: 4px 12px;
    font-size: 0.78rem;
    font-weight: 500;
    backdrop-filter: blur(6px);
}
.back-hero-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.15);
    border: 1.5px solid rgba(255,255,255,0.25);
    border-radius: 10px;
    padding: 8px 14px;
    color: white;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative; z-index: 1;
    white-space: nowrap;
}
.back-hero-btn:hover { background: rgba(255,255,255,0.25); color: white; }

/* ── PDF Viewer ── */
.viewer-card {
    background: white;
    border-radius: 20px;
    border: 1.5px solid #f0f0f8;
    box-shadow: 0 2px 16px rgba(0,0,0,0.05);
    overflow: hidden;
}
.viewer-header {
    padding: 1.1rem 1.5rem;
    border-bottom: 1.5px solid #f5f5fc;
    background: linear-gradient(90deg, #f8f9ff, #ffffff);
    display: flex; align-items: center; gap: 10px;
}
.pdf-frame {
    width: 100%; height: 600px;
    border: none;
    background: #f1f5f9;
}
.docx-placeholder {
    padding: 3rem 2rem;
    text-align: center;
}
.docx-icon {
    width: 80px; height: 80px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.2rem;
    margin: 0 auto 1.25rem;
}
.download-btn {
    display: inline-flex; align-items: center; gap: 7px;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 9px 20px;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(59,130,246,0.3);
    transition: all 0.2s ease;
}
.download-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(59,130,246,0.4); color: white; }

/* ── Feedback Form ── */
.feedback-card {
    background: white;
    border-radius: 20px;
    border: 1.5px solid #f0f0f8;
    box-shadow: 0 2px 16px rgba(0,0,0,0.05);
    overflow: hidden;
    position: sticky;
    top: 80px;
}
.feedback-card-header {
    padding: 1.2rem 1.5rem;
    background: linear-gradient(135deg, #f0fdf4, #f5fff8);
    border-bottom: 1.5px solid #d1fae5;
    display: flex; align-items: center; gap: 10px;
}
.feedback-body { padding: 1.5rem; }

/* ── Score Widget ── */
.score-widget {
    background: linear-gradient(135deg, #f8f9ff, #f0f0ff);
    border: 1.5px solid #e8ecf8;
    border-radius: 16px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    text-align: center;
}
.score-display {
    font-size: 3rem;
    font-weight: 900;
    color: #667eea;
    line-height: 1;
    margin: 0.5rem 0;
    transition: color 0.3s ease;
}
.score-ctrl-btn {
    width: 40px; height: 40px;
    border-radius: 10px;
    border: 1.5px solid #d0d7f5;
    background: white;
    color: #667eea;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex; align-items: center; justify-content: center;
}
.score-ctrl-btn:hover { background: #667eea; color: white; border-color: #667eea; }
.score-bar-bg {
    background: #e8ecf8;
    border-radius: 30px;
    height: 6px;
    overflow: hidden;
    margin-top: 0.75rem;
}
.score-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 30px;
    transition: width 0.4s cubic-bezier(0.4,0,0.2,1);
}

/* ── Feedback Textarea ── */
.feedback-textarea {
    border: 1.5px solid #e8ecf8;
    border-radius: 14px;
    padding: 1rem;
    font-size: 0.875rem;
    line-height: 1.7;
    resize: vertical;
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
    width: 100%;
}
.feedback-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
}

/* ── Templates ── */
.template-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: #f5f0ff;
    border: 1.5px solid #ddd6fe;
    color: #7c3aed;
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.template-chip:hover { background: #7c3aed; color: white; border-color: #7c3aed; }

/* ── Submit Btn ── */
.submit-feedback-btn {
    width: 100%;
    background: linear-gradient(135deg, #11998e, #38ef7d);
    border: none;
    border-radius: 12px;
    padding: 0.9rem;
    color: white;
    font-size: 0.95rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.25s ease;
    box-shadow: 0 4px 16px rgba(17,153,142,0.3);
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.submit-feedback-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(17,153,142,0.4); }

/* Char counter */
.char-counter { font-size: 0.75rem; color: #9ca3af; text-align: right; margin-top: 4px; }
.char-counter.warn { color: #f59e0b; }
.char-counter.good { color: #16a34a; }

/* Animations */
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.an1 { animation: fadeSlideIn 0.5s ease; }
.an2 { animation: fadeSlideIn 0.5s 0.08s ease both; }
</style>

<!-- Review Hero -->
<div class="review-hero an1">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="rh-icon">✏️</div>
        <div class="rh-content flex-grow-1">
        <div class="fw-black mb-1" style="font-size:1.5rem; letter-spacing:-0.3px;">
                Reviewing: <?= htmlspecialchars($review['full_name']) ?>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="rh-meta-chip"><i class="bi bi-building"></i><?= htmlspecialchars($review['department'] ?? 'N/A') ?></span>
                <span class="rh-meta-chip"><i class="bi bi-file-earmark"></i><?= strtoupper($review['file_type']) ?> File</span>
                <span class="rh-meta-chip"><i class="bi bi-calendar3"></i><?= date('d M Y', strtotime($review['created_at'])) ?></span>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/resume" class="back-hero-btn">
            <i class="bi bi-arrow-left"></i>Back to Queue
        </a>
    </div>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show rounded-4 border-0 shadow-sm an1">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4 an2">
    <!-- LEFT: Resume Viewer -->
    <div class="col-lg-7">
        <div class="viewer-card">
            <div class="viewer-header">
                <i class="bi bi-file-earmark-<?= $review['file_type'] === 'pdf' ? 'pdf text-danger' : 'word text-primary' ?>-fill fs-5"></i>
                <div class="fw-semibold" style="font-size:0.88rem; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    <?= htmlspecialchars($review['file_name']) ?>
                </div>
                <a href="<?= BASE_URL ?>/public/uploads/resumes/<?= htmlspecialchars($review['file_path']) ?>"
                   target="_blank"
                   class="btn btn-sm rounded-pill ms-auto"
                   style="background:#f0f0ff; color:#667eea; border:1.5px solid #d0d7f5; font-size:0.78rem; font-weight:600; white-space:nowrap;">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Open in New Tab
                </a>
            </div>
            <div class="p-2">
                <?php if ($review['file_type'] === 'pdf'): ?>
                <iframe
                    src="<?= BASE_URL ?>/public/uploads/resumes/<?= htmlspecialchars($review['file_path']) ?>"
                    class="pdf-frame"
                    title="Student Resume PDF">
                </iframe>
                <?php else: ?>
                <div class="docx-placeholder">
                    <div class="docx-icon">📝</div>
                    <h6 class="fw-bold mb-2">DOCX Preview Not Available</h6>
                    <p class="text-muted mb-4" style="font-size:0.88rem;">DOCX files cannot be previewed directly in the browser.<br>Please download the file to review it.</p>
                    <a href="<?= BASE_URL ?>/public/uploads/resumes/<?= htmlspecialchars($review['file_path']) ?>" download class="download-btn">
                        <i class="bi bi-download"></i>Download & View
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- RIGHT: Feedback Form -->
    <div class="col-lg-5">
        <div class="feedback-card">
            <div class="feedback-card-header">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#11998e,#38ef7d);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;">
                    <i class="bi bi-chat-left-text-fill"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:0.92rem; color:#065f46;">Submit Your Feedback</div>
                    <div style="font-size:0.75rem; color:#059669;">Detailed feedback helps students improve effectively!</div>
                </div>
            </div>
            <div class="feedback-body">
                <form method="POST" action="<?= BASE_URL ?>/resume/faculty-feedback">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">

                    <!-- Score Widget -->
                    <div class="score-widget mb-4">
                        <div style="font-size:0.8rem; font-weight:700; color:#7c7cb5; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Score (Optional)</div>
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <button type="button" class="score-ctrl-btn" onclick="changeScore(-5)">−</button>
                            <div>
                                <div class="score-display" id="scoreDisplay">—</div>
                                <div style="font-size:0.72rem; color:#9ca3af; margin-top:2px;">/100 points</div>
                            </div>
                            <button type="button" class="score-ctrl-btn" onclick="changeScore(+5)">＋</button>
                        </div>
                        <input type="number" name="faculty_score" id="scoreInput" min="0" max="100" class="d-none">
                        <div class="score-bar-bg mt-2">
                            <div class="score-bar-fill" id="scoreBarFill" style="width:0%;"></div>
                        </div>
                        <div style="font-size:0.73rem; color:#9ca3af; margin-top:4px;">Score is optional — you can skip and provide feedback only</div>
                    </div>

                    <!-- Quick Templates -->
                    <div class="mb-3">
                        <div style="font-size:0.78rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Quick Templates:</div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="template-chip" onclick="addTemplate('good')">👍 Good Resume</span>
                            <span class="template-chip" onclick="addTemplate('ats')">🤖 ATS Issues</span>
                            <span class="template-chip" onclick="addTemplate('project')">🛠️ Improve Projects</span>
                        </div>
                    </div>

                    <!-- Feedback Textarea -->
                    <div class="mb-4">
                        <label class="form-label fw-bold" style="font-size:0.88rem;">
                            Detailed Feedback <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="faculty_feedback"
                            id="feedbackText"
                            class="feedback-textarea"
                            rows="9"
                            placeholder="Write detailed feedback here:&#10;• Strengths of the resume&#10;• Areas that need improvement&#10;• Specific suggestions&#10;• Industry-specific advice"
                            oninput="updateCharCount(this)"
                            required></textarea>
                        <div class="char-counter" id="charCounter">0 characters</div>
                    </div>

                    <button type="submit" class="submit-feedback-btn">
                        <i class="bi bi-send-fill"></i>Submit Feedback to Student
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let currentScore = null;

function changeScore(delta) {
    if (currentScore === null) currentScore = 0;
    currentScore = Math.max(0, Math.min(100, currentScore + delta));
    updateScoreDisplay();
}

function updateScoreDisplay() {
    const display = document.getElementById('scoreDisplay');
    const input   = document.getElementById('scoreInput');
    const bar     = document.getElementById('scoreBarFill');

    display.textContent = currentScore;
    input.value = currentScore;
    bar.style.width = currentScore + '%';

    // Dynamic color
    if (currentScore >= 75) display.style.color = '#16a34a';
    else if (currentScore >= 50) display.style.color = '#d97706';
    else display.style.color = '#ef4444';
}

function updateCharCount(el) {
    const len = el.value.length;
    const counter = document.getElementById('charCounter');
    counter.textContent = len + ' characters';
    counter.className = 'char-counter ' + (len >= 100 ? 'good' : (len >= 50 ? 'warn' : ''));
}

const templates = {
    good: `Strengths:\n• Resume format is clean and professional\n• Skills are clearly listed\n• Projects section is informative\n\nSuggestions:\n• Add a LinkedIn profile link\n• Quantify your achievements (e.g., "Improved performance by 30%")\n• Add a summary/objective section`,
    ats: `ATS Issues Detected:\n• Use simple formatting — avoid tables and columns\n• Use standard section headings (Education, Experience, Skills)\n• Match keywords from the job description\n\nSuggestions:\n• Use a single-column format\n• Use bullet points instead of paragraphs`,
    project: `Focus on the Projects Section:\n• Clearly mention technologies used in each project\n• Include results/impact, not just features\n• Add GitHub repository links\n• 2-3 strong projects are better than 5 weak ones\n\nExample: "Built a REST API using Node.js that reduced load time by 40%"`
};

function addTemplate(type) {
    const ta = document.getElementById('feedbackText');
    ta.value = templates[type] || '';
    ta.focus();
    updateCharCount(ta);
}
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
