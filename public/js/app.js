var app = {
    magicScreenWidth: 768,
    statusOk: 200,
    statusNotFound: 404,
    toggle: {},
    collapse: {},
    dropdowns: [],
    DOMReady: function (a, b, c) {
        b = document;
        c = 'addEventListener';
        b[c] ? b[c]('DOMContentLoaded', a) : window.attachEvent('onload', a)
    },
    init: function () {
        var initializer = this.initDropdowns;
        this.DOMReady(function () {
            "use strict";
            initializer();
        });
    },
    initDropdowns: function () {
        this.dropdowns = document.getElementsByClassName('dropdown');
        this.collapse = document.getElementsByClassName('navbar-collapse')[0];
        this.toggle = document.getElementsByClassName('navbar-toggle')[0];
        for (var i = 0; i < this.dropdowns.length; i++) {
            this.dropdowns[i].addEventListener('click', function () {
                if (document.body.clientWidth < this.magicScreenWidth) {
                    var open = this.classList.contains('open');
                    this.closeMenus();
                    if (!open) {
                        this.getElementsByClassName('dropdown-toggle')[0].classList.toggle('dropdown-open');
                        this.classList.toggle('open');
                    }
                }
            });
        }
        window.addEventListener('resize', this.closeMenusOnResize, false);
        if (this.toggle !== undefined && this.toggle != null) {
            this.toggle.addEventListener('click', this.toggleMenu, false);
        }
    },
    toggleMenu: function () {
        this.collapse.classList.toggle('collapse');
        this.collapse.classList.toggle('in');
    },
    closeMenusOnResize: function () {
        if (document.body.clientWidth >= this.magicScreenWidth) {
            this.closeMenus();
            collapse.classList.add('collapse');
            collapse.classList.remove('in');
        }
    },
    closeMenus: function () {
        for (var j = 0; j < this.dropdowns.length; j++) {
            this.dropdowns[j].getElementsByClassName('dropdown-toggle')[0].classList.remove('dropdown-open');
            this.dropdowns[j].classList.remove('open');
        }
    },
    serialize: function (obj) {
        var str = [];
        for (var p in obj)
            if (obj.hasOwnProperty(p)) {
                str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
            }
        return str.join("&");
    },
    getJSON: function (url, params, successHandler, errorHandler) {
        "use strict";
        var xhr = this.XHR();
        xhr.open('get', url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onreadystatechange = function () {
            var status;
            var data;
            if (xhr.readyState == 4) {
                status = xhr.status;
                if (status == this.statusOk) {
                    data = JSON.parse(xhr.responseText);
                    successHandler && successHandler(data);
                } else {
                    errorHandler && errorHandler(status);
                }
            }
        };
        xhr.send(params);
    },
    XHR: function XHR() {
        "use strict";
        try {
            return new XMLHttpRequest();
        } catch (e) {
        }
        try {
            return new ActiveXObject("Msxml3.XMLHTTP");
        } catch (e) {
        }
        try {
            return new ActiveXObject("Msxml2.XMLHTTP.6.0");
        } catch (e) {
        }
        try {
            return new ActiveXObject("Msxml2.XMLHTTP.3.0");
        } catch (e) {
        }
        try {
            return new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
        }
        try {
            return new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
        }
        return null;
    }
};
app.init();