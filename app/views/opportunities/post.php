<?php $pageTitle = 'Post Opportunity'; require $viewPath . 'layouts/header.php'; ?>

<div class="row mb-5 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-black text-main mb-1" style="letter-spacing:-1px;">Publish New <span class="text-gradient-gold">Opportunity</span></h2>
        <p class="text-muted fw-bold text-uppercase small" style="letter-spacing:1px;">Recruitment Hub • Talent Acquisition</p>
    </div>
</div>

<div class="glass-card p-0 overflow-hidden border-0 shadow-lg rounded-4 transition-all mb-5">
    <div class="bg-main bg-opacity-5 p-4 border-bottom">
        <h5 class="fw-black text-main mb-0 small text-uppercase" style="letter-spacing:1px;"><i class="bi bi-pencil-square me-2 text-primary"></i>Listing Details</h5>
    </div>
    <div class="card-body p-5">
        <form method="POST" action="<?= BASE_URL ?>/opportunities/post">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Enterprise Name *</label>
                    <input type="text" name="company_name" class="form-control rounded-pill border-gold bg-transparent px-4 py-2 text-main" value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>" required placeholder="e.g. Google">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Professional Role *</label>
                    <input type="text" name="role" class="form-control rounded-pill border-gold bg-transparent px-4 py-2 text-main" value="<?= htmlspecialchars($_POST['role'] ?? '') ?>" required placeholder="e.g. Software Engineer">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Category</label>
                    <select name="type" class="form-select rounded-pill border-gold bg-transparent px-4 py-2 text-main">
                        <option value="placement" <?= ($_POST['type'] ?? '') === 'placement' ? 'selected' : '' ?> class="bg-card">Placement</option>
                        <option value="internship" <?= ($_POST['type'] ?? '') === 'internship' ? 'selected' : '' ?> class="bg-card">Internship</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Remuneration (LPA)</label>
                    <input type="text" name="package" class="form-control rounded-pill border-gold bg-transparent px-4 py-2 text-main" value="<?= htmlspecialchars($_POST['package'] ?? '') ?>" placeholder="e.g. 12 LPA">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Deployment Base</label>
                    <input type="text" name="location" class="form-control rounded-pill border-gold bg-transparent px-4 py-2 text-main" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" placeholder="e.g. Bangalore">
                </div>

                <div class="col-12 mt-5">
                    <h6 class="text-uppercase fw-black text-primary small mb-3 border-bottom pb-2" style="letter-spacing: 2px;">Eligibility Standards</h6>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label text-main small fw-bold">Critical CGPA Tier</label>
                            <input type="number" step="0.01" name="min_cgpa" class="form-control rounded-pill border bg-transparent px-4" value="<?= htmlspecialchars($_POST['min_cgpa'] ?? '0') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-main small fw-bold">Academic Base (10th %)</label>
                            <input type="number" step="0.01" name="min_10th" class="form-control rounded-pill border bg-transparent px-4" value="<?= htmlspecialchars($_POST['min_10th'] ?? '0') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-main small fw-bold">Intermediate Tier (12th %)</label>
                            <input type="number" step="0.01" name="min_12th" class="form-control rounded-pill border bg-transparent px-4" value="<?= htmlspecialchars($_POST['min_12th'] ?? '0') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-main small fw-bold">Target Faculty Domains (Comma separated)</label>
                            <input type="text" name="allowed_depts" class="form-control rounded-pill border bg-transparent px-4" value="<?= htmlspecialchars($_POST['allowed_depts'] ?? '') ?>" placeholder="e.g. CSE, IT, ECE">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-main small fw-bold">Prerequisite Skillsets (Comma separated)</label>
                            <input type="text" name="required_skills" class="form-control rounded-pill border bg-transparent px-4" value="<?= htmlspecialchars($_POST['required_skills'] ?? '') ?>" placeholder="e.g. Java, Python, React">
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-5">
                    <h6 class="text-uppercase fw-black text-primary small mb-3 border-bottom pb-2" style="letter-spacing: 2px;">Functional Narrative</h6>
                    <div class="mb-4">
                        <label class="form-label text-main small fw-bold">Role Description</label>
                        <textarea name="description" class="form-control rounded-4 border bg-transparent p-4 text-main" rows="4" placeholder="Briefly describe the mission and company culture..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="form-label text-main small fw-bold">Key Benchmarks & Perks</label>
                        <textarea name="requirements" class="form-control rounded-4 border bg-transparent p-4 text-main" rows="3" placeholder="Additional requirements or benefits..."><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="col-md-6 mt-5">
                    <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Application Cut-off</label>
                    <input type="date" name="apply_deadline" class="form-control rounded-pill border bg-transparent px-4 py-2 text-main" value="<?= htmlspecialchars($_POST['apply_deadline'] ?? '') ?>">
                </div>

                <div class="col-12 mt-5 pt-4 text-end">
                    <hr class="opacity-10 mb-4">
                    <a href="<?= BASE_URL ?>/opportunities" class="btn btn-outline-secondary rounded-pill px-5 py-2 fw-black me-2 border-0">DISCARD</a>
                    <button type="submit" class="btn btn-gold rounded-pill px-5 py-3 fw-black shadow">PUBLISH VACANCY <i class="bi bi-rocket-takeoff ms-2"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require $viewPath . 'layouts/footer.php'; ?>
