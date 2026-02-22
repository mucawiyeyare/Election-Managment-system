<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('candidate');

$user_id = $_SESSION['user_id'];
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Get candidate information using prepared statement
$stmt = $conn->prepare("SELECT id, full_name, profile_image, election_id, party, manifesto FROM candidates WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['error'] = "Candidate profile not found.";
    header("Location: login.php");
    exit;
}

$stmt->bind_result($candidate_id, $full_name, $profile_image, $election_id, $party, $manifesto);
$stmt->fetch();
$stmt->close();

// Get election information
$election = [];
if ($election_id) {
    $stmt = $conn->prepare("SELECT title, status, start_date, end_date FROM elections WHERE id = ?");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $election_result = $stmt->get_result();
    $election = $election_result->fetch_assoc();
    $stmt->close();
}

// Get the candidate's vote count
$vote_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as vote_count FROM votes WHERE candidate_id = ?");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$vote_result = $stmt->get_result()->fetch_assoc();
$vote_count = $vote_result['vote_count'] ?? 0;
$stmt->close();

// Get total votes for this election
$total_votes = 0;
if ($election_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total_votes FROM votes WHERE election_id = ?");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $total_votes_result = $stmt->get_result()->fetch_assoc();
    $total_votes = $total_votes_result['total_votes'] ?? 0;
    $stmt->close();
}

$vote_percentage = $total_votes > 0 ? round(($vote_count / $total_votes) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Candidate Dashboard | EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-purple-800 text-white text-xl flex flex-col">
            <div class="p-6 border-b border-purple-700 flex items-center space-x-3">
                <?= getCandidateImageHtml(
                    $profile_image, 
                    $full_name, 
                    'w-10 h-10 rounded-full object-cover'
                ) ?>
                <div>
                    <div class="font-semibold"><?= htmlspecialchars($full_name) ?></div>
                    <div class="text-xs text-purple-300">Candidate</div>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="dashboard.php" class="flex items-center py-2 px-4 rounded bg-purple-700 text-white">
                    <i class="fas fa-tachometer-alt w-6"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="active_elections.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
                    <i class="fas fa-user-circle w-6"></i>
                    <span class="ml-2">Active Elections</span>
                </a>
                <a href="profile.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
                    <i class="fas fa-user-circle w-6"></i>
                    <span class="ml-2">My Profile</span>
                </a>
                 <a href="results.php" class="flex items-center py-2 px-4 rounded bg-purple-700 text-white">
                <i class="fas fa-chart-bar w-6"></i>
                <span class="px-3">Results</span>
            </a>
               
                <a href="logout.php" class="flex items-center py-2 px-4 rounded bg-red-600 hover:bg-red-700 text-white transition-colors">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="px-3">Logout</span>
                </a>
            
            </nav>
            <div class="p-4 text-xs text-purple-300 border-t border-purple-700">
                <p>Election Management System</p>
                <p>&copy; 2023 All Rights Reserved</p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Welcome, <?= htmlspecialchars($full_name) ?></h1>
                <div class="text-sm text-gray-600">
                    <?= date('l, F j, Y') ?>
                </div>
            </div>

            <?php if (!empty($election)): ?>
                <!-- Candidate Profile Card -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
                    <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                        <h2 class="text-lg font-semibold text-blue-800">Your Profile</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row">
                            <div class="md:w-1/4 flex justify-center mb-4 md:mb-0">
                                <?= getCandidateImageHtml(
                                    $profile_image, 
                                    $full_name, 
                                    'w-32 h-32 rounded-full object-cover border-4 border-purple-200'
                                ) ?>
                            </div>
                            <div class="md:w-3/4 md:pl-8">
                                <h3 class="text-xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($full_name) ?></h3>
                                <?php if (!empty($party)): ?>
                                    <p class="text-gray-600 mb-2">
                                        <i class="fas fa-flag text-purple-500 mr-2"></i>
                                        <?= htmlspecialchars($party) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($election['title'])): ?>
                                    <p class="text-gray-600 mb-4">
                                        <i class="fas fa-vote-yea text-purple-500 mr-2"></i>
                                        Running in: <?= htmlspecialchars($election['title']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($manifesto)): ?>
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <h4 class="font-medium text-gray-700 mb-2">Your Manifesto</h4>
                                        <p class="text-gray-600"><?= nl2br(htmlspecialchars($manifesto)) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Election Stats Card -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
                    <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                        <h2 class="text-lg font-semibold text-blue-800">Your Campaign Statistics</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="text-3xl font-bold text-purple-600 mb-1"><?= number_format($vote_count) ?></div>
                                <div class="text-sm text-gray-500">Total Votes</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="text-3xl font-bold text-purple-600 mb-1"><?= $vote_percentage ?>%</div>
                                <div class="text-sm text-gray-500">Vote Share</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="text-3xl font-bold text-purple-600 mb-1">
                                    <?= !empty($election['status']) ? ucfirst(htmlspecialchars($election['status'])) : 'N/A' ?>
                                </div>
                                <div class="text-sm text-gray-500">Election Status</div>
                            </div>
                        </div>

                        <?php if ($election['status'] === 'active'): ?>
                            <div class="mt-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Campaign Progress</h4>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?= min($vote_percentage, 100) ?>%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    You have <?= $vote_count ?> votes (<?= $vote_percentage ?>% of total votes)
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Election Timeline -->
                <?php if (!empty($election['start_date']) && !empty($election['end_date'])): ?>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                            <h2 class="text-lg font-semibold text-blue-800">Election Timeline</h2>
                        </div>
                        <div class="p-6">
                            <div class="relative">
                                <!-- Timeline -->
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                                        Start: <?= date('M j, Y', strtotime($election['start_date'])) ?>
                                    </div>
                                    <div class="text-sm font-medium text-gray-700">
                                        <i class="fas fa-calendar-check text-purple-500 mr-2"></i>
                                        End: <?= date('M j, Y', strtotime($election['end_date'])) ?>
                                    </div>
                                </div>
                                
                                <!-- Progress bar -->
                                <?php 
                                $now = time();
                                $start_date = strtotime($election['start_date']);
                                $end_date = strtotime($election['end_date']);
                                $total_duration = $end_date - $start_date;
                                $elapsed = $now - $start_date;
                                $progress = $total_duration > 0 ? min(100, max(0, ($elapsed / $total_duration) * 100)) : 0;
                                ?>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-green-500 h-2.5 rounded-full" style="width: <?= $progress ?>%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>Started</span>
                                    <span>Ends in <?= ceil(($end_date - $now) / (60 * 60 * 24)) ?> days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                You are not currently assigned to any active election.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
