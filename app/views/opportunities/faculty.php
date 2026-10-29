<?php $pageTitle = 'Manage Opportunities'; require $viewPath . 'layouts/header.php'; ?>

<div class="row mb-5 align-items-center">
    <div class="col-md-6">
        <h1 class="fw-black text-primary mb-1">Admin Placement Hub</h1>
        <p class="text-muted lead mb-0">Centralized control for job postings and applicant tracking.</p>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="<?= BASE_URL ?>/opportunities/post" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-lg">
            <i class="bi bi-plus-circle-fill me-2"></i> POST NEW JOB
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        <h5 class="fw-black mb-4"><i class="bi bi-list-stars text-warning me-2"></i>Your Posted Opportunities</h5>
        
        <div class="glass-stat-card p-0 overflow-hidden border-0 shadow-sm rounded-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light bg-opacity-50">
                        <tr>
                            <th class="ps-4 py-3 border-0">Opportunity Detail</th>
                            <th class="py-3 border-0 text-center">Type</th>
                            <th class="py-3 border-0">Eligibility (GPA)</th>
                            <th class="py-3 border-0">Total Applicants</th>
                            <th class="py-3 border-0">Deadline</th>
                            <th class="pe-4 py-3 border-0 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myOpportunities ?? [] as $opp): ?>
                        <?php 
                            // Query count of applicants for each opp
                            $stmt = $this->db->prepare("SELECT COUNT(*) FROM applications WHERE opportunity_id = ?");
                            $stmt->execute([$opp['id']]);
                            $appCount = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td class="ps-4 py-4">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3 me-3 text-center" style="width: 45px; height: 45px;">
                                        <i class="bi bi-building fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-black text-dark-mode-light mb-0"><?= htmlspecialchars($opp['company_name']) ?></div>
                                        <small class="text-muted fw-bold"><?= htmlspecialchars($opp['role']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-1"><?= strtoupper($opp['type']) ?></span>
                            </td>
                            <td>
                                <span class="text-dark-mode-light small fw-bold"><i class="bi bi-award me-1 text-warning"></i> Min <?= $opp['min_cgpa'] ?: 'Any' ?></span>
                            </td>
                            <td class="fw-black text-primary px-3 h5 mb-0">
                                <?= $appCount ?>
                            </td>
                            <td>
                                <small class="text-muted fw-bold"><?= !empty($opp['apply_deadline']) ? date('M d, Y', strtotime($opp['apply_deadline'])) : 'No Deadline' ?></small>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="<?= BASE_URL ?>/opportunities/applications?id=<?= $opp['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-black">
                                        VIEW APPLIANCES <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($myOpportunities)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png" style="width: 150px; opacity: 0.5;">
                                <h5 class="fw-bold text-muted mt-3">You haven't posted any opportunities yet.</h5>
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
