<?php $pageTitle = 'Interview Experiences'; require $viewPath . 'layouts/header.php'; ?>

<style>
/* ── Hero Section ── */
.interview-hero {
    background: var(--hero-bg-gradient);
    border-radius: 24px;
    padding: 3rem;
    color: white;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}
.hero-icon-label {
    width: 60px; height: 60px;
    background: rgba(255,255,255,0.1);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem;
    border: 1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

/* ── Filter Glass Card ── */
.filter-glass {
    background: var(--card-bg);
    border-radius: 20px;
    border: 1px solid var(--border-color);
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-md);
}

/* ── Experience Card ── */
.exp-card {
    background: var(--card-bg);
    border-radius: 24px;
    border: 1px solid var(--border-color);
    overflow: hidden;
    transition: var(--transition);
    height: 100%;
    display: flex;
    flex-direction: column;
}
.exp-card:hover { transform: translateY(-8px); border-color: var(--primary); box-shadow: var(--shadow-lg); }

.exp-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}
.company-icon-wrap {
    width: 48px; height: 48px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    background: rgba(var(--primary-rgb), 0.1);
    color: var(--primary);
    flex-shrink: 0;
}

.diff-badge {
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.diff-easy   { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
.diff-medium { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
.diff-hard   { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

.exp-body { padding: 1.5rem; flex-grow: 1; }
.exp-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
    background: rgba(var(--primary-rgb), 0.03);
}

.contributor-chip {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 12px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
}
.contributor-avatar {
    width: 32px; height: 32px;
    border-radius: 10px;
    background: var(--gold-gradient);
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800;
    font-size: 0.8rem;
}
</style>

<div class="interview-hero">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon-label">💡</div>
            <div>
                <h2 class="mb-1 fw-black text-white" style="letter-spacing:-1px;">Interview Experiences</h2>
                <p class="mb-0 opacity-75 text-white">Explore real interview journeys from fellow students.</p>
            </div>
        </div>
        <?php if ($canPost ?? false): ?>
            <a href="<?= BASE_URL ?>/interview-experiences/create" class="btn btn-gold px-4 py-2">
                <i class="bi bi-plus-circle me-2"></i>Share Your Story
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-sm rounded-4 mb-4">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<form method="GET" class="filter-glass">
    <div class="row g-3 align-items-end">
        <div class="col-lg-4">
            <label class="form-label small fw-bold text-muted text-uppercase">Search Keyword</label>
            <input type="text" name="search" class="form-control rounded-3" placeholder="Company, role, topics..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-lg-3">
            <label class="form-label small fw-bold text-muted text-uppercase">Company</label>
            <input type="text" name="company" class="form-control rounded-3" placeholder="e.g. Google" value="<?= htmlspecialchars($_GET['company'] ?? '') ?>">
        </div>
        <div class="col-lg-3">
            <label class="form-label small fw-bold text-muted text-uppercase">Level</label>
            <select name="difficulty" class="form-select rounded-3">
                <option value="">All Difficulty</option>
                <option value="easy" <?= (($_GET['difficulty'] ?? '') === 'easy') ? 'selected' : '' ?>>Easy</option>
                <option value="medium" <?= (($_GET['difficulty'] ?? '') === 'medium') ? 'selected' : '' ?>>Medium</option>
                <option value="hard" <?= (($_GET['difficulty'] ?? '') === 'hard') ? 'selected' : '' ?>>Hard</option>
            </select>
        </div>
        <div class="col-lg-2">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-gold flex-grow-1">Filter</button>
                <a href="<?= BASE_URL ?>/interview-experiences" class="btn btn-outline-secondary px-3"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </div>
    </div>
</form>

<div class="row g-4">
    <?php foreach ($experiences ?? [] as $exp): ?>
        <?php
        $short = $exp['description'] ?? $exp['tips'] ?? $exp['questions'] ?? '';
        $short = trim((string)$short);
        if (strlen($short) > 130) $short = substr($short, 0, 130) . '...';
        $who = $exp['contributor_name'] ?? ($exp['full_name'] ?? (explode('@', $exp['email'])[0] ?? 'Contributor'));
        $difficulty = $exp['difficulty'] ?? 'medium';
        $diffClass = 'diff-' . $difficulty;
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="exp-card">
                <div class="exp-header">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="company-icon-wrap">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-truncate" style="max-width: 140px;">
                                    <?= htmlspecialchars($exp['company_name']) ?>
                                </h5>
                                <div class="small text-primary fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">
                                    <?= ucfirst($exp['experience_type'] ?? 'Placement') ?>
                                </div>
                            </div>
                        </div>
                        <span class="diff-badge <?= $diffClass ?>">
                            <?= ucfirst($difficulty) ?>
                        </span>
                    </div>
                    <div class="fw-bold text-main" style="font-size: 0.95rem;">
                        <i class="bi bi-person-badge me-1 text-muted"></i> Role: <?= htmlspecialchars($exp['role']) ?>
                    </div>
                </div>

                <div class="exp-body text-muted small lh-lg">
                    <?php if ($short !== ''): ?>
                        <?= htmlspecialchars($short) ?>
                    <?php else: ?>
                        <span class="fst-italic">No detailed description available.</span>
                    <?php endif; ?>
                </div>

                <div class="exp-footer">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="contributor-chip">
                            <div class="contributor-avatar">
                                <?= strtoupper(substr($who, 0, 1)) ?>
                            </div>
                            <div style="line-height:1.2;">
                                <div class="fw-bold text-main" style="font-size: 0.75rem;"><?= htmlspecialchars($who) ?></div>
                                <div class="text-muted" style="font-size: 0.65rem;"><?= ucfirst($exp['user_role'] ?? 'Student') ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="<?= BASE_URL ?>/interview-experiences/show/<?= (int)$exp['id'] ?>" class="btn btn-gold w-100 rounded-pill fw-bold btn-sm py-2">
                        View Full Experience <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($experiences)): ?>
    <div class="col-12 py-5 text-center">
        <div class="d-inline-flex align-items-center justify-content-center bg-gold-gradient bg-opacity-10 rounded-circle mb-3" style="width: 80px; height: 80px; background: rgba(var(--primary-rgb), 0.1);">
            <i class="bi bi-journal-x fs-1 text-primary"></i>
        </div>
        <h4 class="fw-bold">No Experiences Found</h4>
        <p class="text-muted">Try adjusting your filters or be the first to share one!</p>
        <a href="<?= BASE_URL ?>/interview-experiences/create" class="btn btn-gold mt-2">Share Your Experience</a>
    </div>
<?php endif; ?>

<?php require $viewPath . 'layouts/footer.php'; ?>

