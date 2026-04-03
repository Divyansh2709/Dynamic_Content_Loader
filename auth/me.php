<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    jsonResponse(['error' => 'Method not allowed.'], 405);
}

$user = currentUser();
if ($user === null) {
    jsonResponse(['authenticated' => false, 'user' => null]);
}

jsonResponse(['authenticated' => true, 'user' => $user]);
