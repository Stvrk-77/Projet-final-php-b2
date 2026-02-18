<?php
require_once 'config.php';
require_once 'session.php';

require_login();

$user_id = get_current_user_id();
$errors = [];

// Récupérer le panier
$cart_query = "SELECT c.id as cart_id, c.quantity, a.id as article_id, a.name, a.price, s.quantity as stock 
               FROM cart c 
               JOIN articles a ON c.article_id = a.id 
               LEFT JOIN stock s ON a.id = s.article_id 
               WHERE c.user_id = ?";
$cart_stmt = $mysqli->prepare($cart_query);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

$cart_items = [];
$total = 0;

while ($item = $cart_result->fetch_assoc()) {
    // Vérifier le stock
    if ($item['quantity'] > $item['stock']) {
        $errors[] = "Stock insuffisant pour " . $item['name'];
    }
    $cart_items[] = $item;
    $total += $item['price'] * $item['quantity'];
}
$cart_stmt->close();

if (count($cart_items) === 0) {
    set_flash_message("Votre panier est vide.", 'error');
    redirect('/php_exam/cart.php');
}

// Récupérer le solde
$user_stmt = $mysqli->prepare("SELECT balance FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_balance = $user_data['balance'];
$user_stmt->close();

if ($user_balance < $total) {
    $errors[] = "Solde insuffisant.";
}

// Traitement de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $address = secure_input($_POST['address'] ?? '');
    $city = secure_input($_POST['city'] ?? '');
    $zipcode = secure_input($_POST['zipcode'] ?? '');
    
    if (empty($address) || empty($city) || empty($zipcode)) {
        $errors[] = "Veuillez remplir toutes les informations de facturation.";
    }
    
    if (empty($errors)) {
        // Commencer une transaction
        $mysqli->begin_transaction();
        
        try {
            // Créer la facture
            $invoice_stmt = $mysqli->prepare("INSERT INTO invoices (user_id, amount, billing_address, billing_city, billing_zipcode) VALUES (?, ?, ?, ?, ?)");
            $invoice_stmt->bind_param("idsss", $user_id, $total, $address, $city, $zipcode);
            $invoice_stmt->execute();
            $invoice_id = $mysqli->insert_id;
            $invoice_stmt->close();
            
            // Ajouter les articles à la facture et mettre à jour le stock
            foreach ($cart_items as $item) {
                // Ajouter à invoice_items
                $item_stmt = $mysqli->prepare("INSERT INTO invoice_items (invoice_id, article_id, article_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
                $item_stmt->bind_param("iisid", $invoice_id, $item['article_id'], $item['name'], $item['quantity'], $item['price']);
                $item_stmt->execute();
                $item_stmt->close();
                
                // Mettre à jour le stock
                $new_stock = $item['stock'] - $item['quantity'];
                $stock_stmt = $mysqli->prepare("UPDATE stock SET quantity = ? WHERE article_id = ?");
                $stock_stmt->bind_param("ii", $new_stock, $item['article_id']);
                $stock_stmt->execute();
                $stock_stmt->close();
            }
            
            // Déduire le montant du solde
            $new_balance = $user_balance - $total;
            $balance_stmt = $mysqli->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $balance_stmt->bind_param("di", $new_balance, $user_id);
            $balance_stmt->execute();
            $balance_stmt->close();
            
            // Vider le panier
            $clear_cart = $mysqli->prepare("DELETE FROM cart WHERE user_id = ?");
            $clear_cart->bind_param("i", $user_id);
            $clear_cart->execute();
            $clear_cart->close();
            
            // Valider la transaction
            $mysqli->commit();
            
            set_flash_message("Commande validée avec succès ! Facture n°" . $invoice_id, 'success');
            redirect('/php_exam/account.php');
            
        } catch (Exception $e) {
            $mysqli->rollback();
            $errors[] = "Erreur lors de la validation de la commande.";
        }
    }
}

$page_title = "Validation de la commande";
include 'header.php';
?>

<div class="validate-page">
    <h1>Validation de la commande</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
        <a href="/php_exam/cart.php" class="btn btn-secondary">Retour au panier</a>
    <?php else: ?>
        <div class="order-summary">
            <h2>Récapitulatif de votre commande</h2>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Prix unitaire</th>
                        <th>Quantité</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> €</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong><?php echo number_format($total, 2); ?> €</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="billing-form">
            <h2>Informations de facturation</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="address">Adresse *</label>
                    <input type="text" id="address" name="address" 
                           value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="city">Ville *</label>
                    <input type="text" id="city" name="city" 
                           value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="zipcode">Code postal *</label>
                    <input type="text" id="zipcode" name="zipcode" 
                           value="<?php echo htmlspecialchars($_POST['zipcode'] ?? ''); ?>" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Valider la commande</button>
                    <a href="/php_exam/cart.php" class="btn btn-secondary">Retour au panier</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>