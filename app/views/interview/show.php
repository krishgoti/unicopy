<?php $pageTitle = 'Interview Experience'; require $viewPath . 'layouts/header.php'; ?>

<?php
$who = $exp['contributor_name'] ?? ($exp['full_name'] ?? (explode('@', $exp['email'])[0] ?? 'Contributor'));
$email = $exp['contributor_email'] ?? ($exp['email'] ?? '');
$phone = $exp['contributor_phone'] ?? '';
$img = $exp['contributor_image'] ?? null;
?>

<style>
/* ── Detail Header ── */
.detail-header {
    margin-bottom: 2.5rem;
}
.back-btn-gold {
    width: 44px; height: 44px;
    border-radius: 14px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-main);
    transition: var(--transition);
}
.back-btn-gold:hover { background: var(--primary); color: white; transform: translateX(-4px); }

.pill-badge {
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ── Content Sections ── */
.glass-section {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 28px;
    padding: 3rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-lg);
}

.section-icon-gold {
    width: 48px; height: 48px;
    border-radius: 14px;
    background: var(--gold-gradient);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.2);
}

/* ── Question List ── */
.q-item {
    padding: 1.25rem;
    background: rgba(var(--primary-rgb), 0.03);
    border: 1px solid var(--border-color);
    border-radius: 16px;
    margin-bottom: 1rem;
    display: flex; gap: 1rem;
    transition: var(--transition);
}
.q-item:hover { border-color: var(--primary); transform: translateX(8px); }
.q-num {
    width: 28px; height: 28px;
    background: var(--primary);
    color: white;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; font-size: 0.75rem; flex-shrink: 0;
}

/* ── Pro Tips ── */
.tips-box {
    background: var(--hero-bg-gradient);
    border-radius: 24px;
    padding: 2.5rem;
    color: white;
    position: relative;
    overflow: hidden;
}
.tips-box i.bg-icon {
    position: absolute; right: -20px; bottom: -20px;
    font-size: 8rem; opacity: 0.1; transform: rotate(-15deg);
}

/* ── Profile Column ── */
.profile-stick {
    position: sticky; top: 100px;
}
.profile-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 28px;
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}
.profile-avatar-wrap {
    width: 100px; height: 100px;
    border-radius: 24px;
    background: var(--gold-gradient);
    padding: 4px;
    margin: 0 auto 1.5rem;
}
.profile-avatar-wrap img, .profile-avatar-inner {
    width: 100%; height: 100%;
    border-radius: 20px;
    object-fit: cover;
    background: var(--card-bg);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; font-weight: 900; color: var(--primary);
}

.contact-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px;
    border-radius: 14px;
    transition: var(--transition);
    text-decoration: none;
    color: var(--text-main);
    border: 1px solid transparent;
}
.contact-row:hover { background: rgba(var(--primary-rgb), 0.05); border-color: var(--border-color); }
.contact-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(var(--primary-rgb), 0.1);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
}
</style>

<div class="detail-header d-flex flex-wrap justify-content-between align-items-center gap-4">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= BASE_URL ?>/interview-experiences" class="back-btn-gold">
            <i class="bi bi-arrow-left fs-5"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h2 class="fw-black mb-0" style="letter-spacing:-1px;"><?= htmlspecialchars($exp['company_name'] ?? 'Interview Details') ?></h2>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <span class="pill-badge bg-primary text-white"><i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($exp['role'] ?? 'Role') ?></span>
                <span class="pill-badge bg-gold-gradient text-white"><i class="bi bi-briefcase me-1"></i><?= ucfirst($exp['experience_type'] ?? 'placement') ?></span>
                <?php
                $difficulty = $exp['difficulty'] ?? 'medium';
                $diffColor = match($difficulty) {
                    'easy' => '#10b981',
                    'hard' => '#ef4444',
                    default => '#f59e0b'
                };
                ?>
                <span class="pill-badge" style="background: <?= $diffColor ?>; color: white;"><i class="bi bi-bar-chart-steps me-1"></i><?= ucfirst($difficulty) ?></span>
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill border bg-card text-muted small fw-bold">
        <i class="bi bi-calendar3 text-primary"></i>
        <?= !empty($exp['created_at']) ? date('M d, Y', strtotime($exp['created_at'])) : 'Recent' ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Experience Description -->
        <div class="glass-section">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="section-icon-gold"><i class="bi bi-journal-text"></i></div>
                <h4 class="fw-bold mb-0">Interview Process</h4>
            </div>
            <div class="text-main lh-lg" style="font-size: 1.05rem;">
                <?php if (!empty($exp['description'])): ?>
                    <?= nl2br(htmlspecialchars($exp['description'])) ?>
                <?php elseif (!empty($exp['rounds'])): ?>
                    <?= nl2br(htmlspecialchars($exp['rounds'])) ?>
                <?php else: ?>
                    <p class="text-muted fst-italic">The contributor didn't provide a process description.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="glass-section">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="section-icon-gold" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="bi bi-patch-question"></i></div>
                <h4 class="fw-bold mb-0">Questions Asked</h4>
            </div>
            <?php if (!empty($questionsList)): ?>
                <div class="q-list">
                    <?php foreach ($questionsList as $index => $q): ?>
                        <div class="q-item">
                            <div class="q-num"><?= $index + 1 ?></div>
                            <div class="fw-bold text-main"><?= htmlspecialchars((string)$q) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-card border border-dashed p-5 rounded-4 text-center">
                    <i class="bi bi-stars fs-1 text-primary opacity-25 mb-3 d-block"></i>
                    <p class="text-muted mb-0">No shared questions for this experience.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tips Section -->
        <?php if (!empty($exp['tips'])): ?>
        <div class="tips-box">
            <i class="bi bi-lightbulb-fill bg-icon"></i>
            <div class="d-flex align-items-center gap-3 mb-3">
                <i class="bi bi-stars fs-3"></i>
                <h4 class="fw-bold mb-0 text-white">Contributor's Pro-Tips</h4>
            </div>
            <div class="ps-4 border-start border-white border-4" style="font-size: 1.1rem; line-height: 1.8;">
                <?= nl2br(htmlspecialchars($exp['tips'])) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Contributor Identity -->
    <div class="col-lg-4">
        <div class="profile-stick">
            <div class="profile-card">
                <div class="bg-primary bg-opacity-10 py-5 text-center">
                    <div class="profile-avatar-wrap">
                        <?php if (!empty($img)): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($img) ?>" alt="Profile">
                        <?php else: ?>
                            <div class="profile-avatar-inner"><?= strtoupper(substr($who, 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold text-main mb-1"><?= htmlspecialchars($who) ?></h4>
                    <span class="badge bg-gold-gradient text-white px-3 py-2 rounded-pill mt-1" style="font-size: 0.65rem;"><?= ucfirst($exp['user_role'] ?? 'Student') ?></span>
                </div>
                
                <div class="p-4">
                    <h6 class="text-uppercase fw-black text-primary small mb-3" style="letter-spacing: 2px; font-size: 0.7rem;">Get in Touch</h6>
                    
                    <a href="mailto:<?= htmlspecialchars($email) ?>" class="contact-row mb-2">
                        <div class="contact-icon"><i class="bi bi-envelope-at"></i></div>
                        <div class="text-truncate">
                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.6rem;">Email</div>
                            <div class="fw-bold text-truncate"><?= htmlspecialchars($email) ?></div>
                        </div>
                    </a>

                    <?php if (!empty($phone)): ?>
                    <a href="tel:<?= htmlspecialchars($phone) ?>" class="contact-row mb-2">
                        <div class="contact-icon" style="background:rgba(16, 185, 129, 0.1); color:#10b981;"><i class="bi bi-whatsapp"></i></div>
                        <div>
                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.6rem;">Phone</div>
                            <div class="fw-bold"><?= htmlspecialchars($phone) ?></div>
                        </div>
                    </a>
                    <?php endif; ?>

                    <?php if ((!empty($exp['user_company']) || !empty($exp['designation'])) && strtolower($exp['user_role'] ?? '') !== 'student'): ?>
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="text-uppercase fw-black text-primary small mb-3" style="letter-spacing: 2px; font-size: 0.7rem;">Work Info</h6>
                        <div class="d-flex align-items-center gap-3 p-2">
                            <div class="contact-icon" style="background:rgba(var(--primary-rgb), 0.1);"><i class="bi bi-buildings"></i></div>
                            <div>
                                <div class="fw-bold text-main"><?= !empty($exp['user_company']) ? htmlspecialchars($exp['user_company']) : 'Confirmed Mentor' ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4 p-4 rounded-4 border bg-card text-center">
                <p class="text-muted small mb-0">Is this experience helpful? Share it with your peers!</p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="bi bi-share me-1"></i> Copy Link</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require $viewPath . 'layouts/footer.php'; ?>
