<?php $pageTitle = 'Home'; require $this->viewPath . 'layouts/header.php'; ?>

<style>
/* ── Premium Modern Aesthetic ── */
:root {
    --hero-bg-gradient: linear-gradient(135deg, #00010D 0%, #0D0C07 50%, #594A38 100%);
    --card-surface: rgba(255, 255, 255, 0.95);
    --card-border: rgba(217, 192, 163, 0.2);
    --glass-shadow: 0 25px 50px -12px rgba(0, 1, 13, 0.15);
    --blob-1: rgba(217, 192, 163, 0.15);
    --blob-2: rgba(89, 74, 56, 0.15);
    --text-gradient: linear-gradient(to right, #D9C0A3, #F2E7D5);
}

[data-theme="dark"] {
    --hero-bg-gradient: linear-gradient(135deg, #000005 0%, #00010D 50%, #0D0C07 100%);
    --card-surface: rgba(13, 12, 7, 0.8);
    --card-border: rgba(217, 192, 163, 0.1);
    --glass-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
    --text-gradient: linear-gradient(to right, #F2E7D5, #F2F2F0);
}

/* Animations */
@keyframes blob-bounce {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}

@keyframes fade-up {
    0% { opacity: 0; transform: translateY(30px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes pulse-btn {
    0% { box-shadow: 0 0 0 0 rgba(217, 192, 163, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(217, 192, 163, 0); }
    100% { box-shadow: 0 0 0 0 rgba(217, 192, 163, 0); }
}

.fade-up-element {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}

.fade-up-element.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Hero Section */
.premium-hero {
    position: relative;
    padding: 6rem 0 8rem 0;
    overflow: hidden;
    background: var(--hero-bg-gradient);
}

.hero-blob {
    position: absolute;
    filter: blur(80px);
    z-index: 0;
    opacity: 0.6;
    animation: blob-bounce 10s infinite alternate;
}

.hero-blob-1 {
    top: -10%; left: 0%;
    width: 400px; height: 400px;
    background: var(--blob-1);
}

.hero-blob-2 {
    bottom: -10%; right: 0%;
    width: 500px; height: 500px;
    background: var(--blob-2);
    animation-delay: 2s;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-title {
    font-size: 4rem;
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -1.5px;
    margin-bottom: 1.5rem;
    color: #fff;
    animation: fade-up 0.8s ease-out;
}

.text-gradient {
    background: var(--text-gradient);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

[data-theme="dark"] .hero-title, [data-theme="dark"] .text-white-80 {
    color: #f8fafc;
}

.text-white-80 {
    color: rgba(255, 255, 255, 0.8);
}

.hero-subtitle {
    font-size: 1.25rem;
    max-width: 600px;
    margin-bottom: 2.5rem;
    animation: fade-up 0.8s ease-out 0.2s backwards;
}

.premium-btn {
    padding: 1rem 2.5rem;
    border-radius: 50px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fade-up 0.8s ease-out 0.4s backwards;
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.premium-btn::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: -1;
    transition: opacity 0.3s;
    opacity: 0;
}

.premium-btn-primary {
    background: linear-gradient(135deg, #D9C0A3, #594A38);
    color: #00010D !important;
    border: none;
    box-shadow: 0 10px 20px rgba(217, 192, 163, 0.2);
    animation: pulse-btn 3s infinite;
}

.premium-btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 25px rgba(94, 127, 140, 0.4);
    color: #fff;
}

.premium-btn-primary::before {
    background: linear-gradient(135deg, #F2E7D5, #D9C0A3);
}
.premium-btn-primary:hover::before { opacity: 1; }

.premium-btn-outline {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.premium-btn-outline:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    transform: translateY(-3px);
}

.hero-image-wrapper {
    animation: float 6s ease-in-out infinite;
    position: relative;
    z-index: 2;
}

.hero-image {
    width: 100%;
    max-width: 600px;
    border-radius: 24px;
    box-shadow: var(--glass-shadow);
    transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
    transition: transform 0.5s;
}

.hero-image:hover {
    transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
}

.stats-card {
    background: var(--card-surface);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 1px solid var(--card-border);
    border-bottom: 1px solid var(--card-border);
    border-left: none;
    border-right: none;
    border-radius: 0;
    padding: 3rem 2rem;
    margin-top: 0;
    box-shadow: none;
    position: relative;
    z-index: 10;
    transition: all 0.3s;
}

.stat-item {
    text-align: center;
    padding: 1rem;
}

.stat-number {
    font-size: 3rem;
    font-weight: 900;
    background: var(--text-gradient);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.85rem;
}

/* Trust Banner */
.trust-banner {
    padding: 4rem 0;
    border-bottom: 1px solid var(--border-color);
}

.trust-logo {
    display: flex;
    align-items: center;
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text-muted);
    opacity: 0.6;
    transition: all 0.3s;
    filter: grayscale(100%);
    cursor: default;
}

.trust-logo:hover {
    opacity: 1;
    filter: grayscale(0%);
    transform: scale(1.05);
}

/* Feature/Architecture Grid */
.section-title {
    font-size: 3rem;
    font-weight: 800;
    letter-spacing: -1px;
    margin-bottom: 1rem;
}

.feature-glass-card {
    background: var(--card-surface);
    backdrop-filter: blur(12px);
    border: 1px solid var(--card-border);
    border-radius: 24px;
    padding: 2.5rem;
    height: 100%;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}

.feature-glass-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--glass-shadow);
    border-color: var(--primary-light);
}

.feature-icon-box {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.1), rgba(147, 51, 234, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: var(--primary);
    margin-bottom: 1.5rem;
    transition: all 0.3s;
}

.feature-glass-card:hover .feature-icon-box {
    background: linear-gradient(135deg, var(--primary), #594A38);
    color: #00010D;
    transform: scale(1.1) rotate(5deg);
}

.feature-title {
    font-weight: 700;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

[data-theme="dark"] .feature-title, [data-theme="dark"] .section-title {
    color: #f8fafc;
}

.cta-section {
    padding: 6rem 0;
    background: var(--card-surface);
    border-radius: 32px;
    margin: 4rem 0;
    text-align: center;
    box-shadow: var(--glass-shadow);
}
</style>

<?php if ($flash ?? false): ?>
<div class="container fade-in mt-4">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 d-flex align-items-center mb-5 shadow-sm" style="border-radius: 16px;">
        <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-triangle' : 'check-circle' ?>-fill me-3 fs-4"></i>
        <div class="fw-medium"><?= htmlspecialchars($flash['message']) ?></div>
    </div>
</div>
<?php endif; ?>

<!-- Premium Hero Section -->
<div class="premium-hero">
    <div class="hero-blob hero-blob-1"></div>
    <div class="hero-blob hero-blob-2"></div>
    
    <div class="container-fluid hero-content px-lg-5">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-5 mb-lg-0 pe-lg-5 text-center text-lg-start">
                <div class="d-inline-block px-3 py-1 rounded-pill mb-4" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(10px);">
                    <span class="text-white-80 fw-semibold text-uppercase" style="letter-spacing: 1px; font-size: 0.8rem;">🚀 Career and Placement Guidance Platform</span>
                </div>
                <h1 class="hero-title">
                    Accelerate Your <br>
                    <span class="text-gradient">Professional Journey</span>
                </h1>
                <p class="hero-subtitle text-white-80">
                    A unified platform blending smart career roadmapping, real-time skill analytics, and exclusive industry placements to guarantee outcomes.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                    <?php if (Auth::check()): ?>
                        <a href="<?= BASE_URL ?>/dashboard/<?= Auth::role() ?>" class="premium-btn premium-btn-primary text-decoration-none">
                            Go to Dashboard <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/register" class="premium-btn premium-btn-primary text-decoration-none">
                            Get Started Free <i class="bi bi-rocket-takeoff ms-2"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/login" class="premium-btn premium-btn-outline text-decoration-none">
                            Sign In
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5">
                <!-- 3D Generated Illustration -->
                <div class="hero-image-wrapper">
                    <!-- Ensure the generated image path is correct relative to the base URL -->
                    <img src="<?= BASE_URL ?>/assets/img/hero_background.png" alt="Career Journey Illustration" class="hero-image">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Stats Card - Now Full Width -->
<div class="container-fluid px-0">
    <div class="stats-card row mx-0 g-4">
        <div class="col-md-3 col-6 stat-item border-end border-light">
            <div class="stat-number counters" data-target="99">0%</div>
            <div class="stat-label">Placement Rate</div>
        </div>
        <div class="col-md-3 col-6 stat-item border-end border-light">
            <div class="stat-number counters" data-target="15">0K+</div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="col-md-3 col-6 stat-item border-end border-light">
            <div class="stat-number counters" data-target="500">0+</div>
            <div class="stat-label">Hiring Partners</div>
        </div>
        <div class="col-md-3 col-6 stat-item">
            <div class="stat-number counters" data-target="500">0+</div>
            <div class="stat-label">Placed Students</div>
        </div>
    </div>
</div>

<!-- Trust Banner -->
<div class="trust-banner">
    <div class="container text-center">
        <p class="text-uppercase fw-bold mb-4 stat-label">Trusted by industry leaders globally</p>
        <div class="d-flex flex-wrap justify-content-center align-items-center gap-4 gap-md-5">
            <div class="trust-logo"><i class="bi bi-google me-2" style="color: #DB4437;"></i> Google</div>
            <div class="trust-logo"><i class="bi bi-meta me-2" style="color: #0668E1;"></i> Meta</div>
            <div class="trust-logo"><i class="bi bi-microsoft me-2" style="color: #00A4EF;"></i> Microsoft</div>
            <div class="trust-logo"><i class="bi bi-amazon me-2" style="color: #FF9900;"></i> Amazon</div>
            <div class="trust-logo"><i class="bi bi-stripe me-2" style="color: #635bff;"></i> Stripe</div>
        </div>
    </div>
</div>

<!-- Architecture Grid -->
<div class="container py-5 my-5">
    <div class="text-center mb-5 pb-3">
        <h2 class="section-title">Everything you need to <span class="text-gradient">succeed</span>.</h2>
        <p class="text-muted col-lg-6 mx-auto fs-5">Powerful tools engineered to bridge the gap between academic learning and industry expectations.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-4 col-md-6 fade-up-element">
            <div class="feature-glass-card">
                <div class="feature-icon-box"><i class="bi bi-signpost-split-fill"></i></div>
                <h4 class="feature-title">Dynamic Roadmaps</h4>
                <p class="text-muted mb-0">Follow AI-curated pathways tailored to your dream roles. Track milestones seamlessly from start to finish.</p>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 fade-up-element">
            <div class="feature-glass-card">
                <div class="feature-icon-box"><i class="bi bi-bar-chart-fill"></i></div>
                <h4 class="feature-title">Skill Analytics</h4>
                <p class="text-muted mb-0">Visualize your proficiency and identify exact gaps through our comprehensive competency dashboards.</p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 fade-up-element">
            <div class="feature-glass-card">
                <div class="feature-icon-box"><i class="bi bi-people-fill"></i></div>
                <h4 class="feature-title">1:1 Mentorship</h4>
                <p class="text-muted mb-0">Connect directly with industry alumni and proven faculty for personalized coaching and insights.</p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 fade-up-element">
            <div class="feature-glass-card">
                <div class="feature-icon-box"><i class="bi bi-briefcase-fill"></i></div>
                <h4 class="feature-title">Exclusive Placements</h4>
                <p class="text-muted mb-0">Access high-quality opportunities curated exclusively within our trusted corporate network.</p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 fade-up-element">
            <div class="feature-glass-card">
                <div class="feature-icon-box"><i class="bi bi-file-earmark-code-fill"></i></div>
                <h4 class="feature-title">Real-World Projects</h4>
                <p class="text-muted mb-0">Showcase your portfolio with faculty-evaluated real-world projects that recruiters actually care about.</p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 fade-up-element">
            <div class="feature-glass-card">
                <div class="feature-icon-box"><i class="bi bi-shield-lock-fill"></i></div>
                <h4 class="feature-title">Enterprise Security</h4>
                <p class="text-muted mb-0">Your career data is protected by robust role-based access and military-grade encryption workflows.</p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 fade-up-element">
            <div class="feature-glass-card">
                <div class="feature-icon-box"><i class="bi bi-headset"></i></div>
                <h4 class="feature-title">24/7 Support</h4>
                <p class="text-muted mb-0">Get round-the-clock assistance from our dedicated team to ensure you never miss an opportunity.</p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 fade-up-element">
            <div class="feature-glass-card">
                <div class="feature-icon-box"><i class="bi bi-file-earmark-person-fill"></i></div>
                <h4 class="feature-title">Resume Analyzer</h4>
                <p class="text-muted mb-0">Enhance your profile with AI-driven resume parsing and intelligent formatting suggestions.</p>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 fade-up-element">
            <div class="feature-glass-card">
                <div class="feature-icon-box"><i class="bi bi-stars"></i></div>
                <h4 class="feature-title">Success Stories</h4>
                <p class="text-muted mb-0">Read inspiring journeys from alumni who landed their dream roles using our platform.</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="container my-5">
    <div class="cta-section px-3 px-md-5">
        <h2 class="section-title mb-4">Ready to accelerate your career?</h2>
        <p class="lead text-muted mb-5 col-lg-8 mx-auto">Join thousands of students and faculty members actively using UniHelp to shape the future of professional education.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= BASE_URL ?>/register" class="premium-btn premium-btn-primary text-decoration-none px-5">Join Now</a>
            <a href="mailto:support@unihelp.com" class="premium-btn premium-btn-outline" style="border-width: 2px;">Contact Us</a>
        </div>
    </div>
</div>

<script>
// Simple Counter Animation
document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.counters');
    const speed = 200;

    const animateCounters = () => {
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            if (!target) return;
            
            const suffix = counter.innerText.includes('%') ? '%' : 
                           counter.innerText.includes('K+') ? 'K+' : 
                           counter.innerText.includes('+') ? '+' : '';

            const updateCount = () => {
                const currentVal = +counter.innerText.replace(/[^0-9]/g, '');
                const inc = target / speed;
                if (currentVal < target) {
                    counter.innerText = Math.ceil(currentVal + inc) + suffix;
                    setTimeout(updateCount, 10);
                } else {
                    counter.innerText = target + suffix;
                }
            };
            updateCount();
        });
    };

    // Intersection Observer to trigger counting and animations
    const observerOptions = { threshold: 0.2 };
    
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                if (entry.target.classList.contains('counters')) {
                    animateCounters();
                } else {
                    entry.target.classList.add('visible');
                }
                obs.unobserve(entry.target);
            }
        });
    }, observerOptions);

    counters.forEach(counter => observer.observe(counter));
    document.querySelectorAll('.fade-up-element').forEach(el => observer.observe(el));
});
</script>

<?php require $this->viewPath . 'layouts/footer.php'; ?>
