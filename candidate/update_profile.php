<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('candidate');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Get candidate ID from user_id
$stmt = $conn->prepare("SELECT id FROM candidates WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($candidate_id);
$stmt->fetch();
$stmt->close();

if (!$candidate_id) {
    die("Candidate not found");
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $party = trim($_POST['party'] ?? '');
    
    // Validate required fields
    if (empty($full_name) || empty($email)) {
        $error = "Full name and email are required fields.";
    } else {
        // Get current profile image
        $stmt = $conn->prepare("SELECT profile_image FROM candidates WHERE id = ?");
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        $stmt->bind_result($current_image);
        $stmt->fetch();
        $stmt->close();
        
        $profile_image = $current_image;
        
        // Handle image upload if provided
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $original_name = basename($_FILES['profile_image']['name']);
            $extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $new_image_name = 'candidate_' . uniqid() . '.' . strtolower($extension);
            $target_path = $upload_dir . $new_image_name;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
                $profile_image = $new_image_name;
                
                // Delete old image if exists and is not the default
                if ($current_image && file_exists($upload_dir . $current_image)) {
                    @unlink($upload_dir . $current_image);
                }
            } else {
                $error = "Failed to upload image. Please try again.";
            }
        }
        
        if (empty($error)) {
            // Update candidate profile
            $stmt = $conn->prepare("UPDATE candidates SET full_name = ?, email = ?, phone = ?, party = ?, profile_image = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $full_name, $email, $phone, $party, $profile_image, $candidate_id);
            
            if ($stmt->execute()) {
                $message = "Profile updated successfully!";
            } else {
                $error = "Error updating profile: " . $conn->error;
            }
            
            $stmt->close();
        }
    }
}

// Redirect back to profile page with message
header("Location: profile.php?message=" . urlencode($message) . "&error=" . urlencode($error));
exit;