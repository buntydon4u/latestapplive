<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('get_bearer_token')) {
    function get_bearer_token($authorization_header)
    {
        if (!$authorization_header) {
            return null;
        }

        if (preg_match('/Bearer\s+(\S+)/i', $authorization_header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
