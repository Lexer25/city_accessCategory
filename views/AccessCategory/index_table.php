<!-- Отображение сообщений -->
<?php 
$message = Session::instance()->get_once('message');
$message_type = Session::instance()->get_once('message_type', 'info');
if ($message): 
?>
    <div class="alert alert-<?php echo htmlspecialchars($message_type, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<?php if(isset($acList) && count($acList) > 0): ?>
    <div class="table-responsive">
        <table id="categoriesTable" class="table table-striped table-hover table-condensed table-bordered">
            <thead>
                <tr>
                    <th width="5%" data-sort="0">ID <span class="glyphicon glyphicon-sort"></span></th>
                    <th width="25%" data-sort="1"><?php echo __('Название категории'); ?> <span class="glyphicon glyphicon-sort"></span></th>
                    <th width="15%" data-sort="2"><?php echo __('Дата создания'); ?> <span class="glyphicon glyphicon-sort"></span></th>
                    <th width="25%" data-sort="3"><?php echo __('Контактов'); ?> <span class="glyphicon glyphicon-sort"></span></th>
                    <th width="20%"><?php echo __('Точки прохода'); ?></th>
                    <th width="10%"><?php echo __('Действия'); ?></th>
                </tr>
                <tr class="active">
                    <th><input type="text" id="filterId" class="form-control input-sm" placeholder="<?php echo __('Поиск по ID...'); ?>"></th>
                    <th><input type="text" id="filterName" class="form-control input-sm" placeholder="<?php echo __('Поиск по названию...'); ?>"></th>
                    <th><input type="text" id="filterDate" class="form-control input-sm" placeholder="<?php echo __('Поиск по дате...'); ?>"></th>
                    <th><input type="text" id="filterGuid" class="form-control input-sm" placeholder="<?php echo __('Поиск...'); ?>"></th>
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
                        $pointsList[] = htmlspecialchars(Arr::get($point, 'name'), ENT_QUOTES, 'UTF-8');
                    }
                    $pointsText = implode(' ', $pointsList);
                ?>
                    <tr data-id="<?php echo htmlspecialchars(Arr::get($category, 'id_accessname'), ENT_QUOTES, 'UTF-8'); ?>"
                        data-name="<?php echo htmlspecialchars(Arr::get($category, 'name'), ENT_QUOTES, 'UTF-8'); ?>"
                        data-date="<?php echo htmlspecialchars(Arr::get($category, 'time_stamp'), ENT_QUOTES, 'UTF-8'); ?>"
                        data-guid="<?php echo htmlspecialchars(Arr::get($category, 'guid'), ENT_QUOTES, 'UTF-8'); ?>"
                        data-points="<?php echo strtolower($pointsText); ?>">
                        <td><?php echo htmlspecialchars(Arr::get($category, 'id_accessname'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(Arr::get($category, 'name'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(Arr::get($category, 'time_stamp'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(Arr::get($category, 'peoplecount'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php if(count($accessPoints) > 0): ?>
                                <div class="dropdown">
                                    <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                                        <?php echo __('Точки прохода'); ?> (<?php echo count($accessPoints); ?>)
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($accessPoints as $point): ?>
                                            <li><a href="<?php echo URL::site('door/doorInfo/' . rawurlencode(Arr::get($point, 'id_dev'))); ?>"><?php echo htmlspecialchars(Arr::get($point, 'name'), ENT_QUOTES, 'UTF-8'); ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <span class="text-muted"><?php echo __('Нет точек прохода'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-xs">
                                <a href="<?php echo URL::site('accessCategory/edit/' . rawurlencode(Arr::get($category, 'id_accessname'))); ?>" class="btn btn-primary" title="<?php echo __('Редактировать'); ?>">
                                    <span class="glyphicon glyphicon-edit"></span>
                                </a>
                                <?php if ($is_admin): ?>
                                    <a href="<?php echo URL::site('accessCategory/delete/' . rawurlencode(Arr::get($category, 'id_accessname'))); ?>" class="btn btn-danger" onclick="return confirm('<?php echo addslashes(__('Вы уверены?')); ?>')">
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
    // ---------- Функция фильтрации (существующая) ----------
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
        // Обновляем счётчик видимых записей
        $("#totalCount").text(visibleCount);
    }
    
    // ---------- Функции сортировки (новый блок) ----------
    var sortState = {
        column: null,   // индекс столбца (0,1,2,3)
        order: 'asc'    // 'asc' или 'desc'
    };

    // Сортировка строк таблицы по указанному столбцу и порядку
    function sortTable(columnIndex, order) {
        var $tbody = $('#categoriesTable tbody');
        var rows = $tbody.find('tr').get(); // массив DOM-элементов

        rows.sort(function(a, b) {
            var valA = $(a).find('td:eq(' + columnIndex + ')').text().trim();
            var valB = $(b).find('td:eq(' + columnIndex + ')').text().trim();

            // Для числовых столбцов (ID и Контактов) — числовое сравнение
            var numA = parseFloat(valA.replace(/,/g, ''));
            var numB = parseFloat(valB.replace(/,/g, ''));
            if (!isNaN(numA) && !isNaN(numB)) {
                return order === 'asc' ? numA - numB : numB - numA;
            }

            // Для строковых столбцов (Название, Дата) — регистронезависимое сравнение
            valA = valA.toLowerCase();
            valB = valB.toLowerCase();
            if (order === 'asc') {
                return valA > valB ? 1 : (valA < valB ? -1 : 0);
            } else {
                return valA < valB ? 1 : (valA > valB ? -1 : 0);
            }
        });

        // Переупорядочиваем строки в tbody
        $.each(rows, function(index, row) {
            $tbody.append(row);
        });

        // Обновляем иконки сортировки в заголовках
        updateSortIcons(columnIndex, order);
    }

    // Обновление иконок glyphicon в заголовках
    function updateSortIcons(columnIndex, order) {
        // Сбрасываем все иконки в заголовках (кроме двух последних)
        $('#categoriesTable thead th:not(:nth-child(5)):not(:nth-child(6))').find('.glyphicon')
            .removeClass('glyphicon-sort-by-attributes glyphicon-sort-by-attributes-alt')
            .addClass('glyphicon-sort');

        // Устанавливаем иконку для текущего столбца
        var $header = $('#categoriesTable thead th:eq(' + columnIndex + ')');
        var $icon = $header.find('.glyphicon');
        $icon.removeClass('glyphicon-sort');
        if (order === 'asc') {
            $icon.addClass('glyphicon-sort-by-attributes');
        } else {
            $icon.addClass('glyphicon-sort-by-attributes-alt');
        }
    }

    // Обработчик клика по заголовкам (кроме столбцов "Точки прохода" и "Действия")
    $('#categoriesTable thead th:not(:nth-child(5)):not(:nth-child(6))').on('click', function() {
        var columnIndex = $(this).data('sort'); // используем data-sort, чтобы получить индекс
        if (columnIndex === undefined) {
            // на случай, если data-sort не задан, берём индекс
            columnIndex = $(this).index();
        }

        // Определяем порядок сортировки: если клик по тому же столбцу, переключаем
        var order = 'asc';
        if (sortState.column === columnIndex && sortState.order === 'asc') {
            order = 'desc';
        }

        // Сохраняем состояние
        sortState.column = columnIndex;
        sortState.order = order;

        // Сортируем
        sortTable(columnIndex, order);

        // После сортировки применяем фильтры, чтобы скрыть строки, не соответствующие поиску
        applyFilters();
    });

    // ---------- Сброс фильтров и сортировки ----------
    $("#resetFilters").on("click", function() {
        // Очищаем поля фильтров
        $("#filterId, #filterName, #filterDate, #filterGuid, #filterPoints").val("");
        // Сбрасываем сортировку к исходной (по ID по возрастанию)
        sortState.column = 0;
        sortState.order = 'asc';
        sortTable(0, 'asc');
        // Применяем фильтры (они теперь все пустые, покажут все строки)
        applyFilters();
    });

    // ---------- Фильтрация с debounce (существующий код) ----------
    var debounceTimer;
    $("#filterId, #filterName, #filterDate, #filterGuid, #filterPoints").on("keyup", function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            // Применяем фильтры, но сохраняем текущую сортировку
            applyFilters();
        }, 300);
    });

    // ---------- Инициализация: применяем сортировку по умолчанию (ID asc) ----------
    // Это нужно, чтобы изначально строки были упорядочены, как при загрузке (если сервер уже отсортировал, можно пропустить)
    // Но для единообразия можно задать начальное состояние
    sortState.column = 0;
    sortState.order = 'asc';
    // Сортируем один раз при загрузке, чтобы иконка была установлена
    sortTable(0, 'asc');
    // Применяем фильтры (показываем всё)
    applyFilters();
});
</script>