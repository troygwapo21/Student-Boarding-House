<?php

class RecaptchaService
{
    public function verify(string $token, ?string $remoteIp = null): bool
    {
        $secretKey = RECAPTCHA_SECRET_KEY;
        $verifyUrl = RECAPTCHA_VERIFY_URL;

        $data = [
            'secret' => $secretKey,
            'response' => $token,
        ];

        if ($remoteIp) {
            $data['remoteip'] = $remoteIp;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verifyUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            error_log('reCAPTCHA verification failed: cURL error or HTTP ' . $httpCode);
            return false;
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('reCAPTCHA verification failed: invalid JSON response');
            return false;
        }

        return ($result['success'] ?? false) === true;
    }

    public function getSiteKey(): string
    {
        return RECAPTCHA_SITE_KEY;
    }

    public function renderWidget(): string
    {
        $siteKey = $this->getSiteKey();
        return <<<HTML
<div class="g-recaptcha" data-sitekey="{$siteKey}"></div>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
HTML;
    }
}