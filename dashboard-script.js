// dashboard-script.js

// ฟังก์ชันออกจากระบบ
// dashboard-script.js

function logout() {
    if(confirm('คุณต้องการออกจากระบบใช่หรือไม่?')) {
        // วิ่งไปที่ไฟล์ logout.php เพื่อล้างค่าในฐานข้อมูล/ระบบ
        window.location.href = 'logout.php';
    }
}

// ฟังก์ชันโหลดข้อมูลทริป (ตัวอย่างแบบดึงจาก localStorage เดิมของคุณ)
document.addEventListener('DOMContentLoaded', function() {
    const tripDisplayArea = document.getElementById('tripDisplayArea');
    const allTrips = JSON.parse(localStorage.getItem('allTrips')) || [];

    if (allTrips.length === 0) {
        tripDisplayArea.innerHTML = "<p style='grid-column: 1/-1;'>ยังไม่มีการประกาศทริปในขณะนี้</p>";
        return;
    }

    allTrips.reverse().forEach(trip => {
        const badgeColor = trip.accStatus === 'need' ? '#ff4757' : '#2ed573';
        const badgeText = trip.accStatus === 'need' ? '🔍 หาคนหาร' : '🏠 มีที่พักแล้ว';

        const card = `
            <div class="feature-card">
                <span style="background:${badgeColor}; color:white; padding:4px 12px; border-radius:20px; font-size:12px;">${badgeText}</span>
                <h3 style="margin: 15px 0 10px; color:#007bff;">${trip.title}</h3>
                <p style="font-size:14px; color:#666;">📅 ${trip.startDate} - ${trip.endDate}</p>
                <button onclick="alert('Trip ID: ${trip.id}')" style="margin-top:15px; width:100%; padding:10px; border:none; background:#f0f0f0; border-radius:10px; cursor:pointer;">ดูรายละเอียด</button>
            </div>
        `;
        tripDisplayArea.innerHTML += card;
    });
});