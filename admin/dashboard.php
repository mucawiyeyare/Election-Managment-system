<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/image_utils.php';
require_role('admin');

// Get statistics
$total_elections = $conn->query("SELECT COUNT(*) FROM elections")->fetch_row()[0];
$active_elections = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'active'")->fetch_row()[0];
$upcoming_elections = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'upcoming'")->fetch_row()[0];
$ended_elections = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'ended'")->fetch_row()[0];

$total_candidates = $conn->query("SELECT COUNT(*) FROM candidates")->fetch_row()[0];
$total_voters = $conn->query("SELECT COUNT(*) FROM voters")->fetch_row()[0];
$total_votes = $conn->query("SELECT COUNT(*) FROM votes")->fetch_row()[0];

// Get recent elections
$recent_elections = $conn->query("SELECT id, title, status, start_date, end_date FROM elections ORDER BY start_date DESC LIMIT 5");

// Get recent candidates
$recent_candidates = $conn->query("SELECT c.id, c.full_name, c.email, c.profile_image, e.title as election_title 
                                  FROM candidates c 
                                  LEFT JOIN elections e ON c.election_id = e.id 
                                  ORDER BY c.id DESC LIMIT 5");

// Get admin info
$admin_id = $_SESSION['user_id'];
$admin_info = $conn->query("SELECT username FROM users WHERE id = $admin_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard | EMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
      min-height: 100vh;
    }

    .dashboard-container {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar Styles */
    .sidebar {
      width: 280px;
      background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
      color: white;
      display: flex;
      flex-direction: column;
      box-shadow: 4px 0 20px rgba(102, 126, 234, 0.3);
      position: relative;
      overflow: hidden;
    }

    .sidebar::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      transform: translate(50%, -50%);
    }

    .sidebar-header {
      padding: 30px 24px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      position: relative;
      z-index: 1;
    }

    .admin-profile {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .admin-avatar {
      width: 50px;
      height: 50px;
      background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #667eea;
      font-size: 24px;
      font-weight: 700;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .admin-info h3 {
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .admin-info p {
      font-size: 13px;
      opacity: 0.9;
      color: rgba(255, 255, 255, 0.8);
    }

    .sidebar-nav {
      flex: 1;
      padding: 24px 16px;
      position: relative;
      z-index: 1;
    }

    .nav-item {
      display: flex;
      align-items: center;
      padding: 14px 16px;
      margin-bottom: 8px;
      border-radius: 12px;
      color: rgba(255, 255, 255, 0.9);
      text-decoration: none;
      transition: all 0.3s ease;
      font-size: 15px;
      font-weight: 500;
    }

    .nav-item:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateX(5px);
    }

    .nav-item.active {
      background: rgba(255, 255, 255, 0.25);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .nav-item i {
      width: 24px;
      margin-right: 12px;
      font-size: 18px;
    }

    .nav-divider {
      height: 1px;
      background: rgba(255, 255, 255, 0.1);
      margin: 16px 0;
    }

    .logout-btn {
      background: rgba(239, 68, 68, 0.9);
      color: white;
    }

    .logout-btn:hover {
      background: rgba(220, 38, 38, 1);
      transform: translateX(0);
    }

    .sidebar-footer {
      padding: 20px;
      text-align: center;
      font-size: 12px;
      opacity: 0.7;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Main Content */
    .main-content {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 40px;
    }

    .page-title {
      font-size: 32px;
      font-weight: 800;
      color: #1a202c;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .page-date {
      font-size: 14px;
      color: #718096;
      font-weight: 500;
    }

    /* Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 24px;
      margin-bottom: 40px;
    }

    .stat-card {
      background: white;
      border-radius: 20px;
      padding: 28px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
    }

    .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      margin-bottom: 16px;
    }

    .stat-icon.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    .stat-icon.green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
    .stat-icon.blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; }
    .stat-icon.yellow { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }

    .stat-label {
      font-size: 14px;
      color: #718096;
      font-weight: 600;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .stat-value {
      font-size: 36px;
      font-weight: 800;
      color: #1a202c;
      margin-bottom: 12px;
    }

    .stat-details {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      font-size: 12px;
    }

    .stat-badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-weight: 600;
    }

    .stat-badge.green { background: #d1fae5; color: #065f46; }
    .stat-badge.yellow { background: #fef3c7; color: #92400e; }
    .stat-badge.gray { background: #e5e7eb; color: #374151; }

    /* Content Grid */
    .content-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
      gap: 30px;
      margin-bottom: 30px;
    }

    .content-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      overflow: hidden;
    }

    .card-header {
      background: linear-gradient(135deg, #f8f9fc 0%, #eef2f7 100%);
      padding: 20px 24px;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .card-title {
      font-size: 18px;
      font-weight: 700;
      color: #1a202c;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .card-title i {
      color: #667eea;
    }

    .card-link {
      color: #667eea;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: color 0.3s ease;
    }

    .card-link:hover {
      color: #764ba2;
    }

    .card-body {
      padding: 24px;
    }

    /* Table Styles */
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      text-align: left;
      padding: 12px;
      font-size: 12px;
      font-weight: 700;
      color: #718096;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 2px solid #e5e7eb;
    }

    td {
      padding: 16px 12px;
      font-size: 14px;
      color: #374151;
      border-bottom: 1px solid #f3f4f6;
    }

    tr:hover {
      background: #f9fafb;
    }

    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
    }

    .status-badge.active { background: #d1fae5; color: #065f46; }
    .status-badge.upcoming { background: #fef3c7; color: #92400e; }
    .status-badge.ended { background: #e5e7eb; color: #374151; }

    /* Candidate List */
    .candidate-list {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .candidate-item {
      display: flex;
      align-items: center;
      padding: 16px;
      border-radius: 12px;
      transition: background 0.3s ease;
    }

    .candidate-item:hover {
      background: #f9fafb;
    }

    .candidate-avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 16px;
      border: 2px solid #e5e7eb;
    }

    .candidate-info {
      flex: 1;
    }

    .candidate-name {
      font-size: 15px;
      font-weight: 600;
      color: #1a202c;
      margin-bottom: 4px;
    }

    .candidate-email {
      font-size: 13px;
      color: #718096;
    }

    .candidate-election {
      font-size: 13px;
      color: #667eea;
      font-weight: 600;
    }

    /* Quick Actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
    }

    .action-card {
      display: flex;
      align-items: center;
      padding: 20px;
      border-radius: 16px;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .action-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .action-card.blue { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
    .action-card.green { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); }
    .action-card.purple { background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); }
    .action-card.yellow { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }

    .action-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 16px;
      font-size: 22px;
    }

    .action-card.blue .action-icon { background: #3b82f6; color: white; }
    .action-card.green .action-icon { background: #10b981; color: white; }
    .action-card.purple .action-icon { background: #8b5cf6; color: white; }
    .action-card.yellow .action-icon { background: #f59e0b; color: white; }

    .action-info h4 {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .action-card.blue .action-info h4 { color: #1e40af; }
    .action-card.green .action-info h4 { color: #065f46; }
    .action-card.purple .action-info h4 { color: #5b21b6; }
    .action-card.yellow .action-info h4 { color: #92400e; }

    .action-info p {
      font-size: 12px;
      opacity: 0.8;
    }

    .action-card.blue .action-info p { color: #1e40af; }
    .action-card.green .action-info p { color: #065f46; }
    .action-card.purple .action-info p { color: #5b21b6; }
    .action-card.yellow .action-info p { color: #92400e; }

    /* Responsive */
    @media (max-width: 1024px) {
      .sidebar {
        width: 240px;
      }

      .main-content {
        padding: 30px 20px;
      }

      .content-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .dashboard-container {
        flex-direction: column;
      }

      .sidebar {
        width: 100%;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="admin-profile">
          <div class="admin-avatar">
            <i class="fas fa-user-shield"></i>
          </div>
          <div class="admin-info">
            <h3>EMS Admin</h3>
            <p><?php echo htmlspecialchars($admin_info['username']); ?></p>
          </div>
        </div>
      </div>
      
      <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item active">
          <i class="fas fa-tachometer-alt"></i>
          <span>Dashboard</span>
        </a>
        <a href="elections.php" class="nav-item">
          <i class="fas fa-vote-yea"></i>
          <span>Elections</span>
        </a>
        <a href="candidates.php" class="nav-item">
          <i class="fas fa-user-tie"></i>
          <span>Candidates</span>
        </a>
        <a href="voters.php" class="nav-item">
          <i class="fas fa-users"></i>
          <span>Voters</span>
        </a>
        <a href="results.php" class="nav-item">
          <i class="fas fa-chart-bar"></i>
          <span>Results</span>
        </a>
        <a href="messages.php" class="nav-item">
          <i class="fas fa-envelope"></i>
          <span>Messages</span>
        </a>
        <a href="settings.php" class="nav-item">
          <i class="fas fa-cog"></i>
          <span>Settings</span>
        </a>
        
        <div class="nav-divider"></div>
        
        <a href="logout.php" class="nav-item logout-btn">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </a>
      </nav>
      
      <div class="sidebar-footer">
        <p>Election Management System</p>
        <p>© 2023 All Rights Reserved</p>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="page-header">
        <h1 class="page-title">Admin Dashboard</h1>
        <div class="page-date">
          <?php echo date('l, F j, Y'); ?>
        </div>
      </div>
      
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon purple">
            <i class="fas fa-vote-yea"></i>
          </div>
          <div class="stat-label">Total Elections</div>
          <div class="stat-value"><?php echo $total_elections; ?></div>
          <div class="stat-details">
            <span class="stat-badge green"><i class="fas fa-circle"></i> Active: <?php echo $active_elections; ?></span>
            <span class="stat-badge yellow"><i class="fas fa-circle"></i> Upcoming: <?php echo $upcoming_elections; ?></span>
            <span class="stat-badge gray"><i class="fas fa-circle"></i> Ended: <?php echo $ended_elections; ?></span>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon green">
            <i class="fas fa-user-tie"></i>
          </div>
          <div class="stat-label">Candidates</div>
          <div class="stat-value"><?php echo $total_candidates; ?></div>
          <a href="candidates.php" style="color: #10b981; text-decoration: none; font-size: 14px; font-weight: 600;">
            View all candidates <i class="fas fa-arrow-right"></i>
          </a>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon blue">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-label">Registered Voters</div>
          <div class="stat-value"><?php echo $total_voters; ?></div>
          <a href="voters.php" style="color: #3b82f6; text-decoration: none; font-size: 14px; font-weight: 600;">
            View all voters <i class="fas fa-arrow-right"></i>
          </a>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon yellow">
            <i class="fas fa-check-square"></i>
          </div>
          <div class="stat-label">Total Votes Cast</div>
          <div class="stat-value"><?php echo $total_votes; ?></div>
          <a href="results.php" style="color: #f59e0b; text-decoration: none; font-size: 14px; font-weight: 600;">
            View results <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="content-grid">
        <!-- Recent Elections -->
        <div class="content-card">
          <div class="card-header">
            <h2 class="card-title">
              <i class="fas fa-vote-yea"></i>
              Recent Elections
            </h2>
            <a href="elections.php" class="card-link">
              View All <i class="fas fa-arrow-right"></i>
            </a>
          </div>
          <div class="card-body">
            <?php if ($recent_elections && $recent_elections->num_rows > 0): ?>
              <table>
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Dates</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($election = $recent_elections->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($election['title']); ?></td>
                      <td>
                        <?php if ($election['status'] === 'active'): ?>
                          <span class="status-badge active">Active</span>
                        <?php elseif ($election['status'] === 'upcoming'): ?>
                          <span class="status-badge upcoming">Upcoming</span>
                        <?php else: ?>
                          <span class="status-badge ended">Ended</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php echo date('M j, Y', strtotime($election['start_date'])); ?> - 
                        <?php echo date('M j, Y', strtotime($election['end_date'])); ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p style="text-align: center; color: #718096; padding: 20px;">No elections found.</p>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Recent Candidates -->
        <div class="content-card">
          <div class="card-header">
            <h2 class="card-title">
              <i class="fas fa-user-tie"></i>
              Recent Candidates
            </h2>
            <a href="candidates.php" class="card-link">
              View All <i class="fas fa-arrow-right"></i>
            </a>
          </div>
          <div class="card-body">
            <?php if ($recent_candidates && $recent_candidates->num_rows > 0): ?>
              <div class="candidate-list">
                <?php while ($candidate = $recent_candidates->fetch_assoc()): ?>
                  <div class="candidate-item">
                    <?= getCandidateImageHtml(
                      $candidate['profile_image'], 
                      $candidate['full_name'], 
                      'candidate-avatar'
                    ) ?>
                    <div class="candidate-info">
                      <div class="candidate-name"><?php echo htmlspecialchars($candidate['full_name']); ?></div>
                      <div class="candidate-email"><?php echo htmlspecialchars($candidate['email']); ?></div>
                    </div>
                    <div class="candidate-election">
                      <?php echo htmlspecialchars($candidate['election_title'] ?? 'N/A'); ?>
                    </div>
                  </div>
                <?php endwhile; ?>
              </div>
            <?php else: ?>
              <p style="text-align: center; color: #718096; padding: 20px;">No candidates found.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <!-- Quick Actions -->
      <div class="content-card">
        <div class="card-header">
          <h2 class="card-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
          </h2>
        </div>
        <div class="card-body">
          <div class="quick-actions">
            <a href="elections.php" class="action-card blue">
              <div class="action-icon">
                <i class="fas fa-plus"></i>
              </div>
              <div class="action-info">
                <h4>Add Election</h4>
                <p>Create a new election</p>
              </div>
            </a>
            
            <a href="add_candidate.php" class="action-card green">
              <div class="action-icon">
                <i class="fas fa-user-plus"></i>
              </div>
              <div class="action-info">
                <h4>Add Candidate</h4>
                <p>Register a new candidate</p>
              </div>
            </a>
            
            <a href="add_voter.php" class="action-card purple">
              <div class="action-icon">
                <i class="fas fa-user-plus"></i>
              </div>
              <div class="action-info">
                <h4>Add Voter</h4>
                <p>Register a new voter</p>
              </div>
            </a>
            
            <a href="results.php" class="action-card yellow">
              <div class="action-icon">
                <i class="fas fa-chart-pie"></i>
              </div>
              <div class="action-info">
                <h4>View Results</h4>
                <p>Check election results</p>
              </div>
            </a>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Animate stat values on page load
    document.addEventListener('DOMContentLoaded', function() {
      const statValues = document.querySelectorAll('.stat-value');
      
      statValues.forEach(stat => {
        const target = parseInt(stat.textContent);
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
          current += increment;
          if (current >= target) {
            current = target;
            clearInterval(timer);
          }
          stat.textContent = Math.floor(current);
        }, 20);
      });
    });
  </script>
</body>
</html>
