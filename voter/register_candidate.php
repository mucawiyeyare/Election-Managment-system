<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('voter');

$voter_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get voter information
$voter_query = $conn->prepare("SELECT full_name, email FROM voters WHERE user_id = ?");
$voter_query->bind_param("i", $voter_id);
$voter_query->execute();
$voter_result = $voter_query->get_result();
$voter = $voter_result->fetch_assoc();
$voter_query->close();

// Fetch elections for dropdown
$elections = [];
$sql = "SELECT id, title FROM elections WHERE status = 'active' ORDER BY start_date DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $elections[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = trim($_POST['full_name']);
    $email       = trim($_POST['email']);
    $party       = trim($_POST['party']);
    $manifesto   = trim($_POST['manifesto']);
    $election_id = intval($_POST['election_id']);
    
    $image_name  = null;

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $original_name = basename($_FILES['profile_image']['name']);
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $image_name = uniqid('candidate_', true) . '.' . strtolower($extension);
        $target_path = $upload_dir . $image_name;

        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
            $error = 'Failed to upload the image.';
        }
    }

    if (empty($error)) {
        // Check if user is already registered as a candidate for this election
        $check_stmt = $conn->prepare("SELECT id FROM candidates WHERE user_id = ? AND election_id = ?");
        $check_stmt->bind_param("ii", $voter_id, $election_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "You are already registered as a candidate for this election.";
        } else {
            // Insert candidate into database with user_id
            $stmt = $conn->prepare("INSERT INTO candidates (user_id, full_name, email, party, manifesto, profile_image, election_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssi", $voter_id, $full_name, $email, $party, $manifesto, $image_name, $election_id);

            if ($stmt->execute()) {
                $success = "You have successfully registered as a candidate!";
            } else {
                $error = "Database error: " . $conn->error;
            }

            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register as Candidate | EMS Voter</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-purple-50 font-sans min-h-screen">

<div class="container mx-auto px-4 py-8">
  <div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
      <div class="bg-purple-50 px-6 py-4 border-b border-purple-100">
        <div class="flex items-center">
          <div class="rounded-full bg-purple-100 p-3 mr-4">
            <i class="fas fa-user-tie text-purple-600 text-xl"></i>
          </div>
          <div>
            <h1 class="text-2xl font-semibold text-purple-800">Register as Candidate</h1>
            <p class="text-sm text-purple-600 mt-1">Join an active election as a candidate</p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <?php if ($success): ?>
          <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            <div class="flex items-center">
              <i class="fas fa-check-circle mr-2"></i>
              <?= htmlspecialchars($success) ?>
            </div>
          </div>
        <?php elseif ($error): ?>
          <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <div class="flex items-center">
              <i class="fas fa-exclamation-circle mr-2"></i>
              <?= htmlspecialchars($error) ?>
            </div>
          </div>
        <?php endif; ?>

        <form action="register_candidate.php" method="POST" enctype="multipart/form-data" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-user mr-1 text-purple-500"></i> Full Name
              </label>
              <input type="text" name="full_name" value="<?= htmlspecialchars($voter['full_name'] ?? '') ?>" required 
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-envelope mr-1 text-purple-500"></i> Email
              </label>
              <input type="email" name="email" value="<?= htmlspecialchars($voter['email'] ?? '') ?>" required 
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-flag mr-1 text-purple-500"></i> Political Party
            </label>
            <input type="text" name="party" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                   placeholder="Enter your political party or 'Independent'" />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-scroll mr-1 text-purple-500"></i> Campaign Manifesto
            </label>
            <textarea name="manifesto" rows="4" required 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                      placeholder="Describe your campaign manifesto and what you stand for..."></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-vote-yea mr-1 text-purple-500"></i> Select Election
            </label>
            <select name="election_id" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              <option value="">-- Choose an Active Election --</option>
              <?php foreach ($elections as $election): ?>
                <option value="<?= $election['id'] ?>"><?= htmlspecialchars($election['title']) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (empty($elections)): ?>
              <p class="text-sm text-red-600 mt-1">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No active elections available. Please contact the administrator.
              </p>
            <?php endif; ?>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-camera mr-1 text-purple-500"></i> Profile Image
            </label>
            <input type="file" name="profile_image" accept="image/*" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
            <p class="text-xs text-gray-500 mt-1">Upload a professional photo (JPG, PNG, GIF)</p>
          </div>

          <div class="flex items-center justify-between pt-4">
            <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
              <i class="fas fa-arrow-left mr-2"></i>
              Back to Dashboard
            </a>
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
              <i class="fas fa-user-tie mr-2"></i>
              Register as Candidate
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

</body>
</html>