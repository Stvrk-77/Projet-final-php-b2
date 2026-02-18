<?php
require_once 'config.php';
require_once 'session.php';

// Si déjà connecté, rediriger vers l'accueil
if (is_logged_in()) {
    redirect('/php_exam/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = secure_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation basique
    if (empty($email) || empty($password)) {
        $errors[] = "Veuillez remplir tous les champs.";
    } else {
        // Rechercher l'utilisateur
        $stmt = $mysqli->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Vérifier le mot de passe
            if (password_verify($password, $user['password'])) {
                // Connexion réussie
                login_user($user['id'], $user['username'], $user['email'], $user['role']);
                
                set_flash_message("Bienvenue " . $user['username'] . " !", 'success');
                redirect('/php_exam/index.php');
            } else {
                $errors[] = "Email ou mot de passe incorrect.";
            }
        } else {
            $errors[] = "Email ou mot de passe incorrect.";
        }
        $stmt->close();
    }
}

$page_title = "Connexion";
include 'header.php';
?>

<div class="auth-container">
    <h1>Connexion</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="auth-form">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>

    <p class="auth-link">
        Pas encore de compte ? <a href="/php_exam/register.php">S'inscrire</a>
    </p>
</div>

<?php include 'footer.php'; ?>