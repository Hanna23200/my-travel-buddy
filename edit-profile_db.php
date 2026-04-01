<?php
session_start();
include('connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $occupation = mysqli_real_escape_string($conn, $_POST['occupation']);
    $current_location = mysqli_real_escape_string($conn, $_POST['current_location']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);

    // จัดการอัปโหลดรูปภาพ (ถ้ามีการเลือกรูปใหม่)
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $ext = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
        $file_name = "user_" . $user_id . "_" . time() . "." . $ext;
        $target = "uploads/" . $file_name;

        if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $target)) {
            // อัปเดตชื่อไฟล์รูปในฐานข้อมูลด้วย
            mysqli_query($conn, "UPDATE users SET profile_img = '$file_name' WHERE user_id = '$user_id'");
        }
    }

    // อัปเดตข้อมูลข้อความ
    $sql = "UPDATE users SET 
            username = '$username', 
            phone = '$phone', 
            occupation = '$occupation', 
            current_location = '$current_location', 
            bio = '$bio' 
            WHERE user_id = '$user_id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('อัปเดตโปรไฟล์สำเร็จ!'); window.location='profile.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
mysqli_close($conn);
?>