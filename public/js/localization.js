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
    this.refill_en = "Refill";
    this.refill_ru = "Пополнить";
    this.refill_account_en = "Refill Account";
    this.refill_account_ru = "Пополнить Баланс";
    this.amount_en = "Amount";
    this.amount_ru = "Количество";
    this.price_en = "Price";
    this.price_ru = "Стоимость";
    this.description_en = "Description";
    this.description_ru = "Описание";
    this.delete_task_en = "Delete";
    this.delete_task_ru = "Удалить";
    this.fix_task_en = "Fix payment";
    this.fix_task_ru = "Оплатить";
    this.unpaid_en = "Unpaid";
    this.unpaid_ru = "Не оплачен";
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
    this.performer_customer_en = "I'm a performer|I'm a customer";
    this.performer_customer_ru = "Исполнитель|Заказчик";
    this.balance_tooltip_en = "Note that the balance shown here is the difference between account balance and active task prices";
    this.balance_tooltip_ru = "Баланс показан за вычетом стоимости активных заказов";
    this.commission_en = function () {
        return "Note that the system commission ".concat($('#user-data').data('commission'), "% will be applied");
    };
    this.commission_ru = function () {
        return "Обратите внимание, что система взимает комиссию в размере ".concat($('#user-data').data('commission'), "%");
    };
    this.unpaid_notice_header_en = "Notice";
    this.unpaid_notice_header_ru = "Внимание";
    this.unpaid_notice_body_en = "You have unpaid tasks. You should either fix them or delete to create new task.";
    this.unpaid_notice_body_ru = "У вас имеются неоплаченные заказы. Попробуйте оплатить их или удалить перед тем, как создавать новый заказ.";
    this.minute_en = "minute";
    this.minute_ru = "минуту";
    this.minutes_ru = "минуты";
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
    this.numbers_en = [];
    this.numbers_ru = [];
}