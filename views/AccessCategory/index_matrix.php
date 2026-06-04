<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-th"></span> 
            Матрица доступа: точки прохода ↔ категории
        </h3>
    </div>
    <div class="panel-body">
        
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
        
        <!-- Панель инструментов -->
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
                        <?php foreach ($categories as $category): ?>
                            <th class="cat-col" data-cat-id="<?php echo $category['id_accessname']; ?>" 
                                data-cat-name="<?php echo strtolower(htmlspecialchars($category['name'])); ?>">
                                <div class="cat-title" title="<?php echo htmlspecialchars($category['name']); ?>">
                                    <?php echo htmlspecialchars(mb_substr($category['name'], 0, 12)); ?>
                                </div>
                                <div class="cat-id">ID:<?php echo $category['id_accessname']; ?></div>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allPoints as $point): 
                        $pointId = $point['id_dev'];
                        $pointName = htmlspecialchars($point['name']);
                    ?>
                        <tr class="point-row" data-point-id="<?php echo $pointId; ?>" 
                            data-point-name="<?php echo strtolower($pointName); ?>">
                            <td class="point-col">
                                <span class="glyphicon glyphicon-tower" style="color: #5bc0de;"></span>
                                <span class="point-name"><?php echo $pointName; ?></span>
                                <span class="point-id">(<?php echo $pointId; ?>)</span>
                             </td>
                            <?php foreach ($categories as $category): 
                                $catId = $category['id_accessname'];
                                $isChecked = in_array($pointId, $categoryPointsMap[$catId]);
                            ?>
                                <td class="check-cell" data-cat-id="<?php echo $catId; ?>" data-point-id="<?php echo $pointId; ?>">
                                    <input type="checkbox" class="access-checkbox" 
                                           data-point-id="<?php echo $pointId; ?>"
                                           data-cat-id="<?php echo $catId; ?>"
                                           <?php echo $isChecked ? 'checked' : ''; ?>
                                           <?php echo $is_admin ? '' : 'disabled'; ?>>
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
        
    </div>
</div>

<style>
/* Контейнер с прокруткой */
.matrix-wrapper {
    max-height: 480px;
    overflow: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
}

/* Таблица */
.matrix-table {
    border-collapse: collapse;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    font-size: 12px;
    width: 100%;
}

/* Заголовки */
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
    min-width: 200px;
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

/* Ячейки */
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

/* Чекбоксы */
.matrix-table .access-checkbox {
    width: 16px;
    height: 16px;
    margin: 10px 0;
    cursor: pointer;
    vertical-align: middle;
}

.matrix-table .access-checkbox:disabled {
    cursor: not-allowed;
    opacity: 0.4;
}

/* Ячейка с чекбоксом */
.matrix-table .check-cell {
    width: 30px;
    background-color: #fafafa;
    cursor: pointer;
    transition: background 0.1s;
}

.matrix-table .check-cell:hover {
    background-color: #e8f0fe;
}

/* Скрытие строк при фильтрации точек */
.matrix-table .point-row.filtered-out {
    display: none;
}

/* Скрытие колонок при фильтрации категорий */
.matrix-table .cat-col.hide-col {
    display: none;
}

.matrix-table .check-cell.hide-col {
    display: none;
}

/* Подсветка изменений */
.matrix-table .check-cell.has-changes {
    background-color: #d4edda !important;
}

/* Строка при наведении */
.matrix-table .point-row:hover td {
    background-color: #f9f9f9;
}

.matrix-table .point-row:hover td.point-col {
    background-color: #f9f9f9;
}

/* Нижняя панель */
.matrix-footer {
    margin-top: 8px;
    padding: 5px 8px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 3px;
    font-size: 12px;
    color: #666;
}

/* Индикатор сохранения */
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
    
// Фильтрация категорий (скрываем колонки) - улучшенная версия
$('#filterCategories').on('keyup', function() {
    var term = $(this).val().toLowerCase().trim();
    var visibleCount = 0;
    
    if (term === '') {
        // Показываем все колонки
        $('.cat-col').removeClass('hide-col');
        $('.check-cell').removeClass('hide-col');
        visibleCount = $('.cat-col').length;
    } else {
        // Проходим по всем категориям
        $('.cat-col').each(function() {
            // Берём название из data-cat-name (уже в нижнем регистре)
            var catName = $(this).data('cat-name') || '';
            // Также проверяем оригинальное название (для кириллицы)
            var originalTitle = $(this).find('.cat-title').attr('title') || '';
            var originalNameLower = originalTitle.toLowerCase();
            
            var catId = $(this).data('cat-id');
            
            // Проверяем оба варианта
            var found = (catName.indexOf(term) !== -1) || (originalNameLower.indexOf(term) !== -1);
            
            if (found) {
                // Показываем колонку
                $(this).removeClass('hide-col');
                $('.check-cell[data-cat-id="' + catId + '"]').removeClass('hide-col');
                visibleCount++;
            } else {
                // Скрываем колонку
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
        
        // Показываем все строки
        $('.point-row').removeClass('filtered-out');
        $('#statsPoints').text($('.point-row').length + '/' + $('.point-row').length);
        
        // Показываем все колонки
        $('.cat-col').removeClass('hide-col');
        $('.check-cell').removeClass('hide-col');
        $('#statsCats').text($('.cat-col').length + '/' + $('.cat-col').length);
    });
    
    // Сохраняем исходное состояние
    $('.access-checkbox:enabled').each(function() {
        $(this).closest('.check-cell').data('original-checked', $(this).prop('checked'));
    });
    
    // Отслеживаем изменения
    $('.access-checkbox:enabled').on('change', function() {
        var $cell = $(this).closest('.check-cell');
        var original = $cell.data('original-checked');
        var current = $(this).prop('checked');
        if (original !== current) {
            $cell.addClass('has-changes');
        } else {
            $cell.removeClass('has-changes');
        }
    });
    
    // Клик по ячейке переключает чекбокс
    $(document).on('click', '.check-cell', function(e) {
        if ($(e.target).is('input')) return;
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
    
    // Инициализация статистики
    $('#statsPoints').text($('.point-row').length + '/' + $('.point-row').length);
    $('#statsCats').text($('.cat-col').length + '/' + $('.cat-col').length);
});
</script>