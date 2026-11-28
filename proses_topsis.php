<?php
require_once "koneksi.php";
mysqli_set_charset($conn, "utf8mb4");

function pastikanBobotTersedia($conn, $id_bobot = "BBT-01") {
    $stmt = mysqli_prepare($conn, "SELECT * FROM tabel_bobot WHERE id_bobot = ? LIMIT 1");
    if (!$stmt) {
        return ['ok' => false, 'pesan' => "Gagal menyiapkan query cek bobot: " . mysqli_error($conn), 'bobot' => null];
    }

    mysqli_stmt_bind_param($stmt, "s", $id_bobot);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if ($row) {
        return ['ok' => true, 'pesan' => '', 'bobot' => $row];
    }

    if ($id_bobot === "BBT-01") {
        $b_pend = 5;
        $b_peng = 4;
        $b_skil = 4;
        $b_usia = 3;
        $b_kara = 4;

        $ins = mysqli_prepare(
            $conn,
            "INSERT INTO tabel_bobot (id_bobot, bpendidikan, bpengalaman, bskill, busia, bkarakter)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        if (!$ins) {
            return ['ok' => false, 'pesan' => "Gagal menyiapkan insert bobot default: " . mysqli_error($conn), 'bobot' => null];
        }

        mysqli_stmt_bind_param($ins, "siiiii", $id_bobot, $b_pend, $b_peng, $b_skil, $b_usia, $b_kara);
        $ok = mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);

        if (!$ok) {
            return ['ok' => false, 'pesan' => "Gagal membuat bobot default BBT-01: " . mysqli_error($conn), 'bobot' => null];
        }

        return [
            'ok' => true,
            'pesan' => 'Bobot default BBT-01 dibuat otomatis.',
            'bobot' => [
                'id_bobot'    => $id_bobot,
                'bpendidikan' => $b_pend,
                'bpengalaman' => $b_peng,
                'bskill'      => $b_skil,
                'busia'       => $b_usia,
                'bkarakter'   => $b_kara,
            ]
        ];
    }

    return ['ok' => false, 'pesan' => "Data bobot dengan ID '$id_bobot' tidak ditemukan di tabel_bobot.", 'bobot' => null];
}

function ambilPekerjaTerdaftar($conn) {
    $daftar = [];
    $sql = "SELECT id_pekerja, nama_pekerja, pendidikan, pengalaman, skill, usia, karakter
            FROM tabel_pekerja
            WHERE status = 1
            ORDER BY nama_pekerja ASC";
    $res = mysqli_query($conn, $sql);
    if (!$res) {
        return ['ok' => false, 'pesan' => "Gagal mengambil data pekerja: " . mysqli_error($conn), 'data' => []];
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $daftar[] = $row;
    }

    return ['ok' => true, 'pesan' => '', 'data' => $daftar];
}

function hitungTopsis($conn, $id_bobot = "BBT-01") {
    $kriteria = [
        'pendidikan' => ['kolom' => 'pendidikan', 'bobot_kolom' => 'bpendidikan', 'tipe' => 'benefit'],
        'pengalaman' => ['kolom' => 'pengalaman', 'bobot_kolom' => 'bpengalaman', 'tipe' => 'benefit'],
        'skill'      => ['kolom' => 'skill',      'bobot_kolom' => 'bskill',      'tipe' => 'benefit'],
        'usia'       => ['kolom' => 'usia',       'bobot_kolom' => 'busia',       'tipe' => 'cost'],
        'karakter'   => ['kolom' => 'karakter',   'bobot_kolom' => 'bkarakter',   'tipe' => 'benefit'],
    ];

    $hasil_return = [
        'ok' => false,
        'pesan' => '',
        'id_bobot' => $id_bobot,
        'pekerja_terdaftar' => [],
        'data_hasil' => [],
    ];

    $ambil_pkj = ambilPekerjaTerdaftar($conn);
    if (!$ambil_pkj['ok']) {
        $hasil_return['pesan'] = $ambil_pkj['pesan'];
        return $hasil_return;
    }

    $alternatif = $ambil_pkj['data'];
    $hasil_return['pekerja_terdaftar'] = $alternatif;

    if (count($alternatif) == 0) {
        $hasil_return['pesan'] = "Tidak ada pekerja berstatus terdaftar (status = 1) untuk dihitung.";
        return $hasil_return;
    }

    $cek_bobot = pastikanBobotTersedia($conn, $id_bobot);
    if (!$cek_bobot['ok']) {
        $hasil_return['pesan'] = $cek_bobot['pesan'];
        return $hasil_return;
    }
    $bobot_row = $cek_bobot['bobot'];

    $w = [];
    foreach ($kriteria as $k => $info) {
        $w[$k] = (float) $bobot_row[$info['bobot_kolom']];
    }

    // Perhitungan pembagi & matriks normalisasi R
    $pembagi = [];
    foreach ($kriteria as $k => $info) {
        $kol = $info['kolom'];
        $jumlah_kuadrat = 0.0;

        for ($i = 0; $i < count($alternatif); $i++) {
            $nilai = (float) $alternatif[$i][$kol];
            $jumlah_kuadrat += ($nilai * $nilai);
        }

        $pembagi[$k] = sqrt($jumlah_kuadrat);
    }

    $matriks_r = [];
    for ($i = 0; $i < count($alternatif); $i++) {
        $baris_r = [];
        foreach ($kriteria as $k => $info) {
            $kol = $info['kolom'];
            $nilai = (float) $alternatif[$i][$kol];
            if ($pembagi[$k] == 0.0) {
                $baris_r[$k] = 0.0;
            } else {
                $baris_r[$k] = $nilai / $pembagi[$k];
            }
        }
        $matriks_r[$i] = $baris_r;
    }

    // Perhitungan matriks normalisasi terbobot Y
    $matriks_y = [];
    for ($i = 0; $i < count($alternatif); $i++) {
        $baris_y = [];
        foreach ($kriteria as $k => $info) {
            $baris_y[$k] = $w[$k] * $matriks_r[$i][$k];
        }
        $matriks_y[$i] = $baris_y;
    }

    // Solusi ideal positif (A+) dan negatif (A-)
    $ideal_pos = [];
    $ideal_neg = [];

    foreach ($kriteria as $k => $info) {
        $kol_y = [];
        for ($i = 0; $i < count($matriks_y); $i++) {
            $kol_y[] = $matriks_y[$i][$k];
        }

        if ($info['tipe'] === 'benefit') {
            $ideal_pos[$k] = max($kol_y);
            $ideal_neg[$k] = min($kol_y);
        } else {
            $ideal_pos[$k] = min($kol_y);
            $ideal_neg[$k] = max($kol_y);
        }
    }

    // Hitung D+, D-, & nilai preferensi V
    $perhitungan = [];
    for ($i = 0; $i < count($alternatif); $i++) {
        $sum_d_pos = 0.0;
        $sum_d_neg = 0.0;

        foreach ($kriteria as $k => $info) {
            $selisih_pos = $matriks_y[$i][$k] - $ideal_pos[$k];
            $selisih_neg = $matriks_y[$i][$k] - $ideal_neg[$k];

            $sum_d_pos += ($selisih_pos * $selisih_pos);
            $sum_d_neg += ($selisih_neg * $selisih_neg);
        }

        $d_pos = sqrt($sum_d_pos);
        $d_neg = sqrt($sum_d_neg);
        $penyebut = $d_pos + $d_neg;

        if ($penyebut == 0.0) {
            $preferensi = 0.0;
        } else {
            $preferensi = $d_neg / $penyebut;
        }

        $perhitungan[] = [
            'id_pekerja'   => $alternatif[$i]['id_pekerja'],
            'nama_pekerja' => $alternatif[$i]['nama_pekerja'],
            'r'            => $matriks_r[$i],
            'y'            => $matriks_y[$i],
            'd_positif'    => $d_pos,
            'd_negatif'    => $d_neg,
            'preferensi'   => $preferensi,
            'ranking'      => 0,
        ];
    }

    // Urutkan dari nilai preferensi ascending (bubble sort)
    $n = count($perhitungan);
    for ($a = 0; $a < $n - 1; $a++) {
        for ($b = 0; $b < $n - $a - 1; $b++) {
            if ($perhitungan[$b]['preferensi'] < $perhitungan[$b + 1]['preferensi']) {
                $temp = $perhitungan[$b];
                $perhitungan[$b] = $perhitungan[$b + 1];
                $perhitungan[$b + 1] = $temp;
            }
        }
    }

    for ($i = 0; $i < $n; $i++) {
        $perhitungan[$i]['ranking'] = $i + 1;
    }

    // Simpan
    $sql_upsert = "INSERT INTO tabel_nilai (
                        id_nilai, id_bobot, id_pekerja,
                        r_pendidikan, r_pengalaman, r_skill, r_usia, r_karakter,
                        y_pendidikan, y_pengalaman, y_skill, y_usia, y_karakter,
                        d_positif, d_negatif, preferensi, ranking
                   ) VALUES (
                        ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?
                   )
                   ON DUPLICATE KEY UPDATE
                        r_pendidikan = VALUES(r_pendidikan),
                        r_pengalaman = VALUES(r_pengalaman),
                        r_skill = VALUES(r_skill),
                        r_usia = VALUES(r_usia),
                        r_karakter = VALUES(r_karakter),
                        y_pendidikan = VALUES(y_pendidikan),
                        y_pengalaman = VALUES(y_pengalaman),
                        y_skill = VALUES(y_skill),
                        y_usia = VALUES(y_usia),
                        y_karakter = VALUES(y_karakter),
                        d_positif = VALUES(d_positif),
                        d_negatif = VALUES(d_negatif),
                        preferensi = VALUES(preferensi),
                        ranking = VALUES(ranking)";

    mysqli_query($conn, "DELETE FROM tabel_nilai");
    $stmt_upsert = mysqli_prepare($conn, $sql_upsert);
    if (!$stmt_upsert) {
        $hasil_return['pesan'] = "Gagal menyiapkan query penyimpanan hasil: " . mysqli_error($conn);
        return $hasil_return;
    }

    mysqli_begin_transaction($conn);

    for ($i = 0; $i < count($perhitungan); $i++) {
        $item = $perhitungan[$i];
        $id_nilai = "NIL-" . $id_bobot . "-" . $item['id_pekerja'];

        $r_pend = $item['r']['pendidikan'];
        $r_peng = $item['r']['pengalaman'];
        $r_skil = $item['r']['skill'];
        $r_usia = $item['r']['usia'];
        $r_kara = $item['r']['karakter'];

        $y_pend = $item['y']['pendidikan'];
        $y_peng = $item['y']['pengalaman'];
        $y_skil = $item['y']['skill'];
        $y_usia = $item['y']['usia'];
        $y_kara = $item['y']['karakter'];

        $d_pos_val = $item['d_positif'];
        $d_neg_val = $item['d_negatif'];
        $pref_val  = $item['preferensi'];
        $rank_val  = $item['ranking'];
        $id_pkj    = $item['id_pekerja'];

        $tipe_param = "sss" . str_repeat("d", 13) . "i";

        mysqli_stmt_bind_param(
        	$stmt_upsert,
		    $tipe_param,
		    $id_nilai,
		    $id_bobot,
		    $id_pkj,
		    $r_pend,
		    $r_peng,
		    $r_skil,
		    $r_usia,
		    $r_kara,
		    $y_pend,
		    $y_peng,
		    $y_skil,
		    $y_usia,
		    $y_kara,
		    $d_pos_val,
		    $d_neg_val,
		    $pref_val,
		    $rank_val
		);

        if (!mysqli_stmt_execute($stmt_upsert)) {
            mysqli_rollback($conn);
            mysqli_stmt_close($stmt_upsert);
            $hasil_return['pesan'] = "Gagal menyimpan hasil TOPSIS: " . mysqli_error($conn);
            return $hasil_return;
        }
    }

    mysqli_commit($conn);
    mysqli_stmt_close($stmt_upsert);

    $hasil_return['ok'] = true;
    $hasil_return['pesan'] = "Perhitungan TOPSIS selesai dan tersimpan untuk ID Bobot '$id_bobot'.";
    $hasil_return['data_hasil'] = $perhitungan;

    return $hasil_return;
}

if (basename($_SERVER['PHP_SELF']) === 'proses_topsis.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_bobot_post = trim($_POST['id_bobot'] ?? "BBT-01");
    if ($id_bobot_post === "") {
        $id_bobot_post = "BBT-01";
    }

    $hasil = hitungTopsis($conn, $id_bobot_post);
    if ($hasil['ok']) {
        header("Location: detail.php?id_bobot=" . urlencode($id_bobot_post));
        exit;
    }

    echo htmlspecialchars($hasil['pesan']);
    exit;
}
?>