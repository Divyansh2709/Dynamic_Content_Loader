<?php
declare(strict_types=1);

if (!isset($pageTitle)) {
    $pageTitle = 'Dynamic Content Loader';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fb; color: #1f2937; }
        .auth-shell { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .auth-card { width: min(100%, 460px); background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 28px; box-shadow: 0 20px 40px -30px rgba(31, 41, 55, 0.45); }
        .auth-card h1 { margin: 0 0 8px; font-size: 1.5rem; }
        .auth-card p { margin: 0 0 18px; color: #6b7280; }
        .field { margin-bottom: 14px; }
        .field label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.92rem; }
        .field input { width: 100%; border: 1px solid #d1d5db; border-radius: 10px; padding: 11px 12px; font-size: 0.95rem; }
        .field input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2); }
        .btn { width: 100%; border: 0; border-radius: 10px; padding: 12px; background: #2563eb; color: #fff; font-weight: 700; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .meta-link { margin-top: 14px; text-align: center; font-size: 0.92rem; }
        .meta-link a { color: #2563eb; text-decoration: none; }
        .notice { margin-bottom: 14px; border-radius: 10px; padding: 10px 12px; font-size: 0.92rem; display: none; }
        .notice.error { display: block; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .notice.success { display: block; background: #ecfdf5; border: 1px solid #bbf7d0; color: #065f46; }
    </style>
</head>
<body>
