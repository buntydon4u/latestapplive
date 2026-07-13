# Implementation Best Practices & Code Examples

## 1. Component Structure Best Practices

### ✓ Good: Modular, Focused Components

```jsx
// src/components/forms/LoginForm.jsx
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import { zodResolver } from '@hookform/resolvers/zod'

// Define validation schema
const LoginSchema = z.object({
  username: z.string().min(3, 'Username must be at least 3 characters'),
  password: z.string().min(6, 'Password must be at least 6 characters')
})

export default function LoginForm({ onSubmit, isLoading }) {
  const { register, handleSubmit, formState: { errors } } = useForm({
    resolver: zodResolver(LoginSchema)
  })

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <label className="block text-sm font-medium mb-1">Username</label>
        <input
          {...register('username')}
          className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          disabled={isLoading}
        />
        {errors.username && <p className="text-red-600 text-sm mt-1">{errors.username.message}</p>}
      </div>

      <div>
        <label className="block text-sm font-medium mb-1">Password</label>
        <input
          {...register('password')}
          type="password"
          className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
          disabled={isLoading}
        />
        {errors.password && <p className="text-red-600 text-sm mt-1">{errors.password.message}</p>}
      </div>

      <button
        type="submit"
        disabled={isLoading}
        className="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white py-2 rounded-lg transition"
      >
        {isLoading ? 'Signing in...' : 'Sign In'}
      </button>
    </form>
  )
}
```

### ✗ Bad: Overly Complex, Tightly Coupled

```jsx
// ❌ Avoid this pattern
export default function BadComponent() {
  // Too much logic in one component
  // No separation of concerns
  // Hard to test and reuse
  // Direct API calls mixed with UI logic
}
```

## 2. API Service Pattern

### ✓ Good: Centralized, Typed API Calls

```js
// src/services/api/entries.js
import client from './client'

const ENTRIES_BASE = '/entries'

export const entryService = {
  async list(filters = {}) {
    const response = await client.get(ENTRIES_BASE, { params: filters })
    return response.data
  },

  async get(id) {
    const response = await client.get(`${ENTRIES_BASE}/${id}`)
    return response.data
  },

  async create(data) {
    const response = await client.post(ENTRIES_BASE, data)
    return response.data
  },

  async update(id, data) {
    const response = await client.put(`${ENTRIES_BASE}/${id}`, data)
    return response.data
  },

  async delete(id) {
    return await client.delete(`${ENTRIES_BASE}/${id}`)
  }
}
```

### ✗ Bad: Scattered API Calls

```jsx
// ❌ Avoid calling API directly in components
function BadComponent() {
  useEffect(() => {
    fetch('/api/entries')
      .then(r => r.json())
      .then(d => setData(d))
      .catch(e => alert(e.message)) // ❌ Bad error handling
  }, [])
}
```

## 3. State Management Pattern

### ✓ Good: Context + Custom Hooks

```jsx
// src/context/DataContext.jsx
import { createContext, useReducer, useCallback } from 'react'

const DataContext = createContext()

const initialState = {
  entries: [],
  loading: false,
  error: null
}

function dataReducer(state, action) {
  switch (action.type) {
    case 'FETCH_START':
      return { ...state, loading: true, error: null }
    case 'FETCH_SUCCESS':
      return { ...state, entries: action.payload, loading: false }
    case 'FETCH_ERROR':
      return { ...state, error: action.payload, loading: false }
    default:
      return state
  }
}

export function DataProvider({ children }) {
  const [state, dispatch] = useReducer(dataReducer, initialState)

  const fetchEntries = useCallback(async (filters) => {
    dispatch({ type: 'FETCH_START' })
    try {
      const data = await entryService.list(filters)
      dispatch({ type: 'FETCH_SUCCESS', payload: data })
    } catch (error) {
      dispatch({ type: 'FETCH_ERROR', payload: error.message })
    }
  }, [])

  return (
    <DataContext.Provider value={{ ...state, fetchEntries }}>
      {children}
    </DataContext.Provider>
  )
}

export function useData() {
  return useContext(DataContext)
}
```

## 4. Error Handling Pattern

### ✓ Good: Comprehensive Error Handling

```jsx
// src/hooks/useFetchEntries.js
import { useState, useEffect } from 'react'
import { entryService } from '@services/api/entries'

export function useFetchEntries(filters = {}) {
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    let isMounted = true

    const fetchData = async () => {
      try {
        setLoading(true)
        setError(null)
        const result = await entryService.list(filters)
        
        if (isMounted) {
          setData(result.entries)
        }
      } catch (err) {
        if (isMounted) {
          // Handle specific error types
          if (err.response?.status === 401) {
            setError('Your session has expired. Please login again.')
          } else if (err.response?.status === 403) {
            setError('You do not have permission to access this resource.')
          } else if (err.response?.status === 404) {
            setError('Resource not found.')
          } else {
            setError(err.message || 'An error occurred')
          }
        }
      } finally {
        if (isMounted) {
          setLoading(false)
        }
      }
    }

    fetchData()

    return () => { isMounted = false }
  }, [filters])

  return { data, loading, error }
}
```

### ✗ Bad: Silent Failures

```jsx
// ❌ Avoid
useEffect(() => {
  fetch('/api/entries')
    .then(r => r.json())
    .then(d => setData(d))
    // ❌ No error handling - user won't know what went wrong
}, [])
```

## 5. PHP API Best Practices

### ✓ Good: Secure, Validated API

```php
<?php
// /api/entries/create.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('METHOD_NOT_ALLOWED', 'Only POST allowed', 405);
}

// Authenticate user
$user = AuthMiddleware::verify();

// Parse and validate input
$input = json_decode(file_get_contents('php://input'), true);

$validator = new Validator();
$errors = $validator->validate($input, [
    'ledger_id' => 'required|integer',
    'date' => 'required|date',
    'amount' => 'required|numeric|positive',
    'description' => 'required|string|max:500'
]);

if ($errors) {
    Response::error('VALIDATION_ERROR', 'Validation failed', 400, $errors);
}

// Get database connection
$conn = getDBConnection();

// Prepare statement - prevents SQL injection
$stmt = $conn->prepare(
    "INSERT INTO tbl_entries (ledger_id, date, amount, description, created_by) 
     VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
    Response::error('DATABASE_ERROR', 'Preparation failed', 500);
}

$stmt->bind_param('isdsi', 
    $input['ledger_id'],
    $input['date'],
    $input['amount'],
    $input['description'],
    $user['user_id']
);

if (!$stmt->execute()) {
    Response::error('DATABASE_ERROR', 'Failed to create entry', 500);
}

$id = $conn->insert_id;
$stmt->close();

Response::success([
    'id' => $id,
    'ledger_id' => $input['ledger_id'],
    'date' => $input['date'],
    'amount' => $input['amount']
], 'Entry created successfully', 201);
```

### ✗ Bad: Insecure API

```php
<?php
// ❌ Avoid this pattern

$_SESSION['login'] = $_GET['login']; // ❌ No validation
$sql = "select * from tbl_ledger where id='" . $_GET['id'] . "'"; // ❌ SQL injection!
$rs = mysqli_query($conn, $sql);

header('Location: page.php?result=success'); // ❌ No proper response format
```

## 6. Testing Pattern

### ✓ Good: Testable Components

```jsx
// src/components/Button.test.jsx
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import Button from './Button'

describe('Button Component', () => {
  it('renders button with text', () => {
    render(<Button>Click me</Button>)
    expect(screen.getByText('Click me')).toBeInTheDocument()
  })

  it('calls onClick handler when clicked', async () => {
    const handleClick = jest.fn()
    render(<Button onClick={handleClick}>Click</Button>)
    
    await userEvent.click(screen.getByText('Click'))
    expect(handleClick).toHaveBeenCalledTimes(1)
  })

  it('is disabled when disabled prop is true', () => {
    render(<Button disabled>Click</Button>)
    expect(screen.getByText('Click')).toBeDisabled()
  })
})
```

## 7. Database Query Pattern

### ✓ Good: Prepared Statements

```php
<?php
// ✓ Safe from SQL injection

// Single query
$stmt = $conn->prepare("SELECT * FROM tbl_ledger WHERE id = ?");
$stmt->bind_param('i', $ledger_id);
$stmt->execute();
$result = $stmt->get_result();

// Multiple parameters
$stmt = $conn->prepare(
    "SELECT * FROM tbl_entries 
     WHERE ledger_id = ? AND date BETWEEN ? AND ? AND status = ?
     ORDER BY date DESC"
);
$stmt->bind_param('isss', $ledger_id, $start_date, $end_date, $status);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Process row
}

$stmt->close();
```

### ✗ Bad: String Concatenation

```php
<?php
// ❌ NEVER do this - SQL Injection vulnerability!

$id = $_GET['id'];
$sql = "SELECT * FROM tbl_ledger WHERE id = '" . $id . "'";
// If id = "1' OR '1'='1", this becomes: WHERE id = '1' OR '1'='1'
// Which returns all rows!
```

## 8. Configuration Management

### ✓ Good: Environment Variables

```js
// src/config/api.js
export const API_CONFIG = {
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  timeout: import.meta.env.VITE_API_TIMEOUT || 30000,
  retryAttempts: 3,
  retryDelay: 1000
}

export const AUTH_CONFIG = {
  tokenKey: import.meta.env.VITE_TOKEN_STORAGE_KEY || 'auth_token',
  expiry: import.meta.env.VITE_TOKEN_EXPIRY || 86400
}
```

### ✗ Bad: Hardcoded Values

```js
// ❌ Never hardcode in code
const API_URL = 'http://localhost:8000/api' // Will break in production
const SECRET = 'mysecret123' // Security risk!
```

## 9. Performance Optimization

### ✓ Good: Code Splitting & Lazy Loading

```jsx
import { lazy, Suspense } from 'react'
import Loader from '@components/Loader'

// Lazy load pages
const Reports = lazy(() => import('@pages/Reports'))
const Dashboard = lazy(() => import('@pages/Dashboard'))

function Routes() {
  return (
    <Suspense fallback={<Loader />}>
      <Route path="/dashboard" element={<Dashboard />} />
      <Route path="/reports" element={<Reports />} />
    </Suspense>
  )
}
```

### ✓ Good: Memoization for Performance

```jsx
import { memo, useCallback } from 'react'

const DataTable = memo(function DataTable({ data, onEdit }) {
  // Component only re-renders if data or onEdit actually changes
  return (
    // Table rendering
  )
})

function ParentComponent() {
  // Use useCallback to prevent unnecessary re-renders
  const handleEdit = useCallback((id) => {
    // Edit logic
  }, [])

  return <DataTable data={entries} onEdit={handleEdit} />
}
```

## 10. Security Checklist

### React Frontend
- [ ] No sensitive data in localStorage (use secure HTTP-only cookies if needed)
- [ ] Escape all user input (React does this by default)
- [ ] Use HTTPS only
- [ ] Validate all inputs on client-side (also validate on server!)
- [ ] Use Content Security Policy headers
- [ ] Keep dependencies updated

### PHP Backend
- [ ] Always use prepared statements
- [ ] Hash passwords with bcrypt
- [ ] Validate all inputs
- [ ] Set proper HTTP headers
- [ ] Log suspicious activities
- [ ] Rate limit API endpoints
- [ ] Use HTTPS only
- [ ] Keep PHP updated
- [ ] Don't expose error details to clients
- [ ] Regular security audits

## Summary Table

| Pattern | Good | Bad |
|---------|------|-----|
| State | Context + Hooks | Global variables |
| API | Centralized service | Direct fetch in components |
| Error Handling | Comprehensive try-catch | Silent failures |
| SQL | Prepared statements | String concatenation |
| Components | Small, focused | Large, monolithic |
| Validation | Schema (Zod) | Manual checks |
| Configuration | Environment variables | Hardcoded values |
| Caching | Memoization | Unnecessary re-renders |
| Secrets | Environment variables | In code |
| Logging | Structured logging | console.log() |
