<ul class="nav navbar-nav navbar-right">
    <?php
    global $user;
    if (!is_authorized()) {
        require 'view/templates/login_buttons.html.php';
        require 'view/templates/login_form.html.php';
        require 'view/templates/signup_form.html.php';
    } else {
        if (is_customer($user[ROLE])) {
            require 'view/templates/customer_navbar.html.php';
        } else {
            require 'view/templates/user_navbar.html.php';
        }
        require 'view/templates/logout_button.html.php';
    }
    ?>
</ul>