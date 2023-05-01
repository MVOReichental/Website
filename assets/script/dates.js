import $ from "jquery";
import "bootstrap-select";
import "../images/logo.svg";// Required for print view which includes the logo

$(function () {
    $("#dates-groups-form").on("submit", function () {
        $("#dates-groups-field").val($("#dates-groups-select").selectpicker("val").join(" "));
    });
});