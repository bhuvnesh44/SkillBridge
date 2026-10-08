<?php
/**
 * Skillbridg — Common Navigation Header Component
 */
require_once __DIR__ . '/auth.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="navbar">
  <div class="container">
    <a href="<?php echo isLoggedIn() ? 'dashboard.php' : 'index.php'; ?>" class="brand-logo">
      ⚡ Skill<span>bridg</span>
    </a>

    <nav>
      <ul class="nav-links">
        <?php if (isLoggedIn()): ?>
          <li>
            <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
              Dashboard
            </a>
          </li>
          <li>
            <a href="find-skills.php" class="nav-link <?php echo $current_page == 'find-skills.php' ? 'active' : ''; ?>">
              Find Skills
            </a>
          </li>
          <li>
            <a href="skills.php" class="nav-link <?php echo in_array($current_page, ['skills.php', 'add-skill.php', 'edit-skill.php']) ? 'active' : ''; ?>">
              My Skills
            </a>
          </li>
          <li>
            <a href="requests.php" class="nav-link <?php echo in_array($current_page, ['requests.php', 'send-request.php']) ? 'active' : ''; ?>">
              Requests
            </a>
          </li>
          <li>
            <a href="profile.php" class="nav-link <?php echo in_array($current_page, ['profile.php', 'edit-profile.php']) ? 'active' : ''; ?>">
              Profile
            </a>
          </li>
          <li class="user-welcome">
            👋 <?php echo sanitize(getCurrentUserName()); ?>
          </li>
          <li>
            <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
          </li>
        <?php else: ?>
          <li>
            <a href="index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
              Home
            </a>
          </li>
          <li>
            <a href="login.php" class="nav-link <?php echo $current_page == 'login.php' ? 'active' : ''; ?>">
              Login
            </a>
          </li>
          <li>
            <a href="register.php" class="btn btn-primary btn-sm">
              Register
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>
