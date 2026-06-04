<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-th-list"></span> 
            Категории доступа
        </h3>
    </div>
    <div class="panel-body">
        
        <!-- Переключатель режимов -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-xs-12">
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php echo $view_mode == 'table' ? 'active' : ''; ?>">
                        <input type="radio" name="view_mode" value="table" autocomplete="off" <?php echo $view_mode == 'table' ? 'checked' : ''; ?>> 
                        <span class="glyphicon glyphicon-th-list"></span> Таблица
                    </label>
                    <label class="btn btn-default <?php echo $view_mode == 'tree' ? 'active' : ''; ?>">
                        <input type="radio" name="view_mode" value="tree" autocomplete="off" <?php echo $view_mode == 'tree' ? 'checked' : ''; ?>> 
                        <span class="glyphicon glyphicon-tree-deciduous"></span> Дерево
                    </label>
                    <label class="btn btn-default <?php echo $view_mode == 'matrix' ? 'active' : ''; ?>">
                        <input type="radio" name="view_mode" value="matrix" autocomplete="off" <?php echo $view_mode == 'matrix' ? 'checked' : ''; ?>> 
                        <span class="glyphicon glyphicon-th"></span> Матрица
                    </label>
                </div>
                <?php if ($is_admin): ?>
                    <a href="<?php echo URL::site('accessCategory/add'); ?>" class="btn btn-success" style="margin-left: 10px;">
                        <span class="glyphicon glyphicon-plus"></span> Добавить категорию
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Сообщения -->
        <?php 
        $message = Session::instance()->get_once('message');
        $message_type = Session::instance()->get_once('message_type', 'info');
        if ($message): 
        ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Подключаем нужное представление с правильными переменными -->
        <?php
        if ($view_mode == 'table') {
            echo View::factory('accessCategory/index_table', array(
                'acList' => $acList,
                'is_admin' => $is_admin,
            ));
        } elseif ($view_mode == 'matrix') {
            // Для матрицы используем данные, переданные из контроллера
            $matrixAllPoints = isset($allPoints) ? $allPoints : array();
            $matrixCategories = isset($acList) ? $acList : array();
            $matrixCategoryPointsMap = isset($categoryPointsMap) ? $categoryPointsMap : array();
            
            echo View::factory('accessCategory/index_matrix', array(
                'allPoints' => $matrixAllPoints,
                'categories' => $matrixCategories,
                'categoryPointsMap' => $matrixCategoryPointsMap,
                'is_admin' => $is_admin,
            ));
        } else {
            // Дерево
            echo View::factory('accessCategory/index_tree', array(
                'acList' => $acList,
                'is_admin' => $is_admin,
            ));
        }
        ?>
        
    </div>
</div>

<script>
$(document).ready(function() {
    $('input[name="view_mode"]').on('change', function() {
        var mode = $(this).val();
        var url = new URL(window.location.href);
        url.searchParams.set('mode', mode);
        window.location.href = url.toString();
    });
});
</script>