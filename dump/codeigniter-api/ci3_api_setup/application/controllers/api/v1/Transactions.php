<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/Api_Controller.php';

class Transactions extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Transaction_model');
    }

    public function index()
    {
        $this->require_method('GET');
        $payload = $this->require_ledger_auth();

        $data = $this->Transaction_model->list_for_ledger(
            $payload['user_id'],
            isset($payload['user_type']) ? $payload['user_type'] : 'ledger'
        );

        return $this->success($data, 'Transactions fetched successfully');
    }

    public function show($id)
    {
        $this->require_method('GET');
        $payload = $this->require_ledger_auth();
        $data = $this->Transaction_model->detail_for_ledger($id, $payload['user_id']);

        if (!$data) {
            return $this->error('Transaction not found', 404);
        }

        return $this->success($data, 'Transaction fetched successfully');
    }

    public function create()
    {
        $this->require_method('POST');
        $payload = $this->require_ledger_auth();

        $errors = $this->validate_required($this->request_data, array(
            'party' => 'Party',
            'dateoftrnforapponly' => 'App transaction date',
            'dateoftrn' => 'Transaction date',
            'shift' => 'Shift',
            'trn_number' => 'Transaction number',
            'trn_amount' => 'Transaction amount'
        ));
        if ($errors) {
            return $this->validation_error($errors);
        }

        $shift_id = $this->resolve_shift_id($this->request_data['shift'], $payload['updated_by']);

        $fields = array(
            'party' => $this->request_data['party'],
            'dateoftrnforapponly' => $this->request_data['dateoftrnforapponly'],
            'dateoftrn' => $this->request_data['dateoftrn'],
            'userid' => $payload['user_id'],
            'entryval' => 'Entry-page.php?login=' . $payload['user_id'] . '&user_type=ledger',
            'updated_by' => $payload['updated_by'],
            'shift' => $shift_id,
            'trn_number' => $this->request_data['trn_number'],
            'trn_amount' => $this->request_data['trn_amount'],
            'submitpost' => 'submit'
        );

        $this->config->load('api', true);
        $path = $this->config->item('remote_transaction_create_path', 'api') ?: '/tbl_transactions/add_transaction_final_app_api';
        return $this->proxy_post($path, $fields, 'Transaction submitted successfully', 'Submission failed. Please check shift times and parameters.');
    }

    public function create_jantri()
    {
        $this->require_method('POST');
        $payload = $this->require_ledger_auth();

        $errors = $this->validate_required($this->request_data, array(
            'party' => 'Party',
            'dateoftrnforapponly' => 'App transaction date',
            'dateoftrn' => 'Transaction date',
            'shift' => 'Shift',
            'gtotal' => 'Grand total'
        ));
        if ($errors) {
            return $this->validation_error($errors);
        }

        $shift_id = $this->resolve_shift_id($this->request_data['shift'], $payload['updated_by']);

        $fields = array(
            'party' => $this->request_data['party'],
            'dateoftrnforapponly' => $this->request_data['dateoftrnforapponly'],
            'dateoftrn' => $this->request_data['dateoftrn'],
            'userid' => $payload['user_id'],
            'entryval' => 'Entry-page.php?login=' . $payload['user_id'] . '&user_type=ledger',
            'updated_by' => $payload['updated_by'],
            'shift' => $shift_id,
            'ttamntt' => '0',
            'gtotal' => $this->request_data['gtotal'],
            'submitpost' => 'submit'
        );

        foreach (array('trn_amount', 'a', 'b') as $key) {
            if (isset($this->request_data[$key])) {
                $fields[$key] = $this->request_data[$key];
            }
        }

        return $this->proxy_post('/tbl_jantri/add_jantri_form_app', $fields, 'Jantri submitted successfully', 'Jantri submission failed.');
    }

    public function delete($id)
    {
        $this->require_method('DELETE');
        $payload = $this->require_ledger_auth();

        if (!$id) {
            return $this->validation_error(array('id' => 'Transaction ID required'));
        }

        $result = $this->Transaction_model->delete_for_ledger(
            $id,
            $payload['user_id'],
            isset($payload['user_type']) ? $payload['user_type'] : 'ledger'
        );

        if (empty($result['success'])) {
            if ($result['code'] === 'not_found') {
                return $this->error('Transaction not found', 404);
            }
            if ($result['code'] === 'time_expired') {
                return $this->error('The allowed time for deleting this transaction has expired', 403);
            }
            return $this->error('Transaction could not be deleted', 500);
        }

        return $this->success(array('id' => (int) $id), 'Transaction deleted successfully');
    }

    protected function proxy_post($path, array $fields, $success_message, $failure_message)
    {
        $this->config->load('api', true);
        $base_url = rtrim($this->config->item('remote_transaction_base_url', 'api'), '/');
        $target_url = $base_url . $path;

        $ch = curl_init($target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return $this->error('Remote submission failed', 502, array('remote_error' => $error));
        }

        $body = $response;
        if (!empty($info['header_size'])) {
            $body = substr($response, (int) $info['header_size']);
        }
        $json = json_decode(trim($body), true);
        if (is_array($json)) {
            $ok = !empty($json['status']) || !empty($json['success']);
            if ($ok) {
                return $this->success(isset($json['data']) ? $json['data'] : $json, isset($json['message']) ? $json['message'] : $success_message, 201);
            }

            return $this->error(isset($json['message']) ? $json['message'] : $failure_message, 422, array(
                'remote' => $json,
                'target_url' => $target_url
            ));
        }

        $redirect_url = isset($info['redirect_url']) ? $info['redirect_url'] : '';
        if (!$redirect_url && preg_match('/Location:\s*([^\r\n]+)/i', $response, $matches)) {
            $redirect_url = trim($matches[1]);
        }

        if (strpos($redirect_url, 'status=1') !== false) {
            return $this->success(array('status' => 1), $success_message, 201);
        }

        return $this->error($failure_message, 422, array('redirect' => $redirect_url, 'target_url' => $target_url));
    }

    protected function resolve_shift_id($submitted_shift, $updated_by)
    {
        $submitted_shift = (int) $submitted_shift;
        if (!$submitted_shift || !$updated_by) {
            return $submitted_shift;
        }

        $fromdate = date('Y-m-d');
        $todate = date('Y-m-d', time() + (12 * 60 * 60));

        $rows = $this->db
            ->select('user_shift_timings.id, user_shift_timings.shift_id, user_shift_timings.app_time, user_shift_timings.open_date')
            ->from('user_shift_timings')
            ->where('user_shift_timings.updated_by', $updated_by)
            ->where('user_shift_timings.open_date >=', $fromdate)
            ->where('user_shift_timings.open_date <=', $todate)
            ->group_start()
                ->where('user_shift_timings.id', $submitted_shift)
                ->or_where('user_shift_timings.shift_id', $submitted_shift)
            ->group_end()
            ->order_by('user_shift_timings.open_date', 'ASC')
            ->order_by('user_shift_timings.master', 'ASC')
            ->get()
            ->result_array();

        $now = time();
        foreach ($rows as $row) {
            $limit = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date('H:i', strtotime($row['app_time'])));
            if ($now < $limit && (int) $row['id'] === $submitted_shift) {
                return (int) $row['id'];
            }
        }

        foreach ($rows as $row) {
            $limit = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date('H:i', strtotime($row['app_time'])));
            if ($now < $limit && (int) $row['shift_id'] === $submitted_shift) {
                return (int) $row['id'];
            }
        }

        return $submitted_shift;
    }
}
