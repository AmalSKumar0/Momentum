# Momentum---Gamified-Habit-Tracker

**Momentum** is a web-based productivity application that turns your daily life into an RPG. It leverages gamification psychology to help users build consistent habits by rewarding completion with **Gold Coins** and penalizing missed tasks by reducing **Health Points (HP)**.

![Project Screenshot](assets/image.png)


## 🌟 Features

* **Gamified Dashboard:**
    * **Health Bar System:** Visual HP bar (out of 100). Missed habits deplete health.
    * **Economy System:** Earn Gold Coins for every completed task.
    * **Leveling:** (Future implementation) Use XP to level up.
* **Task Management:**
    * Simple "Did" / "Didn't" interface for quick logging.
    * Daily reset of recurring habits.
* **User Interface:**
    * Modern, clean aesthetic with card-based layout.
    * Calendar view with active date highlighting.
    * Weather widget UI.
    * Profile integration.
* **Authentication:**
    * User Registration and Login.
    * Secure password hashing.

## 🛠️ Tech Stack

* **Frontend:** HTML5, CSS3 (Flexbox/Grid), JavaScript.
* **Backend:** PHP (Native).
* **Database:** MySQL.
* **Fonts:** Google Fonts (Poppins).

## 🚀 Installation & Setup

### Prerequisites
* A local server environment (XAMPP, WAMP, MAMP, or Docker).
* PHP 7.4 or higher.
* MySQL.

### Steps

1.  **Clone the Repository**
    ```bash
    git clone [https://github.com/yourusername/habitquest.git](https://github.com/yourusername/habitquest.git)
    cd habitquest
    ```

2.  **Database Setup**
    * Open phpMyAdmin (or your preferred SQL tool).
    * Create a new database named `habit_tracker_db`.
    * Import the `database.sql` file located in the `root` directory (see Schema below if file is missing).

3.  **Configure Connection**
    * Open `config.php` (or `db.php`).
    * Update your database credentials:
    ```php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "habit_tracker_db";
    ```

4.  **Run the Project**
    * Place the project folder inside your `htdocs` (XAMPP) or `www` (WAMP) folder.
    * Navigate to `http://localhost/habitquest` in your browser.


## 🎮 Game Logic

  * **Health (HP):** Starts at 100.
      * If a user marks a task as **"Didn't"**, HP decreases by **10**.
      * If HP reaches 0, the user "dies" (reset stats or loose levels).
  * **Gold:** Starts at 0.
      * If a user marks a task as **"Did"**, Gold increases by **50**.
      * Gold can be used to "buy" rewards or restore HP (Feature in progress).

## 🤝 Contributing

Contributions are welcome\! Please follow these steps:

1.  Fork the repository.
2.  Create a new branch (`git checkout -b feature/AmazingFeature`).
3.  Commit your changes (`git commit -m 'Add some AmazingFeature'`).
4.  Push to the branch (`git push origin feature/AmazingFeature`).
5.  Open a Pull Request.

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

-----