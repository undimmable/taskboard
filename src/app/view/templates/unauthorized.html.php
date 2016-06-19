<?php
if (!get_authorized_user()) {
    $GLOBALS['reset_csrf'] = get_reset_password_csrf();
    if (array_key_exists("verification_token", $_GET)) {
        $verification_token = htmlspecialchars($_GET['verification_token'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5 | ENT_DISALLOWED, 'UTF-8', true);
        require_once 'reset_password.html.php';
    }
}
?>
<div id="email-sent-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header col-md-12">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center l10n l10n-text" data-l10n="email_sent_notice_header"></h1>
            </div>
            <div class="modal-body col-md-12 l10n l10n-text" data-l10n="email_sent_notice_body"></div>
            <div class="modal-footer">
                <div class="col-md-12">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="reset-form-modal" class="modal fade" data-type="" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center"><span class="l10n l10n-text" data-l10n=""></span></h1>
            </div>
            <div class="modal-body col-md-12">
                <!--suppress HtmlUnknownTarget -->
                <form id="reset-form" class="form col-md-12 center-block" action="/api/v1/auth/reset_password">
                    <div id="reset-<?php echo EMAIL ?>" class="form-group">
                        <label for="reset-<?php echo EMAIL ?>" class="l10n l10n-text" data-l10n="email">Email</label>
                        <input type="email" class="form-control input-lg l10n l10n-placeholder" placeholder="Email"
                               name="<?php echo EMAIL ?>" data-l10n="email"
                               id="reset-<?php echo EMAIL ?>" autofocus>
                        <span id="reset-form-error-<?php echo EMAIL ?>" class="error-description"></span>
                        <span id="reset-form-error-unspecified" class="error-description"></span>
                    </div>
                    <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $GLOBALS['reset_csrf']; ?>"/>
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block btn-default">
                            <i class="fa fa-key" aria-hidden="true"
                               id="reset-form-spinner" data-icon="fa fa-key"></i>
                            <span class="l10n l10n-text" data-l10n="reset_password"></span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
<div class="col-lg-8 col-md-10 col-sm-12 col-xs-12 col-lg-offset-2 col-md-offset-1 col-sm-offset-0 col-xs-offset-0">
    <div class="feed-wrapper">
        <div class="panel panel-info">
            <div class="panel-heading text-center">
                <span class="l10n l10n-text" data-l10n="task_feed"></span>
            </div>
            <div class="panel-body">
                <ul id="task-feed" class="media-list">
                    <li class="task-feed-item media task-active">
                        <a href="#" class="pull-left">
                            <img class="avatar-img" src="/img/w.png">
                        </a>
                        <div class="row media-body task-item">
                            <div class="col-lg-9 col-md-9 col-sm-9 col-xs-8 pull-left">
                                <strong class="task-header">Jean Customer</strong>
                                <p class="task-description">In ornare venenatis nibh nec eleifend. Vestibulum eget
                                    vulputate lacus. Sed ultricies dolor felis, id dapibus lorem feugiat a. In cursus
                                    auctor lectus, facilisis hendrerit ligula volutpat sit amet. Sed sagittis venenatis
                                    tortor quis placerat. Proin ac orci ac eros ultrices venenatis in eget justo. Donec
                                    blandit ipsum sed nibh pretium porta sed in justo.
                                </p>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 pull-right">
                                <small class="text-muted pull-right">
                                    <span class="created_at  l10n  l10n-text" data-l10n="hour_ago">about an hour ago</span>
                                </small>
                                <button type="button" class="perform-task btn btn-primary pull-right no-shadow"
                                        data-toggle="modal" data-target="#signup-form-modal">
                                    <i class="fa fa-usd"></i>100.00
                                </button>
                            </div>
                        </div>
                    </li>
                    <li class="task-feed-item media task-active">
                        <a href="#" class="pull-left">
                            <img class="avatar-img" src="/img/w.png">
                        </a>
                        <div class="row media-body task-item">
                            <div class="col-lg-9 col-md-9 col-sm-9 col-xs-8 pull-left">
                                <strong class="task-header">Jean Customer</strong>
                                <p class="task-description">Mauris nunc justo, feugiat vitae viverra non, gravida ut
                                    tortor. Proin tempus leo sem, vel rutrum purus tincidunt id. Integer at eros
                                    eleifend, aliquet lectus quis, feugiat turpis.</p>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 pull-right">
                                <small class="text-muted pull-right">
                                    <span class="created_at  l10n  l10n-text" data-l10n="hour_ago">about an hour ago</span>
                                </small>
                                <button type="button" class="perform-task btn btn-primary pull-right no-shadow"
                                        data-toggle="modal" data-target="#signup-form-modal"><i class="fa fa-usd"></i>50.00
                                </button>
                            </div>
                        </div>
                    </li>
                    <li class="task-feed-item media task-active">
                        <a href="#" class="pull-left">
                            <img class="avatar-img" src="/img/w.png">
                        </a>
                        <div class="row media-body task-item">
                            <div class="col-lg-9 col-md-9 col-sm-9 col-xs-8 pull-left">
                                <strong class="task-header">Jean Customer</strong>
                                <p class="task-description">Praesent scelerisque scelerisque ornare. Duis fermentum
                                    vehicula nulla, ac convallis mi bibendum nec. Integer sapien nibh, laoreet sed
                                    placerat eget, tincidunt eget mauris. Phasellus mauris velit, sagittis tincidunt
                                    molestie eu, dignissim et ex.
                                </p>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 pull-right">
                                <small class="text-muted pull-right">
                                    <span class="created_at l10n  l10n-text" data-l10n="hour_ago">about an hour ago</span>
                                </small>
                                <button type="button" class="perform-task btn btn-primary pull-right no-shadow"
                                        data-toggle="modal" data-target="#signup-form-modal">
                                    <i class="fa fa-usd"></i>12.00
                                </button>
                            </div>
                        </div>
                    </li>
                    <li class="task-feed-item media task-active">
                        <a href="#" class="pull-left">
                            <img class="avatar-img" src="/img/w.png">
                        </a>
                        <div class="row media-body task-item">
                            <div class="col-lg-9 col-md-9 col-sm-9 col-xs-8 pull-left">
                                <strong class="task-header">Jean Customer</strong>
                                <p class="task-description">Fusce tristique nibh nisi, ac ullamcorper massa suscipit at.
                                </p>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 pull-right">
                                <small class="text-muted pull-right">
                                    <span class="created_at  l10n l10n-text" data-l10n="hour_ago">about an hour ago</span>
                                </small>
                                <button type="button" class="perform-task btn btn-primary pull-right no-shadow"
                                        data-toggle="modal" data-target="#signup-form-modal">
                                    <i class="fa fa-usd"></i>10.00
                                </button>
                            </div>
                        </div>
                    </li>
                    <li class="task-feed-item media task-active">
                        <a href="#" class="pull-left">
                            <img class="avatar-img" src="/img/w.png">
                        </a>
                        <div class="row media-body task-item">
                            <div class="col-lg-9 col-md-9 col-sm-9 col-xs-8 pull-left">
                                <strong class="task-header">Jean Customer</strong>
                                <p class="task-description">Vivamus euismod bibendum lorem vitae imperdiet. Nam luctus
                                    urna sed quam ornare suscipit. Fusce ut consequat velit. In consectetur mi leo, sit
                                    amet ultricies nunc dignissim in. Cum sociis natoque penatibus et magnis dis
                                    parturient montes, nascetur ridiculus mus. Vestibulum iaculis gravida fringilla.
                                    Nullam laoreet pretium eros et rhoncus.</p>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 pull-right">
                                <small class="text-muted pull-right">
                                    <span class="created_at  l10n  l10n-text" data-l10n="hour_ago">about an hour ago</span>
                                </small>
                                <button type="button" class="perform-task btn btn-primary pull-right no-shadow"
                                        data-toggle="modal" data-target="#signup-form-modal">
                                    <i class="fa fa-usd"></i>12.00
                                </button>
                            </div>
                        </div>
                    </li>
                    <li class="task-feed-item media task-active" data-id="23">
                        <a href="#" class="pull-left">
                            <img class="avatar-img" src="/img/w.png">
                        </a>
                        <div class="row media-body task-item">
                            <div class="col-lg-9 col-md-9 col-sm-9 col-xs-8 pull-left">
                                <strong class="task-header">Jean Customer</strong>
                                <p class="task-description">Vivamus dictum, velit eu tempor bibendum, leo augue lacinia
                                    massa, id convallis nulla orci a justo. Nulla facilisi. Ut pellentesque sapien at
                                    odio dapibus, quis posuere turpis rutrum.
                                </p>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3 pull-right">
                                <small class="text-muted pull-right">
                                    <span class="created_at  l10n  l10n-text" data-l10n="hour_ago">about an hour ago</span>
                                </small>
                                <button type="button" class="perform-task btn btn-primary pull-right no-shadow"
                                        data-toggle="modal" data-target="#signup-form-modal"><i class="fa fa-usd"></i>2.00
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div id="loading" class="panel-footer" hidden>
                <span class="l10n l10n-text" data-l10n="loading"></span>
            </div>
            <div id="no-more-content" class="panel-footer">
                <span class="l10n l10n-text" data-l10n="no_more_content"></span>
            </div>
        </div>
    </div>
</div>
