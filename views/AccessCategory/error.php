<div class="panel panel-danger">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Доступ запрещен'); ?></h3>
    </div>
    <div class="panel-body text-center">
        <div class="alert alert-danger">
            <span class="glyphicon glyphicon-warning-sign" style="font-size: 48px; display: block; margin-bottom: 15px;"></span>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <a href="<?php echo URL::site($back_url); ?>" class="btn btn-primary">
            <span class="glyphicon glyphicon-arrow-left"></span> <?php echo __('Вернуться назад'); ?>
        </a>
    </div>
</div>