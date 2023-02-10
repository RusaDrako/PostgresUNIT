<?php

namespace RusaDrako\pgunit;

use Yii;

/**
 * Класс для тестирования SQL-функций
 */
class testSQL {

    /**
     * @var \yii\db\Connection БД
     */
    public $db;
    /**
     * @var \yii\db\Transaction|null Текущая транзакция
     */
    public $trn;
    /**
     * @var int Счётчик тестов
     */
    public $countTest = 0;
    /**
     * @var int Счётчик проверок
     */
    public $countAssert = 0;
    /**
     * @var array Список тестов
     */
    public $tests = [];
    /**
     * @var array Текущий тест
     */
    public $testNow;

    public function __construct() {
        $this->db = Yii::$app->db_test;
        $this->bMessage();
        $this->bMessage('Запуск тестов: ' . get_class($this));
        $this->bMessage();
        foreach (get_class_methods($this) as $v) {
            if (substr($v,0,4) === 'test') {
                $this->tests[] = $v;
            }
        }
    }

    public function __destruct() {
        $this->bMessage();
        $this->bMessage();
        $this->bMessage("Успешно выполнено.");
        $this->bMessage("Проверок: {$this->countAssert}");
        $this->bMessage("Тестов: {$this->countTest}/" . count($this->tests));
        $this->bMessage();
    }

    /**
     * Выводит текстовое сообщение с последующим переводом строки
     * @param string $text Tекстовое сообщение
     * @return void
     */
    public function bMessage(string $text = '') {
        echo $text;
        echo "\r\n";
    }

    /**
     * Проверяет идентичность переданных значений
     * @param mixed $v1 Первое сравниваемое значение
     * @param mixed $v2 Второе сравниваемое значение
     * @param string $errText Сообщение об ошибке
     * @return void
     * @throws \Exception
     */
    public function assert($v1, $v2, $errText = NULL) {
        if ($v1 === $v2)
        {
            if (!($this->countAssert % 20))
            {
                $this->bMessage();
            }
            echo ' ✓';
            $this->countAssert++;
            return;
        }
        $this->bMessage(' ☓');
        $this->bMessage();
        $arr = debug_backtrace();
        $prefix = get_class($this) . " -> {$this->testNow}(): ";
        $text =  "{$prefix}Несовпадение переменных " . var_export($v1, 1) . " и " . var_export($v2, 1) . "\r\n\r\n{$arr[0]['file']} ({$arr[0]['line']})";
        $this->bMessage(' ✕ ' . $text);
        throw new \Exception ($prefix . ($errText ?? $text));
    }

    /**
     * Запускает выполнение всех тестов
     * @return void
     */
    public final function run() {
        foreach ($this->tests as $v) {
            # Запоминаем текущий тест
            $this->testNow = $v;
            # Выполняем тест
            $this->startTest();
            # Увеличиваем счётчик успешных тестов
            $this->countTest++;
        }
    }

    /**
     * Выполняет подготовку и запуск теста
     * @return void
     */
    public final function startTest() {
        # Включаем транзакцию (не трогать, не удалять, а то можно всё похерить)
        $this->trn = $this->db->beginTransaction();
        # Выполняем дополнительные настройки класса
        $this->setSettingsClass();
        # Выполняем функцию перед тестом
        $this->beforeTest();
        # Получаем текущий тест
        $test = $this->testNow;
        # Выполняем тест
        $this->$test();
        # Выполняем функцию после теста
        $this->afterTest();
        # Откатываем трнзакцию (не трогать, не удалять, а то можно всё похерить)
        $this->trn->rollBack();
        $this->trn = null;
    }

    /**
     * Выполняет дополнительные настройки класса перед тестированием
     * @return void
     */
    public function setSettingsClass() {}

    /**
     * Выполняет дополнительные действия перед тестированием
     * @return void
     */
    public function beforeTest() {}

    /**
     * Выполняет дополнительные действия после тестированием
     * @return void
     */
    public function afterTest() {}

    /**
     * Удаляет все констрейны указанной таблицы (для более простого добавления тестовых данных)
     * @param string $tableName Имя таблицы
     * @return void
     * @throws \yii\db\Exception
     */
    public final function dropConstraint($tableName) {
        $sql = <<<SQL
SELECT con.conname, con.contype/*, con.**/
       FROM pg_catalog.pg_constraint con
            INNER JOIN pg_catalog.pg_class rel
                       ON rel.oid = con.conrelid
            INNER JOIN pg_catalog.pg_namespace nsp
                       ON nsp.oid = connamespace
       WHERE rel.relname = '{$tableName}';
SQL;
        $result = $this->db->createCommand($sql)->queryAll();
//        var_dump($result);
        foreach ($result as $v) {
            if ($v['contype'] != 'p') {
                $sql = <<<SQL
ALTER TABLE {$tableName} DROP CONSTRAINT "{$v['conname']}";
SQL;
                $this->db->createCommand($sql)->execute();
            }
        }
    }

    /**
     * Выполняет добавление данных в таблицу
     * @param string $tableName Имя таблицы
     * @param array $arrData Массив данных [['column_name'=>'column_value',...],...]
     * @param string $returnColumns Имена столбцов из которых надо вернуть данные
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     */
    public final function addData($tableName, $arrData, $returnColumns = null) {
        $strKeys = '("' . implode( '", "', array_keys($arrData[0])) . '")';
        $values = [];
        foreach ($arrData as $v) {
            $value = [];
            foreach ($v as $v2) {
                if ($v2 === NULL)
                {
                    $value[] = 'NULL';
                }
                elseif (is_numeric($v2))
                {
                    $value[] = $v2;
                }
                else
                {
                    $value[] = "'{$v2}'";
                }
            }
            $values[] = '(' . implode(", ", $value) . ')';
        }
        $strValues = implode(",\r\n    ", $values);
        $returnColumns = $returnColumns
            ? "RETURNING {$returnColumns}"
            : '';
        $sql = <<<SQL
INSERT INTO "{$tableName}"
    {$strKeys}
VALUES
    {$strValues}
    {$returnColumns}
SQL;
        return $this->db->createCommand($sql)->queryAll();
    }

}
