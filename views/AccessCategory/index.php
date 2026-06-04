<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-tree-deciduous"></span> 
            <?php echo __('Категории доступа'); ?>
        </h3>
    </div>
    <div class="panel-body">
        
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
        
        <!-- Панель инструментов -->
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-xs-12">
                <div class="btn-group btn-group-sm">
                    <?php if ($is_admin): ?>
                        <a href="<?php echo URL::site('accessCategory/add'); ?>" class="btn btn-success">
                            <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить'); ?>
                        </a>
                    <?php endif; ?>
                    <button type="button" id="expandAllBtn" class="btn btn-default" title="Развернуть все">
                        <span class="glyphicon glyphicon-plus-sign"></span>
                    </button>
                    <button type="button" id="collapseAllBtn" class="btn btn-default" title="Свернуть все">
                        <span class="glyphicon glyphicon-minus-sign"></span>
                    </button>
                </div>
                <div class="pull-right" style="width: 250px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                        <input type="text" id="treeSearch" class="form-control" placeholder="Поиск...">
                        <span class="input-group-btn">
                            <button id="clearSearch" class="btn btn-default" type="button">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Контейнер дерева -->
        <div class="tree-container explorer-tree" id="treeContainer">
            <ul class="tree" id="categoryTree">
                <?php foreach ($acList as $category): ?>
                    <li class="tree-category" data-category-id="<?php echo $category['id_accessname']; ?>" data-category-name="<?php echo strtolower(htmlspecialchars($category['name'])); ?>">
                        <div class="tree-node tree-node-category">
                            <span class="tree-toggle">
                                <span class="glyphicon glyphicon-chevron-right"></span>
                            </span>
                            <span class="tree-icon">
                                <span class="glyphicon glyphicon-folder-close"></span>
                            </span>
                            <span class="tree-label"><?php echo htmlspecialchars($category['name']); ?></span>
                            <div class="tree-actions">
                                <a href="<?php echo URL::site('accessCategory/edit/' . $category['id_accessname']); ?>" class="action-btn" title="Редактировать">
                                    <span class="glyphicon glyphicon-edit"></span>
                                </a>
                                <?php if ($is_admin): ?>
                                    <a href="<?php echo URL::site('accessCategory/delete/' . $category['id_accessname']); ?>" class="action-btn" onclick="return confirm('Вы уверены?')" title="Удалить">
                                        <span class="glyphicon glyphicon-trash"></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <ul class="tree-children" style="display: none;"></ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="tree-status">
            <span class="glyphicon glyphicon-dashboard"></span> Категорий: <strong><?php echo count($acList); ?></strong>
            <span id="searchInfo" style="display: none;"> | <span class="glyphicon glyphicon-search"></span> Найдено: <strong id="searchResultsCount">0</strong></span>
        </div>
        
    </div>
</div>

<style>
/* Стили как в предыдущем варианте, можно оставить те же */
.explorer-tree { background: #fff; border: 1px solid #ddd; border-radius: 3px; font-size: 12px; max-height: 500px; overflow-y: auto; }
.explorer-tree .tree { margin: 0; padding: 2px 0; list-style: none; }
.explorer-tree .tree ul { margin: 0; padding-left: 18px; list-style: none; }
.explorer-tree .tree li { margin: 0; padding: 0; }
.explorer-tree .tree-node { display: flex; align-items: center; padding: 2px 4px 2px 0; cursor: pointer; border-radius: 2px; }
.explorer-tree .tree-toggle { width: 16px; margin-right: 2px; text-align: center; cursor: pointer; }
.explorer-tree .tree-toggle .glyphicon { font-size: 10px; color: #888; transition: transform 0.1s; }
.explorer-tree .tree-toggle-placeholder { width: 16px; margin-right: 2px; display: inline-block; }
.explorer-tree .tree-icon { width: 18px; margin-right: 4px; text-align: center; }
.explorer-tree .tree-icon .glyphicon { font-size: 12px; }
.tree-node-category .tree-icon .glyphicon { color: #e6a017; }
.tree-node-device .tree-icon .glyphicon { color: #5bc0de; }
.tree-node-timezone .tree-icon .glyphicon { color: #5cb85c; }
.explorer-tree .tree-label {
    flex: 0 1 auto;        /* не растягивается, занимает только нужную ширину */
    max-width: 300px;      /* ограничиваем длинную строку */
    margin-right: 10px;    /* отступ перед кнопками */
    color: #333;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.explorer-tree .tree-label-empty { color: #999; font-style: italic; }
.explorer-tree .tree-badge { display: inline-block; min-width: 18px; padding: 0 5px; margin-left: 8px; background: #e0e0e0; color: #666; font-size: 10px; border-radius: 10px; }
.explorer-tree .tree-actions {
    display: flex;         /* всегда показываем */
    gap: 2px;
    margin-left: 0;
    flex-shrink: 0;
}
.explorer-tree .tree-node:hover .tree-actions { display: flex; }
.explorer-tree .action-btn { padding: 2px 4px; color: #666; font-size: 11px; border-radius: 3px; }
.explorer-tree .action-btn:hover { background: #e0e0e0; color: #333; text-decoration: none; }
.explorer-tree .tree-node:hover { background-color: #e8f0fe; }
.tree-node-category:hover { background-color: #fdf5e6; }
.tree-node-device:hover { background-color: #e8f0fe; }
.explorer-tree .tree-node.expanded > .tree-toggle .glyphicon { transform: rotate(90deg); }
.explorer-tree .tree-children { display: none; }
.explorer-tree .tree-node.highlight { background-color: #fff3cd; }
.tree-status { margin-top: 8px; padding: 5px 10px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 3px; font-size: 11px; color: #666; }
.loading-spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid #ddd; border-top-color: #5bc0de; border-radius: 50%; animation: spin 0.6s linear infinite; margin-right: 6px; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
$(document).ready(function() {
    // Состояние загруженных устройств и временных зон
    var loadedDevices = {};
    var loadedTimezones = {};
    
    // Функция загрузки устройств для категории
    function loadDevices($categoryLi, categoryId) {
        var $childrenContainer = $categoryLi.children('ul.tree-children');
        if (loadedDevices[categoryId]) {
            // Уже загружено, просто показываем/скрываем
            if ($childrenContainer.is(':visible')) {
                $childrenContainer.slideUp(100);
                $categoryLi.children('.tree-node-category').removeClass('expanded');
            } else {
                $childrenContainer.slideDown(100);
                $categoryLi.children('.tree-node-category').addClass('expanded');
            }
            return;
        }
        
        // Показываем индикатор загрузки
        $childrenContainer.html('<li><div class="tree-node" style="padding-left: 20px;"><span class="loading-spinner"></span> Загрузка...</div></li>');
        $childrenContainer.slideDown(100);
        $categoryLi.children('.tree-node-category').addClass('expanded');
        
        $.ajax({
            url: '<?php echo URL::site("accessCategory/getCategoryDevices"); ?>/' + categoryId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.devices) {
                    if (response.devices.length === 0) {
                        $childrenContainer.html('<li class="tree-empty"><div class="tree-node tree-node-empty"><span class="tree-toggle-placeholder"></span><span class="tree-icon"><span class="glyphicon glyphicon-info-sign"></span></span><span class="tree-label-empty">Нет точек прохода</span></div></li>');
                    } else {
                        var html = '';
                        $.each(response.devices, function(idx, device) {
                            html += '<li class="tree-device" data-device-id="' + device.id + '" data-category-id="' + categoryId + '">';
                            html += '<div class="tree-node tree-node-device">';
                            html += '<span class="tree-toggle"><span class="glyphicon glyphicon-chevron-right"></span></span>';
                            html += '<span class="tree-icon"><span class="glyphicon glyphicon-tower"></span></span>';
                            html += '<span class="tree-label">' + escapeHtml(device.name) + '</span>';
                            if (device.timezone_count > 0) {
                                html += '<span class="tree-badge">' + device.timezone_count + '</span>';
                            }
                            html += '<div class="tree-actions">';
                            html += '<a href="<?php echo URL::site("accessCategory/editTimezones"); ?>/' + categoryId + '/' + device.id + '" class="action-btn" title="Временные зоны"><span class="glyphicon glyphicon-time"></span></a>';
                            html += '</div></div><ul class="tree-children" style="display: none;"></ul></li>';
                        });
                        $childrenContainer.html(html);
                    }
                    loadedDevices[categoryId] = true;
                } else {
                    $childrenContainer.html('<li><div class="tree-node tree-node-empty"><span class="tree-label-empty">Ошибка загрузки</span></div></li>');
                }
            },
            error: function() {
                $childrenContainer.html('<li><div class="tree-node tree-node-empty"><span class="tree-label-empty">Ошибка загрузки</span></div></li>');
            }
        });
    }
    
    // Функция загрузки временных зон для устройства
    function loadTimezones($deviceLi, categoryId, deviceId) {
        var $childrenContainer = $deviceLi.children('ul.tree-children');
        var key = categoryId + '_' + deviceId;
        
        if (loadedTimezones[key]) {
            if ($childrenContainer.is(':visible')) {
                $childrenContainer.slideUp(100);
                $deviceLi.children('.tree-node-device').removeClass('expanded');
            } else {
                $childrenContainer.slideDown(100);
                $deviceLi.children('.tree-node-device').addClass('expanded');
            }
            return;
        }
        
        $childrenContainer.html('<li><div class="tree-node" style="padding-left: 20px;"><span class="loading-spinner"></span> Загрузка...</div></li>');
        $childrenContainer.slideDown(100);
        $deviceLi.children('.tree-node-device').addClass('expanded');
        
        $.ajax({
            url: '<?php echo URL::site("accessCategory/getDeviceTimezones"); ?>/' + categoryId + '/' + deviceId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.timezones) {
                    if (response.timezones.length === 0) {
                        $childrenContainer.html('<li class="tree-empty"><div class="tree-node tree-node-empty"><span class="tree-toggle-placeholder"></span><span class="tree-icon"><span class="glyphicon glyphicon-info-sign"></span></span><span class="tree-label-empty">Нет временных зон</span></div></li>');
                    } else {
                        var html = '';
                        $.each(response.timezones, function(idx, tz) {
                            html += '<li class="tree-timezone">';
                            html += '<div class="tree-node tree-node-timezone">';
                            html += '<span class="tree-toggle-placeholder"></span>';
                            html += '<span class="tree-icon"><span class="glyphicon glyphicon-time"></span></span>';
                            html += '<span class="tree-label">' + escapeHtml(tz.name) + '</span>';
                            html += '</div></li>';
                        });
                        $childrenContainer.html(html);
                    }
                    loadedTimezones[key] = true;
                } else {
                    $childrenContainer.html('<li><div class="tree-node tree-node-empty"><span class="tree-label-empty">Ошибка загрузки</span></div></li>');
                }
            },
            error: function() {
                $childrenContainer.html('<li><div class="tree-node tree-node-empty"><span class="tree-label-empty">Ошибка загрузки</span></div></li>');
            }
        });
    }
    
    // Обработка кликов по узлам
    $(document).on('click', '.tree-node-category', function(e) {
        if ($(e.target).closest('.tree-actions, .action-btn, a').length) return;
        var $li = $(this).closest('.tree-category');
        var catId = $li.data('category-id');
        loadDevices($li, catId);
    });
    
    $(document).on('click', '.tree-node-device', function(e) {
        if ($(e.target).closest('.tree-actions, .action-btn, a').length) return;
        var $li = $(this).closest('.tree-device');
        var catId = $li.data('category-id');
        var devId = $li.data('device-id');
        loadTimezones($li, catId, devId);
    });
    
    // Развернуть все: загружаем все категории рекурсивно (можно реализовать по мере необходимости)
    $('#expandAllBtn').on('click', function() {
        $('.tree-category').each(function() {
            var $this = $(this);
            var catId = $this.data('category-id');
            if (!loadedDevices[catId]) {
                loadDevices($this, catId);
            } else {
                $this.children('ul.tree-children').slideDown(100);
                $this.children('.tree-node-category').addClass('expanded');
            }
        });
    });
    
    $('#collapseAllBtn').on('click', function() {
        $('.tree-children').slideUp(100);
        $('.tree-node').removeClass('expanded');
    });
    
    // Поиск (упрощенный)
    var searchTimeout;
    $('#treeSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        var term = $(this).val().trim().toLowerCase();
        searchTimeout = setTimeout(function() { performSearch(term); }, 300);
    });
    
    function performSearch(term) {
        $('.tree-node').removeClass('highlight');
        if (!term) {
            $('#searchInfo').hide();
            return;
        }
        var matches = 0;
        $('.tree-category').each(function() {
            var catName = $(this).data('category-name') || '';
            if (catName.indexOf(term) !== -1) {
                $(this).children('.tree-node-category').addClass('highlight');
                matches++;
            }
        });
        if (matches) {
            $('#searchInfo').show();
            $('#searchResultsCount').text(matches);
        } else {
            $('#searchInfo').hide();
        }
    }
    
    $('#clearSearch').on('click', function() {
        $('#treeSearch').val('');
        performSearch('');
    });
    
    function escapeHtml(str) {
        return $('<div>').text(str).html();
    }
});
</script>