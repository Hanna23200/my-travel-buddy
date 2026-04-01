<?php
session_start();
include('connect.php'); 

// ตรวจสอบการ Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// 1. ดึงข้อมูลผู้ใช้งาน (ใช้ชื่อตัวแปร $user ทั้งหมดเพื่อให้ไม่งง)
$sql_user = "SELECT * FROM users WHERE user_id = '$user_id'";
$res_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($res_user);

// เตรียมรูปโปรไฟล์
if (!empty($user['profile_img']) && file_exists("uploads/" . $user['profile_img'])) {
    $user_image = "uploads/" . $user['profile_img'];
} else {
    $user_image = "https://ui-avatars.com/api/?name=" . urlencode($user['username']) . "&background=007bff&color=fff&size=150";
}

// 2. ดึงประวัติการเดินทาง (ทริปที่จบไปแล้ว)
$sql_history = "SELECT trips.*, 
                (SELECT COUNT(*) FROM trip_members WHERE trip_members.trip_id = trips.id) AS total_joined
                FROM trips 
                WHERE end_date < '$today' 
                AND (user_id = '$user_id' OR id IN (SELECT trip_id FROM trip_members WHERE user_id = '$user_id'))
                ORDER BY end_date DESC";
$res_history = mysqli_query($conn, $sql_history);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของฉัน - Travel Buddy</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- CSS สำหรับจัดโครงสร้างให้ Footer อยู่ล่างสุด --- */
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f4f7f6;
            display: flex;
            flex-direction: column; /* จัดวางแนวตั้ง */
            padding-top: 80px; /* เว้นที่ให้ Header fixed */
        }

        header {
            position: fixed; top: 0; left: 0; right: 0;
            background: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 5%; z-index: 1000;
        }

        .profile-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
            flex: 1 0 auto; /* หัวใจสำคัญ: ดัน Footer ลงข้างล่าง */
        }

        /* --- UI ส่วนโปรไฟล์ --- */
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            display: flex;
            gap: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 40px;
        }

        .profile-img {
            width: 180px; height: 180px;
            border-radius: 20px;
            object-fit: cover;
            border: 4px solid #f0f2f5;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .info-item { font-size: 15px; color: #444; }
        .info-item i { color: #007bff; width: 25px; }

        .bio-box {
            margin-top: 25px;
            padding: 15px;
            background: #f8fbff;
            border-left: 4px solid #007bff;
            border-radius: 8px;
        }

        /* --- History Section --- */
        .history-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .history-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            border-left: 5px solid #ccc;
        }

        /* --- Footer --- */
        footer {
            flex-shrink: 0;
            text-align: center;
            padding: 25px;
            background: #222;
            color: #bbb;
            font-size: 14px;
        }

        .nav-links { display: flex; list-style: none; gap: 25px; margin: 0; }
        .nav-links a { text-decoration: none; color: #333; }
        
        @media (max-width: 768px) {
            .profile-card { flex-direction: column; align-items: center; text-align: center; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo" style="font-size: 22px; font-weight: 600; color: #007bff;">Travel Buddy</div>
    
    <ul class="nav-links">
        <li><a href="dashboard.php">หน้าแรก</a></li>
        <li><a href="search.php">ค้นหาทริป</a></li>
        <li><a href="my-chats.php">แชทของฉัน</a></li>
    </ul>

    <div class="auth-btns" style="display: flex; align-items: center; gap: 15px;">
        <a href="profile.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: #333;">
            <img src="<?php echo $user_image; ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #007bff;">
            <span style="font-weight: 600; font-size: 14px;">คุณ <?php echo htmlspecialchars($user['username']); ?></span>
        </a>
        <button onclick="location.href='logout.php'" style="background: none; border: 1px solid #ff4d4d; color: #ff4d4d; padding: 5px 15px; border-radius: 20px; cursor: pointer;">ออกจากระบบ</button>
    </div>
</header>

<div class="profile-container">
    <div class="profile-card">
        <div style="text-align: center;">
            <img src="<?php echo $user_image; ?>" class="profile-img">
            <button onclick="location.href='edit-profile.php'" style="margin-top: 15px; width: 100%; padding: 8px; border-radius: 10px; border: 1px solid #ddd; background: #fff; cursor: pointer;">
                <i class="fas fa-edit"></i> แก้ไขโปรไฟล์
            </button>
        </div>
        
        <div style="flex: 1;">
            <h1 style="margin: 0;">คุณ <?php echo htmlspecialchars($user['username']); ?></h1>
            
            <div class="info-grid">
                <div class="info-item"><i class="fas fa-venus-mars"></i> เพศ: <?php echo (strtolower($user['gender'] ?? '') == 'male') ? 'ชาย' : 'หญิง'; ?></div>
                <div class="info-item"><i class="fas fa-calendar-alt"></i> วันเกิด: <?php echo !empty($user['birthdate']) ? date('d/m/Y', strtotime($user['birthdate'])) : '-'; ?></div>
                <div class="info-item"><i class="fas fa-briefcase"></i> อาชีพ: <?php echo htmlspecialchars($user['occupation'] ?? 'ไม่ระบุ'); ?></div>
                <div class="info-item"><i class="fas fa-phone"></i> เบอร์โทร: <?php echo htmlspecialchars($user['phone'] ?? '-'); ?></div>
                <div class="info-item"><i class="fas fa-envelope"></i> อีเมล: <?php echo htmlspecialchars($user['email']); ?></div>
                <div class="info-item"><i class="fas fa-home"></i> บ้านเกิด: <?php echo htmlspecialchars($user['hometown'] ?? '-'); ?></div>
            </div>

            <?php if(!empty($user['bio'])): ?>
                <div class="bio-box">
                    <b>แนะนำตัว:</b> <?php echo nl2br(htmlspecialchars($user['bio'])); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="history-section">
        <h2 style="font-size: 20px;"><i class="fas fa-route"></i> ประวัติการเดินทาง</h2>
        <div class="history-grid">
            <?php if (mysqli_num_rows($res_history) > 0): ?>
                <?php while($h = mysqli_fetch_assoc($res_history)): ?>
                    <div class="history-card">
                        <small style="color: #007bff;">📅 <?php echo date('d M Y', strtotime($h['end_date'])); ?></small>
                        <h4 style="margin: 5px 0;"><?php echo htmlspecialchars($h['title']); ?></h4>
                        <p style="font-size: 13px; color: #666;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($h['destination']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #999;">ยังไม่มีประวัติการเดินทาง</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer>
    &copy; 2026 Travel Buddy Co., Ltd. | เพื่อนเที่ยวที่รู้ใจคุณ
</footer>

</body>
</html>