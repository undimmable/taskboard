<ul class="nav navbar-nav navbar-right">
    <?php
    global $user;
    if (!is_authorized()) {
        require 'login_buttons.html.php';
        require 'login_form.html.php';
        require 'signup_form.html.php';
    } else {
        if (is_customer($user)) {
            require 'create_task_button.html.php';
            require 'create_task_form.html.php';
        }
        require 'logout_button.html.php';
    }
    ?>
</ul>