<?php
$filterChapter = $_GET['chapter_filter'] ?? 'all';

// SAVE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = getJson(USERS_FILE);
    foreach ($data as &$user) {
        if ($user['id'] === $_POST['target_id']) {
            $user['stats']['hp'] = (int)$_POST['hp'];
            $user['stats']['psy'] = (int)$_POST['psy'];
            $user['stats']['rad'] = (int)$_POST['rad'];
            $user['stats']['status'] = $_POST['status'];
            break;
        }
    }
    saveJson(USERS_FILE, $data);
    echo "<script>window.location.href='admin.php?view=medbay&chapter_filter=$filterChapter';</script>";
    exit;
}

$allPlayers = getJson(USERS_FILE);
$players = array_filter($allPlayers, function($u) use ($filterChapter) {
    if ($filterChapter === 'all') return $u['role'] !== 'GAMEMASTER';
    return ($u['chapter'] ?? '') === $filterChapter;
});
?>

<div class="filter-bar" style="margin: -20px -20px 20px -20px;">
    <span style="color:#666;">SECTOR:</span>
    <a href="?view=medbay&chapter_filter=all" class="filter-btn <?= $filterChapter=='all'?'active':'' ?>">ALL</a>
    <a href="?view=medbay&chapter_filter=ch1" class="filter-btn <?= $filterChapter=='ch1'?'active':'' ?>">ARCTIC</a>
    <a href="?view=medbay&chapter_filter=ch2" class="filter-btn <?= $filterChapter=='ch2'?'active':'' ?>">PANDORA</a>
</div>

<h2 style="color:#f55; margin-top:0;">MED-BAY MONITORING</h2>

<div class="med-grid">
    <?php foreach($players as $u): 
        $stats = $u['stats'] ?? ['hp'=>100, 'psy'=>100, 'rad'=>0, 'status'=>'OK'];
    ?>
    <form method="POST" class="med-card">
        <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
        <div class="med-head">
            <span style="color:#fff; font-weight:bold;"><?= htmlspecialchars($u['name']) ?></span>
            <span style="color:#00f0ff; font-size:0.8rem;"><?= htmlspecialchars($u['role']) ?></span>
        </div>
        <select name="status" style="width:100%; background:#000; color:<?= $stats['status']=='OK'?'#0f0':($stats['status']=='DEAD'?'#666':'#f00') ?>; padding:5px; margin-bottom:10px;">
            <option value="OK" <?= $stats['status']=='OK'?'selected':'' ?>>STATUS: STABLE</option>
            <option value="INJURED" <?= $stats['status']=='INJURED'?'selected':'' ?>>STATUS: INJURED</option>
            <option value="CRITICAL" <?= $stats['status']=='CRITICAL'?'selected':'' ?>>STATUS: CRITICAL</option>
            <option value="DEAD" <?= $stats['status']=='DEAD'?'selected':'' ?>>STATUS: DECEASED</option>
        </select>
        <div class="stat-row hp"><span class="stat-label">HP</span><div class="bar-bg"><div class="bar-fill" style="width:<?= $stats['hp'] ?>%"></div></div><input type="number" name="hp" value="<?= $stats['hp'] ?>" class="stat-input"></div>
        <div class="stat-row psy"><span class="stat-label">PSY</span><div class="bar-bg"><div class="bar-fill" style="width:<?= $stats['psy'] ?>%"></div></div><input type="number" name="psy" value="<?= $stats['psy'] ?>" class="stat-input"></div>
        <div class="stat-row rad"><span class="stat-label">RAD</span><div class="bar-bg"><div class="bar-fill" style="width:<?= $stats['rad'] ?>%"></div></div><input type="number" name="rad" value="<?= $stats['rad'] ?>" class="stat-input"></div>
        <button class="btn-act" style="width:100%; margin-top:10px;">UPDATE</button>
    </form>
    <?php endforeach; ?>
</div>
