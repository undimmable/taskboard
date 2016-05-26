if (!String.prototype.format) {
    String.prototype.format = function () {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function (match, number) {
            return typeof args[number] != 'undefined'
                ? args[number]
                : match
                ;
        });
    };
}

function Localization() {
    "use strict";
    this.minute = "minute";
    this.minutes = "minutes";
    this.prefixAgo = null;
    this.suffixAgo = "ago";
    this.inPast = 'any moment now';
    this.seconds = "less than a minute";
    this.minute = "about a minute";
    this.minutes = "{0} minutes";
    this.hour = "about an hour";
    this.hours = "about {0} hours";
    this.day = "a day";
    this.days = "{0} days";
    this.month = "about a month";
    this.months = "{0} months";
    this.year = "about a year";
    this.years = "{0} years";
    this.wordSeparator = " ";
    this.numbers = [];
}