function Taskboard($) {
    "use strict";
    var successPopupKey = 'success-popup';
    var errorPopupKey = 'error-popup';
    var responseLocalStorage = 'local_storage';
    var responseRenderData = 'render_data';
    var taskboardApplication = this;
    var localization = null;
    var feed = null;
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
            $('#nomorecontent').show();
        };
        this.load = function () {
            if (feed.lastTaskId == -1) {
                feed.loading = false;
                return;
            }
            $.ajax({
                url: 'https://taskboard.dev/api/v1/task',
                data: feed.buildQuery(limit),
                contentType: 'application/json; charset=UTF-8',
                type: "GET",
                success: function (response) {
                    feed.hideLoading();
                    feed.loading = false;
                    var jsonResponse = $.parseJSON(response);
                    if (jsonResponse instanceof Array) {
                        var arrLength = jsonResponse.length;
                        if (jsonResponse.length == 0) {
                            feed.lastTaskId = -1;
                            feed.noMoreContent();
                        }
                        var lastTaskId = null;
                        for (var i = 0; i < arrLength; i++) {
                            var task = jsonResponse[i];
                            if (lastTaskId == null)
                                lastTaskId = task['id'];
                            if (lastTaskId != null || lastTaskId > task['id'])
                                lastTaskId = task['id'];
                            taskboardApplication.render(task);
                        }
                        if (lastTaskId != null)
                            feed.lastTaskId = lastTaskId;
                    } else {
                        console.log(response);
                    }
                },
                error: function (error) {
                    taskboardApplication.closeFormOnUnknownError(error);
                    feed.loading = false;
                    feed.hideLoading();
                    console.log(error);
                }
            });
        }
    };

    this.Task = function (taskJson) {
        this.id = taskJson['id'];
        this.description = taskJson['description'];
        this.customer_id = taskJson['customer_id'];
        this.performer_id = taskJson['performer_id'];
        this.created_at_offset = taskJson['created_at_offset'];
        this.updated_at_offset = taskJson['updated_at_offset'];
        this.price = taskJson['amount'];
        var currentDate = new Date().getTime();
        var createdAt = this.created_at_offset == null ? "" : currentDate - this.created_at_offset;
        this.asHTML = function () {
            return ''.concat(
                '<li class="task-feed-item media">',
                '<div class="media-body">',
                '<p class="task-description">', this.description, '</p>',
                '<span class="task-price">', this.price, '</span>',
                '<span class="timestamp created_at" data-timestamp="', createdAt.toString(), '"></span>',
                '</div>',
                '</li>'
            );
        };
        return this;
    };

    this.initializeExtentions = function () {
        $.fn.substituteTime = function () {
            var milliseconds = new Date().getTime() - $(this).data('timestamp');
            var prefix = localization.prefixAgo;
            var suffix = localization.suffixAgo;
            var seconds = Math.abs(milliseconds) / 1000;
            var minutes = seconds / 60;
            var hours = minutes / 60;
            var days = hours / 24;
            var years = days / 365;

            function substituteNumber(number) {
                return (localization.numbers && localization.numbers[number]) || number;
            }

            var words = seconds < 45 && localization.seconds.format(substituteNumber(Math.round(seconds))) ||
                seconds < 90 && localization.minute.format(substituteNumber(1)) ||
                minutes < 45 && localization.minutes.format(substituteNumber(Math.round(minutes))) ||
                minutes < 90 && localization.hour.format(substituteNumber(1)) ||
                hours < 24 && localization.hours.format(substituteNumber(Math.round(hours))) ||
                hours < 42 && localization.day.format(substituteNumber(1)) ||
                days < 30 && localization.days.format(substituteNumber(Math.round(days))) ||
                days < 45 && localization.month.format(substituteNumber(1)) ||
                days < 365 && localization.months.format(substituteNumber(Math.round(days / 30))) ||
                years < 1.5 && localization.year.format(substituteNumber(1)) ||
                localization.years.format(substituteNumber(Math.round(years)));
            var value = $.trim([prefix, words, suffix].join(localization.wordSeparator));
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

    this.delay = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();

    this.initializeSearch = function () {
        var searchInput = $('#search');
        var callback = function () {
            console.log(searchInput.val());
            var icon = searchInput.closest('form').find('i');
            taskboardApplication.replaceIconWithSpinner(icon);
            taskboardApplication.delay(function () {
                taskboardApplication.replaceSpinnerWithIcon(icon);
            }, 10000);
        };
        searchInput.closest('form').submit(function (e) {
            e.preventDefault();
        });
        searchInput.keyup(function () {
            taskboardApplication.delay(callback, 300);
        });
    };

    this.initializeListeners = function () {
        $('#btn-logout').click(function () {
            window.location = "/api/v1/auth/logout";
        });
        $('.modal').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $(this).find('.error-description').each(function () {
                $(this).empty();
            });
            $(this).find('.has-error').each(function () {
                $(this).removeClass('has-error');
            });
        });
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

    this.render = function (data) {
        var task = (new taskboardApplication.Task(data)).asHTML();
        var feed = $('#task-feed');
        feed.prepend(task);
        feed.find('> li :first').find('.timestamp').substituteTime();
    };

    this.processResponseEvent = function (event, response) {
        var localStorageItems = event[responseLocalStorage];
        if (localStorageItems != null) {
            localStorageItems.forEach(function (localStorageItem) {
                taskboardApplication.localStorageAddItem(localStorageItem['key'], localStorageItem['value']);
            });
        }
        var renderData = event[responseRenderData];
        if (renderData != null) {
            if (response['data'].hasOwnProperty('task'))
                taskboardApplication.render(response['data']['task']);
        }
    };

    this.finalizeForm = function () {
        $(taskboardApplication.currentForm).find('button').each(function () {
            $(this).prop('disabled', false);
        });
        taskboardApplication.currentForm = null;
    };

    this.processResponseEvents = function (response) {
        if (response['event'] != null) {
            response['event'].forEach(function (event) {
                taskboardApplication.processResponseEvent(event, response);
            });
        }
    };

    this.closeFormModal = function () {
        $(taskboardApplication.currentForm).closest('.modal').modal('toggle');
    };

    this.onFormSuccess = function (response) {
        taskboardApplication.removeFormSpinner();
        taskboardApplication.enableModals();
        taskboardApplication.closeFormModal();
        taskboardApplication.finalizeForm();
        taskboardApplication.initializePopup(successPopupKey);
        if (response == null) {
            console.error("Something went extremely wrong here, response is not JSON");
            console.error(response);
            return;
        }
        taskboardApplication.processResponseEvents(response);
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

    this.onFormError = function (response) {
        taskboardApplication.removeFormSpinner();
        taskboardApplication.enableModals();
        var json = response['responseJSON'];
        if (json == null) {
            taskboardApplication.closeFormOnUnknownError("Something went extremely wrong here, response is not a JSON.");
            return;
        }
        if (json.error == null) {
            taskboardApplication.closeFormOnUnknownError("Something went extremely wrong here, response is JSON of error type, but doesn't have any explanatory fields.");
            return;
        }
        if (response.status === 401) {
            taskboardApplication.closeFormOnUnknownError(json.error);
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
        $.each(json.error, function (error_name, error_description) {
            var $errorSpan = $('#'.concat(taskboardApplication.currentFormId(), '-error-', error_name));
            $errorSpan.parent('div').addClass('has-error');
            $errorSpan.text(error_description);
        });
        taskboardApplication.processResponseEvents(response);
        taskboardApplication.initializePopup(errorPopupKey);
        taskboardApplication.finalizeForm();
    };

    this.initializeFormModals = function () {
        var $modal = $('.modal');
        $modal.on('hide.bs.modal', function (e) {
            $(this).find('form :input[name=csrf_token]').val('');
            if (taskboardApplication.disableModals) {
                e.preventDefault();
            } else {
                taskboardApplication.cleanupModal(this);
            }
        });
        $modal.on('show.bs.modal', function (e) {
            var modal = $(this);
            var csrfInput = $(this).find('form').find('input[name=csrf_token]');
            if (csrfInput.val() == null || csrfInput.val() == "") {
                e.preventDefault();
                var request = $(this).data('type');
                if (request !== undefined) {
                    $.ajax({
                        url: 'https://taskboard.dev/api/v1/csrf/'.concat(request),
                        contentType: 'application/json; charset=UTF-8',
                        type: "GET",
                        success: function (response) {
                            csrfInput.val(response);
                            modal.modal('show');
                        },
                        error: function (error) {
                            taskboardApplication.closeFormOnUnknownError(error);
                            console.log(error);
                        }
                    });
                }
            }
        });
    };

    this.initializeFormListeners = function () {
        $('#login-form,#signup-form,#task-form').submit(function (e) {
            e.preventDefault();
            if (taskboardApplication.currentForm != null) {
                return;
            }
            taskboardApplication.disableModals = true;
            taskboardApplication.currentForm = this;
            taskboardApplication.cleanupModal($(this).closest('.modal'));
            $(this).find('button').each(function () {
                $(this).attr('disabled', 'true');
            });
            var data = taskboardApplication.serializeForm();
            var action = $(taskboardApplication.currentForm).attr('action');
            taskboardApplication.addFormSpinner();
            $.ajax({
                url: action,
                dataType: 'json',
                contentType: 'application/json; charset=UTF-8',
                type: "POST",
                data: data,
                success: taskboardApplication.onFormSuccess,
                error: taskboardApplication.onFormError
            });
        });
    };

    this.serializeForm = function () {
        return JSON.stringify($(taskboardApplication.currentForm).serializeObject());
    };

    this.initialize = function () {
        "use strict";
        taskboardApplication.initializeExtentions();
        localization = new Localization();
        feed = new taskboardApplication.Feed(100);
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
        taskboardApplication.initializeSearch();
        feed.initialize();
        taskboardApplication.initializeEventStream();
        return taskboardApplication;
    };
    return taskboardApplication.initialize();
}

$(document).ready(function () {
    "use strict";
    new Taskboard($);
});
