<?php

namespace RusaDrako\pgunit;

/**
 * Класс для тестирования SQL-триггеров
 */
class testSQLtriggers extends testSQL {

    /**
     * @var string Имя таблицы
     */
    public $tableName;
    /**
     * @var string Имя тестируемого триггера
     */
    public $triggerName;

    public function __construct() {
        parent::__construct();
        # Получаем имя триггера из имени класса
        $arr = explode('\\', get_class($this));
        $this->triggerName = array_pop($arr);
    }


    /**
     * Выполняет дополнительные настройки класса перед тестированием
     * @return void
     */
    public function setSettingsClass()
    {
        # Отключаем все триггеры таблицы
        $sql = <<<SQL
ALTER TABLE {$this->tableName} DISABLE TRIGGER ALL;
SQL;
        $this->db->createCommand($sql)->execute();
        # Включаем тестируемый триггер
        $sql = <<<SQL
ALTER TABLE {$this->tableName} ENABLE TRIGGER {$this->triggerName};
SQL;
        $this->db->createCommand($sql)->execute();
    }

}
