<?php defined('SYSPATH') or die('No direct script access.');
class Controller_AccessCategory  extends Controller_Template { 

	public function before()
	{
			
			parent::before();
			$session = Session::instance();
			
	}
	
	
	public function action_index()
	{
		$acList=Model::factory('accessCategory')->getAccessCategoryList();
		$content = View::factory('accessCategory/index', array(
			'acList'=>$acList,
			));
        $this->template->content = $content;
	}
	 
	 public function action_add()
	{
			$content = View::factory('accessCategory/add');
			$this->template->content = $content;
		}

		public function action_edit($id = false)
		{
			$id = $this->request->param('id');
			// Логика редактирования
		}

		public function action_delete($id = false)
		{
			$id = $this->request->param('id');
			// Логика удаления
		}


	 public function action_find()
	 {
	 
	 $search=Arr::get($_GET, 'doorInfo');
	 $_SESSION['doorEventsTimeFrom']=Arr::get($_GET, 'timeFrom');
		$_SESSION['doorEventsTimeTo']=Arr::get($_GET, 'timeTo');
	 $result=Model::Factory('Door')->findIdDoor($search);
		 if(count($result)>0)
		 {
			//$this->redirect('door/doorInfo/'.$result);
			$content=View::Factory('door/select', array(
			'list' => $result,
			
			));
		 $this->template->content = $content;
		 
		 } else {
		 $content=View::Factory('door/search');
		 $this->template->content = $content;
		 }
	 }
	
	
	
	public function action_getInfo ($id_door=false)
	{
			$id_door = $this->request->param('id');
			$_SESSION['menu_active']='door';
			if ($id_door == NULL) $this->redirect('door/find');
			$door_data=Model::Factory('Door')->getDoor($id_door);//информация о точке прохода
			$door_load_order=Model::Factory('Door')->getDoorLoadorder($id_door);//Список пользователей для загрузки в контроллер
			
	//		echo Debug::vars('54',$id_door, $door_load_order); exit;
			$door_delete_order=Model::Factory('Door') -> getDoorDeleteOrder($id_door);//Список пользователей для удаления из контроллера
			$door_events=Model::Factory('Event')->event_door($id_door);//информация о событиях точки прохода
			$key_for_door=Model::Factory('Door') -> getKeysForDoor($id_door);//карты для точки прохода, ФИО, сроки действия
			$card_type=Model::Factory('Door')->getCardType();// получить список типов карт
			$enable_card_type=Model::Factory('Door')->getEnableCardType(Arr::get($door_data, 'ID_DEVTYPE'));// получить список обслуживаемых типов карт
	

	$content=View::Factory('door/view', array(
			'door'	=> $door_data,
			'people_add'	=> $door_load_order,
			'people_del'	=> $door_delete_order,
			'events'	=> $door_events,
			'keys'=>$key_for_door,
			'card_type'=>$card_type,
			'enable_card_type'=>$enable_card_type,
			));
			
		$this->template->content = $content;
	}
	
	

}
