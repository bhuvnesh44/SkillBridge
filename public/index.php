<?php
/**
 * Skillbridg — Public Homepage
 */
$page_title = "Home — Peer Learning & Skill Exchange";
require_once __DIR__ . '/../includes/header.php';
?>

<section class="hero">
    <h1 class="hero-title">Share what you know. <span>Learn what you need.</span></h1>
    <p class="hero-subtitle">
        Skillbridg connects college students for peer-to-peer learning. List skills you can teach, discover skills you want to learn, and exchange knowledge directly with your college peers.
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <?php if (isLoggedIn()): ?>
            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <a href="find-skills.php" class="btn btn-secondary">Find Skills</a>
        <?php else: ?>
            <a href="register.php" class="btn btn-primary">Get Started — Register Free</a>
            <a href="login.php" class="btn btn-secondary">Student Login</a>
        <?php endif; ?>
    </div>
</section>

<section style="margin-top: 4rem;">
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <h2 style="font-size: 1.8rem; font-weight: 700; color: var(--text-main);">How Skillbridg Works</h2>
        <p style="color: var(--text-muted); font-size: 1.05rem;">A simple 4-step peer learning workflow designed for college students.</p>
    </div>

    <div class="grid-4">
        <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
            <div style="font-size: 2.2rem; margin-bottom: 0.75rem;">👤</div>
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">1. Create Profile</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Sign up with your student email and set up your profile details.</p>
        </div>

        <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
            <div style="font-size: 2.2rem; margin-bottom: 0.75rem;">📚</div>
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">2. List Your Skills</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Specify skills you can <strong>Teach</strong> and skills you want to <strong>Learn</strong>.</p>
        </div>

        <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
            <div style="font-size: 2.2rem; margin-bottom: 0.75rem;">🔍</div>
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">3. Discover & Request</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Search for skills, find suitable peers, and send learning requests.</p>
        </div>

        <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
            <div style="font-size: 2.2rem; margin-bottom: 0.75rem;">🤝</div>
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">4. Learn & Feedback</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Complete peer learning sessions and exchange valuable feedback.</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
