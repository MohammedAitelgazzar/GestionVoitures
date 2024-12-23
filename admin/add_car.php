<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $registration_number = $_POST['registration_number'];
    $daily_rate = $_POST['daily_rate'];
    
    // Validation de base
    if (empty($brand) || empty($model) || empty($year) || empty($registration_number) || empty($daily_rate)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        try {
            // Vérifier si l'immatriculation existe déjà
            $stmt = $conn->prepare("SELECT id FROM cars WHERE registration_number = ?");
            $stmt->execute([$registration_number]);
            if ($stmt->rowCount() > 0) {
                $error = 'Cette immatriculation existe déjà.';
            } else {
                // Upload de l'image si présente
                $image_url = null;
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
                        $new_filename = uniqid() . '.' . $file_extension;
                        $destination = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                            $image_url = 'uploads/cars/' . $new_filename;
                        }
                    }
                }
                
                if (empty($error)) {
                    $stmt = $conn->prepare("
                        INSERT INTO cars (brand, model, year, registration_number, daily_rate, image_url)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$brand, $model, $year, $registration_number, $daily_rate, $image_url]);
                    
                    $success = 'La voiture a été ajoutée avec succès.';
                    // Redirection après 2 secondes
                    header("refresh:2;url=manage_cars.php");
                }
            }
        } catch(PDOException $e) {
            $error = 'Une erreur est survenue lors de l\'ajout de la voiture.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Voiture - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ajouter une Voiture</h3>
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
                                <input type="text" class="form-control" id="brand" name="brand" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="model" class="form-label">Modèle</label>
                                <input type="text" class="form-control" id="model" name="model" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="year" class="form-label">Année</label>
                                <input type="number" class="form-control" id="year" name="year" 
                                       min="1900" max="<?php echo date('Y') + 1; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="registration_number" class="form-label">Numéro d'immatriculation</label>
                                <input type="text" class="form-control" id="registration_number" 
                                       name="registration_number" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="daily_rate" class="form-label">Prix par jour (€)</label>
                                <input type="number" class="form-control" id="daily_rate" name="daily_rate" 
                                       min="0" step="0.01" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Image de la voiture</label>
                                <input type="file" class="form-control" id="image" name="image" 
                                       accept="image/jpeg,image/png">
                                <small class="text-muted">Formats acceptés: JPG, JPEG, PNG</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Ajouter la voiture</button>
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
