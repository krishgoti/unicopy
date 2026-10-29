<?php $pageTitle = 'Faculty Dashboard'; require $viewPath . 'layouts/header.php'; ?>

<style>
.faculty-hero {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-radius: 32px;
    padding: 3rem;
    color: white;
    margin-bottom: 2.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.3);
}

.stat-icon-box {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
}
.glass-stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
}
[data-theme="dark"] .glass-stat-card {
    background: #1e293b;
    border-color: rgba(255,255,255,0.05);
}

[data-theme="dark"] .glass-stat-card {
    background: #1e293b;
    border-color: rgba(255,255,255,0.05);
}
</style>

<div class="fade-in">
    <!-- Faculty Hero Section -->
    <div class="faculty-hero">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-20 px-3 py-2 rounded-pill fw-bold">FACULTY COMMAND CENTER</span>
                </div>
                <h1 class="display-4 fw-black mb-2">Welcome Back, Counselor</h1>
                <p class="text-white-50 lead mb-0">Monitor campus readiness and student success metrics efficiently.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="p-4 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-10">
                    <div class="small fw-bold text-white-50 mb-1">FACULTY STATUS</div>
                    <div class="badge bg-success rounded-pill fw-black px-3">ACTIVE COUNSELOR</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4">
                <div class="stat-icon-box bg-primary bg-opacity-10 text-primary mx-auto">
                    <i class="bi bi-file-earmark-person"></i>
                </div>
                <div class="small fw-bold text-muted mb-1">RESUMES REVIEWED</div>
                <div class="h3 fw-black mb-0"><?= $resumeReviews ?? 0 ?></div>
                <a href="<?= BASE_URL ?>/resume" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4">
                <div class="stat-icon-box bg-warning bg-opacity-10 text-warning mx-auto">
                    <i class="bi bi-people"></i>
                </div>
                <div class="small fw-bold text-muted mb-1">PENDING REQUESTS</div>
                <div class="h3 fw-black mb-0"><?= $pendingMentorship ?? 0 ?></div>
                <a href="<?= BASE_URL ?>/mentorship" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4">
                <div class="stat-icon-box bg-info bg-opacity-10 text-info mx-auto">
                    <i class="bi bi-patch-question"></i>
                </div>
                <div class="small fw-bold text-muted mb-1">MOCK TESTS</div>
                <div class="h3 fw-black mb-0">Active</div>
                <a href="<?= BASE_URL ?>/mock-tests/faculty" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Main: Skill Analytics -->
        <div class="col-lg-8">
            <h5 class="section-title fw-black mb-4"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Campus Skill Analytics</h5>
            <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4 bg-white">
                <canvas id="skillChart" height="200"></canvas>
            </div>
        </div>

        <!-- Sidebar: Quick Actions -->
        <div class="col-lg-4">
            <h5 class="section-title fw-black mb-4"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Management Hub</h5>
            <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-grid gap-3">
                    <a href="<?= BASE_URL ?>/resume" class="btn btn-outline-warning border-2 rounded-pill py-3 fw-bold text-start ps-4 position-relative">
                        <i class="bi bi-file-earmark-pdf me-3"></i> Review Pipeline
                        <i class="bi bi-chevron-right position-absolute end-0 me-4"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/mentorship" class="btn btn-outline-success border-2 rounded-pill py-3 fw-bold text-start ps-4 position-relative">
                        <i class="bi bi-people me-3"></i> Mentorship Hub
                        <i class="bi bi-chevron-right position-absolute end-0 me-4"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/mock-tests/faculty" class="btn btn-outline-info border-2 rounded-pill py-3 fw-bold text-start ps-4 position-relative">
                        <i class="bi bi-patch-question me-3"></i> Mock Test Portal
                        <i class="bi bi-chevron-right position-absolute end-0 me-4"></i>
                    </a>
                </div>
            </div>
            
            <div class="glass-stat-card mt-4 p-4 border-0 shadow-sm rounded-4 text-white" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);">
                <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Did you know?</h6>
                <p class="small opacity-75 mb-0">Students with more than 3 completed projects are 45% more likely to be shortlisted for Tier-1 companies.</p>
            </div>
        </div>
    </div>
</div>

<?php
$skillLabels = array_column($skillAnalytics ?? [], 'name');
$skillData = array_map(function($s) { return round($s['avg_progress'] ?? 0); }, $skillAnalytics ?? []);
$extraScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script><script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById("skillChart");
    if (ctx) {
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ' . json_encode($skillLabels) . ',
                datasets: [{ 
                    label: "Avg Readiness %", 
                    data: ' . json_encode($skillData) . ',
                    backgroundColor: "rgba(99, 102, 241, 0.5)", 
                    borderColor: "rgba(99, 102, 241, 1)", 
                    borderWidth: 2,
                    borderRadius: 10
                }]
            },
            options: { 
                responsive: true, 
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, max: 100, grid: { display: false } },
                    x: { grid: { display: false } }
                } 
            }
        });
    }
});
</script>';
?>

<?php require $viewPath . 'layouts/footer.php'; ?>
