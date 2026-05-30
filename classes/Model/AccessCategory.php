<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_AccessCategory extends Model
{
    
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
        $sql = 'SELECT an.id_accessname, an.name, an.time_stamp, an.guid FROM accessname an';

        $query = DB::query(Database::SELECT, $sql)
            ->execute(Database::instance('fb'))
            ->as_array();
        
        return $this->convertToUtf8($query);
    }
    
    /**
     * Получить категорию доступа по ID (исправленная версия)
     */
    public function getAccessCategoryById($id)
    {
        // Способ 1:直接用 значение в запросе
        $sql = 'SELECT an.id_accessname, an.name, an.time_stamp, an.guid 
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
     * Получить категорию доступа по ID (альтернативный способ с параметром)
     */
    public function getAccessCategoryByIdParam($id)
    {
        // Способ 2: используем ? вместо :param
        $sql = 'SELECT an.id_accessname, an.name, an.time_stamp, an.guid 
                FROM accessname an 
                WHERE an.id_accessname = ?';
        
        $query = DB::query(Database::SELECT, $sql)
            ->param($id)  // параметр без имени, просто значение
            ->execute(Database::instance('fb'))
            ->as_array();
        
        if (count($query) > 0) {
            $result = $this->convertToUtf8($query);
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Обновить категорию доступа
     */
    public function updateAccessCategory($id, $name, $guid = null)
    {
        // Конвертируем название обратно в Windows-1251 для сохранения в БД
        $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        
        // Способ с прямым использованием значений
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
     * Обновить категорию доступа (безопасная версия с экранированием)
     */
    public function updateAccessCategorySafe($id, $name, $guid = null)
    {
        // Конвертируем название обратно в Windows-1251 для сохранения в БД
        $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        
        // Экранируем строки для безопасности
        $nameForDb = addslashes($nameForDb);
        $guid = addslashes($guid);
        
        $sql = "UPDATE accessname 
                SET name = '{$nameForDb}', 
                    guid = '{$guid}',
                    time_stamp = CURRENT_TIMESTAMP
                WHERE id_accessname = {$id}";
        
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
        // Конвертируем название обратно в Windows-1251 для сохранения в БД
        $nameForDb = iconv('UTF-8', 'Windows-1251//IGNORE', $name);
        
        // Генерируем GUID если не передан
        if (empty($guid)) {
            $guid = $this->generateGuid();
        }
        
        // Экранируем строки
        $nameForDb = addslashes($nameForDb);
        $guid = addslashes($guid);
        
        $sql = "INSERT INTO accessname (id_accessname, name, guid, time_stamp) 
                VALUES ((SELECT COALESCE(MAX(id_accessname), 0) + 1 FROM accessname), '{$nameForDb}', '{$guid}', CURRENT_TIMESTAMP)";
        
        try {
            DB::query(Database::INSERT, $sql)
                ->execute(Database::instance('fb'));
            
            return true;
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
        $sql = "DELETE FROM accessname WHERE id_accessname = " . intval($id);
        
        try {
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
}