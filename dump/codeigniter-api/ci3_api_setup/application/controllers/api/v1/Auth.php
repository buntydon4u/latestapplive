<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/Api_Controller.php';

class Auth extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Auth_model');
    }

    public function login()
    {
        $this->require_method('POST');

        $errors = $this->validate_required($this->request_data, array(
            'username' => 'Username',
            'password' => 'Password'
        ));
        if ($errors) {
            return $this->validation_error($errors);
        }

        $user = $this->Auth_model->login(trim($this->request_data['username']), trim($this->request_data['password']));
        if (!$user) {
            return $this->error('Invalid credentials', 401);
        }

        if ($this->Auth_model->has_children($user['id'])) {
            $token = $this->jwt->encode(array(
                'mode' => 'parent_selection',
                'parent_id' => (int) $user['id'],
                'parent_name' => $user['ledger_name'],
                'updated_by' => $user['updated_by']
            ));

            return $this->success(array(
                'token' => $token,
                'parent_selection_required' => true,
                'parent_id' => (int) $user['id'],
                'name' => $user['ledger_name']
            ), 'Child selection required');
        }

        $token = $this->jwt->encode(array(
            'mode' => 'ledger',
            'user_id' => (int) $user['id'],
            'ledger_name' => $user['ledger_name'],
            'username' => $user['username'],
            'updated_by' => $user['updated_by'],
            'user_type' => 'ledger'
        ));

        return $this->success(array(
            'token' => $token,
            'parent_selection_required' => false,
            'user' => array(
                'id' => (int) $user['id'],
                'name' => $user['ledger_name'],
                'user_type' => 'ledger',
                'updated_by' => $user['updated_by']
            )
        ), 'Login successful');
    }

    public function select_child()
    {
        $this->require_method('POST');
        $payload = $this->require_auth();

        if (empty($payload['parent_id'])) {
            return $this->error('No active parent login found', 401);
        }

        $errors = $this->validate_required($this->request_data, array('child_id' => 'Child ID'));
        if ($errors) {
            return $this->validation_error($errors);
        }

        $child = $this->Auth_model->get_selectable_child($this->request_data['child_id'], $payload['parent_id']);
        if (!$child) {
            return $this->error('Invalid selection', 404);
        }

        $token = $this->jwt->encode(array(
            'mode' => 'ledger',
            'user_id' => (int) $child['id'],
            'ledger_name' => $child['ledger_name'],
            'username' => $child['username'],
            'updated_by' => $child['updated_by'],
            'parent_id' => (int) $payload['parent_id'],
            'user_type' => 'ledger'
        ));

        return $this->success(array(
            'token' => $token,
            'user' => array(
                'id' => (int) $child['id'],
                'name' => $child['ledger_name'],
                'user_type' => 'ledger',
                'updated_by' => $child['updated_by']
            )
        ), 'Child selected successfully');
    }

    public function me()
    {
        $this->require_method('GET');
        $payload = $this->require_auth();

        if (!empty($payload['parent_id']) && empty($payload['user_id'])) {
            return $this->success(array(
                'logged_in' => false,
                'parent_selection_required' => true,
                'parent_id' => (int) $payload['parent_id'],
                'name' => $payload['parent_name']
            ), 'Child selection required');
        }

        $user = $this->Auth_model->get_ledger($payload['user_id']);
        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->success(array(
            'logged_in' => true,
            'user' => array(
                'id' => (int) $user['id'],
                'name' => $user['ledger_name'],
                'user_type' => 'ledger',
                'updated_by' => $user['updated_by']
            )
        ), 'Session fetched successfully');
    }

    public function logout()
    {
        $this->require_method('POST');
        $header = $this->input->get_request_header('Authorization', true);
        $token = get_bearer_token($header);

        if ($token) {
            try {
                $payload = $this->jwt->decode($token);
                $this->Auth_model->blacklist_token($token, $payload['exp']);
            } catch (Exception $e) {
            }
        }

        return $this->success(array(), 'Logout successful');
    }
}
