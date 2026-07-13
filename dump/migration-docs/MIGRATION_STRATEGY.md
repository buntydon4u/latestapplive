# React Migration Strategy - XCH555 Project

## PHASE 1: PROJECT ANALYSIS SUMMARY

### Current Architecture
- **Frontend**: PHP mixed with HTML, Bootstrap-based UI
- **Backend**: PHP pages rendering HTML + handling business logic
- **Database**: MySQL (`555prodb`)
- **Authentication**: PHP Sessions with hardcoded credentials
- **Tables**: `tbl_ledger`, `tbl_user_login` (and potentially others)

### Key Pages & Their Functions

| PHP File | Purpose | Type | Data Flow |
|----------|---------|------|-----------|
| Login.html | Login UI | Frontend | Static HTML |
| LoginCode.php | Authentication | Backend | POST → DB query → Session |
| Parent.php | Parent selection | Frontend+Backend | GET params → DB query → Form |
| Entry-page.php | Data entry form | Frontend+Backend | GET params → Form rendering |
| View-page.php | Results display | Frontend+Backend | GET params → DB query → Table |
| statement.php | Reports | Backend+Frontend | DB aggregation |
| hisab.php | Transaction logic | Backend | Processing |

### Authentication Flow
```
Login.html (form)
    ↓
LoginCode.php (verify credentials)
    ↓
Set $_SESSION['user_id'], $_SESSION['updated_by']
    ↓
Redirect to Parent.php or Entry-page.php
```

### Database Tables Identified
- `tbl_ledger` - Main user/account data
  - Columns: id, username, password, ledger_name, parent_id, updated_by, is_master, status
- `tbl_user_login` - Alternative login table (optional)
- Others: Transaction tables (to be analyzed)

### Key Functionality to Preserve
1. Parent-child ledger hierarchy
2. Session management and user identification
3. Data entry forms
4. Transaction viewing and reporting
5. Date-based filtering
6. Multi-user support

---

## PHASE 2: REACT PROJECT SETUP

### Technology Stack
- **Framework**: React 18+ with Vite
- **Routing**: React Router v6
- **HTTP Client**: Axios
- **Styling**: TailwindCSS
- **State Management**: React Context API (with Redux option for scaling)
- **Authentication**: JWT (replacing PHP sessions)
- **Form Handling**: React Hook Form + Zod validation
- **UI Components**: Custom + Headless UI

### Folder Structure

```
react-xch555/
├── src/
│   ├── components/
│   │   ├── common/
│   │   │   ├── Header.jsx
│   │   │   ├── Footer.jsx
│   │   │   ├── Sidebar.jsx
│   │   │   ├── Card.jsx
│   │   │   ├── Button.jsx
│   │   │   ├── Input.jsx
│   │   │   ├── Table.jsx
│   │   │   ├── Modal.jsx
│   │   │   └── Loader.jsx
│   │   ├── forms/
│   │   │   ├── LoginForm.jsx
│   │   │   ├── DataEntryForm.jsx
│   │   │   ├── FilterForm.jsx
│   │   │   └── SearchForm.jsx
│   │   ├── layouts/
│   │   │   ├── AuthLayout.jsx
│   │   │   ├── DashboardLayout.jsx
│   │   │   └── MainLayout.jsx
│   │   └── features/
│   │       ├── Auth/
│   │       ├── Dashboard/
│   │       ├── Entry/
│   │       ├── Ledger/
│   │       └── Reports/
│   ├── pages/
│   │   ├── Login.jsx
│   │   ├── ParentSelection.jsx
│   │   ├── DataEntry.jsx
│   │   ├── ViewResults.jsx
│   │   ├── Reports.jsx
│   │   ├── Dashboard.jsx
│   │   ├── NotFound.jsx
│   │   └── Profile.jsx
│   ├── services/
│   │   ├── api/
│   │   │   ├── client.js
│   │   │   ├── auth.js
│   │   │   ├── ledger.js
│   │   │   ├── entry.js
│   │   │   └── reports.js
│   │   └── storage/
│   │       ├── localStorage.js
│   │       └── sessionStorage.js
│   ├── hooks/
│   │   ├── useAuth.js
│   │   ├── useFetch.js
│   │   ├── useForm.js
│   │   └── useLocalStorage.js
│   ├── context/
│   │   ├── AuthContext.jsx
│   │   ├── UserContext.jsx
│   │   └── ThemeContext.jsx
│   ├── routes/
│   │   ├── ProtectedRoute.jsx
│   │   ├── index.jsx
│   │   └── routeConfig.js
│   ├── utils/
│   │   ├── helpers.js
│   │   ├── validators.js
│   │   ├── constants.js
│   │   ├── formatters.js
│   │   └── dateUtils.js
│   ├── assets/
│   │   ├── img/
│   │   ├── icons/
│   │   └── styles/
│   │       ├── globals.css
│   │       └── tailwind.css
│   ├── App.jsx
│   └── main.jsx
├── public/
├── index.html
├── vite.config.js
├── tailwind.config.js
├── package.json
└── .env.example
```

---

## PHASE 3: FRONTEND CONVERSION MAP

### PHP to React Component Mapping

| Old (PHP) | New (React) | Type | Notes |
|-----------|------------|------|-------|
| Login.html | Login.jsx | Page | Form submission → API |
| Parent.php | ParentSelection.jsx | Page | Fetch ledgers → Select → Navigate |
| Entry-page.php | DataEntry.jsx | Page | Form with date/entry fields |
| View-page.php | ViewResults.jsx | Page | Table with filtering |
| statement.php | Reports.jsx | Page | Aggregated data display |
| Header template | Header.jsx | Component | Navigation, user info |
| Footer template | Footer.jsx | Component | Footer content |
| Sidebar nav | Sidebar.jsx | Component | Navigation menu |

### Reusable Components to Create

1. **Form Components**
   - LoginForm.jsx
   - DataEntryForm.jsx
   - FilterForm.jsx

2. **Table Components**
   - DataTable.jsx
   - TransactionTable.jsx
   - ReportTable.jsx

3. **Layout Components**
   - AuthLayout.jsx
   - DashboardLayout.jsx

4. **UI Components**
   - Card.jsx
   - Modal.jsx
   - Button.jsx
   - Input.jsx
   - Select.jsx
   - DatePicker.jsx

---

## PHASE 4: API CONVERSION STRATEGY

### Current Flow → API Flow

**Current (PHP):**
```
Browser → Entry-page.php (GET params) → Render HTML + Inline Data
```

**New (API):**
```
React Frontend → API Endpoint → JSON Response
```

### API Endpoints to Create

#### Authentication APIs
```
POST   /api/auth/login           - Username/Password → JWT Token
POST   /api/auth/logout          - Clear token
GET    /api/auth/verify          - Check token validity
POST   /api/auth/refresh         - Refresh JWT token
```

#### Ledger APIs
```
GET    /api/ledgers              - List user's ledgers
GET    /api/ledgers/:id          - Get ledger details
GET    /api/ledgers/:id/children - Get child ledgers (parent select)
POST   /api/ledgers              - Create ledger
PUT    /api/ledgers/:id          - Update ledger
```

#### Entry APIs
```
GET    /api/entries              - List entries (with filters)
GET    /api/entries/:id          - Get single entry
POST   /api/entries              - Create entry
PUT    /api/entries/:id          - Update entry
DELETE /api/entries/:id          - Delete entry
```

#### Report APIs
```
GET    /api/reports/statement    - Transaction statement
GET    /api/reports/summary      - Ledger summary
GET    /api/reports/history      - Transaction history
```

### API Response Format

```json
{
  "success": true,
  "status": 200,
  "data": {
    "id": 1,
    "username": "user@example.com"
  },
  "message": "Operation successful"
}
```

### Error Response Format

```json
{
  "success": false,
  "status": 400,
  "error": "VALIDATION_ERROR",
  "message": "Invalid input",
  "details": {
    "username": "Username is required"
  }
}
```

---

## PHASE 5: MIGRATION PHASES (Recommended Order)

### Phase 5.1: Foundation
- [ ] Set up React + Vite + TailwindCSS
- [ ] Create folder structure
- [ ] Configure routing
- [ ] Set up API client

### Phase 5.2: Authentication
- [ ] Create login API endpoint
- [ ] Create JWT authentication
- [ ] Build Login.jsx component
- [ ] Implement Auth Context
- [ ] Create Protected Routes

### Phase 5.3: Core Features
- [ ] Create Ledger APIs
- [ ] Build ParentSelection.jsx
- [ ] Build Dashboard
- [ ] Create DataEntry.jsx + API

### Phase 5.4: Data Display & Reporting
- [ ] Create ViewResults.jsx
- [ ] Build Reports.jsx
- [ ] Create Report APIs
- [ ] Implement filtering & sorting

### Phase 5.5: Polish & Optimize
- [ ] Add error handling & validations
- [ ] Implement loading states
- [ ] Add success notifications
- [ ] Mobile responsiveness
- [ ] Performance optimization

### Phase 5.6: Deployment
- [ ] Build React app
- [ ] Configure web server
- [ ] Set up environment variables
- [ ] API routing configuration
- [ ] CORS configuration

---

## PHASE 6: KEY IMPLEMENTATION DETAILS

### JWT Authentication Implementation

**Token Structure:**
```
Header: { alg: "HS256", typ: "JWT" }
Payload: { 
  user_id: 1, 
  username: "user", 
  role: "admin",
  exp: timestamp
}
Signature: HMAC-SHA256(header.payload, secret)
```

**Flow:**
1. User submits login → POST /api/auth/login
2. Server validates credentials → Generates JWT
3. React stores token in localStorage
4. Subsequent requests include token in Authorization header
5. Server validates token on each request

### Session to JWT Migration

**PHP Session (Old):**
```php
$_SESSION['user_id'] = $row["id"];
$_SESSION['updated_by'] = $row["updated_by"];
```

**JWT (New):**
```php
$token = jwt_encode([
    'user_id' => $row['id'],
    'username' => $row['username'],
    'updated_by' => $row['updated_by']
], $secret);
```

### Database Access Pattern

**Old (Inline SQL):**
```php
$sql = "select * from tbl_ledger where id='$id'";
```

**New (API):**
```javascript
const response = await api.get(`/api/ledgers/${id}`);
```

---

## PHASE 7: SECURITY IMPROVEMENTS

### Current Vulnerabilities & Fixes

| Issue | Current | Fix |
|-------|---------|-----|
| SQL Injection | Direct string concat | Prepared statements on backend |
| Password Storage | Plaintext in DB | Hash with bcrypt |
| Session Hijacking | PHP sessions | JWT with expiration |
| XSS Attacks | No sanitization | React auto-escapes content |
| CSRF | No protection | CSRF tokens in headers |

### Recommended Security Stack

1. **Password Hashing**
   ```php
   password_hash($password, PASSWORD_BCRYPT)
   ```

2. **JWT Secrets**
   ```
   Store in .env, rotate regularly
   Use strong random string (32+ chars)
   ```

3. **CORS Configuration**
   ```php
   header('Access-Control-Allow-Origin: https://yourdomain.com');
   header('Access-Control-Allow-Credentials: true');
   ```

4. **Input Validation**
   ```php
   filter_var(), preg_match(), prepared statements
   ```

5. **Rate Limiting**
   ```
   Limit login attempts to 5 per 15 minutes
   ```

---

## PHASE 8: MIGRATION CHECKLIST

### Week 1: Setup
- [ ] Clone project structure
- [ ] Initialize React + Vite
- [ ] Configure TailwindCSS
- [ ] Set up Git
- [ ] Create .env files

### Week 2: Authentication
- [ ] Create JWT PHP library
- [ ] Build login API
- [ ] Create Login component
- [ ] Test login flow
- [ ] Implement token persistence

### Week 3: Core Pages
- [ ] Build ParentSelection page
- [ ] Build Dashboard page
- [ ] Create ledger APIs
- [ ] Test data flow

### Week 4: Data Entry
- [ ] Build DataEntry page
- [ ] Create entry APIs
- [ ] Implement form validation
- [ ] Test CRUD operations

### Week 5: Reporting & Filtering
- [ ] Build Reports page
- [ ] Create report APIs
- [ ] Add filtering & sorting
- [ ] Test data aggregation

### Week 6: Testing & Polish
- [ ] Unit tests
- [ ] Integration tests
- [ ] Bug fixes
- [ ] Performance optimization
- [ ] Mobile testing

### Week 7: Deployment Prep
- [ ] Environment configuration
- [ ] Build optimization
- [ ] Web server configuration
- [ ] CORS setup
- [ ] Monitoring setup

### Week 8: Go Live
- [ ] Deploy to staging
- [ ] Final testing
- [ ] Deploy to production
- [ ] Monitor & support
- [ ] Documentation

---

## PHASE 9: ENVIRONMENT SETUP

### .env Configuration

```
# Frontend
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=XCH555
VITE_APP_VERSION=1.0.0

# Backend
DB_HOST=localhost
DB_USER=555prouser
DB_PASSWORD=e2OFVjrRK77ljyfs4z@R
DB_NAME=555prodb
JWT_SECRET=your-secret-key-min-32-chars
JWT_EXPIRY=86400
NODE_ENV=development
```

### Backend API Base Path

All PHP APIs should be under `/api/` directory:
```
/api/auth/login.php
/api/auth/logout.php
/api/ledgers/list.php
/api/ledgers/get.php
/api/entries/create.php
/etc.
```

---

## PHASE 10: BEST PRACTICES

### React Best Practices
1. Use functional components with hooks
2. Keep components small and focused
3. Use custom hooks for logic reuse
4. Implement proper error boundaries
5. Add loading and error states
6. Use lazy loading for code splitting
7. Memoize expensive computations
8. Keep prop drilling to minimum (use Context)

### API Best Practices
1. Always use prepared statements
2. Validate all inputs
3. Return consistent response format
4. Use appropriate HTTP status codes
5. Implement proper error handling
6. Add request/response logging
7. Use rate limiting
8. Document all endpoints

### Database Best Practices
1. Use indexes for frequently queried columns
2. Optimize JOIN operations
3. Implement caching where appropriate
4. Monitor slow queries
5. Regular backups
6. Implement soft deletes

---

## PHASE 11: SCALABILITY CONSIDERATIONS

### For Growth
1. Implement Redux for complex state (instead of Context)
2. Add service workers for offline capability
3. Implement API response caching
4. Add pagination for large datasets
5. Implement search indexing
6. Consider microservices if business logic grows
7. Implement background jobs for heavy processing
8. Add real-time features (WebSockets) if needed

### Performance Optimization
1. Code splitting by route
2. Image optimization (WebP, lazy loading)
3. API response compression (gzip)
4. Database query optimization
5. CDN for static assets
6. Browser caching strategies
7. Minification and bundling
8. Lazy load components

---

## SUCCESS CRITERIA

✓ All existing functionality works in React  
✓ Performance equal or better than PHP version  
✓ Authentication secure with JWT  
✓ Database schema unchanged  
✓ Zero data loss during migration  
✓ Mobile responsive  
✓ API response time < 500ms  
✓ 95%+ test coverage  
✓ Proper error handling  
✓ Documented and maintainable code  

---

## NEXT STEPS

1. Review this migration strategy
2. Approve tech stack and folder structure
3. Set up React project (see PHASE 2 implementation)
4. Create API endpoints (see PHASE 4 implementation)
5. Build authentication system first (see PHASE 6)
6. Incrementally migrate pages
7. Test thoroughly at each phase
8. Deploy to staging environment
9. Get user feedback
10. Deploy to production

---

**Document Version**: 1.0  
**Created**: 2026-05-23  
**Status**: Ready for Implementation
