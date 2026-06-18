<?php
// Just a final syntax check for the PHP files I touched
$files = [
    'public/api/bet.php',
    'public/api/login.php',
    'src/Model/Bet.php'
];

foreach ($files as $file) {
    exec("php -l $file", $output, $return);
    if ($return === 0) {
        echo "✅ $file: Syntax OK\n";
    } else {
        echo "❌ $file: Syntax ERROR\n";
        print_r($output);
    }
}
