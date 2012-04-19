$(document).ready(function () {
    if (typeof(sidebarmenu) !== 'undefined') {
        var showText = sidebarmenu.config.controls.show;
        var hideText = sidebarmenu.config.controls.hide;
        var useAnimations = true;

        function initControls() {
            $('.sidebar-menu-item-collapsed').children('.sidebar-menu-item-text-container').children('.sidebar-menu-item-controls').append(showText);
            $('.sidebar-menu-item-expanded').children('.sidebar-menu-item-text-container').children('.sidebar-menu-item-controls').append(hideText);
        }

        initControls();
        $('.sidebar-menu-item-controls').click(function () {
            var currentText = $(this).text();

            if (currentText == showText) {
                $(this).text(hideText);
            } else if (currentText == hideText) {
                $(this).text(showText);
            }

            if (useAnimations) {
                //A little "ugly" hack to prevent some gui glitches.
                $(this).parents('.sidebar-menu-item:first').toggleClass('sidebar-menu-item-collapsed sidebar-menu-item-expanded', 250).children('.sidebar-menu').show(0, function () {
                    var _this = $(this);
                    setTimeout(function () {
                        _this.css('display', '')
                    }, 250);
                });
            } else {
                $(this).parents('.sidebar-menu-item:first').toggleClass('sidebar-menu-item-collapsed sidebar-menu-item-expanded');
            }
        });

        /*Open submenu of current page if current page is present as a link in sidebarmenu*/
        var selfLink = $('.sidebar-menu-item-collapsed').find('.selflink')[0]
        if(selfLink !== undefined ){
            $(selfLink).parents('.sidebar-menu-item-collapsed').removeClass('sidebar-menu-item-collapsed').addClass('sidebar-menu-item-expanded');
        }

    } else {
        $('.sidebar-menu-container').prepend(mw.msg('sidebar-js-init-error'));
    }
});