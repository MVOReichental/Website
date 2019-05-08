$(function () {
    $(".home-next-date").each(function () {
        var date = moment($(this).data("date"));

        var hasTime = date.hours() || date.minutes();

        $(this).text(date.calendar(null, {
            sameDay: hasTime ? "[heute] LT" : "[heute]",
            nextDay: hasTime ? "[morgen] LT" : "[morgen]",
            nextWeek: hasTime ? "dd, LLL" : "dd, LL",
            sameElse: hasTime ? "dd, LLL" : "dd, LL"
        }));

        $(this).attr("title", date.format(hasTime ? "dd, LLL" : "dd, LL"));
    });
});