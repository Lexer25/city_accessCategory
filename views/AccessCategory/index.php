<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-tree-deciduous"></span> 
            <?php echo __('Категории доступа'); ?>
        </h3>
    </div>
    <div class="panel-body">
        
        <!-- Отображение сообщений -->
        <?php 
        $message = Session::instance()->get_once('message');
        $message_type = Session::instance()->get_once('message_type', 'info');
        if ($message): 
        ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade in" role="alert" style="margin-bottom: 10px;">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Верхняя панель -->
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-xs-12">
                <div class="btn-group btn-group-sm">
                    <?php if ($is_admin): ?>
                        <a href="<?php echo URL::site('accessCategory/add'); ?>" class="btn btn-success">
                            <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить'); ?>
                        </a>
                    <?php else: ?>
                        <span class="btn btn-success disabled" title="<?php echo __('Доступно только администраторам'); ?>">
                            <span class="glyphicon glyphicon-plus"></span> <?php echo __('Добавить'); ?>
                        </span>
                    <?php endif; ?>
                    
                    <button type="button" id="expandAllBtn" class="btn btn-default" title="<?php echo __('Развернуть все'); ?>">
                        <span class="glyphicon glyphicon-plus-sign"></span>
                    </button>
                    <button type="button" id="collapseAllBtn" class="btn btn-default" title="<?php echo __('Свернуть все'); ?>">
                        <span class="glyphicon glyphicon-minus-sign"></span>
                    </button>
                </div>
                
                <!-- Поиск -->
                <div class="pull-right" style="width: 250px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-search"></span>
                        </span>
                        <input type="text" id="treeSearch" class="form-control" placeholder="<?php echo __('Поиск...'); ?>">
                        <span class="input-group-btn">
                            <button id="clearSearch" class="btn btn-default" type="button" title="<?php echo __('Очистить'); ?>">
                                <span class="glyphicon glyphicon-remove"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if(isset($acList) && count($acList) > 0): ?>
            
            <!-- Дерево категорий в стиле Windows Explorer -->
            <div class="tree-container explorer-tree">
                <ul class="tree" id="categoryTree">
                    <?php 
                    // Сортируем категории по названию
                    usort($acList, function($a, $b) {
                        return strcmp(Arr::get($a, 'name'), Arr::get($b, 'name'));
                    });
                    
                    foreach ($acList as $category): 
                        $categoryId = Arr::get($category, 'id_accessname');
                        $categoryName = htmlspecialchars(Arr::get($category, 'name'));
                        
                        // Получаем точки прохода для категории
                        $accessPointsRaw = Model::factory('accessCategory')->getAccessPointsByCategoryId($categoryId);
                        $accessPointsGrouped = Model::factory('AccessCategory')->groupByDevice($accessPointsRaw);
                        
                        // Получаем все временные зоны
                        $allTimezones = Model::factory('accessCategory')->getTimezonesList();
                        $timezonesMap = array();
                        foreach ($allTimezones as $tz) {
                            $timezonesMap[Arr::get($tz, 'id_timezone')] = htmlspecialchars(Arr::get($tz, 'name'));
                        }
                    ?>
                        <li class="tree-category" data-category-id="<?php echo $categoryId; ?>" data-category-name="<?php echo strtolower($categoryName); ?>">
                            <div class="tree-node tree-node-category">
                                <span class="tree-toggle">
                                    <span class="glyphicon glyphicon-chevron-right"></span>
                                </span>
                                <span class="tree-icon">
                                    <span class="glyphicon glyphicon-folder-close"></span>
                                </span>
                                <span class="tree-label"><?php echo $categoryName; ?></span>
                                <span class="tree-badge"><?php echo count($accessPointsGrouped); ?></span>
                                
                                <!-- Кнопки действий -->
                                <div class="tree-actions">
                                    <a href="<?php echo URL::site('accessCategory/edit/' . $categoryId); ?>" class="action-btn" title="<?php echo __('Редактировать'); ?>">
                                        <span class="glyphicon glyphicon-edit"></span>
                                    </a>
                                    <?php if ($is_admin): ?>
                                        <a href="<?php echo URL::site('accessCategory/delete/' . $categoryId); ?>" class="action-btn" onclick="return confirm('<?php echo __('Вы уверены?'); ?>')" title="<?php echo __('Удалить'); ?>">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (count($accessPointsGrouped) > 0): ?>
                                <ul class="tree-children">
                                    <?php foreach ($accessPointsGrouped as $deviceId => $deviceData): ?>
                                        <?php $deviceName = htmlspecialchars(Arr::get($deviceData, 'name')); ?>
                                        <li class="tree-device" data-device-id="<?php echo $deviceId; ?>" data-device-name="<?php echo strtolower($deviceName); ?>" data-category-id="<?php echo $categoryId; ?>">
                                            <div class="tree-node tree-node-device">
                                                <span class="tree-toggle">
                                                    <span class="glyphicon glyphicon-chevron-right"></span>
                                                </span>
                                                <span class="tree-icon">
                                                    <span class="glyphicon glyphicon-tower"></span>
                                                </span>
                                                <span class="tree-label"><?php echo $deviceName; ?></span>
                                                <?php 
                                                $tzCount = is_array(Arr::get($deviceData, 'id_timezone')) ? count(Arr::get($deviceData, 'id_timezone')) : 0;
                                                if ($tzCount > 0): ?>
                                                    <span class="tree-badge"><?php echo $tzCount; ?></span>
                                                <?php endif; ?>
                                                
                                                <div class="tree-actions">
                                                    <a href="<?php echo URL::site('accessCategory/editTimezones/' . $categoryId . '/' . $deviceId); ?>" class="action-btn" title="<?php echo __('Временные зоны'); ?>">
                                                        <span class="glyphicon glyphicon-time"></span>
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            <?php 
                                            $timezoneIds = Arr::get($deviceData, 'id_timezone');
                                            if (!empty($timezoneIds) && is_array($timezoneIds)): 
                                            ?>
                                                <ul class="tree-children">
                                                    <?php foreach ($timezoneIds as $tzId): 
                                                        $tzName = isset($timezonesMap[$tzId]) ? $timezonesMap[$tzId] : $tzId;
                                                    ?>
                                                        <li class="tree-timezone" data-timezone-id="<?php echo $tzId; ?>" data-timezone-name="<?php echo strtolower($tzName); ?>">
                                                            <div class="tree-node tree-node-timezone">
                                                                <span class="tree-toggle-placeholder"></span>
                                                                <span class="tree-icon">
                                                                    <span class="glyphicon glyphicon-time"></span>
                                                                </span>
                                                                <span class="tree-label"><?php echo htmlspecialchars($tzName); ?></span>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <ul class="tree-children">
                                                    <li class="tree-empty">
                                                        <div class="tree-node tree-node-empty">
                                                            <span class="tree-toggle-placeholder"></span>
                                                            <span class="tree-icon">
                                                                <span class="glyphicon glyphicon-info-sign"></span>
                                                            </span>
                                                            <span class="tree-label-empty"><?php echo __('Нет временных зон'); ?></span>
                                                        </div>
                                                    </li>
                                                </ul>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <ul class="tree-children">
                                    <li class="tree-empty">
                                        <div class="tree-node tree-node-empty">
                                            <span class="tree-toggle-placeholder"></span>
                                            <span class="tree-icon">
                                                <span class="glyphicon glyphicon-info-sign"></span>
                                            </span>
                                            <span class="tree-label-empty"><?php echo __('Нет точек прохода'); ?></span>
                                        </div>
                                    </li>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Статистика -->
            <div class="tree-status">
                <span class="glyphicon glyphicon-dashboard"></span> 
                <?php echo __('Категорий'); ?>: <strong><?php echo count($acList); ?></strong>
                <span id="searchInfo" style="display: none;">
                    | <span class="glyphicon glyphicon-search"></span> <?php echo __('Найдено'); ?>: <strong id="searchResultsCount">0</strong>
                </span>
            </div>
            
        <?php else: ?>
            <div class="alert alert-info text-center" style="margin-top: 20px;">
                <span class="glyphicon glyphicon-info-sign"></span> 
                <?php echo __('Нет доступных категорий доступа'); ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<style>
/* ========== Стили дерева в стиле Windows Explorer ========== */

.explorer-tree {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    font-size: 12px;
}

.explorer-tree .tree {
    margin: 0;
    padding: 2px 0;
    list-style: none;
}

.explorer-tree .tree ul {
    margin: 0;
    padding-left: 18px;
    list-style: none;
}

.explorer-tree .tree li {
    margin: 0;
    padding: 0;
    list-style: none;
}

/* Узлы дерева */
.explorer-tree .tree-node {
    display: flex;
    align-items: center;
    padding: 2px 4px 2px 0;
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
    border-radius: 2px;
}

/* Отступы для разных уровней */
.explorer-tree .tree-category > .tree-node {
    padding-left: 4px;
}

/* Кнопка раскрытия/сворачивания */
.explorer-tree .tree-toggle {
    display: inline-flex;
    width: 16px;
    height: 16px;
    margin-right: 2px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.explorer-tree .tree-toggle .glyphicon {
    font-size: 10px;
    color: #888;
    transition: transform 0.1s ease;
}

.explorer-tree .tree-toggle-placeholder {
    display: inline-block;
    width: 16px;
    margin-right: 2px;
    flex-shrink: 0;
}

/* Иконки узлов */
.explorer-tree .tree-icon {
    display: inline-flex;
    width: 18px;
    margin-right: 4px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
}

.explorer-tree .tree-icon .glyphicon {
    font-size: 12px;
}

/* Иконки для разных типов */
.tree-node-category .tree-icon .glyphicon {
    color: #e6a017;
}

.tree-node-device .tree-icon .glyphicon {
    color: #5bc0de;
}

.tree-node-timezone .tree-icon .glyphicon {
    color: #5cb85c;
    font-size: 11px;
}

/* Метки */
.explorer-tree .tree-label {
    flex: 1;
    color: #333;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 12px;
}

.explorer-tree .tree-label-empty {
    color: #999;
    font-style: italic;
    font-size: 11px;
}

/* Бейджи с количеством */
.explorer-tree .tree-badge {
    display: inline-block;
    min-width: 18px;
    padding: 0 5px;
    margin-left: 8px;
    background: #e0e0e0;
    color: #666;
    font-size: 10px;
    font-weight: normal;
    text-align: center;
    border-radius: 10px;
    flex-shrink: 0;
}

/* Кнопки действий */
.explorer-tree .tree-actions {
    display: none;
    margin-left: 8px;
    flex-shrink: 0;
}

.explorer-tree .tree-node:hover .tree-actions {
    display: flex;
    gap: 2px;
}

.explorer-tree .action-btn {
    display: inline-flex;
    padding: 2px 4px;
    color: #666;
    font-size: 11px;
    text-decoration: none;
    border-radius: 3px;
}

.explorer-tree .action-btn:hover {
    background: #e0e0e0;
    color: #333;
    text-decoration: none;
}

/* Hover эффекты */
.explorer-tree .tree-node:hover {
    background-color: #e8f0fe;
}

.explorer-tree .tree-node-category:hover {
    background-color: #fdf5e6;
}

.explorer-tree .tree-node-device:hover {
    background-color: #e8f0fe;
}

.explorer-tree .tree-node-timezone:hover {
    background-color: #eaf5ea;
}

/* Выделение при клике (как в проводнике) */
.explorer-tree .tree-node.selected {
    background-color: #d3e3f5;
    outline: 1px solid #9bc2e6;
}

/* Раскрытые узлы */
.explorer-tree .tree-node.expanded > .tree-toggle .glyphicon {
    transform: rotate(90deg);
}

/* Пустые узлы */
.explorer-tree .tree-node-empty {
    cursor: default;
    opacity: 0.7;
}

.explorer-tree .tree-node-empty:hover {
    background-color: transparent !important;
}

/* Подсветка поиска */
.explorer-tree .tree-node.highlight {
    background-color: #fff3cd;
    outline: 1px solid #ffeaa7;
}

.explorer-tree .tree-node.search-match {
    background-color: #fff3cd;
}

/* Статусная строка */
.tree-status {
    margin-top: 8px;
    padding: 5px 10px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 3px;
    font-size: 11px;
    color: #666;
}

/* Скроллбар */
.tree-container {
    max-height: 500px;
    overflow-y: auto;
    overflow-x: auto;
}

.tree-container::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

.tree-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 5px;
}

.tree-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 5px;
}

.tree-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Анимация раскрытия */
.explorer-tree .tree-children {
    display: none;
}

.explorer-tree .tree-children.expanded {
    display: block;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    
    // Функция для переключения видимости дочерних элементов (как в проводнике)
    function toggleChildren($node) {
        var $li = $node.closest('li');
        var $children = $li.children('ul.tree-children');
        var $toggle = $node.find('.tree-toggle .glyphicon');
        
        if ($children.is(':visible')) {
            $children.slideUp(100);
            $toggle.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
            $node.removeClass('expanded');
        } else {
            $children.slideDown(100);
            $toggle.removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
            $node.addClass('expanded');
        }
    }
    
    // Обработчик клика по узлам дерева
    $(document).on('click', '.tree-node-category, .tree-node-device', function(e) {
        // Не сворачиваем, если клик на кнопках действий или ссылках
        if ($(e.target).closest('.tree-actions, .action-btn, a').length) {
            return;
        }
        toggleChildren($(this));
    });
    
    // Обработчик клика по кнопке раскрытия
    $(document).on('click', '.tree-toggle', function(e) {
        e.stopPropagation();
        toggleChildren($(this).closest('.tree-node'));
    });
    
    // Выделение узла при клике (как в проводнике)
    $(document).on('click', '.tree-node', function(e) {
        if ($(e.target).closest('.tree-actions, .action-btn, a').length) {
            return;
        }
        $('.tree-node').removeClass('selected');
        $(this).addClass('selected');
    });
    
    // Развернуть все
    $('#expandAllBtn').on('click', function() {
        $('.tree-children').show();
        $('.tree-toggle .glyphicon')
            .removeClass('glyphicon-chevron-right')
            .addClass('glyphicon-chevron-down');
        $('.tree-node').addClass('expanded');
        saveTreeState();
    });
    
    // Свернуть все
    $('#collapseAllBtn').on('click', function() {
        $('.tree-children').hide();
        $('.tree-toggle .glyphicon')
            .removeClass('glyphicon-chevron-down')
            .addClass('glyphicon-chevron-right');
        $('.tree-node').removeClass('expanded');
        saveTreeState();
    });
    
    // Поиск по дереву
    var searchTimeout;
    $('#treeSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        var searchTerm = $(this).val().trim().toLowerCase();
        
        searchTimeout = setTimeout(function() {
            performSearch(searchTerm);
        }, 250);
    });
    
    function performSearch(searchTerm) {
        // Снимаем все подсветки
        $('.tree-node').removeClass('highlight search-match');
        $('.highlight-temp').removeClass('highlight-temp');
        
        if (searchTerm === '') {
            $('#searchInfo').hide();
            $('.no-result-message').remove();
            return;
        }
        
        var matches = 0;
        
        // Ищем в категориях
        $('.tree-category').each(function() {
            var $category = $(this);
            var categoryName = $category.data('category-name') || '';
            var $categoryNode = $category.children('.tree-node-category');
            
            if (categoryName.indexOf(searchTerm) !== -1) {
                $categoryNode.addClass('highlight');
                matches++;
                // Раскрываем
                var $children = $category.children('ul.tree-children');
                if ($children.is(':hidden')) {
                    $children.show();
                    $categoryNode.find('.tree-toggle .glyphicon')
                        .removeClass('glyphicon-chevron-right')
                        .addClass('glyphicon-chevron-down');
                    $categoryNode.addClass('expanded');
                }
            }
            
            // Ищем в устройствах
            $category.find('.tree-device').each(function() {
                var $device = $(this);
                var deviceName = $device.data('device-name') || '';
                var $deviceNode = $device.children('.tree-node-device');
                var $categoryChildren = $category.children('ul.tree-children');
                
                if (deviceName.indexOf(searchTerm) !== -1) {
                    $deviceNode.addClass('highlight');
                    matches++;
                    // Раскрываем категорию
                    if ($categoryChildren.is(':hidden')) {
                        $categoryChildren.show();
                        $category.children('.tree-node-category').find('.tree-toggle .glyphicon')
                            .removeClass('glyphicon-chevron-right')
                            .addClass('glyphicon-chevron-down');
                        $category.children('.tree-node-category').addClass('expanded');
                    }
                    // Раскрываем устройство
                    var $deviceChildren = $device.children('ul.tree-children');
                    if ($deviceChildren.is(':hidden')) {
                        $deviceChildren.show();
                        $deviceNode.find('.tree-toggle .glyphicon')
                            .removeClass('glyphicon-chevron-right')
                            .addClass('glyphicon-chevron-down');
                        $deviceNode.addClass('expanded');
                    }
                }
                
                // Ищем в временных зонах
                $device.find('.tree-timezone').each(function() {
                    var $tz = $(this);
                    var tzName = $tz.data('timezone-name') || '';
                    var $tzNode = $tz.children('.tree-node-timezone');
                    
                    if (tzName.indexOf(searchTerm) !== -1) {
                        $tzNode.addClass('highlight');
                        matches++;
                        // Раскрываем все уровни
                        var $categoryChildren = $category.children('ul.tree-children');
                        if ($categoryChildren.is(':hidden')) {
                            $categoryChildren.show();
                            $category.children('.tree-node-category').find('.tree-toggle .glyphicon')
                                .removeClass('glyphicon-chevron-right')
                                .addClass('glyphicon-chevron-down');
                            $category.children('.tree-node-category').addClass('expanded');
                        }
                        var $deviceChildren = $device.children('ul.tree-children');
                        if ($deviceChildren.is(':hidden')) {
                            $deviceChildren.show();
                            $device.children('.tree-node-device').find('.tree-toggle .glyphicon')
                                .removeClass('glyphicon-chevron-right')
                                .addClass('glyphicon-chevron-down');
                            $device.children('.tree-node-device').addClass('expanded');
                        }
                    }
                });
            });
        });
        
        // Показываем информацию о результатах поиска
        if (matches > 0) {
            $('#searchInfo').show();
            $('#searchResultsCount').text(matches);
            $('.no-result-message').remove();
            
            // Прокручиваем к первому найденному
            var $firstMatch = $('.highlight').first();
            if ($firstMatch.length) {
                $('html, body').animate({
                    scrollTop: $firstMatch.offset().top - 120
                }, 200);
            }
        } else {
            $('#searchInfo').hide();
            // Показываем сообщение
            if ($('.no-result-message').length === 0) {
                $('.explorer-tree').append('<div class="alert alert-warning text-center no-result-message" style="margin: 15px;"><span class="glyphicon glyphicon-search"></span> <?php echo __('Ничего не найдено'); ?></div>');
            }
        }
    }
    
    // Очистка поиска
    $('#clearSearch').on('click', function() {
        $('#treeSearch').val('');
        performSearch('');
        $('.no-result-message').remove();
    });
    
    // Сохранение состояния в localStorage
    function saveTreeState() {
        var openNodes = [];
        $('.tree-node.expanded').each(function() {
            var $li = $(this).closest('li');
            if ($li.hasClass('tree-category')) {
                openNodes.push('cat_' + $li.data('category-id'));
            } else if ($li.hasClass('tree-device')) {
                openNodes.push('dev_' + $li.data('device-id'));
            }
        });
        localStorage.setItem('accessCategoryTreeState', JSON.stringify(openNodes));
    }
    
    function loadTreeState() {
        var savedState = localStorage.getItem('accessCategoryTreeState');
        if (savedState) {
            var openNodes = JSON.parse(savedState);
            openNodes.forEach(function(nodeId) {
                if (nodeId.indexOf('cat_') === 0) {
                    var catId = nodeId.replace('cat_', '');
                    var $cat = $('.tree-category[data-category-id="' + catId + '"]');
                    if ($cat.length) {
                        var $children = $cat.children('ul.tree-children');
                        if ($children.is(':hidden')) {
                            $children.show();
                            $cat.children('.tree-node-category').find('.tree-toggle .glyphicon')
                                .removeClass('glyphicon-chevron-right')
                                .addClass('glyphicon-chevron-down');
                            $cat.children('.tree-node-category').addClass('expanded');
                        }
                    }
                } else if (nodeId.indexOf('dev_') === 0) {
                    var devId = nodeId.replace('dev_', '');
                    var $dev = $('.tree-device[data-device-id="' + devId + '"]');
                    if ($dev.length) {
                        var $children = $dev.children('ul.tree-children');
                        if ($children.is(':hidden')) {
                            $children.show();
                            $dev.children('.tree-node-device').find('.tree-toggle .glyphicon')
                                .removeClass('glyphicon-chevron-right')
                                .addClass('glyphicon-chevron-down');
                            $dev.children('.tree-node-device').addClass('expanded');
                        }
                    }
                }
            });
        }
    }
    
    // Сохраняем состояние при клике
    $(document).on('click', '.tree-node', function() {
        saveTreeState();
    });
    
    // Загружаем сохраненное состояние
    setTimeout(loadTreeState, 100);
    
    // Escape для очистки поиска
    $(document).on('keyup', function(e) {
        if (e.key === 'Escape') {
            $('#treeSearch').val('');
            performSearch('');
        }
    });
    
});
</script>