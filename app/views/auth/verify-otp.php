<?php 
$pageTitle = 'Enter OTP Verification Code'; 
require $viewPath . 'layouts/header.php'; 

$otpRemaining = 60;
if (isset($_SESSION['otp_created_at'], $_SESSION['otp_lifetime'])) {
    $elapsed = time() - $_SESSION['otp_created_at'];
    $otpRemaining = max(0, (int)$_SESSION['otp_lifetime'] - $elapsed);
}
$isProduction = (strtolower((string)Env::get('APP_ENV')) === 'production');
$devOtp = (!$isProduction && isset($_SESSION['dev_otp'])) ? $_SESSION['dev_otp'] : null;
?>

<div class="row w-100 justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-md-8 col-lg-5">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-shield-lock-fill fs-1"></i>
                    </div>
                    <h3 class="fw-bold">Enter Verification Code</h3>
                    <p class="text-muted small">We sent a 6-digit OTP code to <br><strong class="text-dark"><?= htmlspecialchars($email ?? ($_SESSION['pending_otp_email'] ?? '')) ?></strong></p>
                </div>
                
                <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : ($flash['type'] === 'warning' ? 'warning' : 'success') ?> border-0 shadow-sm d-flex align-items-center mb-4 rounded-3">
                    <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle-fill' : 'check-circle-fill' ?> me-2 fs-5"></i>
                    <div><?= htmlspecialchars($flash['message']) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($devOtp): ?>
                <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check fs-4 text-primary"></i>
                        <div>
                            <span class="fw-bold small d-block text-dark">Local Testing OTP Code:</span>
                            <span class="fs-4 fw-bold text-primary font-monospace" style="letter-spacing: 4px;" id="devOtpDisplay"><?= htmlspecialchars($devOtp) ?></span>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">(SMTP was unavailable. Use this code to test verification)</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary fw-bold px-3 py-2 shadow-sm rounded-pill" onclick="autoFillOtp('<?= htmlspecialchars($devOtp) ?>')">
                        <i class="bi bi-box-arrow-in-down me-1"></i> Autofill
                    </button>
                </div>
                <?php endif; ?>

                <!-- 60-Second Countdown Timer Badge -->
                <div class="text-center mb-4">
                    <div class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2" id="otpTimerBadge">
                        <i class="bi bi-stopwatch text-danger fs-6"></i>
                        <span class="small">Time remaining to log in: <strong id="otpCountdown" class="text-danger fs-6"><?= $otpRemaining ?>s</strong></span>
                    </div>
                    <div id="otpExpiredNotice" class="text-danger small mt-2 fw-bold <?= $otpRemaining <= 0 ? '' : 'd-none' ?>">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> OTP code has expired! Please click "Resend OTP Code" below.
                    </div>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/verify-otp" id="otpForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? ($_SESSION['pending_otp_email'] ?? '')) ?>">

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted text-center d-block mb-3">6-Digit Security OTP</label>
                        <div class="d-flex justify-content-center gap-2" id="otpInputGroup">
                            <input type="text" maxlength="1" pattern="[0-9]" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 60px; border-radius: 10px;" required autofocus>
                            <input type="text" maxlength="1" pattern="[0-9]" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 60px; border-radius: 10px;" required>
                            <input type="text" maxlength="1" pattern="[0-9]" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 60px; border-radius: 10px;" required>
                            <input type="text" maxlength="1" pattern="[0-9]" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 60px; border-radius: 10px;" required>
                            <input type="text" maxlength="1" pattern="[0-9]" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 60px; border-radius: 10px;" required>
                            <input type="text" maxlength="1" pattern="[0-9]" class="form-control text-center fs-3 fw-bold otp-digit" style="width: 50px; height: 60px; border-radius: 10px;" required>
                        </div>
                        <input type="hidden" name="otp_code" id="fullOtpCode">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm rounded-3 fs-6" id="verifySubmitBtn" <?= $otpRemaining <= 0 ? 'disabled' : '' ?>>
                        Verify Code & Login <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                    <a href="<?= BASE_URL ?>/login" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Login</a>
                    
                    <form method="POST" action="<?= BASE_URL ?>/resend-otp" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? ($_SESSION['pending_otp_email'] ?? '')) ?>">
                        <button type="submit" class="btn btn-link p-0 text-decoration-none small fw-bold text-primary" id="resendOtpBtn">
                            <i class="bi bi-arrow-clockwise me-1"></i> Resend OTP Code
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.autoFillOtp = function(code) {
    if (!code || code.length !== 6) return;
    const digits = document.querySelectorAll('.otp-digit');
    code.split('').forEach((char, i) => {
        if (digits[i]) digits[i].value = char;
    });
    if (digits[5]) digits[5].focus();
    const fullOtpInput = document.getElementById('fullOtpCode');
    if (fullOtpInput) fullOtpInput.value = code;
};

document.addEventListener('DOMContentLoaded', function() {
    const digits = Array.from(document.querySelectorAll('.otp-digit'));
    const fullOtpInput = document.getElementById('fullOtpCode');
    const otpForm = document.getElementById('otpForm');

    // 60-Second Timer Logic
    let otpRemaining = <?= (int)$otpRemaining ?>;
    const countdownEl = document.getElementById('otpCountdown');
    const expiredNotice = document.getElementById('otpExpiredNotice');
    const verifyBtn = document.getElementById('verifySubmitBtn');
    const resendBtn = document.getElementById('resendOtpBtn');

    function updateOtpTimer() {
        if (otpRemaining > 0) {
            if (countdownEl) countdownEl.textContent = otpRemaining + 's';
            if (resendBtn) {
                resendBtn.disabled = true;
                resendBtn.style.pointerEvents = 'none';
                resendBtn.style.opacity = '0.6';
                resendBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Resend in ' + otpRemaining + 's';
            }
            otpRemaining--;
        } else {
            if (countdownEl) countdownEl.textContent = '0s';
            if (expiredNotice) expiredNotice.classList.remove('d-none');
            if (verifyBtn) {
                verifyBtn.disabled = true;
                verifyBtn.classList.add('opacity-50');
            }
            if (resendBtn) {
                resendBtn.disabled = false;
                resendBtn.style.pointerEvents = 'auto';
                resendBtn.style.opacity = '1';
                resendBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Resend OTP Code';
            }
            clearInterval(otpTimerInterval);
        }
    }
    updateOtpTimer();
    const otpTimerInterval = setInterval(updateOtpTimer, 1000);

    digits.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value.length === 1) {
                if (index < digits.length - 1) {
                    digits[index + 1].focus();
                }
            }
            updateFullCode();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                digits[index - 1].focus();
            }
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
            if (/^\d{6}$/.test(pasteData)) {
                pasteData.split('').forEach((char, i) => {
                    if (digits[i]) digits[i].value = char;
                });
                digits[digits.length - 1].focus();
                updateFullCode();
            }
        });
    });

    function updateFullCode() {
        fullOtpInput.value = digits.map(d => d.value).join('');
    }

    otpForm.addEventListener('submit', function(e) {
        updateFullCode();
        if (fullOtpInput.value.length !== 6) {
            e.preventDefault();
            alert('Please enter all 6 digits of the OTP code.');
        }
    });
});
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
