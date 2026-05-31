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
        
        <form method="POST" action="<?php echo URL::site('accessCategory/edit/' . Arr::get($category, 'id_accessname')); ?>" id="editForm">
            <div class="row">
                <div class="col-md-4">
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
                    
                    <!-- Информация о выбранных точках -->
                    <div class="panel panel-default" style="margin-top: 15px;">
                        <div class="panel-heading">
                            <h4 class="panel-title"><?php echo __('Информация'); ?></h4>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label><?php echo __('Выбрано точек прохода'); ?>:</label>
                                <h3><span id="selectedCount" class="label label-primary">0</span></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <?php echo __('Точки прохода'); ?>
                                <div class="btn-group pull-right">
                                    <button type="button" id="checkAll" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-check"></span> <?php echo __('Выбрать все'); ?>
                                    </button>
                                    <button type="button" id="uncheckAll" class="btn btn-xs btn-default">
                                        <span class="glyphicon glyphicon-unchecked"></span> <?php echo __('Снять все'); ?>
                                    </button>
                                </div>
                            </h4>
                        </div>
                        <div class="panel-body" style="padding: 0;">
                            <!-- Таблица точек прохода с фильтрацией в шапке -->
                            <div class="table-responsive">
                                <table id="pointsTable" class="table table-striped table-hover table-condensed table-bordered" style="margin-bottom: 0;">
                                    <thead>
                                        <tr class="active">
                                            <th width="5%" class="text-center">Выбор <span class="glyphicon glyphicon-sort"></span></th>
                                            <th width="8%">ID <span class="glyphicon glyphicon-sort"></span></th>
                                            <th width="40%"><?php echo __('Название точки прохода'); ?> <span class="glyphicon glyphicon-sort"></span></th>
                                            <th width="47%"><?php echo __('Временная зона'); ?> <span class="glyphicon glyphicon-sort"></span></th>
                                        </tr>
                                        <tr class="filter-row">
                                            <th class="text-center">
                                                <input type="checkbox" id="selectAllCheckbox" title="<?php echo __('Выбрать все на странице'); ?>" style="margin: 0;">
                                            </th>
                                            <th>
                                                <input type="text" id="filterId" class="form-control input-sm" placeholder="<?php echo __('Поиск по ID...'); ?>" style="width: 100%;">
                                            </th>
                                            <th>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" id="filterName" class="form-control" placeholder="<?php echo __('Поиск по названию...'); ?>">
                                                    <span class="input-group-btn">
                                                        <button type="button" id="clearSearch" class="btn btn-default" title="<?php echo __('Очистить поиск'); ?>">
                                                            <span class="glyphicon glyphicon-remove"></span>
                                                        </button>
                                                    </span>
                                                </div>
                                            </th>
                                            <th>
                                                <input type="text" id="filterTimezone" class="form-control input-sm" placeholder="<?php echo __('Поиск по временной зоне...'); ?>" style="width: 100%;">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($allPoints) > 0): ?>
                                            <?php foreach ($allPoints as $point): 
                                                // Получаем данные о timezone для этой точки
                                                $timezoneData = null;
                                                foreach ($timezonesData as $tz) {
                                                    if (Arr::get($tz, 'id_timezone') == Arr::get($point, 'id_timezone')) {
                                                        $timezoneData = $tz;
                                                        break;
                                                    }
                                                }
                                            ?>
                                                <tr data-id="<?php echo htmlspecialchars(Arr::get($point, 'id_dev')); ?>"
                                                    data-name="<?php echo htmlspecialchars(Arr::get($point, 'name')); ?>"
                                                    data-timezone-name="<?php echo htmlspecialchars(Arr::get($timezoneData, 'name')); ?>">
                                                    <td class="text-center">
                                                        <input type="checkbox" name="access_points[]" value="<?php echo htmlspecialchars(Arr::get($point, 'id_dev')); ?>"
                                                               class="point-checkbox"
                                                               <?php echo in_array(Arr::get($point, 'id_dev'), $assignedPoints) ? 'checked' : ''; ?>>
                                                      </td>
                                                      <td><?php echo htmlspecialchars(Arr::get($point, 'id_dev')); ?></td>
                                                      <td><?php echo htmlspecialchars(Arr::get($point, 'name')); ?></td>
                                                      <td>
                                                            <?php if($timezoneData): ?>
                                                                <div class="timezone-info">
                                                                    <strong><?php echo htmlspecialchars(Arr::get($timezoneData, 'name')); ?></strong>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <?php echo Arr::get($timezoneData, 'timestart'); ?> - <?php echo Arr::get($timezoneData, 'timeend'); ?>
                                                                    </small>
                                                                    <br>
                                                                    <small>
                                                                        <?php
                                                                        $flag = intval(Arr::get($timezoneData, 'flag'));
                                                                        $days = array();
                                                                        if ($flag & 1) $days[] = 'Пн';
                                                                        if ($flag & 2) $days[] = 'Вт';
                                                                        if ($flag & 4) $days[] = 'Ср';
                                                                        if ($flag & 8) $days[] = 'Чт';
                                                                        if ($flag & 16) $days[] = 'Пт';
                                                                        if ($flag & 32) $days[] = 'Сб';
                                                                        if ($flag & 64) $days[] = 'Вс';
                                                                        if ($flag & 128) $days[] = 'Праздники';
                                                                        if ($flag & 256) $days[] = 'Ночная';
                                                                        if ($flag & 512) $days[] = 'Круглосуточно';
                                                                        
                                                                        if (!empty($days)) {
                                                                            echo '<span class="label label-info">' . implode('</span> <span class="label label-info">', $days) . '</span>';
                                                                        } else {
                                                                            echo '<span class="text-muted">Нет дней</span>';
                                                                        }
                                                                        ?>
                                                                    </small>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted"><?php echo __('Нет временной зоны'); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                  </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr id="noDataRow">
                                                <td colspan="4" class="text-center text-muted">
                                                    <?php echo __('Нет доступных точек прохода'); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="active">
                                            <td colspan="4">
                                                <small class="text-muted">
                                                    <span class="glyphicon glyphicon-stats"></span> 
                                                    <?php echo __('Всего точек'); ?>: <span id="totalPoints"><?php echo count($allPoints); ?></span>
                                                    <span id="filterInfo" style="display: none;">
                                                        , <?php echo __('Показано'); ?>: <span id="filteredCount">0</span>
                                                    </span>
                                                </small>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <button type="submit" id="saveButton" class="btn btn-primary">
                    <span class="glyphicon glyphicon-save"></span> <?php echo __('Сохранить'); ?>
                </button>
                <a href="<?php echo URL::site('accessCategory'); ?>" class="btn btn-default">
                    <span class="glyphicon glyphicon-ban-circle"></span> <?php echo __('Отмена'); ?>
                </a>
            </div>
        </form>
        
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Инициализация счетчика выбранных элементов
        function updateSelectedCount() {
            var count = $("input.point-checkbox:checked").length;
            $("#selectedCount").text(count);
            $("#selectedCount").removeClass("label-primary label-success label-warning");
            if (count > 0) {
                $("#selectedCount").addClass("label-success");
            } else {
                $("#selectedCount").addClass("label-primary");
            }
        }
        
        // Подсчет выбранных при загрузке
        updateSelectedCount();
        
        // Отслеживание изменений чекбоксов
        $("input.point-checkbox").on("change", function() {
            updateSelectedCount();
            updateSelectAllCheckbox();
        });
        
        // Функция фильтрации таблицы
        function filterTable() {
            var idFilter = $("#filterId").val().toLowerCase().trim();
            var nameFilter = $("#filterName").val().toLowerCase().trim();
            var timezoneFilter = $("#filterTimezone").val().toLowerCase().trim();
            var visibleCount = 0;
            
            $("#pointsTable tbody tr").each(function() {
                var $row = $(this);
                var id = $row.attr("data-id").toLowerCase();
                var name = $row.attr("data-name").toLowerCase();
                var timezoneName = $row.attr("data-timezone-name") || "";
                
                var showRow = true;
                
                if (idFilter && id.indexOf(idFilter) === -1) showRow = false;
                if (nameFilter && name.indexOf(nameFilter) === -1) showRow = false;
                if (timezoneFilter && timezoneName.indexOf(timezoneFilter) === -1) showRow = false;
                
                if (showRow) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });
            
            // Обновление информации о фильтрации
            var total = $("#pointsTable tbody tr").length;
            if (idFilter || nameFilter || timezoneFilter) {
                $("#filterInfo").show();
                $("#filteredCount").text(visibleCount);
            } else {
                $("#filterInfo").hide();
            }
            
            // Обновляем состояние чекбокса "Выбрать все на странице"
            updateSelectAllCheckbox();
            
            // Показываем сообщение, если ничего не найдено
            if (visibleCount === 0 && total > 0) {
                if ($("#noFilterData").length === 0) {
                    $("#pointsTable tbody").append('<tr id="noFilterData"><td colspan="4" class="text-center text-muted"><span class="glyphicon glyphicon-search"></span> <?php echo __('Ничего не найдено'); ?></td></tr>');
                }
            } else {
                $("#noFilterData").remove();
            }
        }
        
        // Сортировка таблицы
        var sortOrder = {};
        $("#pointsTable thead tr:first th").on("click", function() {
            var index = $(this).index();
            var $table = $("#pointsTable");
            var rows = $table.find("tbody tr:visible").get();
            var currentOrder = sortOrder[index] || 'asc';
            
            rows.sort(function(a, b) {
                var aVal, bVal;
                
                if (index === 0) {
                    // Сортировка по чекбоксам
                    aVal = $(a).find("input.point-checkbox").prop("checked") ? 1 : 0;
                    bVal = $(b).find("input.point-checkbox").prop("checked") ? 1 : 0;
                } else if (index === 1) {
                    aVal = parseInt($(a).find("td:eq(" + index + ")").text()) || 0;
                    bVal = parseInt($(b).find("td:eq(" + index + ")").text()) || 0;
                } else if (index === 3) {
                    aVal = $(a).attr("data-timezone-name") || "";
                    bVal = $(b).attr("data-timezone-name") || "";
                    if (currentOrder === 'asc') {
                        return aVal.localeCompare(bVal);
                    } else {
                        return bVal.localeCompare(aVal);
                    }
                } else {
                    aVal = $(a).find("td:eq(" + index + ")").text().toLowerCase();
                    bVal = $(b).find("td:eq(" + index + ")").text().toLowerCase();
                }
                
                if (currentOrder === 'asc') {
                    if (aVal < bVal) return -1;
                    if (aVal > bVal) return 1;
                    return 0;
                } else {
                    if (aVal > bVal) return -1;
                    if (aVal < bVal) return 1;
                    return 0;
                }
            });
            
            sortOrder[index] = currentOrder === 'asc' ? 'desc' : 'asc';
            
            $.each(rows, function(i, row) {
                $table.children("tbody").append(row);
            });
            
            // Визуальная индикация сортировки
            $("#pointsTable thead tr:first th").removeClass("active");
            $(this).addClass("active");
        });
        
        // Фильтрация с задержкой
        var debounceTimer;
        function debounceFilter() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(filterTable, 300);
        }
        
        // Назначаем обработчики для полей фильтрации
        $("#filterId, #filterName, #filterTimezone").on("keyup", debounceFilter);
        $("#filterId, #filterName, #filterTimezone").on("change", filterTable);
        
        // Очистка поиска
        $("#clearSearch").on("click", function() {
            $("#filterName").val("");
            filterTable();
        });
        
        // Выбрать все видимые чекбоксы
        $("#checkAll").on("click", function() {
            $("#pointsTable tbody tr:visible .point-checkbox").prop("checked", true);
            updateSelectedCount();
            updateSelectAllCheckbox();
        });
        
        // Снять все видимые чекбоксы
        $("#uncheckAll").on("click", function() {
            $("#pointsTable tbody tr:visible .point-checkbox").prop("checked", false);
            updateSelectedCount();
            updateSelectAllCheckbox();
        });
        
        // Чекбокс "Выбрать все на странице"
        $("#selectAllCheckbox").on("change", function() {
            var isChecked = $(this).prop("checked");
            $("#pointsTable tbody tr:visible .point-checkbox").prop("checked", isChecked);
            updateSelectedCount();
        });
        
        // Обновление состояния чекбокса "Выбрать все на странице"
        function updateSelectAllCheckbox() {
            var visibleCheckboxes = $("#pointsTable tbody tr:visible .point-checkbox");
            var checkedVisible = visibleCheckboxes.filter(":checked").length;
            var allChecked = visibleCheckboxes.length === checkedVisible && visibleCheckboxes.length > 0;
            
            $("#selectAllCheckbox").prop("checked", allChecked);
        }
        
        // Подсветка строки при наведении
        $("#pointsTable tbody tr").hover(
            function() { $(this).addClass("info"); },
            function() { $(this).removeClass("info"); }
        );
        
        // Клик по строке таблицы для выбора чекбокса
        $("#pointsTable tbody tr").on("click", function(e) {
            if ($(e.target).is("input")) return;
            var $checkbox = $(this).find("input.point-checkbox");
            $checkbox.prop("checked", !$checkbox.prop("checked"));
            $checkbox.trigger("change");
        });
        
        // Предупреждение при уходе со страницы без сохранения
        var formChanged = false;
        $("#editForm input, #editForm select, #editForm textarea").on("change", function() {
            formChanged = true;
        });
        
        // Перехват отправки формы - показываем alert
        var saveConfirmed = false;
        
        $("#editForm").on("submit", function(e) {
            if (saveConfirmed) {
                return true;
            }
            
            e.preventDefault();
            
            alert("<?php echo __('Изменения набора точек прохода создаст очередь на запись/удаление идентификаторов в контроллеры.'); ?>");
            
            saveConfirmed = true;
            $(this).submit();
        });
        
        window.onbeforeunload = function() {
            if (formChanged && !saveConfirmed) {
                return '<?php echo __('Вы не сохранили изменения! Вы уверены, что хотите покинуть страницу?'); ?>';
            }
        };
        
        $(document).on("change", ".point-checkbox", function() {
            updateSelectAllCheckbox();
        });
    });
</script>

<style>
    #pointsTable {
        margin-bottom: 0;
    }
    
    #pointsTable th {
        cursor: pointer;
        user-select: none;
        background-color: #f5f5f5;
        vertical-align: middle;
    }
    
    #pointsTable th:hover {
        background-color: #e8e8e8;
    }
    
    #pointsTable th.active {
        background-color: #d9edf7;
    }
    
    #pointsTable th .glyphicon-sort {
        opacity: 0.3;
        margin-left: 5px;
    }
    
    #pointsTable th:hover .glyphicon-sort {
        opacity: 0.7;
    }
    
    .filter-row th {
        background-color: #fafafa !important;
        cursor: default !important;
        padding: 8px;
    }
    
    .filter-row th:hover {
        background-color: #fafafa !important;
    }
    
    .filter-row input {
        width: 100%;
    }
    
    #pointsTable tbody tr {
        transition: background-color 0.2s ease;
        cursor: pointer;
    }
    
    #pointsTable tbody tr.info {
        background-color: #d9edf7;
    }
    
    #pointsTable tbody tr:hover {
        background-color: #f5f5f5;
    }
    
    #selectAllCheckbox {
        cursor: pointer;
        margin: 0;
    }
    
    .point-checkbox {
        cursor: pointer;
    }
    
    #selectedCount {
        font-size: 24px;
        padding: 5px 15px;
    }
    
    tfoot td {
        background-color: #f9f9f9;
        padding: 8px;
    }
    
    #noFilterData td {
        padding: 30px;
    }
    
    .timezone-info {
        font-size: 12px;
    }
    
    .timezone-info .label {
        display: inline-block;
        margin: 1px;
        font-size: 10px;
    }
</style>