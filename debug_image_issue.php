<?php
require_once 'includes/db.php';
require_once 'includes/image_utils.php';

echo "<h1>Debug: Home Page Image Issues</h1>";

// Check database candidates
echo "<h2>1. Database Candidates</h2>";
$candidates = $conn->query("SELECT c.id, c.full_name, c.profile_image, e.title AS election_title, (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.id) AS votes FROM candidates c LEFT JOIN elections e ON c.election_id = e.id ORDER BY c.id DESC LIMIT 6");

if ($candidates && $candidates->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>DB Image</th><th>File Exists</th><th>Generated URL</th><th>Direct Test</th></tr>";
    
    while ($row = $candidates->fetch_assoc()) {
        $file_exists = false;
        $file_path = __DIR__ . '/uploads/' . $row['profile_image'];
        
        if (!empty($row['profile_image']) && file_exists($file_path)) {
            $file_exists = true;
        }
        
        $generated_url = getPublicCandidateImageUrl($row['profile_image']);
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['profile_image'] ?: 'NULL') . "</td>";
        echo "<td style='color: " . ($file_exists ? 'green' : 'red') . "'>" . ($file_exists ? 'YES' : 'NO') . "</td>";
        echo "<td>" . htmlspecialchars($generated_url) . "</td>";
        echo "<td><a href='" . htmlspecialchars($generated_url) . "' target='_blank'>Test Link</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>NO CANDIDATES FOUND IN DATABASE!</p>";
}

// Check uploads directory
echo "<h2>2. Uploads Directory Check</h2>";
$uploads_dir = __DIR__ . '/uploads/';
echo "<p><strong>Directory:</strong> " . htmlspecialchars($uploads_dir) . "</p>";
echo "<p><strong>Exists:</strong> " . (is_dir($uploads_dir) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Readable:</strong> " . (is_readable($uploads_dir) ? 'YES' : 'NO') . "</p>";

if (is_dir($uploads_dir)) {
    $files = glob($uploads_dir . '*');
    echo "<p><strong>Files found:</strong> " . count($files) . "</p>";
    
    echo "<h3>Files in uploads:</h3>";
    echo "<ul>";
    foreach ($files as $file) {
        if (is_file($file)) {
            $filename = basename($file);
            $size = filesize($file);
            echo "<li>" . htmlspecialchars($filename) . " (" . number_format($size) . " bytes)</li>";
        }
    }
    echo "</ul>";
}

// Test the function directly
echo "<h2>3. Function Test</h2>";
$test_images = ['farmajo.jpeg', 'candidate_68a7723bc8dcd.jpeg', 'nonexistent.jpg'];

foreach ($test_images as $test_img) {
    $result = getPublicCandidateImageUrl($test_img);
    $file_exists = file_exists(__DIR__ . '/uploads/' . $test_img);
    
    echo "<p><strong>Input:</strong> " . htmlspecialchars($test_img) . "</p>";
    echo "<p><strong>Output:</strong> " . htmlspecialchars($result) . "</p>";
    echo "<p><strong>File Exists:</strong> " . ($file_exists ? 'YES' : 'NO') . "</p>";
    echo "<p><strong>Test:</strong> <a href='" . htmlspecialchars($result) . "' target='_blank'>Click to test</a></p>";
    echo "<hr>";
}

// Check web access
echo "<h2>4. Web Access Test</h2>";
echo "<p>Try accessing these URLs directly:</p>";
echo "<ul>";
echo "<li><a href='/EMS2/uploads/farmajo.jpeg' target='_blank'>/EMS2/uploads/farmajo.jpeg</a></li>";
echo "<li><a href='/EMS2/uploads/candidate_68a7723bc8dcd.jpeg' target='_blank'>/EMS2/uploads/candidate_68a7723bc8dcd.jpeg</a></li>";
echo "<li><a href='/EMS2/assets/ems_intro.svg' target='_blank'>/EMS2/assets/ems_intro.svg</a></li>";
echo "</ul>";

// Check home page query
echo "<h2>5. Home Page Query Test</h2>";
$home_query = "SELECT c.id, c.full_name, c.profile_image, e.title AS election_title, (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.id) AS votes FROM candidates c LEFT JOIN elections e ON c.election_id = e.id ORDER BY c.id DESC LIMIT 6";
echo "<p><strong>Query:</strong> " . htmlspecialchars($home_query) . "</p>";

$home_result = $conn->query($home_query);
echo "<p><strong>Results:</strong> " . ($home_result ? $home_result->num_rows : 0) . " rows</p>";

if ($home_result && $home_result->num_rows > 0) {
    echo "<h3>Home Page Data:</h3>";
    while ($row = $home_result->fetch_assoc()) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px;'>";
        echo "<strong>" . htmlspecialchars($row['full_name']) . "</strong><br>";
        echo "Image: " . htmlspecialchars($row['profile_image'] ?: 'None') . "<br>";
        echo "Election: " . htmlspecialchars($row['election_title'] ?: 'None') . "<br>";
        echo "Votes: " . (int)$row['votes'] . "<br>";
        
        if (!empty($row['profile_image'])) {
            $img_url = getPublicCandidateImageUrl($row['profile_image']);
            echo "Generated URL: " . htmlspecialchars($img_url) . "<br>";
            echo "<img src='" . htmlspecialchars($img_url) . "' style='max-width: 100px; max-height: 100px;' alt='Preview'>";
        }
        echo "</div>";
    }
}
?>