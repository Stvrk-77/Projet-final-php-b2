<?php
require_once 'config.php';
require_once 'session.php';

require_login();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = secure_input($_POST['name'] ?? '');
    $description = secure_input($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock_quantity = intval($_POST['stock'] ?? 0);
    $author_id = get_current_user_id();
    
    // Gestion de l'upload d'image
    $image_link = 'default-product.jpg';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2 MB
        
        if (in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            $upload_dir = __DIR__ . '/assets/images/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid('product_') . '.' . $extension;
            $upload_path = $upload_dir . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_link = $image_name;
            }
        } else {
            $errors[] = "Format d'image invalide ou fichier trop volumineux (max 2MB).";
        }
    }
    
    // Validation
    if (empty($name)) {
        $errors[] = "Le nom de l'article est requis.";
    }
    
    if (empty($description)) {
        $errors[] = "La description est requise.";
    }
    
    if ($price <= 0) {
        $errors[] = "Le prix doit être supérieur à 0.";
    }
    
    if ($stock_quantity < 0) {
        $errors[] = "Le stock ne peut pas être négatif.";
    }
    
    // Si pas d'erreurs, créer l'article
    if (empty($errors)) {
        $stmt = $mysqli->prepare("INSERT INTO articles (name, description, price, author_id, image_link) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $name, $description, $price, $author_id, $image_link);
        
        if ($stmt->execute()) {
            $article_id = $mysqli->insert_id;
            
            // Ajouter le stock
            $stock_stmt = $mysqli->prepare("INSERT INTO stock (article_id, quantity) VALUES (?, ?)");
            $stock_stmt->bind_param("ii", $article_id, $stock_quantity);
            $stock_stmt->execute();
            $stock_stmt->close();
            
            set_flash_message("Article créé avec succès !", 'success');
            redirect('/php_exam/detail.php?id=' . $article_id);
        } else {
            $errors[] = "Erreur lors de la création de l'article.";
        }
        $stmt->close();
    }
}

$page_title = "Vendre un article";
include 'header.php';
?>

<div class="sell-page">
    <h1>Mettre un article en vente</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data" class="sell-form">
        <div class="form-group">
            <label for="name">Nom de l'article *</label>
            <input type="text" id="name" name="name" 
                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">Prix (€) *</label>
            <input type="number" id="price" name="price" step="0.01" min="0.01" 
                   value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="stock">Quantité en stock *</label>
            <input type="number" id="stock" name="stock" min="0" 
                   value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="image">Image du produit</label>
            <input type="file" id="image" name="image" accept="image/*">
            <small>Format accepté: JPG, PNG, GIF (max 2MB)</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Publier l'article</button>
    </form>
</div>

<?php include 'footer.php'; ?>