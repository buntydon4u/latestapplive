<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends CI_Model
{
    public function list_for_ledger($ledger_id, $user_type = 'ledger')
    {
        $rows = $this->db
            ->select('tbl_master_transaction.id, tbl_agent.agent_name, tbl_trans_numbers.created_date AS createddate, tbl_trans_numbers.modified_date AS modifieddate, tbl_master_transaction.t_date, tbl_master_transaction.total_number_amount, tbl_master_transaction.created_date, tbl_master_transaction.party_id, tbl_shift.id AS shiftid, user_shift_timings.app_time, tbl_shift.shift_name, user_shift_timings.open_date, tbl_shift.super_admin, tbl_shift.data_entry_operator, tbl_shift.id AS shift_id, tbl_ledger.ledger_name, tbl_trans_numbers.number AS trnno, tbl_trans_numbers.amount AS trn_amt')
            ->from('tbl_master_transaction')
            ->join('user_shift_timings', 'user_shift_timings.id = tbl_master_transaction.shift_id')
            ->join('tbl_shift', 'tbl_shift.id = user_shift_timings.shift_id')
            ->join('tbl_trans_numbers', 'tbl_trans_numbers.master_id = tbl_master_transaction.id')
            ->join('tbl_ledger', 'tbl_ledger.id = tbl_master_transaction.party_id')
            ->join('tbl_agent', 'tbl_ledger.agent_id = tbl_agent.id', 'left')
            ->where('tbl_master_transaction.t_date >= NOW() - INTERVAL 30 DAY', null, false)
            ->where('tbl_master_transaction.party_id', (int) $ledger_id)
            ->order_by('tbl_master_transaction.id', 'DESC')
            ->get()
            ->result_array();

        $transactions = array();
        $now = time();

        foreach ($rows as $row) {
            $t_date = date('Y-m-d', strtotime($row['t_date']));

            if ($user_type === 'admin') {
                $limit = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date('H:i', strtotime($row['super_admin'])));
            } elseif ((int) $row['shiftid'] === 11) {
                $limit = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date('H:i', strtotime($row['app_time'])));
                $t_date = $row['t_date'];
            } else {
                $limit = strtotime($row['app_time']);
            }

            $start = strtotime($t_date . ' 14:00:00');
            $end = strtotime('+1 day 04:15:00', strtotime($t_date));
            $can_delete = ((int) $row['shiftid'] === 11)
                ? ($now >= $start && $now <= $end)
                : ($now < $limit && date('Y-m-d', $now) === $t_date);

            $amounts = explode(',', (string) $row['trn_amt']);
            $total = array_sum(array_map('floatval', $amounts));

            $transactions[] = array(
                'id' => (int) $row['id'],
                't_date' => $row['t_date'],
                'display_date' => date('d M, Y', strtotime($row['t_date'])),
                'shift_name' => $row['shift_name'],
                'ledger_name' => $row['ledger_name'],
                'total_amount' => $total,
                'can_delete' => $can_delete,
                'shift_id' => (int) $row['shiftid']
            );
        }

        return $transactions;
    }

    public function detail_for_ledger($id, $ledger_id)
    {
        $row = $this->db
            ->select('tbl_master_transaction.t_date, tbl_shift.shift_name, tbl_trans_numbers.number AS trnno, tbl_trans_numbers.amount AS trn_amt')
            ->from('tbl_master_transaction')
            ->join('user_shift_timings', 'user_shift_timings.id = tbl_master_transaction.shift_id')
            ->join('tbl_shift', 'tbl_shift.id = user_shift_timings.shift_id')
            ->join('tbl_trans_numbers', 'tbl_trans_numbers.master_id = tbl_master_transaction.id')
            ->where('tbl_master_transaction.id', (int) $id)
            ->where('tbl_master_transaction.party_id', (int) $ledger_id)
            ->get()
            ->row_array();

        if (!$row) {
            return null;
        }

        $numbers = explode(',', (string) $row['trnno']);
        $amounts = explode(',', (string) $row['trn_amt']);
        $items = array();

        foreach ($numbers as $index => $number) {
            if (trim($number) !== '') {
                $items[] = array(
                    'number' => trim($number),
                    'amount' => isset($amounts[$index]) ? (float) $amounts[$index] : 0
                );
            }
        }

        return array(
            'shift_name' => $row['shift_name'],
            't_date' => $row['t_date'],
            'items' => $items
        );
    }
}
