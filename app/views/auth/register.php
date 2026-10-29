<?php $pageTitle = 'Register'; require $viewPath . 'layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-person-plus-fill fs-2"></i>
                    </div>
                    <h3 class="fw-bold">Join UniHelp</h3>
                    <p class="text-muted">Create your account to unlock career guidance.</p>
                </div>
                
                <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-sm d-flex align-items-center mb-4 rounded-3">
                    <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle-fill' : 'check-circle-fill' ?> me-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/register">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Full Name *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="full_name" class="form-control border-start-0 ps-0 bg-light" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="Enter full name" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Email Address *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0 bg-light" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Enter email" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0 bg-light" placeholder="Min 6 characters" required minlength="6">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Confirm Password *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key-fill text-muted"></i></span>
                                <input type="password" name="confirm_password" class="form-control border-start-0 ps-0 bg-light" placeholder="Repeat password" required minlength="6">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">I am a *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                            <select name="role" class="form-select border-start-0 ps-0 bg-light" id="roleSelect" required>
                                <option value="student" <?= ($_POST['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                                <option value="faculty" <?= ($_POST['role'] ?? '') === 'faculty' ? 'selected' : '' ?>>Faculty</option>
                                <option value="alumni" <?= ($_POST['role'] ?? '') === 'alumni' ? 'selected' : '' ?>>Alumni</option>
                            </select>
                        </div>
                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i> Alumni accounts require admin approval</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-phone text-muted"></i></span>
                                <input type="tel" name="phone" class="form-control border-start-0 ps-0 bg-light" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="Optional">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Department</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-building text-muted"></i></span>
                                <select name="department" class="form-select border-start-0 ps-0 bg-light text-muted">
                                    <option value="" class="text-muted">Select Department</option>
                                    <option value="CSE" <?= ($_POST['department'] ?? '') === 'CSE' ? 'selected' : '' ?>>CSE</option>
                                    <option value="IT" <?= ($_POST['department'] ?? '') === 'IT' ? 'selected' : '' ?>>IT</option>
                                    <option value="BCA" <?= ($_POST['department'] ?? '') === 'BCA' ? 'selected' : '' ?>>BCA</option>
                                    <option value="MCA" <?= ($_POST['department'] ?? '') === 'MCA' ? 'selected' : '' ?>>MCA</option>
                                    <option value="BSC IT" <?= ($_POST['department'] ?? '') === 'BSC IT' ? 'selected' : '' ?>>BSC IT</option>
                                    <option value="MSC IT" <?= ($_POST['department'] ?? '') === 'MSC IT' ? 'selected' : '' ?>>MSC IT</option>
                                    <option value="BBA" <?= ($_POST['department'] ?? '') === 'BBA' ? 'selected' : '' ?>>BBA</option>
                                    <option value="MBA" <?= ($_POST['department'] ?? '') === 'MBA' ? 'selected' : '' ?>>MBA</option>
                                    <option value="BSC (AI/ML)" <?= ($_POST['department'] ?? '') === 'BSC (AI/ML)' ? 'selected' : '' ?>>BSC (AI/ML)</option>
                                    <option value="MSC (AI/ML)" <?= ($_POST['department'] ?? '') === 'MSC (AI/ML)' ? 'selected' : '' ?>>MSC (AI/ML)</option>
                                    <option value="Other" <?= ($_POST['department'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="alumniFields" class="row" style="display: <?= ($_POST['role'] ?? '') === 'alumni' ? 'flex' : 'none' ?>">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Graduation Year</label>
                            <input type="number" name="graduation_year" class="form-control bg-light" value="<?= htmlspecialchars($_POST['graduation_year'] ?? '') ?>" min="1990" max="2030">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Current Company</label>
                            <input type="text" name="company" class="form-control bg-light" value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Designation</label>
                            <input type="text" name="designation" class="form-control bg-light" value="<?= htmlspecialchars($_POST['designation'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-4"></div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm">
                        Register Now <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
                <div class="text-center mt-4 pt-3 border-top">
                    <p class="mb-0 text-muted">Already have an account? <a href="<?= BASE_URL ?>/login" class="fw-bold">Login Here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('roleSelect').addEventListener('change', function() {
    document.getElementById('alumniFields').style.display = this.value === 'alumni' ? 'flex' : 'none';
});
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
