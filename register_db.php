<?php
include('connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. รับข้อมูลข้อความ
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $birthdate = mysqli_real_escape_string($conn, $_POST['birthdate']);
    $occupation = mysqli_real_escape_string($conn, $_POST['occupation']);
    $hometown = mysqli_real_escape_string($conn, $_POST['hometown']);
    $current_location = mysqli_real_escape_string($conn, $_POST['current_location']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);

    // 2. ส่วนจัดการรูปภาพ (เพิ่มใหม่)
    $profile_img_name = ""; // ตั้งค่าว่างไว้ก่อนกรณีไม่ได้อัปโหลด
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $ext = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
        $new_filename = "user_" . time() . "." . $ext; // ตั้งชื่อไฟล์ใหม่กันซ้ำ เช่น user_171195.jpg
        $target_dir = "uploads/";
        $target_file = $target_dir . $new_filename;

        // ตรวจสอบว่ามีโฟลเดอร์ uploads หรือยัง ถ้าไม่มีให้สร้าง
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $target_file)) {
            $profile_img_name = $new_filename; // เก็บชื่อไฟล์ไว้ลงฐานข้อมูล
        }
    }

    // 3. ปรับ SQL INSERT (เพิ่มคอลัมน์ profile_img และค่า $profile_img_name)
    $sql = "INSERT INTO users (username, email, password_hash, gender, phone, birthdate, occupation, hometown, current_location, bio, profile_img, is_verified, trust_score) 
            VALUES ('$user', '$email', '$pass', '$gender', '$phone', '$birthdate', '$occupation', '$hometown', '$current_location', '$bio', '$profile_img_name', 0, 0)";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('สมัครสมาชิกสำเร็จ!'); window.location='login.html';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
mysqli_close($conn);
?>