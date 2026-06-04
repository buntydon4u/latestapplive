<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/Api_Controller.php';

class PartyJantri extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Party_jantri_model');
    }

    public function meta()
    {
        $this->require_method('GET');
        $payload = $this->require_ledger_auth();

        $updated_by = isset($payload['updated_by']) ? (int) $payload['updated_by'] : 0;
        $rows = $this->Party_jantri_model->get_available_shifts_for_updated_by($updated_by);
        $now = time();

        foreach ($rows as &$row) {
            $open_date = isset($row['user_shift_open_date']) ? $row['user_shift_open_date'] : (isset($row['open_date']) ? $row['open_date'] : '');
            $app_time = isset($row['app_time']) ? $row['app_time'] : '';
            $limit = ($open_date && $app_time) ? strtotime(date('d-m-Y', strtotime($open_date)) . ' ' . date('H:i', strtotime($app_time))) : 0;
            $row['report_pid'] = isset($row['user_shift_timing_id']) ? (int) $row['user_shift_timing_id'] : 0;
            $row['time_limit_timestamp'] = $limit;
            $row['expired'] = $limit ? $now >= $limit : false;
        }

        return $this->success(array(
            'shifts' => $rows
        ), 'Party jantri meta fetched successfully');
    }

    public function report()
    {
        $this->require_method('GET');
        $payload = $this->require_ledger_auth();

        $pid = $this->input->get('pid', true);
        $date = $this->input->get('date', true);
        $partyid = isset($payload['user_id']) ? (int) $payload['user_id'] : 0;
        $updated_by = isset($payload['updated_by']) ? (int) $payload['updated_by'] : 0;
        $errors = array();

        if (!$pid || !ctype_digit((string) $pid)) {
            $errors['pid'] = 'Valid shift required';
        }

        if (!$partyid) {
            $errors['partyid'] = 'Logged-in party required';
        }

        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors['date'] = 'Date must be YYYY-MM-DD';
        } else {
            $parts = explode('-', $date);
            if (!checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0])) {
                $errors['date'] = 'Date is invalid';
            }
        }

        if ($errors) {
            return $this->validation_error($errors);
        }

        $resolved_pid = $this->Party_jantri_model->resolve_report_shift_id((int) $pid, $date, $updated_by);
        $rows = $this->Party_jantri_model->get_party_jantri_report($resolved_pid, $date, (int) $partyid);
        $rows = $this->expand_number_rows($rows);
        $total_amount = 0;

        foreach ($rows as $row) {
            $amounts = explode(',', (string) $row['trn_amt']);
            $total_amount += array_sum(array_map('floatval', $amounts));
        }

        return $this->success(array(
            'filters' => array(
                'pid' => (string) $pid,
                'resolved_pid' => (string) $resolved_pid,
                'date' => $date,
                'partyid' => (string) $partyid
            ),
            'rows' => $rows,
            'summary' => array(
                'totalAmount' => $total_amount,
                'totalRows' => count($rows)
            )
        ), 'Party jantri report fetched successfully');
    }

    private function expand_number_rows(array $rows)
    {
        $expanded = array();

        foreach ($rows as $row) {
            $numbers = explode(',', (string) $row['trnno']);
            $amounts = explode(',', (string) $row['trn_amt']);

            foreach ($numbers as $index => $number) {
                $number = trim($number);
                if ($number === '') {
                    continue;
                }

                $item = $row;
                $item['trnno'] = str_pad($number, 2, '0', STR_PAD_LEFT);
                $item['trn_amt'] = isset($amounts[$index]) ? trim($amounts[$index]) : '0';
                $expanded[] = $item;
            }
        }

        return $expanded;
    }
}
