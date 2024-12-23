<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

// Récupérer tous les utilisateurs sauf l'admin connecté
$stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(r.id) as total_reservations,
           SUM(CASE WHEN r.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_reservations
    FROM users u
    LEFT JOIN reservations r ON u.id = r.user_id
    WHERE u.id != ?
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <h2>Gestion des Utilisateurs</h2>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['flash_message'];
                unset($_SESSION['flash_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Réservations</th>
                        <th>Date d'inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                    <?php echo $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'; ?>
                                </span>
                            </td>
                            <td>
                                Total: <?php echo $user['total_reservations']; ?>
                                <br>
                                Confirmées: <?php echo $user['confirmed_reservations']; ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" action="update_user_role.php" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="new_role" value="admin">
                                            <button type="submit" class="btn btn-warning btn-sm"
                                                    onclick="return confirm('Promouvoir cet utilisateur en administrateur ?')">
                                                Promouvoir Admin
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="update_user_role.php" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="new_role" value="user">
                                            <button type="submit" class="btn btn-secondary btn-sm"
                                                    onclick="return confirm('Rétrograder cet administrateur en utilisateur ?')">
                                                Rétrograder
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="view_user_reservations.php?user_id=<?php echo $user['id']; ?>" 
                                       class="btn btn-info btn-sm">
                                        Voir Réservations
                                    </a>
                                    
                                    <?php if ($user['total_reservations'] == 0): ?>
                                        <form method="POST" action="delete_user.php" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                Supprimer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
