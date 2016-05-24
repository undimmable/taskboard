<div id="task-form-modal" class="modal fade" data-type="task" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h1 class="text-center">Create Task</h1>
            </div>
            <div class="modal-body col-md-12">
                <!--suppress HtmlUnknownTarget -->
                <form id="task-form" class="form col-md-12 center-block" action="/api/v1/task">
                    <div class="form-group">
                        <label for="task-<?php echo DESCRIPTION ?>">Description</label>
                        <textarea id="task-<?php echo DESCRIPTION ?>" class="form-control input-lg"
                                  placeholder="Description" name="<?php echo DESCRIPTION ?>" rows="3"
                                  maxlength="<?php echo get_config_max_task_description_length() ?>"></textarea>
                        <span id="task-form-error-<?php echo DESCRIPTION ?>" class="error-description"></span>
                    </div>
                    <div class="form-group">
                        <label for="task-<?php echo AMOUNT ?>">Price</label>
                        <input id="task-<?php echo AMOUNT ?>" type="number" class="form-control input-lg"
                               placeholder="Price" name="<?php echo AMOUNT ?>" min="1"
                               max="<?php echo get_config_max_amount() ?>">
                        <span id="task-form-error-<?php echo AMOUNT ?>" class="error-description"></span>
                    </div>
                    <input type="hidden" name="csrf_token">
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block">
                            <i class="glyphicon glyphicon-plus" aria-hidden="true"
                               id="task-form-spinner" data-icon="glyphicon glyphicon-plus"></i> Create Task
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-md-12">
                </div>
            </div>
        </div>
    </div>
</div>