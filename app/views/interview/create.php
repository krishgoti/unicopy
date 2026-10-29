<?php $pageTitle = 'Share Interview Experience'; require $viewPath . 'layouts/header.php'; ?>

<style>
/* ── Back Button ── */
.back-pill {
    display: inline-flex; align-items: center; justify-content: center;
    width: 42px; height: 42px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    color: var(--text-main);
    transition: var(--transition);
    text-decoration: none;
}
.back-pill:hover { background: var(--primary); color: white; transform: translateX(-3px); }

/* ── Form Card ── */
.form-glass-card {
    background: var(--card-bg);
    border-radius: 28px;
    border: 1px solid var(--border-color);
    padding: 3rem;
    box-shadow: var(--shadow-xl);
    max-width: 850px;
    margin: 0 auto;
}

/* ── Form Styling ── */
.form-label-gold {
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--primary);
    margin-bottom: 0.75rem;
    display: block;
}
.input-gold-group {
    position: relative;
    margin-bottom: 1.5rem;
}
.input-gold {
    background: rgba(var(--primary-rgb), 0.05);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 12px 16px;
    color: var(--text-main);
    transition: var(--transition);
}
.input-gold:focus {
    background: var(--card-bg);
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
    outline: none;
}

.section-divider {
    height: 1px;
    background: linear-gradient(to right, var(--border-color) 0%, transparent 100%);
    margin: 2.5rem 0;
}

/* ── Question Rows ── */
.q-row {
    background: rgba(var(--primary-rgb), 0.03);
    border-radius: 14px;
    padding: 10px;
    border: 1px dashed var(--border-color);
    margin-bottom: 10px;
}
</style>

<div class="d-flex align-items-center gap-3 mb-5">
    <a href="<?= BASE_URL ?>/interview-experiences" class="back-pill">
        <i class="bi bi-arrow-left fs-5"></i>
    </a>
    <h2 class="mb-0 fw-black" style="letter-spacing:-1px;">Share Your Journey</h2>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-sm rounded-4 mb-4">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="form-glass-card">
    <form method="POST" action="<?= BASE_URL ?>/interview-experiences/post" enctype="multipart/form-data" id="experienceForm">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <!-- Experience Context -->
        <h6 class="form-label-gold">Interview Details</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Company Name *</label>
                <input type="text" name="company_name" class="form-control input-gold" placeholder="e.g. Google, Amazon" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Job Role *</label>
                <input type="text" name="role" class="form-control input-gold" placeholder="e.g. SDE-1" required>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Type</label>
                <select name="experience_type" class="form-select input-gold">
                    <option value="placement">Placement</option>
                    <option value="internship">Internship</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Difficulty</label>
                <select name="difficulty" class="form-select input-gold">
                    <option value="easy">Easy</option>
                    <option value="medium" selected>Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Major Advice (Key Tip)</label>
                <input type="text" name="tips" class="form-control input-gold" placeholder="Brush up on DSA...">
            </div>
        </div>

        <div class="mt-4">
            <label class="form-label small fw-bold">Overall Rounds & Process</label>
            <textarea name="description" class="form-control input-gold" rows="4" placeholder="Briefly explain the rounds (OA, Technical, HR)..."></textarea>
        </div>

        <div class="section-divider"></div>

        <!-- Questions -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="form-label-gold mb-0">Questions Asked</h6>
            <button type="button" class="btn btn-sm btn-gold rounded-pill px-3" id="addQuestionBtn">
                <i class="bi bi-plus-lg me-1"></i> Add Question
            </button>
        </div>
        <div id="questionsContainer"></div>
        
        <div class="mt-4">
            <div class="small text-muted mb-2"><i class="bi bi-info-circle me-1"></i> Alternatively, paste all questions here:</div>
            <textarea name="questions_text" class="form-control input-gold" rows="3" placeholder="Paste questions list here if you don't want to add rows..."></textarea>
        </div>

        <div class="section-divider"></div>

        <!-- Identity -->
        <h6 class="form-label-gold">Your Identity</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Public Display Name *</label>
                <input type="text" name="contributor_name" class="form-control input-gold" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Contact Email *</label>
                <input type="email" name="contributor_email" class="form-control input-gold" required value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Phone Number *</label>
                <input type="text" name="contributor_phone" class="form-control input-gold" required placeholder="+91 ...">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Profile Photo (Optional)</label>
                <input type="file" name="contributor_image" class="form-control input-gold" accept=".jpg,.jpeg,.png">
            </div>
        </div>

        <!-- Submission -->
        <div class="mt-5 pt-3 border-top d-flex gap-3 justify-content-end">
            <a href="<?= BASE_URL ?>/interview-experiences" class="btn btn-outline-secondary px-4 fw-bold rounded-pill">Cancel</a>
            <button type="submit" class="btn btn-gold px-5 py-2 fw-bold">Post Experience <i class="bi bi-send-fill ms-2"></i></button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('questionsContainer');
    const addBtn = document.getElementById('addQuestionBtn');
    
    if (addBtn && container) {
        const addQuestionRow = (value = '') => {
            const row = document.createElement('div');
            row.className = 'q-row d-flex gap-2 align-items-center animate-fade-in';
            row.innerHTML = `
                <div class="flex-grow-1">
                    <input type="text" class="form-control input-gold py-2" name="questions[]" placeholder="e.g. What is polymorphism?" value="${value.replace(/"/g, '&quot;')}">
                </div>
                <button type="button" class="btn text-danger py-1 px-2" title="Remove"><i class="bi bi-trash3-fill"></i></button>
            `;
            row.querySelector('button').addEventListener('click', () => row.remove());
            container.appendChild(row);
        };
        addBtn.addEventListener('click', () => addQuestionRow(''));
        addQuestionRow(''); // Initial row
    }
});
</script>

<style>
.animate-fade-in { animation: fadeIn 0.3s ease both; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<?php require $viewPath . 'layouts/footer.php'; ?>
