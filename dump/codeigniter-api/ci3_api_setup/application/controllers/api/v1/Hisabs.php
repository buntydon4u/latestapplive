<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/Api_Controller.php';

class Hisabs extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hisab_model');
        $this->load->model('Ledger_model');
        $this->load->model('Transaction_model');
        $this->load->model('Result_model');
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

    public function report()
    {
        $this->require_method('GET');
        $payload = $this->require_ledger_auth();

        $ledger_id = (int) ($this->input->get('ledger_id', true) ?: $payload['user_id']);
        $selected_date = trim((string) $this->input->get('date', true));
        $selected_master = trim((string) $this->input->get('master', true));
        $start_date = trim((string) $this->input->get('start_date', true));
        $end_date = trim((string) $this->input->get('end_date', true));

        if (!$selected_date) {
            $selected_date = date('d-m-Y');
        }

        $ledger = $this->Ledger_model->get($ledger_id);
        if (!$ledger) {
            return $this->error('Ledger not found', 404);
        }

        if ($start_date && $end_date) {
            $legacy_report = $this->build_statement_report($ledger_id, $start_date, $end_date, $ledger, $payload);
            return $this->success($legacy_report, 'report fetched successfully');
        }

        $transactions = $this->Transaction_model->list_for_ledger($ledger_id, isset($payload['user_type']) ? $payload['user_type'] : 'ledger');
        $hisabs = $this->Hisab_model->list_for_ledger($ledger_id, $ledger['updated_by']);
        $results = $ledger['updated_by'] ? $this->Result_model->latest_declared_by_shift($ledger['updated_by'], 50) : array();

        $selected_iso = $this->to_iso_date($selected_date);
        $rows = array();
        $running = 0;

        foreach ($transactions as $row) {
            $row_date = $this->to_iso_date($row['t_date']);
            if ($selected_iso && $row_date && strcmp($row_date, $selected_iso) > 0) {
                continue;
            }

            $amount = (float) ($row['total_amount'] ?? 0);
            $running += $amount;
            $rows[] = array(
                'id' => 'tx-' . $row['id'],
                'date' => $row['display_date'] ?? $row['t_date'],
                'type' => 'Transaction',
                'label' => $row['shift_name'] ?? 'Transaction',
                'amount' => $amount,
                'delta' => $amount,
                'running_total' => $running,
                'note' => 'Transaction total'
            );
        }

        foreach ($hisabs as $row) {
            $row_date = $this->to_iso_date($row['date']);
            if ($selected_iso && $row_date && strcmp($row_date, $selected_iso) > 0) {
                continue;
            }

            $amount = (float) ($row['today_hisab'] ?? 0);
            $delta = 0 - $amount;
            $running += $delta;
            $rows[] = array(
                'id' => 'pl-' . $row['date'],
                'date' => $row['date'],
                'type' => 'P/L',
                'label' => 'Hisab adjustment',
                'amount' => $amount,
                'delta' => $delta,
                'running_total' => $running,
                'note' => 'Today hisab'
            );
        }

        foreach ($results as $row) {
            $row_date = $this->to_iso_date($row['declared_date'] ?? '');
            if ($selected_iso && $row_date && strcmp($row_date, $selected_iso) > 0) {
                continue;
            }

            $amount = (float) ($row['today_hisab'] ?? 0);
            $rows[] = array(
                'id' => 'result-' . ($row['id'] ?? uniqid()),
                'date' => $row['declared_date'] ?? ($row['date'] ?? ''),
                'type' => 'Result',
                'label' => $row['shift_name'] ?? 'Declared result',
                'amount' => $amount,
                'delta' => 0,
                'running_total' => $running,
                'note' => 'Declared result'
            );
        }

        usort($rows, function ($a, $b) {
            $left = $this->to_iso_date($a['date']);
            $right = $this->to_iso_date($b['date']);
            $cmp = strcmp($left, $right);
            if ($cmp !== 0) {
                return $cmp;
            }

            return strcmp($a['id'], $b['id']);
        });

        $transaction_total = 0;
        $hisab_total = 0;
        foreach ($rows as $row) {
            if ($row['type'] === 'Transaction') {
                $transaction_total += (float) $row['amount'];
            }
            if ($row['type'] === 'P/L') {
                $hisab_total += (float) $row['amount'];
            }
        }

        $normalized_selected_date = $this->to_iso_date($selected_date);

        return $this->success(array(
            'ledger' => $ledger,
            'selected_date' => $selected_date,
            'selected_date_iso' => $normalized_selected_date,
            'selected_master' => $selected_master,
            'selected_hisab' => $this->find_hisab($hisabs, $normalized_selected_date),
            'rows' => $rows,
            'summary' => array(
                'transaction_total' => $transaction_total,
                'hisab_total' => $hisab_total,
                'net_movement' => $transaction_total - $hisab_total,
                'running_total' => $running,
                'transaction_count' => count(array_filter($rows, function ($row) {
                    return $row['type'] === 'Transaction';
                })),
                'hisab_count' => count(array_filter($rows, function ($row) {
                    return $row['type'] === 'P/L';
                })),
                'result_count' => count(array_filter($rows, function ($row) {
                    return $row['type'] === 'Result';
                }))
            ),
            'report fetched successfully'
        ));
    }

    protected function build_statement_report($ledger_id, $start_date, $end_date, array $ledger, array $payload)
    {
        $start_datetime = date('Y-m-d H:i:s', strtotime($start_date . ' 12:00:00'));
        $end_datetime = date('Y-m-d H:i:s', strtotime($end_date . ' +1 day 06:00:00'));
        $ledger_name = isset($ledger['ledger_name']) ? $ledger['ledger_name'] : 'Unknown';
        $updated_by = isset($ledger['updated_by']) ? $ledger['updated_by'] : (isset($payload['updated_by']) ? $payload['updated_by'] : '');

        $transactions = $this->db
            ->select('c.id, c.amount, c.created_at, c.status, c.type, c.sender_id, c.receiver_id, l1.ledger_name AS sender_name, l2.ledger_name AS receiver_name')
            ->from('coin_transactions c')
            ->join('tbl_ledger l1', 'l1.id = c.sender_id', 'left')
            ->join('tbl_ledger l2', 'l2.id = c.receiver_id', 'left')
            ->group_start()
                ->where('c.receiver_id', (int) $ledger_id)
                ->or_group_start()
                    ->where('c.sender_id', (int) $ledger_id)
                    ->where('c.type', 'spend')
                ->group_end()
            ->group_end()
            ->where('c.created_at >=', $start_datetime)
            ->where('c.created_at <', $end_datetime)
            ->order_by('c.created_at', 'ASC')
            ->get()
            ->result();

        $tx_by_date = array();
        foreach ($transactions as $row) {
            $date_key = date('Y-m-d', strtotime($row->created_at));
            if (!isset($tx_by_date[$date_key])) {
                $tx_by_date[$date_key] = array();
            }
            $tx_by_date[$date_key][] = $row;
        }

        $pl_data = $this->db
            ->select('date, today_hisab AS final_hisab')
            ->from('tbl_final_hisab')
            ->where('ledger_id', (int) $ledger_id)
            ->where("STR_TO_DATE(date, '%d-%m-%Y') >=", date('Y-m-d', strtotime($start_date)))
            ->where("STR_TO_DATE(date, '%d-%m-%Y') <=", date('Y-m-d', strtotime($end_date)))
            ->get()
            ->result();

        $pl_by_date = array();
        foreach ($pl_data as $row) {
            $date_key = date('Y-m-d', strtotime(str_replace('/', '-', $row->date)));
            $pl_by_date[$date_key] = (float) $row->final_hisab;
        }

        $statement = array();
        $all_dates = array_unique(array_merge(array_keys($tx_by_date), array_keys($pl_by_date)));
        sort($all_dates);

        $balance = 0;
        $transaction_total = 0;
        $hisab_total = 0;

        foreach ($all_dates as $date_key) {
            if (!empty($tx_by_date[$date_key])) {
                foreach ($tx_by_date[$date_key] as $row) {
                    $deposit = null;
                    $withdraw = null;
                    $amount = (float) $row->amount;

                    if ((int) $row->receiver_id === (int) $ledger_id) {
                        $balance += $amount;
                        $deposit = $amount;
                        $transaction_total += $amount;
                    } elseif ((int) $row->sender_id === (int) $ledger_id && (int) $row->status === 1) {
                        $balance -= $amount;
                        $withdraw = $amount;
                        $transaction_total += $amount;
                    } else {
                        continue;
                    }

                    $statement[] = array(
                        'id' => 'tx-' . $row->id,
                        'sortKey' => $row->created_at,
                        'dateText' => date('d-m-Y h:i A', strtotime($row->created_at)),
                        'deposit' => $deposit,
                        'withdraw' => $withdraw,
                        'pl' => null,
                        'balance' => $balance,
                        'flow' => trim(($row->sender_name ?: $ledger_name) . ' -> ' . ($row->receiver_name ?: $ledger_name)),
                        'type' => 'transaction'
                    );
                }
            }

            if (array_key_exists($date_key, $pl_by_date)) {
                $pl_value = (float) $pl_by_date[$date_key];
                if ($pl_value < 0) {
                    $balance += abs($pl_value);
                } else {
                    $balance -= $pl_value;
                }

                $hisab_total += abs($pl_value);
                $statement[] = array(
                    'id' => 'pl-' . $date_key,
                    'sortKey' => $date_key . ' 23:59:59',
                    'dateText' => date('d-m-Y', strtotime($date_key)) . ' 11:59 PM (P/L)',
                    'deposit' => null,
                    'withdraw' => null,
                    'pl' => $pl_value,
                    'balance' => $balance,
                    'flow' => 'P/L Adjustment',
                    'type' => 'pl'
                );
            }
        }

        return array(
            'ledger' => $ledger,
            'selected_date' => $end_date,
            'selected_date_iso' => date('Y-m-d', strtotime($end_date)),
            'selected_master' => '',
            'rows' => $statement,
            'statement' => $statement,
            'summary' => array(
                'transaction_total' => $transaction_total,
                'hisab_total' => $hisab_total,
                'net_movement' => $transaction_total - $hisab_total,
                'running_total' => $balance,
                'transaction_count' => count(array_filter($statement, function ($row) {
                    return isset($row['type']) && $row['type'] === 'transaction';
                })),
                'hisab_count' => count(array_filter($statement, function ($row) {
                    return isset($row['type']) && $row['type'] === 'pl';
                })),
                'result_count' => 0
            )
        );
    }

    protected function to_iso_date($value)
    {
        $value = trim((string) $value);
        if (!$value) {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return substr($value, 0, 10);
        }

        $date = DateTime::createFromFormat('d-m-Y', $value);
        if ($date instanceof DateTime) {
            return $date->format('Y-m-d');
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y-m-d', $timestamp) : '';
    }

    protected function find_hisab(array $hisabs, $selected_date_iso)
    {
        foreach ($hisabs as $row) {
            if ($this->to_iso_date(isset($row['date']) ? $row['date'] : '') === $selected_date_iso) {
                return $row;
            }
        }

        return null;
    }
}
