<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

// Statistiques
$stats = [
    'total_cars' => $conn->query("SELECT COUNT(*) FROM cars")->fetchColumn(),
    'available_cars' => $conn->query("SELECT COUNT(*) FROM cars WHERE status = 'available'")->fetchColumn(),
    'total_reservations' => $conn->query("SELECT COUNT(*) FROM reservations")->fetchColumn(),
    'pending_reservations' => $conn->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn()
];

// Dernières réservations
$stmt = $conn->query("
    SELECT r.*, u.username, c.brand, c.model
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN cars c ON r.car_id = c.id
    ORDER BY r.created_at DESC
    LIMIT 5
");
$recent_reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <h2>Dashboard Administrateur</h2>
        
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Voitures</h5>
                        <p class="card-text display-4"><?php echo $stats['total_cars']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Voitures Disponibles</h5>
                        <p class="card-text display-4"><?php echo $stats['available_cars']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Réservations</h5>
                        <p class="card-text display-4"><?php echo $stats['total_reservations']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Réservations en Attente</h5>
                        <p class="card-text display-4"><?php echo $stats['pending_reservations']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Dernières Réservations</h5>
                        <a href="manage_reservations.php" class="btn btn-primary btn-sm">Voir Toutes</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Voiture</th>
                                        <th>Dates</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_reservations as $reservation): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($reservation['username']); ?></td>
                                            <td><?php echo htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']); ?></td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($reservation['start_date'])); ?>
                                                -
                                                <?php echo date('d/m/Y', strtotime($reservation['end_date'])); ?>
                                            </td>
                                            <td><?php echo number_format($reservation['total_price'], 2); ?> €</td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $reservation['status'] === 'pending' ? 'warning' : 
                                                        ($reservation['status'] === 'confirmed' ? 'success' : 'danger'); 
                                                ?>">
                                                    <?php echo ucfirst($reservation['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($reservation['status'] === 'pending'): ?>
                                                    <a href="update_reservation.php?id=<?php echo $reservation['id']; ?>&action=confirm" 
                                                       class="btn btn-success btn-sm">Confirmer</a>
                                                    <a href="update_reservation.php?id=<?php echo $reservation['id']; ?>&action=cancel" 
                                                       class="btn btn-danger btn-sm">Annuler</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Gestion des Voitures</h5>
                        <div>
                            <a href="manage_cars.php" class="btn btn-primary btn-sm">Gérer</a>
                            <a href="add_car.php" class="btn btn-success btn-sm">Ajouter</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Gestion des Utilisateurs</h5>
                        <a href="manage_users.php" class="btn btn-primary btn-sm">Gérer</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
