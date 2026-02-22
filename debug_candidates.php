<?php
require_once 'includes/db.php';
require_once 'includes/image_utils.php';

echo "<h2>Debug: Candidates and Images</h2>\n";

$result = $conn->query("SELECT id, full_name, profile_image FROM candidates ORDER BY id DESC LIMIT 10");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>ID</th><th>Name</th><th>Image Filename</th><th>File Exists</th><th>Web URL</th><th>Public URL</th></tr>\n";
    
    while ($row = $result->fetch_assoc()) {
        $file_exists = 'No';
        $file_size = 'N/A';
        
        if (!empty($row['profile_image'])) {
            $file_path = __DIR__ . '/uploads/' . $row['profile_image'];
            if (file_exists($file_path)) {
                $file_exists = 'Yes (' . filesize($file_path) . ' bytes)';
            }
        }
        
        $web_url = getCandidateImageUrl($row['profile_image'], 'uploads/');
        $public_url = getPublicCandidateImageUrl($row['profile_image']);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['profile_image'] ?: 'None') . "</td>";
        echo "<td>" . $file_exists . "</td>";
        echo "<td>" . htmlspecialchars($web_url) . "</td>";
        echo "<td>" . htmlspecialchars($public_url) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "<p>No candidates found in database.</p>\n";
}

echo "<h3>Upload Directory Info:</h3>\n";
echo "<p>Upload directory: " . __DIR__ . "/uploads/</p>\n";
echo "<p>Directory exists: " . (is_dir(__DIR__ . "/uploads/") ? 'Yes' : 'No') . "</p>\n";
echo "<p>Directory readable: " . (is_readable(__DIR__ . "/uploads/") ? 'Yes' : 'No') . "</p>\n";

if (is_dir(__DIR__ . "/uploads/")) {
    $files = glob(__DIR__ . "/uploads/*");
    echo "<p>Files in directory: " . count($files) . "</p>\n";
    echo "<h4>Files:</h4>\n";
    echo "<ul>\n";
    foreach ($files as $file) {
        echo "<li>" . basename($file) . " (" . filesize($file) . " bytes)</li>\n";
    }
    echo "</ul>\n";
}
?>