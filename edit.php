<?php
require_once 'config.php';
require_once 'session.php';

require_login();

$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = get_current_user_id();

if ($article_id <= 0) {
    set_flash_message("Article introuvable.", 'error');
    redirect('/php_exam/index.php');
}

// Récupérer l'article
$stmt = $mysqli->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->bind_param("i", $article_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    set_flash_message("Article introuvable.", 'error');
    redirect('/php_exam/index.php');
}

$article = $result->fetch_assoc();
$stmt->close();

// Vérifier les permissions
if ($article['author_id'] != $user_id && !is_admin()) {
    set_flash_message("Vous n'avez pas la permission de modifier cet article.", 'error');
    redirect('/php_exam/detail.php?id=' . $article_id);
}

// Récupérer le stock
$stock_stmt = $mysqli->prepare("SELECT quantity FROM stock WHERE article_id = ?");
$stock_stmt->bind_param("i", $article_id);
$stock_stmt->execute();
$stock_result = $stock_stmt->get_result();
$stock_data = $stock_result->fetch_assoc();
$current_stock = $stock_data['quantity'] ?? 0;
$stock_stmt->close();

$errors = [];

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article'])) {
    $delete_stmt = $mysqli->prepare("DELETE FROM articles WHERE id = ?");
    $delete_stmt->bind_param("i", $article_id);
    
    if ($delete_stmt->execute()) {
        set_flash_message("Article supprimé avec succès.", 'success');
        redirect('/php_exam/index.php');
    } else {
        $errors[] = "Erreur lors de la suppression.";
    }
    $delete_stmt->close();
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_article'])) {
    $name = secure_input($_POST['name'] ?? '');
    $description = secure_input($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock_quantity = intval($_POST['stock'] ?? 0);
    
    $image_link = $article['image_link'];
    
    // Gestion de l'upload d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $max_size = 2 * 1024 * 1024;
        
        if (in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            $upload_dir = __DIR__ . '/assets/images/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid('product_') . '.' . $extension;
            $upload_path = $upload_dir . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Supprimer l'ancienne image si ce n'est pas l'image par défaut
                if ($article['image_link'] !== 'default-product.jpg') {
                    @unlink($upload_dir . $article['image_link']);
                }
                $image_link = $image_name;
            }
        }
    }
    
    // Validation
    if (empty($name)) {
        $errors[] = "Le nom est requis.";
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
    
    if (empty($errors)) {
        $update_stmt = $mysqli->prepare("UPDATE articles SET name = ?, description = ?, price = ?, image_link = ? WHERE id = ?");
        $update_stmt->bind_param("ssdsi", $name, $description, $price, $image_link, $article_id);
        
        if ($update_stmt->execute()) {
            // Mettre à jour le stock
            $stock_update = $mysqli->prepare("UPDATE stock SET quantity = ? WHERE article_id = ?");
            $stock_update->bind_param("ii", $stock_quantity, $article_id);
            $stock_update->execute();
            $stock_update->close();
            
            set_flash_message("Article modifié avec succès.", 'success');
            redirect('/php_exam/detail.php?id=' . $article_id);
        } else {
            $errors[] = "Erreur lors de la modification.";
        }
        $update_stmt->close();
    }
}

$page_title = "Modifier l'article";
include 'header.php';
?>

<div class="edit-page">
    <h1>Modifier l'article</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data" class="edit-form">
        <div class="form-group">
            <label for="name">Nom de l'article *</label>
            <input type="text" id="name" name="name" 
                   value="<?php echo htmlspecialchars($article['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($article['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">Prix (€) *</label>
            <input type="number" id="price" name="price" step="0.01" min="0.01" 
                   value="<?php echo $article['price']; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="stock">Quantité en stock *</label>
            <input type="number" id="stock" name="stock" min="0" 
                   value="<?php echo $current_stock; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="image">Changer l'image</label>
            <input type="file" id="image" name="image" accept="image/*">
            <small>Image actuelle: <?php echo htmlspecialchars($article['image_link']); ?></small>
        </div>
        
        <div class="form-actions">
            <button type="submit" name="update_article" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="/php_exam/detail.php?id=<?php echo $article_id; ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
    
    <div class="danger-zone">
        <h2>Zone dangereuse</h2>
        <form method="POST" action="" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">
            <button type="submit" name="delete_article" class="btn btn-danger">Supprimer l'article</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>