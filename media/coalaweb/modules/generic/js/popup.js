/**
 * @author              Steven Palmer
 * @author url          http://coalaweb.com
 * @author email        support@coalaweb.com
 * @copyright           Copyright (c) 2015 Steven Palmer All rights reserved.
*/

// create social networking pop-ups
jQuery(document).ready(function () {
    (function () {
        // link selector and pop-up window size
        var Config = {
            Link: "a.cwshare",
            Width: 500,
            Height: 500
        };

        // add handler links
        var slink = document.querySelectorAll(Config.Link);
        for (var a = 0; a < slink.length; a++) {
            slink[a].onclick = PopupHandler;
        }

        // create popup
        function PopupHandler(e) {

            e = (e ? e : window.event);
            var t = (e.target ? e.target : e.srcElement);

            // popup position
            var
                    px = Math.floor(((screen.availWidth || 1024) - Config.Width) / 2),
                    py = Math.floor(((screen.availHeight || 700) - Config.Height) / 2);

            // open popup
            var popup = window.open(t.href, "social",
                    "width=" + Config.Width + ",height=" + Config.Height +
                    ",left=" + px + ",top=" + py +
                    ",location=0,menubar=0,toolbar=0,status=0,scrollbars=1,resizable=1");
            if (popup) {
                popup.focus();
                if (e.preventDefault)
                    e.preventDefault();
                e.returnValue = false;
            }

            return !!popup;
        }

    }());
});