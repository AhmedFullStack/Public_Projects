<?php 

session_start();
ob_start(); // بدء تخزين الناتج

//---------------------------------

?>
<?php //===================HEADER CSS ==========================

function StartCss() {
		//==============================
	 if (defined("CSS_LOADED")) {
        return;
    }
    define("CSS_LOADED", true);
	//===============================
    // تحديد المسار إلى مجلد الـ CSS
    $directory =  "components/css/";

    // التحقق مما إذا كان المجلد موجودًا
    if (!is_dir($directory)) {
        return;
    }

    // استعراض الملفات داخل المجلد مع استبعاد `.` و `..`
    $files = array_diff(scandir($directory), array('..', '.'));

    // تصفية الملفات التي تنتهي بـ .css والتحقق مما إذا كانت ملفات فعلية
    $CssFiles = array_filter($files, function($file) use ($directory) {
        return is_file($directory . $file) && pathinfo($file, PATHINFO_EXTENSION) === "css";
    });

    // طباعة أكواد الـ CSS
    foreach ($CssFiles as $CssFile) {
        echo '<link rel="stylesheet" href="' . $directory . $CssFile . '"/>' . PHP_EOL;
    }
}
//===================HEADER PHP ==========================

function IncludeAllPHPPage($directory){
	//==============================
	 if (defined("PHP_LOADED")) {
        return;
    }
    define("PHP_LOADED", true);
	//===============================
	//مسار الملفات php
	//$directory = '';
	//التاكد من ان المجلد موجود
	if(is_dir($directory)){
		//افتح الملف
		if($handle=opendir($directory)){
			//قرائة الملفات في المجلد
			while (false !== ($file = readdir($handle))){
				//تحقق من امتلاك الملف للامتداد
				if(pathinfo($file,PATHINFO_EXTENSION)=='php'&& $file != '.' && $file != '..'){
					//تحميل الملفات بستخدام include , require
					require_once($directory.'/'.$file);
					$loadedFiles[] = $file;
					//print_r($loadedFiles).'<br />';
					}//end if pathinfo
				}//Endwhile
				//اغلاق الملف
				closedir($handle);
			}//end if($handle=opendir($directory)
			else{echo'تعذر فتح الملف';}
		}//end if is_dir
		else{echo'المجلد غير موجود';}
	}//endfunction

//=================== PHP ==========================
IncludeAllPHPPage('components/PHP/');
//=================== PHP ==========================
	
	echo '<link rel="icon" href="../uploads/logo32x32.jpg"  sizes="32x32">';
	echo '<link rel="alternate" type="application/rss+xml" title="RSS 1.0 Feed for Mobileg.shop" href="https://mobileg.shop/rss.xml" />';
	 
