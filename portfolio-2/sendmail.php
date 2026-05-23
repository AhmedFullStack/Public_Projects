<?php
// المسار إلى ملفات PHPMailer
$phpmailer_path = './libraries/PHPMailer/src/';

// تضمين ملفات PHPMailer
require_once $phpmailer_path . 'PHPMailer.php';
require_once $phpmailer_path . 'SMTP.php';
require_once $phpmailer_path . 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// التحقق من أن النموذج قد أُرسل
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // جمع البيانات من النموذج
    $name = strip_tags(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST['subject']));
    $message = trim($_POST['message']);
    
    // التحقق من صحة البيانات
    if (empty($name) || empty($message)) {
        header('Location: https://mobileg.shop/contact.php?status=error');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: https://mobileg.shop/contact.php?status=error');
        exit;
    }
    
    // إنشاء محتوى البريد الرئيسي (الذي يصل إلى المسؤول)
    $admin_email_content = "الاسم: $name\n";
    $admin_email_content .= "البريد الإلكتروني: $email\n";
    if (!empty($subject)) {
        $admin_email_content .= "الموضوع: $subject\n";
    }
    $admin_email_content .= "\nالرسالة:\n$message\n";
    
    $admin_email_content_html = "
        <div dir='rtl'>
            <h2>رسالة جديدة من نموذج الاتصال</h2>
            <p><strong>الاسم:</strong> $name</p>
            <p><strong>البريد الإلكتروني:</strong> $email</p>
            <p><strong>الموضوع:</strong> " . (!empty($subject) ? $subject : 'بدون موضوع') . "</p>
            <p><strong>الرسالة:</strong></p>
            <div style='background: #f9f9f9; padding: 15px; border-radius: 5px;'>
                " . nl2br(htmlspecialchars($message)) . "
            </div>
        </div>
    ";
    
    // إنشاء محتوى الرد التلقائي للعميل
    $client_email_content = "عزيزي $name,\n\nشكراً لتواصلك مع mobileg.shop. لقد استلمنا رسالتك وسنقوم بالرد عليك في أقرب وقت ممكن.\n\nمع تحيات،\nفريق الدعم";
    
    $client_email_content_html = "
        <div dir='rtl' style='font-family: Arial, sans-serif;'>
            <h2 style='color: #4CAF50;'>شكراً لتواصلك مع mobileg.shop</h2>
            <p>عزيزي <strong>$name</strong>،</p>
            <p>شكراً لتواصلك معنا. لقد استلمنا رسالتك وسنقوم بالرد عليك في أقرب وقت ممكن.</p>
            <p>مع تحيات،<br>فريق mobileg.shop</p>
            <hr>
            <p style='font-size: 12px; color: #777;'>هذه رسالة تلقائية، يرجى عدم الرد عليها.</p>
        </div>
    ";
    
    // إعدادات SMTP (يمكنك تغييرها لاحقاً لتناسب بريدك الاحترافي)
    $smtp_host = 'smtp.gmail.com';
    $smtp_username = 'alprof89714@gmail.com';
    $smtp_password = 'orns alng oemm ftgr';
    $smtp_secure = PHPMailer::ENCRYPTION_SMTPS; // أو 'tls' مع المنفذ 587
    $smtp_port = 465;
    $from_email = 'support@mobileg.shop'; // البريد الذي يظهر كمرسل
    $from_name = 'mobileg.shop';
    $admin_email = 'ahmedalaaeldeenmahmoud@gmail.com'; // بريد المسؤول المستقبل

    // إرسال البريد الرئيسي إلى المسؤول
    $mail = new PHPMailer(true);
    $admin_sent = false;
    
    try {
        // إعدادات الخادم
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_secure;
        $mail->Port = $smtp_port;
        $mail->CharSet = 'UTF-8';
        
        // المرسل
        $mail->setFrom($from_email, $from_name);
        
        // المستلم: المسؤول
        $mail->addAddress($admin_email, 'Ahmed');
        
        // الرد على المرسل (العميل)
        $mail->addReplyTo($email, $name);
        
        // محتوى البريد الرئيسي
        $mail->isHTML(true);
        $mail->Subject = "رسالة جديدة: " . (!empty($subject) ? $subject : 'بدون موضوع');
        $mail->Body = $admin_email_content_html;
        $mail->AltBody = $admin_email_content;
        
        // إرسال البريد الرئيسي
        $mail->send();
        $admin_sent = true;
        
        // الآن إرسال الرد التلقائي للعميل
        // نعيد تعيين المستلمين ونبقي باقي الإعدادات
        $mail->clearAddresses();
        $mail->clearReplyTos();
        
        // المستلم: العميل
        $mail->addAddress($email, $name);
        
        // الرد على هذا البريد يجب أن يذهب إلى الدعم
        $mail->addReplyTo($from_email, $from_name);
        
        // محتوى البريد للعميل
        $mail->Subject = 'شكراً لتواصلك مع mobileg.shop';
        $mail->Body = $client_email_content_html;
        $mail->AltBody = $client_email_content;
        
        // إرسال البريد للعميل
        $mail->send();
        
        // توجيه المستخدم إلى صفحة النجاح
        header('Location: https://mobileg.shop/contact.php?status=success');
        exit;
        
    } catch (Exception $e) {
        // تسجيل الخطأ في سجل الأخطاء
        error_log("فشل إرسال البريد: " . $mail->ErrorInfo);
        
        // إذا فشل البريد الرئيسي، نعرض خطأ
        if (!$admin_sent) {
            header('Location: https://mobileg.shop/contact.php?status=error');
            exit;
        }
        
        // إذا نجح الرئيسي وفشل التأكيد، نوجه إلى نجاح مع تحذير (يمكنك تعديل صفحة contact.php لقراءة هذا المتغير)
        header('Location: https://mobileg.shop/contact.php?status=success&warning=1');
        exit;
    }
} else {
    // إذا حاول المستخدم الوصول إلى هذا الملف مباشرة
    header('Location: https://mobileg.shop/contact.php');
    exit;
}
?>