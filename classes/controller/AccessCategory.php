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
        $id = $this->request->param('id');
        
        if ($id === NULL) {
            $this->redirect('accessCategory');
        }
        
        // Получаем данные категории
        $category = Model::factory('accessCategory')->getAccessCategoryById($id);
        
        if (empty($category)) {
            $this->redirect('accessCategory');
        }
        
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
                // Обновляем категорию
                $result = Model::factory('accessCategory')->updateAccessCategory($id, $name, $guid);
                
                if ($result) {
                    // Устанавливаем сообщение об успехе
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
                'errors' => $errors,
                'post' => $post,
            ));
        } else {
            // GET запрос - показываем форму
            $content = View::factory('accessCategory/edit', array(
                'category' => $category,
                'errors' => array(),
                'post' => array(),
            ));
        }
        
        $this->template->content = $content;
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
    
    public function action_find()
    {
        $search = Arr::get($_GET, 'doorInfo');
        $_SESSION['doorEventsTimeFrom'] = Arr::get($_GET, 'timeFrom');
        $_SESSION['doorEventsTimeTo'] = Arr::get($_GET, 'timeTo');
        $result = Model::Factory('Door')->findIdDoor($search);
        
        if(count($result) > 0) {
            $content = View::Factory('door/select', array(
                'list' => $result,
            ));
            $this->template->content = $content;
        } else {
            $content = View::Factory('door/search');
            $this->template->content = $content;
        }
    }
    
    public function action_getInfo($id_door = false)
    {
        $id_door = $this->request->param('id');
        $_SESSION['menu_active'] = 'door';
        
        if ($id_door == NULL) $this->redirect('door/find');
        
        $door_data = Model::Factory('Door')->getDoor($id_door);
        $door_load_order = Model::Factory('Door')->getDoorLoadorder($id_door);
        $door_delete_order = Model::Factory('Door')->getDoorDeleteOrder($id_door);
        $door_events = Model::Factory('Event')->event_door($id_door);
        $key_for_door = Model::Factory('Door')->getKeysForDoor($id_door);
        $card_type = Model::Factory('Door')->getCardType();
        $enable_card_type = Model::Factory('Door')->getEnableCardType(Arr::get($door_data, 'ID_DEVTYPE'));

        $content = View::Factory('door/view', array(
            'door' => $door_data,
            'people_add' => $door_load_order,
            'people_del' => $door_delete_order,
            'events' => $door_events,
            'keys' => $key_for_door,
            'card_type' => $card_type,
            'enable_card_type' => $enable_card_type,
        ));
        
        $this->template->content = $content;
    }
}
