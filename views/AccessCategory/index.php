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
                            <th>
                                <input type="text" id="filterId" class="form-control input-sm" placeholder="<?php echo __('Поиск по ID...'); ?>">
                            </th>
                            <th>
                                <input type="text" id="filterName" class="form-control input-sm" placeholder="<?php echo __('Поиск по названию...'); ?>">
                            </th>
                            <th>
                                <input type="text" id="filterDate" class="form-control input-sm" placeholder="<?php echo __('Поиск по дате...'); ?>">
                            </th>
                            <th>
                                <input type="text" id="filterGuid" class="form-control input-sm" placeholder="<?php echo __('Поиск по GUID...'); ?>">
                            </th>
                            <th>
                                <input type="text" id="filterPoints" class="form-control input-sm" placeholder="<?php echo __('Поиск по точкам прохода...'); ?>">
                            </th>
                            <th>
                                <button type="button" id="resetFilters" class="btn btn-default btn-sm btn-block" title="<?php echo __('Сбросить фильтры'); ?>">
                                    <span class="glyphicon glyphicon-refresh"></span>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($acList as $key => $category): 
                            //$accessPoints = Model::factory('accessCategory')->getAccessPointsByCategoryId(Arr::get($category, 'id_accessname'));
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
                                <!-- Точки прохода -->
                                <td>
                                    <?php if(count($accessPoints) > 0): ?>
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
                        <span class="glyphicon glyphicon-dashboard"></span> <?php echo __('Всего категорий'); ?>: <span id="totalCount"><?php echo count($acList); ?></span>
                        <span id="filterInfo" style="display: none;">
                            , <span class="glyphicon glyphicon-filter"></span> <?php echo __('Показано'); ?>: <span id="filteredCount">0</span>
                        </span>
                    </small>
                </div>
                <div class="col-xs-6 text-right">
                    <small class="text-muted">
                        <span class="glyphicon glyphicon-info-sign"></span> <?php echo __('Кликните на заголовок для сортировки'); ?>
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

<script type="text/javascript">
    $(document).ready(function() {
        // Функция фильтрации по всем полям
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
                
                var showRow = true;
                
                if (idFilter && id.indexOf(idFilter) === -1) showRow = false;
                if (nameFilter && name.indexOf(nameFilter) === -1) showRow = false;
                if (dateFilter && date.indexOf(dateFilter) === -1) showRow = false;
                if (guidFilter && guid.indexOf(guidFilter) === -1) showRow = false;
                if (pointsFilter && points.indexOf(pointsFilter) === -1) showRow = false;
                
                if (showRow) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });
            
            // Обновление информации о фильтрации
            var total = $("#categoriesTable tbody tr").length;
            if (idFilter || nameFilter || dateFilter || guidFilter || pointsFilter) {
                $("#filterInfo").show();
                $("#filteredCount").text(visibleCount);
            } else {
                $("#filterInfo").hide();
            }
            
            // Показываем сообщение, если ничего не найдено
            if (visibleCount === 0) {
                if ($("#noDataMessage").length === 0) {
                    $("#categoriesTable tbody").append('<tr id="noDataMessage"><td colspan="6" class="text-center text-muted" style="padding: 30px;"><span class="glyphicon glyphicon-search"></span> <?php echo __('Ничего не найдено'); ?></td></tr>');
                }
            } else {
                $("#noDataMessage").remove();
            }
        }
        
        // Применяем фильтры при вводе с задержкой
        var debounceTimer;
        function debounceApplyFilters() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(applyFilters, 300);
        }
        
        // Назначаем обработчики событий
        $("#filterId, #filterName, #filterDate, #filterGuid, #filterPoints").on("keyup", debounceApplyFilters);
        $("#filterId, #filterName, #filterDate, #filterGuid, #filterPoints").on("change", applyFilters);
        
        // Сброс всех фильтров
        $("#resetFilters").on("click", function() {
            $("#filterId, #filterName, #filterDate, #filterGuid, #filterPoints").val("");
            applyFilters();
            
            $(this).removeClass("btn-default").addClass("btn-success");
            setTimeout(function() {
                $("#resetFilters").removeClass("btn-success").addClass("btn-default");
            }, 300);
        });
        
        // Сортировка таблицы
        var sortOrder = {};
        $("#categoriesTable thead tr:first th").on("click", function() {
            var $table = $("#categoriesTable");
            var index = $(this).index();
            if (index === 4 || index === 5) return; // Не сортируем колонки "Точки прохода" и "Действия"
            
            var rows = $table.find("tbody tr:visible").get();
            var currentOrder = sortOrder[index] || 'asc';
            
            rows.sort(function(a, b) {
                var aVal = $(a).find("td:eq(" + index + ")").text().toLowerCase();
                var bVal = $(b).find("td:eq(" + index + ")").text().toLowerCase();
                
                if (currentOrder === 'asc') {
                    return aVal.localeCompare(bVal);
                } else {
                    return bVal.localeCompare(aVal);
                }
            });
            
            sortOrder[index] = currentOrder === 'asc' ? 'desc' : 'asc';
            
            $.each(rows, function(i, row) {
                $table.children("tbody").append(row);
            });
            
            // Визуальная индикация сортировки
            $("#categoriesTable thead tr:first th").removeClass("active");
            $(this).addClass("active");
        });
        
        // Очистка фильтра по Escape
        $(document).on("keyup", function(e) {
            if (e.key === "Escape") {
                $("#filterId, #filterName, #filterDate, #filterGuid, #filterPoints").val("");
                applyFilters();
            }
        });
        
        // Подсказки для полей фильтрации
        $("#filterId").attr("title", "<?php echo __('Введите ID для поиска'); ?>");
        $("#filterName").attr("title", "<?php echo __('Введите название категории для поиска'); ?>");
        $("#filterDate").attr("title", "<?php echo __('Введите дату для поиска'); ?>");
        $("#filterGuid").attr("title", "<?php echo __('Введите GUID для поиска'); ?>");
        $("#filterPoints").attr("title", "<?php echo __('Введите название точки прохода для поиска'); ?>");
    });
</script>