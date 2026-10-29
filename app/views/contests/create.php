<?php $pageTitle = 'Create Weekly Contest'; require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center mb-4">
            <a href="<?= BASE_URL ?>/contests" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="fw-bold mb-0">Create New Weekly Contest</h2>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm mb-4">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/contests/create" method="POST" id="contestForm">
            <input type="hidden" name="csrf_token" value="<?= $this->csrfToken() ?>">
            
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 border-bottom pb-2">Contest Details</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Contest Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Week 12: Data Structures Challenge" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief instruction for students..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Time *</label>
                            <input type="datetime-local" name="start_time" class="form-control" value="<?= $defaultStart ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Time *</label>
                            <input type="datetime-local" name="end_time" class="form-control" value="<?= $defaultEnd ?>" required>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="alert alert-info py-2 px-3 small border-0 mb-0">
                                <i class="bi bi-info-circle me-1"></i> Week Identifier: <strong><?= $weekKey ?></strong>. Only one contest per week is allowed.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="questionsContainer">
                <!-- Question 1 (Default) -->
                <div class="question-card card shadow-sm border-0 rounded-4 mb-3" data-q-index="0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-primary">Question #1</h6>
                            <div class="d-flex gap-2 align-items-center">
                                <label class="small text-muted mb-0">Marks:</label>
                                <input type="number" name="questions[0][marks]" class="form-control form-control-sm" value="1" min="1" style="width: 60px;">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-question" style="display:none;"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Question Text</label>
                            <textarea name="questions[0][text]" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Type</label>
                                <select name="questions[0][type]" class="form-select q-type-select">
                                    <option value="mcq">MCQ (Multiple Choice)</option>
                                    <option value="short">Short Answer / Concept</option>
                                    <option value="coding">Coding Logic</option>
                                </select>
                            </div>
                        </div>

                        <!-- MCQ Fields -->
                        <div class="mcq-section mt-3">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">A</span>
                                        <input type="text" name="questions[0][option_a]" class="form-control" placeholder="Option A">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">B</span>
                                        <input type="text" name="questions[0][option_b]" class="form-control" placeholder="Option B">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">C</span>
                                        <input type="text" name="questions[0][option_c]" class="form-control" placeholder="Option C">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">D</span>
                                        <input type="text" name="questions[0][option_d]" class="form-control" placeholder="Option D">
                                    </div>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <label class="form-label small fw-bold">Correct Choice</label>
                                    <select name="questions[0][correct_option]" class="form-select form-select-sm">
                                        <option value="A">Option A</option>
                                        <option value="B">Option B</option>
                                        <option value="C">Option C</option>
                                        <option value="D">Option D</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Text Answer Section (Hidden for MCQs) -->
                        <div class="text-section mt-3" style="display:none;">
                            <label class="form-label small fw-bold">Correct Answer(s)</label>
                            <textarea name="questions[0][correct_text]" class="form-control" rows="2" placeholder="Enter the exact correct answer. For multiple accepted answers, put each on a new line."></textarea>
                            <small class="text-muted">Auto-evaluated (case-insensitive, whitespace normalized).</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 mt-4">
                <button type="button" id="addQuestionBtn" class="btn btn-outline-primary fw-bold">
                    <i class="bi bi-plus-lg me-1"></i> Add Question
                </button>
                <button type="submit" class="btn btn-success fw-bold px-5 ms-auto">
                    Create Contest
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('questionsContainer');
    const addBtn = document.getElementById('addQuestionBtn');
    let qCount = 1;

    addBtn.addEventListener('click', function() {
        const newCard = container.querySelector('.question-card').cloneNode(true);
        newCard.dataset.qIndex = qCount;
        
        // Update names
        newCard.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/questions\[\d+\]/, `questions[${qCount}]`);
            if (el.tagName !== 'SELECT' && el.type !== 'number') el.value = '';
        });

        // Reset display
        newCard.querySelector('.mcq-section').style.display = 'block';
        newCard.querySelector('.text-section').style.display = 'none';
        newCard.querySelector('.remove-question').style.display = 'block';
        newCard.querySelector('h6').textContent = `Question #${qCount + 1}`;

        container.appendChild(newCard);
        qCount++;
        attachEvents(newCard);
    });

    function attachEvents(card) {
        const typeSelect = card.querySelector('.q-type-select');
        const mcqSec = card.querySelector('.mcq-section');
        const textSec = card.querySelector('.text-section');
        const removeBtn = card.querySelector('.remove-question');

        typeSelect.addEventListener('change', function() {
            if (this.value === 'mcq') {
                mcqSec.style.display = 'block';
                textSec.style.display = 'none';
            } else {
                mcqSec.style.display = 'none';
                textSec.style.display = 'block';
            }
        });

        removeBtn.addEventListener('click', function() {
            card.remove();
        });
    }

    // Attach to first one
    attachEvents(container.querySelector('.question-card'));
});
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
