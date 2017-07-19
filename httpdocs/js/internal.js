$(function () {
    setInterval(function () {
        $.get("nop");
    }, 60000);
});

this.jQuery.fn.selectpicker.defaults.iconBase = "fa";
this.jQuery.fn.selectpicker.defaults.tickIcon = "fa-check";