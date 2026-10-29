<?php $pageTitle = 'Login'; require $viewPath . 'layouts/header.php'; ?>

<div class="row w-100 justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-8 col-lg-5">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-5">
                 <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-shield-lock-fill fs-2"></i>
                    </div>
                    <h3 class="fw-bold">Welcome Back!</h3>
                    <p class="text-muted">Login with password or request a 6-digit OTP.</p>
                </div>
                
                <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : ($flash['type'] === 'warning' ? 'warning' : 'success') ?> border-0 shadow-sm d-flex align-items-center mb-4 rounded-3">
                    <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle-fill' : 'check-circle-fill' ?> me-2 fs-5"></i>
                    <div><?= htmlspecialchars($flash['message']) ?></div>
                </div>
                <?php endif; ?>

                <?php if (isset($_GET['rejected'])): ?>
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4 rounded-3">
                    <i class="bi bi-x-circle-fill me-2 fs-5"></i>
                    <div>Your registration has been rejected. Please contact admin.</div>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/login">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Select Role</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-badge text-muted"></i></span>
                            <select name="role" class="form-select border-start-0 ps-0 bg-light" required>
                                <option value="" disabled selected>Select your role</option>
                                <option value="student" <?= ($role ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                                <option value="faculty" <?= ($role ?? '') === 'faculty' ? 'selected' : '' ?>>Faculty</option>
                                <option value="alumni" <?= ($role ?? '') === 'alumni' ? 'selected' : '' ?>>Alumni</option>
                                <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" name="email" id="loginEmailInput" class="form-control border-start-0 ps-0 bg-light" placeholder="Enter your email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label fw-bold small text-uppercase text-muted mb-1">Password</label>
                            <a href="<?= BASE_URL ?>/forgot-password" class="text-decoration-none small fw-bold mb-1">Forgot Password?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                            <input type="password" name="password" class="form-control border-start-0 ps-0 bg-light" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm mb-3 rounded-3">
                        Login with Password <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>

                <!-- Alternative: Login via 6-Digit Email OTP -->
                <form method="POST" action="<?= BASE_URL ?>/send-otp" id="otpQuickForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="email" id="otpHiddenEmail">
                    <button type="button" class="btn btn-outline-primary w-100 py-2.5 fw-bold rounded-3" onclick="submitOtpLogin()">
                        <i class="bi bi-envelope-at me-2"></i> Get 6-Digit OTP for Login / Verification
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="mb-0 text-muted">Don't have an account? <a href="<?= BASE_URL ?>/register" class="fw-bold text-primary">Register Now</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function submitOtpLogin() {
    const emailInput = document.getElementById('loginEmailInput').value.trim();
    if (!emailInput) {
        alert('Please enter your email address first.');
        document.getElementById('loginEmailInput').focus();
        return;
    }
    document.getElementById('otpHiddenEmail').value = emailInput;
    document.getElementById('otpQuickForm').submit();
}
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
