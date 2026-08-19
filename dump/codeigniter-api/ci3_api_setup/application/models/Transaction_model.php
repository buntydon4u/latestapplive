<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends CI_Model
{
    public function exists_for_ledger($id, $ledger_id)
    {
        return $this->db
            ->from('tbl_master_transaction')
            ->where('id', (int) $id)
            ->where('party_id', (int) $ledger_id)
            ->count_all_results() > 0;
    }

    public function delete_for_ledger($id, $ledger_id, $user_type = 'ledger')
    {
        $id = (int) $id;
        $ledger_id = (int) $ledger_id;

        $this->db->trans_begin();

        $row = $this->db->query(
            'SELECT m.id, m.party_id, m.shift_id, m.t_date, '
            . 'm.total_number_amount, m.coin_id, ust.app_time, ust.open_date, '
            . 's.id AS game_shift_id, s.super_admin '
            . 'FROM tbl_master_transaction m '
            . 'JOIN user_shift_timings ust ON ust.id = m.shift_id '
            . 'JOIN tbl_shift s ON s.id = ust.shift_id '
            . 'WHERE m.id = ? AND m.party_id = ? FOR UPDATE',
            array($id, $ledger_id)
        )->row_array();

        if (!$row) {
            $this->db->trans_rollback();
            return array('success' => false, 'code' => 'not_found');
        }

        if (!$this->can_delete_row($row, $user_type)) {
            $this->db->trans_rollback();
            return array('success' => false, 'code' => 'time_expired');
        }

        $amount = (float) $row['total_number_amount'];
        $this->update_date_shift_summary_after_delete($row, $amount);

        $this->db->where('master_id', $id)->delete('tbl_trans_numbers');
        $this->db->where('id', $id)->where('party_id', $ledger_id)->delete('tbl_master_transaction');

        // Creation stores the allocation transaction on the master row. Removing
        // it restores balances calculated from coin_transactions.
        if (!empty($row['coin_id']) && $this->db->table_exists('coin_transactions')) {
            $this->db->where('id', (int) $row['coin_id'])->delete('coin_transactions');
        }

        // Keep the legacy cached balance in sync on installations that still use it.
        if ($amount > 0 && $this->db->field_exists('coin_balance', 'tbl_ledger')) {
            $this->db
                ->set('coin_balance', 'COALESCE(coin_balance, 0) + ' . $this->db->escape($amount), false)
                ->where('id', $ledger_id)
                ->update('tbl_ledger');
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return array('success' => false, 'code' => 'database_error');
        }

        $this->db->trans_commit();
        return array('success' => true);
    }

    protected function can_delete_row(array $row, $user_type)
    {
        $now = time();
        $transaction_date = date('Y-m-d', strtotime($row['t_date']));

        if ($user_type === 'admin') {
            $limit = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date('H:i', strtotime($row['super_admin'])));
        } elseif ((int) $row['game_shift_id'] === 11) {
            $start = strtotime($transaction_date . ' 14:00:00');
            $end = strtotime('+1 day 04:15:00', strtotime($transaction_date));
            return $now >= $start && $now <= $end;
        } else {
            $limit = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date('H:i', strtotime($row['app_time'])));
        }

        return $now < $limit && date('Y-m-d', $now) === $transaction_date;
    }

    protected function update_date_shift_summary_after_delete(array $row, $amount)
    {
        $table = null;
        foreach (array('Tb_Date_shift_party_entry', 'tb_date_shift_party_entry') as $candidate) {
            if ($this->db->table_exists($candidate)) {
                $table = $candidate;
                break;
            }
        }

        if (!$table) {
            return;
        }

        $date = date('d-m-Y', strtotime($row['t_date']));
        $summary = $this->db
            ->where('ShiftId', (int) $row['shift_id'])
            ->where('PartyId', (int) $row['party_id'])
            ->where('Date', $date)
            ->get($table)
            ->row_array();

        if (!$summary) {
            return;
        }

        $new_number_total = max(0, (float) $summary['Total_Number_amount'] - $amount);
        $akhar_total = isset($summary['Total_Akhar_amount']) ? (float) $summary['Total_Akhar_amount'] : 0;
        $new_total = max(0, (float) $summary['Total_amount'] - $amount);

        if ($new_number_total == 0.0 && $akhar_total == 0.0 && $new_total == 0.0) {
            $this->db->where('id', (int) $summary['id'])->delete($table);
            return;
        }

        $this->db->where('id', (int) $summary['id'])->update($table, array(
            'Total_Number_amount' => $new_number_total,
            'Total_amount' => $new_total
        ));
    }

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
