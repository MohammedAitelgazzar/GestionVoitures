<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Récupérer la liste des voitures disponibles
$stmt = $conn->query("SELECT * FROM cars WHERE status = 'available' ORDER BY created_at DESC");
$cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h2>Voitures disponibles</h2>
        
        <div class="row">
            <?php foreach ($cars as $car): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if ($car['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($car['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h5>
                            <p class="card-text">
                                Année: <?php echo htmlspecialchars($car['year']); ?><br>
                                Prix par jour: <?php echo htmlspecialchars($car['daily_rate']); ?> €
                            </p>
                            <?php if (isLoggedIn()): ?>
                                <a href="reserve.php?car_id=<?php echo $car['id']; ?>" class="btn btn-primary">Réserver</a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">Connectez-vous pour réserver</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
