<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hisab_model extends CI_Model
{
    public function list_for_ledger($ledger_id, $updated_by)
    {
        $rows = $this->db
            ->select('date, today_hisab')
            ->where('ledger_id', (int) $ledger_id)
            ->order_by("STR_TO_DATE(date, '%d-%m-%Y')", 'DESC', false)
            ->get('tbl_final_hisab')
            ->result_array();

        foreach ($rows as &$row) {
            $row['updated_by'] = $updated_by;
        }

        return $rows;
    }
}
