<?php include "header.php";?>
<?php
$conn = mysqli_connect("localhost","root","","si_database");

$result = mysqli_query($conn, "SELECT *, EXISTS(select 1 FROM tabel_daftar d WHERE d.id_pekerja = p.id_pekerja) status FROM tabel_pekerja p");

if(isset($_POST['status'])){
  $id = $_POST['status'];
  if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM tabel_daftar WHERE id_pekerja = " . $id))>0){
    mysqli_query($conn, "DELETE FROM tabel_daftar WHERE id_pekerja = " . $id);
  }else{
    $id_daftar = (mysqli_fetch_assoc(mysqli_query($conn, "SELECT MAX(id_daftar) id FROM tabel_daftar"))['id'] ?? 0 )+ 1;
    mysqli_query($conn, "INSERT INTO tabel_daftar VALUES (" . $id_daftar . ", " . $id . ")");
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
        <td><form method="post">
          <input type="checkbox" name="status" onchange="this.form.submit()" value="<?php echo $r['id_pekerja']; ?>" <?= $r['status'] ? "checked" : ""?>>
        </form></td>
      </tr>
      <?php endwhile;?>

  </table></p>
</div>

</body>
</html>