<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Добавление категории доступа'); ?></h3>
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
        
        <?php if ($is_admin): ?>
            <form method="POST" action="<?php echo URL::site('accessCategory/add'); ?>">
                <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                    <label for="name"><?php echo __('Название категории'); ?> *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo isset($post['name']) ? htmlspecialchars($post['name']) : ''; ?>" 
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <span class="help-block"><?php echo $errors['name']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="guid"><?php echo __('GUID'); ?></label>
                    <input type="text" class="form-control" id="guid" name="guid" 
                           value="<?php echo isset($post['guid']) ? htmlspecialchars($post['guid']) : ''; ?>">
                    <small class="form-text text-muted"><?php echo __('Оставьте пустым для автоматической генерации'); ?></small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><?php echo __('Добавить'); ?></button>
                    <a href="<?php echo URL::site('accessCategory'); ?>" class="btn btn-default"><?php echo __('Отмена'); ?></a>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <span class="glyphicon glyphicon-lock" style="font-size: 48px; display: block; margin-bottom: 15px;"></span>
                <h4><?php echo __('Доступ запрещен'); ?></h4>
                <p><?php echo __('Только администраторы могут добавлять категории доступа.'); ?></p>
                <a href="<?php echo URL::site('accessCategory'); ?>" class="btn btn-default">
                    <span class="glyphicon glyphicon-arrow-left"></span> <?php echo __('Вернуться к списку'); ?>
                </a>
            </div>
            
            <!-- Показываем форму в режиме только для чтения -->
            <div class="panel panel-default" style="margin-top: 15px;">
                <div class="panel-heading">
                    <h4 class="panel-title"><?php echo __('Предпросмотр формы'); ?></h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="name"><?php echo __('Название категории'); ?> *</label>
                        <input type="text" class="form-control" id="name" value="" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="guid"><?php echo __('GUID'); ?></label>
                        <input type="text" class="form-control" id="guid" value="" disabled>
                        <small class="form-text text-muted"><?php echo __('Оставьте пустым для автоматической генерации'); ?></small>
                    </div>
                    
                    <div class="form-group">
                        <span class="btn btn-primary disabled" title="<?php echo __('Доступно только администраторам'); ?>">
                            <?php echo __('Добавить'); ?>
                        </span>
                        <a href="<?php echo URL::site('accessCategory'); ?>" class="btn btn-default">
                            <?php echo __('Отмена'); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>