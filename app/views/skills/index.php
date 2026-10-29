<?php $pageTitle = 'Skill Tracker'; require $viewPath . 'layouts/header.php'; ?>

<div class="container-fluid pb-5">
    <!-- Premium Hero Section -->
    <div class="dash-hero p-5 rounded-4 shadow-lg mb-5 border-0 overflow-hidden reveal-on-scroll">
        <div class="row align-items-center position-relative z-1">
            <div class="col-md-7">
                <h1 class="fw-black text-white mb-2 display-5" style="letter-spacing:-1.5px;">Skill <span class="text-gradient-gold">Architecture</span></h1>
                <p class="text-white-50 lead mb-4 opacity-75">Visualize your professional evolution and master the technologies of tomorrow.</p>
                <div class="d-flex gap-3">
                    <div class="glass-pill px-3 py-2 d-flex align-items-center gap-2">
                        <i data-lucide="shield-check" class="text-gradient" style="width: 18px; height: 18px;"></i>
                        <span class="text-white small fw-bold">Verified Skills: <?= count($allSkills ?? []) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-5 text-md-end d-none d-md-block">
                <div class="hero-ring-container d-inline-block p-4 glass-card rounded-circle">
                    <div class="hero-ring-inner bg-gold-gradient rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width:120px; height:120px;">
                        <i data-lucide="trending-up" class="text-white" style="width: 48px; height: 48px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Readiness Overview -->
    <?php
    $r      = (float)($readiness ?? 0);
    $rDeg   = round($r / 100 * 360, 1);
    $rlabel = $r >= 80 ? ['EXPERT', 'text-success'] : ($r >= 60 ? ['ADVANCED', 'text-primary'] : ($r >= 40 ? ['INTERMEDIATE', 'text-warning'] : ['BEGINNER', 'text-muted']));
    ?>
    <div class="glass-card mb-5 p-5 border-0 shadow-lg rounded-4 reveal-on-scroll">
        <div class="row align-items-center">
            <div class="col-lg-2 text-center text-lg-start">
                <div class="readiness-ring-luxury d-inline-flex align-items-center justify-content-center p-1 bg-gold-gradient rounded-circle shadow mb-3 mb-lg-0" style="width:140px; height:140px; position:relative;">
                    <div class="bg-card rounded-circle d-flex flex-column align-items-center justify-content-center w-100 h-100">
                        <h2 class="fw-black text-main mb-0" id="readinessNum"><?= round($r) ?>%</h2>
                        <span class="text-muted fw-bold" style="font-size:0.6rem; letter-spacing:1px;">READY</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4 mb-lg-0 ps-lg-5">
                <h4 class="fw-black text-main mb-2">Professional Readiness</h4>
                <p class="text-muted small mb-0">Your aggregated readiness score based on verified technical abilities, project contributions, and peer evaluations.</p>
            </div>
            <div class="col-lg-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-main fw-black small">MASTERY LEVEL</span>
                    <span class="text-gradient-gold fw-black small"><?= $rlabel[0] ?></span>
                </div>
                <div class="progress rounded-pill bg-main bg-opacity-10 shadow-inner" style="height: 14px;">
                    <div id="readinessBar" class="progress-bar bg-gold-gradient rounded-pill" role="progressbar" style="width: 0%; transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Automated Insights Banner -->
    <div class="glass-card p-4 mb-5 border-start border-gold border-5 shadow-sm reveal-on-scroll">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-main bg-opacity-5 rounded-4 text-primary d-flex align-items-center justify-content-center">
                <i data-lucide="brain-circuit" style="width: 24px; height: 24px;" class="text-gradient"></i>
            </div>
            <div>
                <h6 class="fw-black text-main mb-1">Algorithmic Talent-Sync</h6>
                <p class="text-muted small mb-0 fw-bold">Your skills are automatically synchronized with our <a href="<?= BASE_URL ?>/mocktests" class="text-gradient-gold text-decoration-none">Assessment Ecosystem</a> for real-time validation.</p>
            </div>
        </div>
    </div>

    <!-- Skills Categorization -->
    <?php if (empty($allSkills)): ?>
        <div class="text-center py-5 glass-card rounded-4 border-dashed reveal-on-scroll">
            <div class="mb-4">
                <i class="bi bi-layers display-1 text-primary opacity-10"></i>
            </div>
            <h3 class="fw-black text-main mb-3">Portfolio Empty</h3>
            <p class="text-muted lead mb-4">Initiate your expertise development journey by cataloging your first professional skill.</p>
            <a href="<?= BASE_URL ?>/profile/edit" class="btn btn-gold px-5 py-3 rounded-pill fw-black shadow-lg">INITIALIZE PORTFOLIO</a>
        </div>
    <?php else: 
        $groupedSkills = [];
        foreach ($allSkills as $skill) {
            $groupedSkills[$skill['category'] ?? 'General'][] = $skill;
        }

        foreach ($groupedSkills as $category => $categorySkills):
    ?>
    <div class="mb-5 reveal-on-scroll">
        <div class="d-flex align-items-center mb-4">
            <div class="p-2 bg-main bg-opacity-5 rounded-3 me-3 text-primary d-flex align-items-center justify-content-center">
                <i data-lucide="layout-grid" style="width: 18px; height: 18px;"></i>
            </div>
            <h5 class="fw-black text-main mb-0 text-uppercase small" style="letter-spacing: 2px;"><?= htmlspecialchars($category) ?></h5>
            <div class="flex-grow-1 ms-3 border-bottom opacity-10"></div>
        </div>

        <div class="row g-4">
            <?php foreach ($categorySkills as $skill): 
                $userSkill  = $userSkills[$skill['id']] ?? null;
                $progress   = (int)($userSkill['progress'] ?? 0);
                $level      = $userSkill['level'] ?? 'Beginner';
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100 transition-all hover-lift border-0 shadow-sm overflow-hidden position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="p-3 glass-pill rounded-4 text-gradient shadow-sm d-flex align-items-center justify-content-center">
                            <i data-lucide="hexagon" style="width: 24px; height: 24px;"></i>
                        </div>
                        <span class="badge glass-pill text-muted fw-black text-uppercase" style="font-size: 0.6rem; padding: 6px 12px; letter-spacing: 1px;">
                            Level: <?= strtoupper($level) ?>
                        </span>
                    </div>

                    <h5 class="fw-black text-main mb-3 h6"><?= htmlspecialchars($skill['name']) ?></h5>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold">Proficiency</span>
                        <span class="text-main fw-black small"><?= $progress ?>%</span>
                    </div>

                    <div class="progress rounded-pill bg-main bg-opacity-5 mb-4 shadow-inner" style="height: 8px;">
                        <div class="progress-bar bg-gold-gradient rounded-pill skill-bar" role="progressbar" data-width="<?= $progress ?>%" style="width: 0%; transition: width 1.2s cubic-bezier(0.1, 0.9, 0.2, 1);"></div>
                    </div>
                    

                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<style>
/* Local Micro-animations and Luxury Touches */
.dash-hero {
    background: linear-gradient(135deg, #00010D 0%, #0D0C07 100%);
    position: relative;
}
.dash-hero::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: radial-gradient(circle at top right, rgba(217, 192, 163, 0.1), transparent 60%);
    pointer-events: none;
}
.hero-ring-container {
    animation: float-luxury 6s ease-in-out infinite;
}
@keyframes float-luxury {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-15px) rotate(5deg); }
}
.hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2) !important;
}
.shadow-inner {
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}
.readiness-ring-luxury::before {
    content: '';
    position: absolute;
    inset: -5px;
    border: 2px solid rgba(217, 192, 163, 0.2);
    border-radius: 50%;
    animation: pulse-gold 2s infinite;
}
@keyframes pulse-gold {
    0% { transform: scale(1); opacity: 0.5; }
    100% { transform: scale(1.1); opacity: 0; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Reveal animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                
                // If it's a bar container, animate the bars inside
                const bars = entry.target.querySelectorAll('.skill-bar');
                bars.forEach(bar => {
                    setTimeout(() => {
                        bar.style.width = bar.dataset.width;
                    }, 300);
                });

                // If it's the readiness bar
                const rBar = entry.target.querySelector('#readinessBar');
                if (rBar) {
                    setTimeout(() => {
                        rBar.style.width = '<?= round($r) ?>%';
                    }, 500);
                }
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal-on-scroll').forEach(el => observer.observe(el));
});
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
