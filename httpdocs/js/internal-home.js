$(function () {
    $(".home-next-date").each(function () {
        var date = moment($(this).data("date"));

        var hasTime = date.hours() || date.minutes();

        $(this).text(date.calendar(null, {
            sameDay: hasTime ? "[heute] LT" : "[heute]",
            nextDay: hasTime ? "[morgen] LT" : "[morgen]",
            nextWeek: hasTime ? "LLL" : "LL",
            sameElse: hasTime ? "LLL" : "LL"
        }));

        $(this).attr("title", date.format(hasTime ? "LLL" : "LL"));
    });
});