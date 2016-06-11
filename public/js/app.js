function Taskboard($) {
    "use strict";
    var successPopupKey = 'success-popup';
    var errorPopupKey = 'error-popup';
    var taskboardApplication = this;
    var feed = null;
    var role = null;
    var performerRole = 4;
    var customerRole = 2;
    var unauthorizedRole = 0;
    var timestampRefreshPeriod = 60000;
    this.locale = 'ru';
    this.localization = new Localization();
    this.initialized = false;
    this.disableModals = false;
    this.currentForm = null;
    this.supportEvents = false;
    this.logger = function () {
        var oldConsoleLog = null;
        var pub = {};

        pub.enableLogger = function () {
            if (oldConsoleLog == null)
                return;
            window['console']['log'] = oldConsoleLog;
        };

        pub.disableLogger = function () {
            oldConsoleLog = window['console']['log'];
            window['console']['log'] = function () {
            };
        };
        return pub;
    }();

    this.Feed = function (limit) {
        this.lastTaskId = null;
        this.loading = false;
        var feed = this;
        this.buildQuery = function (limit) {
            if (feed.lastTaskId == null)
                return "limit=".concat(limit.toString());
            else {
                return "limit=".concat(limit.toString(), "&", "last_id=", feed.lastTaskId);
            }
        };
        this.initialize = function () {
            feed.load(limit);
            var win = $(window);
            win.scroll(function () {
                if (!feed.loading && !(feed.lastTaskId == -1)) {
                    if ($(document).height() - win.height() == win.scrollTop()) {
                        feed.loading = true;
                        feed.showLoading();
                        feed.load();
                    }
                }
            });
        };
        this.hideLoading = function () {
            $('#loading').hide();
        };
        this.showLoading = function () {
            $('#loading').show();
        };
        this.noMoreContent = function () {
            $('#no-more-content').show();
        };
        this.load = function () {
            if (feed.lastTaskId == -1) {
                feed.loading = false;
                return;
            }
            $.ajax({
                url: '/api/v1/task',
                data: feed.buildQuery(limit),
                contentType: 'text/html; charset=UTF-8',
                type: "GET",
                success: function (response) {
                    feed.hideLoading();
                    feed.loading = false;
                    if (role == customerRole && (feed.lastTaskId == null || feed.lastTaskId == -1)) {
                        response = taskboardApplication.replaceToken(response);
                    }
                    if (response == "") {
                        feed.noMoreContent();
                        var localLastTaskId = feed.lastTaskId;
                        feed.lastTaskId = -1;
                        if (role == customerRole && localLastTaskId == null) {
                            $('#create-task-button').click();
                        }
                    } else {
                        taskboardApplication.renderHtmlTask(response, false);
                    }
                },
                error: function (error, status) {
                    taskboardApplication.closeFormOnUnknownError(status);
                    feed.loading = false;
                    feed.hideLoading();
                    console.log(error);
                }
            });
        }
    };

    this.parseJsonResponse = function (response) {
        var error = {};
        if (response.hasOwnProperty('status') && (response.status == 404 || response.status == 502)) {
            error.unspecified = "Max attempts number exceeded.";
            return {error: error};
        }
        if (response['responseJSON']) {
            if (response['responseJSON'].hasOwnProperty('error')) {
                return response['responseJSON'];
            } else {
                error.unspecified = "Unknown error occurred";
                return {error: error};
            }
        }
        if (response.hasOwnProperty('responseText')) {
            return $.parseJSON(response['responseText']);
        }
        return false;
    };

    this.getLocale = function () {
        var item = localStorage.getItem('locale');
        if (item && (item == 'en' || item == 'ru')) {
            return item;
        } else {
            return 'en';
        }
    };

    this.localizedMessage = function (message) {
        return taskboardApplication.localization[message.concat('_', taskboardApplication.getLocale())];
    };

    this.updateLocale = function (el) {
        var msg = taskboardApplication.localizedMessage(el.data('l10n'));
        if (el.hasClass('l10n-tooltip')) {
            el.attr('title', msg);
            el.data('original-title', msg);
        }
        if (el.hasClass('l10n-placeholder')) {
            el.attr('placeholder', msg)
        }
        if (el.hasClass('l10n-text')) {
            el.text(msg);
        }
        if (el.hasClass('l10n-toggler')) {
            var ind = msg.indexOf('|');
            var on = msg.substring(0, ind);
            var off = msg.substring(ind + 1);
            el.attr('data-on', on);
            el.attr('data-off', off);
        }
    };

    this.initializeExtensions = function () {
        $.fn.setTimestamp = function () {
            if ($(this).data('timestamp') == null) {
                var timestamp = new Date().getTime() + $(this).data('timestamp-offset') * 1000;
                $(this).data('timestamp', timestamp);
            }
        };
        $.fn.substituteTime = function () {
            var timestamp = $(this).data('timestamp') || 0;
            var milliseconds = new Date().getTime() - timestamp;
            var prefix = taskboardApplication.localizedMessage('prefixAgo');
            var suffix = taskboardApplication.localizedMessage('suffixAgo');
            var seconds = Math.abs(milliseconds) / 1000;
            var minutes = seconds / 60;
            var hours = minutes / 60;
            var days = hours / 24;
            var years = days / 365;

            function substituteNumber(number) {
                return number;
            }

            var words = seconds < 45 && taskboardApplication.localizedMessage('seconds').format(substituteNumber(Math.round(seconds))) ||
                seconds < 90 && taskboardApplication.localizedMessage('minute').format(substituteNumber(1)) ||
                minutes < 45 && taskboardApplication.localizedMessage('minutes').format(substituteNumber(Math.round(minutes))) ||
                minutes < 90 && taskboardApplication.localizedMessage('hour').format(substituteNumber(1)) ||
                hours < 24 && taskboardApplication.localizedMessage('hours').format(substituteNumber(Math.round(hours))) ||
                hours < 42 && taskboardApplication.localizedMessage('day').format(substituteNumber(1)) ||
                days < 30 && taskboardApplication.localizedMessage('days').format(substituteNumber(Math.round(days))) ||
                days < 45 && taskboardApplication.localizedMessage('month').format(substituteNumber(1)) ||
                days < 365 && taskboardApplication.localizedMessage('months').format(substituteNumber(Math.round(days / 30))) ||
                years < 1.5 && taskboardApplication.localizedMessage('year').format(substituteNumber(1)) ||
                taskboardApplication.localizedMessage('years').format(substituteNumber(Math.round(years)));
            var value = $.trim([prefix, words, suffix].join(taskboardApplication.localizedMessage('wordSeparator')));
            $(this).text(value);
        };

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
    };

    this.initializePopup = function (storageItemName) {
        var popupItem = taskboardApplication.localStorageGetItem(storageItemName);
        if (popupItem === null)
            return;
        $('#'.concat(storageItemName, '-text')).text(popupItem);
        var popup = $('#'.concat(storageItemName));
        popup.removeClass('hidden');
        popup.fadeTo(2000, 500).fadeOut(5000, function () {
            popup.hide();
            $(popup.find('span')).text('');
        });
        taskboardApplication.localStorageRemoveItem(storageItemName);
    };

    this.initializeEventStream = function () {
        if (!taskboardApplication.supportEvents)
            return;
        if (window.es === undefined) {
            window.es = new EventSource("/api/v1/sse");
            window.es.onmessage = function (e) {
                window.msg = e.data;
                console.log("EventStream: ".concat(window.msg));
            };
            window.es.onerror = function (e) {
                e = e || event;
                window.msg = '';

                switch (e.target.readyState) {
                    case EventSource.CONNECTING:
                        window.msg = 'Reconnecting…';
                        break;
                    case EventSource.CLOSED:
                        window.msg = 'Connection failed. Will create new one.';
                        break;
                }
                console.log("EventStream: ".concat(window.msg));
            };
        }
    };

    this.initializeListeners = function () {
        $('#btn-logout').click(function () {
            window.location = "/api/v1/auth/logout";
        });
        $('.modal').on('hidden.bs.modal', function () {
            var form = $(this).find('form');
            if (form.length) {
                form[0].reset();
                $(this).find('.error-description').each(function () {
                    $(this).empty();
                });
                $(this).find('.has-error').each(function () {
                    $(this).removeClass('has-error');
                });
            }
        });
    };

    this.replaceToken = function (html) {
        var jsonStart = '<!--json-';
        var jsonEnd = '-json-->';
        if (html.indexOf(jsonStart) > -1) {
            var jsonEndIndex = html.indexOf(jsonEnd);
            var token = html.substr(jsonStart.length, jsonEndIndex - jsonEnd.length - 1);
            $('#task-form').find('input[name=csrf_token]').val(token);
            return html.substr(jsonEndIndex + jsonEnd.length);
        }
    };

    this.currentFormId = function () {
        return taskboardApplication.currentForm == null ? null : $(taskboardApplication.currentForm).attr('id');
    };

    this.cleanupModal = function (modal) {
        $(modal).find('.has-error').each(function () {
            $(this).removeClass('has-error');
        });
        $(modal).find('.error-description').each(function () {
            $(this).empty();
        });
        $(modal).find('button').each(function () {
            $(this).prop('disabled', false);
        });
    };

    this.addFormSpinner = function () {
        $('#'.concat(taskboardApplication.currentFormId(), '-spinner')).each(function () {
            taskboardApplication.replaceIconWithSpinner($(this));
        });
    };

    this.replaceIconWithSpinner = function (icon) {
        icon.removeClass(icon.data('icon')).addClass('glyphicon glyphicon-refresh spinning');
    };

    this.replaceSpinnerWithIcon = function (icon) {
        icon.removeClass('glyphicon glyphicon-refresh spinning').addClass(icon.data('icon'));
    };

    this.removeFormSpinner = function () {
        $('#'.concat(taskboardApplication.currentFormId(), '-spinner')).each(function () {
            taskboardApplication.replaceSpinnerWithIcon($(this));
        });
    };

    this.enableModals = function () {
        taskboardApplication.disableModals = false;
    };

    this.localStorageAddItem = function (key, value) {
        localStorage.removeItem(key);
        localStorage.setItem(key, value);
    };

    this.localStorageRemoveItem = function (key) {
        localStorage.removeItem(key);
    };

    this.localStorageGetItem = function (storageItemName) {
        return localStorage.getItem(storageItemName);
    };

    this.renderHtmlTask = function (html, prepend) {
        var feedHtml = $('#task-feed');
        var lastElementIndex = feedHtml.children().length - 1;
        if (lastElementIndex < 0)
            lastElementIndex = 0;
        if (prepend) {
            feedHtml.prepend(html);
            var taskDiv = feedHtml.find('li:first');
            taskDiv.find('.l10n').each(function () {
                taskboardApplication.updateLocale($(this));
            });
            var timestamp = taskDiv.find('.timestamp');
            timestamp.setTimestamp();
            timestamp.substituteTime();
        } else {
            feedHtml.append(html);
            var gtSelector = lastElementIndex == 0 ? '' : ':gt('.concat(lastElementIndex.toString(), ')');
            feedHtml.find('li'.concat(gtSelector)).each(function () {
                var currentElement = $(this);
                currentElement.find('.l10n').each(function () {
                    taskboardApplication.updateLocale($(this));
                });
                var id = parseInt(currentElement.data('id'));
                if (feed.lastTaskId == null || feed.lastTaskId > id) {
                    feed.lastTaskId = id;
                }
                var timestamp = currentElement.find('.timestamp');
                timestamp.setTimestamp();
                timestamp.substituteTime();
            });
        }
    };

    this.finalizeForm = function () {
        $(taskboardApplication.currentForm).find('button').each(function () {
            $(this).prop('disabled', false);
        });
        taskboardApplication.currentForm = null;
    };

    this.closeFormModal = function () {
        $(taskboardApplication.currentForm).closest('.modal').modal('toggle');
    };

    this.isTaskForm = function (form) {
        return $(form).attr('id') == 'task-form';
    };

    this.isBalanceForm = function (form) {
        return $(form).attr('id') == 'account-form';
    };

    this.onFormSuccess = function (response) {
        var taskForm = taskboardApplication.isTaskForm(taskboardApplication.currentForm);
        var balanceForm = taskboardApplication.isBalanceForm(taskboardApplication.currentForm);
        taskboardApplication.removeFormSpinner();
        taskboardApplication.enableModals();
        taskboardApplication.closeFormModal();
        taskboardApplication.finalizeForm();
        taskboardApplication.initializePopup(successPopupKey);
        if (taskForm) {
            taskboardApplication.updateBalance();
            taskboardApplication.replaceToken(response);
            taskboardApplication.renderHtmlTask(response, true);
            return;
        }
        if (response == null) {
            console.error("Something went extremely wrong here, response is not JSON");
            console.error(response);
            return;
        }
        if (balanceForm) {
            $('#user-balance').text(response['balance']);
        }
        if (response['redirect'] != null) {
            location.href = response['redirect'];
        }
    };

    this.closeFormOnUnknownError = function (message) {
        taskboardApplication.localStorageAddItem(errorPopupKey, message);
        taskboardApplication.closeFormModal();
        taskboardApplication.initializePopup(errorPopupKey);
        taskboardApplication.finalizeForm();
    };

    this.closeTaskFormWithAlert = function () {
        taskboardApplication.closeFormModal();
        taskboardApplication.finalizeForm();
        $('#task-unpaid-modal').modal('show');
    };

    this.onFormError = function (response) {
        taskboardApplication.removeFormSpinner();
        taskboardApplication.enableModals();
        var json = taskboardApplication.parseJsonResponse(response);
        if (!json) {
            taskboardApplication.closeFormOnUnknownError("Something went extremely wrong here, response is not a JSON.");
            return;
        }
        if (!json.hasOwnProperty('error') || json.error == null) {
            taskboardApplication.closeFormOnUnknownError("Something went extremely wrong here, response is JSON of error type, but doesn't have any explanatory fields.");
            return;
        }
        $(taskboardApplication.currentForm).find('input').on('change keyup', function () {
            if (!taskboardApplication.disableModals) {
                $(this).off('change keyup');
                $(this).closest('form').find('button').each(function () {
                    $(this).prop('disabled', false);
                });
            }
        });
        if (json.error.hasOwnProperty("task-unpaid")) {
            taskboardApplication.closeTaskFormWithAlert();
            return;
        }
        $.each(json.error, function (error_name, error_description) {
            var $errorSpan = $('#'.concat(taskboardApplication.currentFormId(), '-error-', error_name));
            if ($errorSpan.length == 0) {
                taskboardApplication.closeFormOnUnknownError(json);
            } else {
                $errorSpan.parent('div').addClass('has-error');
                $errorSpan.text(error_description);
            }
        });
        taskboardApplication.finalizeForm();
    };

    this.initializeFormModals = function () {
        var $modal = $('.modal');
        $modal.on('hide.bs.modal', function (e) {
            if (taskboardApplication.disableModals) {
                e.preventDefault();
            } else {
                taskboardApplication.cleanupModal(this);
            }
        });
    };

    this.updateBalance = function () {
        $.ajax({
            type: 'GET',
            url: "api/v1/account",
            error: console.log,
            success: function (response) {
                $('#user-balance').text($.parseJSON(response)['balance']);
            }
        });
    };

    this.initializeFormListeners = function () {
        $('#login-form,#signup-form,#task-form,#account-form').submit(function (e) {
            e.preventDefault();
            if (taskboardApplication.currentForm != null) {
                return;
            }
            taskboardApplication.cleanupModal($(this).closest('.modal'));
            var empty;
            var form = $(this);
            $(this).find('input,textarea').each(function (e, v) {
                if (!v.value.trim().length) {
                    empty = true;
                    var $errorSpan = $('#'.concat(form.attr('id'), '-error-', v.name));
                    $errorSpan.parent('div').addClass('has-error');
                    $errorSpan.text("Not provided");
                }
            });
            if (empty) {
                return;
            }
            var isTaskForm = $(this).attr('id') == 'task-form';
            taskboardApplication.disableModals = true;
            taskboardApplication.currentForm = this;
            $(this).find('button').each(function () {
                $(this).attr('disabled', 'true');
            });
            var data = taskboardApplication.serializeForm();
            var action = $(taskboardApplication.currentForm).attr('action');
            taskboardApplication.addFormSpinner();
            var csrf = $(this).find('input[name=csrf_token]').val();
            $.ajax({
                url: action,
                dataType: isTaskForm ? 'html' : 'json',
                contentType: 'application/json; charset=UTF-8',
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrf
                },
                data: data,
                success: taskboardApplication.onFormSuccess,
                error: taskboardApplication.onFormError
            });
        });
    };

    this.initializeTimestampRefresher = function (interval) {
        setInterval(function () {
            $('.timestamp').substituteTime();
        }, interval);
    };

    this.serializeForm = function () {
        return JSON.stringify($(taskboardApplication.currentForm).serializeObject());
    };

    this.initializeFeed = function () {
        feed = new taskboardApplication.Feed(10);
        feed.initialize();
        $('#hide-completed').change(function () {
            if (this.checked) {
                $('#task-feed').attr('data-hide-completed', true);
            } else {
                $('#task-feed').attr('data-hide-completed', false);
            }
        });
    };

    this.initializeTooltips = function () {
        $("[rel=tooltip]").tooltip({placement: 'bottom'});
    };

    this.parseJsonResponseError = function (response) {
        var json = taskboardApplication.parseJsonResponse(response);
        if (json && json.hasOwnProperty('error') && json['error'] != null) {
            return JSON.stringify($.parseJSON(json.error));
        } else {
            return "Unknown error";
        }
    };

    this.updateLocales = function () {
        $('.l10n').each(function () {
            taskboardApplication.updateLocale($(this));
        });
    };

    this.initialize = function () {
        "use strict";
        taskboardApplication.initializeExtensions();
        taskboardApplication.updateLocales();
        role = $('#user-data').data('role');
        taskboardApplication.logger.enableLogger();
        if (taskboardApplication.initialized)
            return this;
        taskboardApplication.initialized = true;
        [successPopupKey, errorPopupKey].forEach(function (entry) {
            taskboardApplication.initializePopup(entry);
        });
        taskboardApplication.initializeListeners();
        taskboardApplication.initializeFormListeners();
        taskboardApplication.initializeFormModals();
        taskboardApplication.initializeTimestampRefresher(timestampRefreshPeriod);
        taskboardApplication.initializeEventStream();
        taskboardApplication.initializeTooltips();
        if (role != unauthorizedRole) {
            taskboardApplication.initializeFeed();
        }
        if (role == customerRole) {
            $(document).on('click', '.delete-task', function () {
                var task = $(this).closest('.task-feed-item');
                var id = task.data('id');
                var csrf = $(this).data('csrf');
                $.ajax({
                    url: 'api/v1/task/' + id,
                    contentType: 'application/json; charset=UTF-8',
                    type: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": csrf
                    },
                    success: function () {
                        task.remove();
                        taskboardApplication.localStorageAddItem(successPopupKey, "success");
                        taskboardApplication.initializePopup(successPopupKey);
                        taskboardApplication.updateBalance();
                    },
                    error: function (response) {
                        taskboardApplication.localStorageAddItem(errorPopupKey, taskboardApplication.parseJsonResponseError(response));
                        taskboardApplication.initializePopup(errorPopupKey);
                    }
                });
            });
            $(document).on('click', '.fix-task', function () {
                var task = $(this).closest('.task-feed-item');
                var id = task.data('id');
                var csrf = $(this).data('csrf');
                $.ajax({
                    url: 'api/v1/task/' + id,
                    contentType: 'application/json; charset=UTF-8',
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrf
                    },
                    success: function (response) {
                        task.replaceWith(response);
                        taskboardApplication.localStorageAddItem(successPopupKey, "success");
                        taskboardApplication.initializePopup(successPopupKey);
                        taskboardApplication.updateBalance();
                    },
                    error: function (response) {
                        taskboardApplication.localStorageAddItem(errorPopupKey, taskboardApplication.parseJsonResponseError(response));
                        taskboardApplication.initializePopup(errorPopupKey);
                    }
                });
            });
        }
        if (role == performerRole) {
            $(document).on('click', '.perform-task', function () {
                var task = $(this).closest('.task-feed-item');
                var id = task.data('id');
                var csrf = $(this).data('csrf');
                $.ajax({
                    url: 'api/v1/task/' + id,
                    contentType: 'application/json; charset=UTF-8',
                    type: "PUT",
                    headers: {
                        "X-CSRF-TOKEN": csrf
                    },
                    success: function () {
                        task.remove();
                        taskboardApplication.localStorageAddItem(successPopupKey, "success");
                        taskboardApplication.initializePopup(successPopupKey);
                        taskboardApplication.updateBalance();
                    },
                    error: function (response) {
                        taskboardApplication.localStorageAddItem(errorPopupKey, taskboardApplication.parseJsonResponseError(response));
                        taskboardApplication.initializePopup(errorPopupKey);
                    }
                });
            });
        }
        return taskboardApplication;
    };
    return taskboardApplication.initialize();
}

$(document).ready(function () {
    "use strict";
    window.taskboard = new Taskboard($);
});
