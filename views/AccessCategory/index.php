<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Категории доступа'); ?></h3>
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
        
        <!-- Верхняя панель с кнопкой добавления -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-xs-12">
                <a href="<?php echo URL::site('accessCategory/add'); ?>" class="btn btn-success">
                    <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить категорию доступа'); ?>
                </a>
            </div>
        </div>
        
        <?php if(isset($acList) && count($acList) > 0): ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover table-condensed table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="25%"><?php echo __('Название категории'); ?></th>
                            <th width="15%"><?php echo __('Дата создания'); ?></th>
                            <th width="25%"><?php echo __('GUID'); ?></th>
                            <th width="30%"><?php echo __('Точки прохода'); ?></th>
                            <th width="10%"><?php echo __('Действия'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($acList as $key => $category): 
                            $accessPoints = Model::factory('accessCategory')->getAccessPointsByCategoryId(Arr::get($category, 'id_accessname'));
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars(Arr::get($category, 'id_accessname')); ?></td>
                                <td><?php echo htmlspecialchars(Arr::get($category, 'name')); ?></td>
                                <td><?php echo htmlspecialchars(Arr::get($category, 'time_stamp')); ?></td>
                                <td><?php echo htmlspecialchars(Arr::get($category, 'guid')); ?></td>
                               <!-- Вместо прямого отображения всех точек, используем выпадающий список -->
<td>
    <?php 
    $accessPoints = Model::factory('accessCategory')->getAccessPointsByCategoryId(Arr::get($category, 'id_accessname'));
    if(count($accessPoints) > 0): 
    ?>
        <div class="dropdown">
            <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                <?php echo __('Точки прохода'); ?> (<?php echo count($accessPoints); ?>)
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <?php foreach ($accessPoints as $point): ?>
                    <li>
                        <a href="<?php echo URL::site('door/doorInfo/' . Arr::get($point, 'id_dev')); ?>">
                            <?php echo htmlspecialchars(Arr::get($point, 'name')); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <span class="text-muted"><?php echo __('Нет точек прохода'); ?></span>
    <?php endif; ?>
</td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <a href="<?php echo URL::site('accessCategory/edit/' . Arr::get($category, 'id_accessname')); ?>" class="btn btn-primary" title="<?php echo __('Редактировать'); ?>">
                                            <span class="glyphicon glyphicon-edit"></span>
                                        </a>
                                        <a href="<?php echo URL::site('accessCategory/delete/' . Arr::get($category, 'id_accessname')); ?>" class="btn btn-danger" title="<?php echo __('Удалить'); ?>" onclick="return confirm('<?php echo __('Вы уверены, что хотите удалить эту категорию?'); ?>')">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                    </div>
                                 </td>
                             </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Информация о количестве записей -->
            <div class="row" style="margin-top: 10px;">
                <div class="col-xs-6">
                    <small class="text-muted">
                        <span class="glyphicon glyphicon-dashboard"></span> <?php echo __('Всего категорий'); ?>: <?php echo count($acList); ?>
                    </small>
                </div>
            </div>
            
            <!-- Нижняя панель с кнопкой добавления -->
            <div class="row" style="margin-top: 15px;">
                <div class="col-xs-12">
                    <a href="<?php echo URL::site('accessCategory/add'); ?>" class="btn btn-success">
                        <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить категорию доступа'); ?>
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <div class="alert alert-info text-center">
                <span class="glyphicon glyphicon-info-sign"></span> <?php echo __('Нет доступных категорий доступа'); ?>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <a href="<?php echo URL::site('accessCategory/add'); ?>" class="btn btn-success">
                    <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить категорию доступа'); ?>
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<style>
    .access-points-list {
        max-height: 100px;
        overflow-y: auto;
    }
    .label-info a:hover {
        text-decoration: underline !important;
    }
</style>