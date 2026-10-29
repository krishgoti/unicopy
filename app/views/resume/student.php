<?php $pageTitle = 'AI Resume Analysis'; require $viewPath . 'layouts/header.php'; ?>

<style>
/* ── Hero Banner ── */
.resume-hero {
    background: var(--gold-gradient);
    border-radius: 24px;
    padding: 3rem;
    color: white;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

/* ── Mode Cards ── */
.mode-card.selected-ai  { border-color: #5a6acf; box-shadow: 0 0 0 2px #5a6acf; background: rgba(90, 106, 207, 0.05); }
.mode-card.selected-fac { border-color: #11998e; box-shadow: 0 0 0 2px #11998e; background: rgba(17, 153, 142, 0.05); }

.ai-card { transition: all 0.3s ease; }
.fac-card { transition: all 0.3s ease; }

.submit-info-box.ai-mode { border-left: 4px solid #5a6acf !important; background: rgba(90, 106, 207, 0.03); }
.submit-info-box.fac-mode { border-left: 4px solid #11998e !important; background: rgba(17, 153, 142, 0.03); }

#loadingDiv {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.7); z-index: 9999;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: white; backdrop-filter: blur(5px);
}
</style>

<!-- Hero Banner -->
<div class="dash-hero p-5 rounded-4 shadow-lg mb-5 border-0 overflow-hidden reveal-on-scroll">
    <div class="row align-items-center position-relative z-1">
        <div class="col-md-7">
            <div class="glass-pill px-3 py-1 mb-3">
                <span class="pulse-dot"></span>
                <span class="text-white small fw-bold">Gemini AI Engine Active</span>
            </div>
            <h1 class="fw-black text-white mb-2 display-5">Smart Resume Analysis</h1>
            <p class="text-white-50 lead mb-3 opacity-75">Upload your resume — Gemini AI will provide instant deep analysis or get an expert expert review!</p>
            <div class="d-flex gap-3">
                <div class="glass-pill px-3 py-2 text-white small fw-bold">
                    <i class="bi bi-lightning-charge-fill text-gradient me-2"></i> Instant Feedback
                </div>
                <div class="glass-pill px-3 py-2 text-white small fw-bold">
                    <i class="bi bi-shield-check-fill text-gradient me-2"></i> ATS Analysis
                </div>
            </div>
        </div>
        <div class="col-md-5 text-md-end d-none d-md-block">
            <i class="bi bi-robot text-white opacity-10" style="font-size: 8rem;"></i>
        </div>
    </div>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show rounded-4 border-0 shadow-sm anim-2" role="alert">
    <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-triangle' : 'check-circle' ?>-fill me-2"></i>
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Upload Form Card -->
<div class="glass-card mb-5 border-0 shadow-sm rounded-4 reveal-on-scroll">
    <div class="p-4 border-bottom border-secondary border-opacity-10 d-flex align-items-center gap-3">
        <div class="bg-gold-gradient text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">1</div>
        <div>
            <div class="fw-black text-main small text-uppercase">Upload Your Resume</div>
            <div class="text-muted" style="font-size:0.75rem;">PDF or DOCX format — Max 5MB</div>
        </div>
    </div>
    <div class="p-4">
        <form method="POST" action="<?= BASE_URL ?>/resume/upload" enctype="multipart/form-data" id="resumeUploadForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <!-- Drop Zone -->
            <div class="mb-5">
                <div class="drop-zone glass-pill p-5 text-center border-dashed border-2 transition-all hover-lift" id="uploadZone" onclick="document.getElementById('resumeFile').click();" style="cursor: pointer; border-color: var(--primary-gold) !important; background: rgba(217, 192, 163, 0.05);">
                    <input type="file" name="resume" id="resumeFile" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required class="d-none">
                    <div class="bg-gold-gradient text-white rounded-4 d-inline-flex align-items-center justify-content-center p-3 mb-3 shadow-lg">
                        <i class="bi bi-file-earmark-arrow-up fs-2"></i>
                    </div>
                    <h5 class="fw-black text-main mb-1" id="uploadText">Click to browse or drag & drop</h5>
                    <p class="text-muted small mb-0">Securely processed by Gemini AI</p>
                </div>
                <div id="filePreview" class="mt-3 d-none">
                    <div class="glass-pill p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-file-earmark-check-fill text-gradient fs-4"></i>
                            <div>
                                <div class="fw-bold text-main small" id="fileName"></div>
                                <div class="text-muted small" id="fileSize"></div>
                            </div>
                        </div>
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </div>
                </div>
            </div>

            <!-- Review Mode -->
            <div class="mb-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-gold-gradient text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">2</div>
                    <div class="fw-black text-main small text-uppercase">Choose Analysis Method</div>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 text-start">
                        <div class="mode-card" id="cardAi" onclick="selectMode('ai')">
                            <input type="radio" name="review_type" value="ai" id="modeAi" class="d-none" required>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="fs-2 text-gradient">🤖</span>
                                <h5 class="fw-black mb-0 text-main">AI Auto Analysis</h5>
                            </div>
                            <ul class="feature-list list-unstyled mb-4">
                                <li><i class="bi bi-check-circle-fill"></i> Instant feedback (5-10s)</li>
                                <li><i class="bi bi-check-circle-fill"></i> Gemini AI deep insights</li>
                                <li><i class="bi bi-check-circle-fill"></i> Smart keyword detection</li>
                            </ul>
                            <div class="badge bg-gold-gradient px-3 py-2 rounded-pill text-white fw-bold small shadow-sm w-100 text-center">INSTANT RESULT</div>
                        </div>
                    </div>
                    <div class="col-md-6 text-start">
                        <div class="mode-card" id="cardFaculty" onclick="selectMode('faculty')">
                            <input type="radio" name="review_type" value="faculty" id="modeFaculty" class="d-none" required>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="fs-2 text-gradient">👨‍🏫</span>
                                <h5 class="fw-black mb-0 text-main">Expert Faculty Review</h5>
                            </div>
                            <ul class="feature-list list-unstyled mb-4">
                                <li><i class="bi bi-check-circle-fill"></i> Personalized human feedback</li>
                                <li><i class="bi bi-check-circle-fill"></i> Industry specialization tips</li>
                                <li><i class="bi bi-check-circle-fill"></i> Comprehensive scoring</li>
                            </ul>
                            <div class="badge glass-pill border border-secondary border-opacity-10 px-3 py-2 rounded-pill text-muted fw-bold small w-100 text-center">HUMAN EXPERT</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div id="submitSection" class="d-none">
                <div id="submitInfoBox" class="glass-pill p-4 mb-4 border border-secondary border-opacity-10 text-start">
                    <div class="d-flex gap-3">
                        <div id="submitInfoIcon" class="fs-3 text-gradient"></div>
                        <div id="submitInfoText" class="small text-muted"></div>
                    </div>
                </div>
                <button type="submit" class="btn btn-gold w-100 shadow-lg py-3" id="submitBtn">
                    <i class="bi bi-shield-lock me-2"></i> START ANALYSIS
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingDiv" class="d-none">
    <div class="spinner-grow text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
    <h3 class="fw-black mb-2">Analyzing Resume...</h3>
    <p class="opacity-50">Gemini AI is examining your skills and experience.</p>
</div>

<!-- Review History -->
<div class="glass-card mb-5 border-0 shadow-sm rounded-4 reveal-on-scroll">
    <div class="p-4 border-bottom border-secondary border-opacity-10 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-gradient">
                <i class="bi bi-clock-history fs-4"></i>
            </div>
            <div>
                <div class="fw-black text-main small text-uppercase">Recent History</div>
                <div class="text-muted" style="font-size:0.75rem;">Your last 5 resume submissions</div>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/resume/history" class="btn btn-gold btn-sm px-4 rounded-pill shadow-sm">
            Full History <i class="bi bi-arrow-right-short ms-1"></i>
        </a>
    </div>

    <?php if (empty($reviews)): ?>
    <div class="p-5 text-center">
        <div class="p-4 bg-glass rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
            <i class="bi bi-file-earmark-text text-muted opacity-25" style="font-size: 3rem;"></i>
        </div>
        <h6 class="fw-black text-main">No Submissions Yet</h6>
        <p class="text-muted small">Upload your first resume to get expert feedback!</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table history-table mb-0 align-middle">
            <thead>
                <tr>
                    <th class="ps-4">Resume File</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Date</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody class="text-main">
                <?php foreach (array_slice($reviews, 0, 5) as $r): ?>
                <tr class="border-secondary border-opacity-10">
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-pdf-fill text-gradient fs-5"></i>
                            <span class="small fw-bold text-truncate" style="max-width:180px;"><?= htmlspecialchars($r['file_name']) ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge glass-pill text-muted small px-3">
                            <?= $r['review_type'] === 'ai' ? '🤖 Gemini AI' : '👨‍🏫 Expert' ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-<?= $r['status'] === 'completed' ? 'gold-gradient' : 'secondary' ?> text-white small px-3 rounded-pill">
                            <?= strtoupper($r['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        $sc = $r['review_type'] === 'ai' ? ($r['ai_score'] ?? null) : ($r['faculty_score'] ?? null);
                        if ($sc !== null): ?>
                        <span class="fw-black text-gradient fs-5"><?= $sc ?>%</span>
                        <?php else: ?><span class="text-muted small">Pending</span><?php endif; ?>
                    </td>
                    <td class="small text-muted fw-bold"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                    <td class="text-end pe-4">
                        <?php if ($r['status'] === 'completed'): ?>
                            <a href="<?= BASE_URL ?>/resume/<?= $r['review_type'] === 'ai' ? 'ai-result' : 'history' ?>/<?= $r['id'] ?>" class="btn btn-gold btn-sm px-3 rounded-pill fw-bold">
                                VIEW RESULTS
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm px-3 rounded-pill fw-bold opacity-50" disabled>WAITING</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
let selectedMode = null;

function selectMode(mode) {
    selectedMode = mode;
    document.getElementById('modeAi').checked     = (mode === 'ai');
    document.getElementById('modeFaculty').checked = (mode === 'faculty');

    // Card styles
    document.getElementById('cardAi').className     = 'mode-card ai-card'  + (mode === 'ai'      ? ' selected-ai' : '');
    document.getElementById('cardFaculty').className = 'mode-card fac-card' + (mode === 'faculty' ? ' selected-fac' : '');

    // Submit info
    const box  = document.getElementById('submitInfoBox');
    const icon = document.getElementById('submitInfoIcon');
    const text = document.getElementById('submitInfoText');
    const btn  = document.getElementById('submitBtn');

    if (mode === 'ai') {
        box.className = 'submit-info-box ai-mode glass-pill p-4 mb-4 border border-secondary border-opacity-10 text-start';
        icon.textContent = '🤖';
        text.innerHTML = '<strong style="color:#5a6acf;">AI Analysis Mode Selected!</strong><br><span style="font-size:0.85rem;opacity:0.8;">After submitting, Gemini AI will analyze your resume in seconds — providing overall score, ATS check, skills detection and detailed improvement suggestions!</span>';
        btn.className = 'btn btn-gold w-100 shadow-lg py-3';
        btn.innerHTML = '<i class="bi bi-cpu-fill me-2"></i>Analyze With AI';
    } else {
        box.className = 'submit-info-box fac-mode glass-pill p-4 mb-4 border border-secondary border-opacity-10 text-start';
        icon.textContent = '👨‍🏫';
        text.innerHTML = '<strong style="color:#11998e;">Expert Faculty Review Selected!</strong><br><span style="font-size:0.85rem;opacity:0.8;">Your resume will be assigned to a faculty member. You will receive a notification when the review is complete with personalized expert feedback!</span>';
        btn.className = 'btn btn-gold w-100 shadow-lg py-3';
        btn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Submit for Faculty Review';
    }

    const section = document.getElementById('submitSection');
    section.classList.remove('d-none');
    section.style.animation = 'fadeSlideIn 0.4s ease';
}

// File select preview
document.getElementById('resumeFile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    document.getElementById('uploadText').textContent = '✅ File Selected!';
    document.getElementById('filePreview').classList.remove('d-none');
    document.getElementById('fileName').textContent = file.name;
    const size = file.size < 1024*1024
        ? (file.size / 1024).toFixed(1) + ' KB'
        : (file.size / (1024*1024)).toFixed(2) + ' MB';
    document.getElementById('fileSize').textContent = size;
    document.getElementById('uploadZone').classList.add('dragover');
});

// Drag & drop
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    document.getElementById('resumeFile').files = e.dataTransfer.files;
    document.getElementById('resumeFile').dispatchEvent(new Event('change'));
});

// Submit loading
document.getElementById('resumeUploadForm').addEventListener('submit', function() {
    if (selectedMode === 'ai') {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Analyzing...';
        document.getElementById('loadingDiv').classList.remove('d-none');
    }
});
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
