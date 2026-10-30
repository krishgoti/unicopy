<?php
/**
 * UniHelp - Authentication Controller
 * Handles login, registration, logout using 6-digit Email OTP verification
 */

class AuthController extends Controller {

    public function login(): void {
        if (Auth::check()) {
            $this->redirect(BASE_URL . '/dashboard/' . Auth::role());
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid session token. Please try again.');
                $this->view('auth/login');
                return;
            }

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';

            if (empty($email) || empty($password) || empty($role)) {
                $this->flash('error', 'Please enter email, password, and select a role.');
                $this->view('auth/login', ['email' => $email, 'role' => $role]);
                return;
            }

            $stmt = $this->db->prepare("SELECT u.*, up.full_name FROM users u 
                LEFT JOIN user_profiles up ON u.id = up.user_id 
                WHERE u.email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password']) || $user['role'] !== $role) {
                $this->flash('error', 'Invalid email, password, or selected role.');
                $this->view('auth/login', ['email' => $email, 'role' => $role]);
                return;
            }

            if ($user['status'] === 'rejected') {
                $this->flash('error', 'Your registration has been rejected. Please contact admin.');
                $this->view('auth/login', ['email' => $email]);
                return;
            }

            if ($user['role'] === 'alumni' && $user['status'] === 'pending') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'full_name' => $user['full_name'] ?? 'Alumni',
                    'status' => $user['status']
                ];
                $this->redirect(BASE_URL . '/dashboard/alumni?pending=1');
                return;
            }

            if (!in_array($user['status'], ['active', 'approved'])) {
                $this->flash('error', 'Your account is not yet activated.');
                $this->view('auth/login', ['email' => $email]);
                return;
            }

            // MANDATORY 6-DIGIT EMAIL OTP VERIFICATION FOR LOGIN (ALL USERS EXCEPT ADMIN)
            if ($user['role'] !== 'admin') {
                $otp = sprintf('%06d', random_int(0, 999999));
                $otpLifetime = in_array($user['role'], ['student', 'faculty', 'alumni']) ? 60 : 900;
                $otpExpires = date('Y-m-d H:i:s', time() + $otpLifetime);
                
                $stmt = $this->db->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
                $stmt->execute([$otp, $otpExpires, $user['id']]);

                $_SESSION['pending_otp_email'] = $email;
                if (strtolower((string)Env::get('APP_ENV')) !== 'production') {
                    $_SESSION['dev_otp'] = $otp;
                }
                $_SESSION['otp_created_at'] = time();
                $_SESSION['otp_lifetime'] = $otpLifetime;

                $mailSent = false;
                try {
                    $mailSent = Mailer::sendOtpEmail($email, $otp, $user['full_name'] ?? 'User');
                } catch (\Throwable $mailErr) {
                    error_log("Login OTP Mail Error: " . $mailErr->getMessage());
                }

                if ($mailSent) {
                    $this->flash('warning', 'Password verified! A 6-digit OTP code has been sent to ' . htmlspecialchars($email) . '. (Valid for ' . $otpLifetime . ' seconds)');
                } else {
                    $msg = 'Unable to send OTP email. Please check your SMTP settings.';
                    if (strtolower((string)Env::get('APP_ENV')) !== 'production') {
                        $msg .= ' (For testing, your OTP code is: ' . $otp . ')';
                    }
                    $this->flash('warning', $msg);
                }

                $this->redirect(BASE_URL . '/verify-otp');
                return;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name'] ?? 'User',
                'status' => $user['status'],
                'email_verified_at' => $user['email_verified_at']
            ];

            $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/dashboard/' . $user['role'];
            unset($_SESSION['redirect_after_login']);
            $this->redirect($redirect);
            return;
        }

        $this->view('auth/login');
    }

    public function register(): void {
        if (Auth::check()) {
            $this->redirect(BASE_URL . '/dashboard/' . Auth::role());
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid request. Please try again.');
                $this->view('auth/register', $_POST);
                return;
            }

            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $fullName = trim($_POST['full_name'] ?? '');
            $role = $_POST['role'] ?? 'student';
            $phone = trim($_POST['phone'] ?? '');
            $department = trim($_POST['department'] ?? '');
            $graduationYear = $_POST['graduation_year'] ?? null;
            $company = trim($_POST['company'] ?? '');
            $designation = trim($_POST['designation'] ?? '');

            $errors = [];

            if (empty($email)) $errors[] = 'Email is required.';
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
            if (empty($password)) $errors[] = 'Password is required.';
            elseif (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
            if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';
            if (empty($fullName)) $errors[] = 'Full name is required.';
            if (!in_array($role, ['student', 'faculty', 'alumni'])) $errors[] = 'Invalid role selected.';

            if (!empty($errors)) {
                $this->flash('error', implode(' ', $errors));
                $this->view('auth/register', $_POST);
                return;
            }

            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $this->flash('error', 'Email already registered.');
                $this->view('auth/register', $_POST);
                return;
            }

            try {
                $this->db->beginTransaction();
                
                $status = ($role === 'alumni') ? 'pending' : 'active';
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Generate 6-digit OTP
                $otp = sprintf('%06d', random_int(0, 999999));
                $otpLifetime = in_array($role, ['student', 'faculty', 'alumni']) ? 60 : 900;
                $otpExpires = date('Y-m-d H:i:s', time() + $otpLifetime); // 60 seconds for student/faculty/alumni
                $verificationToken = bin2hex(random_bytes(16));
                
                $stmt = $this->db->prepare("INSERT INTO users (email, password, role, status, verification_token, otp_code, otp_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$email, $hashedPassword, $role, $status, $verificationToken, $otp, $otpExpires]);
                $userId = $this->db->lastInsertId();

                $stmt = $this->db->prepare("INSERT INTO user_profiles (user_id, full_name, phone, department, graduation_year, company, designation) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $fullName, $phone ?: null, $department ?: null, $graduationYear ?: null, $company ?: null, $designation ?: null]);

                if ($role === 'student') {
                    $stmt = $this->db->prepare("INSERT INTO student_profiles (user_id) VALUES (?)");
                    $stmt->execute([$userId]);
                }

                $this->db->commit();

                $_SESSION['pending_otp_email'] = $email;
                if (strtolower((string)Env::get('APP_ENV')) !== 'production') {
                    $_SESSION['dev_otp'] = $otp;
                }
                $_SESSION['otp_created_at'] = time();
                $_SESSION['otp_lifetime'] = $otpLifetime;

                $mailSent = false;
                try {
                    $mailSent = Mailer::sendOtpEmail($email, $otp, $fullName);
                } catch (\Throwable $mailError) {
                    error_log("OTP Mail Error: " . $mailError->getMessage());
                }

                if ($role === 'alumni') {
                    $this->flash('success', 'Registration successful! Your account is pending admin approval.');
                    $this->redirect(BASE_URL . '/login');
                    return;
                }

                if ($mailSent) {
                    $this->flash('success', 'Registration successful! A 6-digit OTP code has been sent to ' . htmlspecialchars($email) . '. (Valid for ' . $otpLifetime . ' seconds)');
                } else {
                    $msg = 'Registration successful! Unable to send OTP email. Please check your SMTP settings.';
                    if (strtolower((string)Env::get('APP_ENV')) !== 'production') {
                        $msg .= ' (For testing, your OTP is: ' . $otp . ')';
                    }
                    $this->flash('warning', $msg);
                }

                $this->redirect(BASE_URL . '/verify-otp');
                return;
            } catch (\Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                error_log("Registration Exception: " . $e->getMessage());
                $this->flash('error', 'Registration failed: ' . $e->getMessage());
                $this->view('auth/register', $_POST);
            }
            return;
        }

        $this->view('auth/register');
    }

    public function verifyOtp(): void {
        $email = trim($_REQUEST['email'] ?? ($_SESSION['pending_otp_email'] ?? ''));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid request. Please try again.');
                $this->view('auth/verify-otp', ['email' => $email]);
                return;
            }

            $enteredOtp = trim($_POST['otp_code'] ?? '');

            if (empty($email) || empty($enteredOtp)) {
                $this->flash('error', 'Please enter your registered email and 6-digit OTP code.');
                $this->view('auth/verify-otp', ['email' => $email]);
                return;
            }

            $stmt = $this->db->prepare("SELECT u.*, up.full_name FROM users u 
                LEFT JOIN user_profiles up ON u.id = up.user_id 
                WHERE u.email = ? AND u.otp_code = ? AND u.otp_expires_at >= NOW()");
            $stmt->execute([$email, $enteredOtp]);
            $user = $stmt->fetch();

            if (!$user) {
                // Check if OTP matched but expired
                $expStmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND otp_code = ?");
                $expStmt->execute([$email, $enteredOtp]);
                if ($expStmt->fetch()) {
                    $this->flash('error', 'Your 60-second OTP code has expired. Please click "Resend OTP Code" below.');
                } else {
                    $this->flash('error', 'Invalid 6-digit OTP code. Please check your code or click Resend.');
                }
                $this->view('auth/verify-otp', ['email' => $email]);
                return;
            }

            // Verify email and clear OTP
            $stmt = $this->db->prepare("UPDATE users SET email_verified_at = NOW(), status = 'active', otp_code = NULL, otp_expires_at = NULL, verification_token = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Auto Log In
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name'] ?? 'User',
                'status' => 'active',
                'email_verified_at' => date('Y-m-d H:i:s')
            ];

            unset($_SESSION['pending_otp_email'], $_SESSION['dev_otp'], $_SESSION['otp_created_at'], $_SESSION['otp_lifetime'], $_SESSION['login_time']);
            $this->flash('success', 'Email verified successfully! Welcome to UniHelp.');
            $this->redirect(BASE_URL . '/dashboard/' . $user['role']);
            return;
        }

        $this->view('auth/verify-otp', ['email' => $email]);
    }

    public function sendLoginOtp(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->flash('error', 'Invalid request token.');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            $email = trim($_POST['email'] ?? '');
            if (empty($email)) {
                $this->flash('error', 'Please enter your registered email address.');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            $stmt = $this->db->prepare("SELECT u.id, u.email, u.role, up.full_name FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $this->flash('error', 'No registered account found with that email address.');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            $otp = sprintf('%06d', random_int(0, 999999));
            $otpLifetime = in_array($user['role'], ['student', 'faculty', 'alumni']) ? 60 : 900;
            $otpExpires = date('Y-m-d H:i:s', time() + $otpLifetime);

            $stmt = $this->db->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
            $stmt->execute([$otp, $otpExpires, $user['id']]);

            $_SESSION['pending_otp_email'] = $email;
            if (strtolower((string)Env::get('APP_ENV')) !== 'production') {
                $_SESSION['dev_otp'] = $otp;
            }
            $_SESSION['otp_created_at'] = time();
            $_SESSION['otp_lifetime'] = $otpLifetime;

            $mailSent = false;
            try {
                $mailSent = Mailer::sendOtpEmail($email, $otp, $user['full_name'] ?? 'User');
            } catch (\Throwable $mailErr) {
                error_log("sendLoginOtp Error: " . $mailErr->getMessage());
            }

            if ($mailSent) {
                $this->flash('success', 'A 6-digit OTP code has been sent to ' . htmlspecialchars($email) . '. (Valid for ' . $otpLifetime . ' seconds)');
            } else {
                $msg = 'Unable to send OTP email. Please check your SMTP settings.';
                if (strtolower((string)Env::get('APP_ENV')) !== 'production') {
                    $msg .= ' (For testing, your OTP is: ' . $otp . ')';
                }
                $this->flash('warning', $msg);
            }

            $this->redirect(BASE_URL . '/verify-otp');
            return;
        }

        $this->redirect(BASE_URL . '/login');
    }

    public function resendOtp(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? ($_SESSION['pending_otp_email'] ?? ''));
            if (empty($email)) {
                $this->flash('error', 'Unable to find email to resend OTP. Please enter your email.');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            // Enforce 60-second cooldown between resends
            if (isset($_SESSION['otp_created_at'])) {
                $elapsed = time() - $_SESSION['otp_created_at'];
                if ($elapsed < 60) {
                    $wait = 60 - $elapsed;
                    $this->flash('warning', "Please wait {$wait} seconds before requesting a new OTP code.");
                    $this->redirect(BASE_URL . '/verify-otp');
                    return;
                }
            }

            $stmt = $this->db->prepare("SELECT u.id, u.email, u.role, up.full_name FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $this->flash('error', 'No registered account found with that email.');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            $otp = sprintf('%06d', random_int(0, 999999));
            $otpLifetime = in_array($user['role'], ['student', 'faculty', 'alumni']) ? 60 : 900;
            $otpExpires = date('Y-m-d H:i:s', time() + $otpLifetime);

            $stmt = $this->db->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
            $stmt->execute([$otp, $otpExpires, $user['id']]);

            $_SESSION['pending_otp_email'] = $email;
            if (strtolower((string)Env::get('APP_ENV')) !== 'production') {
                $_SESSION['dev_otp'] = $otp;
            }
            $_SESSION['otp_created_at'] = time();
            $_SESSION['otp_lifetime'] = $otpLifetime;

            $mailSent = false;
            try {
                $mailSent = Mailer::sendOtpEmail($email, $otp, $user['full_name'] ?? 'User');
            } catch (\Throwable $mailErr) {
                error_log("resendOtp Error: " . $mailErr->getMessage());
            }

            if ($mailSent) {
                $this->flash('success', 'A fresh 6-digit OTP code has been sent to ' . htmlspecialchars($email) . '. (Valid for ' . $otpLifetime . ' seconds)');
            } else {
                $msg = 'Unable to send OTP email. Please check your SMTP settings.';
                if (strtolower((string)Env::get('APP_ENV')) !== 'production') {
                    $msg .= ' (For testing, your OTP is: ' . $otp . ')';
                }
                $this->flash('warning', $msg);
            }

            $this->redirect(BASE_URL . '/verify-otp');
            return;
        }

        $this->redirect(BASE_URL . '/login');
    }

    public function logout(): void {
        $expired = isset($_GET['expired']);
        session_destroy();
        session_start();
        if ($expired) {
            $this->flash('warning', 'Your 60-second session has expired. Please log in again.');
            $this->redirect(BASE_URL . '/login');
            return;
        }
        $this->flash('success', 'You have been logged out successfully.');
        $this->redirect(BASE_URL . '/');
    }

    public function verify(): void {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $this->flash('error', 'Invalid verification link.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $stmt = $this->db->prepare("SELECT id FROM users WHERE verification_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->flash('error', 'Invalid or expired verification token.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $stmt = $this->db->prepare("UPDATE users SET email_verified_at = NOW(), verification_token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Sync session if user is logged in
        if (Auth::check() && Auth::id() == $user['id']) {
            $_SESSION['user']['email_verified_at'] = date('Y-m-d H:i:s');
        }

        $this->flash('success', 'Email verified successfully! You can now login.');
        $this->redirect(BASE_URL . '/login');
    }

    public function resendVerification(): void {
        $this->resendOtp();
    }
}
