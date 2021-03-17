$(function () {
    function getNumber() {
        var date = new Date(), hour = date.getHours(), num = 99, array;
        if (hour > 0 && hour < 6) {
            num = num - hour;
        } else if (hour >= 6 && hour < 12) {
            num = 94 - (hour - 6) * 5;
        } else if (hour >= 12 && hour < 18) {
            num = 64 - (hour - 12) * 4;

        } else if (hour >= 18 && hour <= 23) {
            num = 40 - (hour - 18) * 7;
        } else {
            num = 100;
        }
        num = num.toString();
        array = num.split("");

        if (array.length == 3) {
            $("#bai").html(1);
            $("#shi").html(0);
            $("#ge").html(0);
        } else if (array.length == 2) {
            $("#bai").html(0);
            $("#shi").html(array[0]);
            $("#ge").html(array[1]);
        } else {
            $("#bai").html(0);
            $("#shi").html(0);
            $("#ge").html(array[1]);
        }

        setTimeout(getNumber, 2000);
    }

    getNumber();

});