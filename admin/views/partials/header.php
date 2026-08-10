<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bhutan Destination | Admin Panel</title>
    <!-- Relative link back to admin CSS -->
    <link rel="stylesheet" href="/admin/assets/css/admin-style.css">
</head>
<body>

    <!-- Sidebar Partial -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="admin-main">
        
        <!-- Top Navigation Bar -->
        <header class="admin-header">
            <div class="header-left">
                <span class="page-badge">Admin Dashboard</span>
            </div>
            <div class="header-right">
                <div class="user-profile">
                    <span class="status-indicator"></span>
                    <span>Administrator</span>
                </div>
              <a href="/admin/auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <!-- Main Content Section Starts -->
        <main class="admin-content">