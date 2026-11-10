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

    // The desktop ledger_till_date_reports page uses transaction totals
    // (finalsum), then applies the ledger rates to those totals.
    $total_today = 0;
    $total_d = 0;
    $total_a = 0;
    foreach ($data['today'] as &$row) {
        $row['finalsum'] = $this->transaction_total(
            $ledger_id,
            $formatted_date,
            $row['shift_id']
        );
        // Some installations do not expose transaction rows to the API
        // account, while the desktop report still has the daily total on the
        // date-shift summary row.
        if ($row['finalsum'] == 0 && (float) ($row['Total_Number_amount'] ?? 0) != 0) {
            $row['finalsum'] = (float) $row['Total_Number_amount'];
        }
        $row['tamnt'] = $row['finalsum'];

        if ((int) ($row['status'] ?? 0) !== 0) {
            $total_today += (float) $row['finalsum'];
        }
        $total_d += (float) ($row['oamnt'] ?? 0);
        $total_a += (float) ($row['akmnt'] ?? 0);
    }
    unset($row);

    $meta = !empty($data['today']) ? $data['today'][0] : array();
    $commission = (int) ceil($total_today * ((float) ($meta['ledgerdara_commision'] ?? 0) / 100));
    $d_amount = (int) ceil($total_d * (float) ($meta['ledgerdara_rate'] ?? 0));
    $a_amount = (int) ceil($total_a * (float) ($meta['ledgerakhar_rate'] ?? 0));
    $patti_amount = ($meta['hissa_select'] ?? '') === 'y'
        ? (($total_today - ($commission + $a_amount + $d_amount)) * (float) ($meta['pattiperc'] ?? 0) / 100)
        : 0;

    $final_row = $this->final_hisab_row($ledger_id, $formatted_date);
    $opening = $final_row !== null
        ? (float) $final_row['opening_bal']
        : $this->ledger_opening_balance($ledger_id);

    $payment = 0;
    foreach ($this->vouchers_for_date($ledger_id, $formatted_date) as $voucher) {
        if ((string) $ledger_id === (string) ($voucher['PartyId'] ?? '')) {
            $payment -= (float) ($voucher['Amount'] ?? 0);
        }
        if ((string) $ledger_id === (string) ($voucher['Collect_By'] ?? '')) {
            $payment += (float) ($voucher['Amount'] ?? 0);
        }
    }

    $kisht = 0;
    $kist = $this->kist_for_date($ledger_id, $formatted_date);
    if ($kist !== null && !empty($data['today'])) {
        $from = new DateTime(date('Y-m-d', strtotime($kist['frdate'])));
        $to = new DateTime(date('Y-m-d', strtotime($data['today'][0]['Date'])));
        if ($from->diff($to)->days) {
            $kisht = (float) $kist['kist'];
        }
    }

    $data['summary'] = array(
        'total_today' => $total_today,
        'total_d' => $total_d,
        'total_a' => $total_a,
        'commission' => $commission,
        'commission_rate' => (float) ($meta['ledgerdara_commision'] ?? 0),
        'd_amount' => $d_amount,
        'a_amount' => $a_amount,
        'total' => $commission + $d_amount + $a_amount,
        'patti_amount' => $patti_amount,
        'patti_rate' => (float) ($meta['pattiperc'] ?? 0),
        'opening' => $opening,
        'payment' => $payment,
        'today_hisab' => (float) ($final_row['today_hisab'] ?? 0),
        'closing' => (float) ($final_row['final_hisab'] ?? 0),
        'final_total' => (float) ($final_row['today_hisab'] ?? 0),
        'kisht' => $kisht,
    );

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

private function transaction_total($ledger_id, $date, $shift_id)
{
    $rows = $this->db
        ->select('tn.amount')
        ->from('tbl_master_transaction AS mt')
        ->join('tbl_trans_numbers AS tn', 'tn.master_id = mt.id', 'left')
        ->join('user_shift_timings AS ust', 'ust.id = mt.shift_id', 'left')
        ->where('mt.party_id', $ledger_id)
        ->where('mt.created_by', $ledger_id)
        ->where('ust.shift_id', $shift_id)
        ->like('mt.t_date', $date, 'after')
        ->get()
        ->result_array();

    $total = 0;
    foreach ($rows as $row) {
        foreach (explode(',', (string) ($row['amount'] ?? '')) as $amount) {
            $total += (float) trim($amount);
        }
    }
    return $total;
}

private function final_hisab_row($ledger_id, $date)
{
    return $this->db
        ->where('ledger_id', $ledger_id)
        ->where('date', date('d-m-Y', strtotime($date)))
        ->order_by('id', 'DESC')
        ->limit(1)
        ->get('tbl_final_hisab')
        ->row_array() ?: null;
}

private function ledger_opening_balance($ledger_id)
{
    $row = $this->db
        ->select('openingbalance')
        ->where('id', $ledger_id)
        ->get('tbl_ledger')
        ->row_array();
    return (float) ($row['openingbalance'] ?? 0);
}

private function vouchers_for_date($ledger_id, $date)
{
    return $this->db
        ->where('Date', date('Y-m-d', strtotime($date)))
        ->group_start()
        ->where('PartyId', $ledger_id)
        ->or_where('Collect_By', $ledger_id)
        ->group_end()
        ->get('tbl_voucher')
        ->result_array();
}

private function kist_for_date($ledger_id, $date)
{
    return $this->db
        ->where('PartyId', $ledger_id)
        ->where('frdate <', date('Y-m-d', strtotime($date)))
        ->where('todate >=', date('Y-m-d', strtotime($date)))
        ->order_by('id', 'DESC')
        ->limit(1)
        ->get('tbl_kist')
        ->row_array() ?: null;
}
}
