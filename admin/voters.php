<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');

// Initialize stats
$total_voters = 0;
$active_voters = 0;

// Fetch total voters count
$total_voters_result = $conn->query("SELECT COUNT(*) FROM voters");
if ($total_voters_result) {
    $total_voters = $total_voters_result->fetch_row()[0] ?? 0;
    $total_voters_result->free();
}

// Fetch active voters count
$active_voters_result = $conn->query("SELECT COUNT(*) FROM voters WHERE status = 'active'");
if ($active_voters_result) {
    $active_voters = $active_voters_result->fetch_row()[0] ?? 0;
    $active_voters_result->free();
}

// Fetch all voters for listing
$voters_result = $conn->query("SELECT id, full_name, email, status FROM voters ORDER BY id DESC");
if (!$voters_result) {
    die("Database error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Voters | EMS Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-gray-100 font-sans">

<div class="flex min-h-screen">

  <!-- Sidebar -->
  <aside class="w-64 bg-purple-800 text-xl text-white flex flex-col">
    <div class="p-6 text-2xl font-bold border-b border-purple-700">
      EMS Admin
    </div>
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
  <main class="flex-1 p-10">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Voters</h1>
      <a href="add_voter.php" class="bg-purple-700 hover:bg-purple-900 text-xl text-white px-5 py-2 rounded shadow">
        + Add Voter
      </a>
    </div>
    
    <?php if (isset($_GET['message'])): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p><?php echo htmlspecialchars($_GET['message']); ?></p>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p><?php echo htmlspecialchars($_GET['error']); ?></p>
      </div>
    <?php endif; ?>

    <!-- Top Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
      <div class="bg-purple-600 text-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold">Total Voters</h2>
        <p class="mt-2 text-4xl font-bold"><?php echo htmlspecialchars($total_voters); ?></p>
      </div>
      <div class="bg-purple-700 text-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold">Active Voters</h2>
        <p class="mt-2 text-4xl font-bold"><?php echo htmlspecialchars($active_voters); ?></p>
      </div>
    </div>

    <!-- Voters List -->
    <div class="bg-white rounded shadow p-6">
      <table class="min-w-full divide-y divide-gray-200 mt-6">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if ($voters_result->num_rows > 0): ?>
            <?php while ($voter = $voters_result->fetch_assoc()): ?>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($voter['id']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($voter['full_name']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($voter['email']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <?php if ($voter['status'] === 'active'): ?>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                  <?php else: ?>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                  <a href="edit_voter.php?id=<?php echo urlencode($voter['id']); ?>" class="text-purple-700 hover:text-purple-900 bg-purple-100 px-2 py-1 rounded">Edit</a>
                  <a href="delete_voter.php?id=<?php echo urlencode($voter['id']); ?>" 
                     onclick="return confirm('Are you sure you want to delete this voter? This will also delete their account and voting history.');" 
                     class="text-red-600 hover:text-red-900 bg-red-100 px-2 py-1 rounded">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No voters found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

</body>
</html>

<?php
// Free result set and close connection
$voters_result->free();
$conn->close();
?>
