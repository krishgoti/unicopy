<?php
$file = "c:/xampp/htdocs/unicopy/database/unihelp_master.sql";
$sql = file_get_contents($file);
if (strpos($sql, "duration_mins") !== false) {
    echo "Already contains duration_mins.\n";
    exit;
}
$search = "  `is_active` tinyint(1) NOT NULL DEFAULT 1,\n  `single_attempt` tinyint(1) NOT NULL DEFAULT 1,\n";
$replace = $search . "  `duration_mins` int(11) DEFAULT 0,\n  `started_at` datetime DEFAULT NULL,\n";

if (strpos($sql, "single_attempt") !== false) {
    $newSql = str_replace($search, $replace, $sql);
    if ($newSql !== $sql) {
        file_put_contents($file, $newSql);
        echo "Successfully updated mock_tests schema.\n";
    } else {
        echo "str_replace matched nothing.\n";
    }
} else {
    echo "Could not find single_attempt.\n";
}
?>
