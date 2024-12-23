<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    $car_id = $_POST['car_id'];
    
    try {
        // Vérifier si la voiture existe et récupérer son image
        $stmt = $conn->prepare("SELECT image_url FROM cars WHERE id = ?");
        $stmt->execute([$car_id]);
        $car = $stmt->fetch();
        
        if ($car) {
            // Supprimer l'image si elle existe
            if ($car['image_url'] && file_exists('../' . $car['image_url'])) {
                unlink('../' . $car['image_url']);
            }
            
            // Vérifier s'il y a des réservations pour cette voiture
            $stmt = $conn->prepare("SELECT id FROM reservations WHERE car_id = ?");
            $stmt->execute([$car_id]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['flash_error'] = 'Impossible de supprimer cette voiture car elle a des réservations associées.';
            } else {
                // Supprimer la voiture
                $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
                $stmt->execute([$car_id]);
                
                $_SESSION['flash_message'] = 'La voiture a été supprimée avec succès.';
            }
        }
    } catch(PDOException $e) {
        $_SESSION['flash_error'] = 'Une erreur est survenue lors de la suppression de la voiture.';
    }
}

header('Location: manage_cars.php');
exit();
?>
