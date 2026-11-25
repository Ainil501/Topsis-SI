<?php include 'header.php'; ?>
<?php
// =========================================================================
// TEMPAT LOGIKA PHP (BACKEND)
// 1. Koneksi database: $conn = mysqli_connect("localhost", "root", "", "si_database");
// 2. Logika Tambah / Edit Bobot: if (isset($_POST['simpan_bobot'])) { ... }
// 3. Logika Perhitungan TOPSIS: if (isset($_POST['hitung_topsis'])) { ... }
// =========================================================================

// Data dummy sementara Tabel Pekerja (nantinya ambil dari tabel_pekerja yang ada di tabel_daftar)
$data_pekerja = [
    ['id_pekerja' => 1, 'nama_pekerja' => 'Imam', 'pendidikan' => 5, 'pengalaman' => 9, 'skill' => 20, 'usia' => 20, 'karakter' => 1],
    ['id_pekerja' => 2, 'nama_pekerja' => 'Nira', 'pendidikan' => 4, 'pengalaman' => 12, 'skill' => 19, 'usia' => 20, 'karakter' => 1],
    ['id_pekerja' => 3, 'nama_pekerja' => 'Erlangga', 'pendidikan' => 6, 'pengalaman' => 14, 'skill' => 16, 'usia' => 20, 'karakter' => 1],
];

// Data dummy sementara dari tabel_bobot (untuk simulasi pilihan & tombol Edit Bobot)
$daftar_bobot = [
    [
        'id_bobot' => 1,
        'bpendidikan' => 4,
        'bpengalaman' => 4,
        'bskill' => 3,
        'busia' => 3,
        'bkarakter' => 4,
    ],
];

// ID bobot yang sedang dipilih (default mengambil data pertama jika tersedia)
$bobot_aktif = !empty($daftar_bobot) ? $daftar_bobot[0] : null;

// Array kosong default untuk Tabel Hasil (diisi setelah tombol Hitung diproses)
// Contoh struktur isi: ['nama' => 'Imam', 'preferensi' => '0.575476']
$data_hasil = [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/topsis.css">
</head>

<body>
    <div class="main-container">
        <!-- =================================================================
        BARIS ATAS: TABEL PEKERJA (KIRI) & ATUR BOBOT (KANAN)
        ================================================================= -->
        <div class="baris-atas">
            <!-- KIRI: Tabel Pekerja -->
            <div class="kolom-pekerja">
                <h2>Tabel Pekerja</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 45px;">No</th>
                                <th>Nama Pekerja</th>
                                <th class="text-center">Pendidikan</th>
                                <th class="text-center">Pengalaman</th>
                                <th class="text-center">Skill</th>
                                <th class="text-center">Usia</th>
                                <th class="text-center">Karakter</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php // Tempat logika PHP: ganti $data_pekerja dengan hasil query SELECT dari database ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_pekerja as $p): ?>
                            <tr>
                                <td class="text-center">
                                    <?= $no++; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['nama_pekerja']); ?>
                                </td>
                                <td class="text-center">
                                    <?= $p['pendidikan']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $p['pengalaman']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $p['skill']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $p['usia']; ?>
                                </td>
                                <td class="text-center">
                                    <?= $p['karakter']; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- KANAN: Panel Bobot (Form muncul saat klik Tambah atau Edit) -->
            <div class="kolom-bobot">
                <h2>Atur Bobot</h2>
                <div class="box-panel">
                    <!-- Pilihan Data Bobot yang Tersedia -->
                    <label for="pilih_bobot" style="font-size: 14px; font-weight: bold; display: block; margin-bottom: 6px;">
                        Pilih Bobot Perhitungan:
                    </label>
                    <select id="pilih_bobot" class="select-bobot" onchange="sinkronBobotTerpilih()">
                        <?php if (empty($daftar_bobot)): ?>
                        <option value="">-- Belum ada data bobot --</option>
                        <?php else: ?>
                        <?php foreach ($daftar_bobot as $b): ?>
                        <option value="<?= $b['id_bobot']; ?>" data-pendidikan="<?= $b['bpendidikan']; ?>" data-pengalaman="<?= $b['bpengalaman']; ?>" data-skill="<?= $b['bskill']; ?>" data-usia="<?= $b['busia']; ?>" data-karakter="<?= $b['bkarakter']; ?>">
                            Bobot #
                            <?= $b['id_bobot']; ?> (P:
                            <?= $b['bpendidikan']; ?>, Pg:
                            <?= $b['bpengalaman']; ?>, S:
                            <?= $b['bskill']; ?>, U:
                            <?= $b['busia']; ?>, K:
                            <?= $b['bkarakter']; ?>)
                        </option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <!-- Tombol Pemicu Munculnya Form Atur Bobot -->
                    <div class="bobot-toolbar">
                        <button type="button" class="btn btn-hijau" style="flex: 1;" onclick="bukaFormBobot('tambah')">
                            + Tambah Bobot
                        </button>
                        <button type="button" class="btn btn-gelap" style="flex: 1;" onclick="bukaFormBobot('edit')">
                            Edit Bobot
                        </button>
                    </div>
                    <!-- Form Atur Bobot (Tersembunyi secara default) -->
                    <div id="formBobotContainer" class="form-bobot-box">
                        <div id="judulFormBobot" class="form-judul">Form Atur Bobot</div>
                        <form method="POST" action="topsis.php">
                            <!-- Penanda mode aksi ('tambah' atau 'edit') dan id_bobot untuk backend -->
                            <input type="hidden" id="aksi_bobot" name="aksi_bobot" value="tambah">
                            <input type="hidden" id="id_bobot" name="id_bobot" value="">
                            <div class="form-row">
                                <label for="bpendidikan">Pendidikan</label>
                                <input type="number" id="bpendidikan" name="bpendidikan" step="any" min="0" required>
                            </div>
                            <div class="form-row">
                                <label for="bpengalaman">Pengalaman</label>
                                <input type="number" id="bpengalaman" name="bpengalaman" step="any" min="0" required>
                            </div>
                            <div class="form-row">
                                <label for="bskill">Skill</label>
                                <input type="number" id="bskill" name="bskill" step="any" min="0" required>
                            </div>
                            <div class="form-row">
                                <label for="busia">Usia</label>
                                <input type="number" id="busia" name="busia" step="any" min="0" required>
                            </div>
                            <div class="form-row">
                                <label for="bkarakter">Karakter</label>
                                <input type="number" id="bkarakter" name="bkarakter" step="any" min="0" required>
                            </div>
                            <!-- Tombol Batal & Simpan di pojok kanan bawah form -->
                            <div class="form-footer">
                                <button type="button" class="btn btn-batal" onclick="tutupFormBobot()">Batal</button>
                                <button type="submit" name="simpan_bobot" class="btn btn-hijau">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- =================================================================
        BARIS BAWAH: TABEL HASIL (KIRI) & TOMBOL HITUNG / DETAIL (KANAN)
        ================================================================= -->
        <div class="baris-bawah">
            <!-- KIRI: Tabel Hasil -->
            <div class="kolom-hasil">
                <h2>Tabel Hasil</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 55px;">No</th>
                                <th>Nama</th>
                                <th class="text-center">Preferensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php // Tempat logika PHP: tampilkan hasil perhitungan dari tabel_hasil ?>
                            <?php if (empty($data_hasil)): ?>
                            <tr>
                                <td colspan="3" class="empty-row">No Available</td>
                            </tr>
                            <?php else: ?>
                            <?php $no_hasil = 1; ?>
                            <?php foreach ($data_hasil as $h): ?>
                            <tr>
                                <td class="text-center">
                                    <?= $no_hasil++; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($h['nama']); ?>
                                </td>
                                <td class="text-center">
                                    <?= htmlspecialchars($h['preferensi']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- KANAN: Dua Tombol Aksi Vertikal -->
            <div class="kolom-aksi">
                <!-- 1. Tombol Hitung (Submit untuk memicu perhitungan TOPSIS) -->
                <form method="POST" action="topsis.php" style="margin: 0;">
                    <input type="hidden" id="hitung_id_bobot" name="id_bobot" value="<?= $bobot_aktif['id_bobot'] ?? ''; ?>">
                    <button type="submit" name="hitung_topsis" class="btn btn-hijau btn-blok">
                        Hitung
                    </button>
                </form>
                <!-- 2. Tombol Detail (Tautan langsung menuju detail.php) -->
                <a id="linkDetail" href="detail.php<?= !empty($bobot_aktif['id_bobot']) ? '?id_bobot=' . $bobot_aktif['id_bobot'] : ''; ?>" class="btn btn-gelap btn-blok">
                    Detail
                </a>
            </div>
        </div>
    </div>
    <script>
    // Menampilkan form bobot dalam mode 'tambah' atau 'edit'
    function bukaFormBobot(mode) {
        const container = document.getElementById('formBobotContainer');
        const judul = document.getElementById('judulFormBobot');
        const inputAksi = document.getElementById('aksi_bobot');
        const inputId = document.getElementById('id_bobot');
        const selectBobot = document.getElementById('pilih_bobot');

        inputAksi.value = mode;

        if (mode === 'tambah') {
            judul.textContent = 'Tambah Data Bobot Baru';
            inputId.value = '';
            document.getElementById('bpendidikan').value = '';
            document.getElementById('bpengalaman').value = '';
            document.getElementById('bskill').value = '';
            document.getElementById('busia').value = '';
            document.getElementById('bkarakter').value = '';
        } else if (mode === 'edit') {
            const opsi = selectBobot.options[selectBobot.selectedIndex];
            if (!opsi || !opsi.value) {
                alert('Belum ada data bobot untuk diedit. Silakan tambah bobot baru terlebih dahulu.');
                return;
            }
            judul.textContent = 'Edit Data Bobot #' + opsi.value;
            inputId.value = opsi.value;
            document.getElementById('bpendidikan').value = opsi.getAttribute('data-pendidikan');
            document.getElementById('bpengalaman').value = opsi.getAttribute('data-pengalaman');
            document.getElementById('bskill').value = opsi.getAttribute('data-skill');
            document.getElementById('busia').value = opsi.getAttribute('data-usia');
            document.getElementById('bkarakter').value = opsi.getAttribute('data-karakter');
        }

        container.classList.add('tampil');
    }

    // Menyembunyikan kembali form bobot
    function tutupFormBobot() {
        document.getElementById('formBobotContainer').classList.remove('tampil');
    }

    // Menyelaraskan id_bobot terpilih ke tombol Hitung dan tautan Detail
    function sinkronBobotTerpilih() {
        const selectBobot = document.getElementById('pilih_bobot');
        const idTerpilih = selectBobot.value;
        document.getElementById('hitung_id_bobot').value = idTerpilih;

        const linkDetail = document.getElementById('linkDetail');
        linkDetail.href = idTerpilih ? 'detail.php?id_bobot=' + encodeURIComponent(idTerpilih) : 'detail.php';
    }
    </script>
</body>

</html>