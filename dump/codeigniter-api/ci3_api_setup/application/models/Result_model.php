<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Result_model extends CI_Model
{
    public function latest_declared_by_shift($updated_by, $limit = 10)
    {
        $updated_by = (int) $updated_by;
        $limit = max(1, min((int) $limit, 50));

        $rows = $this->db
            ->select('tbl_openno.*, tbl_shift.shift_name, tbl_shift.id AS master_shift_id, user_shift_timings.open_date AS shift_date, user_shift_timings.app_time AS open_time, tbl_shift.app_result_time AS app_result_time, tbl_shift.app_result_time AS result_time')
            ->from('tbl_openno')
            ->join('user_shift_timings', 'user_shift_timings.id = tbl_openno.shift_id')
            ->join('tbl_shift', 'tbl_shift.id = user_shift_timings.shift_id')
            ->where('tbl_openno.updated_by', $updated_by)
            ->order_by('tbl_openno.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result_array();

        // Add declared_date: prefer tbl_openno's own t_date/date field over shift_date
        foreach ($rows as &$row) {
            $row['declared_date'] = isset($row['t_date']) && $row['t_date']
                ? $row['t_date']
                : (isset($row['date']) && $row['date'] ? $row['date'] : ($row['shift_date'] ?? ''));
        }
        unset($row);

        return $rows;
    }

    public function ledger_till_date_result_today_api($params)
{
    $ledger_id = $params['ledger_id'];
    $formatted_date = date('Y-m-d', strtotime($params['date']));
    $first_day_this_month = date('Y-m-01');

    $sql_today = "
        SELECT
            tl.ledger_name,
            tl.hissa AS pattiperc,
            tl.openingbalance AS openingbalance,
            tl.hissa_select AS hissa_select,
            tl.dara_rate AS ledgerdara_rate,
            tl.dara_commision AS ledgerdara_commision,
            tl.akhar_rate AS ledgerakhar_rate,
            tl.akhar_commission AS ledgerakhar_commission,
            topen.number AS number,
            topen.date AS opendate,
            ts.shift_name,
            ust.shift_id,
            tdsp.*,
            tdsp.Open_Number_amount AS oamnt,
            tdsp.Akhar_Number_amount AS akmnt,
            tdsp.Total_Number_amount AS tamnt
        FROM Tb_Date_shift_party_entry AS tdsp
        JOIN user_shift_timings AS ust
            ON ust.id = tdsp.shiftid
        JOIN tbl_shift AS ts
            ON ts.id = ust.shift_id
        LEFT JOIN tbl_openno AS topen
            ON topen.shift_id = ust.id
            AND STR_TO_DATE(topen.date, '%d-%m-%Y') = tdsp.Date
        JOIN tbl_ledger AS tl
            ON tl.id = tdsp.PartyId
        WHERE tdsp.PartyId = ?
            AND tdsp.Date = ?
            AND tdsp.status = 1
            AND topen.updated_by != 1
            AND STR_TO_DATE(topen.date, '%d-%m-%Y') IS NOT NULL
        GROUP BY ts.id, tl.id, ust.shift_id, topen.number, tdsp.Date
    ";

    $today_query = $this->db->query(
        $sql_today,
        array($ledger_id, $formatted_date)
    );

    $data['today'] = $today_query->result_array();

    $sql_before = "
        SELECT
            tl.ledger_name,
            tl.hissa AS pattiperc,
            tl.openingbalance AS openingbalance,
            tl.hissa_select AS hissa_select,
            tl.dara_rate AS ledgerdara_rate,
            tl.dara_commision AS ledgerdara_commision,
            tl.akhar_rate AS ledgerakhar_rate,
            tl.akhar_commission AS ledgerakhar_commission,
            topen.number,
            topen.date AS opendate,
            ts.shift_name,
            tdsp.*,
            tdsp.Open_Number_amount AS oamnt,
            tdsp.Akhar_Number_amount AS akmnt,
            tdsp.Total_Number_amount AS tamnt
        FROM Tb_Date_shift_party_entry AS tdsp
        JOIN user_shift_timings AS ust
            ON ust.id = tdsp.shiftid
        JOIN tbl_shift AS ts
            ON ts.id = ust.shift_id
        JOIN tbl_openno AS topen
            ON STR_TO_DATE(topen.date, '%d-%m-%Y') = tdsp.Date
        JOIN tbl_ledger AS tl
            ON tl.id = tdsp.PartyId
        WHERE tdsp.status = 1
            AND tdsp.PartyId = ?
    ";

    if (strtotime($first_day_this_month) == strtotime($formatted_date)) {
        $data['before'] = array();
    } else {
        $sql_before .= "
            AND tdsp.Date < ?
            AND tdsp.Date >= ?
            GROUP BY tdsp.id
        ";

        $before_query = $this->db->query(
            $sql_before,
            array(
                $ledger_id,
                $formatted_date,
                $first_day_this_month
            )
        );

        $data['before'] = $before_query->result_array();
    }

    return $data;
}
}
