<?php
require_once "koneksi.php";
mysqli_set_charset($conn, "utf8mb4");

$pesan = "";

/* ================= CREATE ================= */
if (isset($_POST['tambah'])) {
    $id = "P" . strtoupper(substr(uniqid(), -8)); // ID unik
    $stmt = mysqli_prepare($conn,
        "INSERT INTO tabel_pekerja (id_pekerja, nama_pekerja, pendidikan, pengalaman, skill, usia, karakter)
         VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssiiiii",
        $id, $_POST['nama'], $_POST['pendidikan'], $_POST['pengalaman'],
        $_POST['skill'], $_POST['usia'], $_POST['karakter']);
    mysqli_stmt_execute($stmt);
    header("Location: index.php?msg=" . urlencode("Data berhasil ditambahkan"));
    exit;
}

/* ================= UPDATE ================= */
if (isset($_POST['edit'])) {
    $stmt = mysqli_prepare($conn,
        "UPDATE tabel_pekerja
         SET nama_pekerja=?, pendidikan=?, pengalaman=?, skill=?, usia=?, karakter=?
         WHERE id_pekerja=?");
    mysqli_stmt_bind_param($stmt, "siiiiis",
        $_POST['nama'], $_POST['pendidikan'], $_POST['pengalaman'],
        $_POST['skill'], $_POST['usia'], $_POST['karakter'], $_POST['id']);
    mysqli_stmt_execute($stmt);
    header("Location: index.php?msg=" . urlencode("Data berhasil diubah"));
    exit;
}

/* ================= DELETE ================= */
if (isset($_POST['hapus'])) {
    $stmt = mysqli_prepare($conn, "DELETE FROM tabel_pekerja WHERE id_pekerja=?");
    mysqli_stmt_bind_param($stmt, "s", $_POST['hapus']);
    mysqli_stmt_execute($stmt);
    header("Location: index.php?msg=" . urlencode("Data berhasil dihapus"));
    exit;
}

/* ================= TOGGLE STATUS (1 pekerja) ================= */
if (isset($_POST['status'])) {
    $aksi = isset($_POST['aksi']) ? 1 : 0;
    $stmt = mysqli_prepare($conn, "UPDATE tabel_pekerja SET status = ? WHERE id_pekerja = ?");
    mysqli_stmt_bind_param($stmt, "is", $aksi, $_POST['status']);
    mysqli_stmt_execute($stmt);
    header("Location: index.php");
    exit;
}

/* ================= SELECT ALL / BATAL SEMUA ================= */
if (isset($_POST['semua'])) {
    $val = $_POST['semua'] === '1' ? 1 : 0;
    $stmt = mysqli_prepare($conn, "UPDATE tabel_pekerja SET status = ?");
    mysqli_stmt_bind_param($stmt, "i", $val);
    mysqli_stmt_execute($stmt);
    header("Location: index.php");
    exit;
}

/* ================= TAMBAH OTOMATIS DARI JSON ================= */
if (isset($_POST['import'])) {
    $file = __DIR__ . "/data_pekerja.json";
    if (!file_exists($file)) {
        header("Location: index.php?msg=" . urlencode("File data_pekerja.json tidak ditemukan"));
        exit;
    }
    $data = json_decode(file_get_contents($file), true);

    // Ambil semua ID yang sudah ada (1 query, lebih cepat daripada cek satu-satu)
    $ada = [];
    $q = mysqli_query($conn, "SELECT id_pekerja FROM tabel_pekerja");
    while ($row = mysqli_fetch_assoc($q)) {
        $ada[$row['id_pekerja']] = true;
    }

    $stmt = mysqli_prepare($conn,
        "INSERT INTO tabel_pekerja (id_pekerja, nama_pekerja, pendidikan, pengalaman, skill, usia, karakter)
         VALUES (?, ?, ?, ?, ?, ?, ?)");

    $masuk = 0; $skip = 0;
    mysqli_begin_transaction($conn);
    foreach ($data as $d) {
        if (isset($ada[$d['id_pekerja']])) { $skip++; continue; }
        mysqli_stmt_bind_param($stmt, "ssiiiii",
            $d['id_pekerja'], $d['nama_pekerja'], $d['pendidikan'],
            $d['pengalaman'], $d['skill'], $d['usia'], $d['karakter']);
        mysqli_stmt_execute($stmt);
        $masuk++;
    }
    mysqli_commit($conn);

    header("Location: index.php?msg=" . urlencode("Import selesai: $masuk ditambahkan, $skip dilewati (sudah ada)"));
    exit;
}

/* ================= DATA UNTUK TAMPILAN ================= */
$result = mysqli_query($conn, "SELECT * FROM tabel_pekerja ORDER BY nama_pekerja");
$total = 0; $terdaftar = 0; $rows = [];
while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
    $total++;
    if ($r['status']) $terdaftar++;
}
$semuaTerdaftar = ($total > 0 && $terdaftar == $total);

// Mode edit: isi form dengan data pekerja yang dipilih
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM tabel_pekerja WHERE id_pekerja=?");
    mysqli_stmt_bind_param($stmt, "s", $_GET['edit']);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th, td { border: 1px solid #ddd; padding: 11px 12px; text-align: center; font-size: 14px; }
        th { background-color: #e9ecef; font-weight: bold; }
        .form-box { background:#f8f9fa; border:1px solid #ddd; padding:14px; margin:12px 0; max-width:900px; }
        .form-box input[type=text], .form-box input[type=number] { padding:6px; margin:4px 6px 4px 0; width:130px; }
        .form-box input[name=nama] { width:200px; }
        .btn { padding:6px 12px; border:1px solid #888; background:#fff; cursor:pointer; text-decoration:none; color:#000; font-size:13px; }
        .btn-danger { background:#dc3545; color:#fff; border-color:#dc3545; }
        .btn-primary { background:#0d6efd; color:#fff; border-color:#0d6efd; }
        .pesan { background:#d1e7dd; padding:10px; margin:10px 0; max-width:900px; }
        .toolbar { display:flex; gap:8px; flex-wrap:wrap; margin:10px 0; }
        .toolbar form { margin:0; }
        .scroll { overflow-x:auto; padding-right:16px; }
    </style>
</head>
<body>
<?php include "header.php"; ?>

<div style="padding-left:16px">
    <h2>Tabel Pekerja</h2>

    <?php if (isset($_GET['msg'])): ?>
        <div class="pesan"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <!-- FORM TAMBAH / EDIT -->
    <div class="form-box">
        <strong><?= $edit ? "Edit Pekerja" : "Tambah Pekerja" ?></strong><br>
        <form method="post">
            <?php if ($edit): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($edit['id_pekerja']) ?>">
            <?php endif; ?>
            <input type="text"   name="nama"       placeholder="Nama"       required value="<?= $edit ? htmlspecialchars($edit['nama_pekerja']) : "" ?>">
            <input type="number" name="pendidikan" placeholder="Pendidikan" required value="<?= $edit['pendidikan'] ?? "" ?>">
            <input type="number" name="pengalaman" placeholder="Pengalaman" required value="<?= $edit['pengalaman'] ?? "" ?>">
            <input type="number" name="skill"      placeholder="Skill"      required value="<?= $edit['skill'] ?? "" ?>">
            <input type="number" name="usia"       placeholder="Usia"       required value="<?= $edit['usia'] ?? "" ?>">
            <input type="number" name="karakter"   placeholder="Karakter"   required value="<?= $edit['karakter'] ?? "" ?>">
            <button class="btn btn-primary" type="submit" name="<?= $edit ? "edit" : "tambah" ?>">
                <?= $edit ? "Simpan Perubahan" : "Tambah" ?>
            </button>
            <?php if ($edit): ?>
                <a class="btn" href="index.php">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <form method="post" onsubmit="return confirm('Import semua data dari data_pekerja.json? Data yang sudah ada akan dilewati.')">
            <button class="btn" type="submit" name="import" value="1">Tambah Otomatis (dari JSON)</button>
        </form>
        <span style="align-self:center">Terdaftar: <?= $terdaftar ?> / <?= $total ?></span>
    </div>

    <div class="scroll">
    <table>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>pendidikan</th>
            <th>pengalaman</th>
            <th>skill</th>
            <th>usia</th>
            <th>karakter</th>
            <th>
                Status daftar<br>
                <!-- CHECKBOX SELECT ALL -->
                <form method="post" style="margin:0">
                    <input type="hidden" name="semua" value="<?= $semuaTerdaftar ? 0 : 1 ?>">
                    <input type="checkbox" title="Daftarkan / batalkan semua"
                           onchange="this.form.submit()" <?= $semuaTerdaftar ? "checked" : "" ?>>
                </form>
            </th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; foreach ($rows as $r): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($r['nama_pekerja']) ?></td>
            <td><?= $r['pendidikan'] ?></td>
            <td><?= $r['pengalaman'] ?></td>
            <td><?= $r['skill'] ?></td>
            <td><?= $r['usia'] ?></td>
            <td><?= $r['karakter'] ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($r['id_pekerja']) ?>">
                    <input type="checkbox" name="aksi" onchange="this.form.submit()"
                        <?= $r['status'] ? "checked" : "" ?>>
                </form>
            </td>
            <td>
                <a class="btn" href="index.php?edit=<?= urlencode($r['id_pekerja']) ?>">Edit</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Hapus pekerja ini?')">
                    <button class="btn btn-danger" type="submit" name="hapus"
                            value="<?= htmlspecialchars($r['id_pekerja']) ?>">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    </div>
</div>

</body>
</html>