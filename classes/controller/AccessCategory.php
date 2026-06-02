<?php defined('SYSPATH') or die('No direct script access.');

class Controller_AccessCategory extends Controller_Template { 

    public function before()
    {
        parent::before();
        $session = Session::instance();
    }
    
    public function action_index()
    {
        $acList = Model::factory('accessCategory')->getAccessCategoryList();
        $content = View::factory('accessCategory/index', array(
            'acList' => $acList,
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
				$groupedDevices = $this->groupByDevice($assignedPointsWithData);
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
				//echo Debug::vars('134', $assignedPointsWithData);//exit;
				//echo Debug::vars('135', $this->groupByDevice($assignedPointsWithData));exit;
				$groupedDevices = $this->groupByDevice($assignedPointsWithData);
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
			private function groupByDevice($inputArray, $idKey = 'id_dev', $nameKey = 'name', $timezoneKey = 'id_timezone', $removeDuplicates = true) {
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
}
