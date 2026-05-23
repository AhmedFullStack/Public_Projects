<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>أحمد علاء الدين | Full-Stack Web Developer & UI/UX Expert</title>
    <meta name="description" content="موقع أحمد علاء الدين، مطور ويب شامل (Front-end & Back-end) ومصمم تجربة مستخدم. أقدم حلولاً برمجية آمنة وسريعة لأصحاب الأعمال.">
    <meta name="keywords" content="مطور ويب, مصمم مواقع, UI/UX, مبرمج Full-Stack, أحمد علاء الدين, تصميم مواقع, برمجة ويب">
    <meta name="author" content="Ahmed AlaaEldin">
    <link rel="canonical" href="https://yourdomain.com">
    
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; font-src 'self' https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="navbar">
        <div class="container nav-content">
            <div class="logo">
                <a href="../#">Ahmed<span>FullStack</span></a>
            </div>
            <nav aria-label="Main Navigation">
                <ul class="nav-links">
                    <li><a href="../#about">من أنا</a></li>
                    <li><a href="../#skills">مهاراتي</a></li>
                    <li><a href="../#portfolio">أعمالي</a></li>
                    <li><a href="../#services">خدماتي</a></li>
                </ul>
            </nav>
            <a href="#contact" class="btn btn-primary">تواصل معي</a>
        </div>
    </header>

    <main>
 <?php
        // عرض رسائل النجاح أو الخطأ
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                ?>
                <div>تم إرسال رسالتك بنجاح. سنرد عليك قريباً.</div>
                <?php 
            } elseif ($_GET['status'] == 'error') {

                
                ?>
                <div class="bg-red color-wight">عذراً، حدث خطأ أثناء إرسال رسالتك. يرجى المحاولة مرة أخرى.</div>
                <?php 
            }
        }
 ?>
    </main>
<footer class="footer">
        <div class="container footer-content">
            <div class="footer-brand">
                <a href="#" class="footer-logo">Ahmed<span>FullStack</span></a>
                <p>لتطوير المواقع وتصميم واجهات المستخدم.</p>
            </div>
            
            <div class="footer-social">
                <a href="https://github.com/AhmedFullStack" target="_blank" rel="noopener">GitHub</a>
                <a href="https://www.linkedin.com/in/ahmedfullstack" target="_blank" rel="noopener">LinkedIn</a>
                <a href="https://web.facebook.com/AhmedFullStack.Studio/" target="_blank" rel="noopener">Facebook</a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2026 جميع الحقوق محفوظة | تم التصميم والبرمجة بكل 💻 بواسطة أحمد علاء الدين.</p>
        </div>
    </footer>
    <script src="gsap.min.js"></script>
    <script src="script.js"></script>
</body>
</html>