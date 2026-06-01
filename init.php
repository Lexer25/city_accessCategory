<?php defined('SYSPATH') or die('No direct script access.');
defined('DOOR_VERSION') OR define('DOOR_VERSION', '1.0.4.3');

Kohana::$config->load('menu')
    ->set('accessCategory', array(
        'title' => 'Категории доступа',
        'url' => 'accessCategory',
        'icon' => 'fa-cog',
        'order' => 100,
       
    ));