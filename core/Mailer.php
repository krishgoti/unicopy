<?php
/**
 * UniHelp - Mailer Service
 * A centralized wrapper for PHPMailer to handle all application emails.
 */

// Include PHPMailer source files directly
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    
    /**
     * Store last error message for troubleshooting
     */
    public static ?string $lastError = null;

    /**
     * Helper to configure PHPMailer instance
     */
    private static function createMailer(): PHPMailer {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = SMTP_PORT;

        // XAMPP SSL options for local environment SSL trust
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->CharSet = 'UTF-8';
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        return $mail;
    }

    /**
     * Send a 6-digit OTP email for account verification or login.
     * 
     * @param string $email The recipient's email address
     * @param string $otp 6-digit OTP code
     * @param string $name The recipient's name
     * @return bool True if sent successfully
     */
    public static function sendOtpEmail($email, $otp, $name = 'User') {
        self::$lastError = null;
        try {
            $mail = self::createMailer();
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Your UniHelp Verification Code: ' . $otp;
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b; max-width: 550px; margin: 0 auto; border: 1px solid #e2e8f0; padding: 30px; border-radius: 12px; background-color: #ffffff;'>
                    <div style='text-align: center; margin-bottom: 25px;'>
                        <h2 style='color: #2563eb; margin: 0;'>UniHelp Authentication</h2>
                        <p style='color: #64748b; font-size: 0.95em; margin-top: 5px;'>Smart Career & Placement Guidance</p>
                    </div>
                    <hr style='border: 0; border-top: 1px solid #f1f5f9; margin-bottom: 25px;'>
                    <p style='font-size: 1.05em;'>Hello <b>" . htmlspecialchars($name) . "</b>,</p>
                    <p>Your 6-digit email verification code is:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <div style='display: inline-block; background: #eff6ff; border: 2px dashed #2563eb; color: #1d4ed8; font-size: 32px; font-weight: 800; letter-spacing: 8px; padding: 15px 35px; border-radius: 10px;'>
                            $otp
                        </div>
                    </div>
                    <p style='font-size: 0.9em; color: #475569;'>This code is valid for <b>60 seconds</b>. Enter this code on the verification page to complete your login or registration.</p>
                    <hr style='border: 0; border-top: 1px solid #f1f5f9; margin: 25px 0;'>
                    <p style='font-size: 0.8em; color: #94a3b8; text-align: center;'>If you did not request this verification code, please ignore this email.</p>
                </div>";

            $mail->AltBody = "Hello $name, your UniHelp 6-digit verification code is: $otp (valid for 60 seconds).";

            return $mail->send();
        } catch (Exception $e) {
            self::$lastError = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            error_log("PHPMailer OTP Exception: " . $e->getMessage() . " | ErrorInfo: " . $mail->ErrorInfo);
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['dev_otp'] = $otp;
            }
            return false;
        }
    }

    /**
     * Send a verification email (link-based fallback).
     */
    public static function sendVerificationEmail($email, $token, $name = 'Student') {
        try {
            $mail = self::createMailer();
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your UniHelp Account';
            
            $verificationUrl = BASE_URL . '/verify-email?token=' . $token;

            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; padding: 25px; border-radius: 10px;'>
                    <h2 style='color: #2563eb;'>Welcome to UniHelp, " . htmlspecialchars($name) . "!</h2>
                    <p>Thank you for registering. Please verify your email address to activate your account and access all campus features.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$verificationUrl' 
                           style='background-color: #2563eb; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;'>
                           Verify Email Address
                        </a>
                    </div>
                    <p style='font-size: 0.9em; color: #64748b;'>Direct Verification Link:</p>
                    <p style='font-size: 0.85em; word-break: break-all; background: #f8fafc; padding: 10px; border-radius: 5px;'><a href='$verificationUrl'>$verificationUrl</a></p>
                    <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 25px 0;'>
                    <p style='font-size: 0.8em; color: #94a3b8;'>If you did not register for a UniHelp account, you can safely ignore this email.</p>
                </div>";

            $mail->AltBody = "Welcome to UniHelp, $name! Please verify your email by opening this link: $verificationUrl";

            return $mail->send();
        } catch (Exception $e) {
            error_log("PHPMailer Exception: " . $e->getMessage() . " | ErrorInfo: " . $mail->ErrorInfo);
            return false;
        }
    }
}
