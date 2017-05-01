$(function () {
    setInterval(function () {
        $.get("nop");
    }, 180000);
});

this.jQuery.fn.selectpicker.defaults.iconBase = "fa";
this.jQuery.fn.selectpicker.defaults.tickIcon = "fa-check";