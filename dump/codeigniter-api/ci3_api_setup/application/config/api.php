<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['jwt_secret'] = getenv('JWT_SECRET') ?: 'CHANGE_THIS_TO_A_LONG_RANDOM_PRODUCTION_SECRET';
$config['jwt_ttl'] = (int) (getenv('JWT_TTL') ?: 86400);
$config['remote_transaction_base_url'] = getenv('REMOTE_TRANSACTION_BASE_URL') ?: 'https://new.bull99exch.com';
$config['remote_transaction_create_path'] = '/tbl_transactions/add_transaction_final_app_api';
