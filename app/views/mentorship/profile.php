<?php $pageTitle = ($mentor['full_name'] ?? 'Mentor') . ' - Profile'; require $viewPath . 'layouts/header.php'; ?>

<style>
/* ── Profile Page ── */
.profile-cover-luxury {
    height: 220px;
    background: var(--hero-bg-gradient);
    border-radius: 32px;
    margin-bottom: -80px;
    position: relative;
    overflow: hidden;
}
.profile-cover-luxury::after {
    content: '';
    position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M0 0l20 20M20 0L0 20" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></svg>');
    opacity: 0.3;
}

.profile-main-glass {
    background: var(--card-bg);
    border-radius: 32px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-xl);
    padding: 3rem;
    position: relative;
    z-index: 2;
}

.profile-avatar-premium {
    width: 150px; height: 150px;
    border-radius: 32px;
    padding: 5px;
    background: var(--gold-gradient);
    margin-top: -110px;
    box-shadow: var(--shadow-lg);
    position: relative;
}
.profile-avatar-premium img {
    width: 100%; height: 100%;
    border-radius: 28px;
    object-fit: cover;
    background: var(--card-bg);
    border: 3px solid var(--card-bg);
}

.pill-status-verified {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    padding: 6px 16px;
    border-radius: 50px;
    font-weight: 800;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.skill-gold-tag {
    background: rgba(var(--primary-rgb), 0.05);
    color: var(--primary);
    padding: 8px 18px;
    border-radius: 14px;
    font-weight: 800;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid var(--border-color);
    transition: var(--transition);
}
.skill-gold-tag:hover { background: var(--gold-gradient); color: white; border-color: transparent; }

/* ── Booking Slots ── */
.slot-luxury-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 1.5rem;
    cursor: pointer;
    transition: var(--transition);
    text-align: center;
}
.slot-luxury-card:hover:not(.disabled) {
    transform: translateY(-5px);
    border-color: var(--primary);
    background: rgba(var(--primary-rgb), 0.05); /* slightly more visible */
}
.slot-luxury-card:hover:not(.disabled) .text-main,
.slot-luxury-card:hover:not(.disabled) .text-muted {
    color: var(--text-main) !important; /* Force visible on hover */
}
.slot-luxury-card.active {
    background: var(--gold-gradient);
    border-color: transparent;
    color: white !important;
    box-shadow: 0 10px 20px rgba(var(--primary-rgb), 0.2);
}
.slot-luxury-card.active .text-main { color: white !important; }
.slot-luxury-card.active .text-muted { color: rgba(255,255,255,0.8) !important; }

/* ── Sidebar ── */
.sticky-sidebar {
    position: sticky; top: 100px;
}
.connect-panel {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 28px;
    padding: 2.5rem;
}
</style>

<div class="container py-4">
    <div class="profile-cover-luxury shadow-lg"></div>
    
    <div class="row g-4 px-2">
        <!-- Left Column: Primary Content -->
        <div class="col-lg-8">
            <div class="profile-main-glass">
                <div class="d-flex align-items-end justify-content-between flex-wrap gap-4 mb-5">
                    <div class="profile-avatar-premium">
                        <img src="<?= !empty($mentor['avatar_url']) ? htmlspecialchars($mentor['avatar_url']) : 'https://ui-avatars.com/api/?name='.urlencode($mentor['full_name']).'&background=00010D&color=D9C0A3&size=200' ?>" alt="Mentor">
                    </div>
                    <div class="d-flex gap-2">
                        <?php if (!empty($mentor['linkedin'])): ?>
                            <a href="<?= htmlspecialchars($mentor['linkedin']) ?>" target="_blank" class="btn btn-outline-primary fw-bold btn-sm rounded-pill px-4" title="Connect on LinkedIn"><i class="bi bi-linkedin"></i></a>
                        <?php endif; ?>
                        <a href="mailto:<?= htmlspecialchars($mentor['email']) ?>" class="btn btn-outline-secondary fw-bold btn-sm rounded-pill px-4" title="Send Email"><i class="bi bi-envelope"></i></a>
                    </div>
                </div>

                <div class="mb-5">
                    <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                        <h1 class="fw-black text-main mb-0" style="letter-spacing:-1.5px;"><?= htmlspecialchars($mentor['full_name']) ?></h1>
                        <span class="pill-status-verified"><i class="bi bi-patch-check-fill"></i> Verified Mentor</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted fw-bold mb-4">
                        <span class="text-primary" style="font-size: 1.1rem;"><?= htmlspecialchars($mentor['designation'] ?? 'Expert') ?> @ <?= htmlspecialchars($mentor['company'] ?? 'UniHelp') ?></span>
                        <span class="opacity-25 fs-5">|</span>
                        <span><i class="bi bi-building me-1"></i> <?= htmlspecialchars($mentor['department']) ?> Dept.</span>
                        <?php if (!empty($mentor['experience'])): ?>
                            <span class="opacity-25 fs-5">|</span>
                            <span><i class="bi bi-briefcase me-1"></i> <?= htmlspecialchars($mentor['experience']) ?> Experience</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-5">
                    <h6 class="text-uppercase fw-black text-primary small mb-3" style="letter-spacing: 2px;">Professional Bio</h6>
                    <div class="text-main lh-lg" style="font-size: 1.1rem;">
                        <?= nl2br(htmlspecialchars($mentor['bio'] ?: 'This mentor hasn\'t added a detailed bio yet, but they are ready to help you grow.')) ?>
                    </div>
                </div>

                <?php if (!empty($mentor['skills'])): ?>
                <div class="mb-5 py-4 border-top">
                    <h6 class="text-uppercase fw-black text-primary small mb-3" style="letter-spacing: 2px;">Areas of Mentorship</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach (explode(',', $mentor['skills']) as $skill): ?>
                            <span class="skill-gold-tag"><?= htmlspecialchars(trim($skill)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div id="booking-section" class="pt-5 border-top">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="p-3 bg-gold-gradient rounded-4 text-white"><i class="bi bi-calendar3 fs-4"></i></div>
                        <h3 class="fw-black text-main mb-0" style="letter-spacing:-1px;">Book a Session</h3>
                    </div>

                    <?php if (empty($slots)): ?>
                        <div class="p-5 rounded-4 border bg-light text-center">
                            <i class="bi bi-calendar-x fs-1 text-muted opacity-25 mb-3 d-block"></i>
                            <h5 class="fw-bold text-main">No Slots Available</h5>
                            <p class="text-muted mb-0">This mentor hasn't published their schedule yet. Connect with them first to request a private session.</p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-4 fs-5">Choose a time that works for you. All sessions are 1-on-1 virtual consultations.</p>
                        <form method="POST" action="<?= BASE_URL ?>/mentorship/book-slot">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <div class="row g-3">
                                <?php foreach ($slots as $s): ?>
                                    <div class="col-sm-6 col-md-4">
                                        <div class="slot-luxury-card" onclick="selectSlot(this, <?= $s['id'] ?>)">
                                            <div class="fw-black text-main mb-1" style="font-size: 1.2rem;"><?= date('d M', strtotime($s['slot_date'])) ?></div>
                                            <div class="text-muted small fw-bold text-uppercase mb-2" style="letter-spacing:1px;"><?= date('h:i A', strtotime($s['slot_time'])) ?></div>
                                            <div class="badge bg-gold-gradient text-white px-3 py-2 rounded-pill" style="font-size: 0.65rem;"><?= $s['duration'] ?> MINS</div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="slot_id" id="selectedSlotId" required>
                            <div class="mt-5 text-center">
                                <button type="submit" id="bookBtn" class="btn btn-gold btn-lg rounded-pill px-5 py-3 fw-black shadow" disabled>Confirm Your Booking</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Connect Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-sidebar">
                <div class="connect-panel shadow-lg">
                    <h4 class="fw-black text-main mb-4" style="letter-spacing:-1px;">Connect</h4>
                    
                    <?php if (Auth::role() !== 'student'): ?>
                        <div class="p-3 rounded-4 bg-info bg-opacity-10 border border-info border-opacity-25 text-info small">
                            <i class="bi bi-info-circle-fill me-2"></i>You are logged in as a mentor. This section is visible to students.
                        </div>
                    <?php else: ?>
                        <?php if ($existingRequest): ?>
                           <div class="text-center py-4">
                               <div class="px-4 py-3 rounded-4 mb-3 status-pill-gold pill-<?= $existingRequest['status'] ?> fw-bold d-inline-block">
                                    <i class="bi bi-<?= $existingRequest['status'] === 'accepted' ? 'check-circle' : 'hourglass-split' ?> me-2"></i>
                                    <?= $existingRequest['status'] === 'accepted' ? 'Mentor Connected' : 'Request Pending' ?>
                               </div>
                               <p class="text-muted small mb-0">Your connection with this mentor is established. You can now use the booking section.</p>
                               <?php if ($existingRequest['status'] === 'accepted'): ?>
                                    <button class="btn btn-link text-primary fw-black text-decoration-none mt-3" onclick="document.getElementById('booking-section').scrollIntoView({behavior:'smooth'})">Select a Slot <i class="bi bi-arrow-down ms-1"></i></button>
                               <?php endif; ?>
                           </div>
                        <?php else: ?>
                            <p class="text-muted small mb-4">Establishing a formal connection is the first step towards personalized guidance.</p>
                            <form method="POST" action="<?= BASE_URL ?>/mentorship/request">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="mentor_id" value="<?= $mentor['id'] ?>">
                                <div class="mb-4">
                                    <label class="form-label-gold small fw-bold text-primary text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px; display:block;">Your Intent</label>
                                    <textarea name="message" class="form-control rounded-4 border p-3 text-main" style="background: rgba(var(--primary-rgb), 0.03);" rows="5" placeholder="Share your career goals or specific challenges you're facing..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-gold w-100 py-3 fw-black rounded-pill shadow-sm">Send Request <i class="bi bi-send-fill ms-2"></i></button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                    <hr class="my-4 border-light">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-4" style="background: rgba(16, 185, 129, 0.05);">
                         <div class="bg-success rounded-circle" style="width: 10px; height: 10px;"></div>
                         <span class="small fw-bold text-success text-uppercase" style="letter-spacing:1px;">Active Network</span>
                    </div>
                </div>

                <div class="mt-4 p-4 text-center rounded-4 border bg-light">
                    <p class="text-muted small mb-0">Help your peers grow. Share this mentor's profile with your campus group.</p>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill mt-3 px-4"><i class="bi bi-share me-2"></i> Copy Profile Link</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectSlot(card, id) {
    document.querySelectorAll('.slot-luxury-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    document.getElementById('selectedSlotId').value = id;
    document.getElementById('bookBtn').removeAttribute('disabled');
}
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
