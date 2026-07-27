<?php

if (!isset($_POST['selectedImages']) || !is_array($_POST['selectedImages'])) {
    die('No images received');
}

$images = $_POST['selectedImages'];

// Function to fetch image using cURL
function getImage($url) {
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0'
    ]);

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) return false;

    return $data;
}

// Create ZIP
$zipFileName = 'images_' . bin2hex(random_bytes(5)) . '.zip';
$zipPath = sys_get_temp_dir() . '/' . $zipFileName;

$zip = new ZipArchive();

if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    die('Cannot create ZIP file');
}

$count = 1;

foreach ($images as $imgUrl) {

    $imageData = getImage($imgUrl);

    if ($imageData === false) continue;

    $ext = pathinfo(parse_url($imgUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (!$ext) $ext = 'jpg';

    $zip->addFromString('image_' . $count . '.' . $ext, $imageData);

    $count++;
}

$zip->close();

// Download ZIP
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
header('Content-Length: ' . filesize($zipPath));

readfile($zipPath);
unlink($zipPath);
exit;