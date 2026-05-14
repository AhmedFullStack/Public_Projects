

 
// إضافة بعض التفاعلية البسيطة
document.addEventListener('DOMContentLoaded', function() {
    // عند النقر على بطاقة إحصائية
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('click', function() {
            this.style.borderColor = '#3498db';
            setTimeout(() => {
                this.style.borderColor = '#e0e0e0';
            }, 500);
        });
    });
    
    // تحديث الوقت في الترحيب (اختياري)
    const updateTime = () => {
        const now = new Date();
        const timeString = now.toLocaleTimeString('ar-EG', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        // يمكنك عرض الوقت في مكان ما إذا أردت
        console.log(`التوقيت الحالي: ${timeString}`);
    };
    
    updateTime();
    setInterval(updateTime, 60000); // تحديث كل دقيقة
});
 