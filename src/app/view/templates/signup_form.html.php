<div id="signupModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center">Sign Up</h1>
            </div>
            <div class="modal-body col-md-12">
                <!--suppress HtmlUnknownTarget -->
                <form id="signup_form" class="form col-md-12 center-block" action="/api/v1/auth/signup">
                    <div id="signup_<?php echo EMAIL ?>" class="form-group">
                        <label for="signup_<?php echo EMAIL ?>">Email</label>
                        <input type="email" class="form-control input-lg" placeholder="Email" name="<?php echo EMAIL ?>"
                               id="signup_<?php echo EMAIL ?>">
                        <span id="signup_form_error_<?php echo EMAIL ?>" class="error-description"></span>
                    </div>
                    <div class="form-group">
                        <label for="signup_<?php echo PASSWORD ?>">Password</label>
                        <input type="password" class="form-control input-lg" placeholder="Password"
                               name="<?php echo PASSWORD ?>" id="signup_<?php echo PASSWORD ?>">
                        <span id="signup_form_error_<?php echo PASSWORD ?>" class="error-description"></span>
                    </div>
                    <div class="form-group">
                        <label for="signup_<?php echo PASSWORD_REPEAT ?>">Repeat Password</label>
                        <input type="password" class="form-control input-lg" placeholder="Password"
                               name="<?php echo PASSWORD_REPEAT ?>" id="signup_<?php echo PASSWORD_REPEAT ?>">
                        <span id="signup_form_error_<?php echo PASSWORD_REPEAT ?>" class="error-description"></span>
                    </div>
                    <div class="form-group center-block pull-right">
                        <label>
                            <input type="checkbox" data-toggle="toggle" data-on="I'm a customer"
                                   data-off="I'm a performer" data-size="large" data-width="250px" data-height="63px"
                                   name="<?php echo IS_CUSTOMER ?>" data-onstyle="success" data-offstyle="success"
                                   class="btn btn-primary btn-lg">
                        </label>
                    </div>
                    <input type="hidden" id="csrf_token" name="csrf_token"/>
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block btn-default">
                            <i class="glyphicon glyphicon-log-in glyphicon-rotate glyphicon-refresh" aria-hidden="true"
                               id="signup_form_spinner"></i> Sign Up
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-md-12">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>