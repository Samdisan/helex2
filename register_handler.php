<?php
// register_handler.php
header('Content-Type: application/json');

// Отримуємо JSON від JS
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data']);
    exit;
}

// Валідація обов'язкових полів
if (empty($data['name']) || empty($data['tg']) || empty($data['role_pref'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$file = 'applications.json';
$apps = [];

if (file_exists($file)) {
    $apps = json_decode(file_get_contents($file), true) ?? [];
}

// Форматуємо PSY дані для відображення
$psyLabels = ['L' => 'Logic', 'H' => 'Humanity', 'M' => 'Mercenary', 'C' => 'Chaos'];
$psyFormatted = [];
if (!empty($data['psy']) && is_array($data['psy'])) {
    $keys = ['L', 'H', 'M', 'C'];
    foreach ($data['psy'] as $i => $val) {
        if (isset($keys[$i])) {
            $psyFormatted[$keys[$i]] = (int)$val;
        }
    }
}

// Форматуємо LORE результати для відображення в адмінці
$loreFormatted = [];
if (!empty($data['lore']) && is_array($data['lore'])) {
    $loreFormatted = [
        'q1' => isset($data['lore']['role']) ? $data['lore']['role'] : 'N/A',
        'q2' => isset($data['lore']['faction']) ? $data['lore']['faction'] : 'N/A'
    ];
}

// Додаємо нову заявку
$newApp = [
    'id' => uniqid(),
    'time' => date('Y-m-d H:i:s'),
    'name' => htmlspecialchars($data['name']),
    'tg' => htmlspecialchars($data['tg']),
    'faction_pref' => htmlspecialchars($data['faction_pref'] ?? ''),
    'role_pref' => htmlspecialchars($data['role_pref'] ?? ''),
    'anon' => $data['anon'] ?? false,
    'psy' => $psyFormatted,
    'lore' => $loreFormatted,
    'status' => 'NEW'
];

array_unshift($apps, $newApp); // Додаємо на початок

if (file_put_contents($file, json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Write error']);
}
?>
