<?php
global $user;
?>
<li class="task-feed-item media" data-id="<?php /** @noinspection PhpUndefinedVariableInspection */
echo $task[ID] ?>">
    <div class="media-body task-item">
        <p class="task-description"><?php echo htmlspecialchars($task[DESCRIPTION], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5 | ENT_DISALLOWED, 'UTF-8', false) ?></p>
        <span class="task-price"><?php echo $task[AMOUNT] ?></span>
        <span class="timestamp created_at" data-timestamp-offset="<?php echo $task[CREATED_AT_OFFSET] ?>"></span>
        <?php if (is_customer($user[ROLE])) {
            echo '<button type="button" class="delete-task btn-danger">Delete</button>';
        } else if (is_performer($user[ROLE])) {
            echo '<button type="button" class="perform-task btn-primary">Perform</button>';
        }
        ?>
    </div>
</li>