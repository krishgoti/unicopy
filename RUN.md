# 🚀 UniHelp — Master Docker & Environment Run Guide

> **All-In-One Containerized Stack:** PHP Web Server + FastAPI RAG Chatbot + MySQL Database.
>
> With Docker Compose, you no longer need to run XAMPP, start Apache/MySQL manually, or set up local Python virtual environments. One command starts it all.

---

## ⚡ 1. Startup & Control Commands

Always run these commands from the project root (`c:\xampp\htdocs\unicopy`):

| Operation | Command | Description | When to Use |
| :--- | :--- | :--- | :--- |
| **Normal Start** | `docker compose up -d` | Starts all services in the background. | Daily development. |
| **Follow Logs** | `docker compose logs -f` | Stream live logs from all containers. | Checking errors/debug logs. |
| **Fresh Build** | `docker compose up -d --build` | Re-installs dependencies and rebuilds images. | After package/code change or environment shift. |
| **Build Single Service** | `docker compose build <service>` | Rebuilds only the specified service's image (e.g., `rag-backend`). | After modifying a single service's Dockerfile/requirements. |
| **Restart Single Service** | `docker compose restart <service>` | Restarts only the specified container (e.g., `rag-backend`). | Applying quick changes without stopping the entire stack. |
| **Stop Stack** | `docker compose down` | Safely stops containers. *Data is preserved.* | Finishing work for the day. |
| **Full Reset** | `docker compose down -v` | Stops stack and **wipes MySQL database volume**. | Schema changes or resetting DB to clean slate. |
| **Status Check** | `docker compose ps` | Lists all running containers and health status. | Verifying if all 3 containers are active. |

---

## 🔌 2. The DB_HOST Change: `127.0.0.1` vs `mysql`

### The Problem
When you run a database locally on XAMPP, `127.0.0.1` (or `localhost`) points to your local PC.
However, inside a Docker container, `127.0.0.1` refers **only to that specific container itself**. 
* If PHP tries to connect to `127.0.0.1`, it looks inside the PHP container, fails to find MySQL, and crashes.

### The Solution
Docker Compose automatically creates an isolated virtual bridge network (`unicopy-net`) and sets up an internal **DNS Server**.
* It maps container services by their name in `docker-compose.yml`.
* Setting `DB_HOST=mysql` tells Docker: *"Route database queries to the container named `mysql` on the virtual network."*

```
 ┌────────────────────────────────────── Unicopy Bridge Network (unicopy-net) ──────────────────────────────────────┐
 │                                                                                                                   │
 │   ┌────────────────────────┐         DB Host: mysql (DNS resolves to MySQL IP)         ┌───────────────────────┐  │
 │   │   unicopy-web          │ ────────────────────────────────────────────────────────> │   unicopy-mysql       │  │
 │   │   (PHP/Apache on :80)  │                                                           │   (MySQL on :3306)    │  │
 │   └────────────────────────┘                                                           └───────────────────────┘  │
 │                                                                                            ▲                      │
 │   ┌────────────────────────┐                         DB Host: mysql                        │                      │
 │   │   unicopy-rag          │ ──────────────────────────────────────────────────────────────┘                      │
 │   │   (FastAPI on :8000)   │                                                                                      │
 │   └────────────────────────┘                                                                                      │
 └───────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📂 3. Where Files & Data Are Stored

The environment maps files dynamically between your local PC and the Docker containers:

* **PHP Source Code:** `c:\xampp\htdocs\unicopy\`
  * *Live-mounted* inside the web container. Any code modifications you make on your PC reflect instantly in the browser without rebuilding!
* **Uploads Directory:** `c:\xampp\htdocs\unicopy\public\uploads\`
  * *Bind-mounted* directly. Uploaded resumes and course material files stay on your PC, persisting forever even if containers are destroyed.
* **Database Schema Script:** `c:\xampp\htdocs\unicopy\database\unihelp_master.sql`
  * Automatically imported when MySQL initializes for the **first time** (or after a `docker compose down -v` command).
* **Persistent MySQL Data:** Docker Named Volume `mysql_data`
  * Maintains database records, tables, and credentials across normal restarts and updates. Wiped only by `down -v`.
* **RAG LLM Cache:** Docker Named Volume `hf_cache`
  * Caches HuggingFace embeddings and models. Prevents having to re-download heavy model files whenever the container is restarted.

---

## ⚙️ 4. Configuration & Secrets Management

To configure passwords, API keys, and server settings, refer to these specific files:

### 🔑 A. Global Env Config (`.env` in Project Root)
*Controls Database access, UI variables, external APIs, and Email notification SMTP settings.*

```env
DB_HOST=mysql                             # Must remain 'mysql' for Docker networking
DB_NAME=unihelp                           # Database name (matches compose default)
DB_USER=root                              # Admin database user
DB_PASS=                                  # Root password (kept blank by default, see below)

GEMINI_API_KEY=your_google_gemini_key     # Gemini API key for Resume Analysis
RAG_API_URL=http://localhost:8000/api/chat # API path accessed directly by client browser
RAG_STUDENT_TOKEN=your_jwt_auth_token     # JWT verification token for web-to-RAG validation

SMTP_HOST=smtp.gmail.com                  # Gmail or other SMTP server
SMTP_PORT=587                             # Secure SMTP port
SMTP_USER=your_email@gmail.com            # Authentication email
SMTP_PASS=your_gmail_app_password         # Google App Password (NOT personal password)
```

### 🧠 B. RAG Backend Config (`RAG/.env`)
*Controls LLM models, vector store keys, authorization, and rate limit protections.*

```env
PINECONE_API_KEY=your_pinecone_key        # Pinecone database credentials
PINECONE_INDEX=hybrid-search              # Pinecone index name
MISTRAL_API_KEY=your_mistral_key          # LLM Generation backend (Mistral API)
OPENROUTER_API_KEY=your_openrouter_key    # LLM alternate gateway

JWT_SECRET_KEY=super-secret-key           # Secret key used to sign RAG communication tokens
CORS_ORIGINS=http://localhost,http://unicopy-web # Approved client URLs to prevent cross-origin issues
```

### 🔧 C. PHP Environment Config (`docker/php.ini`)
*Controls file upload constraints and execution speed limits.*

* **Upload Limits:** `upload_max_filesize = 20M` & `post_max_size = 20M`
* **Resource Limits:** `memory_limit = 256M` & `max_execution_time = 120`

### 🏗️ D. Container Stack Infrastructure (`docker-compose.yml`)
*Controls port forwarding, container volumes, and network bridge bindings.*

* **Port 80:** Mapped to container port 80 (standard HTTP web server)
* **Port 8000:** Mapped to RAG FastAPI service port
* **Database Initialization:** Points MySQL to automatically load `unihelp_master.sql`

---

## 🔒 5. Upgrading DB Passwords (Security)

By default, the environment is configured with **empty** root passwords for ease of local development. If sharing containers or moving to production, secure your database immediately:

1. Open `.env` (Project Root) and configure your database variables:
   ```env
   DB_PASS=MySecureRootPassword123!
   ```
2. Open `docker-compose.yml` and verify the password variable maps dynamically:
   ```yaml
   MYSQL_ROOT_PASSWORD: "${MYSQL_ROOT_PASSWORD:-}"
   ```
3. Run a database reset to apply the credentials:
   ```powershell
   docker compose down -v
   docker compose up -d
   ```

---

## 🧪 6. Verification Status Checks

Verify that everything runs smoothly by loading the following local links:

| Component | Target URL | Expected Result |
| :--- | :--- | :--- |
| 🌐 **PHP Website** | [http://localhost/unicopy](http://localhost/unicopy) | Landing page loads successfully. |
| 🧬 **RAG Chatbot Health** | [http://localhost:8000/health](http://localhost:8000/health) | Responds with `{"status": "OK", "pinecone": "connected"}`. |
| 📑 **Interactive API Docs** | [http://localhost:8000/docs](http://localhost:8000/docs) | Swagger UI interactive playground. |

---

## ⚠️ 7. Legacy Non-Docker Fallback (XAMPP / Local Python)

If you must run the project locally without Docker, follow these legacy manual instructions:

<details>
<summary><b>Show Legacy Manual Steps (XAMPP & Local venv)</b></summary>

### 1. Database & Web (XAMPP)
1. Open XAMPP Control Panel.
2. Start **Apache** and **MySQL**.
3. Create a database named `unihelp` at `http://localhost/phpmyadmin` and import `database/unihelp_master.sql`.
4. Ensure `DB_HOST=127.0.0.1` is set in the root `.env` file.

### 2. RAG Chatbot (Local Python)
1. Open PowerShell and navigate to the RAG directory:
   ```powershell
   cd C:\xampp\htdocs\unicopy\RAG
   ```
2. Create and activate virtual environment:
   ```powershell
   python -m venv venv
   .\venv\Scripts\Activate.ps1
   ```
3. Install packages & start the server:
   ```powershell
   pip install -r requirements.txt
   uvicorn main:app --host 127.0.0.1 --port 8000 --reload
   ```
4. Update `DB_HOST=127.0.0.1` in your `RAG/.env` file.

</details>

---

*Last Updated: May 2026*
