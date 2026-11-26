<?php
// =========================================================================
// KERANGKA LOGIKA PHP (BACKEND TOPSIS)
// =========================================================================

// 1. KONEKSI DATABASE (samakan dengan index.php)
// $conn = mysqli_connect("localhost", "root", "", "si_database");

// 2. AMBIL DATA PEKERJA YANG TERDAFTAR (dari tabel_pekerja & tabel_daftar)
// $data_pekerja = [];
// $query_pekerja = mysqli_query($conn, "SELECT p.* FROM tabel_pekerja p JOIN tabel_daftar d ON p.id_pekerja = d.id_pekerja");
// while ($row = mysqli_fetch_assoc($query_pekerja)) {
//     $data_pekerja[] = $row;
// }

// Data dummy sementara (hapus bagian ini jika query database di atas sudah aktif)
$data_pekerja = [
    ['id_pekerja' => 1, 'nama_pekerja' => 'Imam', 'pendidikan' => 5, 'pengalaman' => 9, 'skill' => 20, 'usia' => 20, 'karakter' => 1],
    ['id_pekerja' => 2, 'nama_pekerja' => 'Nira', 'pendidikan' => 4, 'pengalaman' => 12, 'skill' => 19, 'usia' => 20, 'karakter' => 1],
    ['id_pekerja' => 3, 'nama_pekerja' => 'Erlangga', 'pendidikan' => 6, 'pengalaman' => 14, 'skill' => 16, 'usia' => 20, 'karakter' => 1],
];

// Kumpulkan semua id_pekerja terpilih untuk dikirim ke detail.php via Method GET
// Hasil contoh: "1,2,3"
$id_terpilih = implode(',', array_column($data_pekerja, 'id_pekerja'));

// Array default untuk Tabel Hasil (kosong sebelum tombol Hitung ditekan)
$data_hasil = [];

// 3. PROSES PERHITUNGAN TOPSIS SAAT TOMBOL HITUNG DITEKAN
if (isset($_POST['hitung_topsis'])) {
    /*
     * // A. Ambil data bobot dari tabel_bobot
     * // $q_bobot = mysqli_query($conn, "SELECT * FROM tabel_bobot LIMIT 1");
     * // $bobot   = mysqli_fetch_assoc($q_bobot);
     * // $w = [
     * //     'pendidikan' => $bobot['bpendidikan'],
     * //     'pengalaman' => $bobot['bpengalaman'],
     * //     'skill'      => $bobot['bskill'],
     * //     'usia'       => $bobot['busia'],
     * //     'karakter'   => $bobot['bkarakter']
     * // ];
     *
     * // B. Langkah 1: Hitung Pembagi & Matriks Normalisasi (r_ij = x_ij / akar(jumlah x_ij^2))
     * // C. Langkah 2: Hitung Matriks Normalisasi Terbobot (y_ij = w_j * r_ij)
     * // D. Langkah 3: Tentukan Solusi Ideal Positif (A+) & Solusi Ideal Negatif (A-)
     * // E. Langkah 4: Hitung Jarak Solusi Ideal Positif (D+) & Negatif (D-)
     * // F. Langkah 5: Hitung Nilai Preferensi (V_i = D- / (D- + D+))
     * // G. Urutkan dari nilai preferensi terbesar ke terkecil dan isi ke $data_hasil
     */
}
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topsis</title>
    <style>
        .container {
            max-width: 1100px;
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
            color: #333;
        }

        .container table {
            width: 100%;
            border-collapse: collapse;
        }

        .container th {
            background-color: #333;
            color: #ffffff;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .kosong {
            text-align: center;
            padding: 20px;
            color: #333;
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
            box-sizing: border-box;
        }

        .btn-hitung {
            background-color: #04AA6D;
            color: #ffffff;
        }

        .btn-hitung:hover {
            opacity: 0.9;
        }

        .btn-detail {
            background-color: #333;
            color: #ffffff;
        }

        .btn-detail:hover {
            opacity: 0.9;
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
    <div class="container">

        <!-- BAGIAN ATAS: TABEL PEKERJA -->
        <div class="section-box">
            <h2>Tabel Pekerja</h2>
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
                    <?php $no = 1; ?>
                    <?php foreach ($data_pekerja as $p): ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><?= htmlspecialchars($p['nama_pekerja']); ?></td>
                            <td class="text-center"><?= $p['pendidikan']; ?></td>
                            <td class="text-center"><?= $p['pengalaman']; ?></td>
                            <td class="text-center"><?= $p['skill']; ?></td>
                            <td class="text-center"><?= $p['usia']; ?></td>
                            <td class="text-center"><?= $p['karakter']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- BAGIAN BAWAH: TABEL HASIL (KIRI) & TOMBOL AKSI (KANAN) -->
        <div class="section-box baris-bawah">

            <!-- KIRI: Tabel Hasil -->
            <div class="kolom-hasil">
                <h2>Tabel Hasil</h2>
                <table>
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
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
                            <?php $no_hasil = 1; ?>
                            <?php foreach ($data_hasil as $h): ?>
                                <tr>
                                    <td class="text-center"><?= $no_hasil++; ?></td>
                                    <td><?= htmlspecialchars($h['nama']); ?></td>
                                    <td class="text-center"><?= htmlspecialchars($h['preferensi']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- KANAN: Tombol Hitung & Tombol Detail -->
            <div class="kolom-aksi">
                <!-- Tombol 1: Memicu perhitungan TOPSIS -->
                <form method="POST" action="topsis.php">
                    <button type="submit" name="hitung_topsis" class="btn btn-hitung">
                        Hitung
                    </button>
                </form>

                <!-- Tombol 2: Mengirim daftar id_pekerja ke detail.php lewat URL (Method GET) -->
                <a href="detail.php<?= !empty($id_terpilih) ? '?id=' . htmlspecialchars($id_terpilih) : ''; ?>" class="btn btn-detail">
                    Detail
                </a>
            </div>

        </div>

    </div>

</body>

</html>
