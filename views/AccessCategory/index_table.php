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

<?php if(isset($acList) && count($acList) > 0): ?>
    <div class="table-responsive">
        <table id="categoriesTable" class="table table-striped table-hover table-condensed table-bordered">
            <thead>
                <tr>
                    <th width="5%">ID <span class="glyphicon glyphicon-sort"></span></th>
                    <th width="25%"><?php echo __('Название категории'); ?> <span class="glyphicon glyphicon-sort"></span></th>
                    <th width="15%"><?php echo __('Дата создания'); ?> <span class="glyphicon glyphicon-sort"></span></th>
                    <th width="25%"><?php echo __('GUID'); ?> <span class="glyphicon glyphicon-sort"></span></th>
                    <th width="20%"><?php echo __('Точки прохода'); ?></th>
                    <th width="10%"><?php echo __('Действия'); ?></th>
                </tr>
                <tr class="active">
                    <th><input type="text" id="filterId" class="form-control input-sm" placeholder="<?php echo __('Поиск по ID...'); ?>"></th>
                    <th><input type="text" id="filterName" class="form-control input-sm" placeholder="<?php echo __('Поиск по названию...'); ?>"></th>
                    <th><input type="text" id="filterDate" class="form-control input-sm" placeholder="<?php echo __('Поиск по дате...'); ?>"></th>
                    <th><input type="text" id="filterGuid" class="form-control input-sm" placeholder="<?php echo __('Поиск по GUID...'); ?>"></th>
                    <th><input type="text" id="filterPoints" class="form-control input-sm" placeholder="<?php echo __('Поиск по точкам прохода...'); ?>"></th>
                    <th><button type="button" id="resetFilters" class="btn btn-default btn-sm btn-block" title="<?php echo __('Сбросить фильтры'); ?>"><span class="glyphicon glyphicon-refresh"></span></button></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($acList as $category): 
                    $accessPoints = Model::factory('AccessCategory')->groupByDevice(Model::factory('accessCategory')->getAccessPointsByCategoryId(Arr::get($category, 'id_accessname')));
                    $pointsText = '';
                    $pointsList = array();
                    foreach ($accessPoints as $point) {
                        $pointsList[] = htmlspecialchars(Arr::get($point, 'name'));
                    }
                    $pointsText = implode(' ', $pointsList);
                ?>
                    <tr data-id="<?php echo htmlspecialchars(Arr::get($category, 'id_accessname')); ?>"
                        data-name="<?php echo htmlspecialchars(Arr::get($category, 'name')); ?>"
                        data-date="<?php echo htmlspecialchars(Arr::get($category, 'time_stamp')); ?>"
                        data-guid="<?php echo htmlspecialchars(Arr::get($category, 'guid')); ?>"
                        data-points="<?php echo strtolower($pointsText); ?>">
                        <td><?php echo htmlspecialchars(Arr::get($category, 'id_accessname')); ?></td>
                        <td><?php echo htmlspecialchars(Arr::get($category, 'name')); ?></td>
                        <td><?php echo htmlspecialchars(Arr::get($category, 'time_stamp')); ?></td>
                        <td><?php echo htmlspecialchars(Arr::get($category, 'guid')); ?></td>
                        <td>
                            <?php if(count($accessPoints) > 0): ?>
                                <div class="dropdown">
                                    <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                                        <?php echo __('Точки прохода'); ?> (<?php echo count($accessPoints); ?>)
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($accessPoints as $point): ?>
                                            <li><a href="<?php echo URL::site('door/doorInfo/' . Arr::get($point, 'id_dev')); ?>"><?php echo htmlspecialchars(Arr::get($point, 'name')); ?></a></li>
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
                                <?php if ($is_admin): ?>
                                    <a href="<?php echo URL::site('accessCategory/delete/' . Arr::get($category, 'id_accessname')); ?>" class="btn btn-danger" onclick="return confirm('<?php echo __('Вы уверены?'); ?>')">
                                        <span class="glyphicon glyphicon-trash"></span>
                                    </a>
                                <?php else: ?>
                                    <span class="btn btn-danger disabled" title="<?php echo __('Доступно только администраторам'); ?>">
                                        <span class="glyphicon glyphicon-trash"></span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="row" style="margin-top: 10px;">
        <div class="col-xs-6">
            <small class="text-muted">Всего категорий: <span id="totalCount"><?php echo count($acList); ?></span></small>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center"><?php echo __('Нет доступных категорий доступа'); ?></div>
<?php endif; ?>

<script>
$(document).ready(function() {
    function applyFilters() {
        var idFilter = $("#filterId").val().toLowerCase().trim();
        var nameFilter = $("#filterName").val().toLowerCase().trim();
        var dateFilter = $("#filterDate").val().toLowerCase().trim();
        var guidFilter = $("#filterGuid").val().toLowerCase().trim();
        var pointsFilter = $("#filterPoints").val().toLowerCase().trim();
        
        var visibleCount = 0;
        $("#categoriesTable tbody tr").each(function() {
            var $row = $(this);
            var id = $row.find("td:eq(0)").text().toLowerCase();
            var name = $row.find("td:eq(1)").text().toLowerCase();
            var date = $row.find("td:eq(2)").text().toLowerCase();
            var guid = $row.find("td:eq(3)").text().toLowerCase();
            var points = $row.attr("data-points") || "";
            
            var show = true;
            if (idFilter && id.indexOf(idFilter) === -1) show = false;
            if (nameFilter && name.indexOf(nameFilter) === -1) show = false;
            if (dateFilter && date.indexOf(dateFilter) === -1) show = false;
            if (guidFilter && guid.indexOf(guidFilter) === -1) show = false;
            if (pointsFilter && points.indexOf(pointsFilter) === -1) show = false;
            
            if (show) { $row.show(); visibleCount++; }
            else { $row.hide(); }
        });
    }
    
    var debounceTimer;
    $("#filterId, #filterName, #filterDate, #filterGuid, #filterPoints").on("keyup", function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, 300);
    });
    $("#resetFilters").on("click", function() {
        $("#filterId, #filterName, #filterDate, #filterGuid, #filterPoints").val("");
        applyFilters();
    });
});
</script>