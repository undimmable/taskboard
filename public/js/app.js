function Taskboard() {
    "use strict";
    var successPopupKey = 'success-popup';
    var errorPopupKey = 'error-popup';
    var responseLocalStorage = 'local_storage';
    var responseRenderData = 'render_data';
    var taskboardApplication = this;
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

    this.Task = function (taskJson) {
        this.id = taskJson['id'];
        this.description = taskJson['description'];
        this.customer_id = taskJson['customer_id'];
        this.performer_id = taskJson['performer_id'];
        this.created_at = taskJson['created_at'];
        if (this.created_at == null)
            this.created_at = Date.now();
        this.price = taskJson['amount'];
        this.asHTML = function () {
            return ''.concat(
                '<li class="task-feed-item media">',
                '<div class="media-body">',
                '<p class="task-description">', this.description, '</p>',
                '<span class="task-price">', this.price, '</span>',
                '<span class="task_created_at">', this.created_at, '</span>',
                '</li>',
                '</div>'
            );
        };
        return this;
    };

    this.initializePopup = function (storageItemName) {
        var popupItem = taskboardApplication.localStorageGetItem(storageItemName);
        if (popupItem === null)
            return;
        $('#'.concat(storageItemName, '-text')).text(popupItem);
        var popup = $('#'.concat(storageItemName));
        popup.removeClass('hidden');
        popup.fadeTo(2000, 500).slideUp(500, function () {
            popup.hide();
            $(popup.find('span')).text('');
        });
        taskboardApplication.localStorageRemoveItem(storageItemName);
    };

    this.initializeEventStream = function () {
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
        $('#task-feed').prepend((new taskboardApplication.Task(data['task'])).asHTML());
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
            taskboardApplication.render(response['data']);
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
        taskboardApplication.currentForm = null;
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
        $modal.on('shown.bs.modal', function () {
            var request = $(this).data('type');
            var csrfInput = $(this).find('form').find('input[name=csrf_token]');
            $.ajax({
                url: 'https://taskboard.dev/api/v1/csrf/'.concat(request),
                contentType: 'application/json; charset=UTF-8',
                type: "GET",
                success: function (response) {
                    $(csrfInput).val(response);
                },
                error: function (error) {
                    console.log(error);
                }
            });
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
        taskboardApplication.initializeEventStream();
        taskboardApplication.initializeSearch();
        return taskboardApplication;
    };
    return taskboardApplication.initialize();
}

$(document).ready(function () {
    "use strict";
    //noinspection JSUnusedLocalSymbols
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
    new Taskboard();
});
