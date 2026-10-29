<?php $pageTitle = 'System Settings'; require $viewPath . 'layouts/header.php'; ?>

<div class="fade-in pb-5">
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <h1 class="fw-black text-primary mb-1">Platform Configuration</h1>
            <p class="text-muted lead pb-0 mb-0">Manage global site settings, communication channels, and API credentials.</p>
        </div>
        <div class="col-md-4 text-md-end mt-4 mt-md-0">
            <button type="submit" form="settingsForm" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-lg">SAVE ALL CHANGES</button>
        </div>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> border-0 shadow-sm mb-4">
        <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-triangle-fill' : 'check-circle-fill' ?> me-2"></i>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <form id="settingsForm" method="POST" action="<?= BASE_URL ?>/admin/settings">
        <div class="row g-5">
            <div class="col-lg-8">
                <!-- Site Aesthetics -->
                <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4 bg-white mb-5">
                    <h5 class="fw-black mb-4"><i class="bi bi-palette text-primary me-2"></i>General Identity</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Platform Name</label>
                            <input type="text" name="platform_name" class="form-control rounded-pill px-4 py-2" value="UniHelp" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Support Email</label>
                            <input type="email" name="support_email" class="form-control rounded-pill px-4 py-2" value="krishgoti19@gmail.com" readonly>
                        </div>
                    </div>
                </div>

                <!-- Communication Protocol (SMTP) -->
                <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4 bg-white mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-black mb-0"><i class="bi bi-envelope-at text-success me-2"></i>Email (SMTP) Gateway</h5>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">ENCRYPTED</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">SMTP Host</label>
                            <input type="text" class="form-control rounded-pill px-4 py-2" value="smtp.gmail.com" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Port</label>
                            <input type="text" class="form-control rounded-pill px-4 py-2" value="587" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Authenticated User</label>
                            <input type="text" class="form-control rounded-pill px-4 py-2" value="krishgoti19@gmail.com" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">SMTP Password</label>
                            <input type="password" class="form-control rounded-pill px-4 py-2" value="••••••••" readonly>
                        </div>
                    </div>
                </div>

                <!-- API Connectivity -->
                <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4 bg-white">
                    <h5 class="fw-black mb-4"><i class="bi bi-cpu text-warning me-2"></i>AI Core (Gemini API)</h5>
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Active API Model</label>
                            <select name="gemini_model" id="geminiModelSelect" class="form-select rounded-pill px-4 py-2">
                                <?php
                                $currentModel = defined('GEMINI_MODEL') ? GEMINI_MODEL : 'gemini-2.5-flash';
                                $models = [
                                    'gemini-2.5-flash' => 'Gemini 2.5 Flash (Default)',
                                    'gemini-2.5-pro' => 'Gemini 2.5 Pro',
                                    'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash-Lite',
                                    'gemini-2.0-flash' => 'Gemini 2.0 Flash',
                                    'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash-Lite',
                                    'gemini-3.0-flash' => 'Gemini 3.0 Flash'
                                ];
                                foreach ($models as $val => $label):
                                ?>
                                    <option value="<?= htmlspecialchars($val) ?>" <?= $currentModel === $val ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Active API Endpoint (Computed)</label>
                            <input type="text" id="computedEndpoint" class="form-control rounded-pill px-4 py-2 bg-light text-muted" value="google.generativelanguage.v1beta.<?= htmlspecialchars($currentModel) ?>" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Master API Key</label>
                            <div class="input-group">
                                <input type="password" name="gemini_api_key" id="geminiApiKey" class="form-control rounded-start-pill px-4 py-2 border-end-0" value="<?= htmlspecialchars(defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '') ?>">
                                <button class="btn btn-outline-secondary rounded-end-pill px-3 border-start-0" type="button" id="toggleApiKey" style="border-color: #dee2e6;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Pinecone Vector DB -->
                <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4 bg-white mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-black mb-0"><i class="bi bi-database text-success me-2"></i>Pinecone Vector Database</h5>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">RAG/.env</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold">Pinecone API Key</label>
                            <div class="input-group">
                                <input type="password" name="rag_pinecone_api_key" id="pineconeApiKey" class="form-control rounded-start-pill px-4 py-2 border-end-0" value="<?= htmlspecialchars(defined('RAG_PINECONE_API_KEY') ? RAG_PINECONE_API_KEY : '') ?>">
                                <button class="btn btn-outline-secondary rounded-end-pill px-3 border-start-0" type="button" onclick="toggleVisibility('pineconeApiKey', this)" style="border-color:#dee2e6;"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Pinecone Environment</label>
                            <input type="text" name="rag_pinecone_env" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars(defined('RAG_PINECONE_ENV') ? RAG_PINECONE_ENV : '') ?>" placeholder="us-east1-aws">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Pinecone Index Name</label>
                            <input type="text" name="rag_pinecone_index" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars(defined('RAG_PINECONE_INDEX') ? RAG_PINECONE_INDEX : '') ?>" placeholder="hybrid-search">
                        </div>
                    </div>
                </div>

                <!-- LLM Providers: Mistral (chat) + OpenRouter (embeddings) -->
                <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4 bg-white mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-black mb-0"><i class="bi bi-lightning-charge text-warning me-2"></i>LLM Providers</h5>
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">RAG/.env</span>
                    </div>

                    <!-- Mistral — chat / generation -->
                    <p class="text-muted small fw-bold mb-2 text-uppercase" style="letter-spacing:.05em;">Mistral — Chat &amp; Generation</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Mistral API Key</label>
                            <div class="input-group">
                                <input type="password" name="rag_mistral_api_key" id="mistralApiKey" class="form-control rounded-start-pill px-4 py-2 border-end-0" value="<?= htmlspecialchars(defined('RAG_MISTRAL_API_KEY') ? RAG_MISTRAL_API_KEY : '') ?>">
                                <button class="btn btn-outline-secondary rounded-end-pill px-3 border-start-0" type="button" onclick="toggleVisibility('mistralApiKey', this)" style="border-color:#dee2e6;"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Mistral Model</label>
                            <input type="text" name="rag_mistral_model" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars(defined('RAG_MISTRAL_MODEL') ? RAG_MISTRAL_MODEL : 'mistral-small-2603') ?>" placeholder="mistral-small-2603">
                        </div>
                    </div>

                    <hr class="opacity-10 my-1">

                    <!-- OpenRouter — embeddings -->
                    <p class="text-muted small fw-bold mb-2 mt-3 text-uppercase" style="letter-spacing:.05em;">OpenRouter — Embedding Generation</p>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">OpenRouter API Key</label>
                            <div class="input-group">
                                <input type="password" name="rag_openrouter_api_key" id="openrouterApiKey" class="form-control rounded-start-pill px-4 py-2 border-end-0" value="<?= htmlspecialchars(defined('RAG_OPENROUTER_API_KEY') ? RAG_OPENROUTER_API_KEY : '') ?>">
                                <button class="btn btn-outline-secondary rounded-end-pill px-3 border-start-0" type="button" onclick="toggleVisibility('openrouterApiKey', this)" style="border-color:#dee2e6;"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Embedding Model</label>
                            <input type="text" name="rag_openrouter_model" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars(defined('RAG_OPENROUTER_MODEL') ? RAG_OPENROUTER_MODEL : 'qwen/qwen3-embedding-8b') ?>" placeholder="qwen/qwen3-embedding-8b">
                        </div>
                    </div>
                </div>

                <!-- LangChain Tracing & Security -->
                <div class="glass-stat-card p-4 border-0 shadow-sm rounded-4 bg-white mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-black mb-0"><i class="bi bi-shield-lock text-danger me-2"></i>LangChain Tracing &amp; Security</h5>
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">RAG/.env</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">LangChain Tracing</label>
                            <select name="rag_langchain_tracing" class="form-select rounded-pill px-4 py-2">
                                <option value="true" <?= (defined('RAG_LANGCHAIN_TRACING_V2') && RAG_LANGCHAIN_TRACING_V2 === 'true') ? 'selected' : '' ?>>Enabled</option>
                                <option value="false" <?= (!defined('RAG_LANGCHAIN_TRACING_V2') || RAG_LANGCHAIN_TRACING_V2 !== 'true') ? 'selected' : '' ?>>Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">LangChain Endpoint</label>
                            <input type="text" name="rag_langchain_endpoint" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars(defined('RAG_LANGCHAIN_ENDPOINT') ? RAG_LANGCHAIN_ENDPOINT : '') ?>" placeholder="https://api.smith.langchain.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">LangSmith API Key</label>
                            <div class="input-group">
                                <input type="password" name="rag_langsmith_api_key" id="langsmithApiKey" class="form-control rounded-start-pill px-4 py-2 border-end-0" value="<?= htmlspecialchars(defined('RAG_LANGSMITH_API_KEY') ? RAG_LANGSMITH_API_KEY : '') ?>">
                                <button class="btn btn-outline-secondary rounded-end-pill px-3 border-start-0" type="button" onclick="toggleVisibility('langsmithApiKey', this)" style="border-color:#dee2e6;"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">LangChain Project</label>
                            <input type="text" name="rag_langchain_project" class="form-control rounded-pill px-4 py-2" value="<?= htmlspecialchars(defined('RAG_LANGCHAIN_PROJECT') ? RAG_LANGCHAIN_PROJECT : '') ?>" placeholder="default">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">JWT Secret Key</label>
                            <div class="input-group">
                                <input type="password" name="rag_jwt_secret_key" id="jwtSecretKey" class="form-control rounded-start-pill px-4 py-2 border-end-0" value="<?= htmlspecialchars(defined('RAG_JWT_SECRET_KEY') ? RAG_JWT_SECRET_KEY : '') ?>">
                                <button class="btn btn-outline-secondary rounded-end-pill px-3 border-start-0" type="button" onclick="toggleVisibility('jwtSecretKey', this)" style="border-color:#dee2e6;"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar: System Health -->
            <div class="col-lg-4">
                <h5 class="fw-black mb-4">System Utilities</h5>
                <div class="d-grid gap-3 mb-5">
                    <button type="button" id="btnFlushCache" class="btn btn-outline-primary border-2 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#resumeManagerModal">
                        <i class="bi bi-arrow-clockwise me-2"></i> Flush System Cache
                    </button>
                    <button type="button" id="btnOptimizeDb" class="btn btn-outline-success border-2 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#dbOptimizerModal">
                        <i class="bi bi-database-check me-2"></i> Optimize Database
                    </button>
                    <?php $isLocked = file_exists(BASE_PATH . '/.lockdown'); ?>
                    <button type="button" id="btnToggleLockdown" class="btn <?= $isLocked ? 'btn-danger text-white' : 'btn-outline-danger' ?> border-2 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center">
                        <i class="bi <?= $isLocked ? 'bi-shield-slash-fill' : 'bi-shield-lock' ?> me-2"></i> 
                        <span id="lockdownText"><?= $isLocked ? 'Disable Lockdown' : 'System Lockdown' ?></span>
                    </button>
                </div>

                <div class="p-4 rounded-4 bg-light border border-primary border-opacity-10 text-center">
                    <div class="h1 fw-black text-primary mb-1">v2.1.0-RC</div>
                    <div class="small text-muted fw-bold">LATEST STABLE BUILD</div>
                    <hr class="opacity-10">
                    <p class="small text-muted mb-0">Last Deployment: Today, <?= date('H:i') ?></p>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal: Flush Cache / Resume Manager -->
<div class="modal fade" id="resumeManagerModal" tabindex="-1" aria-labelledby="resumeManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-black" id="resumeManagerModalLabel"><i class="bi bi-arrow-clockwise text-primary me-2"></i>Flush System Cache / Resumes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-muted small">Flush system caches by purging older uploaded resume documents. Check specific student resumes to delete, or use bulk options below.</p>
                
                <div class="mb-3 d-flex gap-2">
                    <input type="text" id="resumeSearch" class="form-control rounded-pill px-3" placeholder="Search by student name or file name...">
                </div>

                <div id="resumeListLoader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div class="table-responsive d-none" id="resumeTableContainer" style="max-height: 350px;">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input class="form-check-input" type="checkbox" id="selectAllResumes">
                                </th>
                                <th>Student</th>
                                <th>File Name</th>
                                <th>Upload Date</th>
                                <th class="text-end">Size</th>
                            </tr>
                        </thead>
                        <tbody id="resumeTableBody">
                            <!-- Dynamic Content -->
                        </tbody>
                    </table>
                </div>

                <div id="noResumesMessage" class="text-center py-5 text-muted d-none">
                    <i class="bi bi-file-earmark-check fs-2 d-block mb-2 text-opacity-50"></i>
                    No uploaded resumes found in cache.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <div>
                    <span class="small text-muted fw-bold" id="selectedResumesCount">0 selected</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="btnDeleteSelectedResumes" class="btn btn-outline-danger rounded-pill px-4" disabled>Delete Selected</button>
                    <button type="button" id="btnPurgeAllResumes" class="btn btn-danger rounded-pill px-4">Purge All Resumes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Database Health Optimizer -->
<div class="modal fade" id="dbOptimizerModal" tabindex="-1" aria-labelledby="dbOptimizerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-black" id="dbOptimizerModalLabel"><i class="bi bi-database-check text-success me-2"></i>Database Health & Optimization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="row g-3 mb-4" id="dbSummaryRow">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-4 text-center">
                            <div class="small text-muted fw-bold">TABLES</div>
                            <div class="h3 fw-black text-primary mb-0" id="dbTotalTables">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-4 text-center">
                            <div class="small text-muted fw-bold">TOTAL ROWS</div>
                            <div class="h3 fw-black text-success mb-0" id="dbTotalRows">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-4 text-center">
                            <div class="small text-muted fw-bold">DATA SIZE</div>
                            <div class="h3 fw-black text-info mb-0" id="dbTotalSize">-</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded-4 text-center">
                            <div class="small text-muted fw-bold">UNUSED (OVERHEAD)</div>
                            <div class="h3 fw-black text-warning mb-0" id="dbTotalUnused">-</div>
                        </div>
                    </div>
                </div>

                <div id="dbStatsLoader" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div class="table-responsive d-none" id="dbStatsContainer" style="max-height: 300px;">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Table Name</th>
                                <th class="text-end">Rows</th>
                                <th class="text-end">Data Size</th>
                                <th class="text-end">Index Size</th>
                                <th class="text-end">Unused Space</th>
                            </tr>
                        </thead>
                        <tbody id="dbStatsBody">
                            <!-- Dynamic Content -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" id="btnRunDbOptimization" class="btn btn-success rounded-pill px-4">
                    <i class="bi bi-magic me-2"></i>Optimize Database Tables
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Generic eye-toggle helper used by new RAG fields
function toggleVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) {
        icon.classList.toggle('bi-eye', !isPassword);
        icon.classList.toggle('bi-eye-slash', isPassword);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Relocate modals to body to prevent stacking context/backdrop issues
    const resumeModal = document.getElementById('resumeManagerModal');
    if (resumeModal) document.body.appendChild(resumeModal);
    const dbModal = document.getElementById('dbOptimizerModal');
    if (dbModal) document.body.appendChild(dbModal);

    const modelSelect = document.getElementById('geminiModelSelect');
    const computedEndpoint = document.getElementById('computedEndpoint');
    const toggleApiKey = document.getElementById('toggleApiKey');
    const apiKeyInput = document.getElementById('geminiApiKey');
    const csrfToken = '<?= $csrf_token ?>';

    // 1. Gemini Config Handlers
    if (modelSelect && computedEndpoint) {
        modelSelect.addEventListener('change', function() {
            const selectedModel = modelSelect.value;
            computedEndpoint.value = 'google.generativelanguage.v1beta.' + selectedModel;
        });
    }

    if (toggleApiKey && apiKeyInput) {
        toggleApiKey.addEventListener('click', function() {
            const type = apiKeyInput.getAttribute('type') === 'password' ? 'text' : 'password';
            apiKeyInput.setAttribute('type', type);
            
            const icon = toggleApiKey.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    }

    // 2. Global Toast Helper
    function showToast(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 320px;';
            document.body.appendChild(container);
        }
        
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} border-0 shadow-lg alert-dismissible fade show rounded-4 p-3 mb-2 d-flex align-items-center justify-content-between`;
        toast.role = 'alert';
        toast.style.background = type === 'error' ? '#fde8eb' : (type === 'success' ? '#e6f9ed' : '#eaf2fe');
        toast.style.color = type === 'error' ? '#8a1f11' : (type === 'success' ? '#135c32' : '#0b409c');
        toast.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-${type === 'error' ? 'exclamation-octagon-fill' : 'check-circle-fill'} fs-5"></i>
                <div class="small fw-bold">${message}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="position: relative; top: 0; right: 0; padding: 0.5rem;"></button>
        `;
        container.appendChild(toast);
        
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(toast);
            bsAlert.close();
        }, 5000);
    }

    // 3. Helper to format bytes
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = 2;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // 4. System Lockdown Logic
    const btnToggleLockdown = document.getElementById('btnToggleLockdown');
    if (btnToggleLockdown) {
        btnToggleLockdown.addEventListener('click', function() {
            const originalHtml = btnToggleLockdown.innerHTML;
            btnToggleLockdown.disabled = true;
            btnToggleLockdown.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';

            const formData = new FormData();
            formData.append('csrf_token', csrfToken);

            fetch('<?= BASE_URL ?>/admin/utility/toggle-lockdown', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnToggleLockdown.disabled = false;
                if (data.success) {
                    showToast(data.message, 'success');
                    if (data.lockdown_active) {
                        btnToggleLockdown.className = 'btn btn-danger text-white border-2 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center';
                        btnToggleLockdown.innerHTML = '<i class="bi bi-shield-slash-fill me-2"></i> <span id="lockdownText">Disable Lockdown</span>';
                    } else {
                        btnToggleLockdown.className = 'btn btn-outline-danger border-2 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center';
                        btnToggleLockdown.innerHTML = '<i class="bi bi-shield-lock me-2"></i> <span id="lockdownText">System Lockdown</span>';
                    }
                } else {
                    showToast(data.error || 'Failed to toggle lockdown', 'error');
                    btnToggleLockdown.innerHTML = originalHtml;
                }
            })
            .catch(err => {
                showToast('A network error occurred', 'error');
                btnToggleLockdown.disabled = false;
                btnToggleLockdown.innerHTML = originalHtml;
            });
        });
    }

    // 5. Database Health logic
    const dbOptimizerModal = document.getElementById('dbOptimizerModal');
    const dbStatsLoader = document.getElementById('dbStatsLoader');
    const dbStatsContainer = document.getElementById('dbStatsContainer');
    const dbStatsBody = document.getElementById('dbStatsBody');
    
    const dbTotalTables = document.getElementById('dbTotalTables');
    const dbTotalRows = document.getElementById('dbTotalRows');
    const dbTotalSize = document.getElementById('dbTotalSize');
    const dbTotalUnused = document.getElementById('dbTotalUnused');

    function loadDbStats() {
        dbStatsLoader.classList.remove('d-none');
        dbStatsContainer.classList.add('d-none');
        
        fetch('<?= BASE_URL ?>/admin/utility/db-stats')
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw new Error(err.error || `HTTP error ${res.status}`); });
            }
            return res.json();
        })
        .then(data => {
            dbStatsLoader.classList.add('d-none');
            dbStatsContainer.classList.remove('d-none');
            
            dbStatsBody.innerHTML = '';
            
            let totalTables = data.length;
            let totalRows = 0;
            let totalSizeBytes = 0;
            let totalUnusedBytes = 0;
            
            data.forEach(table => {
                totalRows += table.rows;
                totalSizeBytes += (table.data_size + table.index_size);
                totalUnusedBytes += table.unused_space;
                
                const tr = document.createElement('tr');
                const isUnused = table.unused_space > 0;
                tr.innerHTML = `
                    <td class="fw-bold">${table.name}</td>
                    <td class="text-end text-muted small">${table.rows.toLocaleString()}</td>
                    <td class="text-end text-muted small">${formatBytes(table.data_size)}</td>
                    <td class="text-end text-muted small">${formatBytes(table.index_size)}</td>
                    <td class="text-end fw-bold ${isUnused ? 'text-warning' : 'text-success'} small">
                        ${table.unused_space > 0 ? formatBytes(table.unused_space) : '0 Bytes'}
                    </td>
                `;
                dbStatsBody.appendChild(tr);
            });
            
            dbTotalTables.textContent = totalTables;
            dbTotalRows.textContent = totalRows.toLocaleString();
            dbTotalSize.textContent = formatBytes(totalSizeBytes);
            dbTotalUnused.textContent = formatBytes(totalUnusedBytes);
            
            if (totalUnusedBytes > 0) {
                dbTotalUnused.className = 'h3 fw-black text-warning mb-0';
            } else {
                dbTotalUnused.className = 'h3 fw-black text-success mb-0';
            }
        })
        .catch(err => {
            showToast('Failed to load database diagnostics: ' + err.message, 'error');
        });
    }

    if (dbOptimizerModal) {
        dbOptimizerModal.addEventListener('show.bs.modal', loadDbStats);
    }

    const btnRunDbOptimization = document.getElementById('btnRunDbOptimization');
    if (btnRunDbOptimization) {
        btnRunDbOptimization.addEventListener('click', function() {
            btnRunDbOptimization.disabled = true;
            btnRunDbOptimization.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Optimizing...';
            
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            
            fetch('<?= BASE_URL ?>/admin/utility/optimize-db', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.error || `HTTP error ${res.status}`); });
                }
                return res.json();
            })
            .then(data => {
                btnRunDbOptimization.disabled = false;
                btnRunDbOptimization.innerHTML = '<i class="bi bi-magic me-2"></i>Optimize Database Tables';
                
                if (data.success) {
                    showToast(data.message + ' Freed ' + formatBytes(data.freed_bytes), 'success');
                    loadDbStats();
                } else {
                    showToast(data.error || 'Optimization failed', 'error');
                }
            })
            .catch(err => {
                showToast('A network error occurred during optimization: ' + err.message, 'error');
                btnRunDbOptimization.disabled = false;
                btnRunDbOptimization.innerHTML = '<i class="bi bi-magic me-2"></i>Optimize Database Tables';
            });
        });
    }

    // 6. Resume Manager Logic
    const resumeManagerModal = document.getElementById('resumeManagerModal');
    const resumeListLoader = document.getElementById('resumeListLoader');
    const resumeTableContainer = document.getElementById('resumeTableContainer');
    const resumeTableBody = document.getElementById('resumeTableBody');
    const noResumesMessage = document.getElementById('noResumesMessage');
    
    const resumeSearch = document.getElementById('resumeSearch');
    const selectAllResumes = document.getElementById('selectAllResumes');
    const selectedResumesCount = document.getElementById('selectedResumesCount');
    const btnDeleteSelectedResumes = document.getElementById('btnDeleteSelectedResumes');
    const btnPurgeAllResumes = document.getElementById('btnPurgeAllResumes');
    
    let activeResumesList = [];

    function loadResumes() {
        resumeListLoader.classList.remove('d-none');
        resumeTableContainer.classList.add('d-none');
        noResumesMessage.classList.add('d-none');
        selectAllResumes.checked = false;
        
        fetch('<?= BASE_URL ?>/admin/utility/resumes')
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw new Error(err.error || `HTTP error ${res.status}`); });
            }
            return res.json();
        })
        .then(data => {
            resumeListLoader.classList.add('d-none');
            activeResumesList = data;
            renderResumes(data);
        })
        .catch(err => {
            showToast('Failed to load resumes from system storage: ' + err.message, 'error');
        });
    }

    function renderResumes(resumes) {
        resumeTableBody.innerHTML = '';
        if (resumes.length === 0) {
            resumeTableContainer.classList.add('d-none');
            noResumesMessage.classList.remove('d-none');
            return;
        }
        
        resumeTableContainer.classList.remove('d-none');
        noResumesMessage.classList.add('d-none');
        
        resumes.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input class="form-check-input resume-checkbox" type="checkbox" value="${r.id}">
                </td>
                <td>
                    <div class="fw-bold">${r.student_name}</div>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 220px;" title="${r.file_name}">${r.file_name}</div>
                </td>
                <td class="small text-muted">${new Date(r.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'})}</td>
                <td class="text-end small text-muted font-monospace">${r.exists ? formatBytes(r.size) : '<span class="text-danger small">Orphaned</span>'}</td>
            `;
            resumeTableBody.appendChild(tr);
        });
        
        // Wire up checkbox events
        document.querySelectorAll('.resume-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectionState);
        });
        updateSelectionState();
    }

    function updateSelectionState() {
        const checkboxes = document.querySelectorAll('.resume-checkbox');
        const checkedCount = document.querySelectorAll('.resume-checkbox:checked').length;
        
        selectedResumesCount.textContent = `${checkedCount} selected`;
        btnDeleteSelectedResumes.disabled = checkedCount === 0;
        
        if (checkboxes.length > 0) {
            selectAllResumes.checked = checkedCount === checkboxes.length;
        }
    }

    if (resumeManagerModal) {
        resumeManagerModal.addEventListener('show.bs.modal', loadResumes);
    }

    if (selectAllResumes) {
        selectAllResumes.addEventListener('change', function() {
            document.querySelectorAll('.resume-checkbox').forEach(cb => {
                cb.checked = selectAllResumes.checked;
            });
            updateSelectionState();
        });
    }

    if (resumeSearch) {
        resumeSearch.addEventListener('input', function() {
            const query = resumeSearch.value.toLowerCase().trim();
            const filtered = activeResumesList.filter(r => 
                r.student_name.toLowerCase().includes(query) || 
                r.file_name.toLowerCase().includes(query)
            );
            renderResumes(filtered);
        });
    }

    // Delete selected handler
    if (btnDeleteSelectedResumes) {
        btnDeleteSelectedResumes.addEventListener('click', function() {
            const selectedChecked = document.querySelectorAll('.resume-checkbox:checked');
            const selectedIds = Array.from(selectedChecked).map(cb => parseInt(cb.value));
            
            if (selectedIds.length === 0) return;
            
            if (!confirm(`Are you sure you want to permanently delete the ${selectedIds.length} selected resume(s) from system storage? This cannot be undone.`)) {
                return;
            }
            
            btnDeleteSelectedResumes.disabled = true;
            btnDeleteSelectedResumes.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Deleting...';
            
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('ids', JSON.stringify(selectedIds));
            
            fetch('<?= BASE_URL ?>/admin/utility/delete-resumes', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnDeleteSelectedResumes.disabled = false;
                btnDeleteSelectedResumes.textContent = 'Delete Selected';
                if (data.success) {
                    showToast(data.message, 'success');
                    loadResumes();
                } else {
                    showToast(data.error || 'Failed to delete selected resumes', 'error');
                }
            })
            .catch(err => {
                showToast('A network error occurred', 'error');
                btnDeleteSelectedResumes.disabled = false;
                btnDeleteSelectedResumes.textContent = 'Delete Selected';
            });
        });
    }

    // Purge all handler
    if (btnPurgeAllResumes) {
        btnPurgeAllResumes.addEventListener('click', function() {
            if (!confirm('⚠️ WARNING: This will permanently delete ALL student resume uploads and associated review logs from both disk and database! This action is irreversible. Proceed?')) {
                return;
            }
            
            btnPurgeAllResumes.disabled = true;
            btnPurgeAllResumes.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Purging...';
            
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('all', 'true');
            
            fetch('<?= BASE_URL ?>/admin/utility/delete-resumes', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnPurgeAllResumes.disabled = false;
                btnPurgeAllResumes.textContent = 'Purge All Resumes';
                if (data.success) {
                    showToast(data.message, 'success');
                    loadResumes();
                } else {
                    showToast(data.error || 'Failed to purge resumes', 'error');
                }
            })
            .catch(err => {
                showToast('A network error occurred', 'error');
                btnPurgeAllResumes.disabled = false;
                btnPurgeAllResumes.textContent = 'Purge All Resumes';
            });
        });
    }
});
</script>

<?php require $viewPath . 'layouts/footer.php'; ?>
