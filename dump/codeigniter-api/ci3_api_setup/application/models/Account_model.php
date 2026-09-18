<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account_model extends CI_Model
{
    private $safe_profile_fields = 'id, ledger_name, username, real_name, owner_name, mobile, address, status, is_locked';

    public function get_profile($ledger_id)
    {
        return $this->db
            ->select($this->safe_profile_fields)
            ->from('tbl_ledger')
            ->where('id', (int) $ledger_id)
            ->get()
            ->row_array();
    }

    public function get_password_row($ledger_id)
    {
        return $this->db
            ->select('id, password, status, is_locked')
            ->from('tbl_ledger')
            ->where('id', (int) $ledger_id)
            ->get()
            ->row_array();
    }

    public function update_profile($ledger_id, array $fields)
    {
        $fields['updated_date'] = date('Y-m-d H:i:s');

        return $this->db
            ->where('id', (int) $ledger_id)
            ->update('tbl_ledger', $fields);
    }

    public function update_password($ledger_id, $new_password)
    {
        return $this->db
            ->where('id', (int) $ledger_id)
            ->update('tbl_ledger', array(
                // TODO: Migrate tbl_ledger passwords to bcrypt/hash later. This requires updating login verification and migrating existing plain text passwords safely.
                'password' => $new_password,
                'updated_date' => date('Y-m-d H:i:s')
            ));
    }

    public function get_master_helpline_for_ledger($ledger_id)
    {
        $ledger = $this->db
            ->select('id, updated_by, status')
            ->from('tbl_ledger')
            ->where('id', (int) $ledger_id)
            ->where('status', 1)
            ->get()
            ->row_array();

        if (!$ledger || empty($ledger['updated_by'])) {
            return array(
                'helpline_number' => '',
                'helpline_type' => 'call_only',
                'can_whatsapp' => false
            );
        }

        $master = $this->db
            ->select('id, helpline_number, helpline_type')
            ->from('tbl_ledger')
            ->where('id', (int) $ledger['updated_by'])
            ->where('is_master', 1)
            ->where('status', 1)
            ->get()
            ->row_array();

        if (!$master) {
            return array(
                'helpline_number' => '',
                'helpline_type' => 'call_only',
                'can_whatsapp' => false
            );
        }

        $helpline_type = isset($master['helpline_type']) && $master['helpline_type'] === 'whatsapp'
            ? 'whatsapp'
            : 'call_only';

        return array(
            'helpline_number' => trim((string) $master['helpline_number']),
            'helpline_type' => $helpline_type,
            'can_whatsapp' => $helpline_type === 'whatsapp'
        );
    }
}
