<?php
echo "Testing API\n";

$opts = array('http' => array('method' => 'GET'));
$context = stream_context_create($opts);
$result = file_get_contents('http://localhost/pinjam/api.php/alat', false, $context);

echo "Result: $result\n";
echo "Done\n";
?>
