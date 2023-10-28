(function ($) {
    "use strict";

    let $window = $(window),
        $body = $('body');

    // No JavaScript needed for this example anymore!
    document.addEventListener('click', function (e) {
        // Hamburger menu
        if (e.target.classList.contains('hamburger-toggle')) {
            e.target.children[0].classList.toggle('active');
        }
    })

    /*[ Back to top ]
    ===========================================================*/
    $(".scrollup").hide();
    $(window).scroll(function () {
        if ($(this).scrollTop() > 400) {
            $('.scrollup').fadeIn();
        } else {
            $('.scrollup').fadeOut();
        }
    });
    $('.scrollup').on('click', function () {
        $('body,html').animate({
            scrollTop: 0
        }, 800);
        return false;
    });


})(jQuery);


