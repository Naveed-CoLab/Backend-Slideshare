<?php
$downloadURL = $_GET['link'];
 
 header('Content-Type: application/force-download');

    header("Cache-Control: public");
    header("Content-Description: File Transfer");
header('Content-disposition: attachment; filename="' . basename($downloadURL) . '"');
    header("Content-Transfer-Encoding: binary");

    readfile($downloadURL);

?>
