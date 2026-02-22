<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');

$message = '';
$error = '';
$voter = null;
$user = null;

// Check if voter ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: voters.php');
    exit;
}

$voter_id = (int)$_GET['id'];

// Fetch voter data
$voter_stmt = $conn->prepare("SELECT v.*, u.username FROM voters v JOIN users u ON v.user_id = u.id WHERE v.id = ?");
$voter_stmt->bind_param("i", $voter_id);
$voter_stmt->execute();
$voter_result = $voter_stmt->get_result();

if ($voter_result->num_rows === 0) {
    header('Location: voters.php');
    exit;
}

$voter = $voter_result->fetch_assoc();
$user_id = $voter['user_id'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? ''); // Optional for update
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = trim($_POST['status'] ?? 'active');

    // Basic validation
    if (empty($username) || empty($full_name) || empty($email)) {
        $error = "Username, full name, and email are required fields.";
    } else {
        // Check if username already exists (excluding current user)
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check_stmt->bind_param("si", $username, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username already exists. Please choose a different username.";
        } else {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Update users table
                if (!empty($password)) {
                    // Update with new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $user_stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                    $user_stmt->bind_param("ssi", $username, $hashed_password, $user_id);
                } else {
                    // Update without changing password
                    $user_stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $user_stmt->bind_param("si", $username, $user_id);
                }
                $user_stmt->execute();
                
                // Update voters table
                $voter_stmt = $conn->prepare("UPDATE voters SET full_name = ?, email = ?, phone = ?, gender = ?, address = ?, status = ? WHERE id = ?");
                $voter_stmt->bind_param("ssssssi", $full_name, $email, $phone, $gender, $address, $status, $voter_id);
                $voter_stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $message = "Voter updated successfully!";
                
                // Refresh voter data
                $voter_stmt = $conn->prepare("SELECT v.*, u.username FROM voters v JOIN users u ON v.user_id = u.id WHERE v.id = ?");
                $voter_stmt->bind_param("i", $voter_id);
                $voter_stmt->execute();
                $voter_result = $voter_stmt->get_result();
                $voter = $voter_result->fetch_assoc();
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $error = "Error updating voter: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Voter | EMS Admin</title>
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
      <h1 class="text-3xl font-bold text-gray-800">Edit Voter</h1>
      <a href="voters.php" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow">
        Back to Voters
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

    <!-- Edit Voter Form -->
    <div class="bg-white rounded shadow p-6">
      <form method="POST" action="">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Account Information -->
          <div class="col-span-2">
            <h2 class="text-xl font-semibold mb-4 pb-2 border-b">Account Information</h2>
          </div>
          
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($voter['username'] ?? ''); ?>" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-sm text-gray-500 mt-1">Only fill this if you want to change the password.</p>
          </div>
          
          <!-- Personal Information -->
          <div class="col-span-2 mt-4">
            <h2 class="text-xl font-semibold mb-4 pb-2 border-b">Personal Information</h2>
          </div>
          
          <div>
            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($voter['full_name'] ?? ''); ?>" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($voter['email'] ?? ''); ?>" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($voter['phone'] ?? ''); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
            <select id="gender" name="gender" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Select Gender</option>
              <option value="Male" <?php echo (isset($voter['gender']) && $voter['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo (isset($voter['gender']) && $voter['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
              <option value="Other" <?php echo (isset($voter['gender']) && $voter['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
          </div>
          
          <div class="col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea id="address" name="address" rows="3" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($voter['address'] ?? ''); ?></textarea>
          </div>
          
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="status" name="status" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="active" <?php echo (isset($voter['status']) && $voter['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
              <option value="inactive" <?php echo (isset($voter['status']) && $voter['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>
        </div>
        
        <div class="mt-8 flex justify-end space-x-4">
          <a href="voters.php" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow">
            Cancel
          </a>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow">
            Update Voter
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

</body>
</html>