<?php
$change_pass_csrf = get_change_password_csrf();
?>

<div id="password-changed-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header col-md-12">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center l10n l10n-text" data-l10n="password_changed_notice_header"></h1>
            </div>
            <div class="modal-body col-md-12 l10n l10n-text" data-l10n="password_changed_notice_body"></div>
            <div class="modal-footer">
                <div class="col-md-12">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="change-password-form-modal" class="modal fade active" data-type="change-password" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center"><span class="l10n l10n-text" data-l10n="change_password"></span></h1>
            </div>
            <div class="modal-body col-md-12">
                <!--suppress HtmlUnknownTarget -->
                <form id="change-password-form" class="form col-md-12 center-block" action="/api/v1/auth/change_password">
                    <div class="form-group">
                        <label for="change-password-<?php echo PASSWORD ?>" class="l10n l10n-text" data-l10n="password">Password</label>
                        <input type="password" class="form-control input-lg l10n l10n-placeholder" placeholder="Password" data-l10n="password"
                               name="<?php echo PASSWORD ?>" id="change-password-<?php echo PASSWORD ?>">
                        <span id="change-password-form-error-<?php echo PASSWORD ?>" class="error-description"></span>
                        <span id="change-password-form-error-unspecified" class="error-description"></span>
                    </div>
                    <div class="form-group">
                        <label for="change-password-<?php echo PASSWORD_REPEAT ?>" class="l10n l10n-text" data-l10n="password_repeat">Repeat Password</label>
                        <input type="password" class="form-control input-lg l10n l10n-placeholder" placeholder="Password" data-l10n="password_repeat"
                               name="<?php echo PASSWORD_REPEAT ?>" id="change-password-<?php echo PASSWORD_REPEAT ?>">
                        <span id="change-password-form-error-<?php echo PASSWORD_REPEAT ?>" class="error-description"></span>
                    </div>
                    <input type="hidden" id="csrf_token" name="csrf_token"
                           value="<?php echo $change_pass_csrf ?>"/>
                    <input type="hidden" id="verification_token" name="verification_token"
                           value="<?php echo $verification_token ?>"/>
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block btn-default">
                            <i class="fa fa-key" aria-hidden="true"
                               id="change-password-form-spinner" data-icon="fa fa-key"></i>
                            <span class="l10n l10n-text" data-l10n="change_password"></span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
