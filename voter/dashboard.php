<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('voter');

// Get voter info with better error handling
$voter_id = $_SESSION['user_id'];
$voter_query = "SELECT v.*, u.username FROM voters v 
                LEFT JOIN users u ON v.user_id = u.id 
                WHERE v.user_id = ?";
$stmt = $conn->prepare($voter_query);
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$voter = $stmt->get_result()->fetch_assoc();

// If no voter record found, create a basic one or handle gracefully
if (!$voter) {
    // Try to get user info directly
    $user_query = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Create a basic voter array
    $voter = [
        'full_name' => $user['username'] ?? 'Voter',
        'voter_id' => 'V' . str_pad($voter_id, 6, '0', STR_PAD_LEFT),
        'email' => '',
        'phone' => '',
        'address' => '',
        'username' => $user['username'] ?? 'Voter'
    ];
}

// Get statistics
$total_elections = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'active'")->fetch_row()[0];
$total_candidates = $conn->query("SELECT COUNT(*) FROM candidates")->fetch_row()[0];
$total_votes = $conn->query("SELECT COUNT(*) FROM votes")->fetch_row()[0];

// Get active elections
$active_elections = $conn->query("SELECT * FROM elections WHERE status = 'active' ORDER BY end_date ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Voter Dashboard | EMS</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-purple-50 font-sans">

<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-purple-800 text-white text-xl flex flex-col shadow-lg">
    <div class="p-6 border-b border-purple-700 flex items-center space-x-3">
      <div class="rounded-full bg-purple-600 w-10 h-10 flex items-center justify-center">
        <i class="fas fa-user text-xl"></i>
      </div>
      <div>
        <div class="text-xl font-bold">EMS Voter</div>
        <div class="text-xs text-purple-300"><?php echo htmlspecialchars($voter['full_name'] ?? 'Voter'); ?></div>
      </div>
    </div>
    
    <nav class="flex-1 p-4 space-y-1">
      <a href="/EMS2/voter/dashboard.php" class="flex items-center py-2 px-4 rounded bg-purple-700 text-white">
        <i class="fas fa-tachometer-alt w-6"></i>
        <span class="px-3">Dashboard</span>
      </a>
      <a href="/EMS2/voter/active_elections.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white transition-colors">
        <i class="fas fa-vote-yea w-6"></i>
        <span class="px-3">Active Elections</span>
      </a>
     
      <a href="profile.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white">
        <i class="fas fa-user-circle w-6"></i>
        <span class="px-3">My Profile</span>
      </a>
      <a href="/EMS2/voter/results.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white transition-colors">
        <i class="fas fa-chart-bar w-6"></i>
        <span class="px-3" >View Results</span>
      </a>
      
      <div class="pt-4 mt-6 border-t border-purple-700">
        <a href="/EMS2/voter/logout.php" class="flex items-center py-2 px-4 rounded bg-red-600 hover:bg-red-700 text-white transition-colors">
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
    <!-- Voter Profile Header -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
      <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
        <h2 class="text-lg font-semibold text-purple-800">Voter Profile</h2>
      </div>
      <div class="p-6">
        <div class="flex items-start space-x-6">
          <?php 
            // Create a nice avatar with the first letter of the name
            $first_letter = strtoupper(substr($voter['full_name'], 0, 1));
            $avatar_colors = ['bg-purple-500', 'bg-blue-500', 'bg-green-500', 'bg-red-500', 'bg-yellow-500', 'bg-indigo-500'];
            $color_index = ord($first_letter) % count($avatar_colors);
            $avatar_color = $avatar_colors[$color_index];
          ?>
          <div class="<?= $avatar_color ?> w-24 h-24 rounded-full flex items-center justify-center text-white text-3xl font-bold border-4 border-purple-100">
            <?= $first_letter ?>
          </div>
          
          <div class="flex-1">
            <h1 class="text-2xl font-bold text-purple-800 mb-2">Welcome, <?php echo htmlspecialchars($voter['full_name']); ?>!</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div class="flex items-center text-gray-600">
                <i class="fas fa-id-card mr-2 text-purple-500"></i>
                <span><strong>Voter ID:</strong> <?php echo htmlspecialchars($voter['voter_id'] ?? 'V' . str_pad($voter_id, 6, '0', STR_PAD_LEFT)); ?></span>
              </div>
              
              <?php if (!empty($voter['email'])): ?>
              <div class="flex items-center text-gray-600">
                <i class="fas fa-envelope mr-2 text-purple-500"></i>
                <span><strong>Email:</strong> <?php echo htmlspecialchars($voter['email']); ?></span>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($voter['phone'])): ?>
              <div class="flex items-center text-gray-600">
                <i class="fas fa-phone mr-2 text-purple-500"></i>
                <span><strong>Phone:</strong> <?php echo htmlspecialchars($voter['phone']); ?></span>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($voter['address'])): ?>
              <div class="flex items-center text-gray-600">
                <i class="fas fa-map-marker-alt mr-2 text-purple-500"></i>
                <span><strong>Address:</strong> <?php echo htmlspecialchars($voter['address']); ?></span>
              </div>
              <?php endif; ?>
            </div>
            
            <div class="flex flex-wrap gap-2">
              <a href="profile.php" class="inline-flex items-center text-sm bg-purple-100 text-purple-700 px-3 py-2 rounded-full hover:bg-purple-200 transition-colors">
                <i class="fas fa-edit mr-1"></i> Edit Profile
              </a>
              <span class="inline-flex items-center text-sm bg-green-100 text-green-700 px-3 py-2 rounded-full">
                <i class="fas fa-check-circle mr-1"></i> Verified Voter
              </span>
              <span class="inline-flex items-center text-sm bg-blue-100 text-blue-700 px-3 py-2 rounded-full">
                <i class="fas fa-calendar mr-1"></i> Registered: <?php echo date('M Y', strtotime($voter['created_at'] ?? 'now')); ?>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
        <div class="flex items-center">
          <div class="rounded-full bg-purple-100 p-3 mr-4">
            <i class="fas fa-vote-yea text-purple-600 text-xl"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">Active Elections</div>
            <div class="text-2xl font-bold text-purple-800"><?php echo $total_elections; ?></div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
        <div class="flex items-center">
          <div class="rounded-full bg-green-100 p-3 mr-4">
            <i class="fas fa-user-tie text-green-600 text-xl"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">Total Candidates</div>
            <div class="text-2xl font-bold text-green-800"><?php echo $total_candidates; ?></div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
          <div class="rounded-full bg-blue-100 p-3 mr-4">
            <i class="fas fa-poll-h text-blue-600 text-xl"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">Total Votes Cast</div>
            <div class="text-2xl font-bold text-blue-800"><?php echo $total_votes; ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Active Elections -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
      <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
        <h2 class="text-lg font-semibold text-purple-800">Active Elections</h2>
      </div>
      <div class="p-6">
        <?php if ($active_elections && $active_elections->num_rows > 0): ?>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php while ($election = $active_elections->fetch_assoc()): ?>
              <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($election['title']); ?></h3>
                  <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
                </div>
                <div class="text-sm text-gray-600 mb-3">
                  <p><i class="fas fa-calendar mr-1"></i> Ends: <?php echo date('M j, Y', strtotime($election['end_date'])); ?></p>
                  <p><i class="fas fa-clock mr-1"></i> <?php echo htmlspecialchars($election['description'] ?? 'No description'); ?></p>
                </div>
                <a href="active_elections.php" class="inline-flex items-center text-sm bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700 transition-colors">
                  <i class="fas fa-vote-yea mr-1"></i> Vote Now
                </a>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <p class="text-gray-500 text-center py-4">No active elections at the moment.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
      <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
        <h2 class="text-lg font-semibold text-purple-800">Quick Actions</h2>
      </div>
      <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="active_elections.php" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
          <div class="rounded-full bg-purple-100 p-3 mr-4">
            <i class="fas fa-vote-yea text-purple-600"></i>
          </div>
          <div>
            <p class="font-medium text-purple-800">Vote Now</p>
            <p class="text-xs text-purple-600">Participate in active elections</p>
          </div>
        </a>
        
        <a href="profile.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
          <div class="rounded-full bg-green-100 p-3 mr-4">
            <i class="fas fa-user-edit text-green-600"></i>
          </div>
          <div>
            <p class="font-medium text-green-800">Update Profile</p>
            <p class="text-xs text-green-600">Edit your information</p>
          </div>
        </a>
        
        <a href="results.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
          <div class="rounded-full bg-blue-100 p-3 mr-4">
            <i class="fas fa-chart-pie text-blue-600"></i>
          </div>
          <div>
            <p class="font-medium text-blue-800">View Results</p>
            <p class="text-xs text-blue-600">Check election results</p>
          </div>
        </a>
        
        <a href="register_candidate.php" class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
          <div class="rounded-full bg-orange-100 p-3 mr-4">
            <i class="fas fa-user-tie text-orange-600"></i>
          </div>
          <div>
            <p class="font-medium text-orange-800">Register as Candidate</p>
            <p class="text-xs text-orange-600">Run for election</p>
          </div>
        </a>
      </div>
    </div>
  </main>
</div>
</body>
</html>
