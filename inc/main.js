const wp_bottom_menu_class = document.querySelector('.wp-bottom-menu');
const wp_bottom_menu_sfw =  document.querySelector('.wp-bottom-menu-search-form-wrapper');
var wpbmsf = false;
document.querySelector(".wp-bottom-menu-search-form-trigger").addEventListener("click" ,function(){
    wp_bottom_menu_sfw.classList.toggle("sf-active");
    wp_bottom_menu_class.classList.toggle("sf-active");
    if (!wpbmsf){
        wp_bottom_menu_sfw.style.bottom = wp_bottom_menu_class.clientHeight + "px";
        wpbmsf = true;
    } else {
        wp_bottom_menu_sfw.style.bottom = "0px";
        wpbmsf = false;
    }
});

window.addEventListener('DOMContentLoaded', (event) => {
    document.body.style.paddingBottom = wp_bottom_menu_class.clientHeight + "px";
});