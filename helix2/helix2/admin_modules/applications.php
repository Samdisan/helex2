<?php
// admin_modules/applications.php
if (!function_exists('esc')) {
    function esc($value) {
        return htmlspecialchars((string)$value);
    }
}

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
        <?php
            $rolePref = $a['role_pref'] ?? '';
            $factionPref = $a['faction_pref'] ?? '';
            $psy = is_array($a['psy'] ?? null) ? $a['psy'] : [];
            $lore = is_array($a['lore'] ?? null) ? $a['lore'] : [];
            $loreTest = is_array($a['lore_test'] ?? null) ? $a['lore_test'] : null;
            $tgRaw = $a['tg'] ?? '';
            $tgClean = ltrim($tgRaw, '@');
            $scoreText = '';
            if ($loreTest && isset($loreTest['scores']) && is_array($loreTest['scores'])) {
                $parts = [];
                foreach (['L','H','M','C'] as $key) {
                    if (isset($loreTest['scores'][$key])) {
                        $parts[] = $key . ':' . $loreTest['scores'][$key];
                    }
                }
                $scoreText = implode(' ', $parts);
            }
        ?>
        <div style="background:#111; border:1px solid <?= $a['status']=='APPROVED'?'#0f0':'#444' ?>; padding:20px;">
            <div style="display:flex; justify-content:space-between;">
                <h3 style="margin:0; color:#fff;"><?= esc($a['name'] ?? '') ?> <span style="font-size:0.8rem; color:#00f0ff;"><?= esc($tgRaw) ?></span></h3>
                <span style="font-size:0.8rem; color:#666;"><?= esc($a['time'] ?? '') ?></span>
            </div>
            
            <div style="margin:10px 0; font-size:0.9rem; color:#aaa;">
                <strong>FACTION:</strong> <?= $factionPref ? esc($factionPref) : 'N/A' ?><br>
                <strong>ROLE:</strong> <?= $rolePref ? esc($rolePref) : 'N/A' ?><br>
                <strong>ANONYMOUS:</strong> <?= !empty($a['anon']) ? 'YES' : 'NO' ?><br>
                <strong>PSY-PROFILE:</strong> <?= $psy ? esc(implode(' / ', $psy)) : 'N/A' ?><br>
                <?php if ($loreTest && !empty($loreTest['role'])): ?>
                    <strong>K.I.R.A. RESULT:</strong> <?= esc($loreTest['role']) ?>
                    <?php if ($scoreText): ?><span style="font-size:0.8rem; color:#666;">(<?= esc($scoreText) ?>)</span><?php endif; ?>
                <?php elseif (isset($lore['q1']) || isset($lore['q2'])): ?>
                    <strong>LORE SCORE:</strong> Q1:<?= esc($lore['q1'] ?? '-') ?> | Q2:<?= esc($lore['q2'] ?? '-') ?>
                <?php else: ?>
                    <strong>LORE SCORE:</strong> N/A
                <?php endif; ?>
            </div>

            <div style="display:flex; gap:10px;">
                <a href="https://t.me/<?= esc($tgClean) ?>" target="_blank" class="btn-act" style="text-decoration:none; text-align:center;">OPEN TELEGRAM</a>
                
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
