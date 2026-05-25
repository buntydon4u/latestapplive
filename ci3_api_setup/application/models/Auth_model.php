<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    public function login($username, $password)
    {
        return $this->db
            ->select('id, ledger_name, username, updated_by, parent_id')
            ->where('username', $username)
            ->where('password', $password)
            ->where('is_master', 0)
            ->where('status', 1)
            ->get('tbl_ledger')
            ->row_array();
    }

    public function get_ledger($id)
    {
        return $this->db
            ->select('id, ledger_name, username, updated_by, parent_id, status')
            ->where('id', (int) $id)
            ->get('tbl_ledger')
            ->row_array();
    }

    public function has_children($ledger_id)
    {
        return $this->db
            ->where('parent_id', (int) $ledger_id)
            ->count_all_results('tbl_ledger') > 0;
    }

    public function get_selectable_child($child_id, $parent_id)
    {
        return $this->db
            ->select('id, ledger_name, username, updated_by, parent_id, status')
            ->where('id', (int) $child_id)
            ->group_start()
                ->where('parent_id', (int) $parent_id)
                ->or_where('id', (int) $parent_id)
            ->group_end()
            ->where('status', 1)
            ->get('tbl_ledger')
            ->row_array();
    }

    public function blacklist_token($token, $expires_at)
    {
        if (!$this->db->table_exists('api_token_blacklist')) {
            return false;
        }

        return $this->db->insert('api_token_blacklist', array(
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', (int) $expires_at),
            'created_at' => date('Y-m-d H:i:s')
        ));
    }

    public function is_token_blacklisted($token)
    {
        if (!$this->db->table_exists('api_token_blacklist')) {
            return false;
        }

        return $this->db
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->count_all_results('api_token_blacklist') > 0;
    }
}
