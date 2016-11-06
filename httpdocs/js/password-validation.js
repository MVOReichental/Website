$(function () {
    $(".password-meter").pwstrength({
        common: {
            zxcvbn: true
        },
        ui: {
            showStatus: true,
            showVerdictsInsideProgressBar: true
        },
        i18n: {
            t: function (key) {
                var strings = {
                    "wordLength": "Das Passwort ist zu kurz",
                    "wordNotEmail": "Das Passwort darf die E-Mail Adresse nicht enthalten",
                    "wordSimilarToUsername": "Das Passwort darf den Benutzernamen nicht enthalten",
                    "wordTwoCharacterClasses": "Bitte Buchstaben und Ziffern verwenden",
                    "wordRepetitions": "Zu viele Wiederholungen",
                    "wordSequences": "Das Passwort enth\u00e4lt Buchstabensequenzen",
                    "errorList": "Fehler:",
                    "veryWeak": "Sehr schwach",
                    "weak": "Schwach",
                    "normal": "Normal",
                    "medium": "Mittel",
                    "strong": "Stark",
                    "veryStrong": "Sehr stark"
                };

                var result = strings[key];

                return result === key ? '' : result;
            }
        }
    });
});