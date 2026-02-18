<?php
require_once '../config.php';
require_once '../session.php';

require_admin();

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    $current_admin_id = get_current_user_id();
    
    // Empêcher l'admin de se supprimer lui-même
    if ($user_id === $current_admin_id) {
        set_flash_message("Vous ne pouvez pas supprimer votre propre compte.", 'error');
    } else {
        $delete_stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
        $delete_stmt->bind_param("i", $user_id);
        
        if ($delete_stmt->execute()) {
            set_flash_message("Utilisateur supprimé avec succès.", 'success');
        } else {
            set_flash_message("Erreur lors de la suppression.", 'error');
        }
        $delete_stmt->close();
    }
    redirect('/php_exam/admin/users.php');
}

// Traitement de la modification du rôle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    
    $role_stmt = $mysqli->prepare("UPDATE users SET role = ? WHERE id = ?");
    $role_stmt->bind_param("si", $new_role, $user_id);
    
    if ($role_stmt->execute()) {
        set_flash_message("Rôle modifié avec succès.", 'success');
    } else {
        set_flash_message("Erreur lors de la modification.", 'error');
    }
    $role_stmt->close();
    redirect('/php_exam/admin/users.php');
}

// Récupérer tous les utilisateurs
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM articles WHERE author_id = u.id) as articles_count 
          FROM users u 
          ORDER BY u.id ASC";
$result = $mysqli->query($query);

$page_title = "Gestion des utilisateurs";
include '../header.php';
?>

<div class="admin-page">
    <h1>Gestion des utilisateurs</h1>
    
    <a href="/php_exam/admin/index.php" class="btn btn-secondary">Retour au tableau de bord</a>
    
    <?php if ($result->num_rows > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Solde</th>
                    <th>Rôle</th>
                    <th>Articles</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo number_format($user['balance'], 2); ?> €</td>
                        <td><?php echo $user['role']; ?></td>
                        <td><?php echo $user['articles_count']; ?></td>
                        <td>
                            <a href="/php_exam/account.php?user_id=<?php echo $user['id']; ?>" class="btn btn-sm">Voir</a>
                            
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role" class="small-select">
                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <button type="submit" name="change_role" class="btn btn-sm">Changer rôle</button>
                            </form>
                            
                            <?php if ($user['id'] !== get_current_user_id()): ?>
                                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr ?');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun utilisateur à gérer.</p>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>