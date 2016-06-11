<?php
$balance = get_balance(get_authorized_user()[ID]);
?>
<li>
    <a type="button" class="btn btn-link" href="#">
        $<span id="user-balance"><?php echo $balance; ?></span>
    </a>
</li>