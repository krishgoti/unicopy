<?php $pageTitle = 'Analytics'; require $viewPath . 'layouts/header.php'; ?>

<h2 class="mb-4">Platform Analytics</h2>

<div class="row g-4 mb-4">
    <?php foreach ($userStats ?? [] as $stat): ?>
    <div class="col-md-3">
        <div class="card stat-card p-3 border-0 shadow-sm">
            <h6 class="text-muted text-uppercase small ls-1"><?= ucfirst($stat['role']) ?></h6>
            <h3 class="mb-0 fw-bold text-dark-mode-light"><?= $stat['count'] ?></h3>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($userStats)): ?>
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm"><i class="bi bi-info-circle me-2"></i>No user data available yet.</div>
    </div>
    <?php endif; ?>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 fw-bold">Student Skill Readiness</h5>
            </div>
            <div class="card-body">
                <?php if (empty($skillStats)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bar-chart fs-1 opacity-25"></i>
                    <p class="small mb-0 mt-2">No skill data available to display chart.</p>
                </div>
                <?php else: ?>
                <canvas id="skillChart" height="200"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 fw-bold">Recent Signups</h5>
            </div>
            <div class="card-body">
                 <?php if (empty($applicationTrends)): ?>
                 <p class="text-muted small">No data available.</p>
                 <?php else: ?>
                 <ul class="list-group list-group-flush">
                     <?php foreach($applicationTrends as $trend): ?>
                     <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                         <span><?= date('M d', strtotime($trend['date'])) ?></span>
                         <span class="badge bg-primary rounded-pill"><?= $trend['count'] ?> Apps</span>
                     </li>
                     <?php endforeach; ?>
                 </ul>
                 <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($skillStats)): ?>
<?php
$skillLabels = array_column($skillStats, 'name');
$skillData = array_map(function($s) { return round($s['avg_progress'] ?? 0); }, $skillStats);
$extraScripts = '<script>
document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById("skillChart");
    if (ctx && typeof Chart !== "undefined") {
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ' . json_encode($skillLabels) . ',
                datasets: [{ label: "Avg Progress %", data: ' . json_encode($skillData) . ',
                    backgroundColor: "rgba(13, 110, 253, 0.7)", borderColor: "rgba(13, 110, 253, 1)", borderWidth: 1 }]
            },
            options: { 
                responsive: true, 
                plugins: {
                    legend: { labels: { color: getComputedStyle(document.documentElement).getPropertyValue("--bs-body-color") } }
                },
                scales: { 
                    y: { beginAtZero: true, max: 100, grid: { color: "rgba(0,0,0,0.1)" }, ticks: { color: getComputedStyle(document.documentElement).getPropertyValue("--bs-body-color") } },
                    x: { grid: { display: false }, ticks: { color: getComputedStyle(document.documentElement).getPropertyValue("--bs-body-color") } }
                } 
            }
        });
    }
});
</script>';
?>
<?php endif; ?>

<?php require $viewPath . 'layouts/footer.php'; ?>
