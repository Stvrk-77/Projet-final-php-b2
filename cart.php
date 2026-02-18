<?php
require_once 'config.php';
require_once 'session.php';

require_login();

$user_id = get_current_user_id();

// Traitement des actions sur le panier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $cart_id = intval($_POST['cart_id']);
        $new_quantity = intval($_POST['quantity']);
        
        if ($new_quantity > 0) {
            $stmt = $mysqli->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $new_quantity, $cart_id, $user_id);
            $stmt->execute();
            $stmt->close();
            set_flash_message("Quantité mise à jour.", 'success');
        }
        redirect('/php_exam/cart.php');
    }
    
    if (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['cart_id']);
        
        $stmt = $mysqli->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        $stmt->close();
        
        set_flash_message("Article retiré du panier.", 'success');
        redirect('/php_exam/cart.php');
    }
}

// Récupérer les articles du panier
$query = "SELECT c.id as cart_id, c.quantity, a.id as article_id, a.name, a.price, a.image_link, s.quantity as stock 
          FROM cart c 
          JOIN articles a ON c.article_id = a.id 
          LEFT JOIN stock s ON a.id = s.article_id 
          WHERE c.user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;

while ($item = $result->fetch_assoc()) {
    $cart_items[] = $item;
    $total += $item['price'] * $item['quantity'];
}
$stmt->close();

// Récupérer le solde de l'utilisateur
$user_stmt = $mysqli->prepare("SELECT balance FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_balance = $user_data['balance'];
$user_stmt->close();

$page_title = "Mon Panier";
include 'header.php';
?>

<div class="cart-page">
    <h1>Mon Panier</h1>
    
    <?php if (count($cart_items) > 0): ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="/php_exam/assets/images/<?php echo htmlspecialchars($item['image_link']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        
                        <div class="cart-item-details">
                            <h3>
                                <a href="/php_exam/detail.php?id=<?php echo $item['article_id']; ?>">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </a>
                            </h3>
                            <p class="item-price">Prix unitaire: <?php echo number_format($item['price'], 2); ?> €</p>
                            <p class="item-stock">Stock disponible: <?php echo $item['stock']; ?></p>
                        </div>
                        
                        <div class="cart-item-actions">
                            <form method="POST" action="" class="quantity-form">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <label for="quantity_<?php echo $item['cart_id']; ?>">Quantité:</label>
                                <input type="number" 
                                       id="quantity_<?php echo $item['cart_id']; ?>" 
                                       name="quantity" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       max="<?php echo $item['stock']; ?>">
                                <button type="submit" name="update_quantity" class="btn btn-sm">Mettre à jour</button>
                            </form>
                            
                            <form method="POST" action="" class="remove-form">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <button type="submit" name="remove_item" class="btn btn-danger">Retirer</button>
                            </form>
                        </div>
                        
                        <div class="cart-item-subtotal">
                            <strong><?php echo number_format($item['price'] * $item['quantity'], 2); ?> €</strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h2>Récapitulatif</h2>
                <div class="summary-line">
                    <span>Nombre d'articles:</span>
                    <span><?php echo count($cart_items); ?></span>
                </div>
                <div class="summary-line">
                    <span>Total:</span>
                    <span><strong><?php echo number_format($total, 2); ?> €</strong></span>
                </div>
                <div class="summary-line">
                    <span>Votre solde:</span>
                    <span><?php echo number_format($user_balance, 2); ?> €</span>
                </div>
                
                <?php if ($user_balance >= $total): ?>
                    <a href="/php_exam/cart_validate.php" class="btn btn-primary btn-block">
                        Passer la commande
                    </a>
                <?php else: ?>
                    <p class="insufficient-funds">
                        Solde insuffisant. Il vous manque <?php echo number_format($total - $user_balance, 2); ?> €
                    </p>
                    <a href="/php_exam/account.php" class="btn btn-secondary btn-block">
                        Recharger mon compte
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <p>Votre panier est vide.</p>
            <a href="/php_exam/index.php" class="btn btn-primary">Continuer mes achats</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>