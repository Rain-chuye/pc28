<?php
session_start();

// Handle login state for main page
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.html');
    die();
}

include __DIR__ . '/index.html';
