<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_AccessCategory  extends Model
{
			public function getAccessCategoryList()
		{
			$sql = 'SELECT an.id_accessname, an.name, an.time_stamp, an.guid FROM accessname an';

			$query = DB::query(Database::SELECT, $sql)
				->execute(Database::instance('fb'))
				->as_array();
			return $this->convertToUtf8($query);
		}

		    /**
     * Преобразование ключей массива из верхнего регистра в нижний
     * и конвертация кодировки из Windows-1251 в UTF-8
     * 
     * @param mixed $data Данные для преобразования
     * @return mixed Преобразованные данные
     */
    private function convertToUtf8($data)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                // Преобразование ключа в нижний регистр
                $newKey = is_string($key) ? strtolower($key) : $key;
                
                if (is_array($value)) {
                    $result[$newKey] = $this->convertToUtf8($value);
                } elseif (is_string($value)) {
                    // Конвертация кодировки из Windows-1251 в UTF-8
                    $result[$newKey] = iconv('Windows-1251', 'UTF-8//IGNORE', $value);
                } else {
                    $result[$newKey] = $value;
                }
            }
            return $result;
        } elseif (is_string($data)) {
            return iconv('Windows-1251', 'UTF-8//IGNORE', $data);
        }
        return $data;
    }


	
}
	

