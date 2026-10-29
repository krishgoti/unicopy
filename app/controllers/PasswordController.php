<?php
/**
 * UniHelp - Password Recovery Controller
 * Handles the forgot password and reset functionality
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Require PHPMailer manually since it's in the libs folder without composer autoloader
require_once BASE_PATH . '/libs/PHPMailer/Exception.php';
require_once BASE_PATH . '/libs/PHPMailer/PHPMailer.php';
require_once BASE_PATH . '/libs/PHPMailer/SMTP.php';

class PasswordController extends Controller {

    /**
     * Show the forgot password form
     */
    public function forgot(): void {
        // Redirect to dashboard if already logged in
        if (Auth::check()) {
            $this->redirect(BASE_URL . '/dashboard');
            return;
        }
        $this->view('auth/forgot-password');
    }

    /**
     * Handle the forgot password form submission (sending the email)
     */
    public function sendResetLink(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Please enter a valid email address.');
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT id, full_name FROM users u LEFT JOIN user_profiles p ON u.id = p.user_id WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate a secure reset token
            $token = bin2hex(random_bytes(32));
            
            // Set expiration to 1 hour from now
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store the token in the database
            $updateStmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE email = ?");
            if ($updateStmt->execute([$token, $expires_at, $email])) {
                
                // Construct the reset link
                $resetLink = BASE_URL . '/reset-password?token=' . $token;
                
                // Mailer initialization
                $mail = new PHPMailer(true);
                
                try {
                    // Server settings (using config.php constants)
                    $mail->isSMTP();
                    $mail->Host       = SMTP_HOST;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = SMTP_USER;
                    $mail->Password   = SMTP_PASS;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = SMTP_PORT;

                    // Header Details
                    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                    $mail->addAddress($email, $user['full_name'] ?? 'UniHelp User');

                    // Email Content
                    $mail->isHTML(true);
                    $mail->Subject = 'UniHelp - Password Reset Request';
                    $mail->Body    = "
                        <h2>Password Reset Request</h2>
                        <p>Hello,</p>
                        <p>We received a request to reset your password for your UniHelp account. If you didn't make this request, you can safely ignore this email.</p>
                        <p>To reset your password, please click the button below:</p>
                        <p><a href='{$resetLink}' style='display:inline-block;padding:10px 20px;background-color:#0d6efd;color:#ffffff;text-decoration:none;border-radius:5px;'>Reset My Password</a></p>
                        <p>Or copy and paste this link into your browser:</p>
                        <p>{$resetLink}</p>
                        <p><em>This link will expire in 1 hour.</em></p>
                        <p>Best regards,<br>The UniHelp Team</p>
                    ";

                    $mail->send();
                    $this->flash('success', 'A password reset link has been sent to your email address.');
                } catch (Exception $e) {
                    $this->flash('error', "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }
            } else {
                $this->flash('error', 'Something went wrong while generating the reset link.');
            }
        } else {
            // For security, do not reveal whether the email exists or not
            $this->flash('success', 'If the email exists, a password reset link has been sent to it.');
        }

        $this->redirect(BASE_URL . '/forgot-password');
    }

    /**
     * Show the reset password form (verifying token first)
     */
    public function reset(): void {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            $this->flash('error', 'Invalid password reset token.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        // Verify token against database
        $stmt = $this->db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires_at > NOW()");
        $stmt->execute([$token]);
        
        if ($stmt->fetch()) {
            $this->view('auth/reset-password', ['token' => $token]);
        } else {
            $this->flash('error', 'This password reset link is invalid or has expired.');
            $this->redirect(BASE_URL . '/forgot-password');
        }
    }

    /**
     * Handle the new password submission
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/login');
            return;
        }

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid session token. Please try again.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $this->flash('error', 'Please fill in all fields.');
            $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($token));
            return;
        }

        if ($password !== $confirmPassword) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($token));
            return;
        }

        if (strlen($password) < 6) {
            $this->flash('error', 'Password must be at least 6 characters long.');
            $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($token));
            return;
        }

        // Verify token again before updating
        $stmt = $this->db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires_at > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $this->db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?");
            
            if ($updateStmt->execute([$hash, $user['id']])) {
                $this->flash('success', 'Your password has been successfully reset. You can now login.');
                $this->redirect(BASE_URL . '/login');
            } else {
                $this->flash('error', 'An error occurred while resetting your password. Please try again.');
                $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($token));
            }
        } else {
            $this->flash('error', 'This password reset link is invalid or has expired.');
            $this->redirect(BASE_URL . '/forgot-password');
        }
    }
}
