$(function () {
    Highcharts.setOptions({
        global: {
            useUTC: false
        },
        lang: {
            decimalPoint: ",",
            thousandsSep: ".",
            loading: "Daten werden geladen...",
            months: ["Januar", "Februar", "M\u00e4rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
            weekdays: ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"],
            shortMonths: ["Jan", "Feb", "M\u00e4r", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
            exportButtonTitle: "Exportieren",
            printButtonTitle: "Drucken",
            rangeSelectorFrom: "Von",
            rangeSelectorTo: "Bis",
            rangeSelectorZoom: "Zeitraum",
            downloadPNG: "Download als PNG-Bild",
            downloadJPEG: "Download als JPEG-Bild",
            downloadPDF: "Download als PDF-Dokument",
            downloadSVG: "Download als SVG-Bild",
            resetZoom: "Zoom zur\u00fccksetzen",
            resetZoomTitle: "Zoom zur\u00fccksetzen"
        }
    });

    $.get("/internal/admin/visits/chart.json", function (data) {
        Highcharts.chart("visits-chart", {
            chart: {
                type: "area",
                zoomType: "x"
            },
            title: {
                text: null
            },
            xAxis: {
                type: "datetime"
            },
            yAxis: {
                title: {
                    text: null
                }
            },
            tooltip: {
                xDateFormat: "%A, %d. %B %Y",
                shared: true
            },
            plotOptions: {
                area: {
                    stacking: "normal"
                }
            },
            series: [
                {
                    name: "G\u00e4ste",
                    data: data.guests
                }, {
                    name: "Benutzer",
                    data: data.users
                }
            ]
        });
    });
});