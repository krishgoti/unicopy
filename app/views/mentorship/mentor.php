<?php $pageTitle = 'Mentor Dashboard'; require $viewPath . 'layouts/header.php'; ?>

<style>
/* ── Mentor Dashboard Hero ── */
.mentor-hero-premium {
    background: var(--hero-bg-gradient);
    border-radius: 32px;
    padding: 3rem;
    color: white;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}
.mentor-hero-premium::after {
    content: '👑';
    position: absolute; right: -20px; bottom: -20px;
    font-size: 8rem; opacity: 0.1; transform: rotate(-15deg);
}

.mentor-avatar-wrap {
    width: 110px; height: 110px;
    border-radius: 28px;
    padding: 4px;
    background: var(--gold-gradient);
    box-shadow: var(--shadow-lg);
}
.mentor-avatar-wrap img {
    width: 100%; height: 100%;
    border-radius: 24px;
    object-fit: cover;
    background: var(--card-bg);
    border: 3px solid var(--card-bg);
}

/* ── Custom Vertical Nav ── */
.nav-pills-luxury-vertical {
    background: var(--card-bg);
    padding: 12px;
    border-radius: 24px;
    border: 1px solid var(--border-color);
}
.nav-pills-luxury-vertical .nav-link {
    border-radius: 16px;
    padding: 14px 20px;
    font-weight: 800;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    transition: var(--transition);
    text-align: left;
    margin-bottom: 6px;
    border: 1px solid transparent;
}
.nav-pills-luxury-vertical .nav-link i { font-size: 1.1rem; margin-right: 12px; }
.nav-pills-luxury-vertical .nav-link.active {
    background: var(--gold-gradient);
    color: white;
    border-color: transparent;
    box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.2);
}
.nav-pills-luxury-vertical .nav-link:hover:not(.active) {
    background: rgba(var(--primary-rgb), 0.05);
    color: var(--primary);
    border-color: var(--border-color);
}

/* ── Content Cards ── */
.glass-item-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 24px;
    padding: 1.75rem;
    margin-bottom: 1rem;
    transition: var(--transition);
}
.glass-item-card:hover { border-color: var(--primary); transform: translateY(-3px); }

.status-label-gold {
    font-size: 0.6rem;
    font-weight: 900;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 50px;
    letter-spacing: 0.5px;
}
</style>

<div class="mentor-hero-premium">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
        <div class="d-flex align-items-center gap-4">
            <div class="mentor-avatar-wrap">
                <img src="<?= !empty($profile['avatar_url']) ? htmlspecialchars($profile['avatar_url']) : 'https://ui-avatars.com/api/?name='.urlencode($user['full_name'] ?? 'Mentor').'&background=00010D&color=D9C0A3&size=200' ?>" alt="Mentor">
            </div>
            <div>
                <h2 class="fw-black mb-1 text-white" style="letter-spacing:-1.5px;"><?= htmlspecialchars($user['full_name'] ?? 'Your Name') ?></h2>
                <p class="mb-0 opacity-75 fs-5 fw-bold"><?= htmlspecialchars($user['designation'] ?? 'Expert Mentor') ?> @ <?= htmlspecialchars($user['company'] ?? 'UniHelp') ?></p>
                <div class="mt-3">
                   <span class="badge bg-gold-gradient text-white px-3 py-2 rounded-pill fw-bold shadow-sm" style="font-size: 0.7rem;">
                     👑 VERIFIED <?= strtoupper($user['role'] ?? 'Mentor') ?>
                   </span>
                </div>
            </div>
        </div>
        
        <div class="p-4 bg-white bg-opacity-10 rounded-4 text-center border border-white border-opacity-20 backdrop-blur-10">
             <label class="d-block small text-white opacity-75 mb-2 fw-black text-uppercase" style="letter-spacing:1px;">Mentorship Status</label>
             <form method="POST" action="<?= BASE_URL ?>/mentorship/toggle-availability">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="is_available" value="<?= (!empty($profile['is_available']) ? '0' : '1') ?>">
                <button type="submit" class="btn <?= (!empty($profile['is_available']) ? 'btn-success' : 'btn-outline-light') ?> btn-sm px-4 py-2 rounded-pill fw-black shadow-sm">
                    <?= (!empty($profile['is_available']) ? '🟢 ACTIVE' : '🔴 PAUSED') ?>
                </button>
             </form>
        </div>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-sm rounded-4 mb-5">
        <i class="bi bi-info-circle-fill me-2"></i><?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Sidebar: Navigation -->
    <div class="col-lg-3">
        <div class="nav-pills-luxury-vertical nav flex-column" id="v-pills-tab" role="tablist">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-requests"><i class="bi bi-inbox-fill"></i> Requests (<?= count($requests) ?>)</button>
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-sessions"><i class="bi bi-calendar-check-fill"></i> My Sessions</button>
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-profile"><i class="bi bi-person-badge-fill"></i> Edit Profile</button>
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-slots"><i class="bi bi-clock-fill"></i> Manage Slots</button>
        </div>
    </div>

    <!-- Main Content: Tabs -->
    <div class="col-lg-9">
        <div class="tab-content" style="background:transparent; border:none; padding:0;">
            
            <!-- Incoming Requests -->
            <div class="tab-pane fade show active" id="tab-requests">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="fw-black text-main mb-0" style="letter-spacing:-1px;">Mentees Seeking Guidance</h4>
                </div>
                
                <?php if (empty($requests)): ?>
                <div class="p-5 text-center bg-card border rounded-4">
                     <i class="bi bi-chat-heart fs-1 text-primary opacity-25 mb-3 d-block"></i>
                     <p class="text-muted fw-bold mb-0">Your inbox is quiet. Share your expertise to attract mentees!</p>
                </div>
                <?php else: ?>
                    <?php foreach($requests as $req): ?>
                    <div class="glass-item-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4 fw-black fs-4">
                                        <?= strtoupper(substr($req['student_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-main"><?= htmlspecialchars($req['student_name']) ?></h6>
                                        <div class="small text-muted"><i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($req['department']) ?> Student</div>
                                    </div>
                                    <span class="status-label-gold pill-<?= $req['status'] ?> ms-auto ms-md-2"><?= strtoupper($req['status']) ?></span>
                                </div>
                                <div class="p-3 mb-3 rounded-4" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border-color);">
                                    <p class="mb-0 text-muted fst-italic small">"<?= htmlspecialchars($req['message']) ?: 'Request for mentorship guidance...' ?>..."</p>
                                </div>
                                <div class="small text-muted fw-bold" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Requested • <?= date('d M, h:i A', strtotime($req['created_at'])) ?></div>
                            </div>
                            <div class="col-md-4 text-md-end mt-4 mt-md-0">
                                <?php if ($req['status'] === 'pending'): ?>
                                <button class="btn btn-gold btn-sm px-4 fw-bold rounded-pill" data-bs-toggle="collapse" data-bs-target="#replyForm<?= $req['id'] ?>">Analyze & Accept</button>
                                <form method="POST" action="<?= BASE_URL ?>/mentorship/respond" class="d-inline" onsubmit="return confirm('Decline this request?')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                    <button name="action" value="reject" class="btn btn-outline-danger btn-sm border-0 px-3 fw-bold">Decline</button>
                                </form>
                                <?php else: ?>
                                    <a href="mailto:<?= htmlspecialchars($req['student_email']) ?>" class="btn btn-outline-primary btn-sm px-4 rounded-pill fw-bold">Email Mentee</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Reply Form -->
                        <div class="collapse" id="replyForm<?= $req['id'] ?>">
                            <form method="POST" action="<?= BASE_URL ?>/mentorship/respond" class="mt-4 pt-4 border-top">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                <input type="hidden" name="action" value="accept">
                                <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.65rem; letter-spacing:1px;">Welcome Message</label>
                                <textarea name="reply" class="form-control rounded-4 border p-3 text-main mb-3" rows="3" style="background: rgba(var(--primary-rgb), 0.02);" placeholder="Hi! I'd love to help with your career goals. Let's start with a general consultation..."></textarea>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-4 rounded-pill fw-bold" data-bs-toggle="collapse" data-bs-target="#replyForm<?= $req['id'] ?>">Cancel</button>
                                    <button type="submit" class="btn btn-gold btn-sm px-4 rounded-pill fw-black">Accept Request</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Session Bookings -->
            <div class="tab-pane fade" id="tab-sessions">
                <h4 class="fw-black text-main mb-4" style="letter-spacing:-1px;">Your Private Sessions</h4>
                 <?php if (empty($slots)): ?>
                    <div class="p-5 text-center bg-card border rounded-4">
                        <i class="bi bi-calendar-week fs-1 text-primary opacity-25 d-block mb-3"></i>
                        <p class="text-muted fw-bold mb-4">You haven't defined any session slots for mentees.</p>
                        <button class="btn btn-gold px-4 py-2 rounded-pill fw-black" data-bs-toggle="pill" data-bs-target="#tab-slots">Establish Availability</button>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($slots as $s): ?>
                            <div class="col-12">
                                <div class="glass-item-card d-flex align-items-center justify-content-between flex-wrap gap-4" style="<?= $s['is_booked'] ? 'background: rgba(16, 185, 129, 0.03); border-color: rgba(16, 185, 129, 0.2);' : '' ?>">
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="bg-gold-gradient text-white p-3 px-4 rounded-4 text-center" style="min-width: 75px;">
                                            <div class="fw-black fs-4 lh-1"><?= date('d', strtotime($s['slot_date'])) ?></div>
                                            <div class="small fw-bold text-uppercase" style="font-size: 0.65rem;"><?= date('M', strtotime($s['slot_date'])) ?></div>
                                        </div>
                                        <div>
                                            <h6 class="fw-black text-main mb-1" style="font-size: 1.15rem;"><?= date('h:i A', strtotime($s['slot_time'])) ?> <span class="text-muted fs-6 fw-bold">/ <?= $s['duration'] ?> MIN</span></h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if ($s['is_booked']): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 fw-black" style="font-size: 0.65rem;"><i class="bi bi-person-fill"></i> OCCUPIED BY <?= strtoupper($s['student_name']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 fw-black" style="font-size: 0.65rem;"><i class="bi bi-unlock-fill"></i> AVAILABLE POOL</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ms-md-auto">
                                        <?php if ($s['is_booked']): ?>
                                             <a href="mailto:<?= htmlspecialchars($s['student_email']) ?>" class="btn btn-gold btn-sm px-4 rounded-pill fw-black">Contact Mentee</a>
                                        <?php else: ?>
                                            <form method="POST" action="<?= BASE_URL ?>/mentorship/delete-slot" onsubmit="return confirm('Note: Deleting active slots might affect mentee scheduling. Continue?')">
                                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                                <input type="hidden" name="slot_id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm border-0 px-4 fw-bold"><i class="bi bi-trash-fill me-2"></i>Remove</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Profile Management -->
            <div class="tab-pane fade" id="tab-profile">
                <div class="glass-item-card p-0 overflow-hidden shadow-sm">
                    <div class="bg-main bg-opacity-5 p-4 border-bottom">
                        <h4 class="fw-black text-main mb-0" style="letter-spacing:-1px;">Mentor Identity Profile</h4>
                    </div>
                    <div class="p-5">
                        <form method="POST" action="<?= BASE_URL ?>/mentorship/save-profile" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <div class="row g-5">
                                <div class="col-md-4 text-center border-end">
                                    <div class="mb-4 position-relative d-inline-block">
                                        <div class="mentor-avatar-wrap" style="width: 160px; height: 160px; border-radius: 40px;">
                                             <img src="<?= !empty($profile['avatar_url']) ? htmlspecialchars($profile['avatar_url']) : 'https://ui-avatars.com/api/?name='.urlencode($user['full_name']).'&background=00010D&color=D9C0A3&size=200' ?>" 
                                                 id="avatarPreview" style="border-radius: 35px;">
                                        </div>
                                        <label for="avatarInput" class="position-absolute bottom-0 end-0 m-2 bg-gold-gradient p-2 px-3 text-white rounded-pill shadow cursor-pointer">
                                            <i class="bi bi-camera-fill"></i>
                                        </label>
                                        <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*" onchange="previewAvatar(this)">
                                    </div>
                                    <h6 class="fw-black text-primary text-uppercase small" style="letter-spacing:1px;">Visual Profile</h6>
                                    <p class="text-muted small px-3">High-resolution professional avatars increase request acceptance rates.</p>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Professional Narrative (Bio)</label>
                                        <textarea name="bio" class="form-control rounded-4 border p-4 text-main" style="background: rgba(var(--primary-rgb), 0.02);" rows="4" placeholder="Craft a story about your expertise and why you mentor..."><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Expertise (Skills)</label>
                                            <input type="text" name="skills" class="form-control rounded-pill border px-4 py-2" placeholder="e.g. Fintech, UI Design, Go" value="<?= htmlspecialchars($profile['skills'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Industry Seniority</label>
                                            <input type="text" name="experience" class="form-control rounded-pill border px-4 py-2" placeholder="e.g. 10+ Years" value="<?= htmlspecialchars($profile['experience'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Network Node (LinkedIn)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-transparent border-end-0 rounded-start-pill ps-4 text-primary"><i class="bi bi-linkedin"></i></span>
                                                <input type="url" name="linkedin" class="form-control border-start-0 rounded-end-pill py-2" placeholder="https://linkedin.com/in/unique-id" value="<?= htmlspecialchars($profile['linkedin'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 pt-4 border-top text-end">
                                        <button type="submit" class="btn btn-gold px-5 py-3 rounded-pill fw-black shadow">Update Mentor Profile <i class="bi bi-check2-circle ms-2"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Manage Availability Slots -->
            <div class="tab-pane fade" id="tab-slots">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="glass-item-card">
                            <h5 class="fw-black text-main mb-4" style="letter-spacing:-1px;">Define New Window</h5>
                            <form method="POST" action="<?= BASE_URL ?>/mentorship/add-slot">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <div class="mb-4">
                                    <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Calendar Date</label>
                                    <input type="date" name="slot_date" class="form-control rounded-pill border px-4 py-2" min="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-6">
                                        <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Start Time</label>
                                        <input type="time" name="slot_time" class="form-control rounded-pill border px-4 py-2" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Duration</label>
                                        <select name="duration" class="form-select rounded-pill border px-4 py-2">
                                            <option value="30" selected>30 MIN</option>
                                            <option value="60">1 HOUR</option>
                                            <option value="90">1.5 HOURS</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-5">
                                    <label class="form-label text-primary small fw-black text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px;">Private Memo (Notes)</label>
                                    <input type="text" name="notes" class="form-control rounded-pill border px-4 py-2" placeholder="e.g. Focus on Tech Review">
                                </div>
                                <button type="submit" class="btn btn-gold w-100 py-3 rounded-pill fw-black shadow">Publish Slot <i class="bi bi-cloud-upload ms-2"></i></button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-7">
                         <div class="p-5 rounded-4 border-2 border-dashed bg-main bg-opacity-5 text-center h-100 d-flex flex-column justify-content-center">
                            <div class="mb-4 bg-white bg-opacity-50 d-inline-flex mx-auto p-4 rounded-circle border shadow-sm">
                                <i class="bi bi-stars fs-1 text-primary"></i>
                            </div>
                            <h4 class="fw-black text-main">Streamlined Scheduling</h4>
                            <p class="text-muted fs-5 px-lg-5">Create multiple availability windows. Mentees can immediately reserve slots, minimizing back-and-forth emails. You maintain full control over your calendar density.</p>
                         </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { $('#avatarPreview').attr('src', e.target.result); }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
