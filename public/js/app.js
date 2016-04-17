function r(f) {
    /in/.test(document.readyState) ? setTimeout('r(' + f + ')', 9) : f()
}
/**
 * @return {null}
 */
function XHR() {
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

//noinspection JSUnusedGlobalSymbols
var serialize = function (obj) {
    var str = [];
    for (var p in obj)
        if (obj.hasOwnProperty(p)) {
            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
        }
    return str.join("&");
};

var getJSON = function (url, params, successHandler, errorHandler) {
    var xhr = XHR();
    xhr.open('get', url, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onreadystatechange = function () {
        var status;
        var data;
        if (xhr.readyState == 4) {
            status = xhr.status;
            if (status == 200) {
                data = JSON.parse(xhr.responseText);
                successHandler && successHandler(data);
            } else {
                errorHandler && errorHandler(status);
            }
        }
    };
    xhr.send(params);
};

getJSON("/api/v1/login/1", JSON.stringify({XDEBUG_SESSION_START: "PHPStorm_Remote"}), function (success) {
    console.log(success);
}, function (error) {
    console.log(error);
});