<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Категории доступа</h3>
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
                </div>
                <?php if ($is_admin): ?>
                    <a href="<?php echo URL::site('accessCategory/add'); ?>" class="btn btn-success" style="margin-left: 10px;">
                        <span class="glyphicon glyphicon-plus"></span> Добавить категорию
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Подключаем нужное представление -->
        <?php
        if ($view_mode == 'table') {
            echo View::factory('accessCategory/index_table', array('acList' => $acList));
        } else {
            echo View::factory('accessCategory/index_tree', array('acList' => $acList));
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