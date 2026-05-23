
<?php
function loadJsFiles() {
    // تحديد المسار إلى المجلد الذي يحتوي على الملفات
    $directory =   "components/js/";

    // التحقق من وجود المجلد
    if (!is_dir($directory)) {
        return;
    }

    // استخدام scandir لاستعراض الملفات
    $files = array_diff(scandir($directory), array('..', '.'));

    // تصفية الملفات التي تنتهي بـ .js
    $JsFiles = array_filter($files, function($file) use ($directory) {
        return is_file($directory . $file) && pathinfo($file, PATHINFO_EXTENSION) === "js";
    });

    // طباعة أكواد JavaScript
    foreach ($JsFiles as $JsFile) {
        echo '<script src="' . $directory . $JsFile . '"></script>' . PHP_EOL;
    }
}

loadJsFiles();



//==================
$html = ob_get_clean();

$newHtml = preg_replace_callback(
    '/(<\/head\s*>)/i',
    function($matches) {
        return $matches[1] . StartCss();
    },
    $html
);

echo $newHtml;