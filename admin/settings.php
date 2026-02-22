<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');

$admin_id = $_SESSION['user_id'];
$admin_info = $conn->query("SELECT username FROM users WHERE id = $admin_id")->fetch_assoc();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_password'])) {
        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        // Validate passwords
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New password and confirmation do not match.";
        } else {
            // Check current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && $user['password'] === $current_password) {
                // Update password
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_password, $admin_id);
                
                if ($stmt->execute()) {
                    $success = "Password updated successfully!";
                } else {
                    $error = "Error updating password: " . $conn->error;
                }
            } else {
                $error = "Current password is incorrect.";
            }
        }
    } elseif (isset($_POST['update_system'])) {
        // Process system settings update
        $site_name = trim($_POST['site_name']);
        $contact_email = trim($_POST['contact_email']);
        
        // Simulate successful update
        $success = "System settings updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | EMS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-gray-100 font-sans">

<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-purple-800 text-xl text-white flex flex-col">
    <div class="p-6 border-b border-purple-700 flex items-center space-x-3">
      <div class="rounded-full bg-purple-600 w-10 h-10 flex items-center justify-center">
        <i class="fas fa-user-shield text-xl"></i>
      </div>
      <div>
        <div class="text-xl font-bold">EMS Admin</div>
        <div class="text-xs text-purple-300"><?php echo htmlspecialchars($admin_info['username'] ?? 'Administrator'); ?></div>
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
  <main class="flex-1 p-8 overflow-auto">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-2xl font-bold text-gray-800">Settings</h1>
    </div>

    <?php if ($success): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <div class="flex items-center">
          <i class="fas fa-check-circle mr-2"></i>
          <p><?= htmlspecialchars($success) ?></p>
        </div>
      </div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <div class="flex items-center">
          <i class="fas fa-exclamation-circle mr-2"></i>
          <p><?= htmlspecialchars($error) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Account Settings -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
          <h2 class="text-lg font-semibold text-purple-800">Account Settings</h2>
        </div>
        
        <div class="p-6">
          <form method="POST" action="" class="space-y-6">
            <input type="hidden" name="update_password" value="1">
            
            <div>
              <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input type="password" id="current_password" name="current_password" required 
                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" />
              </div>
            </div>
            
            <div>
              <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-key text-gray-400"></i>
                </div>
                <input type="password" id="new_password" name="new_password" required 
                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" />
              </div>
            </div>
            
            <div>
              <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-check-double text-gray-400"></i>
                </div>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" />
              </div>
            </div>
            
            <div>
              <button type="submit" class="bg-purple-700 hover:bg-purple-800 text-white font-medium py-2 px-4 rounded-md flex items-center">
                <i class="fas fa-save mr-2"></i> Update Password
              </button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- System Settings -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
          <h2 class="text-lg font-semibold text-purple-800">System Settings</h2>
        </div>
        
        <div class="p-6">
          <form method="POST" action="" class="space-y-6">
            <input type="hidden" name="update_system" value="1">
            
            <div>
              <label for="site_name" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
              <input type="text" id="site_name" name="site_name" value="Election Management System" 
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" />
            </div>
            
            <div>
              <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-envelope text-gray-400"></i>
                </div>
                <input type="email" id="contact_email" name="contact_email" value="admin@example.com" 
                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" />
              </div>
            </div>
            
            <div>
              <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-globe text-gray-400"></i>
                </div>
                <select id="timezone" name="timezone" 
                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 appearance-none">
                  <option value="UTC" selected>UTC</option>
                  <option value="America/New_York">Eastern Time (ET)</option>
                  <option value="America/Chicago">Central Time (CT)</option>
                  <option value="America/Denver">Mountain Time (MT)</option>
                  <option value="America/Los_Angeles">Pacific Time (PT)</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
              </div>
            </div>
            
            <div>
              <button type="submit" class="bg-purple-700 hover:bg-purple-800 text-white font-medium py-2 px-4 rounded-md flex items-center">
                <i class="fas fa-save mr-2"></i> Save System Settings
              </button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Backup & Maintenance -->
      <div class="bg-white rounded-lg shadow overflow-hidden lg:col-span-2">
        <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
          <h2 class="text-lg font-semibold text-purple-800">Backup & Maintenance</h2>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <div class="flex items-center mb-4">
                <div class="rounded-full bg-purple-100 p-3 mr-3">
                  <i class="fas fa-database text-purple-500"></i>
                </div>
                <h3 class="text-md font-semibold">Database Backup</h3>
              </div>
              <p class="text-sm text-gray-600 mb-4">Create a backup of the entire database.</p>
              <button class="w-full bg-purple-700 hover:bg-purple-800 text-white font-medium py-2 px-4 rounded-md flex items-center justify-center">
                <i class="fas fa-download mr-2"></i> Backup Now
              </button>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <div class="flex items-center mb-4">
                <div class="rounded-full bg-yellow-100 p-3 mr-3">
                  <i class="fas fa-broom text-yellow-500"></i>
                </div>
                <h3 class="text-md font-semibold">Clear Cache</h3>
              </div>
              <p class="text-sm text-gray-600 mb-4">Clear system cache to improve performance.</p>
              <button class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-md flex items-center justify-center">
                <i class="fas fa-trash-alt mr-2"></i> Clear Cache
              </button>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <div class="flex items-center mb-4">
                <div class="rounded-full bg-red-100 p-3 mr-3">
                  <i class="fas fa-history text-red-500"></i>
                </div>
                <h3 class="text-md font-semibold">System Logs</h3>
              </div>
              <p class="text-sm text-gray-600 mb-4">View and download system logs.</p>
              <button class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md flex items-center justify-center">
                <i class="fas fa-eye mr-2"></i> View Logs
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

</body>
</html>