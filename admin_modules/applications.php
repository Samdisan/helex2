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
            <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <h3 style="margin:0; color:#fff;"><?= htmlspecialchars($a['name'] ?? '') ?> <span style="font-size:0.8rem; color:#00f0ff;"><?= htmlspecialchars($a['tg'] ?? '') ?></span></h3>
                <span style="font-size:0.8rem; color:#666;"><?= htmlspecialchars($a['time'] ?? '') ?></span>
            </div>
            
            <div style="margin:15px 0; padding:15px; background:#0a0a0a; border-left:3px solid #ffd700;">
                <div style="margin-bottom:10px;">
                    <strong style="color:#ffd700;">FACTION:</strong> <span style="color:#fff;"><?= htmlspecialchars($a['faction_pref'] ?? 'N/A') ?></span><br>
                    <strong style="color:#ffd700;">ROLE:</strong> <span style="color:#fff;"><?= htmlspecialchars($a['role_pref'] ?? 'N/A') ?></span>
                </div>
            </div>
            
            <div style="margin:10px 0; font-size:0.9rem; color:#aaa;">
                <strong>ANONYMOUS:</strong> <?= ($a['anon'] ?? false) ? 'YES' : 'NO' ?><br>
                <strong>PSY-PROFILE:</strong> 
                <?php 
                    if (!empty($a['psy']) && is_array($a['psy'])) {
                        $psyParts = [];
                        foreach ($a['psy'] as $key => $val) {
                            $psyParts[] = "$key:$val";
                        }
                        echo implode(' / ', $psyParts);
                    } else {
                        echo 'N/A';
                    }
                ?><br>
                <strong>LORE TEST:</strong> 
                <?php 
                    if (!empty($a['lore']) && is_array($a['lore'])) {
                        echo "Role: " . htmlspecialchars($a['lore']['q1'] ?? 'N/A') . " | Faction: " . htmlspecialchars($a['lore']['q2'] ?? 'N/A');
                    } else {
                        echo 'N/A';
                    }
                ?>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:15px;">
                <a href="https://t.me/<?= str_replace('@','',htmlspecialchars($a['tg'] ?? '')) ?>" target="_blank" class="btn-act" style="text-decoration:none; text-align:center;">OPEN TELEGRAM</a>
                
                <?php if(($a['status'] ?? '') !== 'APPROVED'): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="target_id" value="<?= htmlspecialchars($a['id'] ?? '') ?>">
                    <button class="btn-act" style="background:#050; border-color:#0f0;">APPROVE</button>
                </form>
                <?php else: ?>
                <span style="color:#0f0; padding:8px;">✓ APPROVED</span>
                <?php endif; ?>

                <form method="POST" onsubmit="return confirm('Видалити заявку?');" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="target_id" value="<?= htmlspecialchars($a['id'] ?? '') ?>">
                    <button class="btn-act" style="background:#300; border-color:#f00;">DELETE</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
