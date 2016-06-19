String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.split(search).join(replacement);
};

function Taskboard($) {
    "use strict";
    var errorNoticeKey = 'error-popup';
    var taskboardApplication = this;
    var feed = null;
    var role = null;
    var performerRole = 4;
    var customerRole = 2;
    var systemRole = 1;
    var unauthorizedRole = 0;
    var timestampRefreshPeriod = 60000;
    var fadeOutSpeed = 1000;
    var fadeInSpeed = 1000;
    window.taskboard = this;
    this.newItemsCounter = 0;
    this.locale = 'ru';
    this.localization = new Localization();
    this.initialized = false;
    this.disableModals = false;
    this.currentForm = null;
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
        this.loadedTaskIds = [];
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
                        taskboardApplication.renderHtmlTasks(response, false);
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
            error.unspecified = taskboardApplication.localizedMessage('too_many_attempts');
            return {error: error};
        }
        if (response['responseJSON']) {
            if (response['responseJSON'].hasOwnProperty('error')) {
                return response['responseJSON'];
            } else {
                error.unspecified = taskboardApplication.localizedMessage('unknown_error');
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

    this.replaceTooltip = function () {
        var tooltip = $("[rel=tooltip]");
        tooltip.attr('data-original-title', taskboardApplication.localizedMessage('balance_tooltip'));
    };

    this.setLocale = function (locale) {
        if (locale != 'ru' && locale != 'en')
            return;
        var item = localStorage.getItem('locale');
        if (!item)
            item = 'en';
        if (locale == item)
            return;
        localStorage.setItem('locale', locale);
        $("#locale").text(locale.toUpperCase());
        taskboardApplication.updateLocales();
        taskboardApplication.initializeToggler();
        taskboardApplication.replaceTooltip();
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
            var seconds = Math.abs(milliseconds) / 1000;
            var minutes = seconds / 60;
            var hours = minutes / 60;
            var days = hours / 24;
            var years = days / 365;
            var prefix = seconds < 90 ? '' : taskboardApplication.localizedMessage('prefixAgo');
            var suffix = seconds < 90 ? '' : taskboardApplication.localizedMessage('suffixAgo');

            function substituteNumber(number) {
                return number;
            }

            var words = seconds < 90 && taskboardApplication.localizedMessage('just_now') ||
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

    this.initializeNotice = function (storageItemName) {
        var popupItem = taskboardApplication.localStorageGetItem(storageItemName);
        if (popupItem === null)
            return;
        $('#'.concat(storageItemName, '-text')).text(popupItem);
        var popup = $('#'.concat(storageItemName));
        popup.removeClass('hidden');
        popup.fadeTo(fadeInSpeed, 500).fadeOut(fadeOutSpeed, function () {
            popup.hide();
            $(popup.find('span')).text('');
        });
        taskboardApplication.localStorageRemoveItem(storageItemName);
    };

    this.setTaskPerformedById = function (id) {
        var indexOf = $.inArray(parseInt(id), feed.loadedTaskIds);
        var changed = indexOf > -1;
        if (changed) {
            $('#task-feed').find('li[data-id="'.concat(id, '"]')).each(function () {
                var $that = $(this);
                $that.find('.avatar-img').attr('src', '/img/m.png');
                $that.addClass('task-completed');
                $that.find('button').remove();
                $that.append('<button class="pull-right l10n l10n-text btn-link no-shadow task-completed" data-l10n="task_completed" disabled>Completed</button>');
                $that.find('.l10n').each(function () {
                    taskboardApplication.updateLocale($(this));
                });
            });
        }
        return changed;
    };

    this.deleteTaskById = function (id) {
        var indexOf = $.inArray(parseInt(id), feed.loadedTaskIds);
        $('#task-feed').find('li[data-id="'.concat(id, '"]')).fadeOut(fadeOutSpeed, function () {
            $(this).remove();
        });
        var changed = indexOf > -1;
        if (changed) {
            feed.loadedTaskIds.splice(indexOf, 1);
            var smallest = Math.min.apply(feed.loadedTaskIds);
            if (smallest < feed.lastTaskId)
                feed.lastTaskId = smallest;
        }
        return changed;
    };

    this.incrementBadge = function (id) {
        if ($.inArray(parseInt(id), feed.loadedTaskIds) === -1) {
            var newItemsCounter = this.newItemsCounter;
            this.newItemsCounter++;
            var $badge = $('#new-items-badge');
            $badge.find('.l10n').each(function () {
                taskboardApplication.updateLocale($(this));
            });
            if (newItemsCounter == 0) {
                $badge.fadeIn(fadeInSpeed);
            }
        }
    };

    this.decrementBadge = function () {
        this.newItemsCounter = 0;
        $('#new-items-badge').fadeOut(fadeOutSpeed);
    };

    this.onEvent = function (e) {
        if (e['id'] !== undefined) {
            $('#user-data').data('last-event-id', e['id']);
        }
        var data = e['data'];
        if (data !== undefined) {
            var jsonData = $.parseJSON(data);
            if (jsonData.constructor === Array) {
                var changed = false;
                $.each(jsonData, function () {
                    if (this.hasOwnProperty('d')) {
                        $.each(this['d'], function () {
                            changed = taskboardApplication.deleteTaskById(this);
                        });
                    } else if (this.hasOwnProperty('p')) {
                        $.each(this['p'], function () {
                            if (role == customerRole || role == systemRole)
                                changed = taskboardApplication.setTaskPerformedById(this);
                            else if (role == performerRole)
                                changed = taskboardApplication.deleteTaskById(this);
                        });
                    } else if (this.hasOwnProperty('c')) {
                        $.each(this['c'], function () {
                            taskboardApplication.incrementBadge(this);
                        });
                    } else {
                        console.log("Unknown event type");
                    }
                });
                if (changed && role === systemRole) {
                    taskboardApplication.updateBalance();
                }
            }
        } else {
            console.log("Unknown event");
        }
    };

    this.initializeEventStream = function () {
        if (window.es === undefined) {
            window.es = new EventSource("/events?lastEventId=".concat(encodeURIComponent(document.getElementById('user-data').getAttribute('data-last-event-id'))));
            window.es.onmessage = function (e) {
                taskboardApplication.onEvent(e);
            };
            window.es.onerror = function (e) {
                console.log(e);
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
        icon.removeClass(icon.data('icon')).addClass('fa fa-cog fa-spin');
    };

    this.replaceSpinnerWithIcon = function (icon) {
        icon.removeClass('fa fa-cog fa-spin').addClass(icon.data('icon'));
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

    this.renderHtmlTasks = function (html, prepend) {
        var feedHtml = $('#task-feed');
        var $html = $(html);
        $html.hide();
        $html.find('.l10n').each(function () {
            taskboardApplication.updateLocale($(this));
        });
        $html.each(function () {
            var dataId = $(this).data('id');
            if (dataId && $.inArray(dataId, feed.loadedTaskIds) < 0) {
                feed.loadedTaskIds.push(dataId);
            }
        });
        $html.find('.timestamp').each(function () {
            var $timestamp = $(this);
            $timestamp.setTimestamp();
            $timestamp.substituteTime();
        });
        var id = Math.min.apply(null, feed.loadedTaskIds);
        if (feed.lastTaskId == null || feed.lastTaskId > id) {
            feed.lastTaskId = id;
        }
        if (prepend === true) {
            feedHtml.prepend($html);
        } else if (prepend === false) {
            feedHtml.append($html);
        } else {
            prepend.replaceWith($html);
        }
        $html.fadeIn(fadeInSpeed);
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

    this.isPasswordResetForm = function (form) {
        return $(form).attr('id') == 'reset-form';
    };

    this.onPostSuccess = function (response) {
        var taskForm = taskboardApplication.isTaskForm(taskboardApplication.currentForm);
        var balanceForm = taskboardApplication.isBalanceForm(taskboardApplication.currentForm);
        var passwordResetForm = taskboardApplication.isPasswordResetForm(taskboardApplication.currentForm);
        taskboardApplication.removeFormSpinner();
        taskboardApplication.enableModals();
        taskboardApplication.closeFormModal();
        taskboardApplication.finalizeForm();
        if (taskForm) {
            taskboardApplication.updateBalance();
            taskboardApplication.replaceToken(response);
            taskboardApplication.renderHtmlTasks(response, true);
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
        if (passwordResetForm) {
            $('#email-sent-modal').modal('show');
        }
        if (response['redirect'] != null) {
            location.href = response['redirect'];
        }
    };

    this.closeFormOnUnknownError = function (message) {
        taskboardApplication.localStorageAddItem(errorNoticeKey, message);
        taskboardApplication.closeFormModal();
        taskboardApplication.initializeNotice(errorNoticeKey);
        taskboardApplication.finalizeForm();
    };

    this.closeTaskFormWithAlert = function () {
        taskboardApplication.closeFormModal();
        taskboardApplication.finalizeForm();
        $('#task-unpaid-modal').modal('show');
    };

    this.onPostError = function (response) {
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
                var localizedDescription = taskboardApplication.localizedMessage(error_name.concat('_', error_description)) || taskboardApplication.localizedMessage(error_description) || error_description;
                $errorSpan.text(localizedDescription);
            }
        });
        taskboardApplication.finalizeForm();
    };

    this.initializeFormModals = function () {
        var $modal = $('.modal');
        $modal.on('shown.bs.modal', function (e) {
            $(this).find("[autofocus]:first").focus();
        });
        $modal.on('hide.bs.modal', function (e) {
            if (taskboardApplication.disableModals) {
                e.preventDefault();
            } else {
                taskboardApplication.cleanupModal(this);
            }
        });
        $('#reset-password').click(function () {
            taskboardApplication.enableModals();
            $('#login-form-modal').modal('hide');
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
        $('#login-form,#signup-form,#task-form,#account-form,#reset-form,#change-password-form').submit(function (e) {
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
                    $errorSpan.text(taskboardApplication.localizedMessage('not_provided'));
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
                success: taskboardApplication.onPostSuccess,
                error: taskboardApplication.onPostError
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
        if (role !== performerRole) {
            var $check = $('#hide-completed');
            $check.change(function () {
                if (this.checked) {
                    $('#task-feed').attr('data-hide-completed', true);
                } else {
                    $('#task-feed').attr('data-hide-completed', false);
                }
            });
            $check.prop('checked', true);
            $('#task-feed').attr('data-hide-completed', true);
        }
    };

    this.initializeTooltips = function () {
        $("[rel=tooltip]").tooltip({placement: 'bottom'});
    };

    this.parseJsonResponseError = function (response) {
        var json = taskboardApplication.parseJsonResponse(response);
        if (json && json.hasOwnProperty('error') && json['error'] != null) {
            if (json.error['unspecified']) {
                return {error: taskboardApplication.localizedMessage(json.error.unspecified) || json.error.unspecified};
            } else if (json.error['popup']) {
                return {popup: json.error.popup};
            } else {
                return {error: JSON.stringify($.parseJSON(json.error))};
            }
        } else {
            return {error: taskboardApplication.localizedMessage('error_unknown')};
        }
    };

    this.updateLocales = function () {
        $('.l10n').each(function () {
            taskboardApplication.updateLocale($(this));
        });
        $('.timestamp').substituteTime();
    };

    this.initializeToggler = function () {
        var $toggler = $('#toggler');
        $toggler.bootstrapToggle('destroy');
        $toggler.bootstrapToggle({
            on: function () {
                return taskboardApplication.localizedMessage('i_am_customer');
            },
            off: function () {
                return taskboardApplication.localizedMessage('i_am_performer');
            }
        });
    };

    this.onNonPostError = function (response) {
        var responseError = taskboardApplication.parseJsonResponseError(response);
        if (responseError['popup']) {
            var selector = '#'.concat(responseError.popup.replaceAll('_', '-')).concat('-modal');
            $(selector).modal('show')
        } else if (responseError['error']) {
            taskboardApplication.localStorageAddItem(errorNoticeKey, responseError.error);
            taskboardApplication.initializeNotice(errorNoticeKey);
        } else {
            console.error("Silently ignore unknown error");
        }
    };

    this.onTaskRemove = function (task) {
        task.fadeOut(fadeOutSpeed, function () {
            task.remove();
        });
        taskboardApplication.updateBalance();
    };

    this.onTaskCreate = function (response, task) {
        taskboardApplication.renderHtmlTasks(response, task);
        taskboardApplication.updateBalance();
    };

    this.sendNonPost = function (el, method, successCallback, errorCallback) {
        var task = $(el).closest('.task-feed-item');
        var id = task.data('id');
        var csrf = $(el).data('csrf');
        $.ajax({
            url: 'api/v1/task/' + id,
            contentType: 'application/json; charset=UTF-8',
            type: method,
            headers: {
                "X-CSRF-TOKEN": csrf
            },
            success: function (response) {
                return successCallback(response, task);
            },
            error: function (response) {
                return errorCallback(response, task);
            }
        });
    };

    this.initializeBadgeUpdate = function () {
        $('#new-items-badge').click(function () {
            if (feed.loading)
                return;
            feed.loading = true;
            var latestTaskID = 0;
            $('#task-feed').find('li').each(function () {
                var id = parseInt($(this).data('id'));
                if (id > latestTaskID) {
                    latestTaskID = id;
                }
            });
            $.ajax({
                url: 'api/v1/task',
                contentType: 'application/json; charset=UTF-8',
                type: "GET",
                headers: {
                    "X-FETCH-NEW": latestTaskID
                },
                success: function (response) {
                    feed.loading = false;
                    if (response != null) {
                        if (response.trim() != "")
                            taskboardApplication.renderHtmlTasks(response, true);
                        taskboardApplication.decrementBadge();
                    }
                },
                error: function (response) {
                    feed.loading = false;
                    taskboardApplication.decrementBadge();
                    console.log(response);
                }
            });
        });
    };

    this.initialize = function () {
        "use strict";
        taskboardApplication.initializeExtensions();
        taskboardApplication.locale = localStorage.getItem('locale') || 'en';
        $("#locale").text(taskboardApplication.locale.toUpperCase());
        taskboardApplication.updateLocales();
        taskboardApplication.initializeToggler();
        $('#english').click(function (e) {
            e.preventDefault();
            taskboardApplication.setLocale('en');
        });
        $('#russian').click(function (e) {
            e.preventDefault();
            taskboardApplication.setLocale('ru');
        });
        role = $('#user-data').data('role');
        taskboardApplication.logger.enableLogger();
        if (taskboardApplication.initialized)
            return this;
        taskboardApplication.initialized = true;
        taskboardApplication.initializeNotice(errorNoticeKey);
        taskboardApplication.initializeListeners();
        taskboardApplication.initializeFormListeners();
        taskboardApplication.initializeFormModals();
        taskboardApplication.initializeTimestampRefresher(timestampRefreshPeriod);
        taskboardApplication.initializeTooltips();
        if (role !== undefined) {
            if (role != unauthorizedRole) {
                taskboardApplication.updateBalance();
                taskboardApplication.initializeFeed();
                taskboardApplication.initializeEventStream();
                taskboardApplication.initializeBadgeUpdate();
                if (role == customerRole) {
                    $(document).on('click', '.delete-task', function () {
                        if (feed.loading)
                            return;
                        feed.loading = true;
                        var el = $(this);
                        taskboardApplication.sendNonPost(el, 'DELETE', function (response, task) {
                            feed.loading = false;
                            taskboardApplication.onTaskRemove(task);
                        }, function (response, task) {
                            feed.loading = false;
                            taskboardApplication.onNonPostError(response, task);
                        });
                    });
                    $(document).on('click', '.fix-task', function () {
                        if (feed.loading)
                            return;
                        feed.loading = true;
                        var el = $(this);
                        taskboardApplication.sendNonPost(el, 'POST', function (response, task) {
                            feed.loading = false;
                            taskboardApplication.onTaskCreate(response, task);
                        }, function (response, task) {
                            feed.loading = false;
                            taskboardApplication.onNonPostError(response, task);
                        });
                    });
                }
                if (role == performerRole) {
                    $(document).on('click', '.perform-task', function () {
                        if (feed.loading)
                            return;
                        feed.loading = true;
                        var el = $(this);
                        el.attr('disabled', true);
                        taskboardApplication.replaceIconWithSpinner(el.find('i'));
                        taskboardApplication.sendNonPost(el, 'PUT', function (response, task) {
                            feed.loading = false;
                            taskboardApplication.replaceSpinnerWithIcon(el.find('i'));
                            el.attr('disabled', false);
                            taskboardApplication.onTaskRemove(task);
                        }, function (response, task) {
                            feed.loading = false;
                            taskboardApplication.replaceSpinnerWithIcon(el.find('i'));
                            el.attr('disabled', false);
                            taskboardApplication.onNonPostError(response, task);
                        });
                    });
                }
            } else {
                $('#change-password-form-modal').modal('show');
            }
        }
        return taskboardApplication;
    };
    return taskboardApplication.initialize();
}

$(document).ready(function () {
    "use strict";
    window.taskboard = new Taskboard($);
});
