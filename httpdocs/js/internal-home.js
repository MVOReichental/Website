$(function () {
    $(".home-next-birthday").each(function () {
        var date = $(this).data("date");
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var todayMoment = moment(today);

        $(this).text(moment(date).calendar(null, {
            sameDay: "[heute]",
            nextDay: "[morgen]",
            nextWeek: "dddd",
            sameElse: "DD.MM.YYYY"
        }));
    });
});