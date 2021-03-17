;+function ($) {
    "use strict";
    var defaults;

    function isHtmlControl(obj) {
        var d = document.createElement("div");
        try {
            d.appendChild(obj.cloneNode(true));
            return obj.nodeType == 1 ? true : false;
        } catch (e) {
            return obj == window || obj == document;
        }
    }

    var events = {
        init: function (menuItems) {
            var children = [];
            var styleNumber = 0;
            $.each(menuItems, function (key, item) {
                if (typeof item === 'object') {
                    var icon, styles = '';
                    if (typeof item.icon === 'string' && isHtmlControl(item.icon)) {
                        icon = item.icon;
                    } else if (typeof item.icon === 'string' && !isHtmlControl(item.icon)) {
                        icon = '<i class="menu-icon ' + item.icon + '"></i>';
                    } else {
                        icon = '<i class="menu-icon menu-center-icon"></i>';
                    }
                    if (typeof item.title !== 'undefined') {
                        icon += '<p>' + item.title + '</p>';
                    }
                    if (typeof item.style === 'string') {
                        styles = item.style;
                    }
                    styleNumber += 1;
                    children.push($('<div class="sub-menu" data-id="' + key + '" style="' + styles + '">' + icon + '</div>'));
                }
            });
            return children;
        },
        close: function () {
            this.removeClass('menu-open').addClass('menu-close');
            $('html').removeClass('alpha');
        },
        show: function () {
            if (this.hasClass('menu-open')) {
                this.removeClass('menu-open').addClass('menu-close');
                $('html').removeClass('alpha');
            } else if (this.hasClass('menu-close')) {
                this.addClass('menu-open').removeClass('menu-close');
            } else {
                this.addClass('menu-open');
            }
        },
    };

    $.fn.GooeyMenu = function (opt) {
        var params = $.extend({}, defaults, opt), _this = $(this).find('.menu-center'), menuItem;
        $(this).prepend('<div class="menu-center-shade"></div>');
        _this.append('<div class="menu-item-row"></div><div class="menu-item"></div>');
        menuItem = _this.find('.menu-item');
        var children = events.init(params.menuItems);
        _this.events = events;
        menuItem.append(children);
        _this.find('.sub-menu').on('click', function () {
            var key = $(this).data('id');
            if (typeof params.menuItems[key].closed == 'undefined' || params.menuItems[key].closed) {
                _this.events.close.call(_this.parent());
            }
            $('html').addClass('alpha');
            params.menuItems[key].click.call(_this);
            return false;
        });

        _this.find('.center-icon-view').on('click', function () {
            _this.events.show.call(_this.parent(), params);
            typeof params.success === 'function' && params.success.call(this);
            return false;
        });
        _this.find('.menu-item').on('click', function () {
            return false;
        });
        $('.menu-center-shade').on('click', function () {
            if (_this.parent().hasClass('menu-open')) {
                _this.events.show.call(_this.parent());
            }
            return false;
        });
        $(this).on('touchmove', function () {
            e.stopPropagation();
            return false;
        });
        $('.menu-center-shade,.layui-layer-shade').on('touchmove', function (e) {
            e.stopPropagation();
            return false;
        });
        return _this;
    };

    defaults = $.fn.GooeyMenu.prototype.defaults = {
        centerIcon: 'menu-center-icon',
        menuItems: {},
    };
}($);