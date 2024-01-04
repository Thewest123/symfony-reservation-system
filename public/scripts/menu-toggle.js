// Toggle menu on click
function menuToggle() {
    let menu = document.querySelector('#menu');
    let toggleIcon = document.querySelector('#toggle-icon');

    // Toggle class "menu-hidden"
    menu.classList.toggle('menu-hidden');

    // Toggle icon
    toggleIcon.classList.toggle('fa-bars');
    toggleIcon.classList.toggle('fa-times');
}