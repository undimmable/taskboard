<?php
$task_class = !is_task_active($GLOBALS['current_task']) ? ' task-inactive' : (is_task_completed($GLOBALS['current_task']) ? ' task-completed' : ' task-active');
$current_task_id = $GLOBALS['current_task'][ID];
$current_task_img = get_task_img($GLOBALS['current_task'], $user);
$current_task_description = htmlspecialchars($GLOBALS['current_task'][DESCRIPTION], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5 | ENT_DISALLOWED, 'UTF-8', true);
$current_task_ts_offset = $GLOBALS['current_task'][CREATED_AT_OFFSET];
$current_task_amount = $GLOBALS['current_task'][AMOUNT];
$current_task_price = $GLOBALS['current_task'][PRICE];
$customer_name = 'Jean Customer';
$user_customer = is_customer($user[ROLE]);
$user_performer = is_performer($user[ROLE]);
$task_unpaid_marker = "";
if ($user_customer) {
    if (!is_task_active($GLOBALS['current_task'])) {
        $task_unpaid = "<span data-l10n=\"unpaid\" class=\"l10n l10n-text task-header$task_class\">Unpaid</span>";
    }
    $strong = "<strong class='task-header$task_class'><i class=\"fa fa-usd\"></i>$current_task_amount $task_unpaid</strong>";
    $csrf = get_customer_task_csrf($user[ID], $GLOBALS['current_task'][ID]);
} elseif ($user_performer) {
    if(!$GLOBALS['current_task'][PAID] && $GLOBALS['current_task'][PERFORMER_ID])
        $task_unpaid_marker = " unpaid";
    $strong = "<strong class='task-header'>$customer_name</strong>";
    $csrf = get_performer_task_csrf($user[ID], $GLOBALS['current_task'][ID]);
} else {
    $strong = "<strong class='task-header'>$customer_name <h4 class=\"system-price text-\">\$$current_task_amount</h4></strong>";
    $csrf = null;
}
?>
<li class="task-feed-item media<?php echo $task_class . $task_unpaid_marker; ?>" data-id="<?php echo $current_task_id ?>">
    <a href="#" class="pull-left">
        <img class="avatar-img" src="<?php echo $current_task_img ?>">
    </a>
    <div class="row media-body task-item">
        <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pull-left">
            <?php echo $strong ?>
            <p class="task-description"><?php echo $current_task_description ?></p>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pull-right">
            <small class="text-muted pull-right">
                <span class="timestamp created_at"
                      data-timestamp-offset="<?php echo $current_task_ts_offset ?>"></span>
            </small>
            <?php if (is_performer($user[ROLE])) {
                echo '<button type="button" class="perform-task btn btn-primary pull-right no-shadow" data-csrf="' . $csrf . '"><i class="fa fa-usd" data-icon="fa fa-usd"></i>' . $current_task_price . '</button>';
            }
            ?>
        </div>
    </div>
    <?php if (is_customer($user[ROLE])) {
        if (!is_task_completed($GLOBALS['current_task'])) {
            if (!is_task_active($GLOBALS['current_task'])) {
                echo '<button type="button" data-l10n="delete_task" class="l10n l10n-text delete-task btn-link pull-right no-shadow" data-csrf="' . $csrf . '">Delete</button>';
                echo '<button type="button" data-l10n="fix_task" class="l10n l10n-text fix-task btn-link pull-right no-shadow" data-csrf="' . $csrf . '">Try again</button>';
            }
        } else {
            echo '<button class="pull-right l10n l10n-text btn-link no-shadow task-completed" data-l10n="task_completed" disabled>Completed</button>';
        }
    }
    ?>
</li>
