<?php
session_start();
include('connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. ดึงข้อมูลผู้ใช้งานสำหรับ Header
$sql_me = "SELECT username, profile_img FROM users WHERE user_id = '$user_id'";
$res_me = mysqli_query($conn, $sql_me);
$me = mysqli_fetch_assoc($res_me);

if (!empty($me['profile_img']) && file_exists("uploads/" . $me['profile_img'])) {
    $my_avatar = "uploads/" . $me['profile_img'];
} else {
    $my_avatar = "https://ui-avatars.com/api/?name=" . urlencode($me['username'] ?? 'User') . "&background=007bff&color=fff";
}

// 2. ดึงข้อมูลทริป
$sql = "SELECT trips.*, users.username AS creator_name,
        (SELECT COUNT(*) FROM trip_members WHERE trip_members.trip_id = trips.id) AS current_members
        FROM trips 
        JOIN users ON trips.user_id = users.user_id
        WHERE trips.id IN (SELECT trip_id FROM trip_members WHERE user_id = '$user_id')
        OR trips.user_id = '$user_id'
        GROUP BY trips.id
        ORDER BY trips.start_date ASC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Chats - Travel Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard-style.css">
    
    <style>
        :root {
            --primary-color: #007bff;
            --bg-light: #f4f7f6;
        }

        /* ส่วนสำคัญที่ทำให้ Footer ติดล่างสุด */
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: var(--bg-light);
            display: flex;
            flex-direction: column; /* จัดวางแบบแนวตั้ง */
            padding-top: 80px; 
        }

        header {
            position: fixed; top: 0; left: 0; right: 0;
            background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 5%; z-index: 1000;
        }

        /* คอนเทนเนอร์หลักขยายตัวเพื่อดัน Footer */
        .container { 
            max-width: 1100px; 
            margin: 40px auto; 
            padding: 0 20px; 
            flex: 1 0 auto; /* ขยายเพื่อกินพื้นที่ว่างทั้งหมด */
        }

        /* --- UI Elements --- */
        .logo { font-size: 22px; font-weight: 600; color: var(--primary-color); }
        .nav-links { display: flex; list-style: none; gap: 20px; margin: 0; }
        .nav-links a { text-decoration: none; color: #333; font-size: 14px; }
        .user-profile-link { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .user-avatar-mini { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-color); }
        
        .btn-logout { 
            color: #ff4d4d; border: 1px solid #ff4d4d; padding: 5px 15px; 
            border-radius: 20px; background: none; cursor: pointer; transition: 0.3s;
        }
        .btn-logout:hover { background: #ff4d4d; color: #fff; }

        .page-title h1 { font-size: 26px; color: #222; display: flex; align-items: center; gap: 10px; margin-bottom: 30px; }

        .chat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .chat-card {
            background: #fff; border-radius: 18px; display: flex; overflow: hidden;
            text-decoration: none; color: inherit; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: 0.3s; border: 1px solid #eee;
        }
        .chat-card:hover { transform: translateY(-5px); border-color: var(--primary-color); }

        .card-icon { width: 100px; background: linear-gradient(135deg, #007bff, #00d4ff); display: flex; align-items: center; justify-content: center; font-size: 35px; color: #fff; }
        .card-info { padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .card-info h3 { margin: 0 0 10px 0; font-size: 17px; }
        
        .tag-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .tag { font-size: 11px; background: #f0f2f5; padding: 4px 10px; border-radius: 50px; color: #555; }
        .tag i { color: var(--primary-color); }

        .empty-view { grid-column: 1 / -1; text-align: center; padding: 60px; background: #fff; border-radius: 20px; border: 2px dashed #ccc; }

        /* Footer ปรับแต่งให้คงที่ */
        footer {
            flex-shrink: 0; /* ป้องกัน footer หดตัว */
            text-align: center;
            padding: 25px;
            color: #888;
            font-size: 13px;
            background: #fff;
            border-top: 1px solid #eee;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .nav-links, .user-name-text { display: none; }
            .chat-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Travel Buddy</div>
    <ul class="nav-links">
        <li><a href="dashboard.php">หน้าแรก</a></li>
        <li><a href="search.php">ค้นหาทริป</a></li>
        <li><a href="my-chats.php" style="color: var(--primary-color); font-weight: 600;">แชทของฉัน</a></li>
    </ul>
    <div class="auth-btns" style="display: flex; align-items: center; gap: 15px;">
        <a href="profile.php" class="user-profile-link">
            <img src="<?php echo $my_avatar; ?>" class="user-avatar-mini" alt="Profile">
            <span class="user-name-text" style="font-weight: 600; font-size: 14px; color: #333;">คุณ <?php echo $me['username']; ?></span>
        </a>
        <button class="btn-logout" onclick="location.href='logout.php'">ออกจากระบบ</button>
    </div>
</header>

<div class="container">
    <div class="page-title">
        <h1><i class="fas fa-comments" style="color: var(--primary-color);"></i> ห้องแชทของฉัน</h1>
        <p style="color: #666; margin-top: -20px; margin-bottom: 30px;">พูดคุยและวางแผนทริปกับเพื่อนร่วมทางของคุณ</p>
    </div>

    <div class="chat-grid">
        <?php 
        if (mysqli_num_rows($result) > 0): 
            while($row = mysqli_fetch_assoc($result)): 
                $icons = ['✈️', '🏝️', '🏔️', '⛺', '🚗', '🚢', '🏨'];
                $random_icon = $icons[$row['id'] % count($icons)];
        ?>
            <a href="chat.php?trip_id=<?php echo $row['id']; ?>" class="chat-card">
                <div class="card-icon"><?php echo $random_icon; ?></div>
                <div class="card-info">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <div class="tag-row">
                        <span class="tag"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['destination']); ?></span>
                        <span class="tag"><i class="fas fa-calendar-alt"></i> <?php echo date('d M', strtotime($row['start_date'])); ?></span>
                        <span class="tag"><i class="fas fa-users"></i> <?php echo $row['current_members']; ?> คน</span>
                    </div>
                    <div class="host-info" style="font-size: 12px; color: #888;">
                        <i class="fas fa-user-circle"></i> โฮสต์: <?php echo htmlspecialchars($row['creator_name']); ?>
                    </div>
                </div>
            </a>
        <?php 
            endwhile; 
        else: 
        ?>
            <div class="empty-view">
                <div style="font-size: 50px; margin-bottom: 20px;">🏜️</div>
                <h3>คุณยังไม่มีห้องแชท</h3>
                <p style="color: #666;">เข้าร่วมทริปที่สนใจเพื่อเริ่มพูดคุยกับเพื่อนใหม่</p>
                <a href="search.php" style="display: inline-block; margin-top: 20px; background: var(--primary-color); color: white; padding: 10px 25px; border-radius: 50px; text-decoration: none;">ไปหาทริปกันเลย</a>
            </div>
        <?php endif; ?>
    </div>
</div>



</body>
</html>