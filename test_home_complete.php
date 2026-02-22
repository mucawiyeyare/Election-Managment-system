<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Home Page Image Test</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center">Complete Home Page Image Test</h1>
        
        <?php
        require_once 'includes/db.php';
        require_once 'includes/image_utils.php';
        
        // Same query as home page
        $latest = $conn->query("SELECT c.id, c.full_name, c.profile_image, e.title AS election_title, (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.id) AS votes FROM candidates c LEFT JOIN elections e ON c.election_id = e.id ORDER BY c.id DESC LIMIT 6");
        ?>
        
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Database Query Results</h2>
            <p><strong>Query:</strong> Same as home page - Latest 6 candidates</p>
            <p><strong>Results found:</strong> <?= $latest ? $latest->num_rows : 0 ?></p>
        </div>
        
        <?php if ($latest && $latest->num_rows > 0): ?>
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Exact Home Page Layout Test</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                    <?php 
                    // Reset the result pointer
                    $latest->data_seek(0);
                    while ($c = $latest->fetch_assoc()): 
                        $img = getPublicCandidateImageUrl($c['profile_image']);
                    ?>
                        <div class="group rounded-2xl overflow-hidden bg-gradient-to-b from-purple-800 to-purple-900 text-purple-50 ring-1 ring-purple-700/50 shadow hover:shadow-2xl transition">
                            <div class="relative">
                                <img src="<?= htmlspecialchars($img) ?>" 
                                     alt="<?= htmlspecialchars($c['full_name']) ?>" 
                                     class="block w-full h-64 object-cover" 
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='/EMS2/assets/ems_intro.svg';this.style.border='3px solid red';"
                                     onload="this.style.border='3px solid green';">
                                <div class="absolute top-3 left-3 flex gap-2">
                                    <span class="px-2 py-1 rounded-full bg-purple-600 bg-opacity-80 text-xs capitalize"><?= htmlspecialchars($c['election_title'] ?? 'Candidate') ?></span>
                                    <span class="px-2 py-1 rounded-full bg-purple-700 bg-opacity-80 text-xs">New</span>
                                </div>
                                <div class="absolute top-3 right-3">
                                    <span class="px-2 py-1 rounded-full bg-purple-700 bg-opacity-80 text-xs flex items-center gap-1">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <?= (int)($c['votes'] ?? 0) ?> votes
                                    </span>
                                </div>
                            </div>
                            <div class="p-5">
                                <h3 class="text-lg font-semibold mb-1 truncate group-hover:text-white">
                                    <?= htmlspecialchars($c['full_name']) ?>
                                </h3>
                                <p class="text-sm text-gray-300 h-10 overflow-hidden">
                                    Running in <?= htmlspecialchars($c['election_title'] ?? 'N/A') ?>.
                                </p>
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <img src="<?= $img ?>" 
                                             alt="<?= htmlspecialchars($c['full_name']) ?>" 
                                             class="w-8 h-8 rounded-full object-cover ring-2 ring-purple-500" 
                                             loading="lazy"
                                             onerror="this.onerror=null;this.src='/EMS2/assets/ems_intro.svg';">
                                        <span class="text-sm text-gray-300"><?= htmlspecialchars($c['full_name']) ?></span>
                                        <i class="fas fa-badge-check text-green-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Detailed Debug Information</h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 p-2">ID</th>
                                <th class="border border-gray-300 p-2">Name</th>
                                <th class="border border-gray-300 p-2">DB Image</th>
                                <th class="border border-gray-300 p-2">File Exists</th>
                                <th class="border border-gray-300 p-2">Generated URL</th>
                                <th class="border border-gray-300 p-2">File Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $latest->data_seek(0);
                            while ($c = $latest->fetch_assoc()): 
                                $img_url = getPublicCandidateImageUrl($c['profile_image']);
                                $file_exists = false;
                                $file_size = 'N/A';
                                
                                if (!empty($c['profile_image'])) {
                                    $file_path = __DIR__ . '/uploads/' . basename($c['profile_image']);
                                    if (file_exists($file_path)) {
                                        $file_exists = true;
                                        $file_size = filesize($file_path) . ' bytes';
                                    }
                                }
                            ?>
                                <tr>
                                    <td class="border border-gray-300 p-2"><?= htmlspecialchars($c['id']) ?></td>
                                    <td class="border border-gray-300 p-2"><?= htmlspecialchars($c['full_name']) ?></td>
                                    <td class="border border-gray-300 p-2"><?= htmlspecialchars($c['profile_image'] ?: 'None') ?></td>
                                    <td class="border border-gray-300 p-2 <?= $file_exists ? 'text-green-600' : 'text-red-600' ?>">
                                        <?= $file_exists ? 'Yes' : 'No' ?>
                                    </td>
                                    <td class="border border-gray-300 p-2 text-xs"><?= htmlspecialchars($img_url) ?></td>
                                    <td class="border border-gray-300 p-2"><?= $file_size ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php else: ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <strong>No candidates found!</strong> The database query returned no results.
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow p-6 mt-8">
            <h2 class="text-xl font-semibold mb-4">System Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <h3 class="font-medium mb-2">Paths:</h3>
                    <ul class="space-y-1">
                        <li><strong>Current Dir:</strong> <?= htmlspecialchars(__DIR__) ?></li>
                        <li><strong>Uploads Dir:</strong> <?= htmlspecialchars(__DIR__ . '/uploads') ?></li>
                        <li><strong>Assets Dir:</strong> <?= htmlspecialchars(__DIR__ . '/assets') ?></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-medium mb-2">Directory Status:</h3>
                    <ul class="space-y-1">
                        <li><strong>Uploads Exists:</strong> <?= is_dir(__DIR__ . '/uploads') ? 'Yes' : 'No' ?></li>
                        <li><strong>Assets Exists:</strong> <?= is_dir(__DIR__ . '/assets') ? 'Yes' : 'No' ?></li>
                        <li><strong>Upload Files:</strong> <?= is_dir(__DIR__ . '/uploads') ? count(glob(__DIR__ . '/uploads/*')) : 'N/A' ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <a href="public/index.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 mr-4">
                View Actual Home Page
            </a>
            <a href="debug_candidates.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                View Debug Info
            </a>
        </div>
    </div>
</body>
</html>