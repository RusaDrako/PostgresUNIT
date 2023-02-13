# PGUNIT

Библиотека на базе Yii2 (php) для тестирования хранимых и триггерных функций.

Тест основан на незавершённой транзакции.

Соглашения:

- имя тестового класса должно соотвествовать имени тестируемого триггера или функции.
- тестовые методы должны начинаться со слова `test`: `testMethod1`, `test_method_1`



## Класс PG_UNIT_Test_Functions

Наследуемый класс, предназначеный для тестирования хранимых функций.



## Класс PG_UNIT_Test_Triggers

Наследуемый класс, предназначеный для тестирования триггеров.

Перед выполнением тестовой функции отключает все триггеры для указанной таблицы, и активирует триггер с именем взятым из имени класса. 



## Метод $this->assert()

Прадназначен для сравнения двух входящих переменных.

```PHP
$this->assert($v1, $v2, $errText);
```

**$v1** - первая переменная для сравнения
**$v1** - первая переменная для сравнения
**$errText** - текс ошибки (не обязательный)

В случае не совпадения переменных, генерируется ошибка с указанным текстом.



## Метод $this->dropConstraint()

Удаляет у указанной таблицы все констрейны кроме primery key.

```PHP
$this->dropConstraint($table_name);
```

**$table_name** - имя обрабатываемой таблицы



## Метод $this->addData()

Добавляет данные в указанную таблицу.

```PHP
$this->addData($table_name, $arrData, $returnColumns);
```

**$table_name** - имя обрабатываемой таблицы



## Пример теста

```PHP

<?php

namespace tests;

/**
 * Тест функции function_name()
 * Функция должна возвращать значение стобца 'value' из таблицы 'table_test' по ключу 'key', либо NULL
 */
class function_name extends \PG_UNIT_Test_Functions {

    /**
     * Тест 1 (ключ в таблице отсутствует)
     * @return void
     * @throws \yii\db\Exception
     */
    public function test_1() {
        # Проверяем функцию
        $sql = "SELECT function_name('TEST_1')";
        $result = $this->db->createCommand($sql)->queryScalar();
        # Проверяем результат на совпадение
        $this->assert($result, NULL, 'Ожидался NULL');
    }

    /**
     * Тест 2 (ключ задан)
     * @return void
     * @throws \yii\db\Exception
     */
    public function test_2() {
        # Добавляем тестовые данные
        $arrData = [
            [
                'key'     => 'TEST_1',
                'value'   => '888',
            ],
        ];
        $this->addData('table_test', $arrData);
        # Проверяем функцию
        $sql = "SELECT function_name('TEST_1')";
        $result = $this->db->createCommand($sql)->queryScalar();
        # Проверяем результат на совпадение
        $this->assert($result, '888', 'Ожидалось 888');
    }

}

```



## Запуск теста

```PHP
$obj = new \tests\function_name();
$obj->run();
```
