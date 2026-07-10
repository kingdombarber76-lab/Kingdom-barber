<?php
$pdo = new PDO('sqlite:database/database.sqlite');
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$output = '';
foreach ($tables as $table) {
    $escapedTable = str_replace('"', '""', $table);
    $rows = $pdo->query("SELECT * FROM \"$escapedTable\"")->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) === 0) {
        continue;
    }
    $columns = array_keys($rows[0]);
    $output .= "-- TABLE: $table (" . count($rows) . ")\n";
    $output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
    foreach ($rows as $index => $row) {
        $values = [];
        foreach ($columns as $column) {
            $value = $row[$column];
            if ($value === null) {
                $values[] = 'NULL';
            } else {
                $escaped = str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$value);
                $values[] = "'" . $escaped . "'";
            }
        }
        $output .= '(' . implode(', ', $values) . ')';
        $output .= $index === count($rows) - 1 ? ";\n\n" : ",\n";
    }
}
file_put_contents('sqlite_table_data_utf8.sql', $output);
