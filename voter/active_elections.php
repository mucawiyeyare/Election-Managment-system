<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('voter');

// Get logged-in user ID from session
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Fetch voter_id from voters table using user_id
$stmt = $conn->prepare("SELECT id FROM voters WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($voter_id);
if (!$stmt->fetch()) {
    // No voter found for this user_id - handle error or redirect
    $stmt->close();
    die("Error: Voter profile not found.");
}
$stmt->close();

// Handle vote submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'], $_POST['election_id'])) {
    $candidate_id = (int)$_POST['candidate_id'];
    $election_id = (int)$_POST['election_id'];

    // Check if voter already voted in this election
    $check_vote = $conn->prepare("SELECT COUNT(*) FROM votes WHERE voter_id = ? AND election_id = ?");
    $check_vote->bind_param("ii", $voter_id, $election_id);
    $check_vote->execute();
    $check_vote->bind_result($vote_count);
    $check_vote->fetch();
    $check_vote->close();

    if ($vote_count > 0) {
        $message = "You have already voted in this election.";
    } else {
        // Insert the vote
        $insert_vote = $conn->prepare("INSERT INTO votes (voter_id, candidate_id, election_id) VALUES (?, ?, ?)");
        $insert_vote->bind_param("iii", $voter_id, $candidate_id, $election_id);
        if ($insert_vote->execute()) {
            $message = "Your vote has been cast successfully!";
        } else {
            $message = "Failed to record your vote. Please try again.";
        }
        $insert_vote->close();
    }
}

// Fetch election stats
$active_elections = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'active'")->fetch_row()[0];
$total_elections = $conn->query("SELECT COUNT(*) FROM elections")->fetch_row()[0];
$upcoming_elections = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'upcoming'")->fetch_row()[0];

// Fetch all active elections
$active_elections_result = $conn->query("SELECT * FROM elections WHERE status = 'active' ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Active Elections | EMS Voter</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-gray-100 font-sans">

<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-purple-800 text-white text-xl flex flex-col">
    <div class="p-6 border-b border-purple-700 flex items-center space-x-3">
      <div class="rounded-full bg-purple-600 w-10 h-10 flex items-center justify-center">
        <i class="fas fa-user-check text-xl"></i>
      </div>
      <div>
        <div class="text-xl font-bold">EMS Voter</div>
        <div class="text-xs text-purple-300"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Voter'); ?></div>
      </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-1">
      <a href="dashboard.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
        <i class="fas fa-tachometer-alt w-6"></i>
        <span class="px-3">Dashboard</span>
      </a>
      <a href="active_elections.php" class="flex items-center py-2 px-4 rounded bg-purple-700 text-white">
        <i class="fas fa-vote-yea w-6"></i>
        <span class="px-3">Active Elections</span>
      </a>
      <a href="profile.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
        <i class="fas fa-user-circle w-6"></i>
        <span class="px-3">My Profile</span>
      </a>
      <a href="results.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
        <i class="fas fa-chart-bar w-6"></i>
        <span class="px-3">View Results</span>
      </a>
      
      <div class="pt-4 mt-6 border-t border-purple-700">
        <a href="logout.php" class="flex items-center py-2 px-4 rounded bg-red-600 hover:bg-red-700 text-white">
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
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800 flex items-center">
        <i class="fas fa-vote-yea text-purple-600 mr-2"></i> Active Elections
      </h1>
      <div class="text-sm text-gray-600">
        <?php echo date('l, F j, Y'); ?>
      </div>
    </div>

    <!-- Dashboard Cards -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
        <div class="flex items-center">
          <div class="rounded-full bg-purple-100 p-3 mr-4">
            <i class="fas fa-vote-yea text-purple-500 text-xl"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">Active Elections</div>
            <div class="text-2xl font-bold text-purple-700"><?= $active_elections ?></div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-gray-500">
        <div class="flex items-center">
          <div class="rounded-full bg-gray-100 p-3 mr-4">
            <i class="fas fa-poll-h text-gray-500 text-xl"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">Total Elections</div>
            <div class="text-2xl font-bold text-gray-800"><?= $total_elections ?></div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
          <div class="rounded-full bg-yellow-100 p-3 mr-4">
            <i class="fas fa-calendar-alt text-yellow-500 text-xl"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">Upcoming Elections</div>
            <div class="text-2xl font-bold text-yellow-600"><?= $upcoming_elections ?></div>
          </div>
        </div>
      </div>
    </section>

    <?php if (!empty($message)): ?>
      <div class="mb-6 p-4 rounded <?= strpos($message, 'successfully') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if ($active_elections == 0): ?>
      <p class="text-gray-600">There are no active elections at the moment.</p>
    <?php else: ?>

      <?php while ($election = $active_elections_result->fetch_assoc()): ?>
        <section class="mb-8 bg-white rounded-lg shadow overflow-hidden">
          <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
            <h2 class="text-lg font-semibold text-purple-800 flex items-center">
              <i class="fas fa-vote-yea text-purple-600 mr-2"></i>
              <?= htmlspecialchars($election['title'] ?? "Election #{$election['id']}") ?>
            </h2>
          </div>
          <div class="p-6">
            <p class="text-gray-600 mb-6"><?= htmlspecialchars($election['description'] ?? '') ?></p>

          <?php
          // Fetch candidates for this election
          $stmt = $conn->prepare("SELECT id, full_name, party, profile_image FROM candidates WHERE election_id = ?");
          $stmt->bind_param("i", $election['id']);
          $stmt->execute();
          $candidates_result = $stmt->get_result();
          ?>

          <?php if ($candidates_result->num_rows === 0): ?>
            <p class="text-gray-500">No candidates registered for this election.</p>
          <?php else: ?>
            <div class="overflow-x-auto">
              <table class="min-w-full border border-gray-300 divide-y divide-gray-200">
                <thead class="bg-purple-100">
                  <tr>
                    <th class="py-3 px-6 text-left text-purple-800"><i class="fas fa-image text-purple-500 mr-1"></i> Photo</th>
                    <th class="py-3 px-6 text-left text-purple-800"><i class="fas fa-user-tie text-purple-500 mr-1"></i> Name</th>
                    <th class="py-3 px-6 text-left text-purple-800"><i class="fas fa-flag text-purple-500 mr-1"></i> Party</th>
                    <th class="py-3 px-6 text-center text-purple-800"><i class="fas fa-check-square text-purple-500 mr-1"></i> Vote</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                  <?php while ($candidate = $candidates_result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                      <td class="py-3 px-6">
                        <?= getCandidateImageHtml(
                          $candidate['profile_image'], 
                          $candidate['full_name'], 
                          'w-12 h-12 rounded-full object-cover'
                        ) ?>
                      </td>
                      <td class="py-3 px-6"><?= htmlspecialchars($candidate['full_name']) ?></td>
                      <td class="py-3 px-6"><?= htmlspecialchars($candidate['party']) ?></td>

                      <td class="py-3 px-6 text-center">
                        <form method="post" onsubmit="return confirm('Are you sure you want to vote for <?= addslashes(htmlspecialchars($candidate['full_name'])) ?>?');">
                          <input type="hidden" name="candidate_id" value="<?= (int)$candidate['id'] ?>" />
                          <input type="hidden" name="election_id" value="<?= (int)$election['id'] ?>" />
                          <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-1 rounded-md shadow-sm flex items-center mx-auto">
                            <i class="fas fa-check-circle mr-1"></i> Vote
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
          <?php $stmt->close(); ?>
          
          <div class="mt-6 flex justify-end">
            <a href="results.php?election_id=<?= $election['id'] ?>" class="inline-flex items-center bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md shadow-sm">
              <i class="fas fa-chart-bar mr-2"></i> View Results
            </a>
          </div>
          </div>
        </section>
      <?php endwhile; ?>
      <?php $active_elections_result->free(); ?>

    <?php endif; ?>
  </main>
</div>

</body>
</html>
