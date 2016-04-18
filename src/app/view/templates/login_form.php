<div class="signin-form">
    <div class="container">
        <form class="form-signin" method="post" id="login-form">
            <h2 class="form-signin-heading">Log In to TaskBoard.</h2>
            <hr/>
            <div id="error"></div>
            <div class="form-group">
                <input type="hidden" id="back" name="back" value="<?php echo $redirect_back ?>"/>
                <input type="email" class="form-control" placeholder="Email address" name="user_email" id="user_email"/>
                <span id="check-e"></span>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" placeholder="Password" name="password" id="password"/>
            </div>
            <hr/>
            <div class="form-group">
                <button type="submit" class="btn btn-default" name="btn-login" id="btn-login">
                    <span class="glyphicon glyphicon-log-in"></span> &nbsp; Sign In
                </button>
            </div>
        </form>

    </div>

</div>