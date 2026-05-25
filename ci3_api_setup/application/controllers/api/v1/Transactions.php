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

        $fields = array(
            'party' => $this->request_data['party'],
            'dateoftrnforapponly' => $this->request_data['dateoftrnforapponly'],
            'dateoftrn' => $this->request_data['dateoftrn'],
            'userid' => $payload['user_id'],
            'entryval' => 'Entry-page.php?login=' . $payload['user_id'] . '&user_type=ledger',
            'updated_by' => $payload['updated_by'],
            'shift' => $this->request_data['shift'],
            'trn_number' => $this->request_data['trn_number'],
            'trn_amount' => $this->request_data['trn_amount'],
            'submitpost' => 'submit'
        );

        return $this->proxy_post('/tbl_transactions/add_transaction_final_app', $fields, 'Transaction submitted successfully', 'Submission failed. Please check shift times and parameters.');
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

        $fields = array(
            'party' => $this->request_data['party'],
            'dateoftrnforapponly' => $this->request_data['dateoftrnforapponly'],
            'dateoftrn' => $this->request_data['dateoftrn'],
            'userid' => $payload['user_id'],
            'entryval' => 'Entry-page.php?login=' . $payload['user_id'] . '&user_type=ledger',
            'updated_by' => $payload['updated_by'],
            'shift' => $this->request_data['shift'],
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

        $this->config->load('api', true);
        $base_url = rtrim($this->config->item('remote_transaction_base_url', 'api'), '/');
        $url = $base_url . '/tbl_transactions/remove_app/' . (int) $id . '/' . (int) $payload['user_id'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return $this->error('Delete request failed', 502, array('remote_error' => $error));
        }

        return $this->success(array('id' => (int) $id), 'Transaction deleted successfully');
    }

    protected function proxy_post($path, array $fields, $success_message, $failure_message)
    {
        $this->config->load('api', true);
        $base_url = rtrim($this->config->item('remote_transaction_base_url', 'api'), '/');

        $ch = curl_init($base_url . $path);
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

        $redirect_url = isset($info['redirect_url']) ? $info['redirect_url'] : '';
        if (!$redirect_url && preg_match('/Location:\s*([^\r\n]+)/i', $response, $matches)) {
            $redirect_url = trim($matches[1]);
        }

        if (strpos($redirect_url, 'status=1') !== false) {
            return $this->success(array('status' => 1), $success_message, 201);
        }

        return $this->error($failure_message, 422, array('redirect' => $redirect_url));
    }
}
