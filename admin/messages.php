<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_role('admin');

// Load real messages submitted via Contact Us
$messages = [];
$result = $conn->query("SELECT id, name, email, subject, message, sent_at FROM messages ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}
// Compute unread count if a status column exists; otherwise default to 0
$unread = 0;
foreach ($messages as $m) {
    if (isset($m['status']) && $m['status'] === 'unread') { $unread++; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | EMS Admin</title>
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
  <main class="flex-1 p-8 overflow-auto">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-2xl font-bold text-gray-800">Messages</h1>
      <div class="flex space-x-2">
        <button class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded shadow flex items-center">
          <i class="fas fa-sync-alt mr-2"></i> Refresh
        </button>
        <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded shadow flex items-center">
          <i class="fas fa-envelope-open mr-2"></i> Mark All as Read
        </button>
      </div>
    </div>

    <!-- Messages Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
        <div class="flex items-center">
          <div class="rounded-full bg-purple-100 p-3 mr-4">
            <i class="fas fa-envelope text-purple-500 text-xl"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">Total Messages</div>
            <div class="text-2xl font-bold text-gray-800"><?php echo count($messages); ?></div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
        <div class="flex items-center">
          <div class="rounded-full bg-red-100 p-3 mr-4">
            <i class="fas fa-envelope-open text-red-500 text-xl"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">Unread Messages</div>
            <div class="text-2xl font-bold text-gray-800">
              <?php echo (int)$unread; ?>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
        <div class="flex items-center">
          <div class="rounded-full bg-green-100 p-3 mr-4">
            <i class="fas fa-check-circle text-green-500 text-xl"></i>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">Read Messages</div>
            <div class="text-2xl font-bold text-gray-800">
              <?php echo (int)(count($messages) - $unread); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Messages List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="bg-purple-50 px-6 py-4 border-b border-purple-100 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-purple-800">Inbox</h2>
        <div class="relative">
          <input type="text" placeholder="Search messages..." 
                 class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
        </div>
      </div>
      
      <div class="divide-y divide-gray-200">
        <?php if (count($messages) > 0): ?>
          <?php foreach ($messages as $message): ?>
            <?php $isUnread = isset($message['status']) && $message['status'] === 'unread'; ?>
            <div class="p-6 hover:bg-gray-50 transition-colors <?php echo $isUnread ? 'bg-purple-50' : ''; ?>">
              <div class="flex items-start">
                <div class="flex-shrink-0 mr-4">
                  <?php if ($isUnread): ?>
                    <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                  <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 truncate">
                      <?php echo htmlspecialchars($message['subject'] ?? 'No subject'); ?>
                    </h3>
                    <span class="text-sm text-gray-500">
                      <?php 
                        $created = $message['created_at'] ?? ($message['date'] ?? null);
                        echo $created ? date('M j, Y g:i A', strtotime($created)) : '';
                      ?>
                    </span>
                  </div>
                  <p class="mt-1 text-sm text-gray-600 truncate">
                    From: <span class="font-medium"><?php echo htmlspecialchars($message['name'] ?? ($message['sender'] ?? 'Anonymous')); ?></span>
                    <?php if (!empty($message['email'])): ?>
                      <span class="text-gray-400">&lt;<?php echo htmlspecialchars($message['email']); ?>&gt;</span>
                    <?php endif; ?>
                  </p>
                  <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                    <?php echo htmlspecialchars($message['message'] ?? ''); ?>
                  </p>
                  <div class="mt-3 flex space-x-2">
                    <a href="mailto:<?php echo htmlspecialchars($message['email'] ?? ''); ?>?subject=Re:%20<?php echo rawurlencode($message['subject'] ?? ''); ?>" class="text-sm bg-purple-100 text-purple-700 px-3 py-1 rounded-full hover:bg-purple-200 inline-flex items-center">
                      <i class="fas fa-reply mr-1"></i> Reply
                    </a>
                    <?php if ($isUnread): ?>
                      <button class="text-sm bg-gray-100 text-gray-700 px-3 py-1 rounded-full hover:bg-gray-200">
                        <i class="fas fa-envelope-open mr-1"></i> Mark as Read
                      </button>
                    <?php endif; ?>
                    <button class="text-sm bg-red-100 text-red-700 px-3 py-1 rounded-full hover:bg-red-200">
                      <i class="fas fa-trash-alt mr-1"></i> Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="p-8 text-center">
            <div class="inline-flex rounded-full bg-gray-100 p-6 mb-4">
              <i class="fas fa-inbox text-gray-500 text-4xl"></i>
            </div>
            <p class="text-gray-600 text-lg">Your inbox is empty</p>
            <p class="text-gray-500 mt-1">No messages have been received yet</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
</body>
</html>