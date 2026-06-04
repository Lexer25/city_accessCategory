<?php defined('SYSPATH') or die('No direct script access.');
defined('DOOR_VERSION') OR define('DOOR_VERSION', '1.0.5.0');

Kohana::$config->load('menu')
    ->set('accessCategory', array(
        'title' => 'Категории доступа',
        'url' => 'accessCategory',
        'icon' => 'fa-cog',
        'order' => 100,
       
    ));
	
	// Добавьте этот маршрут после основных
Route::set('accessCategory_editTimezones', 'accessCategory/editTimezones/<id>/<device_id>', array('id' => '\d+', 'device_id' => '\d+'))
    ->defaults(array(
        'controller' => 'AccessCategory',
        'action' => 'editTimezones',
    ));
	
	// AJAX маршруты
Route::set('accessCategory_addAccessPoints', 'accessCategory/addAccessPoints')
    ->defaults(array('controller' => 'AccessCategory', 'action' => 'addAccessPoints'));

Route::set('accessCategory_removeAccessPoints', 'accessCategory/removeAccessPoints')
    ->defaults(array('controller' => 'AccessCategory', 'action' => 'removeAccessPoints'));

Route::set('accessCategory_getCategoryData', 'accessCategory/getCategoryData/<id>', array('id' => '\d+'))
    ->defaults(array('controller' => 'AccessCategory', 'action' => 'getCategoryData'));
	
	// AJAX маршруты для динамической подгрузки
Route::set('accessCategory_getDevices', 'accessCategory/getCategoryDevices/<id>', array('id' => '\d+'))
    ->defaults(array('controller' => 'AccessCategory', 'action' => 'getCategoryDevices'));

Route::set('accessCategory_getTimezones', 'accessCategory/getDeviceTimezones/<category_id>/<device_id>', array('category_id' => '\d+', 'device_id' => '\d+'))
    ->defaults(array('controller' => 'AccessCategory', 'action' => 'getDeviceTimezones'));