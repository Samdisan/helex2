<?php
// admin_modules/players.php

// --- ФРАКЦІЇ ---
$fractions = ['OLYMPOS', 'ORIGIN', 'THEMIS']; 

$filterChapter = $_GET['chapter_filter'] ?? 'all';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = getJson(USERS_FILE);
    
    // ЗБИРАЄМО ДАНІ
    $newItem = [
        'id' => $_POST['target_id'] ?? uniqid('u_'),
        'name' => $_POST['name'],
        'access_code' => $_POST['access_code'],
        'role' => $_POST['role'],
        'faction' => $_POST['faction'],
        'booking_status' => $_POST['booking_status'], // <--- НОВЕ ПОЛЕ
        'level' => $_POST['level'],
        'chapter' => $_POST['chapter'],
        'history' => $_POST['history'],
        'stats' => ['hp'=>100, 'psy'=>100, 'rad'=>0, 'status'=>'OK']
    ];

    if ($_POST['action'] === 'add') {
        array_unshift($data, $newItem);
    } elseif ($_POST['action'] === 'update') {
        foreach ($data as &$u) {
            if ($u['id'] === $_POST['target_id']) {
                // Зберігаємо старі статси, щоб не скинулися
                $oldStats = $u['stats'];
                $u = $newItem;
                $u['stats'] = $oldStats; 
                break;
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $data = array_values(array_filter($data, fn($i) => $i['id'] !== $_POST['target_id']));
    }
    
    saveJson(USERS_FILE, $data);
    echo "<script>window.location.href='admin.php?view=players&chapter_filter=$filterChapter';</script>";
    exit;
}

$editData = null;
if (isset($_GET['edit_id'])) {
    foreach (getJson(USERS_FILE) as $item) { if ($item['id'] === $_GET['edit_id']) { $editData = $item; break; } }
}

$players = array_filter(getJson(USERS_FILE), function($u) use ($filterChapter) {
    if ($filterChapter === 'all') return true;
    return ($u['chapter'] ?? '') === $filterChapter;
});
?>

<div class="filter-bar" style="margin: -20px -20px 20px -20px;">
    <span style="color:#666;">FILTER:</span>
    <a href="?view=players&chapter_filter=all" class="filter-btn <?= $filterChapter=='all'?'active':'' ?>">ALL</a>
    <a href="?view=players&chapter_filter=ch1" class="filter-btn <?= $filterChapter=='ch1'?'active':'' ?>">CH 1</a>
    <a href="?view=players&chapter_filter=ch2" class="filter-btn <?= $filterChapter=='ch2'?'active':'' ?>">CH 2</a>
</div>

<div class="editor">
    <h3 style="margin-top:0; color:#ffd700;"><?= $editData ? "EDIT: ".$editData['name'] : "NEW ENTRY" ?></h3>
    <form method="POST">
        <input type="hidden" name="action" value="<?= $editData?'update':'add' ?>">
        <?php if($editData): ?><input type="hidden" name="target_id" value="<?= $editData['id'] ?>"><?php endif; ?>
        
        <div class="row">
            <input type="text" name="name" placeholder="Name (e.g. Zeus)" required value="<?= $editData['name']??'' ?>">
            <input type="text" name="access_code" placeholder="Code (Login)" required value="<?= $editData['access_code']??'' ?>">
        </div>
        
        <div class="row">
            <div style="flex:1;">
                <label style="color:#666; font-size:0.7rem;">FACTION</label>
                <select name="faction" style="width:100%; border:1px solid #ffd700; color:#ffd700;">
                    <?php foreach($fractions as $f): ?>
                        <option value="<?= $f ?>" <?= ($editData['faction']??'')==$f ? 'selected' : '' ?>><?= $f ?></option>
                    <?php endforeach; ?>
                    <option value="OTHER" <?= ($editData['faction']??'')=='OTHER' ? 'selected' : '' ?>>OTHER</option>
                </select>
            </div>
            
            <div style="flex:1;">
                <label style="color:#666; font-size:0.7rem;">ROLE NAME</label>
                <input type="text" name="role" placeholder="e.g. Ares" value="<?= $editData['role']??'' ?>">
            </div>
        </div>

        <div class="row">
            <div style="flex:1;">
                <label style="color:#666; font-size:0.7rem;">BOOKING STATUS</label>
                <select name="booking_status" style="width:100%; background:#111;">
                    <option value="taken" <?= ($editData['booking_status']??'')=='taken' ? 'selected' : '' ?> style="color:#f55;">🔴 TAKEN (Player assigned)</option>
                    <option value="free" <?= ($editData['booking_status']??'')=='free' ? 'selected' : '' ?> style="color:#0f0;">🟢 VACANT (Open for registration)</option>
                </select>
            </div>
             <div style="flex:1;">
                <label style="color:#666; font-size:0.7rem;">LEVEL</label>
                <select name="level">
                    <?php for($i=1;$i<=5;$i++) echo "<option value='$i' ".($editData['level']??''==$i?'selected':'').">LVL $i</option>"; ?>
                </select>
            </div>
             <div style="flex:1;">
                <label style="color:#666; font-size:0.7rem;">CHAPTER</label>
                <select name="chapter">
                    <option value="ch2" <?= ($editData['chapter']??'')=='ch2'?'selected':'' ?>>Pandora</option>
                    <option value="ch1" <?= ($editData['chapter']??'')=='ch1'?'selected':'' ?>>Arctic</option>
                </select>
            </div>
        </div>
        
        <textarea name="history" placeholder="Mythos / Description..." style="height:100px;"><?= $editData['history']??'' ?></textarea>
        <button class="btn-act" style="width:100%; border-color:#ffd700; color:#ffd700;">SAVE DATA</button>
        <?php if($editData): ?><a href="?view=players&chapter_filter=<?= $filterChapter ?>" style="display:block; text-align:center; margin-top:10px; color:#666;">CANCEL</a><?php endif; ?>
    </form>
</div>

<table class="data-table">
    <?php foreach($players as $u): 
        $isFree = ($u['booking_status']??'taken') === 'free';
    ?>
    <tr>
        <td style="color:#fff;">
            <?= $u['name'] ?><br>
            <span style="font-size:0.7rem; color:#ffd700;"><?= $u['faction'] ?? '—' ?></span>
        </td>
        <td style="color:#00f0ff;">
            <?= $u['role'] ?>
            <?php if($isFree): ?>
                <br><span style="background:#050; color:#0f0; padding:2px 4px; font-size:0.6rem;">VACANT SLOT</span>
            <?php endif; ?>
        </td>
        <td style="text-align:right;">
            <a href="?view=players&chapter_filter=<?= $filterChapter ?>&edit_id=<?= $u['id'] ?>" style="color:#ffd700; margin-right:10px;">EDIT</a>
            <form method="POST" style="display:inline;" onsubmit="return confirm('DEL?');">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                <button style="background:none; border:none; color:#f00; cursor:pointer;">X</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
