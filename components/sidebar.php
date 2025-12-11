 <nav class="sidebar" id="sidebar">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-gamepad"></i></div>
            Momentum
        </div>
        
        <ul class="nav-links">
            <a class="nav-item  <?php if($page == 1) echo 'active'; ?>" href="Dashboard.php">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="Habit.php" class="nav-item <?php if($page == 2) echo 'active'; ?>">
                <i class="fas fa-scroll"></i> My Habits
            </a>
            <a href="Shop.php" class="nav-item <?php if($page == 3) echo 'active'; ?>">
                <i class="far fa-calendar-alt"></i> Store
            </a>
            <a href="Inventory.php" class="nav-item <?php if($page == 4) echo 'active'; ?>">
                <i class="fas fa-trophy"></i> Inventory
            </a>
            
        </ul>

        <div class="sidebar-footer">
            <button onclick="window.location.href='Auth/logout.php'" class="pass-badge">
    Log out
</button>

           
        </div>
    </nav>