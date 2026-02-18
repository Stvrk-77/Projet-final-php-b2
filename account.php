<?php
require_once 'config.php';
require_once 'session.php';

require_login();

$current_user_id = get_current_user_id();
$view_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $current_user_id;

// Récupérer les informations de l'utilisateur à afficher
$user_stmt = $mysqli->prepare("SELECT id, username, email, balance, profile_picture, role FROM users WHERE id = ?");
$user_stmt->bind_param("i", $view_user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    set_flash_message("Utilisateur introuvable.", 'error');
    redirect('/php_exam/index.php');
}

$user = $user_result->fetch_assoc();
$user_stmt->close();

$is_own_account = ($view_user_id === $current_user_id);

// Traitement des modifications du compte
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own_account) {
    if (isset($_POST['update_info'])) {
        $new_email = secure_input($_POST['email'] ?? '');
        
        if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide.";
        }
        
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        if (empty($errors) && $new_email !== $user['email']) {
            $check_email = $mysqli->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_email->bind_param("si", $new_email, $current_user_id);
            $check_email->execute();
            if ($check_email->get_result()->num_rows > 0) {
                $errors[] = "Cet email est déjà utilisé.";
            }
            $check_email->close();
        }
        
        if (empty($errors)) {
            $update_stmt = $mysqli->prepare("UPDATE users SET email = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_email, $current_user_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            set_flash_message("Informations mises à jour.", 'success');
            redirect('/php_exam/account.php');
        }
    }
    
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Vérifier le mot de passe actuel
        $pass_stmt = $mysqli->prepare("SELECT password FROM users WHERE id = ?");
        $pass_stmt->bind_param("i", $current_user_id);
        $pass_stmt->execute();
        $pass_result = $pass_stmt->get_result();
        $pass_data = $pass_result->fetch_assoc();
        $pass_stmt->close();
        
        if (!password_verify($current_password, $pass_data['password'])) {
            $errors[] = "Mot de passe actuel incorrect.";
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_pass = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_pass->bind_param("si", $hashed_password, $current_user_id);
            $update_pass->execute();
            $update_pass->close();
            
            set_flash_message("Mot de passe modifié avec succès.", 'success');
            redirect('/php_exam/account.php');
        }
    }
    
    if (isset($_POST['add_balance'])) {
        $amount = floatval($_POST['amount'] ?? 0);
        
        if ($amount <= 0) {
            $errors[] = "Le montant doit être supérieur à 0.";
        }
        
        if (empty($errors)) {
            $new_balance = $user['balance'] + $amount;
            $balance_stmt = $mysqli->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $balance_stmt->bind_param("di", $new_balance, $current_user_id);
            $balance_stmt->execute();
            $balance_stmt->close();
            
            set_flash_message("Solde rechargé de " . number_format($amount, 2) . " €", 'success');
            redirect('/php_exam/account.php');
        }
    }
}

// Récupérer les articles publiés par cet utilisateur
$articles_stmt = $mysqli->prepare("SELECT a.*, s.quantity as stock FROM articles a LEFT JOIN stock s ON a.id = s.article_id WHERE a.author_id = ? ORDER BY a.publication_date DESC");
$articles_stmt->bind_param("i", $view_user_id);
$articles_stmt->execute();
$articles_result = $articles_stmt->get_result();

// Récupérer les factures (uniquement pour son propre compte)
$invoices = [];
if ($is_own_account) {
    $invoices_stmt = $mysqli->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY transaction_date DESC");
    $invoices_stmt->bind_param("i", $current_user_id);
    $invoices_stmt->execute();
    $invoices_result = $invoices_stmt->get_result();
    while ($invoice = $invoices_result->fetch_assoc()) {
        $invoices[] = $invoice;
    }
    $invoices_stmt->close();
}

$page_title = $is_own_account ? "Mon Compte" : "Profil de " . $user['username'];
include 'header.php';
?>

<div class="account-page">
    <h1><?php echo $is_own_account ? 'Mon Compte' : 'Profil de ' . htmlspecialchars($user['username']); ?></h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="account-info">
        <h2>Informations du compte</h2>
        <p><strong>Nom d'utilisateur:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <?php if ($is_own_account): ?>
            <p><strong>Solde:</strong> <?php echo number_format($user['balance'], 2); ?> €</p>
            <p><strong>Rôle:</strong> <?php echo $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'; ?></p>
        <?php endif; ?>
    </div>
    
    <?php if ($is_own_account): ?>
        <div class="account-actions">
            <h2>Modifier mes informations</h2>
            
            <form method="POST" action="" class="account-form">
                <h3>Changer l'email</h3>
                <div class="form-group">
                    <label for="email">Nouvel email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <button type="submit" name="update_info" class="btn btn-primary">Mettre à jour l'email</button>
            </form>
            
            <form method="POST" action="" class="account-form">
                <h3>Changer le mot de passe</h3>
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-primary">Changer le mot de passe</button>
            </form>
            
            <form method="POST" action="" class="account-form">
                <h3>Recharger mon solde</h3>
                <div class="form-group">
                    <label for="amount">Montant (€)</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                </div>
                <button type="submit" name="add_balance" class="btn btn-primary">Ajouter au solde</button>
            </form>
        </div>
    <?php endif; ?>
    
    <div class="user-articles">
        <h2>Articles <?php echo $is_own_account ? 'que j\'ai publiés' : 'publiés par cet utilisateur'; ?></h2>
        
        <?php if ($articles_result->num_rows > 0): ?>
            <div class="articles-grid">
                <?php while ($article = $articles_result->fetch_assoc()): ?>
                    <div class="article-card">
                        <div class="article-image">
                            <img src="/php_exam/assets/images/<?php echo htmlspecialchars($article['image_link']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['name']); ?>">
                        </div>
                        <div class="article-content">
                            <h3><?php echo htmlspecialchars($article['name']); ?></h3>
                            <p class="article-price"><?php echo number_format($article['price'], 2); ?> €</p>
                            <p class="article-stock">Stock: <?php echo $article['stock'] ?? 0; ?></p>
                            <a href="/php_exam/detail.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">Voir</a>
                            <?php if ($is_own_account): ?>
                                <a href="/php_exam/edit.php?id=<?php echo $article['id']; ?>" class="btn btn-secondary">Modifier</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Aucun article publié.</p>
        <?php endif; ?>
    </div>
    
    <?php if ($is_own_account && count($invoices) > 0): ?>
        <div class="user-invoices">
            <h2>Mes factures</h2>
            <table class="invoices-table">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Adresse</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td>#<?php echo $invoice['id']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($invoice['transaction_date'])); ?></td>
                            <td><?php echo number_format($invoice['amount'], 2); ?> €</td>
                            <td><?php echo htmlspecialchars($invoice['billing_address'] . ', ' . $invoice['billing_zipcode'] . ' ' . $invoice['billing_city']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>