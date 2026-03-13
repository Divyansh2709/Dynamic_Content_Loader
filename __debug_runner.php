<?php
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e) {
        echo "SHUTDOWN_ERROR: " . json_encode($e) . PHP_EOL;
    }
});
$_GET = ['page'=>1,'limit'=>6,'search'=>'','category'=>''];
include 'fetch_posts.php';
