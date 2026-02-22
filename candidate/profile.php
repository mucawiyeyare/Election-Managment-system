<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('candidate');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Fetch candidate info
$stmt = $conn->prepare("SELECT id, full_name, email, phone, party, profile_image FROM candidates WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($candidate_id, $full_name, $email, $phone, $party, $profile_image);
$stmt->fetch();
$stmt->close();

$profile_image_url = getCandidateImageUrl($profile_image);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Profile | EMS Candidate</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-gray-50 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-purple-800 text-white text-xl flex flex-col shadow-lg">
        <div class="p-6 border-b border-purple-700 flex items-center space-x-3">
            <div class="rounded-full bg-purple-600 w-10 h-10 flex items-center justify-center">
                <i class="fas fa-user-tie text-xl"></i>
            </div>
            <div>
                <div class="text-xl font-bold">EMS Candidate</div>
                <div class="text-xs text-purple-300"><?php echo htmlspecialchars($full_name ?? 'Candidate'); ?></div>
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
            <a href="profile.php" class="flex items-center py-2 px-4 rounded bg-purple-700 text-white">
                <i class="fas fa-user-circle w-6"></i>
                <span class="px-3">My Profile</span>
            </a>
            <a href="results.php" class="flex items-center py-2 px-4 rounded hover:bg-purple-700 text-purple-200 hover:text-white transition-colors">
                <i class="fas fa-chart-bar w-6"></i>
                <span class="px-3">Results</span>
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
            <p> 2023 All Rights Reserved</p>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-auto">
        <h1 class="text-3xl font-semibold mb-8 px-6  text-gray-800">My Profile</h1>
        
        <?php if (!empty($_GET['message'])): ?>
            <div class="max-w-3xl mx-6 mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                <p><?php echo htmlspecialchars($_GET['message']); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($_GET['error'])): ?>
            <div class="max-w-3xl mx-6 mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                <p><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
        <?php endif; ?>

        <div class="max-w-3xl bg-white mx-6 rounded-lg shadow p-8">
            <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="flex flex-col items-center">
                    <img 
                        src="<?= $profile_image_url ?>" 
                        alt="Profile Picture" 
                        class="w-32 h-32 rounded-full object-cover border border-gray-300 mb-4"
                        onerror="this.onerror=null;this.src='https://via.placeholder.com/150?text=No+Photo';"
                    />
                    <label class="cursor-pointer inline-block text-purple-700 hover:underline text-sm">
                        Change Profile Picture
                        <input type="file" name="profile_image" accept="image/*" class="hidden" />
                    </label>
                </div>

                <div>
                    <label for="full_name" class="block text-gray-700 font-medium mb-1">Full Name</label>
                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?= htmlspecialchars($full_name) ?>"
                        required
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                </div>

                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-1">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        required
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                </div>

                <div>
                    <label for="phone" class="block text-gray-700 font-medium mb-1">Phone Number</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?= htmlspecialchars($phone) ?>"
                        placeholder="+1234567890"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                </div>

                <div>
                    <label for="party" class="block text-gray-700 font-medium mb-1">Party</label>
                    <input
                        type="text"
                        id="party"
                        name="party"
                        value="<?= htmlspecialchars($party) ?>"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 px-6 rounded focus:outline-none focus:ring-2 focus:ring-purple-500"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </main>
     
     </div>
    
</body>
</html>
