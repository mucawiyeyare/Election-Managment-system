<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');

// Check if election ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: elections.php?error=' . urlencode('Election ID is required'));
    exit;
}

$election_id = (int)$_GET['id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Check if election exists
    $stmt = $conn->prepare("SELECT id FROM elections WHERE id = ?");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Election not found");
    }
    
    // Delete votes for this election
    $stmt = $conn->prepare("DELETE FROM votes WHERE election_id = ?");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    
    // Get candidates in this election to update their records
    $stmt = $conn->prepare("SELECT id, profile_image FROM candidates WHERE election_id = ?");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $candidates_result = $stmt->get_result();
    
    $candidate_ids = [];
    $profile_images = [];
    
    while ($candidate = $candidates_result->fetch_assoc()) {
        $candidate_ids[] = $candidate['id'];
        if (!empty($candidate['profile_image'])) {
            $profile_images[] = $candidate['profile_image'];
        }
    }
    
    // Delete candidates from this election
    if (!empty($candidate_ids)) {
        $stmt = $conn->prepare("DELETE FROM candidates WHERE election_id = ?");
        $stmt->bind_param("i", $election_id);
        $stmt->execute();
    }
    
    // Delete election
    $stmt = $conn->prepare("DELETE FROM elections WHERE id = ?");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Delete candidate profile images
    foreach ($profile_images as $image) {
        $image_path = "../uploads/$image";
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    header('Location: elections.php?message=' . urlencode('Election deleted successfully'));
    exit;
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header('Location: elections.php?error=' . urlencode('Error deleting election: ' . $e->getMessage()));
    exit;
}
?>