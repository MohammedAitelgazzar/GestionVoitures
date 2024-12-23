<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$error = '';
$success = '';

if (!isset($_GET['car_id'])) {
    header('Location: index.php');
    exit();
}

$car_id = $_GET['car_id'];

// Récupérer les informations de la voiture
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ? AND status = 'available'");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Calculer le nombre de jours
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $end->diff($start)->days;
    
    // Calculer le prix total
    $total_price = $days * $car['daily_rate'];
    
    try {
        $conn->beginTransaction();
        
        // Créer la réservation
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, car_id, start_date, end_date, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $car_id, $start_date, $end_date, $total_price]);
        
        // Mettre à jour le statut de la voiture
        $stmt = $conn->prepare("UPDATE cars SET status = 'reserved' WHERE id = ?");
        $stmt->execute([$car_id]);
        
        $conn->commit();
        $success = 'Réservation effectuée avec succès!';
        
    } catch(PDOException $e) {
        $conn->rollBack();
        $error = 'Une erreur est survenue lors de la réservation';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver une voiture - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <h2>Réserver une voiture</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h5>
                            <p class="card-text">
                                Année: <?php echo htmlspecialchars($car['year']); ?><br>
                                Prix par jour: <?php echo htmlspecialchars($car['daily_rate']); ?> €
                            </p>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Réserver</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation des dates
        document.getElementById('end_date').addEventListener('change', function() {
            var startDate = document.getElementById('start_date').value;
            var endDate = this.value;
            
            if (startDate && endDate && startDate > endDate) {
                alert('La date de fin doit être postérieure à la date de début');
                this.value = '';
            }
        });
    </script>
</body>
</html>
