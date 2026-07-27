<?php

if (!defined('TURNSTILE_SITE_KEY_DEFAULT')) {
    define('TURNSTILE_SITE_KEY_DEFAULT', '0x4AAAAAADEQLuEOkWL_g15s');
}

if (!defined('TURNSTILE_SECRET_KEY_DEFAULT')) {
    define('TURNSTILE_SECRET_KEY_DEFAULT', '0x4AAAAAADEQLjZx_NxC26h8mMKb_hYhYk8');
}

if (!function_exists('turnstile_site_key')) {
    function turnstile_site_key()
    {
        $key = getenv('TURNSTILE_SITE_KEY');
        $key = is_string($key) ? trim($key) : '';
        return $key !== '' ? $key : TURNSTILE_SITE_KEY_DEFAULT;
    }
}

if (!function_exists('turnstile_secret_key')) {
    function turnstile_secret_key()
    {
        $key = getenv('TURNSTILE_SECRET_KEY');
        $key = is_string($key) ? trim($key) : '';
        return $key !== '' ? $key : TURNSTILE_SECRET_KEY_DEFAULT;
    }
}

if (!function_exists('verify_turnstile_token')) {
    function verify_turnstile_token($token, $remoteIp = '')
    {
        $token = is_string($token) ? trim($token) : '';
        $secret = turnstile_secret_key();

        if ($token === '' || $secret === '') {
            return false;
        }

        $payload = http_build_query(array(
            'secret' => $secret,
            'response' => $token,
            'remoteip' => is_string($remoteIp) ? $remoteIp : '',
        ));

        $response = false;
        if (function_exists('curl_init')) {
            $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
            curl_setopt_array($ch, array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
            ));
            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $payload,
                    'timeout' => 15,
                ),
            ));
            $response = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
        }

        if (!is_string($response) || $response === '') {
            return false;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) && !empty($decoded['success']);
    }
}
