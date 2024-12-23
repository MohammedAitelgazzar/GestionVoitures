<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: manage_cars.php');
    exit();
}

$car_id = $_GET['id'];

// Récupérer les informations de la voiture
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    header('Location: manage_cars.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $registration_number = $_POST['registration_number'];
    $daily_rate = $_POST['daily_rate'];
    
    if (empty($brand) || empty($model) || empty($year) || empty($registration_number) || empty($daily_rate)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        try {
            // Vérifier si l'immatriculation existe déjà (sauf pour cette voiture)
            $stmt = $conn->prepare("SELECT id FROM cars WHERE registration_number = ? AND id != ?");
            $stmt->execute([$registration_number, $car_id]);
            if ($stmt->rowCount() > 0) {
                $error = 'Cette immatriculation existe déjà.';
            } else {
                $image_url = $car['image_url'];
                
                // Traitement de la nouvelle image si présente
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/cars/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png'];
                    
                    if (!in_array($file_extension, $allowed_extensions)) {
                        $error = 'Format d\'image non autorisé. Utilisez JPG, JPEG ou PNG.';
                    } else {
                        // Supprimer l'ancienne image si elle existe
                        if ($image_url && file_exists('../' . $image_url)) {
                            unlink('../' . $image_url);
                        }
                        
                        $new_filename = uniqid() . '.' . $file_extension;
                        $destination = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                            $image_url = 'uploads/cars/' . $new_filename;
                        }
                    }
                }
                
                if (empty($error)) {
                    $stmt = $conn->prepare("
                        UPDATE cars 
                        SET brand = ?, model = ?, year = ?, registration_number = ?, 
                            daily_rate = ?, image_url = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $brand, $model, $year, $registration_number, 
                        $daily_rate, $image_url, $car_id
                    ]);
                    
                    $success = 'La voiture a été mise à jour avec succès.';
                    // Redirection après 2 secondes
                    header("refresh:2;url=manage_cars.php");
                }
            }
        } catch(PDOException $e) {
            $error = 'Une erreur est survenue lors de la mise à jour de la voiture.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Voiture - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Modifier une Voiture</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="brand" class="form-label">Marque</label>
                                <input type="text" class="form-control" id="brand" name="brand" 
                                       value="<?php echo htmlspecialchars($car['brand']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="model" class="form-label">Modèle</label>
                                <input type="text" class="form-control" id="model" name="model" 
                                       value="<?php echo htmlspecialchars($car['model']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="year" class="form-label">Année</label>
                                <input type="number" class="form-control" id="year" name="year" 
                                       value="<?php echo htmlspecialchars($car['year']); ?>"
                                       min="1900" max="<?php echo date('Y') + 1; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="registration_number" class="form-label">Numéro d'immatriculation</label>
                                <input type="text" class="form-control" id="registration_number" 
                                       name="registration_number" 
                                       value="<?php echo htmlspecialchars($car['registration_number']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="daily_rate" class="form-label">Prix par jour (€)</label>
                                <input type="number" class="form-control" id="daily_rate" name="daily_rate" 
                                       value="<?php echo htmlspecialchars($car['daily_rate']); ?>"
                                       min="0" step="0.01" required>
                            </div>
                            
                            <?php if ($car['image_url']): ?>
                                <div class="mb-3">
                                    <label class="form-label">Image actuelle</label>
                                    <div>
                                        <img src="<?php echo '../' . htmlspecialchars($car['image_url']); ?>" 
                                             alt="Image actuelle" style="max-width: 200px;">
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Nouvelle image (optionnel)</label>
                                <input type="file" class="form-control" id="image" name="image" 
                                       accept="image/jpeg,image/png">
                                <small class="text-muted">Formats acceptés: JPG, JPEG, PNG</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                <a href="manage_cars.php" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
