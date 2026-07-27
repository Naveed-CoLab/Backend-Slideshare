<?php
require_once('tcpdf/tcpdf.php');

// cURL fetch
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $selectedImages = $_POST['selectedImages'] ?? [];

    if (!empty($selectedImages)) {

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $pdf = new TCPDF();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(false);

        foreach ($selectedImages as $imageUrl) {

            $imageData = getImage($imageUrl);
            if ($imageData === false) continue;

            // Get image size
            $imgInfo = @getimagesizefromstring($imageData);
            if ($imgInfo === false) continue;

            $imgWidth = $imgInfo[0];
            $imgHeight = $imgInfo[1];

            // Decide orientation
            $orientation = ($imgWidth > $imgHeight) ? 'L' : 'P';

            // Add page with orientation
            $pdf->AddPage($orientation);

            // Page size
            $pageWidth = $pdf->getPageWidth();
            $pageHeight = $pdf->getPageHeight();

            // Margins
            $margin = 10;
            $maxWidth = $pageWidth - ($margin * 2);
            $maxHeight = $pageHeight - ($margin * 2);

            // Scale image proportionally
            $ratio = min($maxWidth / $imgWidth, $maxHeight / $imgHeight);

            $newWidth = $imgWidth * $ratio;
            $newHeight = $imgHeight * $ratio;

            // Center position
            $x = ($pageWidth - $newWidth) / 2;
            $y = ($pageHeight - $newHeight) / 2;

            // Add image centered
            $pdf->Image('@' . $imageData, $x, $y, $newWidth, $newHeight, '', '', '', false, 300);
        }

        $pdfName = 'images_' . bin2hex(random_bytes(5)) . '.pdf';
        $pdf->Output($pdfName, 'D');
        exit();

    } else {
        echo 'No images selected.';
    }

} else {
    echo 'Invalid request.';
}