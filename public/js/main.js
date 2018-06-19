
function changeAnchor(idMenu) {
    $(".active").removeClass("active");
    $("#menu-" + idMenu).addClass("active")
}

$(function () {
    $(".anchor").on("click", function () {
        $(".active").removeClass("active");
        $(this).addClass("active")
    });
});

$(document).ready(function () {
    // Add smooth scrolling to all links
    $(".js-scrollTo").on('click', function (event) {

        // Make sure this.hash has a value before overriding default behavior
        if (this.hash !== "") {
            // Prevent default anchor click behavior
            event.preventDefault();

            // Store hash
            var hash = this.hash;

            // Using jQuery's animate() method to add smooth page scroll
            // The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
            $('html, body').animate({
                scrollTop: $(hash).offset().top
            }, 800, function () {

                // Add hash (#) to URL when done scrolling (default click behavior)
                window.location.hash = hash;
            });
        } // End if
    });
});

function checkScroll() {
    var startY = window.innerHeight / 2; //The point where the navbar changes in px
    var stopY = document.body.scrollHeight - window.innerHeight; //The point where the navbar changes in px

    if ($(window).scrollTop() > startY && $(window).scrollTop() < stopY) {
        $('.navbar').addClass("scrolled");
    } else {
        $('.navbar').removeClass("scrolled");
    }
}

if ($('.navbar').length > 0) {
    $(window).on("scroll load resize", function () {
        checkScroll();
    });
}

$(window).on('scroll', function () {
    var scrollPosition = $(window).scrollTop();
    var maxHeight = document.body.scrollHeight - window.innerHeight;
    var trancheHeight = (maxHeight / 5);
    if (scrollPosition > 1) {
        $(".active").removeClass("active");
        $("#menu-top").addClass("active");
    }
    if (scrollPosition > (trancheHeight) - 200) {
        $(".active").removeClass("active");
        $("#menu-aLaUne").addClass("active");
    }
    if (scrollPosition > (trancheHeight * 2) + 200) {
        $(".active").removeClass("active");
        $("#menu-album").addClass("active");
    }
    if (scrollPosition > (trancheHeight * 4)) {
        $(".active").removeClass("active");
        $("#menu-newLetter").addClass("active");
    }
    if (scrollPosition > maxHeight - 200) {
        $(".active").removeClass("active");
        $("#menu-footer").addClass("active");
    }

});