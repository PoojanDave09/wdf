<?php
$data = json_decode(file_get_contents('events.json'), true);
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$perPage = 2;
$start = ($page-1)*$perPage;
foreach (array_slice($data, $start, $perPage) as $e) {
    echo htmlspecialchars($e["name"]) . " - " . $e["date"] . "<br>";
}
for ($i=1; $i <= ceil(count($data)/$perPage); $i++) {
    echo "<a href='?page=$i'>$i</a> ";
}
?>