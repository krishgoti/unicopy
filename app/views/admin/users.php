<?php $pageTitle = 'Manage Users'; require $viewPath . 'layouts/header.php'; ?>

<h2 class="mb-4">Manage Users</h2>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Department</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($users ?? [] as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['full_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge bg-primary"><?= ucfirst($u['role']) ?></span></td>
                        <td><span class="badge bg-<?= in_array($u['status'], ['active','approved']) ? 'success' : ($u['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= ucfirst($u['status']) ?></span></td>
                        <td><?= htmlspecialchars($u['department'] ?? '-') ?></td>
                        <td>
                            <?php if ($u['role'] !== 'admin' && $u['id'] != Auth::id()): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/delete-user" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<?php require $viewPath . 'layouts/footer.php'; ?>
