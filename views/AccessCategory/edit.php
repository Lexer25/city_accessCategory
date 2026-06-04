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
        
        <div class="row">
            <div class="col-md-4">
                <!-- Блок информации о категории -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?php echo __('Информация о категории'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="id"><?php echo __('ID'); ?></label>
                            <input type="text" class="form-control" id="id" value="<?php echo htmlspecialchars(Arr::get($category, 'id_accessname')); ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="name"><?php echo __('Название категории'); ?></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars(Arr::get($category, 'name')); ?>" 
                                   disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="guid"><?php echo __('GUID'); ?></label>
                            <input type="text" class="form-control" id="guid" name="guid" 
                                   value="<?php echo htmlspecialchars(Arr::get($category, 'guid')); ?>" 
                                   disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="time_stamp"><?php echo __('Дата создания'); ?></label>
                            <input type="text" class="form-control" id="time_stamp" value="<?php echo htmlspecialchars(Arr::get($category, 'time_stamp')); ?>" disabled>
                        </div>
                    </div>
                </div>
                
                <!-- Информация о выбранных точках -->
                <div class="panel panel-default" style="margin-top: 15px;">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?php echo __('Статистика'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label><?php echo __('Точек прохода в категории'); ?>:</label>
                            <h3><span id="selectedCount" class="label label-primary">0</span></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <!-- Блок для добавления новых точек -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <?php echo __('Добавить точки прохода'); ?>
                        </h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <select id="availablePointsSelect" class="form-control" multiple size="10" style="height: 200px;">
                                    <?php 
                                    // Используем уже готовые сгруппированные данные
                                    $assignedIds = array_keys($groupedDevices);
                                    
                                    foreach ($allPoints as $point): 
                                        if (!in_array(Arr::get($point, 'id_dev'), $assignedIds)):
                                    ?>
                                        <option value="<?php echo htmlspecialchars(Arr::get($point, 'id_dev')); ?>">
                                            [<?php echo htmlspecialchars(Arr::get($point, 'id_dev')); ?>] <?php echo htmlspecialchars(Arr::get($point, 'name')); ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </select>
                            </div>
							
                            <div class="col-md-4">
                                <button type="button" id="addSelectedPoints" class="btn btn-success btn-block" 
									<?php echo $is_admin ? '' : 'disabled title="' . __('Доступно только администраторам') . '"'; ?>>
									<span class="glyphicon glyphicon-arrow-right"></span> <?php echo __('Добавить выбранные'); ?>
								</button>
                            </div>
							
                        </div>
                        <div class="row" style="margin-top: 10px;">
                            <div class="col-md-12">
                                <small class="text-muted">
                                    <?php echo __('Выберите точки прохода (Ctrl+Click для множественного выбора)'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Блок с уже добавленными точками -->
                <div class="panel panel-default" style="margin-top: 15px;">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <?php echo __('Точки прохода в категории'); ?>
							
                            <div class="btn-group pull-right">
                                <button type="button" id="removeSelectedPoints" class="btn btn-xs btn-danger"
									<?php echo $is_admin ? '' : 'disabled title="' . __('Доступно только администраторам') . '"'; ?>>
									<span class="glyphicon glyphicon-remove"></span> <?php echo __('Удалить выбранные'); ?>
								</button>
                            </div>
							
                        </h4>
                    </div>
                    <div class="panel-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-condensed table-bordered" style="margin-bottom: 0;">
                                <thead>
                                    <tr class="active">
                                        <th width="5%"><input type="checkbox" id="selectAllAssigned"></th>
                                        <th width="8%"><?php echo __('ID'); ?></th>
                                        <th width="52%"><?php echo __('Название точки прохода'); ?></th>
                                        <th width="20%"><?php echo __('Временные зоны'); ?></th>
                                        <th width="15%"><?php echo __('Действия'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="assignedPointsBody">
                                    <?php 
                                    if(count($groupedDevices) > 0): 
                                        foreach ($groupedDevices as $devId => $deviceData): 
                                    ?>
                                        <tr data-id="<?php echo htmlspecialchars($devId); ?>">
                                            <td class="text-center">
                                                <input type="checkbox" class="assigned-checkbox" value="<?php echo htmlspecialchars($devId); ?>">
                                            </td>
                                            <td><?php echo htmlspecialchars($devId); ?></td>
                                            <td><?php echo htmlspecialchars($deviceData['name']); ?></td>
                                            <td>
                                                <?php 
                                                $timezoneIds = $deviceData['id_timezone'];
                                                if(!empty($timezoneIds) && is_array($timezoneIds)): 
                                                    foreach ($timezoneIds as $tzId): 
                                                        $tzName = isset($timezonesMap[$tzId]) ? $timezonesMap[$tzId] : $tzId;
                                                ?>
                                                    <span class="label label-info" style="margin-right: 3px; display: inline-block; margin-bottom: 2px;">
                                                        <?php echo htmlspecialchars($tzName); ?>
                                                    </span>
                                                <?php 
                                                    endforeach; 
                                                else: 
                                                ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo URL::site('accessCategory/editTimezones/' . Arr::get($category, 'id_accessname') . '/' . $devId); ?>" 
                                                   class="btn btn-xs btn-primary" 
                                                   title="<?php echo __('Редактировать временные зоны'); ?>">
                                                    <span class="glyphicon glyphicon-time"></span> <?php echo __('Врем. зоны'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                        endforeach; 
                                    else: 
                                    ?>
                                        <tr id="noAssignedDataRow">
                                            <td colspan="5" class="text-center text-muted">
                                                <?php echo __('Нет точек прохода в этой категории'); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Массив для хранения ID выбранных точек
        var selectedPoints = [];
        
        // Инициализация массива существующими точками
        <?php 
        foreach ($groupedDevices as $devId => $deviceData): 
        ?>
            selectedPoints.push(Number(<?php echo json_encode($devId); ?>));
        <?php endforeach; ?>
        
        // Функция обновления счетчика
        function updateSelectedCount() {
            $("#selectedCount").text(selectedPoints.length);
        }
        
        // Функция обновления таблицы с добавленными точками
        function updateAssignedTable() {
            var $tbody = $("#assignedPointsBody");
            $tbody.empty();
            
            if (selectedPoints.length === 0) {
                $tbody.append('<tr id="noAssignedDataRow"><td colspan="5" class="text-center text-muted"><?php echo __('Нет точек прохода в этой категории'); ?></td></tr>');
                updateSelectedCount();
                return;
            }
            
            // Получаем данные о точках из существующего массива allPoints с временными зонами
            var pointsData = {};
            <?php foreach ($allPoints as $point): 
                // Находим временные зоны для этой точки
                $tzList = array();
                foreach ($groupedDevices as $devId => $deviceData) {
                    if ($devId == Arr::get($point, 'id_dev')) {
                        $tzIds = $deviceData['id_timezone'];
                        if (is_array($tzIds)) {
                            foreach ($tzIds as $tzId) {
                                $tzName = isset($timezonesMap[$tzId]) ? $timezonesMap[$tzId] : $tzId;
                                $tzList[] = htmlspecialchars($tzName);
                            }
                        }
                        break;
                    }
                }
            ?>
                pointsData[Number(<?php echo json_encode(Arr::get($point, 'id_dev')); ?>)] = {
                    id: Number(<?php echo json_encode(Arr::get($point, 'id_dev')); ?>),
                    name: <?php echo json_encode(htmlspecialchars(Arr::get($point, 'name'))); ?>,
                    timezones: <?php echo json_encode($tzList); ?>
                };
            <?php endforeach; ?>
            
            // Добавляем точки из selectedPoints
            for (var i = 0; i < selectedPoints.length; i++) {
                var pointId = selectedPoints[i];
                var point = pointsData[pointId];
                if (point) {
                    // Формируем HTML для временных зон
                    var timezonesHtml = '';
                    if (point.timezones && point.timezones.length > 0) {
                        for (var tzIdx = 0; tzIdx < point.timezones.length; tzIdx++) {
                            timezonesHtml += '<span class="label label-info" style="margin-right: 3px; display: inline-block; margin-bottom: 2px;">' + 
                                             point.timezones[tzIdx] + 
                                             '</span>';
                        }
                    } else {
                        timezonesHtml = '<span class="text-muted">—</span>';
                    }
                    
                    var $row = $('<tr>');
                    $row.attr('data-id', point.id);
                    $row.html('<td class="text-center"><input type="checkbox" class="assigned-checkbox" value="' + point.id + '"></td>' +
                              '<td>' + point.id + '</td>' +
                              '<td>' + point.name + '</td>' +
                              '<td>' + timezonesHtml + '</td>' +
                              '<td><a href="<?php echo URL::site('accessCategory/editTimezones/' . Arr::get($category, 'id_accessname')); ?>/' + point.id + '" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-time"></span> <?php echo __('Врем. зоны'); ?></a></td>');
                    $tbody.append($row);
                }
            }
            
            updateSelectedCount();
        }
        
        // Функция обновления списка доступных точек
        function updateAvailablePoints() {
            var $select = $("#availablePointsSelect");
            $select.empty();
            
            <?php foreach ($allPoints as $point): ?>
                var pointId = Number(<?php echo json_encode(Arr::get($point, 'id_dev')); ?>);
                var pointName = <?php echo json_encode(htmlspecialchars(Arr::get($point, 'name'))); ?>;
                
                if (selectedPoints.indexOf(pointId) === -1) {
                    $select.append('<option value="' + pointId + '">[' + pointId + '] ' + pointName + '</option>');
                }
            <?php endforeach; ?>
        }
        
        // Добавление выбранных точек
        $("#addSelectedPoints").on("click", function() {
            var selectedOptions = $("#availablePointsSelect option:selected");
            var added = false;
            
            if (selectedOptions.length === 0) {
                alert("<?php echo __('Не выбраны точки для добавления'); ?>");
                return;
            }
            
            // Собираем ID выбранных точек
            var pointsToAdd = [];
            selectedOptions.each(function() {
                var pointId = Number($(this).val());
                if (selectedPoints.indexOf(pointId) === -1) {
                    pointsToAdd.push(pointId);
                    added = true;
                }
            });
            
            if (added) {
                // Отправляем AJAX запрос на добавление точек
                $.ajax({
                    url: "<?php echo URL::site('accessCategory/addAccessPoints'); ?>",
                    type: "POST",
                    data: {
                        category_id: Number(<?php echo json_encode(Arr::get($category, 'id_accessname')); ?>),
                        points: pointsToAdd
                    },
                    dataType: "json",
                    cache: false,
                    beforeSend: function() {
                        $("#addSelectedPoints").prop("disabled", true).text("<?php echo __('Добавление...'); ?>");
                    },
                    success: function(response) {
                        if (response.success) {
                            // Обновляем массив выбранных точек
                            for (var i = 0; i < pointsToAdd.length; i++) {
                                if (selectedPoints.indexOf(pointsToAdd[i]) === -1) {
                                    selectedPoints.push(pointsToAdd[i]);
                                }
                            }
                            updateAssignedTable();
                            updateAvailablePoints();
                            alert("<?php echo __('Точки прохода успешно добавлены'); ?>");
                        } else {
                            var errorMsg = response.error || "<?php echo __('Ошибка при добавлении точек прохода'); ?>";
                            alert(errorMsg);
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMsg = "<?php echo __('Ошибка при добавлении точек прохода'); ?>";
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                errorMsg = response.error;
                            }
                        } catch(e) {}
                        alert(errorMsg);
                    },
                    complete: function() {
                        $("#addSelectedPoints").prop("disabled", false).text("<?php echo __('Добавить выбранные'); ?>");
                    }
                });
            } else {
                alert("<?php echo __('Выбранные точки уже добавлены'); ?>");
            }
        });
        
        // Удаление выбранных точек
        $("#removeSelectedPoints").on("click", function() {
            var checkedBoxes = $(".assigned-checkbox:checked");
            var removed = false;
            var pointsToRemove = [];
            
            checkedBoxes.each(function() {
                var pointId = Number($(this).val());
                pointsToRemove.push(pointId);
                removed = true;
            });
            
            if (removed) {
                if (confirm("<?php echo __('Удалить выбранные точки прохода?'); ?>")) {
                    $.ajax({
                        url: "<?php echo URL::site('accessCategory/removeAccessPoints'); ?>",
                        type: "POST",
                        data: {
                            category_id: Number(<?php echo json_encode(Arr::get($category, 'id_accessname')); ?>),
                            points: pointsToRemove
                        },
                        dataType: "json",
                        cache: false,
                        beforeSend: function() {
                            $("#removeSelectedPoints").prop("disabled", true).text("<?php echo __('Удаление...'); ?>");
                        },
                        success: function(response) {
                            if (response.success) {
                                for (var i = 0; i < pointsToRemove.length; i++) {
                                    var index = selectedPoints.indexOf(pointsToRemove[i]);
                                    if (index !== -1) {
                                        selectedPoints.splice(index, 1);
                                    }
                                }
                                updateAssignedTable();
                                updateAvailablePoints();
                                alert("<?php echo __('Точки прохода успешно удалены'); ?>");
                            } else {
                                var errorMsg = response.error || "<?php echo __('Ошибка при удалении точек прохода'); ?>";
                                alert(errorMsg);
                            }
                        },
                        error: function(xhr, status, error) {
                            var errorMsg = "<?php echo __('Ошибка при удалении точек прохода'); ?>";
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.error) {
                                    errorMsg = response.error;
                                }
                            } catch(e) {}
                            alert(errorMsg);
                        },
                        complete: function() {
                            $("#removeSelectedPoints").prop("disabled", false).text("<?php echo __('Удалить выбранные'); ?>");
                        }
                    });
                }
            } else {
                alert("<?php echo __('Не выбраны точки для удаления'); ?>");
            }
        });
        
        // Выбрать все чекбоксы в таблице добавленных точек
        $("#selectAllAssigned").on("change", function() {
            var isChecked = $(this).prop("checked");
            $(".assigned-checkbox").prop("checked", isChecked);
        });
        
        // Обновление состояния чекбокса "Выбрать все"
        $(document).on("change", ".assigned-checkbox", function() {
            var total = $(".assigned-checkbox").length;
            var checked = $(".assigned-checkbox:checked").length;
            $("#selectAllAssigned").prop("checked", total === checked && total > 0);
        });
        
        // Подсветка строки при наведении
        $(document).on("mouseenter", "#assignedPointsBody tr", function() {
            $(this).addClass("info");
        }).on("mouseleave", "#assignedPointsBody tr", function() {
            $(this).removeClass("info");
        });
        
        // Инициализация
        updateAvailablePoints();
        updateAssignedTable();
        updateSelectedCount();
    });
</script>

<style>
    #availablePointsSelect {
        font-size: 12px;
    }
    
    #assignedPointsBody tr {
        cursor: pointer;
    }
    
    #assignedPointsBody tr:hover {
        background-color: #f5f5f5;
    }
    
    #assignedPointsBody tr.info {
        background-color: #d9edf7;
    }
    
    .assigned-checkbox {
        cursor: pointer;
    }
    
    #selectedCount {
        font-size: 24px;
        padding: 5px 15px;
    }
</style>