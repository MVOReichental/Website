$(function () {
    $("#news-print").on("click", function () {
        var link = document.createElement("a");
        link.href = "internal/news";

        var printWindow = window.open(link.toString());
        printWindow.print();
    });
});