<?php
// Simple script to check image accessibility
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Image Access Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        img { max-width: 100px; max-height: 100px; }
    </style>
</head>
<body>
    <h1>Image Access Check</h1>
    
    <?php
    $uploads_dir = __DIR__ . '/uploads/';
    $web_uploads = '/EMS2/uploads/';
    
    echo "<h2>Directory Information</h2>";
    echo "<p><strong>Physical path:</strong> " . htmlspecialchars($uploads_dir) . "</p>";
    echo "<p><strong>Web path:</strong> " . htmlspecialchars($web_uploads) . "</p>";
    echo "<p><strong>Directory exists:</strong> " . (is_dir($uploads_dir) ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
    echo "<p><strong>Directory readable:</strong> " . (is_readable($uploads_dir) ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
    
    if (is_dir($uploads_dir)) {
        $files = glob($uploads_dir . '*');
        echo "<p><strong>Files found:</strong> " . count($files) . "</p>";
        
        if (count($files) > 0) {
            echo "<h2>Image Files Test</h2>";
            echo "<table>";
            echo "<tr><th>Filename</th><th>Size</th><th>Web URL</th><th>Preview</th><th>Status</th></tr>";
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $filename = basename($file);
                    $size = filesize($file);
                    $web_url = $web_uploads . $filename;
                    
                    // Check if it's an image
                    $is_image = false;
                    $image_info = @getimagesize($file);
                    if ($image_info !== false) {
                        $is_image = true;
                    }
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($filename) . "</td>";
                    echo "<td>" . number_format($size) . " bytes</td>";
                    echo "<td><a href='" . htmlspecialchars($web_url) . "' target='_blank'>" . htmlspecialchars($web_url) . "</a></td>";
                    
                    if ($is_image) {
                        echo "<td><img src='" . htmlspecialchars($web_url) . "' alt='Preview' style='max-width: 80px; max-height: 80px;'></td>";
                        echo "<td><span class='success'>Valid Image</span></td>";
                    } else {
                        echo "<td>Not an image</td>";
                        echo "<td><span class='error'>Invalid</span></td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        }
    }
    
    // Test the function
    require_once 'includes/image_utils.php';
    
    echo "<h2>Function Test</h2>";
    $test_files = ['farmajo.jpeg', 'nonexistent.jpg', ''];
    
    foreach ($test_files as $test_file) {
        $result = getPublicCandidateImageUrl($test_file);
        echo "<p><strong>Input:</strong> '" . htmlspecialchars($test_file) . "' → <strong>Output:</strong> " . htmlspecialchars($result) . "</p>";
    }
    
    // Test database candidates
    require_once 'includes/db.php';
    $candidates = $conn->query("SELECT id, full_name, profile_image FROM candidates LIMIT 5");
    
    if ($candidates && $candidates->num_rows > 0) {
        echo "<h2>Database Candidates Test</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>DB Image</th><th>Generated URL</th><th>Preview</th></tr>";
        
        while ($row = $candidates->fetch_assoc()) {
            $url = getPublicCandidateImageUrl($row['profile_image']);
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['profile_image'] ?: 'None') . "</td>";
            echo "<td>" . htmlspecialchars($url) . "</td>";
            echo "<td><img src='" . htmlspecialchars($url) . "' alt='Preview' style='max-width: 60px; max-height: 60px;'></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    ?>
    
    <h2>Quick Actions</h2>
    <p>
        <a href="public/index.php">View Home Page</a> | 
        <a href="test_home_complete.php">Complete Test</a> | 
        <a href="debug_candidates.php">Debug Candidates</a>
    </p>
</body>
</html>