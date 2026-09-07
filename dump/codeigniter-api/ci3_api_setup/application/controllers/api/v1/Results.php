<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/Api_Controller.php';

class Results extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Result_model');
        $this->load->model('Tbl_openno_model');
    }

    public function ledger_till_date_reports_api()
{
    $this->require_method('POST');
    $payload = $this->require_ledger_auth();
    date_default_timezone_set('Asia/Kolkata');

    $input = json_decode($this->input->raw_input_stream, true);

    if (!is_array($input) || empty($input)) {
        $input = $this->input->post(NULL, TRUE);
    }

    $ledger_id = isset($input['ledger_id'])
        ? trim((string) $input['ledger_id'])
        : trim((string) $payload['user_id']);
    $report_date = isset($input['date'])
        ? trim((string) $input['date'])
        : date('Y-m-d');

    if ($ledger_id === '' || $report_date === '' || strtotime($report_date) === FALSE) {
        return $this->output
            ->set_status_header(400)
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => false,
                'message' => 'ledger_id and valid date are required'
            )));
    }

    $params = array(
        'ledger_id' => $ledger_id,
        'date' => $report_date
    );

    $result = $this->Tbl_openno_model
        ->ledger_till_date_result_today_api($params);

    return $this->output
        ->set_status_header(200)
        ->set_content_type('application/json')
        ->set_output(json_encode(array(
            'status' => true,
            'data' => $result
        )));
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
