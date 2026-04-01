<?php
$servername = "localhost";
$username = "root"; // ตามที่ตั้งไว้ใน XAMPP/Wamp
$password = "";     // ปกติจะว่างไว้
$dbname = "users";  // ชื่อ Database จากรูปของคุณ

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>