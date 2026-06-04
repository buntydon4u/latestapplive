<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Party_jantri_model extends CI_Model
{
    public function get_available_shifts_for_updated_by($updated_by)
    {
        $updated_by = (int) $updated_by;

        $rows = $this->db
            ->select('
                tbl_shift.id AS tbl_shift_id,
                tbl_shift.shift_name,
                tbl_shift.open_date AS tbl_shift_open_date,
                tbl_shift.updated_by AS adminshift,
                user_shift_timings.id AS user_shift_timing_id,
                user_shift_timings.id AS id,
                user_shift_timings.shift_id,
                user_shift_timings.open_date AS user_shift_open_date,
                user_shift_timings.open_date,
                user_shift_timings.app_time,
                user_shift_timings.master,
                user_shift_timings.updated_by AS timing_updated_by
            ')
            ->from('tbl_shift')
            ->join(
                'user_shift_timings',
                'tbl_shift.id = user_shift_timings.shift_id AND user_shift_timings.updated_by IN (1, ' . $this->db->escape($updated_by) . ')',
                'left'
            )
            ->group_start()
                ->where('tbl_shift.updated_by', 1)
                ->or_where_in('user_shift_timings.updated_by', array(1, $updated_by))
            ->group_end()
            ->order_by('tbl_shift.id', 'ASC')
            ->order_by('user_shift_timings.updated_by', 'DESC')
            ->get()
            ->result_array();

        $deduped = array();
        foreach ($rows as $row) {
            $key = isset($row['tbl_shift_id']) ? (int) $row['tbl_shift_id'] : 0;
            if (!$key || isset($deduped[$key])) {
                continue;
            }
            $deduped[$key] = $row;
        }

        return array_values($deduped);
    }

    public function get_active_parties_for_admin($admin_id)
    {
        return $this->db
            ->select('tbl_ledger.id, tbl_ledger.ledger_name, tbl_ledger.username, tbl_ledger.updated_by, tbl_ledger.status')
            ->from('tbl_ledger')
            ->where('tbl_ledger.updated_by', (int) $admin_id)
            ->where('tbl_ledger.status', 1)
            ->order_by('tbl_ledger.id', 'DESC')
            ->get()
            ->result_array();
    }

    public function get_party_jantri_report($pid, $date, $partyid)
    {
        return $this->db
            ->select('
                tbl_master_transaction.id,
                tbl_master_transaction.t_date,
                tbl_master_transaction.total_number_amount AS ttamnt,
                tbl_shift.id AS shiftid,
                tbl_shift.shift_name,
                tbl_ledger.id AS ledgerid,
                tbl_ledger.ledger_name,
                tbl_ledger.dara_commision AS dcomm,
                tbl_ledger.hissa AS dhissa,
                tbl_ledger.tppercentage AS tppercentage,
                tbl_trans_numbers.number AS trnno,
                tbl_trans_numbers.amount AS trn_amt
            ')
            ->from('tbl_master_transaction')
            ->join('tbl_trans_numbers', 'tbl_trans_numbers.master_id = tbl_master_transaction.id', 'left')
            ->join('tbl_ledger', 'tbl_ledger.id = tbl_master_transaction.party_id', 'left')
            ->join('user_shift_timings', 'user_shift_timings.id = tbl_master_transaction.shift_id', 'left')
            ->join('tbl_shift', 'tbl_shift.id = user_shift_timings.shift_id', 'left')
            ->where('tbl_master_transaction.party_id', (int) $partyid)
            ->where('tbl_master_transaction.shift_id', (int) $pid)
            ->where('tbl_master_transaction.t_date', $date . ' 00:00:00')
            ->order_by('tbl_master_transaction.id', 'DESC')
            ->get()
            ->result_array();
    }

    public function resolve_report_shift_id($pid, $date, $updated_by)
    {
        $pid = (int) $pid;
        $updated_by = (int) $updated_by;

        $direct = $this->db
            ->select('id')
            ->from('user_shift_timings')
            ->where('id', $pid)
            ->get()
            ->row_array();

        if ($direct) {
            return $pid;
        }

        $timing = $this->db
            ->select('id')
            ->from('user_shift_timings')
            ->where('shift_id', $pid)
            ->where('updated_by', $updated_by)
            ->where('open_date', $date)
            ->order_by('id', 'DESC')
            ->get()
            ->row_array();

        return $timing ? (int) $timing['id'] : $pid;
    }
}
