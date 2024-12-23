<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

// Filtres
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Construction de la requête SQL avec les filtres
$sql = "
    SELECT r.*, u.username, c.brand, c.model, c.registration_number
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN cars c ON r.car_id = c.id
    WHERE 1=1
";

$params = [];

if ($status_filter) {
    $sql .= " AND r.status = ?";
    $params[] = $status_filter;
}

if ($date_filter) {
    $sql .= " AND DATE(r.start_date) = ?";
    $params[] = $date_filter;
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <h2>Gestion des Réservations</h2>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['flash_message'];
                unset($_SESSION['flash_message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>
                                En attente
                            </option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>
                                Confirmée
                            </option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>
                                Annulée
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="date" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?php echo $date_filter; ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filtrer</button>
                        <a href="manage_reservations.php" class="btn btn-secondary">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Voiture</th>
                        <th>Dates</th>
                        <th>Prix Total</th>
                        <th>Statut</th>
                        <th>Date de création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['username']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']); ?>
                                <br>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($reservation['registration_number']); ?>
                                </small>
                            </td>
                            <td>
                                Du <?php echo date('d/m/Y', strtotime($reservation['start_date'])); ?>
                                <br>
                                Au <?php echo date('d/m/Y', strtotime($reservation['end_date'])); ?>
                            </td>
                            <td><?php echo number_format($reservation['total_price'], 2); ?> €</td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $reservation['status'] === 'pending' ? 'warning' : 
                                        ($reservation['status'] === 'confirmed' ? 'success' : 'danger'); 
                                ?>">
                                    <?php 
                                    echo $reservation['status'] === 'pending' ? 'En attente' : 
                                        ($reservation['status'] === 'confirmed' ? 'Confirmée' : 'Annulée'); 
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?></td>
                            <td>
                                <?php if ($reservation['status'] === 'pending'): ?>
                                    <div class="btn-group">
                                        <a href="update_reservation.php?id=<?php echo $reservation['id']; ?>&action=confirm" 
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Confirmer cette réservation ?')">
                                            Confirmer
                                        </a>
                                        <a href="update_reservation.php?id=<?php echo $reservation['id']; ?>&action=cancel" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Annuler cette réservation ?')">
                                            Annuler
                                        </a>
                                    </div>
                                <?php endif; ?>
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
