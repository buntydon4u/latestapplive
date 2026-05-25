<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/Api_Controller.php';

class Ledgers extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ledger_model');
    }

    public function show($id)
    {
        $this->require_method('GET');
        $this->require_ledger_auth();

        $ledger = $this->Ledger_model->get($id);
        if (!$ledger) {
            return $this->error('Ledger not found', 404);
        }

        return $this->success($ledger, 'Ledger fetched successfully');
    }

    public function children()
    {
        $this->require_method('GET');
        $payload = $this->require_auth();
        $parent_id = (int) ($this->input->get('parent_id', true) ?: (!empty($payload['parent_id']) ? $payload['parent_id'] : $payload['user_id']));

        if (!$parent_id) {
            return $this->validation_error(array('parent_id' => 'Parent ID required'));
        }

        return $this->success($this->Ledger_model->get_children($parent_id), 'Children fetched successfully');
    }

    public function parties()
    {
        $this->require_method('GET');
        $this->require_ledger_auth();
        return $this->success($this->Ledger_model->get_parties(), 'Parties fetched successfully');
    }

    public function shifts()
    {
        $this->require_method('GET');
        $payload = $this->require_ledger_auth();
        $rows = $this->Ledger_model->get_shifts($payload['updated_by']);
        $now = time();

        foreach ($rows as &$row) {
            $limit = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date('H:i', strtotime($row['app_time'])));
            $row['time_limit_timestamp'] = $limit;
            $row['expired'] = $now >= $limit;
        }

        return $this->success($rows, 'Shifts fetched successfully');
    }

    public function balance()
    {
        $this->require_method('GET');
        $payload = $this->require_ledger_auth();
        $balance = $this->Ledger_model->get_balance($payload['user_id']);

        return $this->success(array('balance' => $balance), 'Balance fetched successfully');
    }
}
