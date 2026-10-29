<?php
$file = "c:/xampp/htdocs/unicopy/database/unihelp_master.sql";
$sql = file_get_contents($file);
if (strpos($sql, "duration_mins") !== false) {
    echo "Already contains duration_mins.\n";
    exit;
}
$pattern = "/(CREATE TABLE \`mock_tests\` \([\s\S]*?)(\s*PRIMARY KEY)/m";
$replacement = "$1  \`duration_mins\` int(11) DEFAULT \0,\n  \`started_at\` datetime DEFAULT NULL,$2";
$newSql = preg_replace($pattern, $replacement, $sql);
if ($newSql !== $sql) {
    file_put_contents($file, $newSql);
    echo "Successfully updated mock_tests schema.\n";
} else {
    echo "Failed to match CREATE TABLE \`mock_tests\`.\n";
}
?>
