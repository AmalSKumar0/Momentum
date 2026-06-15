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
     - `DB_USER`: Remote database user (e.g. `avnadmin`)
     - `DB_PASSWORD`: Remote database password
     - `DB_NAME`: Remote database name (e.g. `habit_tracker`)
3. **Deploy the Project**:
   - Link your GitHub repository to Vercel.
   - Deploy. Vercel reads `vercel.json` and routes requests through the `api/index.php` serverless router while serving style, script, and image folders statically.
