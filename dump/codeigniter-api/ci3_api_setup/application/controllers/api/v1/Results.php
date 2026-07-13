<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/Api_Controller.php';

class Results extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Result_model');
    }

    public function latest()
    {
        $this->require_method('GET');
        $payload = $this->require_ledger_auth();
        $updated_by = isset($payload['updated_by']) ? (int) $payload['updated_by'] : 0;

        if (!$updated_by) {
            return $this->error('Admin reference missing for this ledger', 422);
        }

        $limit = (int) ($this->input->get('limit', true) ?: 10);

        return $this->success(
            $this->Result_model->latest_declared_by_shift($updated_by, $limit),
            'Latest declared results fetched successfully'
        );
    }
}
