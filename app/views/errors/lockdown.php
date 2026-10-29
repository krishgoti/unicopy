<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Lockdown | UniHelp</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts (Outfit) -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-glow: radial-gradient(circle at 50% 50%, rgba(220, 53, 69, 0.15) 0%, rgba(13, 110, 253, 0.05) 50%, rgba(10, 10, 12, 1) 100%);
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.07);
            --neon-glow: 0 0 40px rgba(220, 53, 69, 0.25);
        }

        body {
            background: #0a0a0c;
            background-image: var(--bg-glow);
            color: #f8f9fa;
            font-family: 'Outfit', sans-serif;
            min-vh: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Abstract glowing particles */
        .glow-orb {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(220, 53, 69, 0.1);
            filter: blur(100px);
            z-index: 1;
            pointer-events: none;
        }
        .orb-1 { top: 10%; left: 15%; animation: float 10s ease-in-out infinite; }
        .orb-2 { bottom: 10%; right: 15%; animation: float 12s ease-in-out infinite reverse; }

        @keyframes float {
            0% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-20px) scale(1.1); }
            100% { transform: translateY(0px) scale(1); }
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4), var(--neon-glow);
            padding: 3rem;
            max-width: 580px;
            width: 90%;
            text-align: center;
            z-index: 10;
            position: relative;
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .lock-icon-wrapper {
            position: relative;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(220, 53, 69, 0.08);
            border: 1.5px solid rgba(220, 53, 69, 0.25);
            margin-bottom: 2rem;
            animation: pulseGlow 2.5s infinite ease-in-out;
        }

        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }

        .lock-icon {
            font-size: 3rem;
            color: #dc3545;
            text-shadow: 0 0 20px rgba(220, 53, 69, 0.6);
        }

        h1 {
            font-weight: 900;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff 30%, #a8b2c1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            color: #9ba4b4;
            font-size: 1.1rem;
            line-height: 1.6;
            font-weight: 300;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0) 100%);
            margin: 2rem 0;
        }

        .btn-admin {
            background: linear-gradient(135deg, #e03e52 0%, #c82333 100%);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 50px;
            padding: 0.9rem 2.2rem;
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.25);
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(220, 53, 69, 0.4);
            background: linear-gradient(135deg, #f25266 0%, #dc3545 100%);
            color: white;
        }

        .btn-admin:active {
            transform: translateY(0);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #dc3545;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.5rem;
        }

        .status-pulse {
            width: 8px;
            height: 8px;
            background-color: #dc3545;
            border-radius: 50%;
            animation: pulse-dot 1.2s infinite ease-in-out;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.8); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.8); opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <div class="glass-card">
        <div class="status-badge">
            <span class="status-pulse"></span>
            System Security Active
        </div>

        <div class="lock-icon-wrapper">
            <i class="bi bi-shield-lock-fill lock-icon"></i>
        </div>

        <h1>Platform Lockdown</h1>
        <p class="mb-4">
            UniHelp is currently undergoing system optimization or maintenance under Lockdown Mode. Access is temporarily restricted for students, alumni, and faculty.
        </p>
        
        <p class="small text-muted mb-0">
            Please contact the administration office or check back later.
        </p>

        <div class="divider"></div>

        <?php if (Auth::check()): ?>
            <div class="d-flex flex-column align-items-center gap-3">
                <span class="small text-muted">Logged in as: <strong><?= htmlspecialchars(Auth::user()['full_name']) ?></strong> (<?= ucfirst(Auth::role()) ?>)</span>
                <a href="<?= BASE_URL ?>/logout" class="btn btn-outline-light rounded-pill px-4 py-2 small">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </div>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login" class="btn btn-admin">
                <i class="bi bi-key-fill me-2"></i>Administrator Login
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
