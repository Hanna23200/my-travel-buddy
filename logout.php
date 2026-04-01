<?php
session_start();
session_unset(); // ล้างค่าตัวแปร Session ทั้งหมด
session_destroy(); // ทำลาย Session ในระบบ

// ส่งกลับไปหน้า index.html (หรือหน้าแรกของคุณ)
header("Location: index.php");
exit();
?>