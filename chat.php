<?php
session_start();
include('connect.php');

if (!isset($_GET['trip_id'])) {
    die("ไม่พบรหัสทริป");
}

$trip_id = mysqli_real_escape_string($conn, $_GET['trip_id']);
$user_id = $_SESSION['user_id'];

// --- 1. จัดการการส่งข้อความ/รูปภาพ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message_text = mysqli_real_escape_string($conn, $_POST['message'] ?? '');
    $image_path = "";

    // จัดการอัปโหลดรูปภาพ
    if (isset($_FILES['chat_image']) && $_FILES['chat_image']['error'] == 0) {
        $target_dir = "uploads/chat/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["chat_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = "IMG_" . time() . "_" . rand(1000,9999) . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["chat_image"]["tmp_name"], $target_file)) {
            $image_path = $new_filename;
        }
    }

    if (!empty($message_text) || !empty($image_path)) {
        // ** สำคัญ: อย่าลืมไปเพิ่มคอลัมน์ image_path ในตาราง messages ใน HeidiSQL นะครับ **
        $sql_insert = "INSERT INTO messages (trip_id, user_id, message_text, image_path) 
                       VALUES ('$trip_id', '$user_id', '$message_text', '$image_path')";
        mysqli_query($conn, $sql_insert);
        header("Location: chat.php?trip_id=" . $trip_id);
        exit();
    }
}

// 2. ดึงข้อมูลทริป
$sql_trip = "SELECT title, destination FROM trips WHERE id = '$trip_id'";
$res_trip = mysqli_query($conn, $sql_trip);
$trip_info = mysqli_fetch_assoc($res_trip);

// 3. ดึงรายการข้อความ
$sql_msg = "SELECT messages.*, users.username, users.profile_img 
            FROM messages 
            JOIN users ON messages.user_id = users.user_id 
            WHERE messages.trip_id = '$trip_id' 
            ORDER BY messages.created_at ASC";
$res_msg = mysqli_query($conn, $sql_msg);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ทริป: <?php echo $trip_info['title']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0084ff;
            --bg-chat: #f0f2f5;
            --font-main: 'Prompt', sans-serif;
        }
        body, html { margin: 0; padding: 0; height: 100%; font-family: var(--font-main); background: #fff; }

        /* Container */
        .chat-app { display: flex; flex-direction: column; height: 100vh; max-width: 600px; margin: 0 auto; background: var(--bg-chat); position: relative; }

        /* Header */
        .chat-header {
            background: linear-gradient(135deg, #0084ff, #00c6ff);
            color: white; padding: 15px; display: flex; align-items: center; gap: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 10;
        }
        .btn-back { color: white; text-decoration: none; font-size: 20px; }
        .trip-details h3 { margin: 0; font-size: 16px; }
        .trip-details p { margin: 0; font-size: 11px; opacity: 0.8; }

        /* Messages Area */
        .chat-messages { flex: 1; padding: 20px 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }

        .message { max-width: 80%; display: flex; flex-direction: column; }
        .msg-left { align-self: flex-start; }
        .msg-right { align-self: flex-end; }

        .bubble {
            padding: 10px 14px; border-radius: 18px; font-size: 14px; line-height: 1.5;
            position: relative; word-wrap: break-word;
        }
        .msg-left .bubble { background: white; color: #333; border-bottom-left-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .msg-right .bubble { background: var(--primary); color: white; border-bottom-right-radius: 4px; }

        .chat-img { max-width: 100%; border-radius: 10px; margin-top: 5px; cursor: pointer; }
        .time { font-size: 9px; opacity: 0.6; margin-top: 4px; align-self: flex-end; }
        .sender { font-size: 11px; font-weight: 600; margin-bottom: 3px; color: #555; }
        .msg-right .sender { display: none; }

        /* Quick Actions Bar */
        .quick-actions {
            display: flex; gap: 10px; padding: 10px 15px; background: white; border-bottom: 1px solid #eee;
        }
        .action-link {
            text-decoration: none; font-size: 11px; background: #f8f9fa; border: 1px solid #ddd;
            padding: 5px 12px; border-radius: 20px; color: #555; display: flex; align-items: center; gap: 5px;
        }

        /* Input Area */
        .chat-input-area { background: white; padding: 10px 15px; padding-bottom: calc(10px + env(safe-area-inset-bottom)); }
        .input-wrapper { display: flex; align-items: center; gap: 10px; background: #f0f2f5; padding: 5px 15px; border-radius: 25px; }
        .input-wrapper input { flex: 1; border: none; background: transparent; padding: 10px 0; outline: none; font-family: var(--font-main); }
        
        .icon-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: #888; padding: 5px; }
        .btn-send { color: var(--primary); font-weight: 600; }

        /* Hidden File Input */
        #fileInput { display: none; }
    </style>
</head>
<body>

<div class="chat-app">
    <div class="chat-header">
        <a href="dashboard.php" class="btn-back">❮</a>
        <div class="trip-details">
            <h3><?php echo htmlspecialchars($trip_info['title']); ?></h3>
            <p>📍 ปลายทาง: <?php echo htmlspecialchars($trip_info['destination']); ?></p>
        </div>
    </div>

    <div class="quick-actions">
        <a href="https://www.agoda.com/search?city=<?php echo urlencode($trip_info['destination']); ?>" target="_blank" class="action-link">🏨 จองที่พัก</a>
        <button onclick="sendLocation()" class="action-link">📍 แชร์พิกัด</button>
        <button onclick="document.getElementById('fileInput').click()" class="action-link">🖼️ ส่งรูป</button>
    </div>

    <div class="chat-messages" id="chatBox">
        <?php while($msg = mysqli_fetch_assoc($res_msg)): 
            $is_me = ($msg['user_id'] == $user_id);
        ?>
            <div class="message <?php echo $is_me ? 'msg-right' : 'msg-left'; ?>">
                <div class="sender"><?php echo $msg['username']; ?></div>
                <div class="bubble">
                    <?php if(!empty($msg['image_path'])): ?>
                        <img src="uploads/chat/<?php echo $msg['image_path']; ?>" class="chat-image chat-img">
                    <?php endif; ?>
                    
                    <?php if(!empty($msg['message_text'])): ?>
                        <div class="text"><?php echo nl2br(htmlspecialchars($msg['message_text'])); ?></div>
                    <?php endif; ?>
                    <div class="time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <form class="chat-input-area" method="POST" enctype="multipart/form-data" id="chatForm">
        <input type="file" name="chat_image" id="fileInput" accept="image/*" onchange="previewImage()">
        <div class="input-wrapper">
            <input type="text" name="message" id="msgInput" placeholder="พิมพ์ข้อความ..." autocomplete="off">
            <button type="submit" class="icon-btn btn-send">ส่ง</button>
        </div>
    </form>
</div>

<script>
    const chatBox = document.getElementById("chatBox");
    chatBox.scrollTop = chatBox.scrollHeight;

    // ฟังก์ชันส่งพิกัด
    function sendLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                const gmapLink = `https://www.google.com/maps?q=${lat},${lon}`;
                document.getElementById('msgInput').value = "📍 พิกัดของฉัน: " + gmapLink;
                // สามารถสั่ง Submit ฟอร์มอัตโนมัติได้ถ้าต้องการ
            });
        } else {
            alert("เบราว์เซอร์ของคุณไม่รองรับการแชร์พิกัด");
        }
    }

    // แจ้งเตือนเมื่อเลือกรูปภาพ
    function previewImage() {
        const file = document.getElementById('fileInput').files[0];
        if(file) {
            document.getElementById('msgInput').placeholder = "เลือกรูปภาพแล้ว: " + file.name;
            document.getElementById('msgInput').style.color = "#0084ff";
        }
    }
</script>

</body>
</html>