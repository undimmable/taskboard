<?php
$balance = get_balance(get_authorized_user()[ID]);
$csrf = get_account_csrf(get_authorized_user()[ID]);
?>
<li>
    <a type="button" class="btn btn-link l10n l10n-tooltip" data-l10n="balance_tooltip" data-toggle="modal" data-type="account"
       data-target="#account-form-modal" rel="tooltip">
        <i class="fa fa-usd"></i><span id="user-balance"><?php echo $balance; ?> </span> <span class="l10n l10n-text" data-l10n="refill_account"></span>
    </a>
</li>
<li>
    <a type="button" class="btn btn-link" data-toggle="modal" data-type="task"
       data-target="#task-form-modal" id="create-task-button">
        <i class="fa fa-plus" aria-hidden="true"></i> <span class="l10n l10n-text" data-l10n="create_task"></span>
    </a>
</li>
<div id="task-form-modal" class="modal fade" data-type="task" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center"><span class="l10n l10n-text" data-l10n="create_task"></span></h1>
            </div>
            <div class="modal-body col-md-12">
                <!--suppress HtmlUnknownTarget -->
                <form id="task-form" class="form col-md-12 center-block" action="/api/v1/task">
                    <div class="form-group">
                        <label for="task-<?php echo DESCRIPTION ?>" class="l10n l10n-text" data-l10n="description"></label>
                        <textarea id="task-<?php echo DESCRIPTION ?>" class="form-control input-lg l10n l10n-placeholder" data-l10n="description_placeholder"
                                  placeholder="Description" name="<?php echo DESCRIPTION ?>" rows="3"
                                  maxlength="<?php echo get_config_max_task_description_length() ?>"></textarea>
                        <span id="task-form-error-<?php echo DESCRIPTION ?>" class="error-description"></span>
                        <span id="task-form-error-unspecified" class="error-description"></span>
                    </div>
                    <div class="form-group">
                        <label for="task-<?php echo AMOUNT ?>" class="l10n l10n-text" data-l10n="price"></label>
                        <input id="task-<?php echo AMOUNT ?>" type="number" class="form-control input-lg l10n l10n-placeholder" data-l10n="price_placeholder"
                               placeholder="Price" name="<?php echo AMOUNT ?>" min="1"
                               max="<?php echo get_config_max_amount() ?>">
                        <span id="task-form-error-<?php echo AMOUNT ?>" class="error-description"></span>
                    </div>
                    <input type="hidden" name="csrf_token">
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block">
                            <i class="fa fa-plus" aria-hidden="true"
                               id="task-form-spinner" data-icon="fa fa-plus"></i> <span class="l10n l10n-text" data-l10n="create_task"></span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-md-12">
                    <span class="l10n l10n-text" data-l10n="commission"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="task-unpaid-modal" class="modal fade center-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header col-md-12">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center l10n l10n-text" data-l10n="unpaid_notice_header"></h1>
            </div>
            <div class="modal-body col-md-12 l10n l10n-text" data-l10n="unpaid_notice_body"></div>
            <div class="modal-footer">
                <div class="col-md-12">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="task-not-enough-money-modal" class="modal fade center-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header col-md-12">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center l10n l10n-text" data-l10n="not_enough_money_notice_header"></h1>
            </div>
            <div class="modal-body col-md-12 l10n l10n-text" data-l10n="not_enough_money_notice_body"></div>
            <div class="modal-footer">
                <div class="col-md-12">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="task-already-paid-modal" class="modal fade center-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header col-md-12">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center l10n l10n-text" data-l10n="already_paid_notice_header"></h1>
            </div>
            <div class="modal-body col-md-12 l10n l10n-text" data-l10n="already_paid_notice_body"></div>
            <div class="modal-footer">
                <div class="col-md-12">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="account-form-modal" class="modal fade" data-type="account" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center"><span class="l10n l10n-text" data-l10n="refill_account"></span></h1>
            </div>
            <div class="modal-body col-md-12">
                <!--suppress HtmlUnknownTarget -->
                <form id="account-form" class="form col-md-12 center-block" action="/api/v1/account">
                    <div class="form-group">
                        <label for="account-<?php echo AMOUNT ?>" class="l10n l10n-text" data-l10n="amount"></label>
                        <input id="account-<?php echo AMOUNT ?>" type="number" class="form-control input-lg l10n l10n-placeholder" data-l10n="amount"
                               placeholder="Amount" name="<?php echo AMOUNT ?>" min="1"
                               max="<?php echo get_config_max_amount() ?>">
                        <span id="account-form-error-<?php echo AMOUNT ?>" class="error-description"></span>
                        <span id="account-form-error-token" class="error-description"></span>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block">
                            <i class="fa fa-usd" aria-hidden="true"
                               id="account-form-spinner" data-icon="fa fa-usd"></i> <span class="l10n l10n-text" data-l10n="refill"></span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-md-12">
                </div>
            </div>
        </div>
    </div>
</div>