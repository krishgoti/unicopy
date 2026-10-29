<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm mt-5">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h4 class="card-title">Forgot Password</h4>
                    <p class="text-muted">Enter your registered email address and we'll send you a link to reset your password.</p>
                </div>

                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show">
                        <?php echo $_SESSION['flash']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_URL; ?>/forgot-password/send" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="mb-4">
                        <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required autofocus>
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary bg-gradient py-2">
                            Send Reset Link
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="mb-0">Remembered your password? <a href="<?php echo BASE_URL; ?>/login" class="text-decoration-none">Back to Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
