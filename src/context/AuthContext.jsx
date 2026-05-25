import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { api } from '../lib/api.js';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [parentSelection, setParentSelection] = useState(null);
  const [balance, setBalance] = useState(null);
  const [loading, setLoading] = useState(true);

  const refreshBalance = useCallback(async () => {
    const result = await api.balance();
    if (result.success) {
      setBalance(Number(result.balance || 0));
    }
    return result;
  }, []);

  const checkSession = useCallback(async () => {
    setLoading(true);
    try {
      const result = await api.session();
      if (result.logged_in) {
        setUser(result.user);
        setParentSelection(null);
        await refreshBalance();
      } else if (result.parent_selection_required) {
        setUser(null);
        setParentSelection(result);
      } else {
        setUser(null);
        setParentSelection(null);
        setBalance(null);
      }
    } finally {
      setLoading(false);
    }
  }, [refreshBalance]);

  useEffect(() => {
    checkSession();
  }, [checkSession]);

  const login = useCallback(async (username, password) => {
    const result = await api.login(username, password);
    if (result.success && result.parent_selection_required) {
      setParentSelection(result);
      setUser(null);
      setBalance(null);
    } else if (result.success) {
      setUser(result.user);
      setParentSelection(null);
      await refreshBalance();
    }
    return result;
  }, [refreshBalance]);

  const selectChild = useCallback(async (childId) => {
    const result = await api.selectChild(childId);
    if (result.success) {
      setUser(result.user);
      setParentSelection(null);
      await refreshBalance();
    }
    return result;
  }, [refreshBalance]);

  const logout = useCallback(async () => {
    await api.logout();
    setUser(null);
    setParentSelection(null);
    setBalance(null);
  }, []);

  const value = useMemo(() => ({
    user,
    parentSelection,
    balance,
    loading,
    login,
    logout,
    selectChild,
    checkSession,
    refreshBalance
  }), [user, parentSelection, balance, loading, login, logout, selectChild, checkSession, refreshBalance]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const value = useContext(AuthContext);
  if (!value) {
    throw new Error('useAuth must be used inside AuthProvider');
  }
  return value;
}
