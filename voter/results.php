<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('voter');

$voter_id = $_SESSION['user_id'];

if (empty($voter_id)) {
    die("Voter ID is not set in session.");
}

// Get voter's actual voter_id from voters table
$voter_query = "SELECT id FROM voters WHERE user_id = ?";
$voter_stmt = $conn->prepare($voter_query);
$voter_stmt->bind_param("i", $voter_id);
$voter_stmt->execute();
$voter_result = $voter_stmt->get_result();
$voter_data = $voter_result->fetch_assoc();
$actual_voter_id = $voter_data['id'] ?? $voter_id;
$voter_stmt->close();

// Check if this voter has any votes
$vote_count_query = "SELECT COUNT(*) as vote_count FROM votes WHERE voter_id = ?";
$vote_stmt = $conn->prepare($vote_count_query);
$vote_stmt->bind_param("i", $actual_voter_id);
$vote_stmt->execute();
$vote_result = $vote_stmt->get_result();
$vote_count = $vote_result->fetch_assoc()['vote_count'];
$vote_stmt->close();

// Get total votes in system
$total_votes_query = "SELECT COUNT(*) as total_votes FROM votes";
$total_votes_result = $conn->query($total_votes_query);
$total_votes = $total_votes_result->fetch_assoc()['total_votes'];

// Get all elections with their results
$elections_query = "SELECT * FROM elections ORDER BY id DESC";
$elections_result = $conn->query($elections_query);

// Get candidates the voter has voted for
$voter_votes_query = "
    SELECT DISTINCT candidate_id, election_id 
    FROM votes 
    WHERE voter_id = ?
";
$voter_votes_stmt = $conn->prepare($voter_votes_query);
$voter_votes_stmt->bind_param("i", $actual_voter_id);
$voter_votes_stmt->execute();
$voter_votes_result = $voter_votes_stmt->get_result();
$voter_votes = [];
while ($vote = $voter_votes_result->fetch_assoc()) {
    $voter_votes[$vote['election_id']][] = $vote['candidate_id'];
}
$voter_votes_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Results | EMS Voter</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 font-sans">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-purple-800 text-white text-xl flex flex-col shadow-lg">
        <div class="p-6 border-b border-purple-700 flex items-center space-x-3">
            <div class="rounded-full bg-purple-600 w-10 h-10 flex items-center justify-center">
                <i class="fas fa-user text-xl"></i>
            </div>
            <div>
                <div class="text-xl font-bold">EMS Voter</div>
                <div class="text-xs text-purple-300"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Voter'); ?></div>
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
                <span class="px-3">View Results</span>
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

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
                <h1 class="text-2xl font-semibold text-purple-800">Election Results</h1>
                <p class="text-sm text-purple-600 mt-1">View complete results for all elections and candidates</p>
            </div>
            
            <div class="p-6">
                <!-- Voting Summary -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="font-medium">Voting Summary:</span>
                    </div>
                    <div class="text-blue-600 mt-2 space-y-1">
                        <p>• You have cast <strong><?= $vote_count ?></strong> vote(s)</p>
                        <p>• Total votes in system: <strong><?= $total_votes ?></strong></p>
                        <?php if ($vote_count == 0): ?>
                            <p class="text-orange-600 font-medium">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                You haven't voted yet. Visit the Active Elections page to participate!
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Election Results -->
                <?php if ($elections_result && $elections_result->num_rows > 0): ?>
                    <?php while ($election = $elections_result->fetch_assoc()): ?>
                        <div class="mb-8 bg-gray-50 rounded-lg overflow-hidden">
                            <div class="bg-purple-100 px-6 py-4 border-b border-purple-200">
                                <h2 class="text-xl font-semibold text-purple-800 flex items-center">
                                    <i class="fas fa-trophy text-purple-600 mr-2"></i>
                                    <?= htmlspecialchars($election['title']) ?>
                                </h2>
                                <p class="text-sm text-purple-600 mt-1"><?= htmlspecialchars($election['description'] ?? '') ?></p>
                                <div class="flex items-center mt-2 text-sm text-purple-700">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <span>Status: <strong><?= ucfirst($election['status']) ?></strong></span>
                                    <?php if ($election['end_date']): ?>
                                        <span class="ml-4">
                                            <i class="fas fa-clock mr-1"></i>
                                            Ends: <?= date('M j, Y', strtotime($election['end_date'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <?php
                                // Get candidates for this election with vote counts
                                $candidates_query = "
                                    SELECT 
                                        c.id,
                                        c.full_name,
                                        c.party,
                                        c.profile_image,
                                        COUNT(v.id) as vote_count,
                                        (SELECT COUNT(*) FROM votes v2 
                                         JOIN candidates c2 ON v2.candidate_id = c2.id 
                                         WHERE c2.election_id = ?) as total_election_votes
                                    FROM candidates c
                                    LEFT JOIN votes v ON c.id = v.candidate_id
                                    WHERE c.election_id = ?
                                    GROUP BY c.id, c.full_name, c.party, c.profile_image
                                    ORDER BY vote_count DESC, c.full_name ASC
                                ";
                                $candidates_stmt = $conn->prepare($candidates_query);
                                $candidates_stmt->bind_param("ii", $election['id'], $election['id']);
                                $candidates_stmt->execute();
                                $candidates_result = $candidates_stmt->get_result();
                                ?>
                                
                                <?php if ($candidates_result->num_rows > 0): ?>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="py-3 px-4 text-left text-gray-700 font-medium">Rank</th>
                                                    <th class="py-3 px-4 text-left text-gray-700 font-medium">Photo</th>
                                                    <th class="py-3 px-4 text-left text-gray-700 font-medium">Candidate</th>
                                                    <th class="py-3 px-4 text-left text-gray-700 font-medium">Party</th>
                                                    <th class="py-3 px-4 text-left text-gray-700 font-medium">Votes</th>
                                                    <th class="py-3 px-4 text-left text-gray-700 font-medium">Percentage</th>
                                                    <th class="py-3 px-4 text-left text-gray-700 font-medium">Your Vote</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $rank = 1;
                                                $prev_votes = -1;
                                                $actual_rank = 1;
                                                while ($candidate = $candidates_result->fetch_assoc()): 
                                                    if ($candidate['vote_count'] != $prev_votes) {
                                                        $actual_rank = $rank;
                                                    }
                                                    $prev_votes = $candidate['vote_count'];
                                                    
                                                    $percentage = $candidate['total_election_votes'] > 0 
                                                        ? round(($candidate['vote_count'] / $candidate['total_election_votes']) * 100, 1) 
                                                        : 0;
                                                    
                                                    $voted_for_this = isset($voter_votes[$election['id']]) && 
                                                                     in_array($candidate['id'], $voter_votes[$election['id']]);
                                                    
                                                    $row_class = $voted_for_this ? 'bg-green-50 border-green-200' : 'hover:bg-gray-50';
                                                ?>
                                                    <tr class="<?= $row_class ?> transition-colors">
                                                        <td class="border-b border-gray-200 py-3 px-4">
                                                            <?php if ($actual_rank == 1 && $candidate['vote_count'] > 0): ?>
                                                                <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                                                    <i class="fas fa-crown mr-1"></i> #<?= $actual_rank ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-gray-600 font-medium">#<?= $actual_rank ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="border-b border-gray-200 py-3 px-4">
                                                            <?= getCandidateImageHtml(
                                                                $candidate['profile_image'], 
                                                                $candidate['full_name'], 
                                                                'w-12 h-12 rounded-full object-cover border-2 border-gray-200'
                                                            ) ?>
                                                        </td>
                                                        <td class="border-b border-gray-200 py-3 px-4">
                                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($candidate['full_name']) ?></div>
                                                        </td>
                                                        <td class="border-b border-gray-200 py-3 px-4">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                <?= htmlspecialchars($candidate['party'] ?: 'Independent') ?>
                                                            </span>
                                                        </td>
                                                        <td class="border-b border-gray-200 py-3 px-4">
                                                            <div class="flex items-center">
                                                                <i class="fas fa-vote-yea text-purple-500 mr-2"></i>
                                                                <span class="font-semibold text-purple-700"><?= $candidate['vote_count'] ?></span>
                                                                <span class="text-gray-500 ml-1">votes</span>
                                                            </div>
                                                        </td>
                                                        <td class="border-b border-gray-200 py-3 px-4">
                                                            <div class="flex items-center">
                                                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                                    <div class="bg-purple-600 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                                                </div>
                                                                <span class="text-sm font-medium text-gray-700"><?= $percentage ?>%</span>
                                                            </div>
                                                        </td>
                                                        <td class="border-b border-gray-200 py-3 px-4">
                                                            <?php if ($voted_for_this): ?>
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                    <i class="fas fa-check-circle mr-1"></i>
                                                                    You Voted
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-gray-400 text-xs">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php 
                                                $rank++;
                                                endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500 text-center py-4">No candidates registered for this election.</p>
                                <?php endif; ?>
                                <?php $candidates_stmt->close(); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-12">
                        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-vote-yea text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Elections Available</h3>
                        <p class="text-gray-600 mb-6">There are no elections in the system yet. Check back later for upcoming elections.</p>
                        <a href="active_elections.php" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-vote-yea mr-2"></i>
                            View Active Elections
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
