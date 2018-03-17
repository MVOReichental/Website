$(function () {
    $("#dates-groups-form").on("submit", function () {
        $("#dates-groups-field").val($("#dates-groups-select").selectpicker("val").join(" "));
    });
});