<?php

try {
    require __DIR__ . '/app/bootstrap.php';
} catch (\Exception $e) {
    echo $e->getMessage();
    exit(1);
}

