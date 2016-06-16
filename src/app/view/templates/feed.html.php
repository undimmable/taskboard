<div class="col-lg-8 col-md-10 col-sm-12 col-xs-12 col-lg-offset-2 col-md-offset-1 col-sm-offset-0 col-xs-offset-0">
    <div class="feed-wrapper">
        <div class="panel panel-info">
            <div class="panel-heading text-center">
                <h4 class="feed-header"><span class="l10n l10n-text" data-l10n="task_feed"></span></h4>
                <?php if (is_customer(get_authorized_user()[ROLE])) {
                    echo "<span class=\"pull-right\">
                <h5 class=\"feed-header hide-completed\">
                    <label>
                        <span class=\"l10n l10n-text\" data-l10n=\"hide_completed\"></span>
                        <input type=\"checkbox\" id=\"hide-completed\" checked>
                    </label>
                    </h5>
                </span>";
                } ?>
            </div>
            <div class="panel-body">
                <div id="new-items-badge" class="container row new-items-badge">
                    <span id="new-items-counter" class="l10n l10n-text" data-l10n="new_items"></span></div>
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