<?php include "header.php";?>
<?php
$conn = mysqli_connect("localhost","root","","si_database");

$result = mysqli_query($conn, "SELECT *, EXISTS(select 1 FROM tabel_daftar d WHERE d.id_pekerja = p.id_pekerja) status FROM tabel_pekerja p");

if(isset($_POST['status'])){
    $id = (int) $_POST['status'];

    if(isset($_POST['aksi'])){
        $id_daftar = (mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT MAX(id_daftar) AS id FROM tabel_daftar"
        ))['id'] ?? 0) + 1;

        mysqli_query($conn,
            "INSERT INTO tabel_daftar (id_daftar, id_pekerja)
             VALUES ($id_daftar, $id)"
        );

    }else{
        mysqli_query($conn,
            "DELETE FROM tabel_daftar WHERE id_pekerja = $id"
        );
    }

    header("Location: index.php");
    exit;
}
?>

<div style="padding-left:16px">
  <h2>Tabel Pekerja</h2>
  <p><table>
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>pendidikan</th>
        <th>pengalaman</th>
        <th>skill</th>
        <th>usia</th>
        <th>karakter</th>
        <th>Status daftar</th>
    </tr>
    <?php 
    $no = 1;
    while($r = mysqli_fetch_assoc($result)):?>
      <tr>
        <td><?= $no++?></td>
        <td><?= $r['nama_pekerja']?></td>
        <td><?= $r['pendidikan']?></td>
        <td><?= $r['pengalaman']?></td>
        <td><?= $r['skill']?></td>
        <td><?= $r['usia']?></td>
        <td><?= $r['karakter']?></td>
        <td>
          <form method="post">
              <input type="hidden" name="status" value="<?= $r['id_pekerja'] ?>">
              <input type="checkbox" name="aksi" onchange="this.form.submit()"
                  <?= $r['status'] ? "checked" : "" ?>>
          </form>
        </td>
      </tr>
      <?php endwhile;?>

  </table></p>
</div>

</body>
</html>