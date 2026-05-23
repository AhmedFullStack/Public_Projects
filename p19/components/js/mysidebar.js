// JavaScript Document
// Script to open and close sidebar
function w3_open() {
  document.getElementById("mySidebar").style.display = "block";
}
 
function w3_close() {
  document.getElementById("mySidebar").style.display = "none";
}

// أضفEventListener event عند تحميل الصفحة
document.addEventListener("DOMContentLoaded", function() {
	
  var buttons = document.querySelectorAll("button");
  
  buttons.forEach(function(button) {
	  
    if (button.textContent.includes("اغلاق النافذه")) {
      button.addEventListener("click", w3_close);
	  
    }
	
	    if (button.textContent.includes("فتح النافذه")) {
      button.addEventListener("click", w3_open);
	  
    }
	
  });
  
});

