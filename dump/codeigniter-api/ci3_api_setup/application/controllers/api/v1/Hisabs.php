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

        if (!$selected_date) {
            $selected_date = date('d-m-Y');
        }

        $ledger = $this->Ledger_model->get($ledger_id);
        if (!$ledger) {
            return $this->error('Ledger not found', 404);
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
