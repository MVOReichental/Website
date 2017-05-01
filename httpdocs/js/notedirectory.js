$(function () {
    var columnSelection = $("#notedirectory-columns");

    $("#notedirectory-list").find("th").each(function () {
        var name = $(this).data("name");

        if (!name) {
            return;
        }

        var optionElement = $("<option>");

        optionElement.attr("value", name);
        optionElement.text($(this).text());
        optionElement.prop("selected", true);

        columnSelection.append(optionElement);
    });

    columnSelection.selectpicker();

    columnSelection.on("changed.bs.select", function () {
        var columns = $(this).val();

        $("#notedirectory-list").find("th, td").each(function () {
            var name = $(this).data("name");

            if (!name) {
                return;
            }

            $(this).toggle(columns.indexOf(name) !== -1);
        });
    });
});