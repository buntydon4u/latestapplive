import { Routes as ReactRoutes, Route, Navigate } from 'react-router-dom'
import ProtectedRoute from './ProtectedRoute'

// Pages
import Login from '@pages/Login'
import Dashboard from '@pages/Dashboard'
import ParentSelection from '@pages/ParentSelection'
import DataEntry from '@pages/DataEntry'
import ViewResults from '@pages/ViewResults'
import Reports from '@pages/Reports'
import NotFound from '@pages/NotFound'

// Layouts
import AuthLayout from '@components/layouts/AuthLayout'
import DashboardLayout from '@components/layouts/DashboardLayout'

export default function Routes() {
  return (
    <ReactRoutes>
      {/* Public Routes */}
      <Route element={<AuthLayout />}>
        <Route path="/login" element={<Login />} />
      </Route>

      {/* Protected Routes */}
      <Route element={<ProtectedRoute />}>
        <Route element={<DashboardLayout />}>
          <Route path="/" element={<Dashboard />} />
          <Route path="/parent-selection" element={<ParentSelection />} />
          <Route path="/entry" element={<DataEntry />} />
          <Route path="/view-results" element={<ViewResults />} />
          <Route path="/reports" element={<Reports />} />
        </Route>
      </Route>

      {/* 404 Route */}
      <Route path="/404" element={<NotFound />} />
      <Route path="*" element={<Navigate to="/404" replace />} />
    </ReactRoutes>
  )
}
