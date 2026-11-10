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
		
		$sql='SELECT an.id_accessname, an.name, an.time_stamp, count(ssa.id_pep) as peoplecount FROM accessname an
				left join ss_accessuser ssa on an.id_accessname=ssa.id_accessname
				group by  an.id_accessname, an.name, an.time_stamp, ssa.id_accessname';

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
        $sql = 'SELECT an.id_accessname, an.name, an.time_stamp, count(ssa.id_pep) as peoplecount
                FROM accessname an 
				left join ss_accessuser ssa on an.id_accessname=ssa.id_accessname
                WHERE an.id_accessname = ' . intval($id).'
				group by an.id_accessname, an.name, an.time_stamp';

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
    
  public function saveAccessPoints($id_accessname, $selectedPoints)
    {
        try {
            // Начинаем транзакцию
            DB::query(Database::DELETE, "DELETE FROM access WHERE id_accessname = " . intval($id_accessname))
                ->execute(Database::instance('fb'));
            
            // Добавляем выбранные точки
            if (!empty($selectedPoints)) {
                foreach ($selectedPoints as $id_dev) {
					
					$newId = DB::query(Database::SELECT, 'SELECT GEN_ID(GEN_ACCESS_ID, 1) as gen FROM RDB$DATABASE')
                    ->execute($db)
                    ->get('GEN');
					
					
                    $sql = "INSERT INTO access (id_access, id_db, id_accessname, id_dev, id_timezone) 
                            VALUES (" . intval($newId) . ",1, " . intval($id_accessname) . ", " . intval($id_dev) . ", NULL)";
        Kohana::$log->add(Log::DEBUG, 'sql ' . $sql);            
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
     * Выдать доступ к категории доступа в таблицу ACCESSUSER.
     *
     * Делаются две вставки:
     *  1) для служебного пользователя id_pep = 1;
     *  2) для текущего авторизованного пользователя — id_pep из параметра,
     *     а если он не передан, то по логину из Auth (people.login).
     * Уже существующие связи (id_pep, id_accessname) пропускаются.
     *
     * @param  int $id_pep        id_pep текущего администратора (может быть NULL)
     * @param  int $id_accessname id_accessname категории доступа
     * @return bool
     */
    public function addACCESSUSER($id_pep = NULL, $id_accessname = NULL)
    {
        $id_accessname = (int) $id_accessname;

        if ($id_accessname <= 0)
        {
            Kohana::$log->add(Log::ERROR, 'addACCESSUSER: не передан id_accessname');
            return false;
        }

        if (empty($id_pep))
        {
            $id_pep = $this->getCurrentIdPep();
        }

        $id_pep = (int) $id_pep;

        // id_pep = 1 — служебный пользователь, доступ получает всегда
        $pepIds = array(1);

        if ($id_pep > 0 AND $id_pep !== 1)
        {
            $pepIds[] = $id_pep;
        }

        $db = Database::instance('fb');

        $result = true;

        foreach ($pepIds as $pepId)
        {
            try
            {
                // Проверяем, нет ли уже такой связи: UNQ_ACCESSUSER не даст вставить дубль
                $sql = "SELECT id_pep FROM accessuser
                        WHERE id_pep = " . $pepId . "
                        AND id_accessname = " . $id_accessname;

                $exists = DB::query(Database::SELECT, $sql)
                    ->execute($db)
                    ->as_array();

                if ( ! empty($exists))
                {
                    Kohana::$log->add(Log::DEBUG, 'addACCESSUSER: связь уже есть, id_pep=' . $pepId . ', id_accessname=' . $id_accessname);
                    continue;
                }

                // ID_ACCESSUSER генерирует BEFORE INSERT триггер ACCESSUSER_CREATE_ID (GEN_ACCESSUSER_ID)
                $sql = "INSERT INTO accessuser (id_db, id_pep, id_accessname)
                        VALUES (1, " . $pepId . ", " . $id_accessname . ")";

                Kohana::$log->add(Log::DEBUG, 'addACCESSUSER sql ' . $sql);

                DB::query(Database::INSERT, $sql)
                    ->execute($db);
            }
            catch (Exception $e)
            {
                Kohana::$log->add(Log::ERROR, 'addACCESSUSER: id_pep=' . $pepId . ', id_accessname=' . $id_accessname . ' — ' . $e->getMessage());
                $result = false;
            }
        }

        return $result;
    }

    /**
     * id_pep текущего авторизованного пользователя.
     *
     * Драйвер Auth (File) хранит в сессии логин, поэтому id_pep ищем
     * в people по колонке login. Если драйвер вернёт объект/массив с id_pep,
     * берём значение из него.
     *
     * @return int|null
     */
    private function getCurrentIdPep()
    {
        if ( ! class_exists('Auth') OR ! Auth::instance()->logged_in())
        {
            return NULL;
        }

        $user = Auth::instance()->get_user();

        if (is_object($user) AND isset($user->id_pep))
        {
            return (int) $user->id_pep;
        }

        if (is_array($user) AND isset($user['id_pep']))
        {
            return (int) $user['id_pep'];
        }

        // Драйвер File отдаёт логин
        $login = is_string($user) ? trim($user) : '';

        if ($login === '')
        {
            return NULL;
        }

        $loginForDb = addslashes($login);

        // TRIM/RTRIM драйвер ODBC (Gemini InterBase) не поддерживает — только UPPER
        $sql = "SELECT FIRST 1 id_pep FROM people
                WHERE UPPER(login) = UPPER('" . $loginForDb . "')";

        try
        {
            $query = DB::query(Database::SELECT, $sql)
                ->execute(Database::instance('fb'))
                ->as_array();

            if ( ! empty($query) AND isset($query[0]['ID_PEP']))
            {
                return (int) $query[0]['ID_PEP'];
            }

            Kohana::$log->add(Log::ERROR, 'addACCESSUSER: в people не найден id_pep для логина ' . $login);
        }
        catch (Exception $e)
        {
            Kohana::$log->add(Log::ERROR, 'addACCESSUSER: не удалось определить id_pep по логину ' . $login . ' — ' . $e->getMessage());
        }

        return NULL;
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
			Kohana::$log->add(Log::DEBUG, '331 timezones ' . Debug::vars($timezones));	
			try {
				$db = Database::instance('fb');
				
				// Удаляем существующие связи для этой точки - этого делать нельзя! Это вызовет формироание очереди на удаление, что займет очень много времени.
				DB::query(Database::DELETE, "DELETE FROM access 
					WHERE id_accessname = " . intval($categoryId) . " 
					AND id_dev = " . intval($deviceId))
					->execute($db);
				
				// Добавляем новые связи
				if (!empty($timezones)) {
					foreach ($timezones as $timezoneId) {
						if ($timezoneId === null || $timezoneId === '') continue ;
						
		 
						
						$sql = "INSERT INTO access (id_db, id_accessname, id_dev, id_timezone) 
								VALUES (1, " . intval($categoryId) . ", " . intval($deviceId) . ", " . intval($timezoneId) . ")";
		Kohana::$log->add(Log::DEBUG, '349 sql ' . $sql);			  
						DB::query(Database::INSERT, $sql)->execute($db);
					}
				} else {
					// Если временных зон нет, создаем запись с NULL временной зоной

					
					$sql = "INSERT INTO access (id_db, id_accessname, id_dev, id_timezone) 
							VALUES (1, " . intval($categoryId) . ", " . intval($deviceId) . ", NULL)";
					
					DB::query(Database::INSERT, $sql)->execute($db);
				}
				
				return true;
			} catch (Exception $e) {
				Kohana::$log->add(Log::ERROR, 'Error saving device timezones: ' . $e->getMessage());
				return false;
			}
		}
		
		/**
 * Добавление точек прохода в категорию
 */
public function addAccessPoints($categoryId, $points)
{
    try {
        $db = Database::instance('fb');
        
        foreach ($points as $deviceId) {
            // Проверяем, существует ли уже такая связь
            $checkSql = "SELECT COUNT(*) as cnt FROM access 
                         WHERE id_accessname = " . intval($categoryId) . " 
                         AND id_dev = " . intval($deviceId);
            
            $exists = DB::query(Database::SELECT, $checkSql)
                ->execute($db)
                ->get('cnt', 0);
            
            if ($exists == 0) {
                $newId = DB::query(Database::SELECT, 'SELECT GEN_ID(GEN_ACCESS_ID, 1) as gen FROM RDB$DATABASE;')
                    ->execute($db)
                    ->get('GEN');
                
                $sql = "INSERT INTO access (id_access, id_db, id_accessname, id_dev, id_timezone) 
                        VALUES (" . intval($newId) . ", 1, " . intval($categoryId) . ", " . intval($deviceId) . ", NULL)";
            
                DB::query(Database::INSERT, $sql)->execute($db);
            }
        }
        
        return true;
    } catch (Exception $e) {
        Kohana::$log->add(Log::ERROR, 'Error adding access points: ' . $e->getMessage());
        return false;
    }
}

		/**
		 * Удаление точек прохода из категории
		 */
		public function removeAccessPoints($categoryId, $points)
		{
			try {
				$db = Database::instance('fb');
				
				foreach ($points as $deviceId) {
					$sql = "DELETE FROM access 
							WHERE id_accessname = " . intval($categoryId) . " 
							AND id_dev = " . intval($deviceId);
					
					DB::query(Database::DELETE, $sql)->execute($db);
				}
				
				return true;
			} catch (Exception $e) {
				Kohana::$log->add(Log::ERROR, 'Error removing access points: ' . $e->getMessage());
				return false;
			}
		}

			// В модели Model_AccessCategory.php
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
					//$result[$id][$timezoneKey] = null;
				}
			}
			
			return $result;
		}
}
