    $(document).ready(function () {

            $('.menu-toggle').on('click', function () {
        
                // Toggle sidebar
                $('.main-menu').toggleClass('menu-open');
        
                // Toggle overlay effect
                $('body').toggleClass('menu-open');
            });
            
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.main-menu, .menu-toggle').length) {
                    $('.main-menu').removeClass('menu-open');
                    $('body').removeClass('menu-open');
                }
            });
        
    });
