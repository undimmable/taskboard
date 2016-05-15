§$(document).ready(function () {
    "use strict";
    $.fn.serializeObject = function () {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
    $('#login_form,#signup_form').on('submit', function (e) {
        e.preventDefault();
        var form = $(this);
        $('.has-error').each(function () {
            $(this).removeClass('has-error');
        });
        $.each($('.error-description'), function () {
            $(this).empty();
        });
        $(".role-btn-group button").click(function () {
            $("#signup_user_role").val($(this).text());
        });
        var formId = form.attr('id');
        var data = form.serializeObject();
        var action = form.attr('action');
        $('#'.concat(formId, '_spinner')).each(function () {
            $(this).addClass('glyphicon glyphicon-refresh spinning');
        });
        $.ajax({
            url: action,
            dataType: "json",
            contentType: "application/json;charset=utf-8",
            type: "POST",
            data: JSON.stringify(data),
            success: function (msg) {
                $('.spinning').each(function () {
                    $(this).removeClass('spinning');
                });
                if (msg != null) {
                    console.log(msg);
                    if (msg == null) {
                        console.log("Something went extremely wrong here, response is not JSON");
                        return;
                    }
                    if (msg.redirect == null) {
                        console.log("Leave as is");
                        return;
                    }
                    location.href = msg.redirect;
                }
            },
            error: function (msg) {
                $('.spinning').each(function () {
                    $(this).removeClass('spinning');
                });
                var response = msg.responseJSON;
                if (response == null) {
                    console.log("Something went extremely wrong here, response is not JSON");
                    return;
                }
                if (response.error == null) {
                    console.log("Something went extremely wrong here, response is error, but JSON doesn't have an error field.");
                    console.log(response);
                    return;
                }
                //noinspection JSUnresolvedVariable
                $.each(response.error, function (error_name, error_description) {
                    var $errorSpan = $('#'.concat(formId, '_error_', error_name));
                    $errorSpan.parent('div').addClass('has-error');
                    $errorSpan.text(error_description);
                });
            }
        });
    });
});