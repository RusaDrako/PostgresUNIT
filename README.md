# PGUNIT

Библиотека на базе **Yii2** (php) для тестирования хранимых и триггерных функций в **postgresql**.

Тест основан на выполнении действий в рамках транзакции с последующим её откатом.

## Соглашения

- имя тестового класса должно соотвествовать имени тестируемого триггера или функции.
- тестовые методы должны начинаться со слова `test`: `testMethod1`, `test_method_1`



## Классы

#### Класс PGUNIT_Test_Functions

Наследуемый класс, предназначеный для тестирования хранимых функций.



#### Класс PGUNIT_Test_Triggers

Наследуемый класс, предназначеный для тестирования триггеров.

*Перед выполнением тестовой функции отключает все триггеры для указанной таблицы, и активирует триггер с именем взятым из имени класса.*



## Свойства

#### $this->db

Подключение к базе данных.

Библиотека выполняет автоматическое подкючение к ```Yii::$app->db_test```.


## Методы настройки тестов

Данные методы выполняются каждый раз при запуске тестовых методов. Используются для общих настроек тестов.



#### public function setSettingsClass() {}

Выполняет дополнительные настройки тестового класса перед началом тестирования.

```PHP
public function setSettingsClass() {
    parent::setSettingsClass();
    # ваши настройки
    ...
}
```



#### public function beforeTest() {}

Выполняет дополнительные действия перед тестированием.

```PHP
public function beforeTest() {
    # Добавляем тестовые данные необходимые для всех тестов
    $arrData = [
        [
            'key'     => 'TEST_1',
            'value'   => '888',
        ],
    ];
    $this->addData('table_test', $arrData);
}
```



#### public function afterTest() {}

Выполняет дополнительные действия после тестированием.

```PHP
public function setSettingsClass() {
    echo 'Тест выполнен';
}
```



## Методы настройки среды



#### Метод $this->dropConstraint()

Удаляет все констрейны указанной таблицы, кроме `primery key`.

```PHP
$this->dropConstraint($table_name);
```

**$table_name** - имя обрабатываемой таблицы



#### Метод $this->addData()

Выполняет добавление данных в таблицу.

```PHP
$this->addData($table_name, $arrData, $returnColumns);
```

**$table_name** - имя таблицы в которую добавляются данные

**$arrData** - массив данных `[['column_name'=>'column_value',...],...]`

**$returnColumns** - имена столбцов из которых надо вернуть данные (опционально)



## Методы проверок

В случае провала проверки, методы генерируют ошибку.



#### Метод $this->assert()

Проверяет идентичность переданных значений

```PHP
$this->assert($v1, $v2, $errText);
```

**$v1** - первое сравниваемое значение

**$v1** - второе сравниваемое значение

**$errText** - ваш текст сообщения об ошибке (опционально)



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
     * Тест 2 (ключ в таблице задан)
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
