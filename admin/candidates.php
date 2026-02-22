<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('admin');

// Get statistics
$res1 = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'active'");
$total_active_elections = $res1->fetch_row()[0];

$res2 = $conn->query("SELECT COUNT(*) FROM candidates");
$total_candidates = $res2->fetch_row()[0];

// Select candidates info including party and manifesto
$result = $conn->query("SELECT id, full_name, email, party, manifesto, election_id, profile_image FROM candidates ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Candidates | EMS Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body class="bg-gray-100 text-gray-800">

<div class="flex min-h-screen">

  <!-- Sidebar -->
  <aside class="w-64 bg-purple-800 text-xl text-white flex flex-col">
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
  <main class="flex-1 p-10">

    <!-- Top Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="bg-purple-200 rounded-lg shadow p-6 border-l-4 border-purple-500">
        <div class="flex items-center">
          <div class="rounded-full bg-purple-100 p-3 mr-4">
            <i class="fas fa-vote-yea text-purple-500 text-xl"></i>
          </div>
          <div>
            <div class="text-lg font-medium text-black ">Total Active Elections</div>
            <div class="text-2xl font-bold text-purple-700"><?php echo $total_active_elections; ?></div>
          </div>
        </div>
      </div>
      
      <div class="bg-purple-200 rounded-lg shadow p-6 border-l-4 border-purple-600">
        <div class="flex items-center">
          <div class="rounded-full bg-purple-100 p-3 mr-4">
            <i class="fas fa-user-tie text-purple-600 text-xl"></i>
          </div>
          <div>
            <div class="text-lg font-medium text-black">Total Registered Candidates</div>
            <div class="text-2xl font-bold text-purple-800"><?php echo $total_candidates; ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Candidates Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800 flex items-center">
        <i class="fas fa-user-tie text-purple-600 mr-2"></i> Candidates Management
      </h1>
      <a href="add_candidate.php" class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded-md shadow-sm flex items-center">
        <i class="fas fa-user-plus mr-2"></i> Add Candidate
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
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>Candidate updated successfully!</p>
      </div>
    <?php endif; ?>

    <!-- Candidates List -->
    <div class="bg-white p-6 rounded shadow overflow-x-auto">
      <?php if ($result && $result->num_rows > 0): ?>
        <table class="min-w-full table-auto border border-gray-200">
          <thead class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
            <tr>
              <th class="py-3 px-6 text-left">#</th>
              <th class="py-3 px-6 text-left">Photo</th>
              <th class="py-3 px-6 text-left">Name</th>
              <th class="py-3 px-6 text-left">Email</th>
              <th class="py-3 px-6 text-left">Party</th>
              <th class="py-3 px-6 text-left">Action</th>
            
            </tr>
          </thead>
         <tbody class="text-gray-700 text-sm font-light">
  <?php while($row = $result->fetch_assoc()): ?>
    <tr class="border-b border-gray-200 hover:bg-gray-100">
      <td class="py-3 px-6"><?php echo htmlspecialchars($row['id']); ?></td>
      <td class="py-3 px-6">
        <div class="flex items-center">
          <?php echo getCandidateImageHtml(
            $row['profile_image'], 
            $row['full_name'], 
            'w-12 h-12 rounded-full object-cover border-2 border-gray-200 shadow-sm'
          ); ?>
        </div>
      </td>
      <td class="py-3 px-6 font-medium"><?php echo htmlspecialchars($row['full_name']); ?></td>
      <td class="py-3 px-6"><?php echo htmlspecialchars($row['email']); ?></td>
      <td class="py-3 px-6">
        <?php if (!empty($row['party'])): ?>
          <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
            <?php echo htmlspecialchars($row['party']); ?>
          </span>
        <?php else: ?>
          <span class="text-gray-400 text-xs">No party</span>
        <?php endif; ?>
      </td>
     
      <td class="py-3 px-6">
        <div class="flex gap-2">
          <a href="edit_candidate.php?id=<?php echo $row['id']; ?>" 
             class="bg-purple-600 text-white px-3 py-1 rounded hover:bg-purple-700 text-sm transition-colors duration-200 flex items-center">
            <i class="fas fa-edit mr-1"></i>Edit
          </a>
          <a href="delete_candidate.php?id=<?php echo $row['id']; ?>" 
             onclick="return confirm('Are you sure you want to delete this candidate? This action cannot be undone.');" 
             class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm transition-colors duration-200 flex items-center">
            <i class="fas fa-trash mr-1"></i>Delete
          </a>
        </div>
      </td>
    </tr>
  <?php endwhile; ?>
</tbody>

        </table>
      <?php else: ?>
        <p class="text-gray-600">No candidates have been registered yet.</p>
      <?php endif; ?>
    </div>

  </main>
</div>

</body>
</html>
