<?php defined('SYSPATH') or die('No direct script access.');
defined('DOOR_VERSION') OR define('DOOR_VERSION', '2.0.0');

Kohana::$config->load('menu')
    ->set('accessCategory', array(
        'title' => 'Категории доступа',
        'url' => 'accessCategory',
        'icon' => 'fa-cog',
        'order' => 100,
       
    ));