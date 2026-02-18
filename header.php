<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'E-Commerce PHP'; ?></title>
    <link rel="stylesheet" href="/php_exam/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="/php_exam/index.php" class="logo">LeBonDuCoin</a>
            <ul class="nav-menu">
                <li><a href="/php_exam/index.php">Accueil</a></li>
                
                <?php if (is_logged_in()): ?>
                    <li><a href="/php_exam/sell.php">Vendre</a></li>
                    <li><a href="/php_exam/cart.php">Panier</a></li>
                    <li><a href="/php_exam/account.php">Mon Compte</a></li>
                    
                    <?php if (is_admin()): ?>
                        <li><a href="/php_exam/admin/index.php">Admin</a></li>
                    <?php endif; ?>
                    
                    <li><a href="/php_exam/logout.php">Déconnexion (<?php echo htmlspecialchars(get_current_username()); ?>)</a></li>
                <?php else: ?>
                    <li><a href="/php_exam/login.php">Connexion</a></li>
                    <li><a href="/php_exam/register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <?php
    // Afficher les messages flash
    $flash = get_flash_message();
    if ($flash):
    ?>
        <div class="flash-message flash-<?php echo $flash['type']; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <main class="container">