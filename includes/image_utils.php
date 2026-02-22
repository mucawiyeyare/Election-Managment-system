<?php
/**
 * Image utility functions for the Election Management System
 */

/**
 * Get candidate image URL with fallback
 * @param string $image_filename The image filename from database
 * @param string $uploads_path Path to uploads directory (default: '../uploads/')
 * @param string $placeholder_text Text for placeholder image
 * @return string The image URL to use
 */
function getCandidateImageUrl($image_filename, $uploads_path = '../uploads/', $placeholder_text = 'No+Photo') {
    // Default placeholder
    $placeholder_url = "https://via.placeholder.com/150?text=" . urlencode($placeholder_text);
    
    // If no image filename provided, return placeholder
    if (empty($image_filename)) {
        return $placeholder_url;
    }
    
    // Construct full path for file existence check
    $file_check_path = $uploads_path . $image_filename;
    
    // For web URLs, we need to ensure the path is correct
    $web_path = $uploads_path . $image_filename;
    
    // Check if file exists (handle different path contexts)
    $file_exists = false;
    if (file_exists($file_check_path)) {
        $file_exists = true;
    } else {
        // Try alternative path for different directory contexts
        $alt_path = dirname(__DIR__) . '/uploads/' . $image_filename;
        if (file_exists($alt_path)) {
            $file_exists = true;
        }
    }
    
    if ($file_exists) {
        return $web_path;
    }
    
    // File doesn't exist, return placeholder
    return $placeholder_url;
}

/**
 * Validate uploaded image file
 * @param array $file The $_FILES array element
 * @param int $max_size Maximum file size in bytes (default: 5MB)
 * @return array Array with 'valid' boolean and 'error' message
 */
function validateImageUpload($file, $max_size = 5242880) { // 5MB default
    $result = ['valid' => false, 'error' => ''];
    
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'No file uploaded or upload error occurred.';
        return $result;
    }
    
    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime_type = $file['type'];
    
    if (!in_array($extension, $allowed_types) || !in_array($mime_type, $allowed_mime_types)) {
        $result['error'] = 'Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.';
        return $result;
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        $size_mb = round($max_size / 1024 / 1024, 1);
        $result['error'] = "File size too large. Maximum size is {$size_mb}MB.";
        return $result;
    }
    
    // Additional security check - verify it's actually an image
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        $result['error'] = 'Invalid image file.';
        return $result;
    }
    
    $result['valid'] = true;
    return $result;
}

/**
 * Generate unique filename for uploaded image
 * @param string $original_filename Original filename
 * @param string $prefix Prefix for the filename (default: 'candidate_')
 * @return string Generated unique filename
 */
function generateImageFilename($original_filename, $prefix = 'candidate_') {
    $extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    return $prefix . uniqid() . '.' . $extension;
}

/**
 * Handle image upload with validation
 * @param array $file The $_FILES array element
 * @param string $upload_dir Upload directory path
 * @param string $prefix Filename prefix
 * @return array Array with 'success' boolean, 'filename' string, and 'error' message
 */
function handleImageUpload($file, $upload_dir = '../uploads/', $prefix = 'candidate_') {
    $result = ['success' => false, 'filename' => '', 'error' => ''];
    
    // Validate the upload
    $validation = validateImageUpload($file);
    if (!$validation['valid']) {
        $result['error'] = $validation['error'];
        return $result;
    }
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            $result['error'] = 'Failed to create upload directory.';
            return $result;
        }
        // Set proper permissions for Windows/XAMPP
        @chmod($upload_dir, 0777);
    }
    
    // Generate unique filename
    $filename = generateImageFilename($file['name'], $prefix);
    $target_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $result['success'] = true;
        $result['filename'] = $filename;
    } else {
        $result['error'] = 'Failed to move uploaded file.';
    }
    
    return $result;
}

/**
 * Delete image file safely
 * @param string $filename Image filename
 * @param string $upload_dir Upload directory path
 * @return boolean True if deleted or file didn't exist, false on error
 */
function deleteImageFile($filename, $upload_dir = '../uploads/') {
    if (empty($filename)) {
        return true; // Nothing to delete
    }
    
    $file_path = $upload_dir . $filename;
    
    if (file_exists($file_path)) {
        return @unlink($file_path);
    }
    
    return true; // File doesn't exist, consider it "deleted"
}

/**
 * Generate HTML img tag for candidate image
 * @param string $image_filename Image filename from database
 * @param string $alt_text Alt text for the image
 * @param string $css_classes CSS classes to apply
 * @param string $uploads_path Path to uploads directory
 * @return string HTML img tag
 */
function getCandidateImageHtml($image_filename, $alt_text = 'Candidate Photo', $css_classes = 'w-10 h-10 rounded-full object-cover', $uploads_path = '../uploads/') {
    $img_url = getCandidateImageUrl($image_filename, $uploads_path);
    $placeholder_url = "https://via.placeholder.com/150?text=No+Photo";
    
    return sprintf(
        '<img src="%s" alt="%s" class="%s" onerror="this.onerror=null;this.src=\'%s\';" title="%s">',
        htmlspecialchars($img_url),
        htmlspecialchars($alt_text),
        htmlspecialchars($css_classes),
        $placeholder_url,
        htmlspecialchars($alt_text)
    );
}

/**
 * Get candidate image URL for public pages (uses absolute web paths)
 * @param string $image_filename The image filename from database
 * @param string $fallback_image Fallback image path
 * @return string The image URL to use
 */
function getPublicCandidateImageUrl($image_filename, $fallback_image = '/EMS2/assets/ems_intro.svg') {
    // If no image filename provided, return fallback
    if (empty($image_filename)) {
        return $fallback_image;
    }
    
    // Clean the filename to prevent path traversal
    $clean_filename = basename($image_filename);
    
    // Check if file exists in uploads directory
    $file_path = dirname(__DIR__) . '/uploads/' . $clean_filename;
    if (file_exists($file_path) && is_file($file_path)) {
        return '/EMS2/uploads/' . $clean_filename;
    }
    
    // File doesn't exist, try placeholder as backup
    if (!file_exists(dirname(__DIR__) . '/assets/ems_intro.svg')) {
        return 'https://via.placeholder.com/400x300/8B5CF6/FFFFFF?text=EMS+Candidate';
    }
    
    return $fallback_image;
}
?>