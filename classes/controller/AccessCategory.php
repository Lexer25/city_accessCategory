<?php defined('SYSPATH') or die('No direct script access.');

class Controller_AccessCategory extends Controller_Template { 

public function before()
{
    parent::before();
    $session = Session::instance();

    // Передаем в шаблон флаг авторизации
    $this->is_admin = Auth::instance()->logged_in('admin');
    View::bind_global('is_admin', $this->is_admin);
    
    // ========== ВРЕМЕННОЕ ОТКЛЮЧЕНИЕ МЕТОДОВ ЗАПИСИ ==========
    // Глобальный флаг отключения записи (true - отключено, false - работает)
    $disable_write_actions = true;  // <-- Меняйте здесь для включения/отключения
    
    if ($disable_write_actions) {
        // Список методов, которые временно запрещены
        $disabled_actions = array(
            'add',
            'edit',
            'delete',
            'addAccessPoints',
            'removeAccessPoints',
            'editTimezones',
            'saveMatrixChanges',
        );
        
        $current_action = $this->request->action();
        
        if (in_array($current_action, $disabled_actions)) {
            if ($this->request->is_ajax()) {
                $this->auto_render = false;
                header('Content-Type: application/json');
                echo json_encode(array(
                    'success' => false, 
                    'error' => 'Сохранение временно отключено администратором'
                ));
                exit;
            }
            
            Session::instance()->set('message', 'Редактирование временно отключено администратором');
            Session::instance()->set('message_type', 'warning');
            $this->redirect('accessCategory');
        }
    }
    // ========== КОНЕЦ БЛОКА ==========
}
    
		public function action_index()
		{
			// Получаем режим отображения из GET или сессии
			$mode = $this->request->query('mode');
			if ($mode && in_array($mode, array('table', 'tree', 'matrix'))) {
				Session::instance()->set('access_category_view_mode', $mode);
			} else {
				$mode = Session::instance()->get('access_category_view_mode', 'tree');
			}
			
			// Для матричного режима устанавливаем full_width
			if ($mode == 'matrix') {
				$this->template->full_width = true;
			}
			
			$acList = Model::factory('accessCategory')->getAccessCategoryList();
			
			// Получаем данные для матрицы (нужны для матричного режима)
			$allPoints = Model::factory('accessCategory')->getAllAccessPoints();
			$categoryPointsMap = array();
			foreach ($acList as $category) {
				$catId = $category['id_accessname'];
				$categoryPointsMap[$catId] = Model::factory('accessCategory')->getAssignedAccessPointsIds($catId);
			}
			
			$content = View::factory('accessCategory/index', array(
				'acList' => $acList,
				'view_mode' => $mode,
				'allPoints' => $allPoints,
				'categoryPointsMap' => $categoryPointsMap,
				'is_admin' => $this->is_admin,
			));
			
			$this->template->content = $content;
		}
					  /**
		 * Редактирование категории доступа
		 */
		public function action_edit()
		{

			//$this->template->full_width = true;
			$id = $this->request->param('id');
			
			if ($id === NULL) {
				$this->redirect('accessCategory');
			}
			
			// Получаем данные категории
			$category = Model::factory('accessCategory')->getAccessCategoryById($id);
			
			if (empty($category)) {
				$this->redirect('accessCategory');
			}
			
			// Получаем все точки прохода
			$allPoints = Model::factory('accessCategory')->getAllAccessPoints();
			
			// Получаем ID уже привязанных точек
			$assignedPoints = Model::factory('accessCategory')->getAssignedAccessPointsIds($id);
			$assignedPointsWithData = Model::factory('accessCategory')->getAccessPointsByCategoryId($id);
					
			$groupedDevices = Model::factory('AccessCategory')->groupByDevice($assignedPointsWithData);

				// Получаем список временных зон
				$timezones = Model::factory('accessCategory')->getTimezonesList();
				$timezonesMap = array();
				foreach ($timezones as $tz) {
					$timezonesMap[Arr::get($tz, 'id_timezone')] = Arr::get($tz, 'name');
				}

				// Группируем timezone по id_dev
				$groupedTimezones = array();
				foreach ($assignedPointsWithData as $assigned) {
					$devId = Arr::get($assigned, 'id_dev');
					$tzId = Arr::get($assigned, 'id_timezone');
					if (!empty($tzId)) {
						if (!isset($groupedTimezones[$devId])) {
							$groupedTimezones[$devId] = array();
						}
						if (!in_array($tzId, $groupedTimezones[$devId])) {
							$groupedTimezones[$devId][] = $tzId;
						}
					}
				}

			// Обработка POST запроса
			if ($this->request->method() == HTTP_Request::POST) {
				$post = $this->request->post();
				
				$name = Arr::get($post, 'name');
				$guid = Arr::get($post, 'guid');
				
				$selectedPointsStr = Arr::get($post, 'access_points', '');
				$selectedPoints = !empty($selectedPointsStr) ? explode(',', $selectedPointsStr) : array();

				
				// Валидация
				$errors = array();
				if (empty($name)) {
					$errors['name'] = __('Название категории обязательно');
				}
				
				if (empty($errors)) {
					// Обновляем категорию
					$result = Model::factory('accessCategory')->updateAccessCategory($id, $name, $guid);
					
					// Сохраняем точки прохода
					$pointsResult = Model::factory('accessCategory')->saveAccessPoints($id, $selectedPoints);
					
					if ($result && $pointsResult) {
						Session::instance()->set('message', __('Категория доступа успешно обновлена'));
						Session::instance()->set('message_type', 'success');
					} else {
						Session::instance()->set('message', __('Ошибка при обновлении категории доступа'));
						Session::instance()->set('message_type', 'danger');
					}
					
					$this->redirect('accessCategory');
				}
				
				// Если есть ошибки, показываем форму с ошибками
				
				$content = View::factory('accessCategory/edit', array(
					'category' => $category,
					'allPoints' => $allPoints,
					'assignedPoints' => $assignedPoints,
					'assignedPointsWithData' => $assignedPointsWithData, 
					'groupedDevices' => $groupedDevices, 
					 'groupedTimezones' => $groupedTimezones,  // Добавьте эту строку
					 'timezonesMap' => $timezonesMap,  // Добавьте эту строку
					'errors' => $errors,
					'post' => $post,
				));
			} else {
				// GET запрос - показываем форму
				
				
				$content = View::factory('accessCategory/edit', array(
					'category' => $category,
					'allPoints' => $allPoints,
					'assignedPoints' => $assignedPoints,
					'assignedPointsWithData' => $assignedPointsWithData,
					'groupedDevices' => $groupedDevices,
					 'groupedTimezones' => $groupedTimezones,  // Добавьте эту строку
					 'timezonesMap' => $timezonesMap,  // Добавьте эту строку
					'errors' => array(),
					'post' => array(),
				));
			}
			
			$this->template->content = $content;
		}
		
		
	

			/**
			 * Группирует массив устройств по id_dev
			 *
			 * @param array $inputArray Исходный массив
			 * @param string $idKey Ключ для группировки (по умолчанию 'id_dev')
			 * @param string $nameKey Ключ с названием (по умолчанию 'name')
			 * @param string $timezoneKey Ключ с таймзоной (по умолчанию 'id_timezone')
			 * @param bool $removeDuplicates Удалять дубликаты таймзон (по умолчанию true)
			 * @return array Сгруппированный массив
			 */
			public function groupByDevice($inputArray, $idKey = 'id_dev', $nameKey = 'name', $timezoneKey = 'id_timezone', $removeDuplicates = true) {
				$result = [];
				
				foreach ($inputArray as $item) {
					$id = $item[$idKey];
					
					if (!isset($result[$id])) {
						$result[$id] = [
							$nameKey => $item[$nameKey],
							$timezoneKey => []
						];
					}
					
					if ($item[$timezoneKey] !== null) {
						$result[$id][$timezoneKey][] = $item[$timezoneKey];
						
						if ($removeDuplicates) {
							$result[$id][$timezoneKey] = array_values(array_unique($result[$id][$timezoneKey]));
						}
					} else {
						$result[$id][$timezoneKey] = null;
					}
				}
				
				return $result;
			}



	
    
    /**
     * Добавление новой категории доступа
     */
    public function action_add()
    {
        // Обработка POST запроса
        if ($this->request->method() == HTTP_Request::POST) {
            $post = $this->request->post();
            
            $name = Arr::get($post, 'name');
            $guid = Arr::get($post, 'guid');
            
            // Валидация
            $errors = array();
            if (empty($name)) {
                $errors['name'] = __('Название категории обязательно');
            }
            
            if (empty($errors)) {
                // Добавляем категорию
                $result = Model::factory('accessCategory')->addAccessCategory($name, $guid);
                
                if ($result) {
                    Session::instance()->set('message', __('Категория доступа успешно добавлена'));
                    Session::instance()->set('message_type', 'success');
                } else {
                    Session::instance()->set('message', __('Ошибка при добавлении категории доступа'));
                    Session::instance()->set('message_type', 'danger');
                }
                
                $this->redirect('accessCategory');
            }
            
            // Если есть ошибки, показываем форму с ошибками
            $content = View::factory('accessCategory/add', array(
                'errors' => $errors,
                'post' => $post,
            ));
        } else {
            // GET запрос - показываем форму
            $content = View::factory('accessCategory/add', array(
                'errors' => array(),
                'post' => array(),
            ));
        }
        
        $this->template->content = $content;
    }
    
    /**
     * Удаление категории доступа
     */
    public function action_delete()
    {
        $id = $this->request->param('id');
        
        if ($id !== NULL) {
            $result = Model::factory('accessCategory')->deleteAccessCategory($id);
            
            if ($result) {
                Session::instance()->set('message', __('Категория доступа успешно удалена'));
                Session::instance()->set('message_type', 'success');
            } else {
                Session::instance()->set('message', __('Ошибка при удалении категории доступа'));
                Session::instance()->set('message_type', 'danger');
            }
        }
        
        $this->redirect('accessCategory');
    }
    

	
	/**
		 * Редактирование временных зон для точки прохода
		 */
		public function action_editTimezones()
		{

			$categoryId = (int)$this->request->param('id');
			$deviceId = (int)$this->request->param('device_id');
			
			if (!$categoryId || !$deviceId) {
				$this->redirect('accessCategory');
			}
			
			// Получаем данные категории
			$category = Model::factory('accessCategory')->getAccessCategoryById($categoryId);
			if (empty($category)) {
				$this->redirect('accessCategory');
			}
			
			// Получаем данные точки прохода
			$device = Model::factory('accessCategory')->getAccessPointById($deviceId);
			if (empty($device)) {
				$this->redirect('accessCategory/edit/' . $categoryId);
			}
			
			// Получаем все временные зоны
			$allTimezones = Model::factory('accessCategory')->getTimezonesList();
			
			// Получаем текущие временные зоны для этой точки
			$selectedTimezones = Model::factory('accessCategory')->getDeviceTimezones($categoryId, $deviceId);
			
			// Обработка POST запроса
			if ($this->request->method() == HTTP_Request::POST) {
				   
				$post = $this->request->post();
				$selectedTimezonesPost = Arr::get($post, 'timezones', array());
				
				// Сохраняем временные зоны
				$result = Model::factory('accessCategory')->saveDeviceTimezones($categoryId, $deviceId, $selectedTimezonesPost);
				
				if ($result) {
					Session::instance()->set('message', __('Временные зоны успешно сохранены'));
					Session::instance()->set('message_type', 'success');
				} else {
					Session::instance()->set('message', __('Ошибка при сохранении временных зон'));
					Session::instance()->set('message_type', 'danger');
				}
				
				$this->redirect('accessCategory/edit/' . $categoryId);
			}
			
			$content = View::factory('accessCategory/editTimezones', array(
				'category' => $category,
				'device' => $device,
				'allTimezones' => $allTimezones,
				'selectedTimezones' => $selectedTimezones,
				'categoryId' => $categoryId,
				'deviceId' => $deviceId,
			));
			
			$this->template->content = $content;
		}
		
/**
 * Добавление точек прохода в категорию (AJAX)
 */
public function action_addAccessPoints()
{
    
	 $this->auto_render = false;
    header('Content-Type: application/json');
    
    if ($this->request->method() != HTTP_Request::POST) {
        echo json_encode(array('success' => false, 'error' => 'Invalid request method'));
        return;
    }
    
    // Получаем параметры и явно преобразуем в целые числа
    $categoryId = (int)$this->request->post('category_id');
    $points = $this->request->post('points');
    
    // Проверяем, что points - массив
    if (!is_array($points)) {
        $points = array();
    }
    
    // Преобразуем все ID точек в целые числа
    $points = array_map('intval', $points);
    
    if ($categoryId <= 0 || empty($points)) {
        echo json_encode(array('success' => false, 'error' => 'Invalid parameters: category_id=' . $categoryId . ', points=' . print_r($points, true)));
        return;
    }
    
    $result = Model::factory('accessCategory')->addAccessPoints($categoryId, $points);
    
    echo json_encode(array('success' => $result));
}

/**
 * Удаление точек прохода из категории (AJAX)
 */
public function action_removeAccessPoints()
{
    $this->auto_render = false;
    header('Content-Type: application/json');
    
    if ($this->request->method() != HTTP_Request::POST) {
        echo json_encode(array('success' => false, 'error' => 'Invalid request method'));
        return;
    }
    
    // Получаем параметры и явно преобразуем в целые числа
    $categoryId = (int)$this->request->post('category_id');
    $points = $this->request->post('points');
    
    // Проверяем, что points - массив
    if (!is_array($points)) {
        $points = array();
    }
    
    // Преобразуем все ID точек в целые числа
    $points = array_map('intval', $points);
    
    if ($categoryId <= 0 || empty($points)) {
        echo json_encode(array('success' => false, 'error' => 'Invalid parameters'));
        return;
    }
    
    $result = Model::factory('accessCategory')->removeAccessPoints($categoryId, $points);
    
    echo json_encode(array('success' => $result));
}

/**
 * AJAX: получить точки прохода для категории
 */
public function action_getCategoryDevices()
{
    $this->auto_render = false;
    header('Content-Type: application/json');
    
    $categoryId = (int)$this->request->param('id');
    if (!$categoryId) {
        echo json_encode(['error' => 'Invalid category ID']);
        return;
    }
    
    $pointsRaw = Model::factory('accessCategory')->getAccessPointsByCategoryId($categoryId);
    $grouped = Model::factory('AccessCategory')->groupByDevice($pointsRaw);
    
    // Формируем массив для JSON
    $devices = [];
    foreach ($grouped as $deviceId => $deviceData) {
        $devices[] = [
            'id' => $deviceId,
            'name' => $deviceData['name'],
            'timezone_count' => is_array($deviceData['id_timezone']) ? count($deviceData['id_timezone']) : 0,
            'has_timezones' => !empty($deviceData['id_timezone'])
        ];
    }
    
    echo json_encode(['success' => true, 'devices' => $devices]);
}

/**
 * AJAX: получить временные зоны для устройства в категории
 */
public function action_getDeviceTimezones()
{
    $this->auto_render = false;
    header('Content-Type: application/json');
    
    $categoryId = (int)$this->request->param('category_id');
    $deviceId = (int)$this->request->param('device_id');
    
    if (!$categoryId || !$deviceId) {
        echo json_encode(['error' => 'Invalid parameters']);
        return;
    }
    
    $timezoneIds = Model::factory('accessCategory')->getDeviceTimezones($categoryId, $deviceId);
    $allTimezones = Model::factory('accessCategory')->getTimezonesList();
    $timezonesMap = [];
    foreach ($allTimezones as $tz) {
        $timezonesMap[$tz['id_timezone']] = $tz['name'];
    }
    
    $timezones = [];
    foreach ($timezoneIds as $tzId) {
        $timezones[] = [
            'id' => $tzId,
            'name' => isset($timezonesMap[$tzId]) ? $timezonesMap[$tzId] : 'ID: ' . $tzId
        ];
    }
    
    echo json_encode(['success' => true, 'timezones' => $timezones]);
}


/**
 * Матрица: категории vs точки прохода
 */
public function action_matrix()
{
    $mode = $this->request->query('mode');
    if ($mode && in_array($mode, ['table', 'tree', 'matrix'])) {
        Session::instance()->set('access_category_view_mode', $mode);
    } else {
        $mode = Session::instance()->get('access_category_view_mode', 'matrix');
    }
    
    // Получаем все категории
    $categories = Model::factory('accessCategory')->getAccessCategoryList();
    
    // Получаем все точки прохода (устройства с ридером)
    $allPoints = Model::factory('accessCategory')->getAllAccessPoints();
    
    // Для каждой категории получаем ID привязанных точек
    $categoryPointsMap = [];
    foreach ($categories as $category) {
        $catId = $category['id_accessname'];
        $assignedIds = Model::factory('accessCategory')->getAssignedAccessPointsIds($catId);
        $categoryPointsMap[$catId] = $assignedIds;
    }
    
    $content = View::factory('accessCategory/index_matrix', array(
        'categories' => $categories,
        'allPoints' => $allPoints,
        'categoryPointsMap' => $categoryPointsMap,
        'view_mode' => $mode,
    ));
    $this->template->content = $content;
}
/**
 * AJAX: сохранение изменений матрицы (пакетное обновление)
 */
public function action_saveMatrixChanges()
{
    $this->auto_render = false;
    header('Content-Type: application/json');
    
    if (!$this->is_admin) {
        echo json_encode(['success' => false, 'error' => 'Доступ запрещён']);
        return;
    }
    
    if ($this->request->method() != HTTP_Request::POST) {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        return;
    }
    
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    $changes = isset($data['changes']) ? $data['changes'] : [];
    
    if (empty($changes)) {
        echo json_encode(['success' => true, 'message' => 'Нет изменений']);
        return;
    }
    
    try {
        // Получаем подключение к базе данных
        $db = Database::instance('fb'); // Без параметра, используем стандартное подключение
		
        
        // Или если подключение называется 'fb':
        // $db = Database::instance('fb');
        
        $db->begin();
        
        $savedCount = 0;
        
        foreach ($changes as $change) {
            $catId = (int)$change['category_id'];
            $pointId = (int)$change['point_id'];
            $shouldExist = (bool)$change['checked'];
            
            if ($shouldExist) {
                // Проверяем, существует ли уже связь
                $checkSql = "SELECT COUNT(*) as cnt FROM access WHERE id_accessname = {$catId} AND id_dev = {$pointId}";
                $result = DB::query(Database::SELECT, $checkSql)->execute($db);
                $exists = false;
                foreach ($result as $row) {
                    if ($row['CNT'] > 0) {
                        $exists = true;
                        break;
                    }
                }
                
                if (!$exists) {
                    // Получаем новый ID
                    $genResult = DB::query(Database::SELECT, 'SELECT GEN_ID(GEN_ACCESS_ID, 1) as gen FROM RDB$DATABASE')->execute($db);
                    $newId = 0;
                    foreach ($genResult as $row) {
                        $newId = $row['GEN'];
                        break;
                    }
                    
                    $insertSql = "INSERT INTO access (id_access, id_db, id_accessname, id_dev, id_timezone) 
                                  VALUES ({$newId}, 1, {$catId}, {$pointId}, NULL)";
                    DB::query(Database::INSERT, $insertSql)->execute($db);
                    $savedCount++;
                }
            } else {
                // Удаляем связь
                $deleteSql = "DELETE FROM access WHERE id_accessname = {$catId} AND id_dev = {$pointId}";
                DB::query(Database::DELETE, $deleteSql)->execute($db);
                $savedCount++;
            }
        }
        
        $db->commit();
        echo json_encode(['success' => true, 'saved_count' => $savedCount]);
        
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollback();
        }
        Kohana::$log->add(Log::ERROR, 'Matrix save error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}


}
