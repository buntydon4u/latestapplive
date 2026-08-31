# Target Portal Entry API

Add this method to the target portal controller that currently contains
`add_transaction_final_app()`, usually `Tbl_transactions`.

## Route

If custom routing is enabled, add this to the target portal
`application/config/routes.php`:

```php
$route['tbl_transactions/add_transaction_final_app_api']['post'] = 'tbl_transactions/add_transaction_final_app_api';
```

New target URL:

```text
https://new.bull99exch.com/tbl_transactions/add_transaction_final_app_api
```

After deploying this target method, point the CI3 API proxy to it:

```text
REMOTE_TRANSACTION_CREATE_PATH=/tbl_transactions/add_transaction_final_app_api
```

The React app can keep posting to:

```text
POST /index.php/api/v1/transactions
```

## Controller Code

```php
private function entry_api_response($http_status, array $payload)
{
    return $this->output
        ->set_content_type('application/json', 'utf-8')
        ->set_status_header($http_status)
        ->set_output(json_encode($payload));
}

public function add_transaction_final_app_api()
{
    date_default_timezone_set('Asia/Kolkata');

    if ($this->input->method(TRUE) !== 'POST') {
        return $this->entry_api_response(405, array(
            'status' => false,
            'message' => 'Method not allowed'
        ));
    }

    $post = $this->input->post(NULL, false);
    $json = json_decode($this->input->raw_input_stream, true);
    if (is_array($json)) {
        $post = array_merge(is_array($post) ? $post : array(), $json);
    }

    $required = array('party', 'userid', 'updated_by', 'shift', 'dateoftrn', 'trn_number', 'trn_amount');
    foreach ($required as $field) {
        if (!isset($post[$field]) || $post[$field] === '') {
            return $this->entry_api_response(422, array(
                'status' => false,
                'code' => 'validation_error',
                'message' => $field . ' is required'
            ));
        }
    }

    $now = strtotime(date('H:i'));
    if ($now >= strtotime('04:59') && $now < strtotime('08:00')) {
        return $this->entry_api_response(422, array(
            'status' => false,
            'code' => 'blocked_time',
            'message' => 'Time Expired. Please try again.'
        ));
    }

    if (!is_array($post['trn_number']) || !is_array($post['trn_amount'])) {
        return $this->entry_api_response(422, array(
            'status' => false,
            'code' => 'validation_error',
            'message' => 'Number and amount must be arrays'
        ));
    }

    $numbers = array();
    $amounts = array();
    foreach ($post['trn_number'] as $key => $number) {
        $number = preg_replace('/\D/', '', (string) $number);
        $amount = isset($post['trn_amount'][$key]) ? preg_replace('/\D/', '', (string) $post['trn_amount'][$key]) : '';

        if ($number === '' || $amount === '' || strpos($number, ',') !== false) {
            continue;
        }
        if ($number === '100') {
            $number = '00';
        }
        if (strlen($number) > 2) {
            return $this->entry_api_response(422, array(
                'status' => false,
                'code' => 'invalid_number',
                'message' => 'Only 00 to 99 numbers are allowed'
            ));
        }

        $numbers[] = str_pad($number, 2, '0', STR_PAD_LEFT);
        $amounts[] = (int) $amount;
    }

    if (empty($numbers)) {
        return $this->entry_api_response(422, array(
            'status' => false,
            'code' => 'validation_error',
            'message' => 'Please enter at least one valid number and amount'
        ));
    }

    $shift = null;
    $all_shifts = $this->Tbl_shift_model->get_tbl_shift_usershift_bymasterid($post['updated_by']);
    foreach ($all_shifts as $candidate) {
        if ((int) $candidate['id'] === (int) $post['shift']) {
            $shift = $candidate;
            break;
        }
    }

    if (!$shift) {
        return $this->entry_api_response(422, array(
            'status' => false,
            'code' => 'invalid_shift',
            'message' => 'Please select a valid shift'
        ));
    }

    $close_time = strtotime(date('d-m-Y', strtotime($shift['open_date'])) . ' ' . date('H:i', strtotime($shift['app_time'])));
    if ((int) $shift['shift_id'] === 11) {
        $close_time = strtotime('+7 hours', $close_time);
    }
    if (time() > $close_time) {
        return $this->entry_api_response(422, array(
            'status' => false,
            'code' => 'time_expired',
            'message' => 'Time Expired. Please try again.'
        ));
    }

    $post['trn_number'] = $numbers;
    $post['trn_amount'] = $amounts;
    $post['ttamntt'] = array_sum($amounts);
    $post['dateoftrn'] = date('Y-m-d', strtotime($post['dateoftrn']));
    $post['entryval'] = isset($post['entryval']) && $post['entryval']
        ? $post['entryval']
        : 'Entry-page.php?login=' . $post['party'] . '&user_type=ledger';

    $coinbal = get_client_coin_balance($post['party']);
    if ($coinbal < $post['ttamntt']) {
        return $this->entry_api_response(422, array(
            'status' => false,
            'code' => 'insufficient_balance',
            'message' => 'Insufficient Coin Balance',
            'balance' => (float) $coinbal,
            'required_amount' => (float) $post['ttamntt']
        ));
    }

    $this->db->trans_begin();

    $coin_id = $this->CoinModel->allocateCoinsapp($post['party'], $post['updated_by'], $post['ttamntt'], $post['shift']);
    if (!$coin_id) {
        $this->db->trans_rollback();
        return $this->entry_api_response(422, array(
            'status' => false,
            'code' => 'coin_allocation_failed',
            'message' => 'Coin allocation failed'
        ));
    }
    $this->CoinModel->updatebal($post['party'], ($coinbal - $post['ttamntt']));

    $ledger = $this->Tbl_ledger_model->get_tbl_ledger($post['party']);
    $master = array(
        'shift_id' => $post['shift'],
        'party_id' => $post['party'],
        'master_id' => $post['updated_by'],
        't_date' => $post['dateoftrn'],
        'total_number_amount' => $post['ttamntt'],
        'total_akhar_amount' => 0,
        'coin_id' => $coin_id
    );

    $date_shift_party_entry = array(
        'master_id' => 0,
        'DaraRate' => isset($ledger['dara_rate']) ? $ledger['dara_rate'] : 0,
        'Commision' => isset($ledger['dara_commision']) ? $ledger['dara_commision'] : 0,
        'AkarRate' => isset($ledger['akhar_rate']) ? $ledger['akhar_rate'] : 0,
        'Commission' => isset($ledger['akhar_commission']) ? $ledger['akhar_commission'] : 0,
        'Rebait' => isset($ledger['rebate']) ? $ledger['rebate'] : 0,
        'TP_person' => isset($ledger['tp_party_id']) ? $ledger['tp_party_id'] : 0,
        'TP_perc' => isset($ledger['tppercentage']) ? $ledger['tppercentage'] : 0,
        'ShiftId' => $post['shift'],
        'PartyId' => $post['party'],
        'Date' => $post['dateoftrn'],
        'Total_Number_amount' => $post['ttamntt'],
        'Total_Akhar_amount' => 0,
        'Total_amount' => $post['ttamntt']
    );

    $transaction_id = $this->Tbl_transactions_model->add_tbl_transaction($master);
    $this->Tbl_transactions_model->add_tbl_only_transaction_may($transaction_id, $post);

    $entry_date = date('d-m-Y', strtotime($post['dateoftrn']));
    $existing = $this->Tbl_transactions_model->get_Date_shift_party_entry($post['shift'], $post['party'], $entry_date);
    if (!empty($existing)) {
        $old = $this->Tbl_transactions_model->get_transaction_result_total($existing[0]['id']);
        $update = array(
            'Total_Number_amount' => $post['ttamntt'] + (int) $old['Total_Number_amount'],
            'Total_Akhar_amount' => (int) $old['Total_Akhar_amount'],
            'Total_amount' => $post['ttamntt'] + (int) $old['Total_amount']
        );
        $entry_id = $this->Tbl_transactions_model->update_Tb_Date_shift_party_entry($existing[0]['id'], $update);
    } else {
        $entry_id = $this->Tbl_transactions_model->add_Tb_Date_shift_party_entry($date_shift_party_entry);
    }

    if (method_exists($this, 'logAction')) {
        $this->logAction('entry inserted', $date_shift_party_entry);
    }

    if ($this->db->trans_status() === false) {
        $this->db->trans_rollback();
        return $this->entry_api_response(500, array(
            'status' => false,
            'code' => 'insert_failed',
            'message' => 'Entry could not be saved'
        ));
    }

    $this->db->trans_commit();

    return $this->entry_api_response(201, array(
        'status' => true,
        'message' => 'Entry submitted successfully',
        'data' => array(
            'status' => 1,
            'entry_id' => $entry_id,
            'transaction_id' => $transaction_id,
            'coin_id' => $coin_id,
            'total_amount' => $post['ttamntt'],
            'shift' => $post['shift']
        )
    ));
}
```
