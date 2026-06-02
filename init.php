<?php defined('SYSPATH') or die('No direct script access.');
defined('DOOR_VERSION') OR define('DOOR_VERSION', '1.0.4.5');

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