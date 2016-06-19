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
    this.create_task_en = "Create Task";
    this.create_task_ru = "Создать заказ";
    this.logout_en = "Logout";
    this.logout_ru = "Выйти";
    this.login_en = "Sign In";
    this.login_ru = "Войти";
    this.signup_en = "Sign Up";
    this.signup_ru = "Регистрация";
    this.to_signup_en = "Sign Up";
    this.to_signup_ru = "Зарегистрироваться";
    this.refill_en = "Refill";
    this.refill_ru = "Пополнить";
    this.refill_account_en = "Refill Account";
    this.refill_account_ru = "Пополнить баланс";
    this.change_password_en = "Change Password";
    this.change_password_ru = "Сменить пароль";
    this.amount_en = "Amount";
    this.amount_ru = "Сумма";
    this.price_en = "Price";
    this.price_ru = "Стоимость";
    this.price_placeholder_en = this.price_en;
    this.price_placeholder_ru = this.price_ru;
    this.description_en = "Description";
    this.description_ru = "Описание";
    this.description_placeholder_en = this.description_en;
    this.description_placeholer_ru = this.description_ru;
    this.delete_task_en = "Delete";
    this.delete_task_ru = "Удалить";
    this.fix_task_en = "Fix payment";
    this.fix_task_ru = "Оплатить";
    this.unpaid_en = "Unpaid";
    this.unpaid_ru = "Не оплачен";
    this.task_already_paid_ru = "Already paid";
    this.task_already_paid_en = "Заказ уже оплачен";
    this.task_feed_en = "Task Feed";
    this.task_feed_ru = "Лента заказов";
    this.loading_en = "Loading...";
    this.loading_ru = "Загрузка...";
    this.no_more_content_en = "All latest tasks shown";
    this.no_more_content_ru = "Показаны все последние заказы";
    this.remember_me_en = "Remember me";
    this.remember_me_ru = "Запомнить меня";
    this.email_en = "Email";
    this.email_ru = "Email";
    this.password_en = "Password";
    this.password_ru = "Пароль";
    this.password_repeat_en = "Password repeat";
    this.password_repeat_ru = "Повторите пароль";
    this.reset_password_en = "Reset password";
    this.reset_password_ru = "Сбросить пароль";
    this.i_am_performer_en = "I'm a performer";
    this.i_am_performer_ru = "Исполнитель";
    this.i_am_customer_en = "I'm a customer";
    this.i_am_customer_ru = "Заказчик";
    this.performer_customer_en = "I'm a performer|I'm a customer";
    this.performer_customer_ru = "Исполнитель|Заказчик";
    this.balance_tooltip_en = "Note that the balance shown here is the difference between account balance and active task prices";
    this.balance_tooltip_ru = "Баланс показан за вычетом стоимости активных заказов";
    this.not_provided_en = "Not Provided";
    this.not_provided_ru = "Поле не заполнено";
    this.token_wrong_en = "Token is invalid";
    this.token_wrong_ru = "Неверный токен";
    this.email_not_provided_en = "Email not provided";
    this.email_not_provided_ru = "Email не заполнен";
    this.password_not_provided_en = "Password not provided";
    this.password_not_provided_ru = "Пароль не заполнен";
    this.password_not_provided_en = "Password repeat not provided";
    this.password_not_provided_ru = "Подтверждение пароля не заполнено";
    this.email_is_invalid_en = "Email is invalid";
    this.email_is_invalid_ru = "Указано неверное значение для Email";
    this.password_is_invalid_en = "Password is invalid";
    this.password_is_invalid_ru = "Указано неверное значение пароля";
    this.email_is_too_short_en = "Email is too short";
    this.email_is_too_short_ru = "Слишком короткий email";
    this.email_already_registered_en = "User with this email alreadt registered";
    this.email_already_registered_ru = "Пользователь с таким именем уже существует";
    this.password_is_too_short_en = "Password is too short";
    this.password_is_too_short_ru = "Слишком короткий пароль";
    this.email_is_too_long_en = "Email is way too long";
    this.email_is_too_long_ru = "Длинновато, парень";
    this.password_is_too_long_en = "Password is way too long";
    this.password_is_too_long_ru = "Длинновато, парень";
    this.email_no_such_user_en = "Wrong email and/or password";
    this.email_no_such_user_ru = "Неверный email и/или пароль";
    this.password_mismatch_en = "Passwords doesn't match";
    this.password_mismatch_ru = "Пароль не совпадает с подтверждением";
    this.password_repeat_mismatch_en = "Passwords doesn't match";
    this.password_repeat_mismatch_ru = "Пароль не совпадает с подтверждением";
    this.price_too_small_en = "Price is too small";
    this.price_too_small_ru = "Слишком маленькая сумма";
    this.price_too_large_en = "Price is too large";
    this.price_too_large_ru = "Слишком большая стоимость";
    this.amount_too_small_en = "Amount is too small";
    this.amount_too_small_ru = "Слишком маленькая сумма";
    this.amount_too_large_en = "Amount is too large";
    this.amount_too_large_ru = "Слишком большая стоимость";
    this.amount_is_invalid_en = "Amount is invalid";
    this.amount_is_invalid_ru = "Сумма указана неверно";
    this.price_is_invalid_en = "Price is invalid";
    this.price_is_invalid_ru = "Стоимость указана неверно";
    this.amount_not_provided_en = "Amount not provided";
    this.amount_not_provided_ru = "Сумма не указана";
    this.price_not_provided_en = "Price not provided";
    this.price_not_provided_ru = "Стоимость не указана";
    this.amount_not_enough_en = "Not enough money";
    this.amount_not_enough_ru = "Недостаточно денег";
    this.task_unable_to_process_en = "Couldn't process";
    this.task_unable_to_process_ru = "Не удалось повторить транзакцию";
    this.price_not_enough_en = this.amount_not_enough_en;
    this.price_not_enough_ru = this.amount_not_enough_ru;
    this.too_many_attempts_en = "Too many attempts, try again later";
    this.too_many_attempts_ru = "Слишком много попыток, попробуйте позже";
    this.hour_ago_en = "About an hour ago";
    this.hour_ago_ru = "Около часа назад";
    this.hide_completed_en = "Hide Completed";
    this.hide_completed_ru = "Скрыть Завершённые";
    this.task_completed_en = "Completed";
    this.task_completed_ru = "Выполнен";
    this.unspecified_too_many_attempts_en = this.too_many_attempts_en;
    this.unspecified_too_many_attempts_ru = this.too_many_attempts_ru;
    this.error_unknown_en = "Unknown error occurred";
    this.error_unknown_ru = "Произошла неизвестная ошибка";
    this.commission_en = function () {
        return "Note that the system commission ".concat($('#user-data').data('commission'), "% will be applied");
    };
    this.commission_ru = function () {
        return "Обратите внимание, что система взимает комиссию в размере ".concat($('#user-data').data('commission'), "%");
    };
    this.email_sent_notice_header_en = "Notice";
    this.email_sent_notice_header_ru = "Внимание";
    this.email_sent_notice_body_en = "Password reset link has been sent to your email.";
    this.email_sent_notice_body_ru = "Ссылка для сброса пароля отправлена на ваш email.";
    this.unpaid_notice_header_en = "Notice";
    this.unpaid_notice_header_ru = "Внимание";
    this.unpaid_notice_body_en = "You have unpaid tasks. You should either fix them or delete to create new task.";
    this.unpaid_notice_body_ru = "У вас имеются неоплаченные заказы. Попробуйте оплатить их или удалить перед тем, как создавать новый заказ.";
    this.already_paid_notice_header_en = "Notice";
    this.already_paid_notice_header_ru = "Внимание";
    this.already_paid_notice_body_en = "You cannot remove active task. Only unpaid tasks can be removed.";
    this.already_paid_notice_body_ru = "Нельзя удалить активный заказ. Только неоплаченные заказы могут быть удалены.";
    this.already_performed_notice_header_en = "Notice";
    this.already_performed_notice_header_ru = "Внимание";
    this.already_performed_notice_body_en = "Seems like this task already performed. Try to perform another task.";
    this.already_performed_notice_body_ru = "Похоже, заказ уже выполнен. Попробуйте выполнить другой заказ.";
    this.already_performed_notice_body_en = "Seems like this task already performed. Try to perform another task.";
    this.already_performed_notice_body_ru = "Похоже, заказ уже выполнен. Попробуйте выполнить другой заказ.";
    this.not_enough_money_notice_header_en = "Error";
    this.not_enough_money_notice_header_ru = "Ошибка";
    this.not_enough_money_notice_body_en = "You don't have enough money to pay for this task.";
    this.not_enough_money_notice_body_ru = "Недостаточно денег для оплаты заказа.";
    this.not_found_en = "Not found";
    this.not_found_ru = "Ресурс не найден";
    this.unsupported_media_type_en = "Unsupported Media Type";
    this.unsupported_media_type_ru = "Тип запроса не поддерживатся";
    this.method_not_allowed_en = "Method Not Allowed";
    this.method_not_allowed_ru = "Метод не разрешён";
    this.something_went_wrong_en = "Something went wrong";
    this.something_went_wrong_ru = "Что-то пошло не так";
    this.not_authorized_en = "Unauthorized";
    this.not_authorized_ru = "Ошибка авторизации";
    this.forbidden_en = "Forbidden";
    this.forbidden_ru = "Запрещено";
    this.minute_en = "minute";
    this.minute_ru = "минуту";
    this.minutes_ru = "минуты";
    this.just_now_ru = "только что";
    this.just_now_en = "just now";
    this.prefixAgo_en = null;
    this.prefixAgo_ru = null;
    this.suffixAgo_en = "ago";
    this.suffixAgo_ru = "назад";
    this.seconds_en = "less than a minute";
    this.seconds_ru = "менее минуты";
    this.minute_en = "about a minute";
    this.minute_ru = "около минуты";
    this.minutes_en = "{0} minutes";
    this.minutes_ru = "{0} минут";
    this.hour_en = "about an hour";
    this.hour_ru = "около часа";
    this.hours_en = "about {0} hours";
    this.hours_ru = "около {0} часов";
    this.day_en = "a day";
    this.day_ru = "день";
    this.days_en = "{0} days";
    this.days_ru = "{0} дней";
    this.month_en = "about a month";
    this.month_ru = "около месяца";
    this.months_en = "{0} months";
    this.months_ru = "{0} месяцев";
    this.year_en = "about a year";
    this.year_ru = "около года";
    this.years_en = "{0} years";
    this.years_ru = "{0} лет";
    this.wordSeparator_en = " ";
    this.wordSeparator_ru = " ";
    this.numbers_en = ["zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine", "ten"];
    this.numbers_ru = [];
    this.new_items_en = function () {
        var newItemsCounter = window.taskboard.newItemsCounter;
        if (newItemsCounter > 10) {
            return "There's more than 10 new tasks";
        } else if (newItemsCounter > 1) {
            return "There're {0} new tasks".format(newItemsCounter);
        } else if (newItemsCounter === 1) {
            return "There's 1 new task";
        }
    };
    this.new_items_ru = function () {
        var newItemsCounter = window.taskboard.newItemsCounter;
        switch (newItemsCounter) {
            case 1:
                return "{0} новый заказ".format(newItemsCounter);
            case 2:
            case 3:
            case 4:
                return "{0} новых заказа".format(newItemsCounter);
                break;
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
                return "{0} новых заказов".format(newItemsCounter);
                break;
            default:
                return "Более 10 новых заказов".format(newItemsCounter);
        }
    };
}
