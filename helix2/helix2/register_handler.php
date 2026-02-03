<?php
// register_handler.php
header('Content-Type: application/json');

// Отримуємо JSON від JS
$input = file_get_contents('php://input');
$data = json_decode($input, true);

function safeString($value) {
    return htmlspecialchars(trim((string)$value));
}

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data']);
    exit;
}

$name = safeString($data['name'] ?? '');
$tg = safeString($data['tg'] ?? '');
$factionPref = safeString($data['faction_pref'] ?? '');
$rolePref = safeString($data['role_pref'] ?? '');

if ($name === '' || $tg === '' || $factionPref === '' || $rolePref === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$file = __DIR__ . '/applications.json';
$apps = [];

if (file_exists($file)) {
    $apps = json_decode(file_get_contents($file), true) ?? [];
}

// Додаємо нову заявку
$psy = is_array($data['psy'] ?? null) ? $data['psy'] : [];
$lore = is_array($data['lore'] ?? null) ? $data['lore'] : [];
$loreTest = is_array($data['lore_test'] ?? null) ? $data['lore_test'] : null;

$newApp = [
    'id' => uniqid(),
    'time' => date('Y-m-d H:i:s'),
    'name' => $name,
    'tg' => $tg,
    'faction_pref' => $factionPref,
    'role_pref' => $rolePref,
    'anon' => $data['anon'] ?? false,
    'psy' => $psy, // масив [50, 20, 90]
    'lore' => $lore, // масив відповідей
    'lore_test' => $loreTest,
    'status' => 'NEW'
];

array_unshift($apps, $newApp); // Додаємо на початок

if (file_put_contents($file, json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Write error']);
}
?>
