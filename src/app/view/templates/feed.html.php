<div class="col-lg-8 col-md-10 col-sm-12 col-xs-12 col-lg-offset-2 col-md-offset-1 col-sm-offset-0 col-xs-offset-0">
    <div class="feed-wrapper">
        <div class="panel panel-info">
            <div class="panel-heading">
                <span class="l10n l10n-text" data-l10n="task_feed"></span>
                <?php if (is_customer(get_current_user()[ROLE])) {
                    echo "<span class=\"pull-right\">
                    <label>
                        Hide completed
                        <input type=\"checkbox\" id=\"hide-completed\">
                    </label>
                </span>";
                } ?>
            </div>
            <div class="panel-body">
                <ul id="task-feed" class="media-list">
                </ul>
            </div>
            <div id="loading" class="panel-footer" hidden>
                <span class="l10n l10n-text" data-l10n="loading"></span>
            </div>
            <div id="no-more-content" class="panel-footer" hidden>
                <span class="l10n l10n-text" data-l10n="no_more_content"></span>
            </div>
        </div>
    </div>
</div>