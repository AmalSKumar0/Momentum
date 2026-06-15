# Momentum Deployment Guide (Docker & Vercel)

This document describes how to run Momentum locally using Docker and how to host it on Vercel.

---

## 🐳 Option 1: Local Development with Docker

Docker Compose orchestrates both the PHP web server and a MySQL database automatically.

### Prerequisites
- Docker and Docker Compose installed and running.

### How to Run
1. Run the following command in the project root:
   ```bash
   docker compose up --build
   ```
2. The application will start and be available at [http://localhost:8080](http://localhost:8080).
3. The database is automatically initialized with the schema and seed data in `habit_tracker.sql`.
4. Changes made to PHP, HTML, CSS, or JS files will hot-reload automatically due to the volume binding.

---

## 🚀 Option 2: Hosting on Vercel

Vercel is serverless, meaning it runs the PHP code via serverless functions and serves assets statically.

### Prerequisites
- A remote MySQL/MariaDB database (e.g. Aiven, Railway, Clever Cloud, etc.).
- Vercel account.

### How to Deploy
1. **Set up the Database**:
   - Provision a MySQL database with a cloud provider.
   - Import the tables and initial seed data from `habit_tracker.sql`.
2. **Add Environment Variables to Vercel**:
   - Go to your Vercel Project Settings > **Environment Variables** and add:
     - `DB_HOST`: Hostname of your remote database (e.g. `mysql-xxx.aivencloud.com`)
     - `DB_PORT`: Port of your remote database (e.g. `10738` - Aiven uses custom ports)
     - `DB_USER`: Remote database user (typically `avnadmin` for Aiven)
     - `DB_PASSWORD`: Remote database password
     - `DB_NAME`: Remote database name (e.g. `defaultdb` or your custom DB name)
     - `DB_SSL_CA`: `ca.pem` (This points to the SSL CA certificate)
3. **Handle Aiven SSL Certificate**:
   - Aiven MySQL strictly requires SSL. Download the **CA Certificate** (`ca.pem`) from the Overview tab of your MySQL service in the Aiven Console.
   - Save this file as `ca.pem` in the root of your project directory.
   - Commit and push `ca.pem` to your git repository (`git add ca.pem && git commit -m "add Aiven CA cert" && git push`). Vercel will upload it during build, and the application will load it to establish a secure connection.
4. **Deploy the Project**:
   - Link your GitHub repository to Vercel.
   - Deploy. Vercel reads `vercel.json` and routes requests through the `api/index.php` serverless router while serving style, script, and image folders statically.
