# UniHelp – Smart Career & Placement Guidance Platform

A complete, production-ready, full-stack responsive web application for career guidance and placement preparation. Final Year Engineering Project.

## Tech Stack

- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5
- **Backend:** PHP (MVC structure)
- **AI & RAG Backend:** FastAPI, Python 3.9+, LangChain, LangGraph
- **Vector Database:** Pinecone (Hybrid Search)
- **LLM Providers:** MistralAI, OpenRouter (HuggingFace/Google GenAI as fallback)
- **Database:** MySQL
- **Charts:** Chart.js

## Features

### User Roles

- **Student:** Career roadmap, skill tracker, resume upload, mentorship requests, opportunities, interview experiences
- **Faculty:** Post opportunities, review resumes, mentorship, analytics
- **Alumni:** Share interview experiences, mentorship (requires admin approval)
- **Admin:** User management, alumni approval, analytics, platform control

### Key Features

- Role-based authentication with secure sessions
- Career roadmap based on career goal (Software, Government, MBA, Core, etc.)
- Skill tracker with progress bars and readiness percentage
- Resume upload (PDF only) with faculty feedback
- Mentorship system (student-faculty/alumni)
- Placement & internship opportunities hub
- Interview experience wall
- **Chart.js analytics dashboards**
- **Smart AI Chatbot:** Context-aware career guidance powered by RAG
- **AI Resume Feedback:** Automated analysis and improvement suggestions
- **Semantic Search:** Intelligent document retrieval from the campus knowledge base
- **Advanced Guardrails:** NeMo Guardrails for safety and hallucination checks

## Installation

### Prerequisites

- XAMPP (Apache + PHP + MySQL) or similar
- PHP 7.4+ with PDO, mbstring, fileinfo extensions

### Setup

1. **Clone/Copy** the project to `C:\xampp\htdocs\uni`

2. **Create Database:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import `database/unihelp.sql` or run it via MySQL command line

3. **Configure Database** (if needed):
   - Edit `config/database.php` with your MySQL credentials
   - Default: host=localhost, db=unihelp, user=root, pass=''

4. **Apache mod_rewrite:**
   - Ensure `mod_rewrite` is enabled in Apache
   - In XAMPP: Edit `httpd.conf`, uncomment `LoadModule rewrite_module modules/mod_rewrite.so`

5. **Base URL:**
   - Edit `config/config.php` - set `BASE_URL` to your installation (e.g., `http://localhost/uni`)

6. **Create uploads directory** (if not exists):
   ```
   mkdir uploads\resumes
   ```
   Ensure the web server can write to this directory.

### AI & RAG Backend Setup (Optional but Required for Smart Features)

The AI capabilities are powered by a FastAPI service located in the `/RAG` directory.

1. **Environment Config:**
   - Create a `.env` file in `/RAG` using `.env.example` as a template.
   - Required Keys: `PINECONE_API_KEY`, `OPENROUTER_API_KEY`, `MISTRAL_API_KEY`, `JWT_SECRET`.

2. **Run with Docker (Recommended):**

   ```bash
   cd RAG
   docker build -t rag-backend .
   docker run -d --name rag-app -p 8000:8000 --env-file .env rag-backend
   ```

3. **Run Manually:**
   ```bash
   cd RAG
   python -m venv venv
   source venv/bin/activate  # venv\Scripts\activate on Windows
   pip install -r requirements.txt
   uvicorn main:app --host 0.0.0.0 --port 8000
   ```

### Advanced Security & Reliability Guardrails

The RAG service implements multiple layers of protection:

- **Authentication:** All AI endpoints are secured via JWT middleware.
- **Content Filtering:** NeMo Guardrails protect against hallucinations and inappropriate content.
- **Resilience:** Circuit Breaker patterns to handle external API (Pinecone/Mistral) failures.
- **Safety:** Built-in prompt injection protection and input sanitization.
- **Rate Limiting:** IP-based rate limiting to prevent automated abuse.

### Default Admin Login (Local Development)

- **Email:** `admin@unihelp.edu`
- **Password:** `Admin@123`

> ⚠️ **Security Warning:** Change default credentials immediately upon deploying to any public or production environment!

## Project Structure

```
├── app/
│   ├── controllers/     # PHP controllers
│   └── views/          # HTML templates
├── assets/
│   └── css/            # Stylesheets
├── config/             # Configuration
├── core/               # Auth, Controller, Model base classes
├── database/           # SQL schema
├── uploads/resumes/    # Resume uploads
├── index.php           # Front controller
├── .htaccess           # URL rewriting
├── README.md
│
└── RAG/                # AI & RAG Backend (FastAPI, Python)
   ├── api/           # API routes (chat, upload, delete)
   ├── config/        # Guardrails and prompts config
   ├── services/      # Embedding and retrieval logic
   └── main.py        # Entry point for RAG service
```

## Routes

| URL                    | Description           |
| ---------------------- | --------------------- |
| /                      | Landing page          |
| /login                 | Login                 |
| /register              | Registration          |
| /dashboard/student     | Student dashboard     |
| /dashboard/faculty     | Faculty dashboard     |
| /dashboard/alumni      | Alumni dashboard      |
| /dashboard/admin       | Admin panel           |
| /career-roadmap        | Career roadmap        |
| /skill-tracker         | Skill tracker         |
| /resume                | Resume upload/review  |
| /opportunities         | Placement hub         |
| /interview-experiences | Interview experiences |
| /mentorship            | Mentorship            |

## Security Notes

- Passwords hashed with `password_hash()` (bcrypt)
- CSRF protection on forms
- Session-based authentication
- Role-based access control
- Secure file upload (PDF only, size limit)

## Internal AI Service Reference

| Component      | Port        | Security         | Description                         |
| -------------- | ----------- | ---------------- | ----------------------------------- |
| **Core API**   | `8000`      | JWT/Rate-Limited | FastAPI endpoints for AI operations |
| **Vector DB**  | `RPC`       | API Key Secured  | Pinecone hybrid search storage      |
| **Guardrails** | `Local`     | Built-in Rails   | NeMo Guardrails for content safety  |
| **Swagger**    | `8000/docs` | Public Read-Only | Interactive API documentation       |

## License

Final Year Project - Educational Use

## Recent Updates (2026-02-12)

- **UI Redesign:**
  - Modernized **Home Page** with new Hero section, Stats, and Features grid.
  - Updated color palette and typography (Poppins font).
  - Enhanced **Dashboards** for all roles (Student, Faculty, Alumni, Admin).
  - Improved **Navbar** visibility and styling.
- **Bug Fixes:**
  - Addressed missing Career Roadmap entries ('startup', 'research', 'other').
  - Fixed SQL schema for new roadmap types.
  - Resolved `add_missing_roadmaps.php` script issues.
