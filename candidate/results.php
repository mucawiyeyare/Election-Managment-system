<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('candidate');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit;
}

// Get candidate information
$candidate_query = "SELECT id, full_name, profile_image, election_id FROM candidates WHERE user_id = $user_id";
$candidate_result = $conn->query($candidate_query);
$candidate = $candidate_result->fetch_assoc();
$candidate_id = $candidate['id'];
$election_id = $candidate['election_id'];

// Get election information
$election_query = "SELECT id, title, status FROM elections WHERE id = $election_id";
$election_result = $conn->query($election_query);
$election = $election_result->fetch_assoc();

// Get all candidates for this election with vote counts
$candidates_query = "
    SELECT 
        c.id,
        c.full_name,
        c.party,
        c.profile_image,
        (SELECT COUNT(*) FROM votes WHERE candidate_id = c.id) AS vote_count
    FROM candidates c
    WHERE c.election_id = $election_id
    ORDER BY vote_count DESC, c.full_name ASC
";
$candidates_result = $conn->query($candidates_query);

// Get total votes for this election
$total_votes_query = "SELECT COUNT(*) FROM votes WHERE election_id = $election_id";
$total_votes_result = $conn->query($total_votes_query);
$total_votes = $total_votes_result->fetch_row()[0];

// Prepare data for charts
$candidate_names = [];
$vote_counts = [];
$colors = [];
$borders = [];

// Color palette
$color_palette = [
    ['rgba(126, 34, 206, 0.7)', 'rgba(126, 34, 206, 1)'],  // Purple
    ['rgba(34, 197, 94, 0.7)', 'rgba(34, 197, 94, 1)'],    // Green
    ['rgba(59, 130, 246, 0.7)', 'rgba(59, 130, 246, 1)'],  // Blue
    ['rgba(249, 115, 22, 0.7)', 'rgba(249, 115, 22, 1)'],  // Orange
    ['rgba(236, 72, 153, 0.7)', 'rgba(236, 72, 153, 1)'],  // Pink
    ['rgba(234, 179, 8, 0.7)', 'rgba(234, 179, 8, 1)'],    // Yellow
    ['rgba(14, 165, 233, 0.7)', 'rgba(14, 165, 233, 1)'],  // Sky
    ['rgba(168, 85, 247, 0.7)', 'rgba(168, 85, 247, 1)'],  // Violet
];

// Process candidates for chart data
$candidates_data = [];
$color_index = 0;
$my_votes = 0;

if ($candidates_result) {
    while ($row = $candidates_result->fetch_assoc()) {
        $candidates_data[] = $row;
        
        // For chart data
        $candidate_names[] = $row['full_name'];
        $vote_counts[] = (int)$row['vote_count'];
        
        // Highlight current candidate with a different color
        if ($row['id'] == $candidate_id) {
            $colors[] = 'rgba(220, 38, 38, 0.7)';  // Red for current candidate
            $borders[] = 'rgba(220, 38, 38, 1)';
            $my_votes = (int)$row['vote_count'];
        } else {
            $colors[] = $color_palette[$color_index % count($color_palette)][0];
            $borders[] = $color_palette[$color_index % count($color_palette)][1];
            $color_index++;
        }
    }
}

// Calculate other votes for pie chart
$other_votes = $total_votes - $my_votes;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Results | EMS Candidate</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Fix canvas height to keep charts visible and consistent */
        canvas {
            height: 300px !important;
        }
    </style>
</head>
<body class="bg-gray-50 flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-purple-800 text-white text-xl flex flex-col shadow-lg">
        <div class="p-6 border-b border-purple-700 flex items-center space-x-3">
            <div class="rounded-full bg-purple-600 w-10 h-10 flex items-center justify-center">
                <i class="fas fa-user-tie text-xl"></i>
            </div>
            <div>
                <div class="text-xl font-bold">EMS Candidate</div>
                <div class="text-xs text-purple-300"><?php echo htmlspecialchars($candidate['full_name'] ?? 'Candidate'); ?></div>
            </div>
        </div>
        
        <nav class="flex-1 p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white transition-colors">
                <i class="fas fa-tachometer-alt w-6"></i>
                <span class="px-3">Dashboard</span>
            </a>
            <a href="active_elections.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white transition-colors">
                <i class="fas fa-vote-yea w-6"></i>
                <span class="px-3">Active Elections</span>
            </a>
            <a href="profile.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white transition-colors">
                <i class="fas fa-user-circle w-6"></i>
                <span class="px-3">My Profile</span>
            </a>
            <a href="results.php" class="flex items-center py-2 px-4 rounded bg-purple-700 text-white">
                <i class="fas fa-chart-bar w-6"></i>
                <span class="px-3">Results</span>
            </a>
            
            <div class="pt-4 mt-6 border-t border-purple-700">
                <a href="logout.php" class="flex items-center py-2 px-4 rounded bg-red-600 hover:bg-red-700 text-white transition-colors">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="px-3">Logout</span>
                </a>
            </div>
        </nav>
        
        <div class="p-4 text-xs text-purple-300">
            <p>Election Management System</p>
            <p>© 2023 All Rights Reserved</p>
        </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-8 overflow-auto">
        <h1 class="text-3xl font-bold mb-2 text-gray-800">Election Results</h1>
        <p class="text-gray-600 mb-6">
            <?= htmlspecialchars($election['title'] ?? 'Unknown Election') ?> 
            (Status: <span class="font-medium"><?= htmlspecialchars(ucfirst($election['status'] ?? 'unknown')) ?></span>)
        </p>

        <?php if (empty($candidates_data)): ?>
            <p class="text-gray-600">No candidates found for this election.</p>
        <?php else: ?>
            <!-- Candidates Table -->
            <div class="bg-white p-6 rounded shadow mb-8 overflow-x-auto">
                <h2 class="text-xl font-semibold mb-4">All Candidates</h2>
                <table class="min-w-full border border-gray-300 divide-y divide-gray-200">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-3 px-4 text-left">Rank</th>
                            <th class="py-3 px-4 text-left">Photo</th>
                            <th class="py-3 px-4 text-left">Candidate</th>
                            <th class="py-3 px-4 text-left">Party</th>
                            <th class="py-3 px-4 text-center">Votes</th>
                            <th class="py-3 px-4 text-center">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php 
                            $rank = 1;
                            foreach ($candidates_data as $cand): 
                                $percentage = $total_votes > 0 ? round(($cand['vote_count'] / $total_votes) * 100, 1) : 0;
                                $is_current_candidate = ($cand['id'] == $candidate_id);
                                $row_class = $is_current_candidate ? 'bg-green-50' : '';
                        ?>
                            <tr class="<?= $row_class ?>">
                                <td class="py-3 px-4"><?= $rank++ ?></td>
                                <td class="py-3 px-4">
                                    <?= getCandidateImageHtml(
                                      $cand['profile_image'], 
                                      $cand['full_name'], 
                                      'w-10 h-10 rounded-full object-cover'
                                    ) ?>
                                </td>
                                <td class="py-3 px-4 font-medium <?= $is_current_candidate ? 'text-green-700' : '' ?>">
                                    <?= htmlspecialchars($cand['full_name']) ?>
                                    <?= $is_current_candidate ? ' (You)' : '' ?>
                                </td>
                                <td class="py-3 px-4"><?= htmlspecialchars($cand['party'] ?: 'Independent') ?></td>
                                <td class="py-3 px-4 text-center font-bold"><?= $cand['vote_count'] ?></td>
                                <td class="py-3 px-4 text-center"><?= $percentage ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Charts container with flex layout -->
            <div class="flex flex-col md:flex-row gap-8 justify-center items-center">
                <!-- Bar chart -->
                <div class="bg-white p-6 rounded shadow max-w-xl w-full">
                    <h2 class="text-xl font-semibold mb-4">Vote Distribution</h2>
                    <canvas id="votesChart" class="w-full"></canvas>
                </div>

                <!-- Pie chart -->
                <div class="bg-white p-6 rounded shadow max-w-md w-full">
                    <h2 class="text-xl font-semibold mb-4">Your Vote Share</h2>
                    <canvas id="pieChart" class="w-full"></canvas>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
    const ctxBar = document.getElementById('votesChart').getContext('2d');
    const votesChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: <?= json_encode($candidate_names) ?>,
            datasets: [{
                label: 'Votes',
                data: <?= json_encode($vote_counts) ?>,
                backgroundColor: 'rgba(128, 0, 128, 0.8)',  // purple fill
                borderColor: 'rgba(192, 43, 192, 1)', 
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    title: { display: true, text: 'Number of Votes' }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: { 
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.x !== null) {
                                label += context.parsed.x + ' votes';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    const ctxPie = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Your Votes', 'Other Votes'],
            datasets: [{
                data: [<?= $my_votes ?>, <?= $other_votes ?>],
                backgroundColor: [
                   'rgba(128, 0, 128, 0.8)',
                    'rgba(121, 14, 27, 0.8)'
                ],
                borderColor: [
                    'rgba(128, 0, 128, 0.8)',
                    'rgba(139, 10, 68, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
             maintainAspectRatio: true,   // keep true for circle
            aspectRatio: 1, 
            plugins: {
                legend: { position: 'bottom', labels: { padding: 15 } },
                tooltip: { 
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} votes (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    </script>

</body>
</html>
