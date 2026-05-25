import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [parentLoginInfo, setParentLoginInfo] = useState(null);
  const [balance, setBalance] = useState(0);
  const [shifts, setShifts] = useState([]);
  const [activeShift, setActiveShift] = useState(null);
  const [loading, setLoading] = useState(true);

  // Check session on mount
  useEffect(() => {
    checkSession();
  }, []);

  const checkSession = async () => {
    try {
      const response = await axios.get('/api?action=get_session');
      if (response.data.logged_in) {
        setUser(response.data.user);
        setParentLoginInfo(null);
        fetchBalance();
        fetchShifts();
      } else if (response.data.parent_selection_required) {
        setParentLoginInfo({
          parent_id: response.data.parent_id,
          name: response.data.name
        });
        setUser(null);
      } else {
        setUser(null);
        setParentLoginInfo(null);
      }
    } catch (err) {
      console.error('Session validation error:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchBalance = async () => {
    try {
      const response = await axios.get('/api?action=get_balance');
      if (response.data.success) {
        setBalance(response.data.balance);
      }
    } catch (err) {
      console.error('Failed to fetch balance:', err);
    }
  };

  const fetchShifts = async () => {
    try {
      const response = await axios.get('/api?action=get_shifts');
      if (response.data.success) {
        setShifts(response.data.shifts);
        // Auto-select first non-expired shift if none selected
        if (response.data.shifts.length > 0) {
          const active = response.data.shifts.find(s => !s.expired);
          setActiveShift(active || response.data.shifts[0]);
        } else {
          setActiveShift(null);
        }
      }
    } catch (err) {
      console.error('Failed to fetch shifts:', err);
    }
  };

  const login = async (username, password) => {
    try {
      const response = await axios.post('/api?action=login', { username, password });
      if (response.data.success) {
        if (response.data.parent_selection_required) {
          setParentLoginInfo({
            parent_id: response.data.parent_id,
            name: response.data.name
          });
          setUser(null);
        } else {
          setUser(response.data.user);
          setParentLoginInfo(null);
          fetchBalance();
        }
        return { success: true, parent_selection_required: response.data.parent_selection_required };
      } else {
        return { success: false, error: response.data.error || 'Invalid credentials' };
      }
    } catch (err) {
      return { success: false, error: 'Server connection failed' };
    }
  };

  const selectChild = async (childId) => {
    try {
      const response = await axios.post('/api?action=select_child', { child_id: childId });
      if (response.data.success) {
        setUser(response.data.user);
        setParentLoginInfo(null);
        fetchBalance();
        return { success: true };
      } else {
        return { success: false, error: response.data.error || 'Failed to select login' };
      }
    } catch (err) {
      return { success: false, error: 'Request failed' };
    }
  };

  const logout = async () => {
    try {
      await axios.get('/api?action=logout');
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      setUser(null);
      setParentLoginInfo(null);
      setBalance(0);
    }
  };

  return (
    <AuthContext.Provider value={{
      user,
      parentLoginInfo,
      balance,
      loading,
      login,
      selectChild,
      logout,
      fetchBalance
    }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
