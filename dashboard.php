<?php
session_start();
include('connect.php'); 

// 1. ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d'); 

// 2. ดึงข้อมูลผู้ใช้งานที่ล็อกอิน
$sql_me = "SELECT username, profile_img FROM users WHERE user_id = '$user_id'";
$res_me = mysqli_query($conn, $sql_me);
$me = mysqli_fetch_assoc($res_me);

// ตรวจสอบรูปโปรไฟล์
if (!empty($me['profile_img']) && file_exists("uploads/" . $me['profile_img'])) {
    $my_avatar = "uploads/" . $me['profile_img'];
} else {
    $my_avatar = "https://ui-avatars.com/api/?name=" . urlencode($me['username']) . "&background=007bff&color=fff";
}

// 3. ดึงข้อมูลทริป
$sql = "SELECT trips.*, users.username AS creator_name, users.gender AS creator_gender,
        (SELECT COUNT(*) FROM trip_members WHERE trip_members.trip_id = trips.id) AS current_members
        FROM trips
        LEFT JOIN users ON trips.user_id = users.user_id 
        WHERE trips.end_date >= '$today' 
        AND trips.status = 'open'
        ORDER BY trips.start_date ASC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Travel Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard-style.css">
    <style>
        .trip-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; padding: 20px 5%; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 15px; font-size: 12px; margin-bottom: 10px; color: white; }
        .bg-open { background: #28a745; }
        .bg-full { background: #dc3545; } /* สีแดงสำหรับทริปเต็ม */
        .bg-info-custom { background: #17a2b8; }
        
        /* Profile & Logout in Header */
        .user-profile-link { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #333; }
        .user-avatar-mini { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #007bff; }
        .user-name-text { font-weight: 600; font-size: 14px; color: #333; }

        .btn-logout { 
            color: #ff4d4d; 
            text-decoration: none; 
            font-size: 14px; 
            border: 1px solid #ff4d4d; 
            padding: 6px 15px; 
            border-radius: 500px; 
            background: none; 
            cursor: pointer; 
            transition: 0.3s; 
        }
        .btn-logout:hover { background: #ff4d4d; color: #fff; }

        /* --- Floating Chat Button --- */
        .floating-chat-btn {
            position: fixed !important;
            bottom: 30px;
            right: 30px;
            background-color: #007bff;
            color: white !important;
            padding: 12px 25px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
            z-index: 9999;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .floating-chat-btn:hover { transform: scale(1.05) translateY(-5px); background-color: #0056b3; }
        
        /* จุดแจ้งเตือนบนปุ่มแชท */
        .chat-badge-dot {
            min-width: 18px; height: 18px; background: #ff4757; color: white;
            font-size: 10px; border-radius: 50%; display: flex; align-items: center;
            justify-content: center; border: 2px solid #007bff;
        }

        @media (max-width: 768px) {
            .floating-chat-btn { bottom: 20px; right: 20px; padding: 15px; border-radius: 50%; }
            .floating-chat-btn .chat-text { display: none; } /* ซ่อนตัวหนังสือในมือถือ */
            .user-name-text { display: none; } /* ซ่อนชื่อใน Header มือถือเพื่อประหยัดพื้นที่ */
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Travel Buddy</div>
    <ul class="nav-links">
        <li><a href="dashboard.php" style="color: #007bff;">หน้าแรก</a></li>
        <li><a href="search.php">ค้นหาทริป</a></li>
        <li><a href="my-chats.php">แชทของฉัน</a></li>
    </ul>
    <div class="auth-btns" style="display: flex; align-items: center; gap: 15px;">
        <a href="profile.php" class="user-profile-link">
            <img src="<?php echo $my_avatar; ?>" class="user-avatar-mini" alt="Profile">
            <span class="user-name-text">คุณ <?php echo $me['username']; ?></span>
        </a>
        <button class="btn-logout" onclick="location.href='logout.php'">ออกจากระบบ</button>
    </div>
</header>

<div class="hero">
    <div class="hero-content">
        <h1>ยินดีต้อนรับ!</h1>
        <p>คุณกำลังล็อกอินอยู่ในระบบสมาชิก มั่นใจได้ด้วยระบบความปลอดภัยของเรา</p>
        <div class="cta-btns">
            <button class="btn-main btn-find" onclick="location.href='search.php'">หาเพื่อนเที่ยวตอนนี้</button>
            <button class="btn-main btn-create" onclick="location.href='create-trip.php'">+ สร้างทริปใหม่</button>
        </div>
    </div>
</div>

<div class="features">
    <h2>🚀 ทริปที่กำลังเปิดรับสมัคร</h2>
    <div class="trip-grid">
        <?php 
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $max = $row['max_members'];
                $current = $row['current_members'];
                $is_full = ($current >= $max);
        ?>
            <div class="feature-card" style="text-align: left; border: 1px solid #eee; position: relative;">
                <div style="margin-bottom: 10px;">
                    <?php if ($is_full): ?>
                        <span class="badge bg-full">🔴 เต็มแล้ว (<?php echo $current.'/'.$max; ?>)</span>
                    <?php else: ?>
                        <span class="badge bg-open">🟢 ว่าง (<?php echo $current.'/'.$max; ?>)</span>
                    <?php endif; ?>
                    <span class="badge bg-info-custom">👫 <?php echo ($row['gender_pref'] == 'all') ? "ทุกเพศ" : ($row['gender_pref'] == 'male' ? "ชาย" : "หญิง"); ?></span>
                </div>

                <h3 style="margin-top: 0; color: #007bff;"><?php echo $row['title']; ?></h3>
                <p style="font-size: 14px; margin: 5px 0;">📍 <b>ที่ไหน:</b> <?php echo $row['destination']; ?></p>
                <p style="font-size: 14px; margin: 5px 0;">📅 <b>วันที่:</b> <?php echo date('d M', strtotime($row['start_date'])); ?> - <?php echo date('d M Y', strtotime($row['end_date'])); ?></p>
                
                <p style="font-size: 13px; margin: 10px 0; color: #666; border-top: 1px solid #eee; padding-top: 10px;">
                    👤 <b>โฮสต์:</b> <?php echo $row['creator_name']; ?> 
                </p>

                <?php 
                $current_trip_id = $row['id'];
                $is_host = ($row['user_id'] == $user_id);
                $check_member = mysqli_query($conn, "SELECT id FROM trip_members WHERE trip_id = '$current_trip_id' AND user_id = '$user_id'");
                $is_already_member = (mysqli_num_rows($check_member) > 0);

                if ($is_host || $is_already_member): ?>
                    <button class="btn-main" onclick="location.href='chat.php?trip_id=<?php echo $row['id']; ?>'" style="width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        💬 เข้าสู่ห้องแชท
                    </button>
                <?php elseif ($is_full): ?>
                    <button class="btn-main" style="background: #ccc; cursor: not-allowed; width: 100%; border:none; padding:10px;" disabled>ทริปนี้เต็มแล้ว</button>
                <?php else: ?>
                    <button class="btn-main btn-find" onclick="location.href='join-and-chat.php?id=<?php echo $row['id']; ?>'" style="width: 100%; padding: 10px; background-color: #e2bf21; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        🔵 เข้าร่วมทริป
                    </button>
                <?php endif; ?>
            </div>
        <?php 
            } 
        } else {
            echo "<p style='grid-column: 1/-1; text-align: center; color: #666;'>ยังไม่มีทริปในขณะนี้</p>";
        }
        ?>
    </div>
</div>



<a href="my-chats.php" class="floating-chat-btn">
    <span style="font-size: 20px;">💬</span>
    <span class="chat-text">แชทของฉัน</span>
    <div class="chat-badge-dot">!</div>
</a>

</body>
</html>