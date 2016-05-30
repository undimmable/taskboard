<?php
global $user;
global $current_task;
$task_class = !is_task_active($current_task) ? ' task-inactive' : (is_task_completed($current_task) ? ' task-completed' : ' task-active');
$current_task_id = $current_task[ID];
$current_task_img = get_task_img($current_task, $user);
$current_task_description = htmlspecialchars($current_task[DESCRIPTION], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5 | ENT_DISALLOWED, 'UTF-8', false);
$current_task_ts_offset = $current_task[CREATED_AT_OFFSET];
$current_task_price = $current_task[AMOUNT];
$customer_name = 'John Customer';
$user_customer = is_customer($user[ROLE]);
$user_performer = is_performer($user[ROLE]);
if ($user_customer) {
    if (!is_task_active($current_task)) {
        $task_unpaid = "<span class=\"task-header$task_class\">Unpaid</span>";
    }
    $strong = "<strong class='task-header$task_class'>\$$current_task_price $task_unpaid</strong>";
} elseif ($user_performer) {
    $strong = "<strong class='task-header'>$customer_name</strong>";
} else {
    $strong = "<strong class='task-header'>$customer_name <h4 class=\"system-price text-\">\$$current_task_price</h4></strong>";
}
?>


<li class="task-feed-item media<?php echo $task_class; ?>" data-id="<?php echo $current_task_id ?>">
    <a href="#" class="pull-left">
        <img class="avatar-img" src="<?php echo $current_task_img ?>">
    </a>
    <div class="row media-body task-item">
        <div class="col-md-9">
            <?php echo $strong ?>
            <p class="task-description"><?php echo $current_task_description ?></p>
        </div>
        <div class="col-md-3">
            <small class="text-muted pull-right">
                <span class="timestamp created_at"
                      data-timestamp-offset="<?php echo $current_task_ts_offset ?>"></span>
            </small>
            <?php if (is_performer($user[ROLE])) {
                echo '<button type="button" class="perform-task btn btn-primary pull-right no-shadow">$' . $current_task_price . '</button>';
            }
            ?>
        </div>
    </div>
    <?php if (is_customer($user[ROLE])) {
        if (!is_task_completed($current_task)) {
            echo '<button type="button" class="delete-task btn-link pull-right no-shadow" data-csrf="10">Delete</button>';
            if (!is_task_active($current_task)) {
                echo '<button type="button" class="fix-task btn-link pull-right no-shadow" data-csrf="10">Try again</button>';
            }
        } else {
            echo '<span class="pull-right ">Completed</span>';
        }
    }
    ?>
</li>