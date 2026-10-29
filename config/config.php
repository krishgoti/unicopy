<?php
/**
 * UniHelp - Main Configuration
 * Application-wide settings
 */

// Set Timezone
date_default_timezone_set('Asia/Kolkata');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL - adjust for your environment (XAMPP: http://localhost/uni)
define('BASE_URL', 'http://localhost/unicopy');
define('BASE_PATH', dirname(__DIR__));

// File upload settings
define('UPLOAD_PATH', BASE_PATH . '/public/uploads');
define('RESUME_PATH', UPLOAD_PATH . '/resumes');
define('MAX_RESUME_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_RESUME_TYPES', [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);
define('ALLOWED_RESUME_EXTENSIONS', ['pdf', 'docx']);

require_once BASE_PATH . '/core/Env.php';
Env::load(BASE_PATH . '/.env');

// AI - Google Gemini API
define('GEMINI_API_KEY', Env::get('GEMINI_API_KEY'));
define('GEMINI_API_URL', Env::get('GEMINI_API_URL'));
define('GEMINI_MODEL', Env::get('GEMINI_MODEL', 'gemini-2.5-flash'));

// Live RAG Backend Settings (PHP ↔ RAG API bridge keys in main .env)
define('RAG_API_URL', Env::get('RAG_API_URL'));
define('RAG_STUDENT_TOKEN', Env::get('RAG_STUDENT_TOKEN'));

// RAG Backend Internal Config (stored in RAG/.env)
$_ragEnvPath = BASE_PATH . '/RAG/.env';
Env::load($_ragEnvPath, 'RAG_'); // load with prefix to avoid collisions
define('RAG_PINECONE_API_KEY',      Env::get('RAG_PINECONE_API_KEY', ''));
define('RAG_PINECONE_ENV',          Env::get('RAG_PINECONE_ENV', 'us-east1-aws'));
define('RAG_PINECONE_INDEX',        Env::get('RAG_PINECONE_INDEX', 'hybrid-search'));
define('RAG_MISTRAL_API_KEY',       Env::get('RAG_MISTRAL_API_KEY', ''));
define('RAG_MISTRAL_MODEL',         Env::get('RAG_MISTRAL_MODEL', 'mistral-small-2603'));
define('RAG_OPENROUTER_API_KEY',    Env::get('RAG_OPENROUTER_API_KEY', ''));
define('RAG_OPENROUTER_MODEL',      Env::get('RAG_OPENROUTER_MODEL', 'qwen/qwen3-embedding-8b'));
define('RAG_LANGCHAIN_TRACING_V2',  Env::get('RAG_LANGCHAIN_TRACING_V2', 'false'));
define('RAG_LANGSMITH_API_KEY',     Env::get('RAG_LANGSMITH_API_KEY', ''));
define('RAG_LANGCHAIN_ENDPOINT',    Env::get('RAG_LANGCHAIN_ENDPOINT', 'https://api.smith.langchain.com'));
define('RAG_LANGCHAIN_PROJECT',     Env::get('RAG_LANGCHAIN_PROJECT', 'default'));
define('RAG_JWT_SECRET_KEY',        Env::get('RAG_JWT_SECRET_KEY', ''));

// Image upload settings
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// Interview Experience uploads (passport-size profile image)
define('INTERVIEW_UPLOAD_PATH', UPLOAD_PATH . '/interviews');
define('MAX_INTERVIEW_IMAGE_SIZE', MAX_IMAGE_SIZE);
define('ALLOWED_INTERVIEW_IMAGE_TYPES', ['image/jpeg', 'image/png']);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 3600); // 1 hour

// Pagination
define('ITEMS_PER_PAGE', 10);

// Mock Tests
// If false, students can attempt the same mock test multiple times (not recommended for evaluations).
define('MOCK_TEST_SINGLE_ATTEMPT', true);

// SMTP Setup (Secrets moved to .env)
define('SMTP_HOST', Env::get('SMTP_HOST'));
define('SMTP_PORT', Env::get('SMTP_PORT'));
define('SMTP_USER', Env::get('SMTP_USER'));
define('SMTP_PASS', Env::get('SMTP_PASS'));
define('SMTP_FROM_EMAIL', Env::get('SMTP_FROM_EMAIL'));
define('SMTP_FROM_NAME', Env::get('SMTP_FROM_NAME'));

// Include core and database
require_once __DIR__ . '/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/ResumeParser.php';
require_once BASE_PATH . '/core/GeminiResumeAnalyzer.php';
require_once BASE_PATH . '/core/Mailer.php';
