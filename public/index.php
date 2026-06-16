<?php
session_start();
// Basic routing for API/Pages
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/api/login') {
    // Handle login
} elseif ($uri === '/api/bet') {
    // Handle bet
} else {
    // Load frontend
    include __DIR__ . '/index.html';
}
