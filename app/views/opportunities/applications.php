<?php require $viewPath . 'layouts/header.php'; ?>

<div class="row mb-5 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-black text-main mb-1" style="letter-spacing:-1px;">Applicants for <span class="text-gradient-gold"><?= htmlspecialchars($opportunity['role']) ?></span></h2>
        <p class="text-muted fw-bold text-uppercase small" style="letter-spacing:1px;"><?= htmlspecialchars($opportunity['company_name']) ?> • Recruitment Portal • <?= ucfirst($opportunity['type']) ?></p>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="<?= BASE_URL ?>/opportunities" class="btn btn-outline-gold rounded-pill px-4 fw-black shadow-sm"><i class="bi bi-arrow-left me-2"></i>Back to Postings</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="glass-card p-0 overflow-hidden border-0 shadow-lg rounded-4 transition-all">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-main bg-opacity-5">
                        <tr>
                            <th class="ps-4 py-4 border-0 text-primary small fw-black text-uppercase" style="letter-spacing:1px;">Talent Identity</th>
                            <th class="py-4 border-0 text-primary small fw-black text-uppercase" style="letter-spacing:1px;">Domain</th>
                            <th class="py-4 border-0 text-primary small fw-black text-uppercase" style="letter-spacing:1px;">Timeline</th>
                            <th class="py-4 border-0 text-primary small fw-black text-uppercase" style="letter-spacing:1px;">Credentials</th>
                            <th class="py-4 border-0 text-primary small fw-black text-uppercase" style="letter-spacing:1px;">Status</th>
                            <th class="pe-4 py-4 border-0 text-primary small fw-black text-uppercase text-end" style="letter-spacing:1px;">Decision Hub</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php foreach ($applications ?? [] as $app): ?>
                        <tr class="transition-all hover-lift">
                            <td class="ps-4 py-4">
                                <div class="d-flex align-items-center">
                                    <div class="p-3 bg-gold-gradient text-white rounded-4 me-3 fw-black shadow-sm" style="width:50px; height:50px; display:flex; align-items:center; justify-content:center;">
                                        <?= strtoupper(substr($app['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-black text-main"><?= htmlspecialchars($app['full_name']) ?></div>
                                        <div class="small text-muted fw-bold"><?= htmlspecialchars($app['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge glass-pill text-muted rounded-pill px-3 py-2 fw-bold"><?= htmlspecialchars($app['department']) ?></span></td>
                            <td>
                                <div class="small fw-bold text-main"><?= date('M d, Y', strtotime($app['applied_at'])) ?></div>
                                <div class="small text-muted opacity-50"><?= date('H:i A', strtotime($app['applied_at'])) ?></div>
                            </td>
                            <td>
                                <?php if ($app['resume_path']): ?>
                                    <a href="<?= BASE_URL ?>/public/uploads/resumes/<?= $app['resume_path'] ?>" class="btn btn-sm btn-outline-primary border-2 rounded-pill px-3 fw-black" target="_blank">
                                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> VIEW CV
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small italic opacity-50">Pending Upload</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-pill-gold pill-<?= $app['status'] ?> px-3 py-2 fw-black small">
                                    <?= strtoupper($app['status']) ?>
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <form method="POST" action="<?= BASE_URL ?>/opportunities/update-status" class="d-flex gap-2 justify-content-end align-items-center">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                    <select name="status" class="form-select form-select-sm rounded-pill border-gold bg-transparent fw-bold text-main px-3" style="min-width: 130px;" onchange="this.form.submit()">
                                        <option value="applied" <?= $app['status'] == 'applied' ? 'selected' : '' ?> class="bg-card">Applied</option>
                                        <option value="shortlisted" <?= $app['status'] == 'shortlisted' ? 'selected' : '' ?> class="bg-card">Shortlist</option>
                                        <option value="selected" <?= $app['status'] == 'selected' ? 'selected' : '' ?> class="bg-card">Select</option>
                                        <option value="rejected" <?= $app['status'] == 'rejected' ? 'selected' : '' ?> class="bg-card">Reject</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-person-slash display-1 text-primary opacity-10 d-block mb-3"></i>
                                <h5 class="fw-black text-main opacity-50">No candidates detected.</h5>
                                <p class="text-muted small">This listing is currently awaiting talent submissions.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require $viewPath . 'layouts/footer.php'; ?>
