<?php
function HeaderMetaCodes(

    // الإعدادات الأساسية
    $title = '',
    $description = '',
    $keywords = '',
    $author = '',
    $charset = 'UTF-8',
    $ContentLanguage = 'ar',
    
    // إعدادات محركات البحث
    $robots = 'index, follow',
    $pageURL = '',
    
    // إعدادات الوسائط الاجتماعية
    $pageIMGUrl = '',
    
    // إعدادات التخزين المؤقت والأداء
 
    $CacheControl = 'no-store',
    $pragma = 'no-cache',
    
    // إعدادات التخصيص
    $themeColor = '#ff5733',
    $appleTouchIconUrl = '',
    $ApplicationName = '',
    $msapplicationTileColor = '',
    $AppleMobileWebAppStatusBarStyle = 'black-translucent',
    
    // التحقق من الملكية
    $GoogleSiteVerification = '',
    $MSValidate = '',
    
    // معلومات التقنية
    $Generator = ''
	
) {
    // بداية إخراج رأس HTML
    $output = '';
	
		// adsense
	$output .= '<meta name="google-adsense-account" content="ca-pub-5472772862993262">';
    
    // 1. إعدادات الأساسية
    $output .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    $output .= '<meta http-equiv="Content-Language" content="' . htmlspecialchars($ContentLanguage) . '">';
    $output .= '<meta charset="' . htmlspecialchars($charset) . '">';
    $output .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
    
    // 2. معلومات المحتوى (يتم عرضها فقط إذا كانت تحتوي على قيم)
    if (!empty($title)) {
        $output .= '<title>' . htmlspecialchars($title) . '</title>';
        $output .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">';
        $output .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">';
    }
    
    if (!empty($description)) {
        $output .= '<meta name="description" content="' . htmlspecialchars($description) . '">';
        $output .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">';
        $output .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">';
    }
    
    if (!empty($keywords)) {
        $output .= '<meta name="keywords" content="' . htmlspecialchars($keywords) . '">';
    }
    
    if (!empty($author)) {
        $output .= '<meta name="author" content="' . htmlspecialchars($author) . '">';
    }
    
    // 3. إعدادات محركات البحث
    $output .= '<meta name="robots" content="' . htmlspecialchars($robots) . '">';
    
    if (!empty($pageURL)) {
        $output .= '<link rel="canonical" href="' . htmlspecialchars($pageURL) . '">';
        $output .= '<meta property="og:url" content="' . htmlspecialchars($pageURL) . '">';
    }
    
    // 4. وسائط الاجتماعية
    if (!empty($pageIMGUrl)) {
        $output .= '<meta property="og:image" content="' . htmlspecialchars($pageIMGUrl) . '">';
        $output .= '<meta name="twitter:image" content="' . htmlspecialchars($pageIMGUrl) . '">';
        $output .= '<meta name="twitter:card" content="summary_large_image">';
    } else {
        $output .= '<meta name="twitter:card" content="summary">';
    }
    
    // 5. إعدادات التخزين المؤقت والأداء
    
    $output .= '<meta http-equiv="pragma" content="' . htmlspecialchars($pragma) . '">';
    $output .= '<meta http-equiv="Cache-Control" content="' . htmlspecialchars($CacheControl) . '">';
    
    // 6. تحسينات الأمان
    $output .= '<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests; default-src \'self\' https:; script-src \'self\' \'unsafe-inline\' https://trusted-scripts.com;">';
    $output .= '<meta http-equiv="X-Content-Type-Options" content="nosniff">';
    $output .= '<meta name="referrer" content="strict-origin-when-cross-origin">';
    $output .= '<meta http-equiv="Strict-Transport-Security" content="max-age=31536000; includeSubDomains">';
    $output .= '<meta http-equiv="X-Frame-Options" content="SAMEORIGIN">';
    $output .= '<meta http-equiv="X-XSS-Protection" content="1; mode=block">';
    
    // 7. التخصيص والمظهر
    if (!empty($themeColor)) {
        $output .= '<meta name="theme-color" content="' . htmlspecialchars($themeColor) . '">';
    }
    
    if (!empty($appleTouchIconUrl)) {
        $output .= '<link rel="apple-touch-icon" href="' . htmlspecialchars($appleTouchIconUrl) . '">';
    }
    
    if (!empty($ApplicationName)) {
        $output .= '<meta name="application-name" content="' . htmlspecialchars($ApplicationName) . '">';
    }
    
    if (!empty($msapplicationTileColor)) {
        $output .= '<meta name="msapplication-TileColor" content="' . htmlspecialchars($msapplicationTileColor) . '">';
    }
    
    if (!empty($AppleMobileWebAppStatusBarStyle)) {
        $output .= '<meta name="apple-mobile-web-app-status-bar-style" content="' . htmlspecialchars($AppleMobileWebAppStatusBarStyle) . '">';}
    
    // 8. التحقق من الملكية
    if (!empty($GoogleSiteVerification)) {
        $output .= '<meta name="google-site-verification" content="' . htmlspecialchars($GoogleSiteVerification) . '">';
    }
    
    if (!empty($MSValidate)) {
        $output .= '<meta name="msvalidate.01" content="' . htmlspecialchars($MSValidate) . '">';
    }
    
    // 9. معلومات التقنية
    if (!empty($Generator)) {
        $output .= '<meta name="generator" content="' . htmlspecialchars($Generator) . '">';
    }
    

		   

    // إخراج كل محتوى الـ meta tags
	
	
    static $executed = false;
    if (!$executed) {
        // الكود الذي تريد تنفيذه مرة واحدة فقط
      echo $output;
        
        $executed = true;
    }

}
?>

<?php /*?>use this
<?php */?>
<?php /*?><?php
HeaderMetaCodes(
    'عنوان موقعي', // العنوان
    'وصف مختصر لموقعي', // الوصف
    'كلمة مفتاحية1, كلمة مفتاحية2', // الكلمات المفتاحية
    'اسمي', // المؤلف
    'UTF-8', // الترميز
    'ar', // اللغة
    'index, follow', // إعدادات المحركات
    'https://example.com', // رابط الصفحة
    'https://example.com/صورة.jpg', // صورة الوسائط الاجتماعية
    '3600', // التحديث التلقائي
    'no-store', // التحكم بالكاش
    'no-cache', // البراجما
    '#ff5733', // لون المظهر
    '/apple-touch-icon.png', // أيقونة Apple
    'اسم التطبيق', // اسم التطبيق
    '#ff5733', // لون بلاط Windows
    'black-translucent', // شريط حالة iOS
    'google-verification-code', // تحقق Google
    'bing-verification-code', // تحقق Bing
    'WordPress' // منشئ الموقع
);
?><?php */?>