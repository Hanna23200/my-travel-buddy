<?php
// 1. เชื่อมต่อฐานข้อมูล
include('connect.php'); 

$today = date('Y-m-d'); 

// 2. ดึงข้อมูลทริปที่ยังเปิดรับสมัครอยู่ (จำกัดโชว์แค่ 6 อันดับแรก)
$sql = "SELECT trips.*, users.username AS creator_name, users.gender AS creator_gender,
        (SELECT COUNT(*) FROM trip_members WHERE trip_members.trip_id = trips.id) AS current_members
        FROM trips
        LEFT JOIN users ON trips.user_id = users.user_id 
        WHERE trips.end_date >= '$today' 
        AND trips.status = 'open'
        ORDER BY trips.start_date ASC LIMIT 6";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Buddy - หาเพื่อนเที่ยว แชร์ค่าห้อง</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="index-style.css">
    
    <style>
        /* CSS เพิ่มเติมเฉพาะหน้า index เพื่อความรวดเร็ว */
        .trip-section { padding: 60px 5%; background: #fff; text-align: center; }
        .trip-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 25px; 
            max-width: 1200px; 
            margin: 30px auto; 
        }
        .trip-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 20px;
            padding: 25px;
            text-align: left;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .trip-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        
        .badge-status { background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
        .btn-lock { 
            width: 100%; padding: 12px; margin-top: 15px; border: none; border-radius: 10px;
            background: #f1f1f1; color: #666; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .btn-lock:hover { background: #e2bf21; color: white; }

        /* Floating Chat Button */
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
            transition: 0.3s;
        }
        .floating-chat-btn:hover { transform: scale(1.05) translateY(-5px); background-color: #0056b3; }

        @media (max-width: 768px) {
            .floating-chat-btn { bottom: 20px; right: 20px; padding: 15px; border-radius: 50%; }
            .floating-chat-btn span:last-child { display: none; } /* ซ่อนข้อความในมือถือ */
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Travel Buddy</div>
    
    <div class="auth-btns">
        <a href="login.html" class="btn-login">เข้าสู่ระบบ</a>
        <a href="register.html" class="btn-register">สมัครสมาชิก</a>
    </div>
</header>

<div class="hero">
    <div class="hero-content">
        <h1>ออกไปเที่ยวด้วยกัน...<br>แชร์ความสนุก แชร์ค่าห้อง</h1>
        <p>ค้นหาเพื่อนร่วมทริปที่ไลฟ์สไตล์ตรงกัน หารค่าห้องพักให้ประหยัดขึ้น หรือจอยกลุ่มทำกิจกรรมสนุกๆ ทั่วไทย</p>
        <div class="cta-btns">
            <button class="btn-main btn-find" onclick="checkAccess()">หาเพื่อนเที่ยวตอนนี้</button>
            <button class="btn-main btn-create" onclick="checkAccess()">สร้างทริปใหม่</button>
        </div>
    </div>
</div>

<section class="trip-section">
    <h2>🚀 ทริปที่กำลังเปิดรับเพื่อนใหม่</h2>
    <div class="trip-grid">
        <?php 
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        // --- ส่วนเช็คว่าเต็มหรือยัง ---
        $is_full = ($row['current_members'] >= $row['max_members']);
        $badge_color = $is_full ? '#dc3545' : '#28a745'; // แดงถ้าเต็ม เขียวถ้าว่าง
        $badge_text = $is_full ? '🔴 เต็มแล้ว' : '🟢 ว่าง';
?>
    <div class="trip-card">
        <span class="badge-status" style="background-color: <?php echo $badge_color; ?>;">
            <?php echo $badge_text; ?> (<?php echo $row['current_members'].'/'.$row['max_members']; ?>)
        </span>

        <h3 style="margin: 15px 0 10px; color: #333;"><?php echo $row['title']; ?></h3>
        <p style="font-size: 14px; color: #666;">📍 <b>ปลายทาง:</b> <?php echo $row['destination']; ?></p>
        <p style="font-size: 14px; color: #666;">📅 <b>เดินทาง:</b> <?php echo date('d M', strtotime($row['start_date'])); ?></p>
        
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; font-size: 13px;">
            👤 <b>โฮสต์:</b> <?php echo $row['creator_name']; ?>
        </div>

        <?php if ($is_full): ?>
            <button class="btn-lock" style="background: #eee; color: #bbb; cursor: not-allowed;" disabled>
                ทริปนี้เต็มแล้ว
            </button>
        <?php else: ?>
            <button class="btn-lock" onclick="checkAccess()">
                🔒 ล็อกอินเพื่อดูรายละเอียด
            </button>
        <?php endif; ?>
    </div>
<?php 
    }
}
?>
    </div>
    <a href="login.html" style="color: #007bff; text-decoration: underline;">ดูทริปทั้งหมด</a>
</section>

<div class="features" style="background: #f9f9f9;">
    <h2>ทำไมต้อง Travel Buddy?</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">🤝</div>
            <h3>Match ตามไลฟ์สไตล์</h3>
            <p>ระบบคัดกรองเพื่อนร่วมทริปที่นอนเวลาเดียวกัน งบใกล้กัน เพื่อลดปัญหาทะเลาะหน้างาน</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💸</div>
            <h3>แชร์ค่าใช้จ่าย</h3>
            <p>หารค่าห้องพัก วิลล่าหรู หรือค่าเหมารถเที่ยว ช่วยให้ทริปของคุณคุ้มค่าและประหยัดขึ้น</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🛡️</div>
            <h3>ปลอดภัยและไว้ใจได้</h3>
            <p>ระบบยืนยันตัวตน (Verify) และระบบรีวิวเพื่อนร่วมทริป เพื่อความสบายใจในการเดินทาง</p>
        </div>
    </div>
</div>

<footer>
    <div class="footer-links">
        <a href="#">เงื่อนไขการใช้งาน</a>
        <a href="#">นโยบายความเป็นส่วนตัว</a>
        <a href="#">ติดต่อเรา</a>
    </div>
    <p class="copyright">&copy; 2026 Travel Buddy Co., Ltd. All rights reserved.</p>
</footer>

<a href="login.html" class="floating-chat-btn">
    <span style="font-size: 20px;">💬</span>
    <span>แชทกับเพื่อนเที่ยว</span>
</a>

<script>
    function checkAccess() {
        alert("กรุณาเข้าสู่ระบบก่อนดูข้อมูลรายละเอียดทริปหรือใช้งานแชทนะครับ");
        window.location.href = "login.html"; 
    }
</script>

</body>
</html>