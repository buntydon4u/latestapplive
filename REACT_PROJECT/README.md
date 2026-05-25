# XCH555 React Frontend

Modern React-based frontend for XCH555 with API-driven architecture.

## Installation

```bash
npm install
```

## Development

```bash
npm run dev
```

Open http://localhost:5173

## Building

```bash
npm run build
```

## Environment Configuration

Create `.env.local` file:

```
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=XCH555
```

## Project Structure

- `/src/components` - Reusable UI components
- `/src/pages` - Page components
- `/src/services` - API and utility services
- `/src/hooks` - Custom React hooks
- `/src/context` - React Context for state management
- `/src/utils` - Helper functions
- `/src/assets` - Images, icons, styles

## Architecture

- **Frontend Framework**: React 18 with Vite
- **Routing**: React Router v6
- **HTTP Client**: Axios
- **Styling**: TailwindCSS
- **State Management**: React Context + Zustand
- **Form Handling**: React Hook Form + Zod

## Key Features

- ✓ JWT Authentication
- ✓ Protected Routes
- ✓ API-driven Data
- ✓ Responsive Design
- ✓ Form Validation
- ✓ Error Handling
- ✓ Loading States

## Documentation

See `/docs` folder for detailed documentation.
