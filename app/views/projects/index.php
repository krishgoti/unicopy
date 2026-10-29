<?php $pageTitle = 'Student Projects'; require $viewPath . 'layouts/header.php'; ?>

<div class="container-fluid pb-5">
    <!-- Premium Hero Section -->
    <div class="dash-hero p-5 rounded-4 shadow-lg mb-5 border-0 overflow-hidden reveal-on-scroll">
        <div class="row align-items-center position-relative z-1">
            <div class="col-md-7">
                <h1 class="fw-black text-white mb-2 display-5" style="letter-spacing:-1.5px;">Student <span class="text-gradient-gold">Showcase</span></h1>
                <p class="text-white-50 lead mb-4 opacity-75">Discover innovative architectures and engineering masterpieces from our global talent community.</p>
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-gold px-4 py-2 rounded-pill fw-black shadow-lg d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                        <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i> PUBLISH YOUR PROJECT
                    </button>
                </div>
            </div>
            <div class="col-md-5 text-md-end d-none d-md-block">
                <div class="hero-ring-container d-inline-block p-4 glass-card rounded-circle">
                    <div class="hero-ring-inner bg-gold-gradient rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width:120px; height:120px;">
                        <i data-lucide="rocket" class="text-white" style="width: 48px; height: 48px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Flash Messages Feedback -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-lg rounded-4 mb-5 p-4 reveal-on-scroll">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> bg-opacity-10 text-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> rounded-circle p-2">
                    <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle' : 'check-circle' ?>-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0"><?= $flash['type'] === 'error' ? 'Submission Error' : 'Success' ?></h6>
                    <p class="mb-0 small opacity-75"><?= htmlspecialchars($flash['message']) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Project Matrix -->
    <div class="row g-4 mb-5">
        <?php if (empty($projects)): ?>
            <div class="col-12 text-center py-5 glass-card rounded-4 border-dashed reveal-on-scroll">
                <div class="mb-4">
                    <i class="bi bi-inbox display-1 text-primary opacity-10"></i>
                </div>
                <h3 class="fw-black text-main mb-3">Innovation Pending</h3>
                <p class="text-muted lead mb-4">No projects have been cataloged yet. Be the first to pioneer our showcase.</p>
                <button type="button" class="btn btn-gold px-5 py-3 rounded-pill fw-black shadow-lg" data-bs-toggle="modal" data-bs-target="#addProjectModal">SHARE PROJECT</button>
            </div>
        <?php else: ?>
            <?php foreach ($projects as $proj): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="glass-card p-3 h-100 d-flex flex-column transition-all hover-lift border-0 shadow-lg overflow-hidden reveal-on-scroll">
                    <!-- Project Preview -->
                    <div class="project-overlay flex-shrink-0 position-relative rounded-4 overflow-hidden" style="height: 220px;">
                        <?php if (!empty($proj['image_path'])): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($proj['image_path']) ?>" alt="Project Image" class="w-100 h-100 object-fit-cover transition-all" style="filter: brightness(0.9);">
                        <?php else: ?>
                            <div class="h-100 d-flex align-items-center justify-content-center bg-main bg-opacity-5">
                                <i data-lucide="code-2" class="text-primary opacity-25" style="width: 48px; height: 48px;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Floating Badge -->
                        <div class="position-absolute top-0 end-0 p-3">
                            <div class="glass-pill text-dark border-0 shadow-sm px-3 py-2" style="font-size: 0.65rem; background: rgba(255,255,255,0.7); letter-spacing: 1px;">
                                <i data-lucide="sparkles" class="me-1" style="width: 12px; height: 12px;"></i> PROJECT
                            </div>
                        </div>
                    </div>

                    <!-- Project Metadata -->
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <div class="mb-3">
                            <h5 class="fw-black text-gradient-gold mb-1 text-uppercase letter-spacing-1" style="font-size: 1.1rem;"><?= htmlspecialchars($proj['project_name']) ?></h5>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="bg-gold-gradient rounded-circle" style="width: 5px; height: 5px;"></span>
                                <small class="text-dark text-uppercase fw-black" style="font-size: 0.65rem; letter-spacing: 1.5px; opacity: 0.8;">Student Innovation</small>
                            </div>
                        </div>

                        <p class="text-dark mb-4 flex-grow-1" style="line-height: 1.7; opacity: 0.85; font-size: 0.85rem;">
                            <?= nl2br(htmlspecialchars($proj['description'])) ?>
                        </p>
                        
                        <!-- Tech Stack Tags -->
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <?php 
                                $skillsList = array_map('trim', explode(',', $proj['skills']));
                                foreach ($skillsList as $sk): 
                                    if (empty($sk)) continue;
                            ?>
                                <span class="glass-pill px-3 py-1 fw-black text-dark" style="font-size:0.65rem; background: rgba(0, 0, 0, 0.05); border: 1px solid rgba(0,0,0,0.05);">#<?= htmlspecialchars($sk) ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Card Footer: Contributor & Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-4 border-top border-white border-opacity-10">
                            <div class="d-flex align-items-center">
                                <div class="p-1 bg-gold-gradient rounded-circle me-3 shadow-sm d-flex align-items-center justify-content-center" style="width:34px; height:34px;">
                                    <i data-lucide="user" class="text-white" style="width: 16px; height: 16px;"></i>
                                </div>
                                <div>
                                    <small class="d-block text-muted text-uppercase fw-black mb-0" style="font-size: 0.55rem; letter-spacing: 0.5px;">Architect</small>
                                    <small class="fw-black text-dark text-uppercase" style="letter-spacing:0.5px; font-size: 0.8rem;"><?= htmlspecialchars($proj['student_name']) ?></small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php 
                                if (!empty($proj['github_link'])): 
                                    $ghUrl = $proj['github_link'];
                                    if (!str_starts_with($ghUrl, 'http://') && !str_starts_with($ghUrl, 'https://')) {
                                        $ghUrl = 'https://' . $ghUrl;
                                    }
                                ?>
                                    <a href="<?= htmlspecialchars($ghUrl) ?>" target="_blank" class="btn btn-sm glass-pill p-2 transition-all hover-lift d-flex align-items-center justify-content-center text-dark" title="View Source on GitHub" style="background: rgba(255,255,255,0.8); border: 1px solid rgba(0,0,0,0.1); width: 38px; height: 38px;">
                                        <i class="bi bi-github" style="font-size: 1.2rem;"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (Auth::id() == $proj['user_id'] || (isset(Auth::user()['role']) && Auth::user()['role'] === 'admin')): ?>
                                    <button type="button" class="btn btn-sm glass-pill text-danger p-2 transition-all hover-lift d-flex align-items-center justify-content-center" onclick="confirmDelete(<?= $proj['id'] ?>)" title="Delete Project" style="background: rgba(255,255,255,0.8); border: 1px solid rgba(220,53,69,0.1); width: 38px; height: 38px;">
                                        <i class="bi bi-trash3-fill" style="font-size: 1.1rem;"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-white border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom bg-light bg-opacity-50 p-4">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-primary bg-opacity-10 rounded-3 me-3 d-flex align-items-center justify-content-center">
                        <i data-lucide="folder-plus" class="text-primary" style="width: 20px; height: 20px;"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-dark mb-0">Submit Project to Showcase</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/projects/add" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-2">Project Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" name="project_name" required placeholder="e.g. Neural Nexus AI">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-2">Project Description <span class="text-danger">*</span></label>
                            <textarea class="form-control bg-light border-0" style="min-height: 120px;" name="description" rows="4" required placeholder="Briefly describe what your project does, the problem it solves, and its architecture..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-2">Technology Stack <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light border-0" name="skills" placeholder="React, Node.js, Python" required>
                            <div class="form-text small">Comma separated technologies</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-2">GitHub / Repository Link</label>
                            <input type="url" class="form-control bg-light border-0" name="github_link" placeholder="https://github.com/...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-2">Project Screenshot <span class="text-danger">*</span></label>
                            <input class="form-control bg-light border-0" type="file" name="project_image" accept="image/*" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 p-md-5 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm d-flex align-items-center gap-2">
                        Share Project <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
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
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}
.hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3) !important;
}
.project-overlay img {
    transition: transform 0.6s cubic-bezier(0.33, 1, 0.68, 1);
}
.hover-lift:hover .project-overlay img {
    transform: scale(1.1);
}
.modal-content input:focus, .modal-content textarea:focus {
    background: #fff !important;
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    outline: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Relocate modal to document root to prevent stacking context/overflow issues (Z-index/frozen UI fix)
    const projectModal = document.getElementById('addProjectModal');
    if (projectModal) {
        document.body.appendChild(projectModal);
    }
});

function confirmDelete(projectId) {
    if (confirm("Are you certain you wish to remove this project from the showcase?")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/projects/delete';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?= $csrf_token ?>';
        form.appendChild(csrfInput);

        const projectInput = document.createElement('input');
        projectInput.type = 'hidden';
        projectInput.name = 'project_id';
        projectInput.value = projectId;
        form.appendChild(projectInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
