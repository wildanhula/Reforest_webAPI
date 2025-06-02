<?php
$file = __DIR__ . '/storage/images/1748283832_A7yFvjf0YU.jpg';
if (file_exists($file)) {
    echo "File ada dan path: " . realpath($file);
} else {
    echo "File tidak ditemukan di path: $file";
}
