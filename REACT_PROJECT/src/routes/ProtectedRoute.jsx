import React from 'react';
import { Navigate, useLocation, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function ProtectedRoute({ children }) {
  const { user, parentLoginInfo, loading } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-950">
        <div className="flex flex-col items-center gap-4">
          <div className="w-12 h-12 border-4 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
          <span className="text-sm font-medium text-rose-500 tracking-wider">LOADING SECURE SESSION...</span>
        </div>
      </div>
    );
  }

  // If not logged in and not selecting child
  if (!user && !parentLoginInfo) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  // If parent selection is pending, and they aren't on the parent-selection page
  if (!user && parentLoginInfo && location.pathname !== '/parent-selection') {
    return <Navigate to="/parent-selection" replace />;
  }

  // If logged in fully, and they try to access parent-selection page, redirect to home
  if (user && location.pathname === '/parent-selection') {
    return <Navigate to="/" replace />;
  }

  return children ? children : <Outlet />;
}
