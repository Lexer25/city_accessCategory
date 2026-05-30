<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_AccessCategory  extends Model
{
	
	
	
	
	/** получить список категорий доступа
	
	*/
	public function getAccessCategoryList()
	{
	$sql='select an.id_accessname, an.name, an.time_stamp, an.guid from accessname an';

		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		
		
		return $query;
	}

	
}
	

