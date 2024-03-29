(function() {
    var didScroll;
    var lastScrollTop = 0;
    var delta = 5;
    var $nav = $('.perception-navbar');
    var navbarHeight = $nav.outerHeight();
    var float = false;

    $(window).scroll(function(event){
        didScroll = true;
    });

    setInterval(function() {
        if (didScroll) {
            hasScrolled();
            didScroll = false;
        }
    }, 250);

    function hasScrolled() {
        var st = $(this).scrollTop();
        
        // floating navbar effect
        if(st == 0) {
            float = false;
            $nav.removeClass('floating');
        } else if(!float) {
            float = true;
            $nav.addClass('floating');
        }

        // Make sure they scroll more than delta
        if(Math.abs(lastScrollTop - st) <= delta)
            return;
        
        // If they scrolled down and are past the navbar, add class .nav-up.
        // This is necessary so you never see what is "behind" the navbar.
        if (st > lastScrollTop && st > navbarHeight){
            // Scroll Down
            $nav.removeClass('nav-down').addClass('nav-up');
        } else {
            // Scroll Up
            if(st + $(window).height() < $(document).height()) {
                $nav.removeClass('nav-up').addClass('nav-down');
            }
        }
        
        lastScrollTop = st;
    }
})();