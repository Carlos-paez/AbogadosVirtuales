<?php
require 'app/autoload.php';
$db = new PDO('sqlite:data/app.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$cols = $db->query('PRAGMA table_info(lawyers)')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['name'] . "\n";
}
// Also trigger Model migration to see if pais gets added
$r = $db->query("SELECT COUNT(*) FROM lawyers")->fetchColumn();
echo "Lawyers count: $r\n";
unlink('_check.php');
