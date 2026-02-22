<?php
require_once '../includes/db.php';
require_once '../includes/image_utils.php';

// Get some candidates to test
$result = $conn->query("SELECT id, full_name, profile_image FROM candidates LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page Image Test</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Home Page Image Test</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Testing Public Image URLs</h2>
            
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php while ($candidate = $result->fetch_assoc()): ?>
                        <div class="border rounded-lg p-4">
                            <h3 class="font-medium mb-2"><?= htmlspecialchars($candidate['full_name']) ?></h3>
                            
                            <?php 
                                $img_url = getPublicCandidateImageUrl($candidate['profile_image']);
                                $file_exists = !empty($candidate['profile_image']) && file_exists('../uploads/' . $candidate['profile_image']);
                            ?>
                            
                            <div class="mb-4">
                                <img src="<?= htmlspecialchars($img_url) ?>" 
                                     alt="<?= htmlspecialchars($candidate['full_name']) ?>" 
                                     class="w-full h-48 object-cover rounded border"
                                     onerror="this.onerror=null;this.src='https://via.placeholder.com/400x300/8B5CF6/FFFFFF?text=EMS+Candidate';"
                                     onload="this.style.border='2px solid green';"
                                     onerror="this.style.border='2px solid red';">
                            </div>
                            
                            <div class="text-xs text-gray-500 space-y-1">
                                <p><strong>DB Image:</strong> <?= htmlspecialchars($candidate['profile_image'] ?: 'None') ?></p>
                                <p><strong>Generated URL:</strong> <?= htmlspecialchars($img_url) ?></p>
                                <p><strong>File Exists:</strong> <?= $file_exists ? 'Yes' : 'No' ?></p>
                                <?php if (!empty($candidate['profile_image'])): ?>
                                    <p><strong>File Path:</strong> ../uploads/<?= htmlspecialchars($candidate['profile_image']) ?></p>
                                    <p><strong>Full Path:</strong> <?= htmlspecialchars(realpath('../uploads/' . $candidate['profile_image']) ?: 'Not found') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No candidates found to test.</p>
            <?php endif; ?>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Directory Information</h2>
            <div class="text-sm space-y-2">
                <p><strong>Current Directory:</strong> <?= htmlspecialchars(__DIR__) ?></p>
                <p><strong>Uploads Directory:</strong> <?= htmlspecialchars(realpath('../uploads') ?: 'Not found') ?></p>
                <p><strong>Uploads Exists:</strong> <?= is_dir('../uploads') ? 'Yes' : 'No' ?></p>
                <p><strong>Uploads Readable:</strong> <?= is_readable('../uploads') ? 'Yes' : 'No' ?></p>
                <p><strong>Files in Uploads:</strong> <?= is_dir('../uploads') ? count(glob('../uploads/*')) : 'N/A' ?></p>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="index.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>