<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <a href="#" class="pull-left navbar-brand logo"></a>
            <a class="navbar-brand" href="#">TaskBoards</a>
        </div>
        <div>
            <form class="navbar-form navbar-left">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Search">
                </div>
            </form>
            <?php if (!is_authorized()) add_login_buttons(); ?>
            <?php if (is_authorized()) add_logout_button(); ?>
        </div>
    </div>
</nav>
<?php if (!is_authorized()) add_login_form(); ?>
<?php if (!is_authorized()) add_signup_form(); ?>