<div id="login-form-modal" class="modal fade" data-type="login" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center">Login</h1>
            </div>
            <div class="modal-body col-md-12">
                <!--suppress HtmlUnknownTarget -->
                <form id="login-form" class="form col-md-12 center-block" action="/api/v1/auth/login">
                    <div class="form-group">
                        <label for="login-<?php echo EMAIL ?>">Email</label>
                        <input id="login-<?php echo EMAIL ?>" type="email" class="form-control input-lg"
                               placeholder="Email" name="<?php echo EMAIL ?>">
                        <span id="login-form-error-<?php echo EMAIL ?>" class="error-description"></span>
                    </div>
                    <div class="form-group">
                        <label for="login-<?php echo PASSWORD ?>">Password</label>
                        <input id="login-<?php echo PASSWORD ?>" type="password" class="form-control input-lg"
                               placeholder="Password" name="<?php echo PASSWORD ?>">
                        <span id="login-form-error-<?php echo PASSWORD ?>" class="error-description"></span>
                    </div>
                    <label><input name="remember_me" type="checkbox"> Remember me</label>
                    <input type="hidden" name="csrf_token">
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block">
                            <i class="glyphicon glyphicon-log-in" aria-hidden="true"
                               id="login-form-spinner" data-icon="glyphicon glyphicon-log-in"></i> Sign In
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