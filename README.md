# 🎓 UniHelp — Smart Career & Campus Placement Guidance Platform

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Python Version](https://img.shields.io/badge/Python-3.10%2B-3776AB?style=flat-square&logo=python&logoColor=white)](https://www.python.org/)
[![FastAPI](https://img.shields.io/badge/FastAPI-0.100%2B-009688?style=flat-square&logo=fastapi&logoColor=white)](https://fastapi.tiangolo.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

**UniHelp** is a unified, full-stack campus career acceleration and placement ecosystem designed for higher-education institutions. It bridges students, faculty, alumni, and administrators through automated AI resume diagnostics, skill-linked evaluation tests, coding contests, mentorship networks, and a hybrid-search Retrieval-Augmented Generation (RAG) campus assistant.

---

## 📌 Table of Contents

- [Overview](#-overview)
- [Architecture & Tech Stack](#-architecture--tech-stack)
- [Key Modules & Features](#-key-modules--features)
  - [1. Student Career Hub](#1-student-career-hub)
  - [2. Faculty & Mentor Portal](#2-faculty--mentor-portal)
  - [3. Alumni Network](#3-alumni-network)
  - [4. Admin Command Center](#4-admin-command-center)
  - [5. Smart AI & RAG Chatbot](#5-smart-ai--rag-chatbot)
- [Security & Privacy Guardrails](#-security--privacy-guardrails)
- [Getting Started](#-getting-started)
  - [Method 1: Docker Compose (Recommended)](#method-1-docker-compose-recommended)
  - [Method 2: Manual Local Setup (XAMPP + Python venv)](#method-2-manual-local-setup-xampp--python-venv)
- [Environment Configuration](#-environment-configuration)
- [Directory Structure](#-directory-structure)
- [Application Routes](#-application-routes)
- [Contributing & License](#-contributing--license)

---

## 🌟 Overview

Preparing for campus placements requires coordinating resumes, technical interview preparation, alumni advice, verified job notifications, and institution policies. **UniHelp** replaces fragmented systems with a centralized web platform featuring:

1. **AI-Powered Diagnostics**: Instant ATS scoring, skills gap identification, and structural suggestions powered by Google Gemini.
2. **Context-Aware Campus Assistant**: RAG chatbot answering university guidelines, placement policies, and career paths using Pinecone hybrid search.
3. **Structured Skill Assessment**: Faculty-administered timed mock tests with auto-evaluation and student skill point tracking.
4. **Alumni Insights & Mentorship**: Real interview experience sharing and one-on-one booking with campus graduates.
5. **Two-Factor Authentication**: Email OTP login with automated 60-second cooldown protection.

---

## 🏗 Architecture & Tech Stack

```
                              ┌──────────────────────────────────────────────┐
                              │                 Web Browser                  │
                              │           (Bootstrap 5, Chart.js)            │
                              └──────────────┬──────────────────┬────────────┘
                                             │ HTTP (Port 80)   │ RAG Chat
                                             ▼                  ▼
┌──────────────────────────────────────────────┐    ┌──────────────────────────────────────────────┐
│            PHP / Apache (Port 80)            │    │          FastAPI RAG Backend (Port 8000)     │
│  - Custom MVC Architecture                   │    │  - NeMo Guardrails (Content Safety)          │
│  - Session & Role-Based Access Control       │    │  - Pinecone Hybrid Retriever (Dense + Sparse)│
│  - PHPMailer (SMTP OTP Authentication)       │    │  - Mistral AI & OpenRouter Models            │
│  - Google Gemini 2.5 API (Resume Analyzer)   │    │  - JWT Bearer Authentication                 │
└──────────────────────┬───────────────────────┘    └──────────────────────┬───────────────────────┘
                       │                                                   │
                       └───────────────────────┬───────────────────────────┘
                                               ▼
                               ┌──────────────────────────────┐
                               │       MySQL 8.0 Engine       │
                               │  - User profiles & roles     │
                               │  - Mock tests & questions    │
                               │  - Opportunities & reviews   │
                               │  - RAG document registry     │
                               └──────────────────────────────┘
```

- **Frontend**: Responsive HTML5, Vanilla CSS3, Bootstrap 5.3, Lucide Icons, Chart.js.
- **Backend**: Native PHP 8.2 with Object-Oriented MVC pattern and PDO database abstraction.
- **AI & RAG Engine**: Python 3.10+, FastAPI, LangChain, Pinecone Vector DB (Dense + BM25 Hybrid Retrieval), NeMo Guardrails, Google Gemini API, Mistral AI.
- **Database**: MySQL 8.0 with foreign keys, transactional integrity, and prepared statements.
- **Deployment**: Docker & Docker Compose with multi-stage builds.

---

## 🚀 Key Modules & Features

### 1. Student Career Hub
- **AI Resume Analyzer**: Upload PDF resumes to receive instant ATS compatibility scores, identified skills, missing keywords, and actionable suggestions.
- **Curated Career Roadmaps**: Tailored learning paths for Software Engineering, Government exams (GATE/UPSC), MBA, Core Engineering, Startups, and Research.
- **Skill Tracker & Analytics**: Dynamic radar and bar charts tracking technical, soft, and aptitude skill progress.
- **Timed Mock Tests**: Attempt skill-based evaluations created by faculty with immediate scoring (+10 per correct answer) and attempt history.
- **Coding Contests**: Participate in competitive coding rounds with real-time leaderboards.
- **Job & Internship Portal**: Search active openings by role, company, or location, and track applications.
- **Alumni Interview Experiences**: Read comprehensive accounts of company hiring rounds, questions asked, and candidate tips.

### 2. Faculty & Mentor Portal
- **Opportunity Posting**: Publish placement and internship listings with deadline tracking and applicant review.
- **Mock Test Creator**: Construct 10-question timed multiple-choice assessments linked to specific university skills.
- **Manual Resume Reviews**: Provide personalized line-by-line feedback and ratings to compliment automated AI diagnostics.
- **Mentorship Scheduling**: Accept or manage student guidance sessions with meeting details.

### 3. Alumni Network
- **Interview Experience Sharing**: Submit structured interview guides detailing rounds (Online Assessment, Technical, HR) and difficulty levels.
- **Mentorship Availability**: Offer one-on-one placement advice and resume reviews to juniors.
- **Account Verification**: Dedicated approval workflow through the institutional administrator.

### 4. Admin Command Center
- **User & Role Management**: Manage and approve students, faculty, and alumni accounts.
- **System Health & DB Optimization**: Inspect table row counts, index sizes, and run live overhead table optimization.
- **Resume Cache Manager**: Search, filter, and selectively purge stale uploaded documents to optimize storage.
- **Emergency System Lockdown**: One-click platform lockdown preventing non-admin access during maintenance.
- **Global Settings**: Configure platform name, SMTP gateway, active Gemini models, and RAG vector store parameters.

### 5. Smart AI & RAG Chatbot
- **Hybrid Retrieval**: Combines dense semantic embeddings with BM25 sparse keyword search over institutional policy documents.
- **NeMo Guardrails**: Validates input/output boundaries to prevent prompt injections, off-topic discussions, or hallucinations.
- **Student Context Injection**: Tailors guidance using the active student's semester, CGPA, target career goal, and prioritized tasks.

---

## 🔒 Security & Privacy Guardrails

UniHelp implements defense-in-depth protections across all layers:

- **Authentication**: Bcrypt password hashing (`PASSWORD_BCRYPT`), session management, and optional email OTP 2-Factor Authentication with a 60-second expiry timer.
- **Access Control (RBAC)**: Strict server-side permission checks enforcing role boundaries (`student`, `faculty`, `alumni`, `admin`).
- **CSRF Protection**: Cryptographically secure anti-CSRF token verification on all POST/PUT/DELETE forms.
- **SQL Injection Defense**: 100% prepared PDO statements with bound parameters across all database queries.
- **Server File Protection**: Hardened `.htaccess` blocking direct HTTP access to `/database/`, `/RAG/`, `/docker/`, `/libs/`, `.env`, `.sql`, `.ini`, and log files (`403 Forbidden`).
- **Data Privacy**: No real user credentials, private keys, or plain-text passwords are stored in version control; environment variables strictly isolate production secrets.

---

## 💻 Getting Started

### Method 1: Docker Compose (Recommended)

Run the entire application (MySQL, Web UI, and AI RAG Backend) with a single command without installing PHP or Python locally:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/krishgoti/unicopy.git
   cd unicopy
   ```

2. **Configure environment files:**
   ```bash
   cp .env.example .env
   cp RAG/.env.example RAG/.env
   ```
   *Edit `.env` and `RAG/.env` to provide your Google Gemini, SMTP, and Pinecone credentials.*

3. **Start the containers:**
   ```bash
   docker compose up -d
   ```

4. **Access the application:**
   - Web Platform: [http://localhost](http://localhost)
   - RAG AI Documentation: [http://localhost:8000/docs](http://localhost:8000/docs)
   - Health Status: [http://localhost:8000/health](http://localhost:8000/health)

---

### Method 2: Manual Local Setup (XAMPP + Python venv)

#### Prerequisites
- **Web & Database**: XAMPP / WampServer (PHP 8.2+, Apache, MySQL 8.0+)
- **AI Service**: Python 3.10+ with `pip`
- **Apache Modules**: Enable `mod_rewrite` in `httpd.conf`

#### Step 1: Web Application Setup
1. Clone the repository into your web root:
   ```bash
   # In Windows XAMPP:
   cd c:\xampp\htdocs\
   git clone https://github.com/krishgoti/unicopy.git
   ```

2. Import the database schema:
   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Create a database named `unihelp`
   - Import `database/unihelp_master.sql` (or `database/unihelp.sql`)

3. Create your root configuration:
   ```bash
   cp .env.example .env
   ```
   *Fill in your database name, user, password, and SMTP credentials.*

4. Verify uploads directory exists:
   ```bash
   mkdir public\uploads\resumes
   ```

5. Access the web portal:
   - Navigate to [http://localhost/unicopy](http://localhost/unicopy)

#### Step 2: AI & RAG Service Setup
1. Navigate to the `RAG` directory and set up a Python virtual environment:
   ```bash
   cd RAG
   python -m venv venv
   # On Windows:
   venv\Scripts\activate
   # On Linux/macOS:
   source venv/bin/activate
   ```

2. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```

3. Configure RAG environment:
   ```bash
   cp .env.example .env
   ```
   *Supply your `PINECONE_API_KEY`, `MISTRAL_API_KEY`, and `JWT_SECRET_KEY`.*

4. Launch the AI server:
   ```bash
   uvicorn main:app --host 127.0.0.1 --port 8000 --reload
   ```

---

## ⚙️ Environment Configuration

### Root Environment Variables (`.env`)

| Variable | Description | Default |
| :--- | :--- | :--- |
| `APP_ENV` | Application environment (`development` or `production`) | `development` |
| `DB_HOST` | MySQL hostname (`mysql` for Docker, `127.0.0.1` for local) | `mysql` |
| `DB_NAME` | Database schema name | `unihelp` |
| `DB_USER` | MySQL database user | `root` |
| `DB_PASS` | MySQL database password | *(empty)* |
| `GEMINI_API_KEY` | Google Gemini API Key for Resume Analysis | *(your key)* |
| `RAG_API_URL` | RAG service chat URL accessed by clients | `http://localhost:8000/api/chat` |
| `SMTP_HOST` | SMTP server address | `smtp.gmail.com` |
| `SMTP_PORT` | SMTP port | `587` |
| `SMTP_USER` | SMTP authentication email | *(your email)* |
| `SMTP_PASS` | SMTP App password | *(your app password)* |
| `SMTP_FROM_EMAIL`| Sender email address | *(your email)* |

### RAG Service Environment (`RAG/.env`)

| Variable | Description |
| :--- | :--- |
| `PINECONE_API_KEY` | Pinecone vector database API key |
| `PINECONE_INDEX` | Pinecone index name (e.g. `hybrid-search`) |
| `MISTRAL_API_KEY` | Mistral AI API key for chat generation |
| `OPENROUTER_API_KEY`| OpenRouter API key for fallback LLMs/embeddings |
| `JWT_SECRET_KEY` | Cryptographic secret for signing RAG bearer tokens |
| `CORS_ORIGINS` | Permitted client origins separated by commas |

---

## 📁 Directory Structure

```
unicopy/
├── app/
│   ├── controllers/         # Application controllers (Auth, Resume, MockTest, etc.)
│   └── views/               # PHP presentation templates and layouts
│       ├── admin/           # Platform analytics and system management
│       ├── auth/            # Login, registration, OTP verification, password reset
│       ├── dashboard/       # Role-specific dashboards (student, faculty, alumni, admin)
│       ├── layouts/         # Header, footer, and chatbot UI components
│       ├── mocktests/       # Faculty creation and student test attempt interfaces
│       └── resume/          # AI resume feedback and manual review queues
├── assets/
│   ├── css/                 # Modern styling and custom theme sheets
│   └── js/                  # Interactive chatbot client and dynamic UI helpers
├── config/                  # Database connection and platform configuration
├── core/                    # Foundation classes (Auth, Controller, Model, Mailer, AI Analyzer)
├── database/                # Relational schemas, seed data, and schema migration scripts
├── docker/                  # Custom container configuration (php.ini overrides)
├── public/uploads/          # Storage destination for uploaded student resumes
├── RAG/                     # Standalone Python FastAPI RAG Microservice
│   ├── api/routes/          # FastAPI route handlers (chat, documents, upload, delete)
│   ├── config/              # NeMo Guardrails definitions and prompts
│   ├── middleware/          # JWT auth, rate limiter, security headers, input validator
│   ├── services/            # Pinecone hybrid retriever, embeddings, and chat service
│   ├── Dockerfile           # Microservice container definition
│   └── requirements.txt     # Python dependencies
├── .env.example             # Template for main application configuration
├── .htaccess                # Apache routing, security headers, and directory protection
├── docker-compose.yml       # Production/development multi-container specification
├── Dockerfile.php           # PHP 8.2 Apache container definition
└── index.php                # Master front controller
```

---

## 🗺️ Application Routes

| Endpoint | Method | Role Access | Description |
| :--- | :--- | :--- | :--- |
| `/` | `GET` | Public | Platform landing page |
| `/login` | `GET`, `POST` | Public | Account authentication with password or OTP |
| `/register` | `GET`, `POST` | Public | Account registration (Student, Faculty, Alumni) |
| `/verify-otp` | `GET`, `POST` | Public | 60-second OTP verification gateway |
| `/dashboard` | `GET` | Authenticated | Redirects to role-specific dashboard |
| `/resume` | `GET`, `POST` | Student | Upload resume & receive instant AI evaluation |
| `/career-roadmap` | `GET` | Authenticated | Interactive career progression roadmap |
| `/mock-tests` | `GET` | Student | Browse and take skill evaluation tests |
| `/mock-tests/create` | `GET`, `POST` | Faculty | Build new timed mock tests |
| `/contests` | `GET` | Authenticated | Coding contests and leaderboard standings |
| `/opportunities` | `GET` | Authenticated | Verified placement & internship openings |
| `/interview-experiences`| `GET`, `POST` | Authenticated | Browse and submit company interview reviews |
| `/mentorship` | `GET`, `POST` | Authenticated | Schedule and manage student mentorship |
| `/admin/settings` | `GET`, `POST` | Admin | Configure API keys, SMTP, and vector parameters |
| `/admin/utility/optimize-db` | `POST` | Admin | Live MySQL table defragmentation |

---

## 👥 Admin Setup

Administrator accounts can be created by registering an account and assigning the `'admin'` role in the database, or by running `database/seed_admin.sql`. Ensure you set a secure password upon initial setup.

---

## 📄 License

This project is licensed under the [MIT License](LICENSE). Built for academic, institutional, and educational use.
