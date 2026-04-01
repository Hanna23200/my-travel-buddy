<?php
session_start();
include('connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$search_query = "";
$where_clause = "WHERE trips.end_date >= CURDATE() AND trips.status = 'open'";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['q']);
    $where_clause .= " AND (trips.title LIKE '%$search_query%' OR trips.destination LIKE '%$search_query%')";
}

$sql = "SELECT trips.*, users.username AS creator_name, 
        (SELECT COUNT(*) FROM trip_members WHERE trip_members.trip_id = trips.id) AS current_members
        FROM trips
        LEFT JOIN users ON trips.user_id = users.user_id 
        $where_clause
        ORDER BY trips.start_date ASC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหาทริปที่ใช่ - Travel Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard-style.css">
    <style>
        :root {
            --primary: #007bff;
            --secondary: #00c6ff;
            --accent: #ffc107;
            --danger: #ff4757;
            --success: #2ed573;
            --bg: #f4f7f6;
        }

        body { background-color: var(--bg); font-family: 'Prompt', sans-serif; }

        /* --- Header & Search Section --- */
        .search-hero {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 100px 5% 60px;
            text-align: center;
            color: white;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 10px 30px rgba(0,123,255,0.2);
        }

        .search-hero h1 { font-size: 2.2rem; margin-bottom: 10px; font-weight: 600; }

        .search-box { 
            display: flex; 
            max-width: 700px; 
            margin: 30px auto 0; 
            gap: 0; 
            background: white; 
            padding: 5px; 
            border-radius: 50px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .search-box input { 
            flex: 1; border: none; padding: 15px 25px; 
            font-size: 16px; outline: none; border-radius: 50px 0 0 50px;
        }

        .btn-search { 
            background: var(--primary); color: white; border: none; padding: 0 35px; 
            border-radius: 50px; cursor: pointer; font-weight: 600; transition: 0.3s;
        }

        .btn-search:hover { background: #0056b3; transform: scale(1.02); }

        /* --- Trip Grid & Cards --- */
        .content-container { padding: 40px 5%; max-width: 1200px; margin: 0 auto; }

        .trip-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); 
            gap: 25px; 
            margin-top: 20px; 
        }

        .trip-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .trip-card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }

        .card-body { padding: 25px; flex-grow: 1; }

        .badge-status {
            padding: 5px 15px; border-radius: 50px; font-size: 12px; font-weight: 600;
            display: inline-block; margin-bottom: 15px; text-transform: uppercase;
        }

        .bg-open { background: rgba(46, 213, 115, 0.1); color: var(--success); }
        .bg-full { background: rgba(255, 71, 87, 0.1); color: var(--danger); }

        .trip-title { font-size: 1.3rem; margin-bottom: 12px; color: #2d3436; font-weight: 600; }

        .trip-info { font-size: 14px; color: #636e72; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }

        .btn-join {
            width: 100%; padding: 14px; border: none; border-radius: 12px;
            font-weight: 600; font-size: 16px; cursor: pointer; transition: 0.3s;
            margin-top: 15px; display: block; text-align: center;
        }

        .btn-active { background: var(--accent); color: #2d3436; }
        .btn-active:hover { background: #ffca28; box-shadow: 0 5px 15px rgba(255,193,7,0.3); }

        .btn-disabled { background: #f1f2f6; color: #a4b0be; cursor: not-allowed; }

        /* --- Floating Chat --- */
        .floating-chat-btn {
            position: fixed; bottom: 30px; right: 30px;
            background: var(--primary); color: white !important; padding: 15px 25px;
            border-radius: 50px; display: flex; align-items: center; gap: 10px;
            box-shadow: 0 10px 25px rgba(0,123,255,0.3); z-index: 1000;
            font-weight: 600; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .floating-chat-btn:hover { transform: scale(1.1) translateY(-5px); background: #0056b3; }

        /* --- Mobile Responsive --- */
        @media (max-width: 768px) {
            .search-hero { padding: 80px 20px 40px; }
            .search-hero h1 { font-size: 1.6rem; }
            .search-box { flex-direction: column; background: transparent; box-shadow: none; }
            .search-box input { border-radius: 15px; margin-bottom: 10px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
            .btn-search { border-radius: 15px; padding: 15px; }
            .floating-chat-btn { bottom: 20px; right: 20px; width: 60px; height: 60px; padding: 0; justify-content: center; }
            .chat-text { display: none; }
            .trip-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Travel Buddy</div>
    <ul class="nav-links">
        <li><a href="dashboard.php">หน้าแรก</a></li>
        <li><a href="search.php" style="color: var(--primary);">ค้นหาทริป</a></li>
        <li><a href="my-chats.php">แชทของฉัน</a></li>
    </ul>
     
</header>

<div class="search-hero">
    <h1>ค้นพบโลกกว้างไปกับเพื่อนใหม่ 🌍</h1>
    <p>ค้นหาจุดหมายปลายทางที่ใช่ แล้วออกเดินทางไปพร้อมกัน</p>
    <form action="search.php" method="GET" class="search-box">
        <input type="text" name="q" placeholder="ระบุจังหวัด หรือคีย์เวิร์ดทริปที่คุณมองหา..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" class="btn-search">ค้นหาทริป</button>
    </form>
</div>

<div class="content-container">
    <?php if (!empty($search_query)): ?>
        <p style="text-align: center; color: #636e72; margin-bottom: 20px;">
            🔍 พบผลลัพธ์สำหรับ: <b>"<?php echo htmlspecialchars($search_query); ?>"</b>
        </p>
    <?php endif; ?>

    <div class="trip-grid">
        <?php 
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $is_full = ($row['current_members'] >= $row['max_members']);
        ?>
            <div class="trip-card">
                <div class="card-body">
                    <span class="badge-status <?php echo $is_full ? 'bg-full' : 'bg-open'; ?>">
                        <?php echo $is_full ? '🔴 ทริปเต็มแล้ว' : '🟢 เปิดรับเพื่อน ('.$row['current_members'].'/'.$row['max_members'].')'; ?>
                    </span>
                    <h3 class="trip-title"><?php echo $row['title']; ?></h3>
                    <div class="trip-info">📍 <span><b>จุดหมาย:</b> <?php echo $row['destination']; ?></span></div>
                    <div class="trip-info">📅 <span><b>วันที่เดินทาง:</b> <?php echo date('d M Y', strtotime($row['start_date'])); ?></span></div>
                    <div class="trip-info">👤 <span><b>โฮสต์:</b> <?php echo $row['creator_name']; ?></span></div>
                    
                    <?php if ($is_full): ?>
                        <button class="btn-join btn-disabled" disabled>ขออภัย ทริปนี้เต็มแล้ว</button>
                    <?php else: ?>
                        <button class="btn-join btn-active" onclick="location.href='join-and-chat.php?id=<?php echo $row['id']; ?>'">
                            ดูรายละเอียด / เข้าร่วม
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php 
            }
        } else {
            echo "
            <div style='grid-column: 1/-1; text-align: center; padding: 60px 20px;'>
                <div style='font-size: 50px; margin-bottom: 20px;'>🏝️</div>
                <h3 style='color: #2d3436;'>ยังไม่พบทริปที่ค้นหา...</h3>
                <p style='color: #636e72;'>ลองเปลี่ยนคำค้นหา หรือสร้างทริปใหม่ด้วยตัวเองเลย!</p>
                <button onclick=\"location.href='create-trip.php'\" class='btn-join btn-active' style='max-width: 200px; margin: 20px auto;'>+ สร้างทริปใหม่</button>
            </div>";
        }
        ?>
    </div>
</div>

<a href="my-chats.php" class="floating-chat-btn">
    <span>💬</span>
    <span class="chat-text">แชทของฉัน</span>
</a>

</body>
</html>