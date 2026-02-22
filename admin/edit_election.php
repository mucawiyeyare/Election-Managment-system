<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');

$message = '';
$error = '';

// Check if election ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: elections.php?error=' . urlencode('Election ID is required'));
    exit;
}

$election_id = (int)$_GET['id'];

// Fetch election data
$stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: elections.php?error=' . urlencode('Election not found'));
    exit;
}

$election = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $status = $_POST['status'] ?? '';
    
    // Validate required fields
    if (empty($title) || empty($start_date) || empty($end_date) || empty($status)) {
        $error = "Title, start date, end date, and status are required fields.";
    } else {
        // Update election
        $stmt = $conn->prepare("UPDATE elections SET title = ?, description = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $title, $description, $start_date, $end_date, $status, $election_id);
        
        if ($stmt->execute()) {
            $message = "Election updated successfully!";
            
            // Refresh election data
            $stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
            $stmt->bind_param("i", $election_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $election = $result->fetch_assoc();
        } else {
            $error = "Error updating election: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Election | EMS Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-gray-100 font-sans">

<div class="flex min-h-screen">

  <!-- Sidebar -->
  <aside class="w-64 bg-purple-800 text-xl text-white flex flex-col">
    <div class="p-6 text-2xl font-bold border-b border-gray-700">
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
      <h1 class="text-3xl font-bold text-gray-800">Edit Election</h1>
      <a href="elections.php" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow">
        Back to Elections
      </a>
    </div>

    <?php if (!empty($message)): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p><?php echo htmlspecialchars($message); ?></p>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p><?php echo htmlspecialchars($error); ?></p>
      </div>
    <?php endif; ?>

    <!-- Edit Election Form -->
    <div class="bg-white rounded shadow p-6">
      <form method="POST" action="">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="col-span-2">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($election['title']); ?>" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div class="col-span-2">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" name="description" rows="4" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($election['description']); ?></textarea>
          </div>
          
          <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($election['start_date']); ?>" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-red-500">*</span></label>
            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($election['end_date']); ?>" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select id="status" name="status" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="upcoming" <?php echo ($election['status'] === 'upcoming') ? 'selected' : ''; ?>>Upcoming</option>
              <option value="active" <?php echo ($election['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
              <option value="ended" <?php echo ($election['status'] === 'ended') ? 'selected' : ''; ?>>Ended</option>
            </select>
          </div>
        </div>
        
        <div class="mt-8 flex justify-end space-x-4">
          <a href="elections.php" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow">
            Cancel
          </a>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow">
            Update Election
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

</body>
</html>