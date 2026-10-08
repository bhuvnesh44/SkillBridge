<?php
/**
 * Skillbridg — Common Header Component
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? sanitize($page_title) . ' — Skillbridg' : 'Skillbridg — Peer Learning Portal'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include_once __DIR__ . '/navbar.php'; ?>

<main class="main-content">
    <div class="container">
        <?php displayFlashMessages(); ?>
