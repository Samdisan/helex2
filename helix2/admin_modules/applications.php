<?php
// admin_modules/applications.php

// Логіка зміни статусу або видалення
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apps = getJson('applications.json');
    
    if ($_POST['action'] === 'delete') {
        $apps = array_values(array_filter($apps, fn($a) => $a['id'] !== $_POST['target_id']));
    } elseif ($_POST['action'] === 'approve') {
        foreach ($apps as &$a) {
            if ($a['id'] === $_POST['target_id']) { $a['status'] = 'APPROVED'; break; }
        }
    }
    
    saveJson('applications.json', $apps);
    echo "<script>window.location.href='admin.php?view=applications';</script>";
    exit;
}

$apps = getJson('applications.json');
?>

<h2 style="color:#0f0;">RECRUITMENT APPLICATIONS</h2>

<?php if(empty($apps)): ?>
    <div style="color:#666;">No new messages.</div>
<?php else: ?>
    <div style="display:grid; gap:20px;">
    <?php foreach($apps as $a): ?>
        <div style="background:#111; border:1px solid <?= $a['status']=='APPROVED'?'#0f0':'#444' ?>; padding:20px;">
            <div style="display:flex; justify-content:space-between;">
                <h3 style="margin:0; color:#fff;"><?= $a['name'] ?> <span style="font-size:0.8rem; color:#00f0ff;"><?= $a['tg'] ?></span></h3>
                <span style="font-size:0.8rem; color:#666;"><?= $a['time'] ?></span>
            </div>
            
            <div style="margin:10px 0; font-size:0.9rem; color:#aaa;">
                <strong>FACTION:</strong> <?= $a['faction_pref'] ?? 'N/A' ?><br>
                <strong>ROLE:</strong> <?= $a['role_pref'] ?? 'N/A' ?><br>
                <strong>ANONYMOUS:</strong> <?= $a['anon'] ? 'YES' : 'NO' ?><br>
                <strong>PSY-PROFILE:</strong> <?= !empty($a['psy']) ? implode(' / ', $a['psy']) : 'N/A' ?><br>
                <strong>LORE SCORE:</strong> <?= !empty($a['lore']) ? ('Q1:' . ($a['lore']['q1'] ?? 'N/A') . ' | Q2:' . ($a['lore']['q2'] ?? 'N/A')) : 'N/A' ?>
            </div>

            <div style="display:flex; gap:10px;">
                <a href="https://t.me/<?= str_replace('@','',$a['tg']) ?>" target="_blank" class="btn-act" style="text-decoration:none; text-align:center;">OPEN TELEGRAM</a>
                
                <?php if($a['status'] !== 'APPROVED'): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="target_id" value="<?= $a['id'] ?>">
                    <button class="btn-act" style="background:#050; border-color:#0f0;">APPROVE</button>
                </form>
                <?php endif; ?>

                <form method="POST" onsubmit="return confirm('DEL?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="target_id" value="<?= $a['id'] ?>">
                    <button class="btn-act" style="background:#300; border-color:#f00;">DELETE</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
