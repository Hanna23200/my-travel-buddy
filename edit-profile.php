<?php
session_start();
include('connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$res = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์ - Travel Buddy</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="index-style.css">
    <link rel="stylesheet" href="profile-style.css"> </head>
<body>

<div class="profile-container">
    <div class="profile-card" style="display: block;">
        <h2 style="margin-bottom: 20px; color: #007bff;">📝 แก้ไขข้อมูลส่วนตัว</h2>
        
        <form action="edit-profile_db.php" method="POST" enctype="multipart/form-data">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="<?php echo !empty($user['profile_img']) ? 'uploads/'.$user['profile_img'] : 'https://ui-avatars.com/api/?name='.urlencode($user['username']); ?>" 
                     style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                <br>
                <label style="font-size: 13px; color: #666; cursor: pointer;">
                    เปลี่ยนรูปโปรไฟล์: <input type="file" name="profile_img" accept="image/*">
                </label>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <b>ชื่อผู้ใช้งาน:</b><br>
                    <input type="text" name="username" value="<?php echo $user['username']; ?>" style="width: 100%; border: 1px solid #ddd; padding: 5px; border-radius: 5px;" required>
                </div>
                <div class="info-item">
                    <b>เบอร์โทรศัพท์:</b><br>
                    <input type="text" name="phone" value="<?php echo $user['phone']; ?>" style="width: 100%; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                </div>
                <div class="info-item">
                    <b>อาชีพ:</b><br>
                    <input type="text" name="occupation" value="<?php echo $user['occupation']; ?>" style="width: 100%; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                </div>
                <div class="info-item">
                    <b>ที่อยู่ปัจจุบัน:</b><br>
                    <input type="text" name="current_location" value="<?php echo $user['current_location']; ?>" style="width: 100%; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                </div>
            </div>

            <div style="margin-top: 20px;">
                <b>แนะนำตัว (Bio):</b>
                <textarea name="bio" rows="4" style="width: 100%; border: 1px solid #ddd; padding: 10px; border-radius: 10px; margin-top: 5px;"><?php echo $user['bio']; ?></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn-edit" style="background: #28a745; max-width: 150px;">บันทึกข้อมูล</button>
                <button type="button" class="btn-edit" onclick="location.href='profile.php'" style="background: #ccc; max-width: 150px; color: #333;">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>