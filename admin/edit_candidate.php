<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('admin');

$message = '';
$error = '';

// Check if candidate ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: candidates.php?error=' . urlencode('Candidate ID is required'));
    exit;
}

$candidate_id = (int)$_GET['id'];

// Fetch candidate data
$stmt = $conn->prepare("SELECT c.*, u.username FROM candidates c LEFT JOIN users u ON c.user_id = u.id WHERE c.id = ?");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: candidates.php?error=' . urlencode('Candidate not found'));
    exit;
}

$candidate = $result->fetch_assoc();

// Fetch elections for dropdown
$elections_query = "SELECT id, title FROM elections ORDER BY start_date DESC";
$elections_result = $conn->query($elections_query);
$elections = [];

if ($elections_result) {
    while ($row = $elections_result->fetch_assoc()) {
        $elections[] = $row;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $party = isset($_POST['party']) ? trim($_POST['party']) : '';
    $manifesto = isset($_POST['manifesto']) ? trim($_POST['manifesto']) : '';
    $election_id = isset($_POST['election_id']) ? (int)$_POST['election_id'] : 0;
    
    // Validate required fields
    if (empty($full_name) || empty($email) || empty($election_id)) {
        $error = "Full name, email, and election are required fields.";
    } else {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update candidate record
            $stmt = $conn->prepare("UPDATE candidates SET full_name = ?, email = ?, phone = ?, party = ?, manifesto = ?, election_id = ? WHERE id = ?");
            $stmt->bind_param("sssssii", $full_name, $email, $phone, $party, $manifesto, $election_id, $candidate_id);
            $stmt->execute();
            
            // Update user account if exists
            if (!empty($candidate['user_id'])) {
                if (!empty($username)) {
                    // Check if username already exists (excluding current user)
                    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                    $check_stmt->bind_param("si", $username, $candidate['user_id']);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result->num_rows > 0) {
                        throw new Exception("Username already exists. Please choose a different username.");
                    }
                    
                    // Update username
                    if (!empty($password)) {
                        // Update with new password
                        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
                        $stmt->bind_param("ssi", $username, $password, $candidate['user_id']);
                    } else {
                        // Update without changing password
                        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                        $stmt->bind_param("si", $username, $candidate['user_id']);
                    }
                    $stmt->execute();
                } elseif (!empty($password)) {
                    // Update only password
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $password, $candidate['user_id']);
                    $stmt->execute();
                }
            }
            
            // Handle image upload if provided
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $upload_result = handleImageUpload($_FILES['profile_image']);
                if ($upload_result['success']) {
                    $new_image_name = $upload_result['filename'];
                    
                    // Delete old image if exists
                    if (!empty($candidate['profile_image'])) {
                        deleteImageFile($candidate['profile_image']);
                    }
                    
                    // Update profile image in database
                    $stmt = $conn->prepare("UPDATE candidates SET profile_image = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_image_name, $candidate_id);
                    $stmt->execute();
                    
                    // Update candidate data
                    $candidate['profile_image'] = $new_image_name;
                } else {
                    throw new Exception($upload_result['error']);
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            $message = "Candidate updated successfully!";
            
            // Refresh candidate data
            $stmt = $conn->prepare("SELECT c.*, u.username FROM candidates c LEFT JOIN users u ON c.user_id = u.id WHERE c.id = ?");
            $stmt->bind_param("i", $candidate_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $candidate = $result->fetch_assoc();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = "Error updating candidate: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Candidate | EMS Admin</title>
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
      <h1 class="text-3xl font-bold text-gray-800">Edit Candidate</h1>
      <a href="candidates.php" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow">
        Back to Candidates
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

    <!-- Edit Candidate Form -->
    <div class="bg-white rounded shadow p-6">
      <form method="POST" action="" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Profile Image -->
          <div class="col-span-2 flex flex-col items-center mb-6">
            <h3 class="text-md font-semibold mb-3 flex items-center text-gray-700">
              <i class="fas fa-image mr-2 text-yellow-500"></i> Profile Image
            </h3>
            
            <div class="flex items-center space-x-6">
              <div class="shrink-0">
                <?php 
                  $img_url = getCandidateImageUrl($candidate['profile_image']);
                ?>
                <img src="<?= htmlspecialchars($img_url) ?>" alt="<?= htmlspecialchars($candidate['full_name']) ?>" 
                     class="w-24 h-24 rounded-full object-cover border-4 border-gray-200"
                     id="current-image"
                     onerror="this.onerror=null;this.src='https://via.placeholder.com/150?text=No+Photo';">
              </div>
              <div>
                <label class="cursor-pointer bg-blue-50 text-blue-600 px-4 py-2 rounded hover:bg-blue-100 inline-block">
                  <i class="fas fa-camera mr-2"></i>Change Photo
                  <input type="file" name="profile_image" accept="image/jpeg,image/jpg,image/png,image/gif" 
                         class="hidden" onchange="previewEditImage(this)">
                </label>
                <p class="mt-2 text-sm text-gray-500">PNG, JPG, JPEG, or GIF up to 5MB</p>
                <?php if (!empty($candidate['profile_image'])): ?>
                  <p class="text-xs text-gray-400 mt-1">Current: <?= htmlspecialchars($candidate['profile_image']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <script>
          function previewEditImage(input) {
              if (input.files && input.files[0]) {
                  const file = input.files[0];
                  
                  // Validate file type
                  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                  if (!allowedTypes.includes(file.type)) {
                      alert('Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.');
                      input.value = '';
                      return;
                  }
                  
                  // Validate file size (5MB)
                  if (file.size > 5 * 1024 * 1024) {
                      alert('File size too large. Maximum size is 5MB.');
                      input.value = '';
                      return;
                  }
                  
                  const reader = new FileReader();
                  reader.onload = function(e) {
                      document.getElementById('current-image').src = e.target.result;
                  };
                  reader.readAsDataURL(file);
              }
          }
          </script>
          
          <!-- Account Information (if user account exists) -->
          <?php if (!empty($candidate['user_id'])): ?>
          <div class="col-span-2 border-b pb-4 mb-4">
            <h2 class="text-lg font-semibold mb-3">Account Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($candidate['username'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-sm text-gray-500 mt-1">Only fill this if you want to change the password.</p>
              </div>
            </div>
          </div>
          <?php endif; ?>
          
          <!-- Personal Information -->
          <div class="col-span-2 border-b pb-4 mb-4">
            <h2 class="text-lg font-semibold mb-3">Personal Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($candidate['full_name']); ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($candidate['email']); ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($candidate['phone'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              
              <div>
                <label for="party" class="block text-sm font-medium text-gray-700 mb-1">Party</label>
                <input type="text" id="party" name="party" value="<?php echo htmlspecialchars($candidate['party']); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
          </div>
          
          <!-- Election Information -->
          <div class="col-span-2">
            <h2 class="text-lg font-semibold mb-3">Election Information</h2>
            
            <div class="mb-4">
              <label for="election_id" class="block text-sm font-medium text-gray-700 mb-1">Election <span class="text-red-500">*</span></label>
              <select id="election_id" name="election_id" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Select Election --</option>
                <?php foreach ($elections as $election): ?>
                  <option value="<?= $election['id'] ?>" <?= ($candidate['election_id'] == $election['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($election['title']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div>
              <label for="manifesto" class="block text-sm font-medium text-gray-700 mb-1">Manifesto</label>
              <textarea id="manifesto" name="manifesto" rows="6" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($candidate['manifesto']); ?></textarea>
            </div>
          </div>
        </div>
        
        <div class="mt-8 flex justify-end space-x-4">
          <a href="candidates.php" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow">
            Cancel
          </a>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow">
            Update Candidate
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

</body>
</html>
