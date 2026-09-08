<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$requestedDevice = (string) ($_GET['device'] ?? '');
if (in_array($requestedDevice, ['mobile', 'laptop'], true)) {
	$_SESSION['site_device'] = $requestedDevice;
}

$device = $_SESSION['site_device'] ?? '';
if ($device === 'mobile') {
	require __DIR__ . '/views/home-mobile.php';
} elseif ($device === 'laptop') {
	require __DIR__ . '/views/home-laptop.php';
} else {
	require __DIR__ . '/views/device-choice.php';
}
