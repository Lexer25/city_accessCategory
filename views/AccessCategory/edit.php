<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Редактирование категории доступа'); ?></h3>
    </div>
    <div class="panel-body">
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo URL::site('accessCategory/edit/' . Arr::get($category, 'id_accessname')); ?>">
            <div class="form-group">
                <label for="id"><?php echo __('ID'); ?></label>
                <input type="text" class="form-control" id="id" value="<?php echo htmlspecialchars(Arr::get($category, 'id_accessname')); ?>" disabled>
            </div>
            
            <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                <label for="name"><?php echo __('Название категории'); ?> *</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo isset($post['name']) ? htmlspecialchars($post['name']) : htmlspecialchars(Arr::get($category, 'name')); ?>" 
                       required>
                <?php if (isset($errors['name'])): ?>
                    <span class="help-block"><?php echo $errors['name']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="guid"><?php echo __('GUID'); ?></label>
                <input type="text" class="form-control" id="guid" name="guid" 
                       value="<?php echo isset($post['guid']) ? htmlspecialchars($post['guid']) : htmlspecialchars(Arr::get($category, 'guid')); ?>">
                <small class="form-text text-muted"><?php echo __('Оставьте пустым для автоматической генерации'); ?></small>
            </div>
            
            <div class="form-group">
                <label for="time_stamp"><?php echo __('Дата создания'); ?></label>
                <input type="text" class="form-control" id="time_stamp" value="<?php echo htmlspecialchars(Arr::get($category, 'time_stamp')); ?>" disabled>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?php echo __('Сохранить'); ?></button>
                <a href="<?php echo URL::site('accessCategory'); ?>" class="btn btn-default"><?php echo __('Отмена'); ?></a>
            </div>
        </form>
        
    </div>
</div>