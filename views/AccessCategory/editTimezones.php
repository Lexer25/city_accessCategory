<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php echo __('Редактирование временных зон'); ?>
        </h3>
    </div>
    <div class="panel-body">
        
        <!-- Отображение сообщений -->
        <?php 
        $message = Session::instance()->get_once('message');
        $message_type = Session::instance()->get_once('message_type', 'info');
        if ($message): 
        ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?php echo __('Информация'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label><?php echo __('Категория доступа'); ?>:</label>
                            <p class="form-control-static">
                                <strong><?php echo htmlspecialchars(Arr::get($category, 'name')); ?></strong> 
                                (ID: <?php echo $categoryId; ?>)
                            </p>
                        </div>
                        
                        <div class="form-group">
                            <label><?php echo __('Точка прохода'); ?>:</label>
                            <p class="form-control-static">
                                <strong><?php echo htmlspecialchars(Arr::get($device, 'name')); ?></strong> 
                                (ID: <?php echo $deviceId; ?>)
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?php echo __('Доступные временные зоны'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <form method="POST" action="<?php echo URL::site('accessCategory/editTimezones/' . $categoryId . '/' . $deviceId); ?>">
                            <div class="form-group">
                                <label><?php echo __('Выберите временные зоны'); ?></label>
                                <div class="well" style="max-height: 300px; overflow-y: auto;">
                                    <?php if(count($allTimezones) > 0): ?>
                                        <?php foreach ($allTimezones as $tz): ?>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="timezones[]" 
                                                           value="<?php echo htmlspecialchars(Arr::get($tz, 'id_timezone')); ?>"
                                                           <?php echo in_array(Arr::get($tz, 'id_timezone'), $selectedTimezones) ? 'checked' : ''; ?>
                                                           <?php echo $is_admin ? '' : 'disabled title="' . __('Доступно только администраторам') . '"'; ?>>
                                                    <?php echo htmlspecialchars(Arr::get($tz, 'name')); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted"><?php echo __('Нет доступных временных зон'); ?></p>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo __('Можно выбрать несколько временных зон'); ?>
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <?php if ($is_admin): ?>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="glyphicon glyphicon-save"></span> <?php echo __('Сохранить'); ?>
                                    </button>
                                    <a href="<?php echo URL::site('accessCategory/edit/' . $categoryId); ?>" class="btn btn-default">
                                        <span class="glyphicon glyphicon-ban-circle"></span> <?php echo __('Отмена'); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="btn btn-primary disabled" title="<?php echo __('Доступно только администраторам'); ?>">
                                        <span class="glyphicon glyphicon-save"></span> <?php echo __('Сохранить'); ?>
                                    </span>
                                    <a href="<?php echo URL::site('accessCategory/edit/' . $categoryId); ?>" class="btn btn-default">
                                        <span class="glyphicon glyphicon-ban-circle"></span> <?php echo __('Отмена'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!$is_admin): ?>
            <div class="alert alert-info text-center" style="margin-top: 15px;">
                <span class="glyphicon glyphicon-lock"></span> 
                <?php echo __('Режим только для просмотра. Для редактирования временных зон необходимы права администратора.'); ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>