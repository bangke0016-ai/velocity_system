<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/config/helpers.php';
redirect('admin.php?tab=joki');
