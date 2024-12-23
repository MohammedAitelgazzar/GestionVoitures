<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

// Récupérer toutes les voitures
$stmt = $conn->query("SELECT * FROM cars ORDER BY created_at DESC");
$cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Voitures - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Voitures</h2>
            <a href="add_car.php" class="btn btn-success">Ajouter une voiture</a>
        </div>

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
                        <th>Image</th>
                        <th>Marque & Modèle</th>
                        <th>Année</th>
                        <th>Immatriculation</th>
                        <th>Prix/Jour</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cars as $car): ?>
                        <tr>
                            <td>
                                <?php if ($car['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($car['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>"
                                         style="max-width: 100px;">
                                <?php else: ?>
                                    <span class="text-muted">Pas d'image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></td>
                            <td><?php echo htmlspecialchars($car['year']); ?></td>
                            <td><?php echo htmlspecialchars($car['registration_number']); ?></td>
                            <td><?php echo number_format($car['daily_rate'], 2); ?> €</td>
                            <td>
                                <span class="badge bg-<?php echo $car['status'] === 'available' ? 'success' : 'warning'; ?>">
                                    <?php echo $car['status'] === 'available' ? 'Disponible' : 'Réservée'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_car.php?id=<?php echo $car['id']; ?>" 
                                   class="btn btn-primary btn-sm">Modifier</a>
                                <form method="POST" action="delete_car.php" style="display: inline;">
                                    <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette voiture ?')">
                                        Supprimer
                                    </button>
                                </form>
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
