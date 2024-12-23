<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

// Récupérer les réservations de l'utilisateur
$stmt = $conn->prepare("
    SELECT r.*, c.brand, c.model, c.year, c.registration_number
    FROM reservations r
    JOIN cars c ON r.car_id = c.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h2>Mes Réservations</h2>
        
        <?php if (empty($reservations)): ?>
            <div class="alert alert-info">Vous n'avez pas encore de réservations.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Voiture</th>
                            <th>Dates</th>
                            <th>Prix Total</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($reservation['brand'] . ' ' . $reservation['model'] . ' (' . $reservation['year'] . ')'); ?>
                                    <br>
                                    <small class="text-muted">Immatriculation: <?php echo htmlspecialchars($reservation['registration_number']); ?></small>
                                </td>
                                <td>
                                    Du <?php echo date('d/m/Y', strtotime($reservation['start_date'])); ?>
                                    <br>
                                    Au <?php echo date('d/m/Y', strtotime($reservation['end_date'])); ?>
                                </td>
                                <td><?php echo number_format($reservation['total_price'], 2); ?> €</td>
                                <td>
                                    <?php
                                    $status_class = [
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'cancelled' => 'danger'
                                    ][$reservation['status']];
                                    
                                    $status_text = [
                                        'pending' => 'En attente',
                                        'confirmed' => 'Confirmée',
                                        'cancelled' => 'Annulée'
                                    ][$reservation['status']];
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <form method="POST" action="cancel_reservation.php" style="display: inline;">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                Annuler
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
