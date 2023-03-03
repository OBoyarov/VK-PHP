<?php

// Проверяем параметры запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['maze']) &&
    isset($_POST['sx']) &&
    isset($_POST['sy']) &&
    isset($_POST['ex']) &&
    isset($_POST['ey'])) {

    // Получаем данные из запроса
    $post_maze = $_POST['maze'];
    $startPoint = [$_POST['sx'], $_POST['sy']];
    $endPoint = [$_POST['ex'], $_POST['ey']];

    $rows = explode("\n", $post_maze);
    $maze = array();
    foreach ($rows as $i => $row) {
        $cells = explode(" ", trim($row));
        foreach ($cells as  $j => $cell) {
            $maze[$i][$j] = $cell;
        }
    }

    // Выполняем поиск
    $result = findShortestPath($maze, $startPoint, $endPoint);

    if (isset($result) && is_numeric($result)) {
        echo '<h3 style="color: green; margin-bottom: 2px;">Предыдущий поиск в лабиринте:</h3>';
        echo '<style>table, th, td {border: 1px solid; text-align: center;}</style><div style="display: grid; grid-template-columns: repeat(1, 0.2fr);grid-auto-flow: column;"><table>';
        foreach ($maze as $row)  {
            echo "<tr>";
            foreach ($row as $col) {
                if ($col == 0) {
                    echo "<td style='background: red;'>$col</td>";
                } else {
                    echo "<td>$col</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table></div></br>";
        echo "Стартовая точка: [" . $startPoint[0] . ", " . $startPoint[1] . "]</br>";
        echo "Конечная точка: [" . $endPoint[0] . ", " . $endPoint[1] . "]</br>";
        echo '<h3 style="color: green">Кратчайший путь: ' . $result . ' ход(а).</h3>';
        echo "<hr>";
    } else {
        echo '<h3 style="color: red">Путь не найден.</h3>';
    }
}

function findShortestPath($maze, $startPoint, $endPoint) {
    // Проверка входных данных
    $width = count($maze[0]);
    $height = count($maze);

    if ($width == 0 || $height == 0 || $startPoint[0] >= $width || $startPoint[1] >= $height || $endPoint[0] >= $width || $endPoint[1] >= $height || $maze[$startPoint[1]][$startPoint[0]] == 0 || $maze[$endPoint[1]][$endPoint[0]] == 0) {
        die('<meta http-equiv="Refresh" content="3" /><h3 style="color: red">Недопустимые входные данные.</h3>Обновление страницы через 3 секунды.');
    }

    // Создание рабочего массива с целевыми значениями
    $values = array_fill(0, $height, array_fill(0, $width, null));

    // Создание рабочей очереди
    $queue = new SplPriorityQueue();

    // Добавляем точку старта в очередь с приоритетом 0
    $queue->insert([$startPoint[0], $startPoint[1]], 0);

    // Заполняем первую точку значением 0
    $values[$startPoint[1]][$startPoint[0]] = 0;

    // Инициализация переменных
    $min_steps = null;
    $steps = [];

    // Работа алгоритма
    while (!$queue->isEmpty()) {
        // Получаем первую точку из очереди
        $point = $queue->extract();

        // Текущее значение для точки
        $value = $values[$point[1]][$point[0]];

        // Проверяем точку на соответствие целевой точки
        if ($point[0] == $endPoint[0] && $point[1] == $endPoint[1]) {
            $min_steps = $value;
            break;
        }

        // Ищем соседние точки
        if ($point[0] + 1 < $width && $maze[$point[1]][$point[0] + 1] > 0 && ($values[$point[1]][$point[0] + 1] === null || $values[$point[1]][$point[0] + 1] > $value + $maze[$point[1]][$point[0] + 1])) { // Вправо
            $values[$point[1]][$point[0] + 1] = $value + $maze[$point[1]][$point[0] + 1]; // Обновляем значение
            $queue->insert([$point[0] + 1, $point[1]], -$values[$point[1]][$point[0] + 1]); // Добавляем точку в очередь
        }

        if ($point[1] + 1 < $height && $maze[$point[1] + 1][$point[0]] > 0 && ($values[$point[1] + 1][$point[0]] === null || $values[$point[1] + 1][$point[0]] > $value + $maze[$point[1] + 1][$point[0]])) { // Вниз
            $values[$point[1] + 1][$point[0]] = $value + $maze[$point[1] + 1][$point[0]]; // Обновляем значение
            $queue->insert([$point[0], $point[1] + 1], -$values[$point[1] + 1][$point[0]]); // Добавляем точку в очередь
        }

        if ($point[0] - 1 >= 0 && $maze[$point[1]][$point[0] - 1] > 0 && ($values[$point[1]][$point[0] - 1] === null || $values[$point[1]][$point[0] - 1] > $value + $maze[$point[1]][$point[0] - 1])) { // Влево
            $values[$point[1]][$point[0] - 1] = $value + $maze[$point[1]][$point[0] - 1]; // Обновляем значение
            $queue->insert([$point[0] - 1, $point[1]], -$values[$point[1]][$point[0] - 1]); // Добавляем точку в очередь
        }

        if ($point[1] - 1 >= 0 && $maze[$point[1] - 1][$point[0]] > 0 && ($values[$point[1] - 1][$point[0]] === null || $values[$point[1] - 1][$point[0]] > $value + $maze[$point[1] - 1][$point[0]])) { // Вверх
            $values[$point[1] - 1][$point[0]] = $value + $maze[$point[1] - 1][$point[0]]; // Обновляем значение
            $queue->insert([$point[0], $point[1] - 1], -$values[$point[1] - 1][$point[0]]); // Добавляем точку в очередь
        }

    }
    return $min_steps;
}

//рендерим форму
$x = mt_rand(5, 10);
$y = mt_rand(5, 10);
$str_maze = "";
for ($c = 0; $c < $x; $c++) {
    for ($r = 0; $r < $y; $r++) {
        $str_maze = $str_maze . mt_rand(0, 9) . " ";
    }
    $str_maze = trim($str_maze) . PHP_EOL;
}
$str_maze = substr($str_maze,0,-1);

echo '<form action="" method="POST">';
echo '<p>Введите структуру лабиринта в виде массива цифр от 0 до 9:</br><h5 style="margin-top: -18px; margin-bottom: -12px;">(между цифрами пробел, разделитеь строк - символ переноса)</h5></p>';
echo '<textarea name="maze" style="width: 354px;height: 264px;">' . $str_maze . '</textarea>';
echo '<p>Введите координаты стартовой точки (x,y):</p>';
echo '<input type="text" name="sx" value="0"/><input type="text" name="sy" value="0"/>';
echo '<p>Введите координаты финишной точки (x,y):</p>';
echo '<input type="text" name="ex" value="' . ($y - 1) . '"/><input type="text" name="ey" value="' . ($x - 1) . '"/></br></br>';
echo '<input type="submit" value="Найти">';
echo '</form>';
