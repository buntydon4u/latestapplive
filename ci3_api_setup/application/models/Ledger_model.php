<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ledger_model extends CI_Model
{
    public function get($id)
    {
        return $this->db
            ->select('id, ledger_name, username, mobile, parent_id, updated_by, status, agent_id')
            ->where('id', (int) $id)
            ->get('tbl_ledger')
            ->row_array();
    }

    public function get_children($parent_id)
    {
        return $this->db
            ->select('id, ledger_name, parent_id, updated_by, status')
            ->where('parent_id', (int) $parent_id)
            ->order_by('ledger_name', 'ASC')
            ->get('tbl_ledger')
            ->result_array();
    }

    public function get_parties()
    {
        return $this->db
            ->select('id, ledger_name AS name')
            ->where('status', 1)
            ->order_by('ledger_name', 'ASC')
            ->get('tbl_ledger')
            ->result_array();
    }

    public function get_shifts($updated_by)
    {
        $fromdate = date('Y-m-d');
        $todate = date('Y-m-d', time() + (12 * 60 * 60));

        return $this->db
            ->select('user_shift_timings.id AS id, tbl_shift.id AS tbl_shift_id, tbl_shift.shift_name AS name, user_shift_timings.app_time, user_shift_timings.open_date, tbl_shift.super_admin')
            ->from('user_shift_timings')
            ->join('tbl_shift', 'user_shift_timings.shift_id = tbl_shift.id', 'left')
            ->where('user_shift_timings.updated_by', $updated_by)
            ->where('user_shift_timings.open_date >=', $fromdate)
            ->where('user_shift_timings.open_date <=', $todate)
            ->order_by('user_shift_timings.open_date', 'ASC')
            ->order_by('user_shift_timings.master', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_balance($ledger_id)
    {
        $start_datetime = '2025-08-01';
        $end_datetime = date('Y-m-d 06:00:00', strtotime('+1 day'));
        $balance = 0;

        $coin_transactions = $this->db
            ->select('amount, sender_id, receiver_id, status, type')
            ->from('coin_transactions')
            ->group_start()
                ->where('receiver_id', (int) $ledger_id)
                ->or_group_start()
                    ->where('sender_id', (int) $ledger_id)
                    ->where('type', 'spend')
                ->group_end()
            ->group_end()
            ->where('created_at >=', $start_datetime)
            ->where('created_at <', $end_datetime)
            ->get()
            ->result_array();

        foreach ($coin_transactions as $tx) {
            if ((int) $tx['receiver_id'] === (int) $ledger_id) {
                $balance += (float) $tx['amount'];
            } elseif ((int) $tx['sender_id'] === (int) $ledger_id && (int) $tx['status'] === 1) {
                $balance -= (float) $tx['amount'];
            }
        }

        $start_date = date('Y-m-d', strtotime($start_datetime));
        $end_date = date('Y-m-d', strtotime($end_datetime));
        $hisabs = $this->db
            ->select('today_hisab')
            ->where('ledger_id', (int) $ledger_id)
            ->where("STR_TO_DATE(date, '%d-%m-%Y') >=", $start_date)
            ->where("STR_TO_DATE(date, '%d-%m-%Y') <", $end_date)
            ->get('tbl_final_hisab')
            ->result_array();

        foreach ($hisabs as $row) {
            $pl = (float) $row['today_hisab'];
            $balance += $pl < 0 ? abs($pl) : -$pl;
        }

        $deduct = $this->db
            ->select_sum('amount', 'deduct_amount')
            ->where('shift_id IS NOT NULL', null, false)
            ->where('deposite_byto_master', 0)
            ->where('type', 'allocation')
            ->where('status', 1)
            ->where('sender_id', (int) $ledger_id)
            ->where('created_at >=', $start_datetime)
            ->where('created_at <', $end_datetime)
            ->get('coin_transactions')
            ->row_array();

        $balance -= (float) ($deduct['deduct_amount'] ?: 0);

        return $balance;
    }
}
