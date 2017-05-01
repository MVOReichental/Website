$(function () {
    setInterval(function () {
        $.get("nop").fail(function (data, textStatus, jqXHR) {
            console.log(data, textStatus, jqXHR);
        });
    }, 60000);
});

this.jQuery.fn.selectpicker.defaults.iconBase = "fa";
this.jQuery.fn.selectpicker.defaults.tickIcon = "fa-check";