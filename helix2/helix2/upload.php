<?php
// upload.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['access_code'];
    
    // Перевірка, чи є файл
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(['success' => false, 'msg' => 'Upload failed']));
    }

    $fileTmpPath = $_FILES['avatar']['tmp_name'];
    $fileNameCmps = explode(".", $_FILES['avatar']['name']);
    $fileExtension = strtolower(end($fileNameCmps));

    // Дозволені розширення
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
    if (!in_array($fileExtension, $allowedfileExtensions)) {
        die(json_encode(['success' => false, 'msg' => 'Invalid file type']));
    }

    // Нове ім'я файлу = КОД_ДОСТУПУ.jpg (або png)
    // Ми будемо зберігати все як jpg або png, але для простоти збережемо розширення
    $newFileName = $code . '.' . $fileExtension;
    
    // Шлях (в папку uploads)
    $uploadFileDir = './uploads/';
    $dest_path = $uploadFileDir . $newFileName;

    if(move_uploaded_file($fileTmpPath, $dest_path)) {
        // Зберігаємо ім'я файлу в JSON, якщо треба, але ми можемо просто шукати файл по імені
        echo json_encode(['success' => true, 'file' => $newFileName]);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Save error']);
    }
}
?>