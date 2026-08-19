<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Copy these lines into application/config/routes.php.
| They keep the API isolated under /api/v1 and do not affect old PHP pages.
*/

$route['api/v1/login']['post'] = 'api/v1/Auth/login';
$route['api/v1/login']['options'] = 'api/v1/Auth/login';
$route['api/v1/logout']['post'] = 'api/v1/Auth/logout';
$route['api/v1/logout']['options'] = 'api/v1/Auth/logout';
$route['api/v1/me']['get'] = 'api/v1/Auth/me';
$route['api/v1/me']['options'] = 'api/v1/Auth/me';
$route['api/v1/auth/select-child']['post'] = 'api/v1/Auth/select_child';
$route['api/v1/auth/select-child']['options'] = 'api/v1/Auth/select_child';

$route['api/v1/ledgers/children']['get'] = 'api/v1/Ledgers/children';
$route['api/v1/ledgers/children']['options'] = 'api/v1/Ledgers/children';
$route['api/v1/ledgers/(:num)']['get'] = 'api/v1/Ledgers/show/$1';
$route['api/v1/ledgers/(:num)']['options'] = 'api/v1/Ledgers/show/$1';
$route['api/v1/parties']['get'] = 'api/v1/Ledgers/parties';
$route['api/v1/parties']['options'] = 'api/v1/Ledgers/parties';
$route['api/v1/shifts']['get'] = 'api/v1/Ledgers/shifts';
$route['api/v1/shifts']['options'] = 'api/v1/Ledgers/shifts';
$route['api/v1/balance']['get'] = 'api/v1/Ledgers/balance';
$route['api/v1/balance']['options'] = 'api/v1/Ledgers/balance';

$route['api/v1/account/profile']['get'] = 'api/v1/Account/profile';
$route['api/v1/account/profile']['post'] = 'api/v1/Account/profile';
$route['api/v1/account/profile']['put'] = 'api/v1/Account/profile';
$route['api/v1/account/profile']['options'] = 'api/v1/Account/profile';
$route['api/v1/account/change-password']['post'] = 'api/v1/Account/change_password';
$route['api/v1/account/change-password']['put'] = 'api/v1/Account/change_password';
$route['api/v1/account/change-password']['options'] = 'api/v1/Account/change_password';

$route['api/v1/transactions']['get'] = 'api/v1/Transactions/index';
$route['api/v1/transactions']['post'] = 'api/v1/Transactions/create';
$route['api/v1/transactions']['options'] = 'api/v1/Transactions/index';
$route['api/v1/transactions/(:num)']['get'] = 'api/v1/Transactions/show/$1';
$route['api/v1/transactions/(:num)']['delete'] = 'api/v1/Transactions/delete/$1';
$route['api/v1/transactions/(:num)']['options'] = 'api/v1/Transactions/show/$1';
$route['api/v1/jantri']['post'] = 'api/v1/Transactions/create_jantri';
$route['api/v1/jantri']['options'] = 'api/v1/Transactions/create_jantri';
$route['api/v1/jantri/party/meta']['get'] = 'api/v1/PartyJantri/meta';
$route['api/v1/jantri/party/meta']['options'] = 'api/v1/PartyJantri/meta';
$route['api/v1/jantri/party/report']['get'] = 'api/v1/PartyJantri/report';
$route['api/v1/jantri/party/report']['options'] = 'api/v1/PartyJantri/report';
$route['api/v1/party-jantri/meta']['get'] = 'api/v1/PartyJantri/meta';
$route['api/v1/party-jantri/meta']['options'] = 'api/v1/PartyJantri/meta';
$route['api/v1/party-jantri/report']['get'] = 'api/v1/PartyJantri/report';
$route['api/v1/party-jantri/report']['options'] = 'api/v1/PartyJantri/report';

$route['api/v1/hisabs']['get'] = 'api/v1/Hisabs/index';
$route['api/v1/hisabs']['options'] = 'api/v1/Hisabs/index';
$route['api/v1/hisabs/report']['get'] = 'api/v1/Hisabs/report';
$route['api/v1/hisabs/report']['options'] = 'api/v1/Hisabs/report';

$route['api/v1/results/latest']['get'] = 'api/v1/Results/latest';
$route['api/v1/results/latest']['options'] = 'api/v1/Results/latest';
