<!-- Панель инструментов матрицы -->
<div class="row" style="margin-bottom: 10px;">
    <div class="col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span> Точки:</span>
            <input type="text" id="filterPoints" class="form-control" placeholder="название точки...">
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span> Категории:</span>
            <input type="text" id="filterCategories" class="form-control" placeholder="скрыть остальные...">
        </div>
    </div>
    <div class="col-md-4 text-right">
        <?php if ($is_admin): ?>
            <button type="button" id="saveMatrix" class="btn btn-sm btn-primary">
                <span class="glyphicon glyphicon-save"></span> Сохранить
            </button>
            <button type="button" id="resetFilters" class="btn btn-sm btn-default">
                <span class="glyphicon glyphicon-refresh"></span> Сброс
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Матричная таблица -->
<div class="matrix-wrapper">
    <table class="matrix-table">
        <thead>
            <tr>
                <th class="point-col">Точка прохода</th>
                <?php foreach ($categories as $category): 
                    // Подсчитываем количество точек прохода в этой категории
                    $pointsInCategory = 0;
                    foreach ($allPoints as $point) {
                        $pointId = $point['id_dev'];
                        if (in_array($pointId, $categoryPointsMap[$category['id_accessname']])) {
                            $pointsInCategory++;
                        }
                    }
                    $pointsBadgeClass = $pointsInCategory == 0 ? 'count-zero' : '';
                    $pointsBadgeColor = $pointsInCategory == 0 ? '#ffc107' : '#5bc0de';
                    $pointsBadgeTextColor = $pointsInCategory == 0 ? '#856404' : 'white';
                ?>
                    <th class="cat-col" data-cat-id="<?php echo $category['id_accessname']; ?>" 
                        data-cat-name="<?php echo mb_strtolower(htmlspecialchars($category['name']), 'UTF-8'); ?>">
                        <a href="<?php echo URL::site('accessCategory/edit/' . $category['id_accessname']); ?>" 
                           class="cat-link" 
                           title="Редактировать категорию: <?php echo htmlspecialchars($category['name']); ?>">
                            <div class="cat-title">
                                <?php echo htmlspecialchars(mb_substr($category['name'], 0, 12)); ?>
                            </div>
                            <div class="cat-id">
                                ID:<?php echo $category['id_accessname']; ?>
                                <span class="cat-points-count <?php echo $pointsBadgeClass; ?>" 
                                      style="background-color: <?php echo $pointsBadgeColor; ?>; color: <?php echo $pointsBadgeTextColor; ?>;"
                                      title="Точек прохода в категории: <?php echo $pointsInCategory; ?>">
                                    <?php echo $pointsInCategory; ?>
                                </span>
                            </div>
                        </a>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Предварительно загружаем все временные зоны для всех пар (категория, точка)
            $timezonesCache = array();
            foreach ($categories as $category) {
                $catId = $category['id_accessname'];
                foreach ($allPoints as $point) {
                    $pointId = $point['id_dev'];
                    $timezones = Model::factory('accessCategory')->getDeviceTimezones($catId, $pointId);
                    $timezonesCache[$catId . '_' . $pointId] = $timezones;
                }
            }
            
            // Получаем список всех временных зон с названиями
            $allTimezones = Model::factory('accessCategory')->getTimezonesList();
            $timezonesMap = array();
            foreach ($allTimezones as $tz) {
                $timezonesMap[$tz['id_timezone']] = htmlspecialchars($tz['name']);
            }
            
            foreach ($allPoints as $point): 
                $pointId = $point['id_dev'];
                $pointName = htmlspecialchars($point['name']);
                
                // Подсчитываем количество категорий, в которые входит эта точка
                $categoriesCount = 0;
                foreach ($categories as $category) {
                    $catId = $category['id_accessname'];
                    if (in_array($pointId, $categoryPointsMap[$catId])) {
                        $categoriesCount++;
                    }
                }
                
                $badgeClass = $categoriesCount == 0 ? 'count-zero' : '';
                $badgeColor = $categoriesCount == 0 ? '#ffc107' : '#5bc0de';
                $badgeTextColor = $categoriesCount == 0 ? '#856404' : 'white';
            ?>
                <tr class="point-row" data-point-id="<?php echo $pointId; ?>" 
                    data-point-name="<?php echo mb_strtolower($pointName, 'UTF-8'); ?>">
                    <td class="point-col">
                        <span class="glyphicon glyphicon-tower" style="color: #5bc0de;"></span>
                        <span class="point-name"><?php echo $pointName; ?></span>
                        <span class="point-id">(<?php echo $pointId; ?>)</span>
                        <span class="badge point-categories-count <?php echo $badgeClass; ?>" 
                              style="background-color: <?php echo $badgeColor; ?>; color: <?php echo $badgeTextColor; ?>;"
                              title="Входит в <?php echo $categoriesCount; ?> категорий<?php echo $categoriesCount % 10 == 1 && $categoriesCount % 100 != 11 ? 'у' : ($categoriesCount % 10 >= 2 && $categoriesCount % 10 <= 4 && ($categoriesCount % 100 < 10 || $categoriesCount % 100 >= 20) ? 'и' : ''); ?>">
                            <?php echo $categoriesCount; ?>
                        </span>
                    </td>
                    <?php foreach ($categories as $category): 
                        $catId = $category['id_accessname'];
                        $isChecked = in_array($pointId, $categoryPointsMap[$catId]);
                        $timezones = isset($timezonesCache[$catId . '_' . $pointId]) ? $timezonesCache[$catId . '_' . $pointId] : array();
                        $timezonesNames = array();
                        foreach ($timezones as $tzId) {
                            if (isset($timezonesMap[$tzId])) {
                                $timezonesNames[] = $timezonesMap[$tzId];
                            }
                        }
                    ?>
                        <td class="check-cell" data-cat-id="<?php echo $catId; ?>" data-point-id="<?php echo $pointId; ?>">
                            <div class="cell-content">
                                <input type="checkbox" class="access-checkbox" 
                                       data-point-id="<?php echo $pointId; ?>"
                                       data-cat-id="<?php echo $catId; ?>"
                                       <?php echo $isChecked ? 'checked' : ''; ?>
                                       <?php echo $is_admin ? '' : 'disabled'; ?>>
                                <?php if (!empty($timezonesNames)): ?>
                                    <div class="timezones-list">
                                        <?php foreach ($timezonesNames as $tzName): ?>
                                            <span class="timezone-badge" title="<?php echo $tzName; ?>">
                                                <?php echo htmlspecialchars(mb_substr($tzName, 0, 6)); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Статистика -->
<div class="matrix-footer">
    <span class="glyphicon glyphicon-stats"></span> 
    Точек: <strong id="statsPoints"><?php echo count($allPoints); ?></strong> | 
    Категорий: <strong id="statsCats"><?php echo count($categories); ?></strong>
    <span id="filterStats" class="text-muted"></span>
</div>

<style>
.matrix-wrapper {
    max-height: 480px;
    overflow: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
}

.matrix-table {
    border-collapse: collapse;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    font-size: 12px;
    width: auto;
    min-width: 100%;
}

.matrix-table th {
    background: #f5f5f5;
    padding: 8px 6px;
    text-align: center;
    font-weight: normal;
    border-bottom: 1px solid #ddd;
    border-right: 1px solid #e0e0e0;
    white-space: nowrap;
    vertical-align: middle;
    min-width: 70px;
}

.matrix-table th.point-col {
    background: #f5f5f5;
    text-align: left;
    padding: 8px 10px;
    font-weight: bold;
    border-right: 1px solid #ddd;
    min-width: 260px;
}

/* Ссылка на редактирование категории */
.matrix-table .cat-link {
    display: block;
    color: #333;
    text-decoration: none;
    transition: color 0.1s;
}

.matrix-table .cat-link:hover {
    color: #337ab7;
    text-decoration: underline;
}

.matrix-table .cat-link:hover .cat-id {
    color: #337ab7;
}

.matrix-table .cat-title {
    font-weight: bold;
    font-size: 11px;
    line-height: 1.3;
}

.matrix-table .cat-id {
    font-size: 9px;
    color: #999;
    margin-top: 2px;
}

/* Счётчик точек в категории */
.matrix-table .cat-points-count {
    display: inline-block;
    margin-left: 6px;
    padding: 1px 5px;
    font-size: 9px;
    font-weight: bold;
    border-radius: 10px;
    background-color: #5bc0de;
    color: white;
}

.matrix-table .cat-points-count.count-zero {
    background-color: #ffc107;
    color: #856404;
}

.matrix-table td {
    padding: 0;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
    border-right: 1px solid #f5f5f5;
}

.matrix-table td.point-col {
    background: #fff;
    padding: 8px 10px;
    text-align: left;
    white-space: nowrap;
    font-size: 12px;
    border-right: 1px solid #ddd;
}

.matrix-table .point-name {
    font-weight: 500;
}

.matrix-table .point-id {
    font-size: 10px;
    color: #999;
    margin-left: 6px;
}

/* Счётчик категорий для точки */
.matrix-table .point-categories-count {
    display: inline-block;
    margin-left: 8px;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: bold;
    border-radius: 10px;
    background-color: #5bc0de;
    color: white;
}

.matrix-table .point-categories-count.count-zero {
    background-color: #ffc107;
    color: #856404;
}

/* Содержимое ячейки */
.matrix-table .cell-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 6px 4px;
    gap: 4px;
}

.matrix-table .access-checkbox {
    width: 16px;
    height: 16px;
    margin: 0;
    cursor: pointer;
    vertical-align: middle;
}

.matrix-table .access-checkbox:disabled {
    cursor: not-allowed;
    opacity: 0.4;
}

/* Список временных зон */
.matrix-table .timezones-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 2px;
    max-width: 100px;
}

.matrix-table .timezone-badge {
    display: inline-block;
    padding: 1px 3px;
    font-size: 8px;
    font-weight: normal;
    background-color: #e8f0fe;
    color: #333;
    border-radius: 3px;
    white-space: nowrap;
    cursor: help;
}

.matrix-table .timezone-badge:hover {
    background-color: #d0e0f8;
}

.matrix-table .check-cell {
    min-width: 80px;
    background-color: #fafafa;
    cursor: pointer;
    transition: background 0.1s;
}

.matrix-table .check-cell:hover {
    background-color: #e8f0fe;
}

/* Подсветка изменений */
.matrix-table .check-cell.has-changes {
    background-color: #d4edda !important;
}

.matrix-table .point-row.filtered-out {
    display: none;
}

.matrix-table .cat-col.hide-col {
    display: none;
}

.matrix-table .check-cell.hide-col {
    display: none;
}

.matrix-table .point-row:hover td {
    background-color: #f9f9f9;
}

.matrix-table .point-row:hover td.point-col {
    background-color: #f9f9f9;
}

.matrix-footer {
    margin-top: 8px;
    padding: 5px 8px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 3px;
    font-size: 12px;
    color: #666;
}

.saving-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    padding: 8px 15px;
    background: #5bc0de;
    color: white;
    border-radius: 4px;
    font-size: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.saving-indicator.success {
    background: #5cb85c;
}

.saving-indicator.error {
    background: #d9534f;
}

.glyphicon-spin {
    animation: spin 1s infinite linear;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
$(document).ready(function() {
    
    // Функция обновления счётчиков категорий для точек
    function updatePointsCounters() {
        $('.point-row').each(function() {
            var $row = $(this);
            var checkedCount = $row.find('.access-checkbox:checked').length;
            var $counter = $row.find('.point-categories-count');
            
            if ($counter.length) {
                $counter.text(checkedCount);
                
                // Обновляем класс и стили для подсветки нуля
                if (checkedCount == 0) {
                    $counter.addClass('count-zero');
                    $counter.css('background-color', '#ffc107');
                    $counter.css('color', '#856404');
                } else {
                    $counter.removeClass('count-zero');
                    $counter.css('background-color', '#5bc0de');
                    $counter.css('color', 'white');
                }
                
                var wordEnding = '';
                if (checkedCount % 10 == 1 && checkedCount % 100 != 11) {
                    wordEnding = 'у';
                } else if (checkedCount % 10 >= 2 && checkedCount % 10 <= 4 && (checkedCount % 100 < 10 || checkedCount % 100 >= 20)) {
                    wordEnding = 'и';
                }
                $counter.attr('title', 'Входит в ' + checkedCount + ' категори' + wordEnding);
            }
        });
    }
    
    // Функция обновления счётчиков категорий
    function updateCategoryCounters() {
        $('.cat-col').each(function() {
            var $catCol = $(this);
            var catId = $catCol.data('cat-id');
            var checkedCount = $('.check-cell[data-cat-id="' + catId + '"] .access-checkbox:checked').length;
            var $counter = $catCol.find('.cat-points-count');
            
            if ($counter.length) {
                $counter.text(checkedCount);
                
                if (checkedCount == 0) {
                    $counter.addClass('count-zero');
                    $counter.css('background-color', '#ffc107');
                    $counter.css('color', '#856404');
                } else {
                    $counter.removeClass('count-zero');
                    $counter.css('background-color', '#5bc0de');
                    $counter.css('color', 'white');
                }
                
                $counter.attr('title', 'Точек прохода в категории: ' + checkedCount);
            }
        });
    }
    
    // Фильтрация точек (скрываем строки)
    $('#filterPoints').on('keyup', function() {
        var term = $(this).val().toLowerCase().trim();
        var visibleCount = 0;
        
        $('.point-row').each(function() {
            var pointName = $(this).data('point-name') || '';
            if (term === '' || pointName.indexOf(term) !== -1) {
                $(this).removeClass('filtered-out');
                visibleCount++;
            } else {
                $(this).addClass('filtered-out');
            }
        });
        
        $('#statsPoints').text(visibleCount + '/' + $('.point-row').length);
    });
    
    // Фильтрация категорий (скрываем колонки)
    $('#filterCategories').on('keyup', function() {
        var term = $(this).val().toLowerCase().trim();
        var visibleCount = 0;
        
        if (term === '') {
            $('.cat-col').removeClass('hide-col');
            $('.check-cell').removeClass('hide-col');
            visibleCount = $('.cat-col').length;
        } else {
            $('.cat-col').each(function() {
                var catName = $(this).data('cat-name') || '';
                var catId = $(this).data('cat-id');
                
                if (catName.indexOf(term) !== -1) {
                    $(this).removeClass('hide-col');
                    $('.check-cell[data-cat-id="' + catId + '"]').removeClass('hide-col');
                    visibleCount++;
                } else {
                    $(this).addClass('hide-col');
                    $('.check-cell[data-cat-id="' + catId + '"]').addClass('hide-col');
                }
            });
        }
        
        $('#statsCats').text(visibleCount + '/' + $('.cat-col').length);
    });
    
    // Сброс всех фильтров
    $('#resetFilters').on('click', function() {
        $('#filterPoints').val('');
        $('#filterCategories').val('');
        
        $('.point-row').removeClass('filtered-out');
        $('#statsPoints').text($('.point-row').length + '/' + $('.point-row').length);
        
        $('.cat-col').removeClass('hide-col');
        $('.check-cell').removeClass('hide-col');
        $('#statsCats').text($('.cat-col').length + '/' + $('.cat-col').length);
    });
    
    // Сохраняем исходное состояние
    $('.access-checkbox:enabled').each(function() {
        $(this).closest('.check-cell').data('original-checked', $(this).prop('checked'));
    });
    
    // Отслеживаем изменения с подсветкой
    $('.access-checkbox:enabled').on('change', function() {
        var $cell = $(this).closest('.check-cell');
        var original = $cell.data('original-checked');
        var current = $(this).prop('checked');
        if (original !== current) {
            $cell.addClass('has-changes');
        } else {
            $cell.removeClass('has-changes');
        }
        updatePointsCounters();
        updateCategoryCounters();
    });
    
    // Клик по ячейке переключает чекбокс (но не по ссылке и не по временным зонам)
    $(document).on('click', '.check-cell', function(e) {
        if ($(e.target).is('input')) return;
        if ($(e.target).hasClass('timezone-badge')) return;
        if ($(e.target).closest('.cat-link').length) return;
        var $checkbox = $(this).find('.access-checkbox:enabled');
        if ($checkbox.length) {
            $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
        }
    });
    
    // Сохранение изменений
    $('#saveMatrix').on('click', function() {
        var changes = [];
        $('.access-checkbox:enabled').each(function() {
            var $checkbox = $(this);
            var current = $checkbox.prop('checked');
            var original = $checkbox.closest('.check-cell').data('original-checked');
            
            if (current !== original) {
                changes.push({
                    category_id: parseInt($checkbox.data('cat-id')),
                    point_id: parseInt($checkbox.data('point-id')),
                    checked: current
                });
            }
        });
        
        if (changes.length === 0) {
            alert('Нет изменений для сохранения');
            return;
        }
        
        var $indicator = $('<div class="saving-indicator"><span class="glyphicon glyphicon-refresh glyphicon-spin"></span> Сохранение... (' + changes.length + ')</div>');
        $('body').append($indicator);
        
        $.ajax({
            url: '<?php echo URL::site("accessCategory/saveMatrixChanges"); ?>',
            type: 'POST',
            data: JSON.stringify({ changes: changes }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    changes.forEach(function(change) {
                        var $cell = $('input[data-cat-id="' + change.category_id + '"][data-point-id="' + change.point_id + '"]').closest('.check-cell');
                        $cell.data('original-checked', change.checked);
                        $cell.removeClass('has-changes');
                    });
                    $indicator.addClass('success').html('<span class="glyphicon glyphicon-ok"></span> Сохранено!');
                } else {
                    $indicator.addClass('error').html('<span class="glyphicon glyphicon-exclamation-sign"></span> Ошибка: ' + (response.error || '?'));
                }
                setTimeout(function() { $indicator.fadeOut(500, function() { $(this).remove(); }); }, 2000);
            },
            error: function(xhr, status, error) {
                $indicator.addClass('error').html('<span class="glyphicon glyphicon-exclamation-sign"></span> Ошибка: ' + error);
                setTimeout(function() { $indicator.fadeOut(500, function() { $(this).remove(); }); }, 3000);
            }
        });
    });
    
    // Инициализация
    updatePointsCounters();
    updateCategoryCounters();
    $('#statsPoints').text($('.point-row').length + '/' + $('.point-row').length);
    $('#statsCats').text($('.cat-col').length + '/' + $('.cat-col').length);
});
</script>