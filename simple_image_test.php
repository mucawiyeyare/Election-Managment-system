<!DOCTYPE html>
<html>
<head>
    <title>Simple Image Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
        .success { color: green; }
        .error { color: red; }
        img { max-width: 200px; max-height: 200px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Simple Image Test - Identifying Problems</h1>
    
    <?php
    require_once 'includes/db.php';
    require_once 'includes/image_utils.php';
    
    echo "<div class='test-section'>";
    echo "<h2>Problem 1: Database Connection</h2>";
    if ($conn) {
        echo "<p class='success'>✓ Database connected successfully</p>";
    } else {
        echo "<p class='error'>✗ Database connection failed</p>";
        exit;
    }
    echo "</div>";
    
    echo "<div class='test-section'>";
    echo "<h2>Problem 2: Candidates in Database</h2>";
    $candidates = $conn->query("SELECT id, full_name, profile_image FROM candidates LIMIT 3");
    if ($candidates && $candidates->num_rows > 0) {
        echo "<p class='success'>✓ Found " . $candidates->num_rows . " candidates</p>";
        while ($row = $candidates->fetch_assoc()) {
            echo "<p>- " . htmlspecialchars($row['full_name']) . " (Image: " . htmlspecialchars($row['profile_image'] ?: 'None') . ")</p>";
        }
    } else {
        echo "<p class='error'>✗ No candidates found in database</p>";
    }
    echo "</div>";
    
    echo "<div class='test-section'>";
    echo "<h2>Problem 3: Upload Directory</h2>";
    $uploads_dir = __DIR__ . '/uploads/';
    if (is_dir($uploads_dir)) {
        echo "<p class='success'>✓ Uploads directory exists: " . htmlspecialchars($uploads_dir) . "</p>";
        $files = glob($uploads_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        echo "<p class='success'>✓ Found " . count($files) . " image files</p>";
        foreach (array_slice($files, 0, 3) as $file) {
            echo "<p>- " . basename($file) . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Uploads directory not found</p>";
    }
    echo "</div>";
    
    echo "<div class='test-section'>";
    echo "<h2>Problem 4: Web Access to Images</h2>";
    $test_images = ['farmajo.jpeg', 'candidate_68a7723bc8dcd.jpeg'];
    foreach ($test_images as $img) {
        $web_url = '/EMS2/uploads/' . $img;
        $file_path = __DIR__ . '/uploads/' . $img;
        
        echo "<h3>Testing: " . htmlspecialchars($img) . "</h3>";
        echo "<p>File exists on server: " . (file_exists($file_path) ? "<span class='success'>YES</span>" : "<span class='error'>NO</span>") . "</p>";
        echo "<p>Web URL: <a href='" . htmlspecialchars($web_url) . "' target='_blank'>" . htmlspecialchars($web_url) . "</a></p>";
        echo "<p>Direct image test:</p>";
        echo "<img src='" . htmlspecialchars($web_url) . "' alt='Test' onerror=\"this.style.border='3px solid red'; this.alt='FAILED TO LOAD';\" onload=\"this.style.border='3px solid green';\">";
        echo "<hr>";
    }
    echo "</div>";
    
    echo "<div class='test-section'>";
    echo "<h2>Problem 5: Function Test</h2>";
    $candidates->data_seek(0); // Reset result pointer
    while ($row = $candidates->fetch_assoc()) {
        $generated_url = getPublicCandidateImageUrl($row['profile_image']);
        echo "<h3>" . htmlspecialchars($row['full_name']) . "</h3>";
        echo "<p>DB Image: " . htmlspecialchars($row['profile_image'] ?: 'None') . "</p>";
        echo "<p>Generated URL: " . htmlspecialchars($generated_url) . "</p>";
        echo "<p>Function result:</p>";
        echo "<img src='" . htmlspecialchars($generated_url) . "' alt='Function Test' onerror=\"this.style.border='3px solid red'; this.alt='FUNCTION FAILED';\" onload=\"this.style.border='3px solid green';\">";
        echo "<hr>";
    }
    echo "</div>";
    
    echo "<div class='test-section'>";
    echo "<h2>Problem 6: Home Page Query</h2>";
    $home_query = "SELECT c.id, c.full_name, c.profile_image, e.title AS election_title, (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.id) AS votes FROM candidates c LEFT JOIN elections e ON c.election_id = e.id ORDER BY c.id DESC LIMIT 6";
    $home_result = $conn->query($home_query);
    
    if ($home_result && $home_result->num_rows > 0) {
        echo "<p class='success'>✓ Home page query returned " . $home_result->num_rows . " results</p>";
        echo "<h3>Home Page Results:</h3>";
        while ($row = $home_result->fetch_assoc()) {
            $img_url = getPublicCandidateImageUrl($row['profile_image']);
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px;'>";
            echo "<strong>" . htmlspecialchars($row['full_name']) . "</strong><br>";
            echo "Image file: " . htmlspecialchars($row['profile_image'] ?: 'None') . "<br>";
            echo "Generated URL: " . htmlspecialchars($img_url) . "<br>";
            echo "Election: " . htmlspecialchars($row['election_title'] ?: 'None') . "<br>";
            echo "<img src='" . htmlspecialchars($img_url) . "' style='max-width: 100px; max-height: 100px; margin-top: 5px;' alt='Home page test' onerror=\"this.style.border='2px solid red';\" onload=\"this.style.border='2px solid green';\">";
            echo "</div>";
        }
    } else {
        echo "<p class='error'>✗ Home page query returned no results</p>";
    }
    echo "</div>";
    ?>
    
    <div class="test-section">
        <h2>Next Steps</h2>
        <p><a href="public/index.php">View Home Page</a></p>
        <p><a href="debug_image_issue.php">Detailed Debug</a></p>
    </div>
    
    <script>
    console.log('Image test page loaded');
    setTimeout(function() {
        const images = document.querySelectorAll('img');
        let loaded = 0, failed = 0;
        images.forEach(img => {
            if (img.style.border.includes('green')) loaded++;
            if (img.style.border.includes('red')) failed++;
        });
        console.log(`Image test results: ${loaded} loaded, ${failed} failed, ${images.length} total`);
    }, 3000);
    </script>
</body>
</html>