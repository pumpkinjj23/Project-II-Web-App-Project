<?php
/**
 * index.php - Web Application Entry Point
 */
if (file_exists(__DIR__ . '/index.html')) {
    include __DIR__ . '/index.html';
} else {
    echo "Northwind BMS Application Ready";
}

