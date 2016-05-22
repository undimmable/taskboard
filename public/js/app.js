function Taskboard() {
    "use strict";
    var successPopupKey = 'success-popup';
    var errorPopupKey = 'error-popup';
    var responseLocalStorage = 'local_storage';
    var responseRenderData = 'render_data';
    var taskboardApplication = this;
    this.initialized = false;
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

    this.task = function (taskJson) {
        this.id = taskJson['id'];
        this.description = taskJson['description'];
        this.customer_id = taskJson['customer_id'];
        this.performer_id = taskJson['performer_id'];
        this.created_at = taskJson['created_at'];
        this.price = taskJson['amount'];
        this.asHTML = function () {
            return ''.concat(
                '<div class="task-feed-item">',
                '<span class="task-description">', this.description, '</span>',
                '<span class="task-price">', this.price, '</span>',
                '<span class="task_created_at">', this.created_at, '</span>',
                '</div>'
            );
        };
        return this;
    };

    this.initializePopup = function (storageItemName, autohide) {
        var popupItem = taskboardApplication.localStorageGetItem(storageItemName);
        if (popupItem === null)
            return;
        $('#'.concat(storageItemName, '-text')).text(popupItem);
        var popup = $('#'.concat(storageItemName));
        popup.removeClass('hidden');
        // if (autohide) {
        popup.fadeTo(2000, 500).slideUp(500, function () {
            popup.hide();
            $(popup.find('span')).text('');
        });
        // }
        taskboardApplication.localStorageRemoveItem(storageItemName);
    };

    this.initializeSearch = function () {
        $('#search').on('keyup', function () {
            console.log($(this).val());
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
    };

    this.addFormSpinner = function () {
        $('#'.concat(taskboardApplication.currentFormId(), '-spinner')).each(function () {
            $(this).removeAttr('class');
            $(this).addClass('glyphicon glyphicon-refresh spinning');
        });
    };

    this.removeFormSpinner = function () {
        $('#'.concat(taskboardApplication.currentFormId(), '-spinner')).each(function () {
            $(this).removeClass('glyphicon-refresh');
            $(this).removeClass('spinning');
            $(this).addClass($(this).data('icon'));
        });
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
        console.log(JSON.stringify(data));
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
            taskboardApplication.render(renderData, response['data']);
        }
    };

    this.finalizeForm = function () {
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
        taskboardApplication.closeFormModal();
        taskboardApplication.initializePopup(successPopupKey, true);
        taskboardApplication.finalizeForm();
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
        taskboardApplication.initializePopup(errorPopupKey, false);
        taskboardApplication.finalizeForm();
    };

    this.onFormError = function (response) {
        taskboardApplication.removeFormSpinner();
        var json = response['responseJSON'];
        if (json == null) {
            taskboardApplication.closeFormOnUnknownError("Something went extremely wrong here, response is not a JSON.");
            return;
        }
        if (json.error == null) {
            taskboardApplication.closeFormOnUnknownError("Something went extremely wrong here, response is JSON of error type, but doesn't have any explanatory fields.");
            return;
        }
        $(taskboardApplication.currentForm).find('input').one('change', function () {
            $(this).closest('form').find('button').each(function () {
                $(this).prop('disabled', false);
            });
        });
        $.each(json.error, function (error_name, error_description) {
            var $errorSpan = $('#'.concat(taskboardApplication.currentFormId(), '-error-', error_name));
            $errorSpan.parent('div').addClass('has-error');
            $errorSpan.text(error_description);
        });
        taskboardApplication.processResponseEvents(response);
        taskboardApplication.initializePopup(errorPopupKey, false);
        taskboardApplication.currentForm = null;
    };

    this.initializeFormModals = function () {
        $('.modal').on('hide.bs.modal', function (e) {
            if (taskboardApplication.currentForm != null) {
                e.preventDefault();
            } else {
                taskboardApplication.cleanupModal(this);
            }
        });
    };

    this.initializeFormListeners = function () {
        $('#login-form,#signup-form,#task-form').submit(function (e) {
            e.preventDefault();
            if (taskboardApplication.currentForm != null) {
                return;
            }
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
            taskboardApplication.initializePopup(entry, true);
        });
        taskboardApplication.initializeListeners();
        taskboardApplication.initializeFormListeners();
        taskboardApplication.initializeFormModals();
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
    var taskboard = new Taskboard();
});