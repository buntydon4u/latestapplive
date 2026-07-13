<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jwt
{
    protected $secret;
    protected $ttl;

    public function __construct()
    {
        $CI =& get_instance();
        $CI->config->load('api', true);
        $this->secret = $CI->config->item('jwt_secret', 'api');
        $this->ttl = (int) $CI->config->item('jwt_ttl', 'api');
    }

    public function encode(array $payload)
    {
        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + $this->ttl;
        $payload['jti'] = bin2hex(random_bytes(16));

        $segments = array(
            $this->base64url_encode(json_encode(array('typ' => 'JWT', 'alg' => 'HS256'))),
            $this->base64url_encode(json_encode($payload))
        );

        $signature = hash_hmac('sha256', implode('.', $segments), $this->secret, true);
        $segments[] = $this->base64url_encode($signature);

        return implode('.', $segments);
    }

    public function decode($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token');
        }

        list($header64, $payload64, $signature64) = $parts;
        $expected = $this->base64url_encode(hash_hmac('sha256', $header64 . '.' . $payload64, $this->secret, true));

        if (!hash_equals($expected, $signature64)) {
            throw new Exception('Invalid token signature');
        }

        $payload = json_decode($this->base64url_decode($payload64), true);
        if (!is_array($payload)) {
            throw new Exception('Invalid token payload');
        }

        if (empty($payload['exp']) || time() >= (int) $payload['exp']) {
            throw new Exception('Token expired');
        }

        return $payload;
    }

    protected function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
