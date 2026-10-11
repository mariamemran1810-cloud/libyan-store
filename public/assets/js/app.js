/**
 * ملف الجافاسكربت العام.
 * المرحلة الأولى: وظائف بسيطة فقط، ويُوسَّع في المراحل التالية.
 */
document.addEventListener('DOMContentLoaded', function () {
  // إخفاء تنبيهات النجاح تلقائيًا بعد 5 ثوانٍ
  document.querySelectorAll('.alert-success').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .4s';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 400);
    }, 5000);
  });
});