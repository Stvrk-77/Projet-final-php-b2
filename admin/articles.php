<?php
require_once '../config.php';
require_once '../session.php';

require_admin();

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article'])) {
    $article_id = intval($_POST['article_id']);
    
    $delete_stmt = $mysqli->prepare("DELETE FROM articles WHERE id = ?");
    $delete_stmt->bind_param("i", $article_id);
    
    if ($delete_stmt->execute()) {
        set_flash_message("Article supprimé avec succès.", 'success');
    } else {
        set_flash_message("Erreur lors de la suppression.", 'error');
    }
    $delete_stmt->close();
    redirect('/php_exam/admin/articles.php');
}

// Récupérer tous les articles
$query = "SELECT a.*, u.username as author_name, s.quantity as stock 
          FROM articles a 
          LEFT JOIN users u ON a.author_id = u.id 
          LEFT JOIN stock s ON a.id = s.article_id 
          ORDER BY a.publication_date DESC";
$result = $mysqli->query($query);

$page_title = "Gestion des articles";
include '../header.php';
?>

<div class="admin-page">
    <h1>Gestion des articles</h1>
    
    <a href="/php_exam/admin/index.php" class="btn btn-secondary">Retour au tableau de bord</a>
    
    <?php if ($result->num_rows > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($article = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $article['id']; ?></td>
                        <td><?php echo htmlspecialchars($article['name']); ?></td>
                        <td><?php echo number_format($article['price'], 2); ?> €</td>
                        <td><?php echo $article['stock'] ?? 0; ?></td>
                        <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($article['publication_date'])); ?></td>
                        <td>
                            <a href="/php_exam/detail.php?id=<?php echo $article['id']; ?>" class="btn btn-sm">Voir</a>
                            <a href="/php_exam/edit.php?id=<?php echo $article['id']; ?>" class="btn btn-sm">Modifier</a>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr ?');">
                                <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                <button type="submit" name="delete_article" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun article à gérer.</p>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>