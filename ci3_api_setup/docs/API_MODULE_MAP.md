# API Module Map

## Auth Module

Routes:
- `POST api/v1/login` -> `api/v1/Auth/login`
- `POST api/v1/logout` -> `api/v1/Auth/logout`
- `GET api/v1/me` -> `api/v1/Auth/me`
- `POST api/v1/auth/select-child` -> `api/v1/Auth/select_child`

Files:
- Controller: `application/controllers/api/v1/Auth.php`
- Model: `application/models/Auth_model.php`
- Auth library/helper: `application/libraries/Jwt.php`, `application/helpers/jwt_helper.php`

Existing PHP source:
- `LoginCode.php`
- `LoginCodewithmobile.php`
- `Parent.php`
- `api.php?action=login`
- `api.php?action=select_child`

Tables:
- `tbl_ledger`
- Optional `api_token_blacklist`

Validation:
- Login requires `username`, `password`
- Select child requires `child_id`

SQL adjustment:
- No existing table change required.
- Optional `api_token_blacklist` enables server-side logout.

Postman:
- Entries 1 to 4 in `POSTMAN_ENDPOINTS.txt`

## Ledger Module

Routes:
- `GET api/v1/ledgers/{id}` -> `api/v1/Ledgers/show/{id}`
- `GET api/v1/ledgers/children` -> `api/v1/Ledgers/children`
- `GET api/v1/parties` -> `api/v1/Ledgers/parties`
- `GET api/v1/shifts` -> `api/v1/Ledgers/shifts`
- `GET api/v1/balance` -> `api/v1/Ledgers/balance`

Files:
- Controller: `application/controllers/api/v1/Ledgers.php`
- Model: `application/models/Ledger_model.php`

Existing PHP source:
- `Parent.php`
- `Entry-page.php`
- `Date-shift.php`
- `api.php?action=get_children`
- `api.php?action=get_parties`
- `api.php?action=get_shifts`
- `api.php?action=get_balance`

Tables:
- `tbl_ledger`
- `user_shift_timings`
- `tbl_shift`
- `coin_transactions`
- `tbl_final_hisab`

Validation:
- All routes require `Authorization: Bearer <token>`
- `children` accepts optional `parent_id`; defaults to token parent/user id

SQL adjustment:
- None required.

Postman:
- Entries 5 to 9 in `POSTMAN_ENDPOINTS.txt`

## Transaction Module

Routes:
- `GET api/v1/transactions` -> `api/v1/Transactions/index`
- `GET api/v1/transactions/{id}` -> `api/v1/Transactions/show/{id}`
- `POST api/v1/transactions` -> `api/v1/Transactions/create`
- `DELETE api/v1/transactions/{id}` -> `api/v1/Transactions/delete/{id}`
- `POST api/v1/jantri` -> `api/v1/Transactions/create_jantri`

Files:
- Controller: `application/controllers/api/v1/Transactions.php`
- Model: `application/models/Transaction_model.php`

Existing PHP source:
- `Entry-page.php`
- `entry-jantri.php`
- `View-page.php`
- `Date-shift.php`
- `Typingback.php`
- `api.php?action=get_transactions`
- `api.php?action=get_transaction_details`
- `api.php?action=submit_transaction`
- `api.php?action=submit_jantri`
- `api.php?action=delete_transaction`

Tables:
- `tbl_master_transaction`
- `tbl_trans_numbers`
- `user_shift_timings`
- `tbl_shift`
- `tbl_ledger`
- `tbl_agent`

Validation:
- Create transaction requires `party`, `dateoftrnforapponly`, `dateoftrn`, `shift`, `trn_number`, `trn_amount`
- Create jantri requires `party`, `dateoftrnforapponly`, `dateoftrn`, `shift`, `gtotal`
- Delete requires URL `{id}`

SQL adjustment:
- None required.
- Create/delete reuse existing live business endpoints configured by `REMOTE_TRANSACTION_BASE_URL` to preserve current server-side rules.

Postman:
- Entries 10 to 14 in `POSTMAN_ENDPOINTS.txt`

## Hisab Module

Routes:
- `GET api/v1/hisabs` -> `api/v1/Hisabs/index`

Files:
- Controller: `application/controllers/api/v1/Hisabs.php`
- Model: `application/models/Hisab_model.php`

Existing PHP source:
- `hisablist.php`
- `api.php?action=get_hisabs`

Tables:
- `tbl_final_hisab`

Validation:
- Requires `Authorization: Bearer <token>`

SQL adjustment:
- None required.

Postman:
- Entry 15 in `POSTMAN_ENDPOINTS.txt`
