<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Options extends CI_Controller
{
    public function index()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        return $this->output
            ->set_status_header(204)
            ->set_output('');
    }
}
