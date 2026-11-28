<?php
require_once "proses_topsis.php";

$id_bobot = trim($_REQUEST['id_bobot'] ?? "BBT-01");
if ($id_bobot === "") {
    $id_bobot = "BBT-01";
}

// Ambil daftar ID bobot yang ada di tabel_bobot
$daftar_bobot = [];
$q_bobot = mysqli_query($conn, "SELECT id_bobot FROM tabel_bobot ORDER BY id_bobot ASC");
if ($q_bobot) {
    while ($b = mysqli_fetch_assoc($q_bobot)) {
        $daftar_bobot[] = $b['id_bobot'];
    }
}
if (!in_array("BBT-01", $daftar_bobot)) {
    array_unshift($daftar_bobot, "BBT-01");
}

// Ambil pekerja terdaftar (status = 1)
$ambil_pkj = ambilPekerjaTerdaftar($conn);
$data_pekerja = $ambil_pkj['ok'] ? $ambil_pkj['data'] : [];

$data_hasil = [];
$pesan_topsis = "";
$topsis_sukses = false;

if (isset($_POST['hitung_topsis'])) {
    $hasil_topsis = hitungTopsis($conn, $id_bobot);
    $topsis_sukses = $hasil_topsis['ok'];
    $pesan_topsis = $hasil_topsis['pesan'];

    if (!empty($hasil_topsis['pekerja_terdaftar'])) {
        $data_pekerja = $hasil_topsis['pekerja_terdaftar'];
    }
}

// Ambil hasil perhitungan tersimpan di tabel_nilai untuk id_bobot aktif
$stmt_hasil = mysqli_prepare(
    $conn,
    "SELECT n.id_pekerja, p.nama_pekerja, n.preferensi, n.ranking
     FROM tabel_nilai n
     JOIN tabel_pekerja p ON n.id_pekerja = p.id_pekerja
     WHERE n.id_bobot = ? AND p.status = 1
     ORDER BY n.ranking ASC"
);
if ($stmt_hasil) {
    mysqli_stmt_bind_param($stmt_hasil, "s", $id_bobot);
    mysqli_stmt_execute($stmt_hasil);
    $res_hasil = mysqli_stmt_get_result($stmt_hasil);
    if ($res_hasil) {
        while ($row_hasil = mysqli_fetch_assoc($res_hasil)) {
            $data_hasil[] = $row_hasil;
        }
    }
    mysqli_stmt_close($stmt_hasil);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topsis</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f9;
            color: #333333;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 16px;
        }

        .section-box {
            margin-bottom: 24px;
        }

        .container h2 {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 20px;
            line-height: 24px;
            color: #333333;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            background-color: #ffffff;
        }

        .container table {
            width: 100%;
            border-collapse: collapse;
        }

        .container th,
        .container td {
            border: 1px solid #dddddd;
            padding: 9px 10px;
        }

        .container th {
            background-color: #333333;
            color: #ffffff;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .kosong {
            text-align: center;
            padding: 20px;
            color: #333333;
            font-style: italic;
        }

        .baris-bawah {
            display: flex;
            gap: 24px;
            align-items: flex-start;
        }

        .kolom-hasil {
            flex: 2;
        }

        .kolom-aksi {
            flex: 1;
            padding-top: 36px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .form-bobot {
            margin-bottom: 10px;
        }

        .form-bobot label {
            display: block;
            font-size: 14px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .form-bobot select {
            width: 100%;
            padding: 9px 10px;
            font-size: 14px;
            border: 1px solid #cccccc;
            background-color: #ffffff;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px 16px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-hitung {
            background-color: #04AA6D;
            color: #ffffff;
        }

        .btn-hitung:hover {
            opacity: 0.9;
        }

        .btn-detail {
            background-color: #333333;
            color: #ffffff;
        }

        .btn-detail:hover {
            opacity: 0.9;
        }

        .alert {
            padding: 10px 12px;
            margin-bottom: 14px;
            border-radius: 4px;
            font-size: 14px;
        }

        .alert-ok {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .alert-err {
            background-color: #f8d7da;
            color: #842029;
        }

        @media (max-width: 768px) {
            .baris-bawah {
                flex-direction: column;
            }

            .kolom-hasil,
            .kolom-aksi {
                width: 100%;
            }

            .kolom-aksi {
                padding-top: 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">

        <?php if ($pesan_topsis !== ""): ?>
            <div class="alert <?= $topsis_sukses ? 'alert-ok' : 'alert-err' ?>">
                <?= htmlspecialchars($pesan_topsis) ?>
            </div>
        <?php endif; ?>

        <!-- BAGIAN ATAS: TABEL PEKERJA -->
        <div class="section-box">
            <h2>Tabel Pekerja Terdaftar</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Nama Pekerja</th>
                            <th class="text-center">Pendidikan</th>
                            <th class="text-center">Pengalaman</th>
                            <th class="text-center">Skill</th>
                            <th class="text-center">Usia</th>
                            <th class="text-center">Karakter</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data_pekerja)): ?>
                            <tr>
                                <td colspan="7" class="kosong">Belum ada pekerja berstatus terdaftar (status = 1)</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; ?>
                            <?php for ($i = 0; $i < count($data_pekerja); $i++): ?>
                                <?php $p = $data_pekerja[$i]; ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($p['nama_pekerja']); ?></td>
                                    <td class="text-center"><?= (int) $p['pendidikan']; ?></td>
                                    <td class="text-center"><?= (int) $p['pengalaman']; ?></td>
                                    <td class="text-center"><?= (int) $p['skill']; ?></td>
                                    <td class="text-center"><?= (int) $p['usia']; ?></td>
                                    <td class="text-center"><?= (int) $p['karakter']; ?></td>
                                </tr>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BAGIAN BAWAH: TABEL HASIL (KIRI) & TOMBOL AKSI (KANAN) -->
        <div class="section-box baris-bawah">

            <!-- KIRI: Tabel Hasil -->
            <div class="kolom-hasil">
                <h2>Tabel Hasil (<?= htmlspecialchars($id_bobot) ?>)</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">Rank</th>
                                <th>Nama</th>
                                <th class="text-center">Preferensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data_hasil)): ?>
                                <tr>
                                    <td colspan="3" class="kosong">No Available</td>
                                </tr>
                            <?php else: ?>
                                <?php for ($i = 0; $i < count($data_hasil); $i++): ?>
                                    <?php $h = $data_hasil[$i]; ?>
                                    <tr>
                                        <td class="text-center"><?= (int) $h['ranking']; ?></td>
                                        <td><?= htmlspecialchars($h['nama_pekerja']); ?></td>
                                        <td class="text-center"><?= number_format((float) $h['preferensi'], 6, '.', ''); ?></td>
                                    </tr>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- KANAN: Pilih Bobot, Tombol Hitung & Tombol Detail -->
            <div class="kolom-aksi">
                <form method="POST" action="topsis.php">
                    <div class="form-bobot">
                        <label for="id_bobot">ID Bobot</label>
                        <select name="id_bobot" id="id_bobot">
                            <?php for ($i = 0; $i < count($daftar_bobot); $i++): ?>
                                <?php $kode_bobot = $daftar_bobot[$i]; ?>
                                <option value="<?= htmlspecialchars($kode_bobot) ?>" <?= $id_bobot === $kode_bobot ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kode_bobot) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <button type="submit" name="hitung_topsis" class="btn btn-hitung">
                        Hitung
                    </button>
                </form>

                <a href="detail.php?id_bobot=<?= urlencode($id_bobot); ?>" class="btn btn-detail">
                    Detail
                </a>
            </div>

        </div>

    </div>

</body>

</html>
