# 🎮 Momentum | Gamified Habit Tracker & RPG Life Simulator

[![Docker Support](https://img.shields.io/badge/Docker-Enabled-blue?logo=docker&logoColor=white)](https://www.docker.com/)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%207.4-8892BF?logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

**Momentum** is a premium, web-based gamified productivity platform that turns building habits and completing daily tasks into an engaging RPG experience. By leveraging behavioral psychology and gamification design patterns, Momentum helps users build long-term routines by rewarding consistent habits with **Gold** and **XP**, while penalizing missed tasks with **HP damage**.

---

## 🌟 Key Features

### ⚔️ RPG Character progression
* **Dynamic Level & XP System:** Earn XP by completing habits. Leveling up occurs dynamically when your XP reaches `Level * 100`. Leveling up automatically restores 25 HP.
* **Tiered Titles:** Characters receive custom titles based on their level (*Novice Adventurer*, *Experienced Scholar*, *Elite Warrior*, *Grand Magus*, *Legendary Master*).
* **Streak Multipliers:** Maintain consistency to build streak chains. Streaks grant escalating rewards multipliers (e.g. 1.1x at 5+ days, 1.25x at 10+ days, 1.5x at 30+ days).
* **Scaling Difficulty & Penalty System:** Quests are categorized by difficulty (*Easy*, *Medium*, *Hard*). Failing a hard quest scales damage accordingly (reducing HP by 5, 10, or 20). If HP reaches 0, the player dies and must buy a resurrection potion to respawn.

### 🧪 Apothecary Shop & Inventory
* **Item Purchases:** Spend earned Gold Coins at the apothecary shop.
* **Consumables catalog:**
  * **Minor Health Potion (50g):** Heals 25 HP.
  * **Major Health Potion (90g):** Heals 50 HP.
  * **Elixir of Life (150g):** Restores HP to 100%.
  * **Scroll of Wisdom (80g):** Instantly grants 100 XP.
* **Inventory Tracking:** Items bought are placed in the character's inventory where they can be dynamically used. Using potions updates character stats instantly.

### 📅 Date-Driven Calendar History
* **Activity Viewer:** Select any date on the navigation strip to inspect historic quest completion logs.
* **Interactive Date Strip**: Uses local-timezone neutral date calculations to bypass standard JS UTC-shifting browser bugs.
* **Status Badges:**
  * **Present Day**: Complete or Fail quests using active forms.
  * **Past Days**: Actions are disabled, displaying historical states: **COMPLETED** (Green), **FAILED** (Red), or **MISSED** (Gray).
  * **Future Days**: Displays dashed **UPCOMING** status placeholders.
* **Week & Month Jumping**: Navigate weeks with chevrons or jump months instantly using the month selector.

### 👤 Profile & Account Administration
* **Character Card Visualizer**: Fully interactive visual sheet containing live progress bars for HP and XP.
* **Account Settings**: Form validation allowing users to update their Username, Email, and Password securely with verification checks.

### 🎨 Dark Glassmorphism Design
* **Frosted Glass Cards:** Modern translucent paneling designed with rich gradient backdrops and glowing border highlights.
* **Confetti Rewards:** Features high-framerate gold coin particles using `canvas-confetti` when checking off habits.
* **Responsive Layout:** Optimized flex-and-grid alignments ensuring responsiveness across mobile viewports, tablets, and wide monitors.

---

## 🛠️ Architecture & Tech Stack

* **Frontend:** HTML5, Vanilla CSS3 (Custom Variables, Flexbox, Grid), JavaScript (ES6+).
* **Confetti Engine:** `canvas-confetti` (CDN integration).
* **Backend Core:** PHP (Native), Output Buffering (`ob_start()`) handled globally to manage state-driven redirects.
* **Database Engine:** MySQL 8.0 with transactional processing (`begin_transaction()`, `commit()`, `rollback()`) to ensure absolute data integrity.
* **Environment Virtualization:** Docker & Docker Compose.

---

## 🚀 Getting Started (Local Development)

### Run with Docker (Recommended)

1. **Clone the Repository**
   ```bash
   git clone https://github.com/AmalSKumar0/Momentum.git
   cd Momentum
   ```

2. **Spin Up Containers**
   Launch the web server and database:
   ```bash
   docker-compose up -d --build
   ```
   *This automatically builds the custom PHP image, provisions MySQL 8.0, and executes the database seed script `/docker-entrypoint-initdb.d/habit_tracker.sql`.*

3. **Open the App**
   Open your browser and navigate to:
   ```
   http://localhost:8080/
   ```

---

## 🗄️ Database Schema

The relational database is split into four primary tables:
* `users`: Stores core credentials, password hashes, levels, XP, gold, current streaks, and active HP.
* `habits`: Stores quest titles, difficulty ranks, XP/Gold reward scales, streak counters, and last-completed timestamps.
* `activity`: Logs execution histories mapping timestamps (`completedDay`) and binary completion status (`isComplete = 1/0`) to users and habits.
* `inventory`: Tracks quantities (`Quantity`) of items owned by a user linked to the store templates.

---

## 📄 License

This project is licensed under the MIT License. See the `LICENSE` file for details.