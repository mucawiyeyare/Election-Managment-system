<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('voter');

$voter_id = $_SESSION['user_id'] ?? null;
if (!$voter_id) {
    header("Location: login.php");
    exit;
}

// Fetch voter info with better error handling
$voter_query = "SELECT v.full_name, v.email, v.phone, v.address, u.username 
                FROM voters v 
                LEFT JOIN users u ON v.user_id = u.id 
                WHERE v.user_id = ?";
$stmt = $conn->prepare($voter_query);
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$result = $stmt->get_result();
$voter_data = $result->fetch_assoc();
$stmt->close();

// If no voter record found, get user info
if (!$voter_data) {
    $user_query = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $stmt->close();
    
    $voter_data = [
        'full_name' => $user_data['username'] ?? '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'username' => $user_data['username'] ?? ''
    ];
}

$full_name = $voter_data['full_name'] ?? '';
$email = $voter_data['email'] ?? '';
$phone = $voter_data['phone'] ?? '';
$address = $voter_data['address'] ?? '';

// Get success/error messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Profile | EMS Voter</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-purple-50 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-purple-800 text-white text-xl  flex flex-col shadow-lg">
        <div class="p-6 border-b border-purple-700 flex items-center space-x-3">
            <div class="rounded-full bg-purple-600 w-10 h-10 flex items-center justify-center">
                <i class="fas fa-user text-xl"></i>
            </div>
            <div>
                <div class="text-xl font-bold">EMS Voter</div>
                <div class="text-xs text-purple-300"><?php echo htmlspecialchars($full_name ?: 'Voter'); ?></div>
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
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
            <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
                <h1 class="text-2xl font-semibold text-purple-800">My Profile</h1>
                <p class="text-sm text-purple-600 mt-1">Update your personal information</p>
            </div>
            
            <div class="p-6">
                <?php if ($success_message): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form action="update_profile.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="full_name" class="block text-purple-800 font-medium mb-2">
                                <i class="fas fa-user mr-2 text-purple-500"></i>Full Name
                            </label>
                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                value="<?= htmlspecialchars($full_name) ?>"
                                required
                                class="w-full border border-purple-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Enter your full name"
                            />
                        </div>

                        <div>
                            <label for="email" class="block text-purple-800 font-medium mb-2">
                                <i class="fas fa-envelope mr-2 text-purple-500"></i>Email Address
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($email) ?>"
                                required
                                class="w-full border border-purple-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Enter your email address"
                            />
                        </div>

                        <div>
                            <label for="phone" class="block text-purple-800 font-medium mb-2">
                                <i class="fas fa-phone mr-2 text-purple-500"></i>Phone Number
                            </label>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="<?= htmlspecialchars($phone) ?>"
                                class="w-full border border-purple-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="+1 (555) 123-4567"
                            />
                        </div>
                        
                        <div>
                            <label for="address" class="block text-purple-800 font-medium mb-2">
                                <i class="fas fa-map-marker-alt mr-2 text-purple-500"></i>Address
                            </label>
                            <input
                                type="text"
                                id="address"
                                name="address"
                                value="<?= htmlspecialchars($address) ?>"
                                class="w-full border border-purple-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Enter your address"
                            />
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-6 border-t border-purple-100">
                        <div class="text-sm text-purple-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Your information is kept secure and private
                        </div>
                        <div class="flex space-x-3">
                            <a href="dashboard.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-lg transition-colors">
                                <i class="fas fa-times mr-1"></i>Cancel
                            </a>
                            <button
                                type="submit"
                                class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors"
                            >
                                <i class="fas fa-save mr-1"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
