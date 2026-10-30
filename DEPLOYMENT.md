# 🚀 UniHelp — Production Deployment Guide (AWS EC2)

> **Goal:** Deploy the full UniHelp stack (MySQL + PHP/Apache + RAG Chatbot) on a single AWS EC2 instance with a live domain, HTTPS, and production security.

---

## 📋 Table of Contents

1. [Architecture Overview](#-1-architecture-overview)
2. [Prerequisites Checklist](#-2-prerequisites-checklist)
3. [Phase 1 — EC2 Instance Setup](#-phase-1--ec2-instance-setup)
4. [Phase 2 — Install Docker & Docker Compose](#-phase-2--install-docker--docker-compose)
5. [Phase 3 — Deploy Application](#-phase-3--deploy-application)
6. [Phase 4 — Domain & SSL (HTTPS)](#-phase-4--domain--ssl-https)
7. [Phase 5 — Security Hardening](#-phase-5--security-hardening)
8. [Phase 6 — Monitoring & Maintenance](#-phase-6--monitoring--maintenance)
9. [Troubleshooting](#-troubleshooting)
10. [Optional: Kubernetes Deployment](#-optional-kubernetes-deployment)

---

## 🏗 1. Architecture Overview

```
                          ┌─────────────────────────────────────────────┐
   Internet               │            AWS EC2 Instance                 │
   ────────►  :443 (HTTPS) │                                             │
                          │   ┌──────────────────┐                     │
                          │   │  Nginx Reverse    │  (SSL termination)  │
                          │   │  Proxy (:80/:443) │                     │
                          │   └────┬─────────┬────┘                     │
                          │        │         │                          │
                          │   :80  ▼    :8000 ▼                         │
                          │  ┌──────────┐  ┌──────────┐                │
                          │  │ PHP/     │  │ RAG      │                │
                          │  │ Apache   │  │ FastAPI  │                │
                          │  │ (Web UI) │  │ (AI API) │                │
                          │  └────┬─────┘  └────┬─────┘                │
                          │       │              │                      │
                          │       ▼              ▼                      │
                          │    ┌─────────────────────┐                 │
                          │    │   MySQL 8.0          │                 │
                          │    │   (unicopy-net)      │                 │
                          │    └─────────────────────┘                 │
                          └─────────────────────────────────────────────┘
```

**Why Docker Compose instead of Kubernetes?**

| Factor | Docker Compose | Kubernetes |
|---|---|---|
| **Your use case (3 containers, 1 server)** | ✅ Perfect fit | ❌ Massive overkill |
| **Setup complexity** | 5 minutes | 2-3 hours minimum |
| **Resource overhead** | ~50 MB | ~500 MB+ (control plane) |
| **Learning curve** | You already know it | Steep (kubectl, manifests, etc.) |
| **When to switch** | — | Multiple servers, auto-scaling, 10+ services |

> **Verdict:** Use Docker Compose. You already have `docker-compose.prod.yml` ready. Kubernetes is documented at the bottom as an optional advanced path if you ever scale beyond a single server.

---

## ✅ 2. Prerequisites Checklist

Before starting, make sure you have:

- [ ] **AWS Account** with billing enabled
- [ ] **Domain name** purchased (e.g., `unihelp.com` from Namecheap, GoDaddy, or AWS Route 53)
- [ ] **Docker Hub images pushed** (you already have these):
  - `rajtejani38/unihelp:php-apache`
  - `rajtejani38/unihelp:rag-backend`
- [ ] **Environment files ready:**
  - `.env` (root — DB, Gemini, SMTP, RAG keys)
  - `RAG/.env` (Pinecone, Mistral, JWT keys)
- [ ] **Database dump:** `database/unihelp_master.sql`
- [ ] **SSH key pair** for EC2 access (create during EC2 launch if you don't have one)

---

## 🖥 Phase 1 — EC2 Instance Setup

### Step 1.1: Launch EC2 Instance

1. Go to **AWS Console → EC2 → Launch Instance**

2. Configure:

   | Setting | Value |
   |---|---|
   | **Name** | `unihelp-production` |
   | **AMI** | Ubuntu Server 24.04 LTS (Free Tier eligible) |
   | **Instance Type** | `t2.medium` (2 vCPU, 4 GB RAM) — minimum for RAG backend |
   | **Key Pair** | Create new or select existing `.pem` file |
   | **Storage** | 30 GB gp3 (default 8 GB is too small) |

   > ⚠️ **`t2.micro` (free tier) has only 1 GB RAM** — the RAG backend alone needs ~2 GB. Use `t2.medium` or `t2.small` at minimum.

3. **Network Settings (Security Group)** — allow these ports:

   | Port | Protocol | Source | Purpose |
   |---|---|---|---|
   | **22** | TCP | Your IP only | SSH access |
   | **80** | TCP | 0.0.0.0/0 (Anywhere) | HTTP |
   | **443** | TCP | 0.0.0.0/0 (Anywhere) | HTTPS |

   > 🔒 Do **NOT** open port 3306 (MySQL) or 8000 (RAG API) to the public. They stay internal.

4. Click **Launch Instance**

### Step 1.2: Allocate Elastic IP (Static IP)

Without this, your server's IP changes every time you stop/start the instance.

1. Go to **EC2 → Elastic IPs → Allocate Elastic IP address**
2. Click **Associate** → select your `unihelp-production` instance
3. Note down this IP: `___.___.___.__` (your permanent server IP)

### Step 1.3: Connect via SSH

```bash
# From your local terminal (PowerShell / Git Bash)
# Replace with your .pem file path and Elastic IP
ssh -i "C:\path\to\your-key.pem" ubuntu@YOUR_ELASTIC_IP
```

If you get a permission error on the `.pem` file:
```powershell
# PowerShell — fix .pem permissions
icacls "C:\path\to\your-key.pem" /inheritance:r /grant:r "$($env:USERNAME):(R)"
```

---

## 🐳 Phase 2 — Install Docker & Docker Compose

Run these commands **on the EC2 instance** (after SSH):

### Step 2.1: System Update

```bash
sudo apt update && sudo apt upgrade -y
```

### Step 2.2: Install Docker

```bash
# Install Docker using official script
curl -fsSL https://get.docker.com | sudo sh

# Add your user to docker group (avoids needing sudo for every docker command)
sudo usermod -aG docker $USER

# Apply group change (or log out and back in)
newgrp docker

# Verify
docker --version
docker compose version
```

### Step 2.3: Verify Docker Works

```bash
docker run --rm hello-world
```

You should see "Hello from Docker!" — if yes, Docker is ready.

---

## 📦 Phase 3 — Deploy Application

### Step 3.1: Create Project Directory

```bash
mkdir -p ~/unihelp && cd ~/unihelp
```

### Step 3.2: Transfer Files to EC2

From your **local machine** (PowerShell), upload the required files:

```powershell
# Set variables
$KEY = "C:\path\to\your-key.pem"
$SERVER = "ubuntu@YOUR_ELASTIC_IP"
$PROJECT = "d:\unicopy"

# Upload docker-compose.prod.yml
scp -i $KEY "$PROJECT\docker-compose.prod.yml" "${SERVER}:~/unihelp/docker-compose.prod.yml"

# Upload environment files
scp -i $KEY "$PROJECT\.env" "${SERVER}:~/unihelp/.env"
scp -i $KEY -r "$PROJECT\RAG\.env" "${SERVER}:~/unihelp/RAG/.env"

# Upload database dump
scp -i $KEY "$PROJECT\database\unihelp_master.sql" "${SERVER}:~/unihelp/database/unihelp_master.sql"
```

> **Create the required directories on EC2 first:**
> ```bash
> mkdir -p ~/unihelp/database ~/unihelp/RAG ~/unihelp/public/uploads
> ```

### Step 3.3: Update Production Environment Variables

SSH into the server and edit `.env`:

```bash
cd ~/unihelp
nano .env
```

**Critical changes for production:**

```env
# ── Database (USE STRONG PASSWORD!) ────────────────────────
DB_HOST=mysql
DB_NAME=unihelp
DB_USER=root
DB_PASS=YOUR_STRONG_PASSWORD_HERE          # ← CHANGE THIS
MYSQL_ROOT_PASSWORD=YOUR_STRONG_PASSWORD_HERE  # ← MUST MATCH DB_PASS
MYSQL_ALLOW_EMPTY_PASSWORD=no              # ← CHANGE to 'no'

# ── RAG Backend URL (change from localhost to your domain) ──
RAG_API_URL=https://yourdomain.com:8000/api/chat   # ← update after domain setup
# OR if using Nginx reverse proxy (recommended):
RAG_API_URL=https://yourdomain.com/api/chat

# ── Keep all other keys (Gemini, SMTP, etc.) ───────────────
```

Also edit `RAG/.env`:
```bash
nano RAG/.env
```

```env
# Update CORS to allow your domain
CORS_ORIGINS=https://yourdomain.com,https://www.yourdomain.com
```

### Step 3.4: Update docker-compose.prod.yml for Production

The existing `docker-compose.prod.yml` volume-mounts the full source code (`. :/var/www/html/unicopy`), which requires the codebase on the server. Since you're using pre-built Docker images, **remove the source volume mount** so the image's baked-in code is used instead.

Create a clean production compose file on the server:

```bash
cat > ~/unihelp/docker-compose.prod.yml << 'EOF'
# UniHelp — Production Docker Compose
services:

  # ── 1. MySQL Database ──────────────────────────────────
  mysql:
    image: mysql:8.0
    container_name: unicopy-mysql
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: "${MYSQL_ROOT_PASSWORD}"
      MYSQL_DATABASE: "${DB_NAME:-unihelp}"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/unihelp_master.sql:/docker-entrypoint-initdb.d/init.sql:ro
    networks:
      - unicopy-net
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 30s

  # ── 2. PHP + Apache (Website) ──────────────────────────
  php-apache:
    image: rajtejani38/unihelp:php-apache
    container_name: unicopy-web
    restart: unless-stopped
    ports:
      - "8080:80"           # Changed to 8080 — Nginx handles port 80
    env_file:
      - .env
    volumes:
      - uploads_data:/var/www/html/unicopy/public/uploads
    depends_on:
      mysql:
        condition: service_healthy
    networks:
      - unicopy-net

  # ── 3. RAG Chatbot Backend ─────────────────────────────
  rag-backend:
    image: rajtejani38/unihelp:rag-backend
    container_name: unicopy-rag
    restart: unless-stopped
    ports:
      - "8000:8000"
    env_file:
      - ./RAG/.env
    volumes:
      - hf_cache:/root/.cache/huggingface
    depends_on:
      mysql:
        condition: service_healthy
    networks:
      - unicopy-net

volumes:
  mysql_data:
  uploads_data:
  hf_cache:

networks:
  unicopy-net:
    driver: bridge
EOF
```

### Step 3.5: Pull Images & Start

```bash
cd ~/unihelp

# Login to Docker Hub (needed for private images)
docker login

# Pull latest images
docker compose -f docker-compose.prod.yml pull

# Start everything
docker compose -f docker-compose.prod.yml up -d

# Check status
docker compose -f docker-compose.prod.yml ps

# Check logs
docker compose -f docker-compose.prod.yml logs -f
```

### Step 3.6: Verify Containers Are Running

```bash
# All 3 should show "Up" and "healthy"
docker compose -f docker-compose.prod.yml ps

# Quick health checks
curl http://localhost:8080/unicopy     # PHP website
curl http://localhost:8000/health      # RAG API
```

---

## 🌐 Phase 4 — Domain & SSL (HTTPS)

### Step 4.1: Point Domain to EC2

Go to your domain registrar (GoDaddy, Namecheap, or Route 53) and add DNS records:

| Type | Name | Value | TTL |
|---|---|---|---|
| **A** | `@` | `YOUR_ELASTIC_IP` | 300 |
| **A** | `www` | `YOUR_ELASTIC_IP` | 300 |

> DNS propagation takes 5-30 minutes. Check with: `nslookup yourdomain.com`

### Step 4.2: Install Nginx (Reverse Proxy)

Nginx sits in front of your Docker containers — it handles SSL, routes traffic, and hides internal ports.

```bash
sudo apt install nginx -y
sudo systemctl enable nginx
```

### Step 4.3: Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/unihelp
```

Paste this configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    # ── PHP Website (main site) ──────────────────────
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # ── RAG API (chatbot backend) ────────────────────
    location /api/ {
        proxy_pass http://127.0.0.1:8000/api/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # WebSocket support (if needed)
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }

    # ── RAG Health Check ─────────────────────────────
    location /health {
        proxy_pass http://127.0.0.1:8000/health;
    }

    # ── File upload size ─────────────────────────────
    client_max_body_size 20M;
}
```

Enable the site:

```bash
# Enable the config
sudo ln -s /etc/nginx/sites-available/unihelp /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test config
sudo nginx -t

# Reload
sudo systemctl reload nginx
```

### Step 4.4: Install SSL Certificate (Let's Encrypt — Free)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate (auto-configures Nginx)
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Follow prompts:
#   - Enter email
#   - Agree to terms
#   - Choose redirect HTTP → HTTPS (recommended)
```

**Auto-renewal** is set up automatically. Verify:

```bash
sudo certbot renew --dry-run
```

### Step 4.5: Update Application URLs

After SSL is working, update your `.env` on the server:

```bash
nano ~/unihelp/.env
```

```env
RAG_API_URL=https://yourdomain.com/api/chat
```

Also update `config/config.php` **inside the Docker image** — you need to rebuild the PHP image with the production BASE_URL:

> **Important:** The `BASE_URL` in `config/config.php` is currently hardcoded to `http://localhost/unicopy`. For production, you need to either:
>
> **Option A (Recommended):** Make BASE_URL read from environment variable:
> ```php
> define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/unicopy');
> ```
> Then set `BASE_URL=https://yourdomain.com/unicopy` in your `.env`
>
> **Option B:** Rebuild the Docker image with the correct URL baked in.

Restart containers to apply changes:

```bash
cd ~/unihelp
docker compose -f docker-compose.prod.yml restart
```

### Step 4.6: Verify HTTPS

Open in browser:
- `https://yourdomain.com/unicopy` → Website loads with 🔒
- `https://yourdomain.com/health` → `{"status": "OK", ...}`

---

## 🔒 Phase 5 — Security Hardening

### 5.1: Firewall (UFW)

```bash
# Enable firewall
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP (redirects to HTTPS)
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable

# Verify
sudo ufw status
```

### 5.2: Lock Down Docker Ports

Edit `docker-compose.prod.yml` — bind internal ports to localhost only so they're not exposed to the internet:

```yaml
  php-apache:
    ports:
      - "127.0.0.1:8080:80"     # Only accessible via Nginx

  rag-backend:
    ports:
      - "127.0.0.1:8000:8000"   # Only accessible via Nginx
```

> ⚠️ **Docker bypasses UFW by default.** Binding to `127.0.0.1` is the correct fix.

### 5.3: Secure MySQL

MySQL is already unexposed (no port mapping in compose). Additional hardening:

```bash
# Connect to MySQL inside container
docker exec -it unicopy-mysql mysql -u root -p

# Inside MySQL:
ALTER USER 'root'@'%' IDENTIFIED BY 'YourSuperStrongPassword!';
FLUSH PRIVILEGES;
EXIT;
```

### 5.4: Change Default Admin Password

The default admin login (`admin@unihelp.edu` / `Admin@123`) must be changed:

1. Open `https://yourdomain.com/unicopy/login`
2. Login with default credentials
3. Go to profile/settings and change the password
4. Or update directly in the database:

```bash
docker exec -it unicopy-mysql mysql -u root -p unihelp \
  -e "UPDATE users SET password = '\$2y\$10\$YOUR_BCRYPT_HASH' WHERE email = 'admin@unihelp.edu';"
```

### 5.5: SSH Hardening

```bash
sudo nano /etc/ssh/sshd_config
```

```
PermitRootLogin no
PasswordAuthentication no        # Key-only auth
MaxAuthTries 3
```

```bash
sudo systemctl restart sshd
```

### 5.6: Automatic Security Updates

```bash
sudo apt install unattended-upgrades -y
sudo dpkg-reconfigure -plow unattended-upgrades
```

---

## 📊 Phase 6 — Monitoring & Maintenance

### 6.1: Container Auto-Restart

Already configured — `restart: unless-stopped` in compose file ensures containers restart after server reboot.

To also start Docker on boot:

```bash
sudo systemctl enable docker
```

### 6.2: Log Management

```bash
# View all logs
docker compose -f docker-compose.prod.yml logs -f

# View specific service
docker compose -f docker-compose.prod.yml logs -f php-apache
docker compose -f docker-compose.prod.yml logs -f rag-backend
docker compose -f docker-compose.prod.yml logs -f mysql

# Limit log file size (add to each service in compose):
#   logging:
#     driver: "json-file"
#     options:
#       max-size: "10m"
#       max-file: "3"
```

### 6.3: Database Backups

Create a cron job for daily MySQL backups:

```bash
# Create backup script
cat > ~/unihelp/backup.sh << 'EOF'
#!/bin/bash
BACKUP_DIR=~/unihelp/backups
mkdir -p $BACKUP_DIR
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
docker exec unicopy-mysql mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" unihelp \
  > "$BACKUP_DIR/unihelp_${TIMESTAMP}.sql"
# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
echo "Backup completed: unihelp_${TIMESTAMP}.sql"
EOF

chmod +x ~/unihelp/backup.sh

# Schedule daily backup at 2 AM
(crontab -l 2>/dev/null; echo "0 2 * * * cd ~/unihelp && source .env && ~/unihelp/backup.sh") | crontab -
```

### 6.4: Updating the Application

When you push new Docker images:

```bash
cd ~/unihelp

# Pull latest images
docker compose -f docker-compose.prod.yml pull

# Restart with new images (zero-downtime-ish)
docker compose -f docker-compose.prod.yml up -d

# Verify
docker compose -f docker-compose.prod.yml ps
```

### 6.5: Monitoring Disk Space

```bash
# Check disk usage
df -h

# Check Docker disk usage
docker system df

# Clean up unused images/containers
docker system prune -f
```

---

## 🔧 Troubleshooting

### Container won't start

```bash
# Check logs for the failing container
docker compose -f docker-compose.prod.yml logs <service-name>

# Check if port is already in use
sudo lsof -i :80
sudo lsof -i :8000
```

### MySQL connection refused

```bash
# Verify MySQL is healthy
docker compose -f docker-compose.prod.yml ps mysql

# Check MySQL logs
docker compose -f docker-compose.prod.yml logs mysql

# Test connection from PHP container
docker exec -it unicopy-web php -r "new PDO('mysql:host=mysql;dbname=unihelp', 'root', 'YOUR_PASSWORD');"
```

### RAG backend unhealthy / OOM

The RAG backend needs significant memory for ML models. If the container keeps dying:

```bash
# Check memory usage
docker stats

# If OOM — upgrade EC2 instance type or add swap:
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

### SSL certificate renewal fails

```bash
sudo certbot renew --force-renewal
sudo systemctl reload nginx
```

### Website shows "localhost" URLs

The `BASE_URL` in `config/config.php` is still set to `http://localhost/unicopy`. Fix by making it environment-driven (see Phase 4, Step 4.5).

---

## ☸ Optional: Kubernetes Deployment

> **When to use K8s:** Only if you need auto-scaling, multi-server deployment, or are deploying for a company/startup with real production traffic. For a single EC2 instance, Docker Compose is simpler and sufficient.

<details>
<summary><b>Click to expand Kubernetes setup guide</b></summary>

### K8s Option A: Amazon EKS (Managed Kubernetes)

**Cost:** ~$75/month for the EKS cluster + EC2 node costs. Expensive for a student project.

```bash
# Install eksctl
curl --silent --location "https://github.com/eksctl-io/eksctl/releases/latest/download/eksctl_Linux_amd64.tar.gz" | tar xz -C /tmp
sudo mv /tmp/eksctl /usr/local/bin

# Create cluster
eksctl create cluster \
  --name unihelp-cluster \
  --region ap-south-1 \
  --nodegroup-name standard-nodes \
  --node-type t3.medium \
  --nodes 2 \
  --nodes-min 1 \
  --nodes-max 3
```

### K8s Option B: k3s (Lightweight K8s on your EC2)

Better for single-server setups. Gives you K8s features without the overhead.

```bash
# Install k3s (lightweight Kubernetes)
curl -sfL https://get.k3s.io | sh -

# Verify
sudo k3s kubectl get nodes
```

### Kubernetes Manifests

You would need to create these YAML files:

```
k8s/
├── namespace.yml          # unihelp namespace
├── secrets.yml            # env vars as K8s secrets
├── mysql-deployment.yml   # MySQL StatefulSet + PVC
├── mysql-service.yml      # ClusterIP service for MySQL
├── php-deployment.yml     # PHP/Apache Deployment
├── php-service.yml        # ClusterIP service for PHP
├── rag-deployment.yml     # RAG Deployment
├── rag-service.yml        # ClusterIP service for RAG
└── ingress.yml            # Nginx Ingress for routing
```

**Example MySQL Deployment:**

```yaml
apiVersion: apps/v1
kind: StatefulSet
metadata:
  name: mysql
  namespace: unihelp
spec:
  serviceName: mysql
  replicas: 1
  selector:
    matchLabels:
      app: mysql
  template:
    metadata:
      labels:
        app: mysql
    spec:
      containers:
      - name: mysql
        image: mysql:8.0
        env:
        - name: MYSQL_ROOT_PASSWORD
          valueFrom:
            secretKeyRef:
              name: unihelp-secrets
              key: mysql-root-password
        - name: MYSQL_DATABASE
          value: unihelp
        ports:
        - containerPort: 3306
        volumeMounts:
        - name: mysql-data
          mountPath: /var/lib/mysql
  volumeClaimTemplates:
  - metadata:
      name: mysql-data
    spec:
      accessModes: ["ReadWriteOnce"]
      resources:
        requests:
          storage: 10Gi
```

**Example PHP Deployment:**

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: php-apache
  namespace: unihelp
spec:
  replicas: 2    # Can scale horizontally!
  selector:
    matchLabels:
      app: php-apache
  template:
    metadata:
      labels:
        app: php-apache
    spec:
      containers:
      - name: php-apache
        image: rajtejani38/unihelp:php-apache
        ports:
        - containerPort: 80
        envFrom:
        - secretRef:
            name: unihelp-secrets
```

### Deploy to K8s

```bash
kubectl apply -f k8s/namespace.yml
kubectl apply -f k8s/secrets.yml
kubectl apply -f k8s/mysql-deployment.yml
kubectl apply -f k8s/mysql-service.yml
kubectl apply -f k8s/php-deployment.yml
kubectl apply -f k8s/php-service.yml
kubectl apply -f k8s/rag-deployment.yml
kubectl apply -f k8s/rag-service.yml
kubectl apply -f k8s/ingress.yml

# Verify
kubectl get pods -n unihelp
kubectl get services -n unihelp
```

</details>

---

## 📝 Quick Reference — Full Deployment Checklist

Use this as a step-by-step checklist:

```
── PREPARE ─────────────────────────────────────────────────
[ ] Push latest Docker images to Docker Hub
[ ] Buy domain name
[ ] Have .env and RAG/.env files ready with production values

── EC2 SETUP ───────────────────────────────────────────────
[ ] Launch EC2 (Ubuntu 24.04, t2.medium, 30 GB storage)
[ ] Configure Security Group (ports 22, 80, 443)
[ ] Allocate and associate Elastic IP
[ ] SSH into instance

── INSTALL ─────────────────────────────────────────────────
[ ] Install Docker & Docker Compose
[ ] Verify with `docker run hello-world`

── DEPLOY ──────────────────────────────────────────────────
[ ] Create ~/unihelp directory
[ ] Upload docker-compose.prod.yml, .env, RAG/.env, database dump
[ ] Set strong MySQL password in .env
[ ] docker compose pull & up -d
[ ] Verify all 3 containers are healthy

── DOMAIN & SSL ────────────────────────────────────────────
[ ] Point domain DNS A records to Elastic IP
[ ] Install Nginx as reverse proxy
[ ] Configure Nginx site for unihelp
[ ] Install Certbot & get SSL certificate
[ ] Verify https://yourdomain.com works

── SECURITY ────────────────────────────────────────────────
[ ] Enable UFW firewall
[ ] Bind Docker ports to 127.0.0.1
[ ] Set strong MySQL root password
[ ] Change default admin password
[ ] Disable SSH root login & password auth
[ ] Enable automatic security updates

── POST-DEPLOY ─────────────────────────────────────────────
[ ] Set up daily database backups (cron)
[ ] Add swap space (if t2.medium is tight)
[ ] Test all features end-to-end
[ ] Monitor with `docker stats` and `docker logs`
```

---

## 💰 Estimated Monthly Cost (AWS)

| Resource | Cost |
|---|---|
| EC2 `t2.medium` (on-demand) | ~$34/month |
| Elastic IP (while instance running) | Free |
| 30 GB EBS storage | ~$2.40/month |
| Data transfer (first 100 GB) | Free |
| SSL (Let's Encrypt) | Free |
| Domain name | ~$10-15/year |
| **Total** | **~$37/month** |

> **Save money:** Use a Reserved Instance (1-year commitment) for ~$22/month, or Spot Instances for testing.

---

*Last Updated: June 2026*
