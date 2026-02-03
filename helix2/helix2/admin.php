<?php
// admin.php
require_once 'admin_modules/config.php';

// --- ЛОГІКА ВХОДУ ---
if (isset($_POST['login_pass'])) {
    // 1. Перевірка головного пароля
    if ($_POST['login_pass'] === ADMIN_PASS) {
        $_SESSION['admin_logged'] = true;
    } else {
        // 2. Перевірка кодів ГМ-ів з бази
        $users = getJson(USERS_FILE);
        foreach($users as $u) {
            if ($u['access_code'] === $_POST['login_pass'] && ($u['role'] === 'GAMEMASTER' || $u['chapter'] === 'admin')) {
                $_SESSION['admin_logged'] = true; break;
            }
        }
    }
}

// --- ЕКРАН ЛОГІНУ ---
if (!isset($_SESSION['admin_logged'])) {
    echo '<body style="background:#000;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;">
    <form method="POST">
        <div style="color:#0f0; font-family:monospace; margin-bottom:10px; text-align:center;">HELIX SYSTEM CORE</div>
        <input type="password" name="login_pass" placeholder="ACCESS KEY" style="padding:15px;font-size:1.5rem;text-align:center;background:#111;color:#0f0;border:1px solid #333;outline:none;font-family:monospace; width: 300px;">
    </form>
    </body>';
    exit;
}

$view = $_GET['view'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>HELIX ADMIN | <?= strtoupper($view) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Courier+New&display=swap" rel="stylesheet">
    <style>
        /* GLOBAL STYLES */
        body { background: #050505; color: #ccc; font-family: 'Courier New', monospace; margin: 0; padding-bottom: 50px; }
        
        /* NAVIGATION */
        .nav { 
            background: #111; padding: 15px 30px; border-bottom: 1px solid #333; 
            display: flex; gap: 25px; align-items: center; position: sticky; top:0; z-index:100; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
        }
        .nav-brand { color:#fff; font-weight:bold; margin-right: 20px; letter-spacing: 2px; }
        .nav a { color: #666; text-decoration: none; font-weight: bold; transition:0.3s; text-transform: uppercase; font-size: 0.9rem; }
        .nav a:hover { color: #fff; }
        .nav a.active { color: #00f0ff; text-shadow: 0 0 10px rgba(0, 240, 255, 0.5); }
        .nav-right { margin-left: auto; display: flex; gap: 20px; }
        
        .container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        
        /* DASHBOARD GRID */
        .dash-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 30px; }
        .dash-card { 
            background: #111; padding: 30px; text-align: center; border: 1px solid #333; 
            text-decoration: none; transition: 0.3s; display: block; position: relative; overflow: hidden; 
        }
        .dash-card:hover { transform: translateY(-5px); background: #1a1a1a; border-color: #555; }
        .dash-icon { font-size: 3rem; margin-bottom: 15px; display: block; }
        .dash-title { margin: 0; font-size: 1.5rem; letter-spacing: 2px; text-transform: uppercase; }
        .dash-desc { color: #666; margin-top: 10px; font-size: 0.8rem; }
        
        /* COMMON MODULE STYLES */
        .editor { background: #151515; padding: 20px; border: 1px solid #333; margin-bottom: 20px; }
        input, select, textarea { width: 100%; background: #000; border: 1px solid #444; color: #fff; padding: 10px; box-sizing: border-box; margin-bottom: 10px; font-family: inherit; }
        input:focus, select:focus, textarea:focus { border-color: #00f0ff; outline: none; }
        
        .row { display: flex; gap: 15px; }
        .btn-act { background: #222; color: #ccc; border: 1px solid #444; padding: 10px 15px; cursor: pointer; text-transform: uppercase; font-size: 0.8rem; font-weight: bold; transition: 0.3s; }
        .btn-act:hover { background: #fff; color: #000; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table td { border-bottom: 1px solid #222; padding: 12px; }
        .data-table tr:hover { background: #111; }

        .filter-bar { padding: 15px 20px; background: #0a0a0a; border-bottom: 1px solid #222; display: flex; gap: 10px; align-items: center; margin: -30px -30px 30px -30px;}
        .filter-btn { padding: 5px 15px; border: 1px solid #444; color: #888; text-decoration: none; font-size: 0.8rem; }
        .filter-btn:hover { border-color: #fff; color: #fff; }
        .filter-btn.active { background: #333; color: #fff; border-color: #fff; }
        
        /* MEDBAY SPECIFIC */
        .med-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px; }
        .med-card { background: #111; border: 1px solid #333; padding: 15px; }
        .stat-row { display: flex; align-items: center; margin-bottom: 8px; font-size: 0.8rem; }
        .stat-label { width: 40px; font-weight: bold; }
        .bar-bg { flex: 1; height: 8px; background: #222; margin: 0 10px; }
        .bar-fill { height: 100%; }
        .stat-input { width: 45px; background: #000; border: 1px solid #444; color: #fff; text-align: center; padding: 5px; }
        .hp .bar-fill { background: #0f0; } .hp .stat-label { color:#0f0; }
        .psy .bar-fill { background: #00f0ff; } .psy .stat-label { color:#00f0ff; }
        .rad .bar-fill { background: #f00; } .rad .stat-label { color:#f00; }
    </style>
</head>
<body>
    <div class="nav">
        <span class="nav-brand">HELIX CORE</span>
        <a href="?view=dashboard" class="<?= $view=='dashboard'?'active':'' ?>">DASHBOARD</a>
        <a href="?view=applications" class="<?= $view=='applications'?'active':'' ?>">INBOX</a>
        <a href="?view=players" class="<?= $view=='players'?'active':'' ?>">PLAYERS</a>
        <a href="?view=lore" class="<?= $view=='lore'?'active':'' ?>">LORE</a>
        <a href="?view=medbay" class="<?= $view=='medbay'?'active':'' ?>" style="color:#f55;">✚ MED-BAY</a>
        
        <div class="nav-right">
            <a href="index.html" target="_blank">[ OPEN SITE ]</a>
            <a href="?logout=1" style="color:#555;">LOGOUT</a>
        </div>
    </div>

    <div class="container">
        <?php
        if ($view === 'dashboard') {
            ?>
            <div style="border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:20px; color:#666;">
                SYSTEM STATUS: <span style="color:#0f0;">ONLINE</span> // ADMIN: LOGGED
            </div>

            <div class="dash-grid">
                <a href="?view=chapter_control&target=ch1" class="dash-card" style="border-color:#00f0ff;">
                    <span class="dash-icon" style="color:#00f0ff;">❄️</span>
                    <h2 class="dash-title" style="color:#00f0ff;">ARCTIC (CH1)</h2>
                    <p class="dash-desc">Ice Station Control & Timer</p>
                </a>

                <a href="?view=chapter_control&target=ch2" class="dash-card" style="border-color:#0f0;">
                    <span class="dash-icon" style="color:#0f0;">☢️</span>
                    <h2 class="dash-title" style="color:#0f0;">PANDORA (CH2)</h2>
                    <p class="dash-desc">Bunker Control & Timer</p>
                </a>

                <a href="?view=applications" class="dash-card">
                    <span class="dash-icon">📩</span>
                    <h2 class="dash-title" style="color:#fff;">INBOX</h2>
                    <p class="dash-desc">Recruitment Applications</p>
                </a>

                <a href="?view=players" class="dash-card">
                    <span class="dash-icon">👥</span>
                    <h2 class="dash-title" style="color:#ffd700;">PLAYERS</h2>
                    <p class="dash-desc">Manifest & Roles</p>
                </a>

                <a href="?view=lore" class="dash-card">
                    <span class="dash-icon">📚</span>
                    <h2 class="dash-title" style="color:#ccc;">LORE DB</h2>
                    <p class="dash-desc">Documents & Secrets</p>
                </a>
                
                <a href="?view=medbay" class="dash-card">
                    <span class="dash-icon">✚</span>
                    <h2 class="dash-title" style="color:#f55;">MED-BAY</h2>
                    <p class="dash-desc">Live Bio-Monitoring</p>
                </a>
            </div>
            <?php
        } 
        elseif (file_exists("admin_modules/$view.php")) {
            include "admin_modules/$view.php";
        } else {
            echo "<h2 style='color:red; text-align:center; margin-top:50px;'>ERROR 404: MODULE NOT FOUND</h2>";
        }
        ?>
    </div>
</body>
</html>
