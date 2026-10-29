<?php $pageTitle = 'Resume Review'; require $viewPath . 'layouts/header.php'; ?>

<h2 class="mb-4">Resume Review</h2>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0 py-3">
        <h5 class="mb-0 fw-bold">Students with Resumes</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($students)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-file-earmark-person fs-1 opacity-25"></i>
            <p class="small mb-0 mt-2">No students have uploaded resumes yet.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="bg-light"><tr><th class="ps-4">Student</th><th>Department</th><th>Resume</th><th>Feedback</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($students ?? [] as $s): ?>
                    <tr>
                        <td class="ps-4 fw-bold text-dark-mode-light"><?= htmlspecialchars($s['full_name']) ?></td>
                        <td><?= htmlspecialchars($s['department'] ?? '-') ?></td>
                        <td>
                            <?php if (!empty($s['resume_path'])): ?>
                            <a href="<?= BASE_URL ?>/public/uploads/resumes/<?= htmlspecialchars($s['resume_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="bi bi-eye me-1"></i>View</a>
                            <?php else: ?>-<?php endif; ?>
                        </td>
                        <td class="text-muted small fst-italic"><?= !empty($s['resume_feedback']) ? substr($s['resume_feedback'], 0, 50) . '...' : '-' ?></td>
                        <td>
                            <form method="POST" action="<?= BASE_URL ?>/resume/feedback" class="d-flex gap-2">
                                <input type="hidden" name="student_id" value="<?= $s['user_id'] ?>">
                                <input type="text" name="feedback" class="form-control form-control-sm d-inline-block" style="min-width: 150px;" placeholder="Add feedback" required>
                                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>


<?php require $viewPath . 'layouts/footer.php'; ?>
