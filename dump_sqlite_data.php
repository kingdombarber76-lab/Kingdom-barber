<?php
$pdo = new PDO('sqlite:database/database.sqlite');
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $tableIdentifier = str_replace('"', '""', $table);
    $rows = $pdo->query("SELECT * FROM \"$tableIdentifier\"")->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) === 0) {
        continue;
    }
    $columns = array_keys($rows[0]);
    echo "-- TABLE: $table (" . count($rows) . " rows)\n";
    echo "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
    foreach ($rows as $index => $row) {
        $values = [];
        foreach ($columns as $column) {
            $value = $row[$column];
            if ($value === null) {
                $values[] = 'NULL';
            } else {
                $escaped = str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
                $values[] = "'" . $escaped . "'";
            }
        }
        echo '(' . implode(', ', $values) . ')';
        echo $index === count($rows) - 1 ? ";\n\n" : ",\n";
    }
}
