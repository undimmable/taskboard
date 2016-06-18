<?php
$balance = get_balance(get_authorized_user()[ID]);
?>
<li>
    <a type="button" class="btn btn-link" href="#"><i class="fa fa-usd"></i><span id="user-balance"><?php echo $balance; ?></span>
    </a>
</li>

<div id="task-already-performed-modal" class="modal fade center-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header col-md-12">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center l10n l10n-text" data-l10n="already_performed_notice_header"></h1>
            </div>
            <div class="modal-body col-md-12 l10n l10n-text" data-l10n="already_performed_notice_body"></div>
            <div class="modal-footer">
                <div class="col-md-12">
                </div>
            </div>
        </div>
    </div>
</div>