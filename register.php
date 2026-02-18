<?php
require_once 'config.php';
require_once 'session.php';

// Si déjà connecté, rediriger vers l'accueil
if (is_logged_in()) {
    redirect('/php_exam/index.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données
    $username = secure_input($_POST['username'] ?? '');
    $email = secure_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username)) {
        $errors[] = "Le nom d'utilisateur est requis.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
    }

    if (empty($email)) {
        $errors[] = "L'email est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // Vérifier si username existe déjà
    if (empty($errors)) {
        $check_username = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        $check_username->bind_param("s", $username);
        $check_username->execute();
        if ($check_username->get_result()->num_rows > 0) {
            $errors[] = "Ce nom d'utilisateur est déjà utilisé.";
        }
        $check_username->close();
    }

    // Vérifier si email existe déjà
    if (empty($errors)) {
        $check_email = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            $errors[] = "Cet email est déjà utilisé.";
        }
        $check_email->close();
    }

    // Si pas d'erreurs, créer l'utilisateur
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password, balance, role) VALUES (?, ?, ?, 100.00, 'user')");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            // Récupérer l'ID de l'utilisateur créé
            $user_id = $mysqli->insert_id;
            
            // Connecter automatiquement l'utilisateur
            login_user($user_id, $username, $email, 'user');
            
            // Rediriger vers l'accueil
            set_flash_message("Bienvenue $username ! Votre compte a été créé avec succès.", 'success');
            redirect('/php_exam/index.php');
        } else {
            $errors[] = "Une erreur est survenue lors de la création du compte.";
        }
        $stmt->close();
    }
}

$page_title = "Inscription";
include 'header.php';
?>

<div class="auth-container">
    <h1>Inscription</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="auth-form">
        <div class="form-group">
            <label for="username">Nom d'utilisateur *</label>
            <input type="text" id="username" name="username" 
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe *</label>
            <input type="password" id="password" name="password" required>
            <small>Au moins 6 caractères</small>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmer le mot de passe *</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </form>

    <p class="auth-link">
        Vous avez déjà un compte ? <a href="/php_exam/login.php">Se connecter</a>
    </p>
</div>

<?php include 'footer.php'; ?>