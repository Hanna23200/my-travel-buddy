<?php
session_start();
include('connect.php'); 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id']; 
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $destination = mysqli_real_escape_string($conn, $_POST['destination']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']); 
    $max_members = (int)$_POST['max_members'];
    $budget = mysqli_real_escape_string($conn, $_POST['budget']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // --- Business Logic ส่วนที่เพิ่มใหม่ ---
    $gender_pref = mysqli_real_escape_string($conn, $_POST['gender_pref']); // ชาย/หญิง/รวม
    $room_type = mysqli_real_escape_string($conn, $_POST['room_type']);     // หารห้อง/มีที่พักแล้ว
    $safety_policy = mysqli_real_escape_string($conn, $_POST['safety_policy']); // มาตรการความปลอดภัย
    $trip_style = mysqli_real_escape_string($conn, $_POST['trip_style']);
    $conditions = mysqli_real_escape_string($conn, $_POST['conditions']);

    $sql = "INSERT INTO trips (
                user_id, title, destination, start_date, end_date, 
                max_members, gender_pref, room_type, budget, 
                description, safety_policy, trip_style, conditions, status
            ) VALUES (
                '$user_id', '$title', '$destination', '$start_date', '$end_date', 
                '$max_members', '$gender_pref', '$room_type', '$budget', 
                '$description', '$safety_policy', '$trip_style', '$conditions', 'open'
            )";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('🚀 ประกาศทริปสำเร็จ! ระบบความปลอดภัยพร้อมดูแลคุณแล้ว');
                window.location.href='dashboard.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>