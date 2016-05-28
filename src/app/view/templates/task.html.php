<?php
global $user;
?>


<li class="task-feed-item media" data-id="<?php /** @noinspection PhpUndefinedVariableInspection */
echo $task[ID] ?>">
    <a href="#" class="pull-left">
        <img class="avatar-img" src="/img/404.jpg" alt="">
    </a>

    <div class="row media-body task-item">

        <div class="col-md-9">
            <strong>Customer Name</strong>
            <p class="task-description"><?php echo htmlspecialchars($task[DESCRIPTION], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5 | ENT_DISALLOWED, 'UTF-8', false) ?></p>
        </div>

        <div class="col-md-3">
            <small class="text-muted pull-right">
                <span class="timestamp created_at"
                      data-timestamp-offset="<?php echo $task[CREATED_AT_OFFSET] ?>"></span>
            </small>
                        <?php if (is_performer($user[ROLE])) {
                            echo '<button type="button" class="delete-task btn-primary btn pull-right no-shadow">$' . $task[AMOUNT]. '</button>';
                        }
                        ?>

        </div>
    </div>
<?php if (is_customer($user[ROLE])) {
    echo '<button type="button" class="delete-task btn-primary btn-sm btn-link pull-right">Delete</button>';
}
    ?>
</li>