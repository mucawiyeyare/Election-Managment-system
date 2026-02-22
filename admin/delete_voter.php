<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');

// Check if voter ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: voters.php?error=' . urlencode('Voter ID is required'));
    exit;
}

$voter_id = (int)$_GET['id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Get the user_id associated with this voter
    $stmt = $conn->prepare("SELECT user_id FROM voters WHERE id = ?");
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Voter not found");
    }
    
    $user_id = $result->fetch_assoc()['user_id'];
    
    // Delete votes cast by this voter
    $stmt = $conn->prepare("DELETE FROM votes WHERE voter_id = ?");
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
    
    // Delete voter record
    $stmt = $conn->prepare("DELETE FROM voters WHERE id = ?");
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
    
    // Delete user account
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    header('Location: voters.php?message=' . urlencode('Voter deleted successfully'));
    exit;
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header('Location: voters.php?error=' . urlencode('Error deleting voter: ' . $e->getMessage()));
    exit;
}
?>