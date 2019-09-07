/**
 * @author              Steven Palmer
 * @author url          http://coalaweb.com
 * @author email        support@coalaweb.com
 * @copyright           Copyright (c) 2015 Steven Palmer All rights reserved.
 */

jQuery.noConflict();
(function($) {
    if ($(window).width() < 1024) {
        $(function() {
            $(".cws-tabs.left > li").hover(
                    function() {
                        $(this).animate({"margin-left": "0px"}, 400);
                    },
                    function() {
                        $(this).stop().animate({"margin-left": "0px"}, 400);
                    }
            );
            $(".cws-tabs.right > li").hover(
                    function() {
                        $(this).animate({"margin-left": "0px"}, 400);
                    },
                    function() {
                        $(this).stop().animate({"margin-left": "0px"}, 400);
                    }
            );
        });

    } else {
        $(function() {
            $(".cws-tabs.left > li").css("left", "0px");
            $(".cws-tabs.left > li").hover(
                    function() {
                        $(this).animate({"left": "170px"}, 400);
                    },
                    function() {
                        $(this).stop().animate({"left": "0px"}, 400);
                    }
            );

            $(".cws-tabs.right > li").css("right", "0px");
            $(".cws-tabs.right > li").hover(
                    function() {
                        $(this).animate({"right": "170px"}, 400);
                    },
                    function() {
                        $(this).stop().animate({"right": "0px"}, 400);
                    }
            );
        });
    }
})(jQuery);


