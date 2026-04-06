<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CollabCrew Marketplace</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif; 
            color: #001e00;
        }
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e4ebe4;
        }
        .navbar-brand {
            color: #14a800 !important;
            font-weight: 700;
            letter-spacing: -1.5px;
            font-size: 1.7rem;
        }
        .nav-link, .nav-item > a {
            color: #001e00 !important;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .nav-link:hover, .nav-item > a:hover {
            color: #14a800 !important;
        }
        .dropdown-menu {
            border: 1px solid #e4ebe4;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .dropdown-item {
            font-size: 0.9rem;
            color: #001e00;
        }
        .dropdown-item:hover {
            background-color: #f2f7f2;
            color: #14a800;
        }
        .btn-primary {
            background-color: #14a800;
            border-color: #14a800;
            font-weight: 600;
            border-radius: 20px;
            padding: 6px 16px;
        }
        .btn-primary:hover {
            background-color: #3c8224;
            border-color: #3c8224;
        }
        .btn-outline-primary {
            color: #14a800;
            border-color: #14a800;
            font-weight: 600;
            border-radius: 20px;
            padding: 6px 16px;
        }
        .btn-outline-primary:hover {
            background-color: #f2f7f2;
            color: #14a800;
            border-color: #14a800;
        }
        .text-upwork {
            color: #14a800 !important;
        }
        .bg-upwork {
            background-color: #14a800 !important;
        }
        .search-container {
            position: relative;
        }
        .search-input {
            border-radius: 20px;
            padding-left: 35px;
            border: 1px solid #e4ebe4;
            background-color: #f2f7f2;
            font-size: 0.9rem;
        }
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #5e6d55;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg py-2 sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand me-4 d-flex align-items-center gap-2" href="index.php" style="color: #14a800 !important; font-weight: 700; letter-spacing: -1.5px; font-size: 1.7rem; text-decoration: none;">
            <img src="assets/img/logo.png" alt="CollabCrew Logo" height="35" width="35" class="rounded-circle" style="object-fit: cover;" onerror="this.onerror=null; this.src=''; this.alt=''">
            CollabCrew
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php
            // Fetch user role for role-aware navigation
            $nav_user_role = '';
            if (isset($_SESSION['user_id'])) {
                $nav_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
                $nav_stmt->bind_param("i", $_SESSION['user_id']);
                $nav_stmt->execute();
                $nav_stmt->bind_result($nav_user_role);
                $nav_stmt->fetch();
                $nav_stmt->close();
            }
            ?>
            <ul class="navbar-nav me-auto align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($nav_user_role === 'freelancer' || $nav_user_role === 'both'): ?>
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Find Work</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="view_works.php">Find Work</a></li>
                            <li><a class="dropdown-item" href="dashboard.php">Saved Jobs</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <?php if ($nav_user_role === 'client' || $nav_user_role === 'both'): ?>
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Hire talent</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="post_project.php">Post a job</a></li>
                            <li><a class="dropdown-item" href="find_talent.php">Search for talent</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item me-2">
                        <a class="nav-link" href="dashboard.php">Manage work <i class="bi bi-chevron-down ms-1" style="font-size:0.75rem;"></i></a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link" href="messages.php">Messages</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item me-3">
                        <div class="search-container d-none d-lg-block" style="width: 250px;">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" class="form-control search-input" placeholder="Search">
                        </div>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link" href="#"><i class="bi bi-question-circle fs-5"></i></a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link" href="#"><i class="bi bi-bell fs-5"></i></a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 me-1 text-upwork"></i> 
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item" href="profile.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item me-4 d-none d-lg-block">
                        <a class="nav-link" href="join.php"><i class="bi bi-globe me-1"></i> EN</a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link fw-bold" href="login.php">Log In</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary text-white" href="join.php">Sign Up</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid <?php echo isset($fluid_container) && $fluid_container ? '' : 'container'; ?>">
