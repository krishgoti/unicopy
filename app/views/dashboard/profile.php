<?php $pageTitle = 'Edit Profile'; require $viewPath . 'layouts/header.php'; ?>

<div class="row align-items-center mb-5">
    <div class="col-md-8">
        <h1 class="fw-black text-primary mb-1">Account & Academic Profile</h1>
        <p class="text-muted lead mb-0">Manage your identity and academic details securely.</p>
    </div>
</div>

<div class="row g-5">
    <!-- Left: Profile Summary -->
    <div class="col-lg-4 text-center">
        <div class="glass-stat-card p-5 border-0 shadow-sm rounded-4 bg-white text-center">
            <?php if (!empty($profile['profile_image'])): ?>
                <img src="<?= BASE_URL ?>/public/uploads/profiles/<?= htmlspecialchars($profile['profile_image']) ?>" alt="Profile" class="rounded-circle mx-auto mb-4 border shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
            <?php else: ?>
                <div class="avatar-circle bg-primary bg-opacity-10 text-primary mx-auto mb-4 border" style="width: 120px; height: 120px; font-size: 3rem; display: flex; align-items: center; justify-content: center;">
                    <?= strtoupper(substr($profile['full_name'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            <h4 class="fw-black mb-1"><?= htmlspecialchars($profile['full_name']) ?></h4>
            <p class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold text-uppercase mb-4"><?= $profile['role'] ?></p>
            <div class="text-start mt-4">
                <div class="small fw-bold text-muted mb-1">EMAIL</div>
                <div class="fw-bold mb-3"><?= htmlspecialchars($profile['email']) ?></div>
                <div class="small fw-bold text-muted mb-1">ACCOUNT STATUS</div>
                <div><span class="badge bg-success rounded-pill px-3 py-1">Verified User</span></div>
            </div>
        </div>
    </div>

    <!-- Right: Update Form -->
    <div class="col-lg-8">
        <div class="glass-stat-card p-5 border-0 shadow-sm rounded-4 bg-white">
            <form method="POST" action="<?= BASE_URL ?>/dashboard/profile" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <!-- Basic Info -->

                <h5 class="fw-black mb-4 border-bottom pb-3"><i class="bi bi-person-circle text-primary me-2"></i>Personal Information</h5>
                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">Profile Image <span class="fw-normal">(Optional)</span></label>
                        <input type="file" name="profile_image" class="form-control rounded-pill border-light bg-light bg-opacity-10 px-4 py-2" accept="image/jpeg, image/png, image/webp">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Full Name</label>
                        <input type="text" name="full_name" class="form-control rounded-pill border-light bg-light bg-opacity-10 px-4 py-3" value="<?= htmlspecialchars($profile['full_name']) ?>" required placeholder="Enter your full name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Phone Number</label>
                        <input type="text" name="phone" class="form-control rounded-pill border-light bg-light bg-opacity-10 px-4 py-3" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" placeholder="e.g. +91 98765 43210">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Department</label>
                        <select name="department" class="form-select rounded-pill border-light bg-light bg-opacity-10 px-4 py-3">
                            <option value="">Select Department</option>
                            <?php 
                            $depts = ['CSE', 'IT', 'BCA', 'MCA', 'BSC IT', 'MSC IT', 'BBA', 'MBA', 'BSC (AI/ML)', 'MSC (AI/ML)', 'Other'];
                            foreach($depts as $d): ?>
                                <option value="<?= htmlspecialchars($d) ?>" <?= $profile['department'] === $d ? 'selected' : '' ?>><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">Short Bio</label>
                        <textarea name="bio" class="form-control rounded-4 border-light bg-light bg-opacity-10 px-4 py-3" rows="3" placeholder="Tell us a little about yourself, your skills, and aspirations..."><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Academic Specs (Only for Students) -->
                <?php if ($profile['role'] === 'student'): ?>
                <h5 class="fw-black mb-4 border-bottom pb-3 mt-5"><i class="bi bi-award-fill text-warning me-2"></i>Academic & Career Credentials</h5>
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Degree Level</label>
                        <select name="degree_level" class="form-select rounded-pill border-light bg-light bg-opacity-10 px-4 py-3">
                            <option value="">Select Level</option>
                            <option value="Graduate" <?= ($profile['degree_level'] ?? '') === 'Graduate' ? 'selected' : '' ?>>Graduate</option>
                            <option value="Post Graduate" <?= ($profile['degree_level'] ?? '') === 'Post Graduate' ? 'selected' : '' ?>>Post Graduate</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Current Semester</label>
                        <select name="current_semester" class="form-select rounded-pill border-light bg-light bg-opacity-10 px-4 py-3">
                            <option value="">Select Semester</option>
                            <?php for($i=1; $i<=8; $i++): ?>
                                <option value="<?= $i ?>" <?= ($profile['current_semester'] ?? '') == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Career Goal / Ambition</label>
                        <select name="career_goal" class="form-select rounded-pill border-light bg-light bg-opacity-10 px-4 py-3">
                            <option value="software" <?= ($profile['career_goal'] ?? '') == 'software' ? 'selected' : '' ?>>Software Engineering / IT</option>
                            <option value="government" <?= ($profile['career_goal'] ?? '') == 'government' ? 'selected' : '' ?>>Civil Services / Gate</option>
                            <option value="mba" <?= ($profile['career_goal'] ?? '') == 'mba' ? 'selected' : '' ?>>Management (CAT/MBA)</option>
                            <option value="core" <?= ($profile['career_goal'] ?? '') == 'core' ? 'selected' : '' ?>>Core Industry / PSU</option>
                            <option value="startup" <?= ($profile['career_goal'] ?? '') == 'startup' ? 'selected' : '' ?>>Startup & Entrepreneurship</option>
                            <option value="research" <?= ($profile['career_goal'] ?? '') == 'research' ? 'selected' : '' ?>>Higher Ed / Research</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">CGPA (Upto Current Sem)</label>
                        <input type="number" step="0.01" name="cgpa" class="form-control rounded-pill border-light bg-light bg-opacity-10 px-4 py-3" value="<?= htmlspecialchars($profile['cgpa'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">10th %</label>
                        <input type="number" step="0.01" name="tenth_percentage" class="form-control rounded-pill border-light bg-light bg-opacity-10 px-4 py-3" value="<?= htmlspecialchars($profile['tenth_percentage'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">12th %</label>
                        <input type="number" step="0.01" name="twelfth_percentage" class="form-control rounded-pill border-light bg-light bg-opacity-10 px-4 py-3" value="<?= htmlspecialchars($profile['twelfth_percentage'] ?? '') ?>">
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-12 mt-5 py-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-sm">
                        <i class="bi bi-save2 me-2"></i>SAVE CHANGES
                    </button>
                    <a href="<?= BASE_URL ?>/dashboard/<?= Auth::role() ?>" class="btn btn-light rounded-pill px-4 py-3 fw-bold ms-2">CANCEL</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require $viewPath . 'layouts/footer.php'; ?>
