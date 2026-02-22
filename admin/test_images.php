<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('admin');

// Get a few candidates to test image display
$result = $conn->query("SELECT id, full_name, profile_image FROM candidates LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Test | EMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Candidate Image Display Test</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Testing Image Utility Functions</h2>
            
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($candidate = $result->fetch_assoc()): ?>
                        <div class="border rounded-lg p-4">
                            <h3 class="font-medium mb-2"><?= htmlspecialchars($candidate['full_name']) ?></h3>
                            
                            <!-- Test different image sizes -->
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Small (40x40):</p>
                                    <?= getCandidateImageHtml(
                                        $candidate['profile_image'], 
                                        $candidate['full_name'], 
                                        'w-10 h-10 rounded-full object-cover'
                                    ) ?>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Medium (80x80):</p>
                                    <?= getCandidateImageHtml(
                                        $candidate['profile_image'], 
                                        $candidate['full_name'], 
                                        'w-20 h-20 rounded-lg object-cover'
                                    ) ?>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Large (120x120):</p>
                                    <?= getCandidateImageHtml(
                                        $candidate['profile_image'], 
                                        $candidate['full_name'], 
                                        'w-30 h-30 rounded-lg object-cover'
                                    ) ?>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-xs text-gray-500">
                                <p>Image file: <?= htmlspecialchars($candidate['profile_image'] ?: 'None') ?></p>
                                <p>Image URL: <?= htmlspecialchars(getCandidateImageUrl($candidate['profile_image'])) ?></p>
                                <?php if (!empty($candidate['profile_image'])): ?>
                                    <p>File exists: <?= file_exists('../uploads/' . $candidate['profile_image']) ? 'Yes' : 'No' ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">No candidates found to test.</p>
            <?php endif; ?>
            
            <div class="mt-8 pt-6 border-t">
                <h3 class="font-semibold mb-2">Test Results:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>✓ Image utility functions loaded successfully</li>
                    <li>✓ Uploads directory exists: <?= is_dir('../uploads') ? 'Yes' : 'No' ?></li>
                    <li>✓ Uploads directory writable: <?= is_writable('../uploads') ? 'Yes' : 'No' ?></li>
                    <li>✓ Total image files in uploads: <?= count(glob('../uploads/*')) ?></li>
                </ul>
            </div>
            
            <div class="mt-4">
                <a href="candidates.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    Back to Candidates
                </a>
            </div>
        </div>
    </div>
</body>
</html>