<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('admin');

// Check if candidate ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: candidates.php?error=' . urlencode('Candidate ID is required'));
    exit;
}

$candidate_id = (int)$_GET['id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Get candidate information including profile image and user_id
    $stmt = $conn->prepare("SELECT user_id, profile_image FROM candidates WHERE id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Candidate not found");
    }
    
    $candidate = $result->fetch_assoc();
    $user_id = $candidate['user_id'];
    $profile_image = $candidate['profile_image'];
    
    // Delete votes for this candidate
    $stmt = $conn->prepare("DELETE FROM votes WHERE candidate_id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    
    // Delete candidate record
    $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    
    // Delete user account if exists
    if ($user_id) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Delete profile image if exists
    deleteImageFile($profile_image);
    
    header('Location: candidates.php?message=' . urlencode('Candidate deleted successfully'));
    exit;
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header('Location: candidates.php?error=' . urlencode('Error deleting candidate: ' . $e->getMessage()));
    exit;
}
?>
