<div id="signup-form-modal" class="modal fade" data-type="signup" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center"><span class="l10n l10n-text" data-l10n="signup"></span></h1>
            </div>
            <div class="modal-body col-md-12">
                <!--suppress HtmlUnknownTarget -->
                <form id="signup-form" class="form col-md-12 center-block" action="/api/v1/auth/signup">
                    <div id="signup-<?php echo EMAIL ?>" class="form-group">
                        <label for="signup-<?php echo EMAIL ?>" class="l10n l10n-text" data-l10n="email">Email</label>
                        <input type="email" class="form-control input-lg l10n l10n-placeholder" placeholder="Email" name="<?php echo EMAIL ?>" data-l10n="email"
                               id="signup-<?php echo EMAIL ?>">
                        <span id="signup-form-error-<?php echo EMAIL ?>" class="error-description"></span>
                        <span id="login-form-error-unspecified" class="error-description"></span>
                    </div>
                    <div class="form-group">
                        <label for="signup-<?php echo PASSWORD ?>" class="l10n l10n-text" data-l10n="password">Password</label>
                        <input type="password" class="form-control input-lg l10n l10n-placeholder" placeholder="Password" data-l10n="password"
                               name="<?php echo PASSWORD ?>" id="signup-<?php echo PASSWORD ?>">
                        <span id="signup-form-error-<?php echo PASSWORD ?>" class="error-description"></span>
                    </div>
                    <div class="form-group">
                        <label for="signup-<?php echo PASSWORD_REPEAT ?>" class="l10n l10n-text" data-l10n="password_repeat">Repeat Password</label>
                        <input type="password" class="form-control input-lg l10n l10n-placeholder" placeholder="Password" data-l10n="password_repeat"
                               name="<?php echo PASSWORD_REPEAT ?>" id="signup-<?php echo PASSWORD_REPEAT ?>">
                        <span id="signup-form-error-<?php echo PASSWORD_REPEAT ?>" class="error-description"></span>
                    </div>
                    <div class="form-group center-block pull-right">
                        <label>
                            <input type="checkbox" id="toggler" data-size="large" data-width="250px"
                                   name="<?php echo IS_CUSTOMER ?>" data-onstyle="success" data-offstyle="success"
                                   class="btn btn-primary btn-lg l10n l10n-toggler" data-l10n="performer_customer">
                        </label>
                    </div>
                    <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo get_signup_csrf(); ?>"/>
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block btn-default">
                            <i class="glyphicon glyphicon-menu-up" aria-hidden="true"
                               id="signup-form-spinner" data-icon="glyphicon glyphicon-log-in glyphicon-rotate"></i>
                            <span class="l10n l10n-text" data-l10n="signup"></span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>