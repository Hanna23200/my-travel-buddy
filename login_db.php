<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// ... โค้ดเดิมของคุณ ...
?>
<?php
session_start(); // เริ่มต้น session เพื่อจำว่าใครเข้าระบบอยู่
include('connect.php'); // ไฟล์เชื่อมต่อฐานข้อมูลตัวเดียวกับหน้า register

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = mysqli_real_escape_string($conn, $_POST['user_input']);
    $password = $_POST['password'];

    // ค้นหาผู้ใช้จาก username หรือ email
    $sql = "SELECT * FROM users WHERE username = '$user_input' OR email = '$user_input' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // ตรวจสอบรหัสผ่าน (เปรียบเทียบรหัสที่พิมพ์มา กับรหัสยาวๆ ใน DB)
        if (password_verify($password, $row['password_hash'])) {
            // Login สำเร็จ! เก็บข้อมูลลง Session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            
            echo "<script>alert('ยินดีต้อนรับคุณ " . $row['username'] . "'); window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('ไม่พบชื่อผู้ใช้งานนี้'); window.history.back();</script>";
    }
}
mysqli_close($conn);
?>