<?php
/**
 * Proxy for the Railway Scribd downloader API.
 *
 * Set SCRIBD_PYTHON_API to override the base URL.
 * Set SCRIBD_API_KEY if the backend service uses SCRIBD_API_KEY.
 */

require_once __DIR__ . '/function.php';

if (!defined('SCRIBD_BACKEND_URL_FALLBACK')) {
    define('SCRIBD_BACKEND_URL_FALLBACK', 'https://backend-slideshare-production-96f2.up.railway.app');
}

// Enable CORS for cross-domain requests (e.g., from WordPress widgets)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
} else {
    header("Access-Control-Allow-Origin: *");
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    } else {
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
    }
    exit(0);
}

if (!defined('SCRIBD_BACKEND_KEY_FALLBACK')) {
    define('SCRIBD_BACKEND_KEY_FALLBACK', 'sdl-3f8b9c2e7a1d4f6b8e0c5a9d2f7b1e4c6a8d0f3b9e2c7a1d');
}

function scribd_python_api_base()
{
    $b = getenv('SCRIBD_PYTHON_API');
    $b = is_string($b) ? trim($b) : '';
    if ($b !== '') {
        return rtrim($b, '/');
    }
    return rtrim(SCRIBD_BACKEND_URL_FALLBACK, '/');
}

function scribd_backend_api_key()
{
    $k = getenv('SCRIBD_API_KEY');
    $k = is_string($k) ? trim($k) : '';
    if ($k !== '') {
        return $k;
    }
    return trim(SCRIBD_BACKEND_KEY_FALLBACK);
}

function scribd_is_uuid($id)
{
    return (bool) preg_match(
        '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
        $id
    );
}

function scribd_backend_headers($extra = array())
{
    $headers = array_merge(array('Content-Type: application/json'), $extra);
    $key = scribd_backend_api_key();
    if ($key !== '') {
        $headers[] = 'X-API-Key: ' . $key;
    }
    return $headers;
}

$base = scribd_python_api_base();
$key = scribd_backend_api_key();

$action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';

if ($action === 'download') {
    $job_id = isset($_GET['job_id']) ? (string) $_GET['job_id'] : '';
    if (!scribd_is_uuid($job_id)) {
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Invalid job id';
        exit;
    }

    $url = $base . '/api/jobs/' . rawurlencode($job_id) . '/file';
    $headers = array();
    if ($key !== '') {
        $headers[] = 'X-API-Key: ' . $key;
    }

    $metaName = '';
    $metaUrl = $base . '/api/jobs/' . rawurlencode($job_id);
    $chMeta = curl_init($metaUrl);
    curl_setopt($chMeta, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chMeta, CURLOPT_TIMEOUT, 15);
    curl_setopt($chMeta, CURLOPT_HTTPHEADER, $key !== '' ? array('X-API-Key: ' . $key) : array());
    $metaBody = curl_exec($chMeta);
    $metaCode = (int) curl_getinfo($chMeta, CURLINFO_HTTP_CODE);
    curl_close($chMeta);
    if ($metaCode === 200 && is_string($metaBody) && $metaBody !== '') {
        $meta = json_decode($metaBody, true);
        if (is_array($meta) && !empty($meta['filename']) && is_string($meta['filename'])) {
            $metaName = basename($meta['filename']);
        }
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 600);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if ($code !== 200 || !is_string($body)) {
        header('HTTP/1.1 ' . ($code >= 400 ? $code : 502));
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Download failed';
        exit;
    }

    if (is_string($ctype) && stripos($ctype, 'pdf') !== false) {
        header('Content-Type: application/pdf');
    } else {
        header('Content-Type: application/octet-stream');
    }
    $disposition = 'attachment';
    $dlName = $metaName !== '' ? $metaName : 'scribd-document.pdf';
    header('Content-Disposition: ' . $disposition . '; filename="' . str_replace('"', '', $dlName) . '"');
    header('Content-Length: ' . strlen($body));
    header('Cache-Control: no-store');
    echo $body;
    exit;
}

header('Content-Type: application/json; charset=utf-8');

if ($action === 'status') {
    $job_id = isset($_GET['job_id']) ? (string) $_GET['job_id'] : '';
    if (!scribd_is_uuid($job_id)) {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid job id'));
        exit;
    }

    $url = $base . '/api/jobs/' . rawurlencode($job_id);
    $headers = array();
    if ($key !== '') {
        $headers[] = 'X-API-Key: ' . $key;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $resp = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    http_response_code($code >= 100 ? $code : 502);
    if (is_string($resp) && $resp !== '') {
        echo $resp;
    } else {
        echo json_encode(array('error' => 'Backend unreachable'));
    }
    exit;
}

if ($action === 'submit') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(array('error' => 'Method not allowed'));
        exit;
    }

    $url = isset($_POST['scribd_url']) ? trim((string) $_POST['scribd_url']) : '';
    if ($url === '' || stripos($url, 'scribd.com') === false) {
        http_response_code(400);
        echo json_encode(array('error' => 'Please paste a valid Scribd document URL.'));
        exit;
    }

    $turnstileToken = isset($_POST['cf-turnstile-response']) ? (string) $_POST['cf-turnstile-response'] : '';
    if (!verify_turnstile_token($turnstileToken, isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')) {
        http_response_code(400);
        echo json_encode(array('error' => 'Captcha verification failed. Please try again.'));
        exit;
    }

    $payload = json_encode(array('url' => $url));
    $reqUrl = $base . '/api/jobs';

    $ch = curl_init($reqUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_HTTPHEADER, scribd_backend_headers());
    $resp = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    http_response_code($code >= 100 ? $code : 502);
    if (is_string($resp) && $resp !== '') {
        echo $resp;
    } else {
        echo json_encode(array('error' => 'Could not reach Scribd backend. Is the Python API running?'));
    }
    exit;
}

http_response_code(400);
echo json_encode(array('error' => 'Unknown action'));
