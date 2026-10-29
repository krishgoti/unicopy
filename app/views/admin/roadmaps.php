<?php $pageTitle = 'Career Roadmaps'; require $viewPath . 'layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Manage Career Roadmaps</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roadmapModal" onclick="clearModal()">
        <i class="bi bi-plus-lg me-2"></i>Add New Roadmap
    </button>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="row g-4">
    <?php foreach ($roadmaps ?? [] as $rm): 
        $steps = json_decode($rm['steps'] ?? '[]', true);
        $skills = json_decode($rm['skills_required'] ?? '[]', true);
        $stepsStr = implode(', ', is_array($steps) ? $steps : []);
        $skillsStr = implode(', ', is_array($skills) ? $skills : []);
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-3 d-flex justify-content-between align-items-start">
                <span class="badge bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($rm['career_goal']) ?></span>
                <div class="dropdown">
                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                        <li>
                            <a class="dropdown-item" href="#" onclick="editRoadmap(
                                <?= $rm['id'] ?>, 
                                <?= htmlspecialchars(json_encode($rm['career_goal']), ENT_QUOTES) ?>, 
                                <?= htmlspecialchars(json_encode($rm['title']), ENT_QUOTES) ?>, 
                                <?= htmlspecialchars(json_encode($rm['description']), ENT_QUOTES) ?>, 
                                <?= htmlspecialchars(json_encode($stepsStr), ENT_QUOTES) ?>, 
                                <?= htmlspecialchars(json_encode($skillsStr), ENT_QUOTES) ?>, 
                                <?= $rm['order_index'] ?>
                            )">
                                <i class="bi bi-pencil me-2"></i>Edit
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="<?= BASE_URL ?>/admin/roadmap/delete" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="roadmap_id" value="<?= $rm['id'] ?>">
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-2"><?= htmlspecialchars($rm['title']) ?></h5>
                <p class="text-muted small mb-3"><?= htmlspecialchars($rm['description']) ?></p>
                <div class="mb-2">
                    <small class="fw-bold text-dark-mode-light">Steps:</small>
                    <p class="small text-muted text-truncate"><?= htmlspecialchars($stepsStr) ?></p>
                </div>
                <div>
                    <small class="fw-bold text-dark-mode-light">Skills:</small>
                    <div>
                        <?php foreach (array_slice($skills, 0, 3) as $skill): ?>
                        <span class="badge bg-light border text-dark me-1 mb-1"><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                        <?php if (count($skills) > 3): ?>
                        <span class="badge bg-light border text-dark me-1 mb-1">+<?= count($skills) - 3 ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Roadmap Modal -->
<div class="modal fade" id="roadmapModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/roadmap/save">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="roadmap_id" id="roadmapId">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Roadmap</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Career Goal (ID)</label>
                            <input type="text" name="career_goal" id="inputGoal" class="form-control" placeholder="e.g. software" required>
                            <div class="form-text">Unique identifier for this path</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" id="inputTitle" class="form-control" placeholder="e.g. Software Engineer" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="inputDesc" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Steps (Comma separated)</label>
                            <textarea name="steps" id="inputSteps" class="form-control" rows="3" placeholder="Step 1, Step 2, Step 3..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Skills Required (Comma separated)</label>
                            <input type="text" name="skills_required" id="inputSkills" class="form-control" placeholder="Java, SQL, React...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Order Index</label>
                            <input type="number" name="order_index" id="inputOrder" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editRoadmap(id, goal, title, desc, steps, skills, order) {
    document.getElementById('roadmapId').value = id;
    document.getElementById('inputGoal').value = goal;
    document.getElementById('inputGoal').readOnly = true;
    document.getElementById('inputTitle').value = title;
    document.getElementById('inputDesc').value = desc;
    document.getElementById('inputSteps').value = steps;
    document.getElementById('inputSkills').value = skills;
    document.getElementById('inputOrder').value = order;
    
    document.getElementById('modalTitle').textContent = 'Edit Roadmap';
    
    new bootstrap.Modal(document.getElementById('roadmapModal')).show();
}

function clearModal() {
    document.getElementById('roadmapId').value = '';
    document.getElementById('inputGoal').value = '';
    document.getElementById('inputGoal').readOnly = false;
    document.getElementById('inputTitle').value = '';
    document.getElementById('inputDesc').value = '';
    document.getElementById('inputSteps').value = '';
    document.getElementById('inputSkills').value = '';
    document.getElementById('inputOrder').value = '0';
    document.getElementById('modalTitle').textContent = 'Add New Roadmap';
}
</script>


<?php require $viewPath . 'layouts/footer.php'; ?>
