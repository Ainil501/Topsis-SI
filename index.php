<?php
$conn = mysqli_connect("localhost","root","","si_database");

if(isset($_POST['status'])){
    $id = $_POST['status'];
    $aksi = isset($_POST['aksi']) ? 1 : 0;

    $stmt = mysqli_prepare($conn,
        "UPDATE tabel_pekerja SET status = ? WHERE id_pekerja = ?"
    );
    mysqli_stmt_bind_param($stmt, "is", $aksi, $id);
    mysqli_stmt_execute($stmt);

    header("Location: index.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM tabel_pekerja");
?>
<?php include "header.php";?>

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
