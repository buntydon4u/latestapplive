<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'core/Api_Controller.php';

class Account extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Account_model');
    }

    public function profile()
    {
        $payload = $this->require_ledger_auth();
        $ledger_id = (int) $payload['user_id'];

        if ($this->input->method(TRUE) === 'GET') {
            return $this->show_profile($ledger_id);
        }

        if ($this->input->method(TRUE) === 'PUT') {
            return $this->update_profile($ledger_id);
        }

        return $this->error('Method not allowed', 405);
    }

    public function change_password()
    {
        $this->require_method('PUT');
        $payload = $this->require_ledger_auth();
        $ledger_id = (int) $payload['user_id'];

        $errors = $this->validate_required($this->request_data, array(
            'currentPassword' => 'Current password',
            'newPassword' => 'New password',
            'confirmPassword' => 'Confirm password'
        ));

        $current_password = isset($this->request_data['currentPassword']) ? (string) $this->request_data['currentPassword'] : '';
        $new_password = isset($this->request_data['newPassword']) ? (string) $this->request_data['newPassword'] : '';
        $confirm_password = isset($this->request_data['confirmPassword']) ? (string) $this->request_data['confirmPassword'] : '';

        if ($new_password !== '' && strlen($new_password) < 6) {
            $errors['newPassword'] = 'New password must be at least 6 characters';
        }

        if ($new_password !== '' && $confirm_password !== '' && $new_password !== $confirm_password) {
            $errors['confirmPassword'] = 'New password and confirm password must match';
        }

        if ($current_password !== '' && $new_password !== '' && $current_password === $new_password) {
            $errors['newPassword'] = 'New password must be different from current password';
        }

        if ($errors) {
            return $this->validation_error($errors, 400);
        }

        $row = $this->Account_model->get_password_row($ledger_id);
        if (!$row) {
            return $this->error('Profile not found', 404);
        }

        if ((int) $row['status'] !== 1 || (isset($row['is_locked']) && (int) $row['is_locked'] === 1)) {
            return $this->error('Account is inactive or locked', 403);
        }

        if ((string) $row['password'] !== $current_password) {
            return $this->validation_error(array('currentPassword' => 'Current password is incorrect.'), 400);
        }

        if (!$this->Account_model->update_password($ledger_id, $new_password)) {
            return $this->error('Password change failed', 500);
        }

        return $this->success(array(), 'Password changed successfully');
    }

    private function show_profile($ledger_id)
    {
        $profile = $this->Account_model->get_profile($ledger_id);
        if (!$profile) {
            return $this->error('Profile not found', 404);
        }

        if ((int) $profile['status'] !== 1 || (isset($profile['is_locked']) && (int) $profile['is_locked'] === 1)) {
            return $this->error('Account is inactive or locked', 403);
        }

        return $this->success(array('profile' => $profile), 'Profile fetched successfully');
    }

    private function update_profile($ledger_id)
    {
        $current = $this->Account_model->get_profile($ledger_id);
        if (!$current) {
            return $this->error('Profile not found', 404);
        }

        if ((int) $current['status'] !== 1 || (isset($current['is_locked']) && (int) $current['is_locked'] === 1)) {
            return $this->error('Account is inactive or locked', 403);
        }

        $fields = array(
            'real_name' => isset($this->request_data['real_name']) ? trim((string) $this->request_data['real_name']) : '',
            'owner_name' => isset($this->request_data['owner_name']) ? trim((string) $this->request_data['owner_name']) : '',
            'mobile' => isset($this->request_data['mobile']) ? trim((string) $this->request_data['mobile']) : '',
            'address' => isset($this->request_data['address']) ? trim((string) $this->request_data['address']) : ''
        );

        $errors = array();
        if (strlen($fields['real_name']) > 255) {
            $errors['real_name'] = 'Real name must be 255 characters or less';
        }
        if (strlen($fields['owner_name']) > 255) {
            $errors['owner_name'] = 'Owner name must be 255 characters or less';
        }
        if ($fields['mobile'] !== '' && !preg_match('/^[6-9]\d{9}$/', $fields['mobile'])) {
            $errors['mobile'] = 'Mobile must be a valid 10-digit Indian mobile number';
        }
        if (strlen($fields['address']) > 1000) {
            $errors['address'] = 'Address must be 1000 characters or less';
        }

        if ($errors) {
            return $this->validation_error($errors, 400);
        }

        if (!$this->Account_model->update_profile($ledger_id, $fields)) {
            return $this->error('Profile update failed', 500);
        }

        return $this->success(array(
            'profile' => $this->Account_model->get_profile($ledger_id)
        ), 'Profile updated successfully');
    }
}
