<?php
/**
 * Fichier de gestion des sessions
 * À inclure dans chaque fichier qui nécessite une authentification
 */

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur est administrateur
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Protéger une page (redirection si non connecté)
 */
function require_login() {
    if (!is_logged_in()) {
        redirect('/php_exam/login.php');
    }
}

/**
 * Protéger une page admin (redirection si non admin)
 */
function require_admin() {
    if (!is_admin()) {
        redirect('/php_exam/index.php');
    }
}

/**
 * Obtenir l'ID de l'utilisateur connecté
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Obtenir le nom d'utilisateur connecté
 */
function get_current_username() {
    return $_SESSION['username'] ?? null;
}

/**
 * Connecter un utilisateur
 */
function login_user($user_id, $username, $email, $role) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
}

/**
 * Déconnecter l'utilisateur
 */
function logout_user() {
    session_unset();
    session_destroy();
    redirect('/php_exam/login.php');
}

/**
 * Afficher un message flash
 */
function set_flash_message($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type; // success, error, warning, info
}

/**
 * Récupérer et supprimer le message flash
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}
?>