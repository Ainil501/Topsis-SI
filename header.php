<?php
$halaman_sekarang = basename($_SERVER['PHP_SELF']);
?>

<style>
body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
}

.topnav {
    overflow: hidden;
    background-color: #333;
}

.topnav a {
    float: left;
    color: #f2f2f2;
    text-align: center;
    padding: 14px 16px;
    text-decoration: none;
    font-size: 17px;
}

.topnav a:hover {
    background-color: #ddd;
    color: black;
}

.topnav a.active {
    background-color: #04AA6D;
    color: white;
}
</style>

<div class="topnav">
    <a href="index.php"
       class="<?= $halaman_sekarang == 'index.php' ? 'active' : '' ?>">
       Home
    </a>

    <a href="topsis.php"
       class="<?= $halaman_sekarang == 'topsis.php' ? 'active' : '' ?>">
       Topsis
    </a>

    <a href="detail.php?id_bobot=BBT-01"
       class="<?= $halaman_sekarang == 'detail.php' ? 'active' : '' ?>">
       Detail
    </a>
</div>
