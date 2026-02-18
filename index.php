<?php
require_once 'config.php';
require_once 'session.php';

// Récupérer tous les articles, triés par date de publication 
$query = "SELECT a.*, u.username as author_name, s.quantity as stock 
          FROM articles a 
          LEFT JOIN users u ON a.author_id = u.id 
          LEFT JOIN stock s ON a.id = s.article_id 
          ORDER BY a.publication_date DESC";
$result = $mysqli->query($query);

$page_title = "Accueil - Tous les articles";
include 'header.php';
?>

<div class="home-page">
    <h1>Tous nos articles</h1>
    
    <?php if ($result->num_rows > 0): ?>
        <div class="articles-grid">
            <?php while ($article = $result->fetch_assoc()): ?>
                <div class="article-card">
                    <div class="article-image">
                        <img src="/php_exam/assets/images/<?php echo htmlspecialchars($article['image_link']); ?>" 
                             alt="<?php echo htmlspecialchars($article['name']); ?>">
                    </div>
                    
                    <div class="article-content">
                        <h3><?php echo htmlspecialchars($article['name']); ?></h3>
                        
                        <p class="article-description">
                            <?php 
                            $description = htmlspecialchars($article['description']);
                            echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                            ?>
                        </p>
                        
                        <div class="article-meta">
                            <span class="article-price"><?php echo number_format($article['price'], 2); ?> €</span>
                            <span class="article-author">Par <?php echo htmlspecialchars($article['author_name']); ?></span>
                        </div>
                        
                        <?php if (isset($article['stock'])): ?>
                            <p class="article-stock">
                                <?php if ($article['stock'] > 0): ?>
                                    <span class="stock-available">En stock: <?php echo $article['stock']; ?></span>
                                <?php else: ?>
                                    <span class="stock-unavailable">Rupture de stock</span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                        
                        <a href="/php_exam/detail.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">
                            Voir les détails
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="no-articles">Aucun article disponible pour le moment.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>