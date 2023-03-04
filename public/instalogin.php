<?php
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$actual_link = str_replace('instalogin.php?', 'index.php?url=instagram-login/index&', $actual_link);

try {
	header('Location: '.$actual_link);
} catch (\Error $e) {
	echo $e->getMessage();
}
?>