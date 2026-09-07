<?php
$url = "http://localhost:8000/api/index.php?action=admin/menu";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer test"]);
$res = curl_exec($ch);
curl_close($ch);
echo $res;
