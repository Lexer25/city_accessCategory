<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_AccessCategory extends Model
{
    
	/**
	 * Получить список всех временных зон
	 */
	public function getTimezonesList()
	{
		$sql = 'SELECT id_timezone, name FROM timezone ORDER BY name';
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		
		return $this->convertToUtf8($query);
	}

	/**
	 * Получить название временной зоны по ID
	 */
	public function getTimezoneNameById($id)
	{
		$sql = 'SELECT name FROM timezone WHERE id_timezone = ' . intval($id);
		
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		
		if (count($query) > 0) {
			$result = $this->convertToUtf8($query);
			return $result[0]['NAME'];
		}
		
		return null;
	}
	
    /**
     * Преобразование ключей массива из верхнего регистра в нижний
     * и конвертация кодировки из Windows-1251 в UTF-8
     */
    private function convertToUtf8($data)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $newKey = is_string($key) ? strtolower($key) : $key;
                
                if (is_array($value)) {
                    $result[$newKey] = $this->convertToUtf8($value);
                } elseif (is_string($value)) {
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
    
    /**
     * Получить список категорий доступа
     */
    public function getAccessCategoryList()
    {
        $sql = 'SELECT an.id_accessname, an.name, an.time_stamp FROM accessname an';

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        return $this->convertToUtf8($query);
    }
    
    /**
     * Получить категорию доступа по ID
     */
    public function getAccessCategoryById($id)
    {
        $sql = 'SELECT an.id_accessname, an.name, an.time_stamp
                FROM accessname an 
                WHERE an.id_accessname = ' . intval($id);

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        if (count($query) > 0) {
            $result = $this->convertToUtf8($query);
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Получить список точек прохода по ID категории доступа
     */
    public function getAccessPointsByCategoryId($id_accessname)
    {
        $sql = 'SELECT d.id_dev, d.name, a.id_timezone 
                FROM access a
                JOIN device d ON a.id_dev = d.id_dev
                WHERE a.id_accessname = ' . intval($id_accessname);
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        return $this->convertToUtf8($query);
    }
    
    /**
     * Получить все точки прохода (устройства с ридером)
     */
    public function getAllAccessPoints()
    {
        $sql = 'SELECT d.id_dev, d.name 
                FROM device d 
                WHERE d.id_reader IS NOT NULL
                ORDER BY d.name';
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        return $this->convertToUtf8($query);
    }
    
    /**
     * Получить ID точек прохода, уже привязанных к категории
     */
    public function getAssignedAccessPointsIds($id_accessname)
    {
        $sql = 'SELECT a.id_dev 
                FROM access a
                WHERE a.id_accessname = ' . intval($id_accessname);
        
        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        $ids = array();
        foreach ($query as $row) {
            $ids[] = $row['ID_DEV'];
        }
        
        return $ids;
    }
    
    /**
     * Сохранить точки прохода для категории
     */
    public function saveAccessPoints($id_accessname, $selectedPoints)
    {
        try {
            // Начинаем транзакцию
            DB::query(Database::DELETE, "DELETE FROM access WHERE id_accessname = " . intval($id_accessname))
                ->execute(Database::instance('fb'));
            
            // Добавляем выбранные точки
            if (!empty($selectedPoints)) {
                foreach ($selectedPoints as $id_dev) {
                    $sql = "INSERT INTO access (id_access, id_db, id_accessname, id_dev, id_timezone) 
                            VALUES ((SELECT COALESCE(MAX(id_access), 0) + 1 FROM access), 1, " . intval($id_accessname) . ", " . intval($id_dev) . ", NULL)";
                    
                    DB::query(Database::INSERT, $sql)
                        ->execute(Database::instance('fb'));
                }
            }
            
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error saving access points: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Обновить категорию доступа
     */
    public function updateAccessCategory($id, $name, $guid = null)
    {
        $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        
        $sql = "UPDATE accessname 
                SET name = '{$nameForDb}', 
                    guid = '{$guid}',
                    time_stamp = CURRENT_TIMESTAMP
                WHERE id_accessname = " . intval($id);
        
        try {
            DB::query(Database::UPDATE, $sql)
                ->execute(Database::instance('fb'));
            
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error updating access category: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Добавить новую категорию доступа
     */
    public function addAccessCategory($name, $guid = null)
    {
        $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        
        if (empty($guid)) {
            $guid = $this->generateGuid();
        }
        
        $nameForDb = addslashes($nameForDb);
        $guid = addslashes($guid);
        
        $sql = "INSERT INTO accessname (name) 
                VALUES ('{$nameForDb}')";
        
        try {
            $result = DB::query(Database::INSERT, $sql)
                ->execute(Database::instance('fb'));
            
            // Получаем последний вставленный ID
            $lastId = DB::query(Database::SELECT, "SELECT MAX(id_accessname) as last_id FROM accessname")
                ->execute(Database::instance('fb'))
                ->as_array();
            
            return $lastId[0]['LAST_ID'];
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error adding access category: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Удалить категорию доступа
     */
    public function deleteAccessCategory($id)
    {
        try {
            // Сначала удаляем связи с точками прохода
            DB::query(Database::DELETE, "DELETE FROM access WHERE id_accessname = " . intval($id))
                ->execute(Database::instance('fb'));
            
            // Затем удаляем саму категорию
            $sql = "DELETE FROM accessname WHERE id_accessname = " . intval($id);
            
            DB::query(Database::DELETE, $sql)
                ->execute(Database::instance('fb'));
            
            return true;
        } catch (Exception $e) {
            Kohana::$log->add(Log::ERROR, 'Error deleting access category: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Генерация GUID
     */
    private function generateGuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
	
	/**
 * Получить данные точки прохода по ID
 */
public function getAccessPointById($deviceId)
{
    $sql = 'SELECT d.id_dev, d.name 
            FROM device d 
            WHERE d.id_dev = ' . intval($deviceId);
    
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    if (count($query) > 0) {
        $result = $this->convertToUtf8($query);
        return $result[0];
    }
    
    return null;
}

/**
 * Получить временные зоны для конкретной точки прохода в категории
 */
public function getDeviceTimezones($categoryId, $deviceId)
{
    $sql = "SELECT id_timezone FROM access 
            WHERE id_accessname = " . intval($categoryId) . " 
            AND id_dev = " . intval($deviceId) . "
            AND id_timezone IS NOT NULL";
    
    $query = DB::query(Database::SELECT, $sql)
        ->execute(Database::instance('fb'))
        ->as_array();
    
    $timezones = array();
    foreach ($query as $row) {
        $timezones[] = (int)$row['ID_TIMEZONE'];
    }
    
    return $timezones;
}

/**
 * Сохранить временные зоны для точки прохода в категории
 */
public function saveDeviceTimezones($categoryId, $deviceId, $timezones)
{
    try {
        $db = Database::instance('fb');
        
        // Удаляем существующие связи для этой точки
        DB::query(Database::DELETE, "DELETE FROM access 
            WHERE id_accessname = " . intval($categoryId) . " 
            AND id_dev = " . intval($deviceId))
            ->execute($db);
        
        // Добавляем новые связи
        if (!empty($timezones)) {
            foreach ($timezones as $timezoneId) {
                if (empty($timezoneId)) continue;
                
                $newId = DB::query(Database::SELECT, "SELECT COALESCE(MAX(id_access), 0) + 1 as new_id FROM access")
                    ->execute($db)
                    ->get('new_id', 1);
                
                $sql = "INSERT INTO access (id_access, id_db, id_accessname, id_dev, id_timezone) 
                        VALUES (" . intval($newId) . ", 1, " . intval($categoryId) . ", " . intval($deviceId) . ", " . intval($timezoneId) . ")";
                
                DB::query(Database::INSERT, $sql)->execute($db);
            }
        } else {
            // Если временных зон нет, создаем запись с NULL временной зоной
            $newId = DB::query(Database::SELECT, "SELECT COALESCE(MAX(id_access), 0) + 1 as new_id FROM access")
                ->execute($db)
                ->get('new_id', 1);
            
            $sql = "INSERT INTO access (id_access, id_db, id_accessname, id_dev, id_timezone) 
                    VALUES (" . intval($newId) . ", 1, " . intval($categoryId) . ", " . intval($deviceId) . ", NULL)";
            
            DB::query(Database::INSERT, $sql)->execute($db);
        }
        
        return true;
    } catch (Exception $e) {
        Kohana::$log->add(Log::ERROR, 'Error saving device timezones: ' . $e->getMessage());
        return false;
    }
}
}
