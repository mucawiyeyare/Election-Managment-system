<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');

// Handle Add Election
$add_success = false;
$add_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_election'])) {
    $title = trim($_POST['title']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if ($title && $start_date && $end_date) {
        $status = (strtotime($start_date) > time()) ? 'upcoming' :
                 ((strtotime($end_date) >= time()) ? 'active' : 'ended');

        $stmt = $conn->prepare(
            'INSERT INTO elections (title, description, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssss', $title, $description, $start_date, $end_date, $status);

        if ($stmt->execute()) {
            $add_success = true;
        } else {
            $add_error = 'Failed to add election.';
        }

        $stmt->close();
    } else {
        $add_error = 'Please fill all required fields.';
    }
}

// Handle Make Inactive action
if (isset($_GET['action']) && $_GET['action'] === 'make_inactive' && isset($_GET['id'])) {
    $election_id = (int) $_GET['id'];
    if ($election_id > 0) {
        $stmt = $conn->prepare("UPDATE elections SET status = 'inactive' WHERE id = ?");
        $stmt->bind_param('i', $election_id);
        if ($stmt->execute()) {
            header('Location: elections.php?message=' . urlencode('Election marked as inactive.'));
            exit;
        } else {
            header('Location: elections.php?error=' . urlencode('Failed to update election status.'));
            exit;
        }
    }
}

// Fetch Statistics
$total_elections    = $conn->query("SELECT COUNT(*) FROM elections")->fetch_row()[0];
$active_elections   = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'active'")->fetch_row()[0];
$upcoming_elections = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'upcoming'")->fetch_row()[0];

// Fetch All Elections
$elections = $conn->query("SELECT * FROM elections ORDER BY start_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elections | EMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <script>
        function toggleModal() {
            document.getElementById('addElectionModal').classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-gray-100 text-gray-800">

<!-- Layout: Sidebar + Main Content -->
<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-purple-800 text-white text-xl flex flex-col">
      <div class="p-6 border-b border-purple-700 flex items-center space-x-3">
        <div class="rounded-full bg-purple-600 w-10 h-10 flex items-center justify-center">
          <i class="fas fa-user-shield text-xl"></i>
        </div>
        <div>
          <div class="text-xl font-bold">EMS Admin</div>
          <div class="text-xs text-purple-300"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Administrator'); ?></div>
        </div>
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
      
      <div class="p-4 text-xs text-purple-300">
        <p>Election Management System</p>
        <p>© 2023 All Rights Reserved</p>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
       

        <div class="container mx-auto">

            <!-- Stats Cards -->
            <div class="flex flex-col md:flex-row md:space-x-6 mb-6">
                <div class="bg-purple-600 p-6 w-80 h-30 rounded-2xl shadow-lg text-center text-white mb-4 md:mb-0">
              <div class="text-xl opacity-80">Total Elections</div>
           <div class="text-3xl font-bold"><?php echo $total_elections; ?></div>
          </div>

    <!-- Active Elections -->
    <div class="bg-purple-700 p-6 w-80 h-30 rounded-2xl shadow-lg text-center text-white mb-4 md:mb-0">
        <div class="text-xl opacity-80">Active Elections</div>
        <div class="text-3xl font-bold"><?php echo $active_elections; ?></div>
    </div>

    <!-- Upcoming Elections -->
    <div class="bg-purple-600 p-6 w-80 h-30 rounded-2xl shadow-lg text-center text-white">
        <div class="text-xl opacity-80">Upcoming Elections</div>
        <div class="text-3xl font-bold"><?php echo $upcoming_elections; ?></div>
    </div>
</div>

            <!-- Page Heading + Add Button -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Elections</h1>
                <button onclick="toggleModal()" class="bg-purple-700 hover:bg-purple-800 text-white text-xl px-4 py-2 rounded">Add Election</button>
            </div>

            <!-- Success/Error Message -->
            <?php if ($add_success): ?>
                <div class="bg-green-100 text-green-800 p-4 rounded mb-4">Election added successfully!</div>
            <?php elseif ($add_error): ?>
                <div class="bg-red-100 text-red-800 p-4 rounded mb-4"><?php echo $add_error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="bg-green-100 text-green-800 p-4 rounded mb-4"><?php echo htmlspecialchars($_GET['message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 text-red-800 p-4 rounded mb-4"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <!-- Elections Table -->
            <div class="bg-white p-6 rounded shadow overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Position</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Start Date</th>
                            <th class="px-4 py-2 text-left">End Date</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $elections->fetch_assoc()): ?>
                            <tr class="border-t">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td class="px-4 py-2 capitalize"><?php echo htmlspecialchars($row['status']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['start_date']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($row['end_date']); ?></td>
                                <td class="px-4 py-2">
                                    <a href="edit_election.php?id=<?php echo $row['id']; ?>" class="bg-purple-100 text-purple-700 px-2 py-1 rounded hover:bg-purple-200 mr-2">Edit</a>
                                    <a href="delete_election.php?id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this election? This will also delete all candidates and votes associated with this election.');" 
                                       class="bg-red-100 text-red-600 px-2 py-1 rounded hover:bg-red-200">Delete</a>
                                    <?php if ($row['status'] !== 'inactive' && $row['status'] !== 'ended'): ?>
                                        <a href="elections.php?action=make_inactive&id=<?= (int)$row['id'] ?>"
                                           onclick="return confirm('Mark this election as inactive?');"
                                           class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded hover:bg-yellow-200 ml-2">Inactivate</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Add Election Modal -->
<div id="addElectionModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 hidden">
    <div class="bg-white p-8 rounded shadow max-w-md w-full relative">
        <button onclick="toggleModal()" class="absolute top-2 right-2 text-gray-500">&times;</button>
        <h2 class="text-2xl font-bold mb-4">Add Election</h2>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="add_election" value="1">
            <div>
                <label class="block text-gray-700">Position</label>
                <input type="text" name="title" required class="w-full border p-2 rounded">
            </div>
          
            <div>
                <label class="block text-gray-700">Start Date</label>
                <input type="date" name="start_date" required class="w-full border p-2 rounded">
            </div>
            <div>
                <label class="block text-gray-700">End Date</label>
                <input type="date" name="end_date" required class="w-full border p-2 rounded">
            </div>
            <button type="submit" class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded">Add Election</button>
        </form>
    </div>
</div>

<script>
// Close modal on outside click
document.addEventListener('click', function(e) {
    var modal = document.getElementById('addElectionModal');
    if (!modal.classList.contains('hidden') && !modal.contains(e.target) && e.target.tagName !== 'BUTTON') {
        modal.classList.add('hidden');
    }
});
</script>

</body>
</html>
