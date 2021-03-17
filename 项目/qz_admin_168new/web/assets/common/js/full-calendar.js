/*
* @Author: qz_chk
* @Date:   2017-12-04 16:34:21
* @Last Modified by:   qz_chk
* @Last Modified time: 2017-12-04 17:08:36
*/



!function($){
    // var FullCalendar = function(element, options) {
    //     // var that = this;

    //     // this.element = $(element);


    // }

    // FullCalendar.prototype = {
    //     constructor: FullCalendar

    // }

    $.fn.fullCalendar = function ( option ) {
        var args = Array.apply(null, arguments);
        args.shift();
        this.each(function () {
            var $this = $(this),
                data = $this.data('datepicker'),
                options = typeof option == 'object' && option;
            if (!data) {
                console.log(data);
            }

        });
    }
}(window.jQuery);


