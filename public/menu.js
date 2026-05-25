const menu_btn = document.getElementById("menu");
const menu_btn2 = document.getElementById("menu2");
const mobnav = document.getElementById("mobnav");

function ad(){ mobnav.classList.add('active'); }
function del(){ mobnav.classList.remove('active'); }

if (menu_btn) menu_btn.addEventListener('click', ad);
if (menu_btn2) menu_btn2.addEventListener('click', del);
