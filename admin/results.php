<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');

// Fetch vote counts per candidate
$sql = "
    SELECT 
        c.full_name AS candidate_name,
        COUNT(v.id) AS vote_count
    FROM candidates c
    LEFT JOIN votes v ON c.id = v.candidate_id
    GROUP BY c.id, c.full_name
    ORDER BY vote_count DESC
";

$result = $conn->query($sql);

$candidateNames = [];
$voteCounts = [];
$totalVotes = 0;

while ($row = $result->fetch_assoc()) {
    $candidateNames[] = $row['candidate_name'];
    $voteCounts[] = (int)$row['vote_count'];
    $totalVotes += (int)$row['vote_count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Results | EMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        canvas { height: 300px !important; }
    </style>
</head>
<body class="h-full flex bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-purple-800 text-xl text-white flex flex-col">
        <div class="p-6 text-2xl font-bold border-b border-purple-700">EMS Admin</div>
          <nav class="flex-1 p-4 space-y-1">
        <a href="dashboard.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
          <i class="fas fa-tachometer-alt w-6"></i>
          <span class="px-4">Dashboard</span>
        </a>
        <a href="elections.php" class="flex items-center py-2 px-4 rounded bg-purple-700 text-white">
          <i class="fas fa-vote-yea w-6"></i>
          <span class="px-4">Elections</span>
        </a>
        <a href="candidates.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
          <i class="fas fa-user-tie w-6"></i>
          <span class="px-4">Candidates</span>
        </a>
        <a href="voters.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
          <i class="fas fa-users w-6"></i>
          <span class="px-4">Voters</span>
        </a>
        <a href="results.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
          <i class="fas fa-chart-bar w-6"></i>
          <span class="px-4">Results</span>
        </a>
        <a href="messages.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
          <i class="fas fa-envelope w-6"></i>
          <span class="px-4">Messages</span>
        </a>
        <a href="settings.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
          <i class="fas fa-cog w-6"></i>
          <span class="px-4">Settings</span>
        </a>
        
        <div class="pt-4 mt-6 border-t border-purple-700">
          <a href="logout.php" class="flex items-center py-2 px-4 rounded bg-red-600 hover:bg-red-700 text-white">
            <i class="fas fa-sign-out-alt w-6"></i>
            <span class="px-4">Logout</span>
          </a>
        </div>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-10 overflow-auto">
       

        <h1 class="text-3xl font-bold mb-6 text-purple-800">Election Results</h1>

        <?php if (empty($candidateNames)): ?>
            <p class="text-gray-600">No votes have been cast yet.</p>
        <?php else: ?>
            <!-- Table -->
            <div class="bg-white p-6 rounded shadow mb-8 overflow-x-auto">
                <h2 class="text-xl font-semibold mb-4 text-purple-800">Votes by Numbers</h2>
                <table class="min-w-full border border-gray-300 divide-y divide-gray-200">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-3 px-6 text-left">Candidate</th>
                            <th class="py-3 px-6 text-center">Votes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php foreach ($candidateNames as $index => $name): ?>
                            <tr>
                                <td class="py-3 px-6"><?= htmlspecialchars($name) ?></td>
                                <td class="py-3 px-6 text-center"><?= $voteCounts[$index] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="font-semibold bg-gray-100">
                            <td class="py-3 px-6">Total</td>
                            <td class="py-3 px-6 text-center"><?= $totalVotes ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Charts -->
            <div class="flex flex-col md:flex-row gap-8 justify-center items-center">
                <!-- Bar Chart -->
                <div class="bg-white p-6 rounded shadow w-full max-w-2xl">
                    <h2 class="text-xl font-semibold mb-4 text-purple-800">Votes per Candidate</h2>
                    <canvas id="barChart"></canvas>
                </div>

                <!-- Pie Chart -->
               <div class="bg-white p-6 rounded shadow w-full max-w-md">
    <h2 class="text-xl font-semibold mb-4 text-purple-800">Vote Share</h2>
    <div class="flex justify-center text-purple-600">
        <canvas id="pieChart" class="w-64 h-64"></canvas>
    </div>
</div>

        <?php endif; ?>
    </main>

    <!-- Chart JS -->
    <script>
        const candidateNames = <?= json_encode($candidateNames) ?>;
        const voteCounts = <?= json_encode($voteCounts) ?>;

        // Bar Chart
        new Chart(document.getElementById('barChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: candidateNames,
                datasets: [{
                    label: 'Votes',
                    data: voteCounts,
                    backgroundColor: 'rgba(124, 34, 140, 0.7)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Pie Chart
        new Chart(document.getElementById('pieChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: candidateNames,
                datasets: [{
                    data: voteCounts,
                   backgroundColor: candidateNames.map((_, i) => `hsl(270, 70%, ${40 + i * 10}%)`), 
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>
