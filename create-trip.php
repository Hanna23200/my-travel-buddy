<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างทริปสุดคูลของคุณ - Travel Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard-style.css"> 
    <link rel="stylesheet" href="create-trip-style.css">
    <style>
        /* สไตล์เพิ่มเติมสำหรับส่วนที่แบ่งเป็น Step */
        .form-section { border-bottom: 1px solid #eee; padding-bottom: 30px; margin-bottom: 30px; }
        .form-section h3 { font-weight: 600; color: #007bff; margin-bottom: 15px; }
        .input-group-row { display: flex; gap: 15px; flex-wrap: wrap; }
        .input-group-row .form-group { flex: 1; min-width: 250px; }
        .trip-style-options { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
        .trip-style-option { border: 1px solid #ddd; padding: 8px 15px; border-radius: 20px; cursor: pointer; transition: 0.3s; }
        .trip-style-option:hover, .trip-style-option.active { background-color: #007bff; color: white; border-color: #007bff; }
    </style>
</head>
<body>

<header>
    <div class="logo">Travel Buddy</div>
    <div class="user-profile">
        <button class="btn-logout" onclick="location.href='dashboard.php'">ย้อนกลับ</button>
    </div>
</header>

<section class="hero" style="height: 40vh; margin-top: 60px;">
    <div class="hero-content">
        <h1>สร้างทริปในฝันของคุณ</h1>
        <p>ออกแบบทริปที่ใช่ หาเพื่อนที่ชอบ แล้วไปลุยกัน!</p>
    </div>
</section>

<div class="form-container">
    <form action="save-trip.php" method="POST">
        
        <div class="form-section">
            <h3>📍 ข้อมูลพื้นฐานทริป</h3>
            <div class="form-group">
                <label>หัวข้อทริป (เช่น ไปน่านแชร์ค่ารถกันครับ)</label>
                <input type="text" name="title" placeholder="ระบุชื่อทริปที่น่าสนใจ" required>
            </div>
            <div class="input-group-row">
                <div class="form-group">
                    <label>สถานที่ปลายทาง / จังหวัด</label>
                    <input type="text" name="destination" placeholder="เช่น เชียงใหม่, ภูกระดึง" required>
                </div>
                <div class="form-group">
                    <label>จำนวนคนที่รับ (ไม่รวมตัวเรา)</label>
                    <input type="number" name="max_members" min="1" max="50" placeholder="ระบุจำนวน" required>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>📅 วันที่และแผนเดินทาง</h3>
            <div class="input-group-row">
                <div class="form-group">
                    <label>วันที่เริ่มออกเดินทาง</label>
                    <input type="date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label>วันที่เดินทางกลับ</label>
                    <input type="date" name="end_date">
                </div>
            </div>
            <div class="form-group">
                <label>รายละเอียด/แพลนเที่ยว (Day by Day)</label>
                <textarea name="description" rows="6" placeholder="เล่าแพลนเดินทางคร่าวๆ เช่น วันแรก: , วันที่สอง: ..."></textarea>
            </div>
        </div>

        <div class="form-section">
            <h3>💰 สไตล์และงบประมาณ</h3>
            <div class="form-group">
                <label>งบประมาณโดยประมาณ (บาท)</label>
                <input type="number" name="budget" placeholder="เช่น 3000" required>
            </div>
            <div class="form-group">
                <label>สไตล์ทริป (เลือกได้มากกว่า 1)</label>
                <div class="trip-style-options">
                    <div class="trip-style-option">สายชิล</div>
                    <div class="trip-style-option">สายลุย</div>
                    <div class="trip-style-option">สายกิน</div>
                    <div class="trip-style-option">สายถ่ายรูป</div>
                    <div class="trip-style-option">สายวัฒนธรรม</div>
                    <input type="hidden" name="trip_style" id="trip_style_input">
                </div>
            </div>
            <div class="form-group">
                <label>เงื่อนไข/สิ่งที่รวมอยู่ (เช่น ค่าที่พัก, ค่ารถ, ไม่รวมค่ากิน)</label>
                <textarea name="conditions" rows="3" placeholder="ระบุเงื่อนไขการแชร์ค่าใช้จ่าย..."></textarea>
            </div>
        </div>

        <button type="submit" class="btn-submit">ลงประกาศทริปสุดคูล!</button>
    </form>
</div>

<script>
    // ส่วน JS สำหรับเลือกสไตล์ทริป (เก็บค่าลง input hidden)
    const styleOptions = document.querySelectorAll('.trip-style-option');
    const styleInput = document.getElementById('trip_style_input');
    const selectedStyles = [];

    styleOptions.forEach(option => {
        option.addEventListener('click', () => {
            option.classList.toggle('active');
            const style = option.textContent;
            if (option.classList.contains('active')) {
                selectedStyles.push(style);
            } else {
                const index = selectedStyles.indexOf(style);
                if (index > -1) {
                    selectedStyles.splice(index, 1);
                }
            }
            styleInput.value = selectedStyles.join(', ');
        });
    });
</script>

</body>
</html>