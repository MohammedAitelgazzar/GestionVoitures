<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

requireAdmin();

if (isset($_GET['id']) && isset($_GET['action'])) {
    $reservation_id = $_GET['id'];
    $action = $_GET['action'];
    
    try {
        $conn->beginTransaction();
        
        // Vérifier que la réservation existe et est en attente
        $stmt = $conn->prepare("
            SELECT r.*, c.id as car_id 
            FROM reservations r
            JOIN cars c ON r.car_id = c.id
            WHERE r.id = ? AND r.status = 'pending'
        ");
        $stmt->execute([$reservation_id]);
        $reservation = $stmt->fetch();
        
        if ($reservation) {
            if ($action === 'confirm') {
                // Confirmer la réservation
                $stmt = $conn->prepare("UPDATE reservations SET status = 'confirmed' WHERE id = ?");
                $stmt->execute([$reservation_id]);
                
                $_SESSION['flash_message'] = 'La réservation a été confirmée avec succès.';
                
            } elseif ($action === 'cancel') {
                // Annuler la réservation
                $stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
                $stmt->execute([$reservation_id]);
                
                // Remettre la voiture comme disponible
                $stmt = $conn->prepare("UPDATE cars SET status = 'available' WHERE id = ?");
                $stmt->execute([$reservation['car_id']]);
                
                $_SESSION['flash_message'] = 'La réservation a été annulée avec succès.';
            }
            
            $conn->commit();
        }
    } catch(PDOException $e) {
        $conn->rollBack();
        $_SESSION['flash_error'] = 'Une erreur est survenue lors de la mise à jour de la réservation.';
    }
}

header('Location: manage_reservations.php');
exit();
?>
