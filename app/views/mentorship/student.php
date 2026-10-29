<?php $pageTitle = 'Mentorship'; require $viewPath . 'layouts/header.php'; ?>

<style>
/* ── Mentorship Hero ── */
.mentorship-hero-premium {
    background: var(--hero-bg-gradient);
    border-radius: 32px;
    padding: 3.5rem;
    color: white;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}
.hero-icon-label {
    width: 70px; height: 70px;
    background: rgba(255,255,255,0.1);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem;
    border: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
}

/* ── Custom Nav Pills ── */
.nav-pills-luxury {
    background: var(--card-bg);
    padding: 6px;
    border-radius: 50px;
    border: 1px solid var(--border-color);
    display: inline-flex;
    gap: 5px;
}
.nav-pills-luxury .nav-link {
    border-radius: 50px;
    padding: 10px 24px;
    font-weight: 800;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    transition: var(--transition);
}
.nav-pills-luxury .nav-link.active {
    background: var(--gold-gradient);
    color: white;
    box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.3);
}

/* ── Mentor Card ── */
.mentor-glass-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 28px;
    padding: 2rem;
    transition: var(--transition);
    height: 100%;
    display: flex;
    flex-direction: column;
}
.mentor-glass-card:hover { 
    transform: translateY(-10px); 
    border-color: var(--primary); 
    box-shadow: var(--shadow-lg); 
}

.mentor-avatar-wrap {
    position: relative;
    width: 90px; height: 90px;
    border-radius: 24px;
    padding: 3px;
    background: var(--gold-gradient);
}
.mentor-avatar-wrap img {
    width: 100%; height: 100%;
    border-radius: 21px;
    object-fit: cover;
    background: var(--card-bg);
    border: 2px solid var(--card-bg);
}

.status-dot {
    position: absolute; bottom: -2px; right: -2px;
    width: 22px; height: 22px;
    border-radius: 50%;
    border: 3px solid var(--card-bg);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; color: white;
}
.dot-online { background: #10b981; }
.dot-offline { background: #94a3b8; }

.skill-tag {
    background: rgba(var(--primary-rgb), 0.05);
    color: var(--primary);
    font-size: 0.65rem;
    font-weight: 900;
    padding: 6px 14px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ── Request Cards ── */
.req-luxury-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 24px;
    padding: 1.5rem;
    transition: var(--transition);
}
.status-pill-gold {
    font-size: 0.6rem;
    font-weight: 900;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 50px;
    letter-spacing: 0.5px;
}
.pill-pending  { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
.pill-accepted { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
.pill-rejected { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
</style>

<div class="mentorship-hero-premium">
    <div class="d-flex flex-wrap align-items-center gap-4 position-relative z-1">
        <div class="hero-icon-label">
            <i data-lucide="rocket" style="width: 32px; height: 32px;" class="text-white"></i>
        </div>
        <div>
            <h1 class="fw-black mb-2 text-white" style="letter-spacing:-1.5px;">Achieve More Together</h1>
            <p class="mb-0 opacity-75 fs-5">Connect with industry experts who can accelerate your growth.</p>
        </div>
    </div>
</div>

<div class="mb-5 text-center">
    <div class="nav nav-pills-luxury" id="mentorshipTabs" role="tablist">
        <button class="nav-link active d-flex align-items-center gap-2" id="find-mentors-tab" data-bs-toggle="pill" data-bs-target="#find-mentors">
            <i data-lucide="users" style="width: 16px; height: 16px;"></i> Explore Experts
        </button>
        <button class="nav-link d-flex align-items-center gap-2" id="my-requests-tab" data-bs-toggle="pill" data-bs-target="#my-requests">
            <i data-lucide="mail-plus" style="width: 16px; height: 16px;"></i> My Connections 
            <span class="badge ms-2 bg-dark text-white" style="font-size: 0.6rem; padding: 4px 8px;"><?= count($myRequests) ?></span>
        </button>
        <button class="nav-link d-flex align-items-center gap-2" id="my-sessions-tab" data-bs-toggle="pill" data-bs-target="#my-sessions">
            <i data-lucide="calendar-check" style="width: 16px; height: 16px;"></i> Booked Sessions
        </button>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-sm rounded-4 mb-5">
        <i class="bi bi-info-circle-fill me-2"></i><?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<div class="tab-content" id="mentorshipTabContent">
    
    <!-- Tab 1: Mentors Listing -->
    <div class="tab-pane fade show active" id="find-mentors">
        <div class="row g-4">
            <?php if (empty($allMentors)): ?>
                <div class="col-12 text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center bg-gold-gradient rounded-circle mb-4 shadow-lg" style="width: 100px; height: 100px;">
                        <i data-lucide="search" class="text-white" style="width: 48px; height: 48px;"></i>
                    </div>
                    <h4 class="fw-bold">Establishing Connections...</h4>
                    <p class="text-muted">No mentors found at this moment. Check back soon!</p>
                </div>
            <?php else: ?>
                <?php foreach ($allMentors as $m): ?>
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="mentor-glass-card">
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="mentor-avatar-wrap">
                                <img src="<?= !empty($m['avatar_url']) ? htmlspecialchars($m['avatar_url']) : 'https://ui-avatars.com/api/?name='.urlencode($m['full_name']).'&background=00010D&color=D9C0A3&size=150&bold=true' ?>" alt="Mentor">
                                <div class="status-dot <?= !empty($m['is_available']) ? 'dot-online' : 'dot-offline' ?>">
                                    <i data-lucide="<?= !empty($m['is_available']) ? 'zap' : 'moon' ?>" style="width: 10px; height: 10px;"></i>
                                </div>
                            </div>
                            <div class="overflow-hidden">
                                <h5 class="fw-bold mb-1 text-truncate text-main"><?= htmlspecialchars($m['full_name']) ?></h5>
                                <div class="small fw-bold text-primary text-uppercase mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                    <?= htmlspecialchars($m['role'] ?: 'Mentor') ?>
                                </div>
                                <div class="small text-muted text-truncate d-flex align-items-center gap-1">
                                    <i data-lucide="building" style="width: 12px; height: 12px;"></i> <?= htmlspecialchars($m['company'] ?: 'UniHelp Network') ?>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mb-4 flex-grow-1 line-clamp-2" style="font-size: 0.85rem; line-height: 1.6;">
                            <?= !empty($m['bio']) ? htmlspecialchars(strip_tags($m['bio'])) : 'Experienced mentor ready to help you navigate your career path with expert guidance.' ?>
                        </p>

                        <div class="mb-4 d-flex flex-wrap gap-2">
                            <?php 
                            $skills = !empty($m['skills']) ? explode(',', $m['skills']) : ['Career Coaching', 'Engineering'];
                            foreach (array_slice($skills, 0, 3) as $skill): ?>
                                <span class="skill-tag">#<?= htmlspecialchars(trim($skill)) ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="<?= BASE_URL ?>/mentorship/profile/<?= $m['id'] ?>" class="btn btn-outline-primary fw-bold btn-sm rounded-pill px-3 flex-grow-1">View Details</a>
                             <?php if (!isset($mentorStatuses[$m['id']])): ?>
                                <button class="btn btn-gold fw-bold btn-sm rounded-pill px-4 flex-grow-1" data-bs-toggle="modal" data-bs-target="#requestModal<?= $m['id'] ?>">Connect</button>
                            <?php elseif ($mentorStatuses[$m['id']] === 'pending'): ?>
                                <button class="btn btn-outline-secondary btn-sm rounded-pill px-4 flex-grow-1" disabled><i class="bi bi-clock me-1"></i> Requested</button>
                            <?php elseif ($mentorStatuses[$m['id']] === 'accepted'): ?>
                                <button class="btn btn-outline-success btn-sm rounded-pill px-4 flex-grow-1" disabled><i class="bi bi-patch-check-fill me-1"></i> Mentoring</button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Modern Request Modal -->
                    <div class="modal fade" id="requestModal<?= $m['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="background: var(--card-bg); border-radius: 24px;">
                                 <div class="p-5">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h4 class="fw-black mb-0" style="letter-spacing:-1px;">Start Your Journey</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="p-3 mb-4 rounded-4 border bg-light d-flex align-items-center gap-3">
                                        <div class="mentor-avatar-wrap" style="width: 50px; height: 50px; border-radius: 12px;">
                                            <img src="<?= !empty($m['avatar_url']) ? htmlspecialchars($m['avatar_url']) : 'https://ui-avatars.com/api/?name='.urlencode($m['full_name']) ?>" style="border-radius: 10px;">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-main lh-1 mb-1"><?= htmlspecialchars($m['full_name']) ?></div>
                                            <div class="small text-muted lh-1"><?= htmlspecialchars($m['company'] ?: 'UniHelp') ?></div>
                                        </div>
                                    </div>
                                    <form method="POST" action="<?= BASE_URL ?>/mentorship/request">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="mentor_id" value="<?= $m['id'] ?>">
                                        <div class="mb-4">
                                            <label class="form-label-gold small fw-bold text-primary text-uppercase mb-2" style="font-size:0.7rem; letter-spacing:1px; display:block;">Your Message to Mentor</label>
                                            <textarea name="message" class="form-control rounded-4 border p-3 text-main" style="background: rgba(var(--primary-rgb), 0.03);" rows="4" placeholder="Briefly introduce yourself and your mentorship goals..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-gold w-100 py-3 fw-bold rounded-pill">Send Connection Request <i class="bi bi-send-fill ms-2"></i></button>
                                    </form>
                                 </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>


    <!-- Tab 2: My Requests -->
    <div class="tab-pane fade" id="my-requests">
        <div class="row g-4">
            <?php if (empty($myRequests)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-envelope-x fs-1 text-muted opacity-25 mb-4 d-block"></i>
                    <h5 class="fw-bold">No Connection Requests</h5>
                    <p class="text-muted">Reach out to mentors to start your career journey.</p>
                </div>
            <?php else: ?>
                <?php foreach ($myRequests as $req): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="req-luxury-card h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="mentor-avatar-wrap" style="width: 48px; height: 48px; border-radius: 12px;">
                                    <img src="<?= !empty($req['mentor_avatar']) ? htmlspecialchars($req['mentor_avatar']) : 'https://ui-avatars.com/api/?name='.urlencode($req['mentor_name']) ?>" style="border-radius: 10px;">
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="fw-bold text-main mb-1 text-truncate" style="max-width: 140px;"><?= htmlspecialchars($req['mentor_name']) ?></h6>
                                    <span class="status-pill-gold pill-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span>
                                </div>
                            </div>
                            <div class="text-muted small fw-black text-end" style="line-height: 1;">
                                <div class="mb-1 opacity-50" style="font-size: 0.6rem;"><?= date('M', strtotime($req['created_at'])) ?></div>
                                <div class="fs-4"><?= date('d', strtotime($req['created_at'])) ?></div>
                            </div>
                        </div>

                        <div class="p-3 rounded-4 mb-4" style="background: rgba(var(--primary-rgb), 0.03); min-height: 90px; border: 1px solid var(--border-color);">
                            <div class="text-uppercase small fw-bold text-muted mb-2" style="font-size: 0.6rem; letter-spacing: 0.5px;">Sent Message</div>
                            <p class="small mb-0 text-muted fst-italic">"<?= htmlspecialchars($req['message'] ?? '') ?>..."</p>
                        </div>

                        <?php if (!empty($req['mentor_reply'])): ?>
                            <div class="mb-4">
                                <div class="text-uppercase small fw-bold text-primary mb-2" style="font-size: 0.6rem; letter-spacing: 1px;">Mentor Feedback</div>
                                <div class="alert alert-primary py-2 px-3 small mb-0 border-0 bg-opacity-10 text-main rounded-3" style="background: rgba(var(--primary-rgb), 0.1);">
                                    <?= htmlspecialchars($req['mentor_reply']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($req['status'] === 'accepted'): ?>
                        <div class="mt-auto d-grid gap-2">
                            <a href="<?= BASE_URL ?>/mentorship/profile/<?= $req['mentor_id'] ?>" class="btn btn-gold btn-sm py-2 fw-bold rounded-pill">Book a Session</a>
                            <a href="mailto:<?= htmlspecialchars($req['mentor_email']) ?>" class="btn btn-outline-secondary btn-sm py-2 fw-bold rounded-pill">Contact Email</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tab 3: My Sessions / Bookings -->
    <div class="tab-pane fade" id="my-sessions">
         <div class="row g-4">
            <?php if (empty($myBookings)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-calendar-x fs-1 text-muted opacity-25 mb-4 d-block"></i>
                <h5 class="fw-bold">No Scheduled Sessions</h5>
                <p class="text-muted">Confirm a connection and book a session to get started.</p>
            </div>
            <?php else: ?>
                <?php foreach ($myBookings as $b): 
                    $isFuture = strtotime($b['slot_date']) >= strtotime('today');
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="req-luxury-card h-100 position-relative <?= !$isFuture ? 'opacity-50' : '' ?>">
                        <?php if (!$isFuture): ?>
                            <span class="position-absolute top-0 end-0 m-3 status-pill-gold bg-secondary text-white">COMPLETED</span>
                        <?php else: ?>
                            <span class="position-absolute top-0 end-0 m-3 status-pill-gold bg-success text-white">ACTIVE</span>
                        <?php endif; ?>
                        
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="p-2 px-3 bg-gold-gradient rounded-4 text-center text-white" style="min-width: 65px;">
                                <div class="fw-black fs-4 lh-1"><?= date('d', strtotime($b['slot_date'])) ?></div>
                                <div class="small fw-bold text-uppercase"><?= date('M', strtotime($b['slot_date'])) ?></div>
                            </div>
                            <div class="overflow-hidden">
                                <h6 class="fw-bold text-main mb-1 text-truncate"><?= htmlspecialchars($b['mentor_name']) ?></h6>
                                <div class="small fw-bold text-primary"><i class="bi bi-clock-fill me-1"></i> <?= date('h:i A', strtotime($b['slot_time'])) ?></div>
                            </div>
                        </div>

                        <div class="p-3 mb-4 rounded-4" style="background: rgba(var(--primary-rgb), 0.03); border: 1px solid var(--border-color);">
                            <div class="small fw-bold text-muted text-uppercase mb-2" style="font-size: 0.6rem;">Agenda</div>
                            <p class="small mb-0 text-main line-clamp-2">
                                <?= !empty($b['notes']) ? htmlspecialchars($b['notes']) : 'One-on-one session with your selected mentor.' ?>
                            </p>
                        </div>

                        <div class="mt-auto d-flex gap-2">
                            <a href="mailto:<?= htmlspecialchars($b['mentor_email']) ?>" class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill fw-bold">Message</a>
                            <?php if ($isFuture): ?>
                            <form method="POST" action="<?= BASE_URL ?>/mentorship/cancel-booking" class="flex-grow-1" onsubmit="return confirm('Note: Cancellations within 24h are not recommended. Continue?')">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill fw-bold">Cancel</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Relocate all modals to body to prevent stacking context/overflow issues
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        document.body.appendChild(modal);
    });
});
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
