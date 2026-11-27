
<?php
require_once "koneksi.php";

$id_bobot = $_GET['id_bobot'] ?? "";

$kriteria = [
    'pendidikan' => 'Pendidikan',
    'pengalaman' => 'Pengalaman',
    'skill' => 'Skill',
    'usia' => 'Usia',
    'karakter' => 'Karakter'
];

$tipeKriteria = [
    'pendidikan' => 'benefit',
    'pengalaman' => 'benefit',
    'skill' => 'benefit',
    'usia' => 'cost',
    'karakter' => 'benefit'
];

$data = [];
$idealPos = [];
$idealNeg = [];
$pesan = "";

if ($id_bobot == "") {
    $pesan = "ID bobot tidak ditemukan!";
} else {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT n.*, p.nama_pekerja
         FROM tabel_nilai n
         JOIN tabel_pekerja p ON n.id_pekerja = p.id_pekerja
         WHERE n.id_bobot = ?
         ORDER BY n.ranking ASC"
    );

    if (!$stmt) {
        die("Query gagal: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $id_bobot);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        die("Gagal mengambil data: " . mysqli_error($conn));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    if (count($data) == 0) {
        $pesan = "Data hasil perhitungan tidak ditemukan!";
    } else {

        // Menghitung solusi ideal positif dan negatif
        foreach ($kriteria as $k => $nama) {

            $kolom = 'y_' . $k;
            $nilai = array_column($data, $kolom);

            if ($tipeKriteria[$k] == 'benefit') {
                $idealPos[$k] = max($nilai);
                $idealNeg[$k] = min($nilai);
            } else {
                $idealPos[$k] = min($nilai);
                $idealNeg[$k] = max($nilai);
            }
        }
    }

    mysqli_stmt_close($stmt);
}

// Format angka
function formatAngka($nilai) {
    return number_format((float) $nilai, 6, '.', '');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Perhitungan TOPSIS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        .container {
            width: 95%;
            max-width: 1250px;
            margin: 25px auto;
            padding-bottom: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        h3 {
            margin-top: 25px;
            margin-bottom: 12px;
            font-size: 19px;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 25px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 7px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 11px 12px;
            text-align: center;
            font-size: 14px;
        }

        th {
            background-color: #e9ecef;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tbody tr:hover {
            background-color: #eef3f8;
        }

        .ranking {
            font-weight: bold;
            color: #198754;
        }

        .peringkat-pertama {
            background-color: #d1e7dd;
            font-weight: bold;
        }

        .pesan {
            background-color: #f8d7da;
            color: #842029;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .btn-kembali {
            display: inline-block;
            background-color: #333;
            color: white;
            text-decoration: none;
            padding: 11px 20px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .btn-kembali:hover {
            background-color: #555;
        }

        @media (max-width: 600px) {
            .container {
                width: 94%;
            }

            h2 {
                font-size: 21px;
            }
        }
    </style>
</head>

<body>

<?php include 'header.php'; ?>

<div class="container">

    <h2>Detail Perhitungan TOPSIS</h2>

    <p>ID Bobot: <strong><?= htmlspecialchars($id_bobot) ?></strong></p>

    <?php if ($pesan != ""): ?>

        <div class="pesan">
            <?= htmlspecialchars($pesan) ?>
        </div>

    <?php else: ?>

        <!-- Tabel Normalisasi -->
        <h3>Tabel Normalisasi</h3>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pekerja</th>

                        <?php foreach ($kriteria as $nama): ?>
                            <th><?= $nama ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>

                            <td>
                                <?= htmlspecialchars($row['nama_pekerja']) ?>
                            </td>

                            <?php foreach ($kriteria as $k => $nama): ?>
                                <td>
                                    <?= formatAngka($row['r_' . $k]) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabel Normalisasi Terbobot -->
        <h3>Tabel Normalisasi Terbobot</h3>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pekerja</th>

                        <?php foreach ($kriteria as $nama): ?>
                            <th><?= $nama ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>

                            <td>
                                <?= htmlspecialchars($row['nama_pekerja']) ?>
                            </td>

                            <?php foreach ($kriteria as $k => $nama): ?>
                                <td>
                                    <?= formatAngka($row['y_' . $k]) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabel Solusi Ideal -->
        <h3>Tabel Solusi Ideal Positif dan Negatif</h3>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Solusi Ideal</th>

                        <?php foreach ($kriteria as $nama): ?>
                            <th><?= $nama ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <th>A+</th>

                        <?php foreach ($kriteria as $k => $nama): ?>
                            <td>
                                <?= formatAngka($idealPos[$k]) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <tr>
                        <th>A-</th>

                        <?php foreach ($kriteria as $k => $nama): ?>
                            <td>
                                <?= formatAngka($idealNeg[$k]) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tabel Jarak Solusi -->
        <h3>Tabel Jarak Solusi</h3>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pekerja</th>
                        <th>Jarak Positif (D+)</th>
                        <th>Jarak Negatif (D-)</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>

                            <td>
                                <?= htmlspecialchars($row['nama_pekerja']) ?>
                            </td>

                            <td>
                                <?= formatAngka($row['d_positif']) ?>
                            </td>

                            <td>
                                <?= formatAngka($row['d_negatif']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tabel Preferensi dan Perangkingan -->
        <h3>Tabel Preferensi dan Perangkingan</h3>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pekerja</th>
                        <th>Nilai Preferensi</th>
                        <th>Ranking</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data as $i => $row): ?>
                        <tr class="<?= $row['ranking'] == 1 ? 'peringkat-pertama' : '' ?>">

                            <td><?= $i + 1 ?></td>

                            <td>
                                <?= htmlspecialchars($row['nama_pekerja']) ?>
                            </td>

                            <td>
                                <?= formatAngka($row['preferensi']) ?>
                            </td>

                            <td class="ranking">
                                <?= (int) $row['ranking'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

    <a href="topsis.php" class="btn-kembali">Kembali</a>

</div>

</body>
</html>
