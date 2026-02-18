<?php
require_once '../config.php';
require_once '../session.php';

require_admin();

$page_title = "Administration";
include '../header.php';
?>

<div class="admin-page">
    <h1>Tableau de bord administrateur</h1>
    
    <div class="admin-menu">
        <a href="/php_exam/admin/articles.php" class="admin-card">
            <h2>Gérer les articles</h2>
            <p>Modifier ou supprimer tous les articles</p>
        </a>
        
        <a href="/php_exam/admin/users.php" class="admin-card">
            <h2>Gérer les utilisateurs</h2>
            <p>Modifier ou supprimer les utilisateurs</p>
        </a>
    </div>
    
    <div class="admin-stats">
        <h2>Statistiques</h2>
        
        <?php
        // Compter les utilisateurs
        $users_count = $mysqli->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        
        // Compter les articles
        $articles_count = $mysqli->query("SELECT COUNT(*) as count FROM articles")->fetch_assoc()['count'];
        
        // Compter les factures
        $invoices_count = $mysqli->query("SELECT COUNT(*) as count FROM invoices")->fetch_assoc()['count'];
        
        // Calculer le chiffre d'affaires total
        $total_revenue = $mysqli->query("SELECT SUM(amount) as total FROM invoices")->fetch_assoc()['total'] ?? 0;
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $users_count; ?></h3>
                <p>Utilisateurs</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $articles_count; ?></h3>
                <p>Articles</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $invoices_count; ?></h3>
                <p>Commandes</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($total_revenue, 2); ?> €</h3>
                <p>Chiffre d'affaires</p>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>