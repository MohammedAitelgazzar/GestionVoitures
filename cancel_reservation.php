<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];
    
    try {
        $conn->beginTransaction();
        
        // Vérifier que la réservation appartient bien à l'utilisateur
        $stmt = $conn->prepare("
            SELECT car_id 
            FROM reservations 
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ");
        $stmt->execute([$reservation_id, $_SESSION['user_id']]);
        $reservation = $stmt->fetch();
        
        if ($reservation) {
            // Mettre à jour le statut de la réservation
            $stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$reservation_id]);
            
            // Remettre la voiture comme disponible
            $stmt = $conn->prepare("UPDATE cars SET status = 'available' WHERE id = ?");
            $stmt->execute([$reservation['car_id']]);
            
            $conn->commit();
            $_SESSION['flash_message'] = 'La réservation a été annulée avec succès.';
        }
        
    } catch(PDOException $e) {
        $conn->rollBack();
        $_SESSION['flash_error'] = 'Une erreur est survenue lors de l\'annulation de la réservation.';
    }
}

header('Location: my_reservations.php');
exit();
?>
