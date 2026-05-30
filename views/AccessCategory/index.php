<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Категории доступа'); ?></h3>
    </div>
    <div class="panel-body">
        
        <?php if(isset($acList) && count($acList) > 0): ?>
            <table class="table table-striped table-hover table-condensed table-bordered tablesorter">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php echo __('Название категории'); ?></th>
                        <th><?php echo __('Дата создания'); ?></th>
                        <th><?php echo __('GUID'); ?></th>
                        <th><?php echo __('Действия'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($acList as $key => $category): ?>
                        <tr>
							<td><?php echo htmlspecialchars(Arr::get($category, 'id_accessname')); ?></td>
                            <td><?php echo htmlspecialchars(Arr::get($category, 'name')); ?></td>
                            <td><?php echo htmlspecialchars(Arr::get($category, 'time_stamp')); ?></td>
                            <td><?php echo htmlspecialchars(Arr::get($category, 'guid')); ?></td>
                            <td>
                                <a href="<?php echo URL::site('accessCategory/edit/' . Arr::get($category, 'id_accessname')); ?>" class="btn btn-xs btn-primary">
                                    <i class="fa fa-edit"></i> <?php echo __('Редактировать'); ?>
                                </a>
                                <a href="<?php echo URL::site('accessCategory/delete/' . Arr::get($category, 'id_accessname')); ?>" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Вы уверены?'); ?>')">
                                    <i class="fa fa-trash"></i> <?php echo __('Удалить'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">
                <?php echo __('Нет доступных категорий доступа'); ?>
            </div>
        <?php endif; ?>
        
        <!-- Кнопка для добавления новой категории -->
        <div class="form-group">
            <a href="<?php echo URL::site('accessCategory/add'); ?>" class="btn btn-success">
                <i class="fa fa-plus"></i> <?php echo __('Добавить категорию доступа'); ?>
            </a>
        </div>
        
    </div>
</div>

<!-- Подключение таблицы с сортировкой, если нужно -->
<script type="text/javascript">
    $(document).ready(function() {
        $(".tablesorter").tablesorter({
            sortList: [[0,0]],
            headers: { 
                4: { sorter: false } // Отключаем сортировку для колонки "Действия"
            }
        });
    });
</script>