<?php
// admin_modules/config.php
session_start();

// Налаштування показу помилок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- КОНСТАНТИ ФАЙЛІВ ---
define('LORE_FILE', 'helix_data.json');
define('USERS_FILE', 'users.json');
define('GAMESTATE_FILE', 'gamestate.json'); // <--- ОСЬ ЦЬОГО РЯДКА НЕ ВИСТАЧАЛО
define('ADMIN_PASS', 'HELIX2025');

// --- ФУНКЦІЇ ---
function getJson($file) {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    return json_decode($content, true) ?? [];
}

function saveJson($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Обробка виходу
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}
?>
