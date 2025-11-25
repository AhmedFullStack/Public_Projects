document.addEventListener('DOMContentLoaded', function () {
    // تحديد جميع العناصر التي تحتوي على سمة data-bs-content
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-content]'));
    
    // تفعيل Popover
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        // إذا لم يتم تحديد data-bs-trigger، سيستخدم الإعداد الافتراضي وهو 'click'
        // لذلك، يجب التأكد من ضبط 'trigger' على 'hover focus' في HTML أو هنا في الخيارات:
        return new bootstrap.Popover(popoverTriggerEl)
    });
});
 // تفعيل جميع الـ Tooltips في الصفحة
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
}) 

function startOfferCountdown(durationInSeconds) {
    let timerElement = document.getElementById("timer_offer");
    let totalSeconds = durationInSeconds;

    let timerInterval = setInterval(() => {
        // 1. العمليات الحسابية لاستخراج الساعات والدقائق والثواني
        let hours = Math.floor(totalSeconds / 3600);
        let minutes = Math.floor((totalSeconds % 3600) / 60);
        let seconds = totalSeconds % 60;

        // 2. إضافة الصفر التجميلي (مثلاً 5 تصبح 05)
        // نستخدم دالة جاهزة في النصوص اسمها padStart
        hours = hours.toString().padStart(2, "0");
        minutes = minutes.toString().padStart(2, "0");
        seconds = seconds.toString().padStart(2, "0");

        // 3. عرض النص في الصفحة

        timerElement.innerHTML = `<span class="col-1 bg-c-color rounded-circle  as1-1 j-color">${hours}</span>:<span class="col-1 bg-c-color rounded-circle  as1-1 j-color">${minutes}</span>:<span class="col-1 bg-c-color rounded-circle  as1-1 j-color">${seconds}</span>`;

        // 4. التحقق من الانتهاء وإنقاص الوقت
        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            timerElement.innerText = "انتهى العرض!";
            timerElement.style.color = "gray"; // تغيير اللون عند الانتهاء
        } else {
            totalSeconds--; // انقص ثانية واحدة
        }

    }, 1000);
}
// function IntersectionObserver(){

//     // 1. تحديد العناصر التي سيتم مراقبتها
// const boxes = document.querySelectorAll('box');

// // 2. خيارات المراقبة (متى تعتبر العنصر مرئيًا)
// const options = {
//   root: null, // المراقبة بالنسبة لمجال الرؤية (Viewport)
//   rootMargin: '0px',
//   threshold: 0.1, // تشغيل الدالة عندما يصبح 10% من العنصر مرئيًا
// };

// // 3. الدالة التي تعمل عند التقاطع
// const observer = new IntersectionObserver((entries, observer) => {
//   entries.forEach(entry => {
//     if (entry.isIntersecting) {
//       // إذا تقاطع (ظهر) العنصر، أضف كلاس الحركة
//       entry.target.classList.add('animate');
//       // توقف عن مراقبة هذا العنصر لتجنب تكرار الحركة
//       observer.unobserve(entry.target); 
//     }
//   });
// }, options);

// // 4. ابدأ مراقبة جميع العناصر
// boxes.forEach(box => {
//   observer.observe(box);
// });
// }

function scrolltop(targetY = null){
    document.getElementById('scrollToYButton').addEventListener('click', function() {
        // تحديد القيمة التي تريد التمرير إليها عموديًا (Y)
        if(targetY === null){
            const targetY = 1234;
        }
       
    
        // استخدام window.scrollTo لتمرير الصفحة.
        // القيمة الأولى هي الإحداثي الأفقي (X)، والقيمة الثانية هي الإحداثي العمودي (Y).
        // نستخدم window.scrollX للحفاظ على الموضع الأفقي الحالي للصفحة.
        window.scrollTo({
            left: window.scrollX, //  <-- الحفاظ على الموضع الأفقي الحالي
            top: targetY,         //  <-- الانتقال إلى الموضع العمودي المطلوب
            behavior: 'smooth'    // لتمرير سلس (اختياري)
        });
    
        // يمكنك استخدام الطريقة القديمة (بدون تمرير سلس) كالتالي:
        // window.scrollTo(window.scrollX, targetY);
    });
}

function slider(){

    const slider = document.querySelector('.draggable-list');
let isDown = false; // هل زر الماوس مضغوط حالياً؟
let startX;         // موضع الماوس الأفقي عند بداية الضغط
let scrollLeft;     // موضع التمرير الأفقي للعنصر عند بداية الضغط

slider.addEventListener('mousedown', (e) => {
    isDown = true;
    slider.classList.add('active'); // إضافة فئة لتغيير مؤشر الماوس
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
});

slider.addEventListener('mouseleave', () => {
    isDown = false;
    slider.classList.remove('active');
});

slider.addEventListener('mouseup', () => {
    isDown = false;
    slider.classList.remove('active');
});

slider.addEventListener('mousemove', (e) => {
    if(!isDown) return; // توقف إذا لم يكن زر الماوس مضغوطاً
    e.preventDefault();
    
    // حساب المسافة التي تم سحبها
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 2; // ضاعف القيمة لجعل السحب أسرع
    
    // تحديث موضع التمرير
    slider.scrollLeft = scrollLeft - walk; 
});
}
// ----------Mycarosel-------------

function Mycarosel(){
     // JavaScript للتحكم في الكاروسيل
    // 1. تحديد العناصر الأساسية من الـ DOM
const slidesWrapper = document.querySelector('.slides-wrapper');
const slides = document.querySelectorAll('.slide');
const prevBtn = document.querySelector('.prev-btn');
const nextBtn = document.querySelector('.next-btn');

// 2. المتغيرات الرئيسية
let currentSlideIndex = 0; // يبدأ من الشريحة الأولى (الفهرس 0)
const totalSlides = slides.length; // عدد الشرائح هو 3

// 3. الوظيفة الأساسية لتحديث الشريحة
function updateCarousel() {
    // لحساب مقدار الإزاحة: (الفهرس الحالي) * (عرض شريحة واحدة)
    // بما أننا نعمل في وضع RTL (من اليمين لليسار)، نستخدم transform: translateX
    
    // ملاحظة: كل شريحة عرضها 100% من الحاوية الأم
    const offset = currentSlideIndex * 100; 

    // تطبيق الإزاحة (النقل الأفقي) على حاوية الشرائح
    // إذا كانت الشريحة 0: تتحرك 0% (تبقى مكانها)
    // إذا كانت الشريحة 1: تتحرك 100% (تظهر الشريحة الثانية)
    // إذا كانت الشريحة 2: تتحرك 200% (تظهر الشريحة الثالثة)
    slidesWrapper.style.transform = `translateX(-${offset}%)`;
}

// 4. وظائف أزرار التنقل
function showNextSlide() {
    // زيادة الفهرس، وإذا وصل للنهاية، يعود إلى 0 (التفاف)
    currentSlideIndex = (currentSlideIndex + 1) % totalSlides;
    updateCarousel();
}

function showPrevSlide() {
    // إنقاص الفهرس، وإذا وصل لأقل من 0، يعود إلى الشريحة الأخيرة (التفاف)
    currentSlideIndex = (currentSlideIndex - 1 + totalSlides) % totalSlides;
    updateCarousel();
}

// 5. ربط الوظائف بأحداث النقر
nextBtn.addEventListener('click', showNextSlide);
prevBtn.addEventListener('click', showPrevSlide);

// 6. تشغيل التنقل التلقائي (اختياري)
// const intervalTime = 3000; // 3 ثوانٍ
// let carouselInterval = setInterval(showNextSlide, intervalTime);

// يمكنك إيقاف التنقل التلقائي عند تمرير الماوس
// slidesWrapper.addEventListener('mouseenter', () => clearInterval(carouselInterval));
// slidesWrapper.addEventListener('mouseleave', () => {
//     carouselInterval = setInterval(showNextSlide, intervalTime);
// });

// التأكد من أن الكاروسيل يبدأ من الشريحة الأولى
updateCarousel();
}
Mycarosel();
// مثال: تشغيل العداد لمدة ساعتين (2 * 60 * 60 = 7200 ثانية)
// يمكنك تغيير الرقم 7200 لأي عدد ثواني تريده
startOfferCountdown(7200);
// IntersectionObserver();
scrolltop(1);
slider();
