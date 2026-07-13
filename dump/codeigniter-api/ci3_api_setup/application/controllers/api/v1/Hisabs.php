<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/Api_Controller.php';

class Hisabs extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hisab_model');
    }

    public function index()
    {
        $this->require_method('GET');
        $payload = $this->require_ledger_auth();

        return $this->success(
            $this->Hisab_model->list_for_ledger($payload['user_id'], $payload['updated_by']),
            'Hisab dates fetched successfully'
        );
    }
}
