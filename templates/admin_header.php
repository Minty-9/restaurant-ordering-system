<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; margin:0; padding:0; }
        .topbar { background: #222; color: #fff; padding: 15px; }
        .topbar a { color:#fff; margin-left:20px; text-decoration:none; }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
            gap: 20px;
            padding: 20px;
        }
        .box {
            background:#fff; padding:20px; border-radius:8px;
            box-shadow:0 2px 6px rgba(0,0,0,0.2);
            text-decoration:none; color:#000;
        }
        .box:hover { background:#f0f0f0; }
        .login-box { max-width:350px; margin:70px auto; background:#fff; padding:30px; border-radius:6px; }
        .login-box input { width:100%; padding:10px; margin:10px 0; }
        .login-box button { padding:10px 20px; background:#222; color:#fff; cursor:pointer; }
        .error { color:red; }
    </style>
</head>
<body>
<div class="topbar">
    Admin Panel  
    <a href="dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
</div>
