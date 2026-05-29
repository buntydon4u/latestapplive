<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Result_model extends CI_Model
{
    public function latest_declared_by_shift($updated_by, $limit = 10)
    {
        $updated_by = (int) $updated_by;
        $limit = max(1, min((int) $limit, 50));

        return $this->db
            ->select('tbl_openno.*, tbl_shift.shift_name, tbl_shift.id AS master_shift_id, user_shift_timings.open_date')
            ->from('tbl_openno')
            ->join('user_shift_timings', 'user_shift_timings.id = tbl_openno.shift_id')
            ->join('tbl_shift', 'tbl_shift.id = user_shift_timings.shift_id')
            ->where('tbl_openno.updated_by', $updated_by)
            ->order_by('tbl_openno.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();
    }
}
