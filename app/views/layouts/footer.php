    </main>


    
    <?php if (($pageTitle ?? '') === 'Home'): ?>
    <footer class="footer mt-auto py-5">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4 col-md-6">
                    <h5 class="mb-3"><i class="bi bi-mortarboard-fill me-2"></i>UniHelp</h5>
                    <p class="opacity-75">Empowering students with smart career guidance, placement tracking, and mentorship opportunities. Your bridge to a successful future.</p>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>" class="text-decoration-none opacity-90 hover-opacity-100 mb-2 d-block">Home</a></li>
                        <li><a href="<?= BASE_URL ?>/opportunities" class="text-decoration-none opacity-90 hover-opacity-100 mb-2 d-block">Jobs</a></li>
                        <li><a href="<?= BASE_URL ?>/mentorship" class="text-decoration-none opacity-90 hover-opacity-100 mb-2 d-block">Mentorship</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Resources</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>/career-roadmap" class="text-decoration-none opacity-90 hover-opacity-100 mb-2 d-block">Roadmaps</a></li>
                        <li><a href="<?= BASE_URL ?>/interview-experiences" class="text-decoration-none opacity-90 hover-opacity-100 mb-2 d-block">Interviews</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="mb-3">Contact Me</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i><a href="https://maps.app.goo.gl/YzdiZMpaTZmswEbH8" target="_blank" class="text-decoration-none opacity-90 hover-opacity-100">SSASIT</a></li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i><a href="mailto:krishgoti19@gmail.com" class="text-decoration-none opacity-90 hover-opacity-100">krishgoti19@gmail.com</a></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary opacity-25 my-4">
            <div class="text-center opacity-50 small">
                &copy; <?= date('Y') ?> UniHelp by Krish Goti. All rights reserved.
            </div>
        </div>
    </footer>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
    
    <!-- RAG Chatbot Integration -->
    <?php if (Auth::check() && Auth::isVerified()): ?>
        <link href="<?= BASE_URL ?>/assets/css/chatbot.css?v=<?= time() ?>" rel="stylesheet">
        <?php require BASE_PATH . '/app/views/layouts/chatbot-ui.php'; ?>
        <script src="<?= BASE_URL ?>/assets/js/chatbot.js?v=<?= time() ?>"></script>
    <?php endif; ?>
    <script>
        // Initialize Lucide Icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>
