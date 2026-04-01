<?php
session_start();
include('connect.php');

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $trip_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // 1. เช็คก่อนว่าเคยเข้าร่วมหรือยัง เพื่อไม่ให้ข้อมูลซ้ำ
    $check = mysqli_query($conn, "SELECT * FROM trip_members WHERE trip_id = '$trip_id' AND user_id = '$user_id'");
    
    if (mysqli_num_rows($check) == 0) {
        // 2. ถ้ายังไม่เคยเข้า ให้บันทึกลงฐานข้อมูล
        $sql = "INSERT INTO trip_members (trip_id, user_id, joined_at) VALUES ('$trip_id', '$user_id', NOW())";
        mysqli_query($conn, $sql);
    }

    // 3. วิ่งไปหน้าแชทของทริปนั้นทันที (เหมือนเปิดหน้าต่างแชท Facebook)
    header("Location: chat.php?trip_id=$trip_id");
    exit();
}
?>


<?php
session_start();
include('connect.php');

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $trip_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // 1. เช็คก่อนว่าเคยเข้าร่วมหรือยัง
    $check = mysqli_query($conn, "SELECT * FROM trip_members WHERE trip_id = '$trip_id' AND user_id = '$user_id'");
    
    if (mysqli_num_rows($check) == 0) {
        // 2. จุดสำคัญ: ต้องเป็น joined_at ให้ตรงกับหัวตารางใน HeidiSQL ของคุณเป๊ะๆ
        $sql = "INSERT INTO trip_members (trip_id, user_id, joined_at) VALUES ('$trip_id', '$user_id', NOW())";
        
        if (!mysqli_query($conn, $sql)) {
            die("เกิดข้อผิดพลาดในการบันทึก: " . mysqli_error($conn));
        }
    }

    // 3. วิ่งไปหน้าแชท
    header("Location: chat.php?trip_id=$trip_id");
    exit();
}
?>