# Backend API Implementation Guide

This folder contains the PHP backend API endpoints that replace the old PHP pages.

## Folder Structure

```
api/
├── auth/
│   ├── login.php
│   ├── logout.php
│   ├── verify.php
│   └── refresh.php
├── ledgers/
│   ├── list.php
│   ├── get.php
│   ├── children.php
│   ├── create.php
│   ├── update.php
│   └── delete.php
├── entries/
│   ├── list.php
│   ├── get.php
│   ├── create.php
│   ├── update.php
│   ├── delete.php
│   └── export.php
├── reports/
│   ├── statement.php
│   ├── summary.php
│   ├── history.php
│   ├── analytics.php
│   └── export.php
├── config/
│   ├── database.php
│   ├── jwt.php
│   └── helpers.php
└── middleware/
    ├── auth.php
    ├── validation.php
    └── cors.php
```

## Key Implementation Principles

1. **No More Sessions**: Use JWT authentication instead
2. **JSON Responses**: All endpoints return JSON
3. **Prepared Statements**: Prevent SQL injection
4. **Input Validation**: Validate all inputs
5. **Error Handling**: Consistent error responses
6. **Database Reuse**: Keep existing tables intact
7. **Backward Compatibility**: Support old data format during transition

## Environment Setup

Create a `.env` file in the api directory:

```
DB_HOST=localhost
DB_USER=555prouser
DB_PASSWORD=e2OFVjrRK77ljyfs4z@R
DB_NAME=555prodb
JWT_SECRET=your-very-secret-key-min-32-characters
JWT_EXPIRY=86400
CORS_ORIGIN=http://localhost:5173
```

## Getting Started

See individual API endpoint files for implementation details.
