<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_Controller extends CI_Controller
{
    protected $auth_user = null;
    protected $request_data = array();

    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set('Asia/Kolkata');
        $this->output->set_content_type('application/json', 'utf-8');

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        if ($this->input->method(TRUE) === 'OPTIONS') {
            $this->output->set_status_header(204)->set_output('');
            $this->output->_display();
            exit;
        }

        $this->load->library('Jwt');
        $this->load->helper('jwt');
        $this->load->database();
        $this->request_data = $this->parse_input();
    }

    protected function parse_input()
    {
        $raw = $this->input->raw_input_stream;
        $json = json_decode($raw, true);
        if (is_array($json)) {
            return $json;
        }

        if ($this->input->method(TRUE) === 'GET') {
            return $this->input->get(NULL, true) ?: array();
        }

        $post = $this->input->post(NULL, true);
        if (!empty($post)) {
            return $post;
        }

        parse_str($raw, $parsed);
        return is_array($parsed) ? $parsed : array();
    }

    protected function success($data = array(), $message = 'Data fetched successfully', $status_code = 200)
    {
        return $this->output
            ->set_status_header($status_code)
            ->set_output(json_encode(array(
                'status' => true,
                'message' => $message,
                'data' => $data
            )));
    }

    protected function error($message = 'Something went wrong', $status_code = 400, $extra = array())
    {
        $payload = array_merge(array(
            'status' => false,
            'message' => $message
        ), $extra);

        return $this->output
            ->set_status_header($status_code)
            ->set_output(json_encode($payload));
    }

    protected function validation_error($errors, $status_code = 422)
    {
        return $this->output
            ->set_status_header($status_code)
            ->set_output(json_encode(array(
                'status' => false,
                'errors' => $errors
            )));
    }

    protected function require_method($method)
    {
        if ($this->input->method(TRUE) !== strtoupper($method)) {
            $this->error('Method not allowed', 405);
            $this->output->_display();
            exit;
        }
    }

    protected function require_auth()
    {
        $header = $this->input->get_request_header('Authorization', true);
        $token = get_bearer_token($header);

        if (!$token) {
            $this->error('Authorization token missing', 401);
            $this->output->_display();
            exit;
        }

        try {
            $payload = $this->jwt->decode($token);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 401);
            $this->output->_display();
            exit;
        }

        $this->load->model('Auth_model');
        if ($this->Auth_model->is_token_blacklisted($token)) {
            $this->error('Token has been logged out', 401);
            $this->output->_display();
            exit;
        }

        $this->auth_user = $payload;
        return $payload;
    }

    protected function require_ledger_auth()
    {
        $payload = $this->require_auth();
        if (empty($payload['user_id'])) {
            $this->error('Ledger selection required', 403);
            $this->output->_display();
            exit;
        }

        return $payload;
    }

    protected function validate_required($data, $rules)
    {
        $errors = array();
        foreach ($rules as $field => $label) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $errors[$field] = $label . ' required';
            }
        }
        return $errors;
    }
}
