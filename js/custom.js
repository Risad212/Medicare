// No JavaScript needed for this example anymore!
document.addEventListener('click', function (e) {
    // Hamburger menu
    if (e.target.classList.contains('hamburger-toggle')) {
        e.target.children[0].classList.toggle('active');
    }
})


// For Nav sticky
const nav = document.querySelector('.header');
let navTop = nav.offsetTop;
function fixedNav() {
    if (window.scrollY >= navTop) {
        nav.classList.add('sticky-nav');
    } else {
        nav.classList.remove('sticky-nav');
    }
}
window.addEventListener('scroll', fixedNav);





