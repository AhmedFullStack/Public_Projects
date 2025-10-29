
    // نحصل على الزر
const themeButton1 = document.getElementById("theme1-toggle");
const themeButton2 = document.getElementById("theme2-toggle");
const themeButton3 = document.getElementById("theme3-toggle");

// "نستمع" لأي ضغطة على الزر
themeButton1.addEventListener("click", function () {

  // "toggle" تعني:
  // إذا كان الكلاس موجوداً -> احذفه
  // إذا لم يكن موجوداً -> أضفه
  document.body.classList.toggle("dark-mode");
});
themeButton2.addEventListener("click", function () {

// "toggle" تعني:
// إذا كان الكلاس موجوداً -> احذفه
// إذا لم يكن موجوداً -> أضفه
document.body.classList.toggle("mode-2");
});
themeButton3.addEventListener("click", function () {

// "toggle" تعني:
// إذا كان الكلاس موجوداً -> احذفه
// إذا لم يكن موجوداً -> أضفه
document.body.classList.toggle("mode-3");
});
