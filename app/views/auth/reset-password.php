<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm mt-5">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h4 class="card-title">Reset Password</h4>
                    <p class="text-muted">Enter and confirm your new password below.</p>
                </div>

                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show">
                        <?php echo $_SESSION['flash']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_URL; ?>/reset-password/update" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6" autofocus>
                        </div>
                        <div class="form-text">Must be at least 6 characters long.</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary bg-gradient py-2">
                            Reset Password
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
