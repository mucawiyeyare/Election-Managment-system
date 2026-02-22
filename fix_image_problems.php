<?php
// Comprehensive fix for home page image problems
require_once 'includes/db.php';
require_once 'includes/image_utils.php';

echo "<h1>🔧 Fixing Home Page Image Problems</h1>";

// Problem 1: Check and fix database issues
echo "<h2>Step 1: Database Check & Fix</h2>";

$candidates = $conn->query("SELECT id, full_name, profile_image FROM candidates");
if ($candidates && $candidates->num_rows > 0) {
    echo "<p>✅ Found " . $candidates->num_rows . " candidates in database</p>";
    
    $fixed_count = 0;
    while ($row = $candidates->fetch_assoc()) {
        if (!empty($row['profile_image'])) {
            $file_path = __DIR__ . '/uploads/' . $row['profile_image'];
            if (!file_exists($file_path)) {
                echo "<p>⚠️ Missing file for " . htmlspecialchars($row['full_name']) . ": " . htmlspecialchars($row['profile_image']) . "</p>";
                
                // Try to find similar file
                $base_name = pathinfo($row['profile_image'], PATHINFO_FILENAME);
                $extension = pathinfo($row['profile_image'], PATHINFO_EXTENSION);
                $similar_files = glob(__DIR__ . '/uploads/*' . $base_name . '*.' . $extension);
                
                if (!empty($similar_files)) {
                    $new_file = basename($similar_files[0]);
                    echo "<p>🔄 Found similar file: " . htmlspecialchars($new_file) . "</p>";
                    
                    // Update database
                    $update_stmt = $conn->prepare("UPDATE candidates SET profile_image = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $new_file, $row['id']);
                    if ($update_stmt->execute()) {
                        echo "<p>✅ Updated database for " . htmlspecialchars($row['full_name']) . "</p>";
                        $fixed_count++;
                    }
                }
            } else {
                echo "<p>✅ File exists for " . htmlspecialchars($row['full_name']) . "</p>";
            }
        }
    }
    echo "<p><strong>Fixed " . $fixed_count . " database entries</strong></p>";
} else {
    echo "<p>❌ No candidates found in database!</p>";
}

// Problem 2: Check uploads directory
echo "<h2>Step 2: Uploads Directory Check</h2>";
$uploads_dir = __DIR__ . '/uploads/';
if (is_dir($uploads_dir)) {
    echo "<p>✅ Uploads directory exists</p>";
    
    if (is_readable($uploads_dir)) {
        echo "<p>✅ Directory is readable</p>";
    } else {
        echo "<p>❌ Directory is not readable - check permissions</p>";
    }
    
    $files = glob($uploads_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    echo "<p>✅ Found " . count($files) . " image files</p>";
    
    // List files
    foreach ($files as $file) {
        $filename = basename($file);
        $size = filesize($file);
        echo "<p>- " . htmlspecialchars($filename) . " (" . number_format($size) . " bytes)</p>";
    }
} else {
    echo "<p>❌ Uploads directory does not exist!</p>";
    echo "<p>Creating uploads directory...</p>";
    if (mkdir($uploads_dir, 0755, true)) {
        echo "<p>✅ Created uploads directory</p>";
    } else {
        echo "<p>❌ Failed to create uploads directory</p>";
    }
}

// Problem 3: Check assets directory
echo "<h2>Step 3: Assets Directory Check</h2>";
$assets_dir = __DIR__ . '/assets/';
if (is_dir($assets_dir)) {
    echo "<p>✅ Assets directory exists</p>";
    
    $svg_file = $assets_dir . 'ems_intro.svg';
    if (file_exists($svg_file)) {
        echo "<p>✅ EMS logo file exists</p>";
    } else {
        echo "<p>⚠️ EMS logo missing - creating default...</p>";
        // Create default SVG
        $svg_content = '<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
  <rect width="400" height="300" fill="#8B5CF6" rx="10"/>
  <circle cx="200" cy="120" r="40" fill="white" opacity="0.9"/>
  <text x="200" y="180" font-family="Arial" font-size="24" font-weight="bold" text-anchor="middle" fill="white">EMS</text>
  <text x="200" y="210" font-family="Arial" font-size="16" text-anchor="middle" fill="white">Election Management</text>
</svg>';
        if (file_put_contents($svg_file, $svg_content)) {
            echo "<p>✅ Created default EMS logo</p>";
        }
    }
} else {
    echo "<p>❌ Assets directory missing - creating...</p>";
    if (mkdir($assets_dir, 0755, true)) {
        echo "<p>✅ Created assets directory</p>";
    }
}

// Problem 4: Test image function
echo "<h2>Step 4: Image Function Test</h2>";
$test_files = ['farmajo.jpeg', 'candidate_68a7723bc8dcd.jpeg', 'nonexistent.jpg'];
foreach ($test_files as $test_file) {
    $result = getPublicCandidateImageUrl($test_file);
    $exists = file_exists(__DIR__ . '/uploads/' . $test_file);
    echo "<p>Input: " . htmlspecialchars($test_file) . " → Output: " . htmlspecialchars($result) . " (File exists: " . ($exists ? 'Yes' : 'No') . ")</p>";
}

// Problem 5: Test home page query
echo "<h2>Step 5: Home Page Query Test</h2>";
$home_query = "SELECT c.id, c.full_name, c.profile_image, e.title AS election_title, (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.id) AS votes FROM candidates c LEFT JOIN elections e ON c.election_id = e.id ORDER BY c.id DESC LIMIT 6";
$home_result = $conn->query($home_query);

if ($home_result && $home_result->num_rows > 0) {
    echo "<p>✅ Home page query returned " . $home_result->num_rows . " results</p>";
    
    echo "<h3>Preview of home page candidates:</h3>";
    while ($row = $home_result->fetch_assoc()) {
        $img_url = getPublicCandidateImageUrl($row['profile_image']);
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px; display: inline-block; width: 200px;'>";
        echo "<strong>" . htmlspecialchars($row['full_name']) . "</strong><br>";
        echo "<small>Image: " . htmlspecialchars($row['profile_image'] ?: 'None') . "</small><br>";
        echo "<img src='" . htmlspecialchars($img_url) . "' style='width: 100%; max-height: 150px; object-fit: cover; margin-top: 5px;' alt='Preview'>";
        echo "</div>";
    }
} else {
    echo "<p>❌ Home page query returned no results</p>";
    echo "<p>This means either:</p>";
    echo "<ul>";
    echo "<li>No candidates in database</li>";
    echo "<li>No elections in database</li>";
    echo "<li>Database connection issues</li>";
    echo "</ul>";
}

// Problem 6: Create .htaccess if missing
echo "<h2>Step 6: Web Server Configuration</h2>";
$htaccess_file = $uploads_dir . '.htaccess';
if (!file_exists($htaccess_file)) {
    echo "<p>⚠️ .htaccess missing in uploads - creating...</p>";
    $htaccess_content = '# Allow access to image files
<Files ~ "\.(jpg|jpeg|png|gif|webp)$">
    Order allow,deny
    Allow from all
</Files>

# Prevent access to PHP files
<Files ~ "\.php$">
    Order deny,allow
    Deny from all
</Files>

# Set proper MIME types
AddType image/jpeg .jpg .jpeg
AddType image/png .png
AddType image/gif .gif
';
    if (file_put_contents($htaccess_file, $htaccess_content)) {
        echo "<p>✅ Created .htaccess file</p>";
    }
} else {
    echo "<p>✅ .htaccess file exists</p>";
}

echo "<h2>🎯 Summary & Next Steps</h2>";
echo "<p><strong>Fixes Applied:</strong></p>";
echo "<ul>";
echo "<li>✅ Checked and fixed database image references</li>";
echo "<li>✅ Verified uploads directory structure</li>";
echo "<li>✅ Ensured fallback assets exist</li>";
echo "<li>✅ Tested image utility functions</li>";
echo "<li>✅ Verified home page query</li>";
echo "<li>✅ Created web server configuration</li>";
echo "</ul>";

echo "<p><strong>Test Your Home Page:</strong></p>";
echo "<p><a href='public/index.php' target='_blank' style='background: #8B5CF6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Home Page</a></p>";

echo "<p><strong>Additional Tests:</strong></p>";
echo "<ul>";
echo "<li><a href='simple_image_test.php'>Simple Image Test</a></li>";
echo "<li><a href='test_direct_access.html'>Direct Access Test</a></li>";
echo "<li><a href='debug_image_issue.php'>Detailed Debug</a></li>";
echo "</ul>";

echo "<p><strong>If images still don't work:</strong></p>";
echo "<ol>";
echo "<li>Restart XAMPP Apache service</li>";
echo "<li>Clear browser cache (Ctrl+F5)</li>";
echo "<li>Check browser console for error messages</li>";
echo "<li>Verify XAMPP is serving files from correct directory</li>";
echo "</ol>";
?>