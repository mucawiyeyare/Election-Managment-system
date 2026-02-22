<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('voter');

$voter_id = $_SESSION['user_id'] ?? null;
if (!$voter_id) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Check if voter record exists
    $check_query = "SELECT id FROM voters WHERE user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_voter = $result->fetch_assoc();
    $stmt->close();
    
    if ($existing_voter) {
        // Update existing voter record
        $update_query = "UPDATE voters SET full_name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $voter_id);
    } else {
        // Create new voter record
        $insert_query = "INSERT INTO voters (user_id, full_name, email, phone, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("issss", $voter_id, $full_name, $email, $phone, $address);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update profile. Please try again.";
    }
    $stmt->close();
}

// Redirect back to profile page
header("Location: profile.php");
exit;
?>