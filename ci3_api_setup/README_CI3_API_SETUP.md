# CodeIgniter 3 API Setup

This package was generated from the existing Core PHP files in `latestapplive`, using `api.php`, `LoginCode.php`, `LoginCodewithmobile.php`, `Parent.php`, `Entry-page.php`, `View-page.php`, `Date-shift.php`, and `hisablist.php` as the source behavior.

## Folder Structure

```text
application/
  core/
    Api_Controller.php
  controllers/
    api/
      v1/
        Auth.php
        Ledgers.php
        Transactions.php
        Hisabs.php
  models/
    Auth_model.php
    Ledger_model.php
    Transaction_model.php
    Hisab_model.php
  helpers/
    jwt_helper.php
  libraries/
    Jwt.php
  config/
    api.php
    routes_api.php
docs/
  POSTMAN_ENDPOINTS.txt
  SQL_CHANGES_OPTIONAL.sql
```

## Existing Tables Used

- `tbl_ledger`: login, parent/child ledger selection, parties
- `user_shift_timings`: shift availability and app closing time
- `tbl_shift`: shift metadata
- `tbl_master_transaction`: transaction master records
- `tbl_trans_numbers`: transaction numbers and amounts
- `tbl_agent`: optional ledger agent display data
- `coin_transactions`: balance calculation
- `tbl_final_hisab`: balance adjustment and hisab list
- Optional `api_token_blacklist`: server-side JWT logout/revocation

## Converted Core PHP Actions

- `LoginCode.php` and `api.php?action=login` to `POST /api/v1/login`
- `Parent.php` and `api.php?action=select_child` to `POST /api/v1/auth/select-child`
- `api.php?action=get_children` to `GET /api/v1/ledgers/children`
- `api.php?action=get_parties` to `GET /api/v1/parties`
- `api.php?action=get_shifts` to `GET /api/v1/shifts`
- `api.php?action=get_balance` to `GET /api/v1/balance`
- `Date-shift.php` and `api.php?action=get_transactions` to `GET /api/v1/transactions`
- `View-page.php` and `api.php?action=get_transaction_details` to `GET /api/v1/transactions/{id}`
- `api.php?action=submit_transaction` to `POST /api/v1/transactions`
- `api.php?action=submit_jantri` to `POST /api/v1/jantri`
- `api.php?action=delete_transaction` to `DELETE /api/v1/transactions/{id}`
- `hisablist.php` and `api.php?action=get_hisabs` to `GET /api/v1/hisabs`

## Installation

1. Copy the generated `application` subfolders into your CodeIgniter 3 application.
2. Copy the contents of `application/config/routes_api.php` into your real `application/config/routes.php`.
3. Configure your normal CI3 database in `application/config/database.php` with the existing MySQL database.
4. Set a production JWT secret:

```php
$config['jwt_secret'] = 'a-long-random-secret';
```

or set the server environment variable:

```text
JWT_SECRET=a-long-random-secret
JWT_TTL=86400
REMOTE_TRANSACTION_BASE_URL=https://new.555xch.pro
```

5. Optional but recommended: run `docs/SQL_CHANGES_OPTIONAL.sql` for server-side token logout.

## Notes

- Existing frontend PHP pages are not modified.
- Existing database schema is preserved.
- Transaction creation and deletion intentionally proxy to the existing live endpoints because the Core PHP app already delegates these operations to `https://new.555xch.pro/tbl_transactions/...` and `https://new.555xch.pro/tbl_jantri/...`.
- Passwords are compared exactly as the current app stores them in `tbl_ledger.password`. A later hardening step should migrate to `password_hash()` and `password_verify()` after all legacy clients are ready.
- All private routes require `Authorization: Bearer <token>`.
- All responses are JSON only.

## Testing

1. Import or manually create requests from `docs/POSTMAN_ENDPOINTS.txt`.
2. Call `POST /api/v1/login`.
3. If `parent_selection_required` is true, call `GET /api/v1/ledgers/children`, then `POST /api/v1/auth/select-child`.
4. Store the returned ledger token as `{{token}}`.
5. Test protected endpoints with the bearer token.
