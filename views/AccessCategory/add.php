<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Добавление категории доступа'); ?></h3>
    </div>
    <div class="panel-body">
        <form method="POST" action="<?php echo URL::site('accessCategory/save'); ?>">
            <div class="form-group">
                <label><?php echo __('Название категории'); ?></label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label><?php echo __('GUID'); ?></label>
                <input type="text" name="guid" class="form-control">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?php echo __('Сохранить'); ?></button>
                <a href="<?php echo URL::site('accessCategory'); ?>" class="btn btn-default"><?php echo __('Отмена'); ?></a>
            </div>
        </form>
    </div>
</div>