<?php
require_once 'config.php';
require_once 'session.php';

// Récupérer l'ID de l'article
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($article_id <= 0) {
    set_flash_message("Article introuvable.", 'error');
    redirect('/php_exam/index.php');
}

// Récupérer les détails de l'article
$stmt = $mysqli->prepare("SELECT a.*, u.username as author_name, u.id as author_id, s.quantity as stock 
                          FROM articles a 
                          LEFT JOIN users u ON a.author_id = u.id 
                          LEFT JOIN stock s ON a.id = s.article_id 
                          WHERE a.id = ?");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash_message("Article introuvable.", 'error');
    redirect('/php_exam/index.php');
}

$article = $result->fetch_assoc();
$stmt->close();

// Traitement de l'ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    require_login();
    
    $user_id = get_current_user_id();
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Vérifier le stock
    if ($article['stock'] < $quantity) {
        set_flash_message("Stock insuffisant.", 'error');
    } else {
        // Vérifier si l'article est déjà dans le panier
        $check_cart = $mysqli->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND article_id = ?");
        $check_cart->bind_param("ii", $user_id, $article_id);
        $check_cart->execute();
        $cart_result = $check_cart->get_result();
        
        if ($cart_result->num_rows > 0) {
            // Article déjà dans le panier, mettre à jour la quantité
            $cart_item = $cart_result->fetch_assoc();
            $new_quantity = $cart_item['quantity'] + $quantity;
            
            $update_cart = $mysqli->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update_cart->bind_param("ii", $new_quantity, $cart_item['id']);
            $update_cart->execute();
            $update_cart->close();
        } else {
            // Ajouter l'article au panier
            $add_cart = $mysqli->prepare("INSERT INTO cart (user_id, article_id, quantity) VALUES (?, ?, ?)");
            $add_cart->bind_param("iii", $user_id, $article_id, $quantity);
            $add_cart->execute();
            $add_cart->close();
        }
        $check_cart->close();
        
        set_flash_message("Article ajouté au panier !", 'success');
        redirect('/php_exam/cart.php');
    }
}

$page_title = $article['name'];
include 'header.php';
?>

<div class="detail-page">
    <div class="detail-container">
        <div class="detail-image">
            <img src="/php_exam/assets/images/<?php echo htmlspecialchars($article['image_link']); ?>" 
                 alt="<?php echo htmlspecialchars($article['name']); ?>">
        </div>
        
        <div class="detail-info">
            <h1><?php echo htmlspecialchars($article['name']); ?></h1>
            
            <p class="detail-price"><?php echo number_format($article['price'], 2); ?> €</p>
            
            <div class="detail-meta">
                <p><strong>Vendeur:</strong> 
                    <a href="/php_exam/account.php?user_id=<?php echo $article['author_id']; ?>">
                        <?php echo htmlspecialchars($article['author_name']); ?>
                    </a>
                </p>
                <p><strong>Publié le:</strong> <?php echo date('d/m/Y', strtotime($article['publication_date'])); ?></p>
                
                <?php if (isset($article['stock'])): ?>
                    <p><strong>Stock:</strong> 
                        <?php if ($article['stock'] > 0): ?>
                            <span class="stock-available"><?php echo $article['stock']; ?> disponible(s)</span>
                        <?php else: ?>
                            <span class="stock-unavailable">Rupture de stock</span>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="detail-description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
            </div>
            
            <?php if (is_logged_in()): ?>
                <?php if ($article['stock'] > 0): ?>
                    <form method="POST" action="" class="add-to-cart-form">
                        <div class="form-group">
                            <label for="quantity">Quantité:</label>
                            <input type="number" id="quantity" name="quantity" value="1" 
                                   min="1" max="<?php echo $article['stock']; ?>" required>
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                            Ajouter au panier
                        </button>
                    </form>
                <?php else: ?>
                    <p class="stock-message">Cet article n'est plus en stock.</p>
                <?php endif; ?>
                
                <?php if (get_current_user_id() == $article['author_id'] || is_admin()): ?>
                    <div class="article-actions">
                        <a href="/php_exam/edit.php?id=<?php echo $article['id']; ?>" class="btn btn-secondary">
                            Modifier l'article
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="login-message">
                    <a href="/php_exam/login.php">Connectez-vous</a> pour ajouter cet article à votre panier.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>