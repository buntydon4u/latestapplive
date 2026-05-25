import React, { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

export default function ParentSelection() {
  const { parentLoginInfo, selectChild, logout } = useAuth();
  const navigate = useNavigate();
  const [childrenLogins, setChildrenLogins] = useState([]);
  const [selectedId, setSelectedId] = useState(null);
  const [loadingList, setLoadingList] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!parentLoginInfo) {
      navigate('/login');
      return;
    }
    // Set parent as default selected
    setSelectedId(parentLoginInfo.parent_id);
    fetchChildren();
  }, [parentLoginInfo]);

  const fetchChildren = async () => {
    try {
      const response = await axios.get(`/api?action=get_children&parent_id=${parentLoginInfo.parent_id}`);
      if (response.data.success) {
        setChildrenLogins(response.data.children);
      } else {
        setError('Failed to fetch available child logins.');
      }
    } catch (err) {
      setError('Connection error. Failed to load logins.');
    } finally {
      setLoadingList(false);
    }
  };

  const handleSignIn = async () => {
    if (!selectedId) return;
    setSubmitting(true);
    setError('');

    try {
      const res = await selectChild(selectedId);
      if (res.success) {
        navigate('/');
      } else {
        setError(res.error || 'Failed to select login');
      }
    } catch (err) {
      setError('Network error. Failed to sign in.');
    } finally {
      setSubmitting(false);
    }
  };

  if (!parentLoginInfo) return null;

  return (
    <div className="min-h-screen flex items-center justify-center p-4">
      <div className="w-full max-w-xl glass-card rounded-2xl p-8 relative overflow-hidden shadow-2xl">
        <div className="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-rose-500 via-purple-600 to-rose-500" />
        
        <div className="text-center mb-8">
          <h2 className="text-2xl font-bold tracking-tight text-white mb-2">Available Logins</h2>
          <p className="text-sm text-slate-400">Select which ledger you want to login as</p>
        </div>

        {error && (
          <div className="mb-6 p-4 rounded-xl bg-red-950/50 border border-red-500/30 text-red-400 text-sm">
            {error}
          </div>
        )}

        {loadingList ? (
          <div className="flex flex-col items-center gap-3 py-12">
            <div className="w-10 h-10 border-3 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
            <span className="text-sm text-slate-400">Loading accounts...</span>
          </div>
        ) : (
          <div className="space-y-6">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[320px] overflow-y-auto pr-1">
              {/* Parent Option */}
              <div
                onClick={() => setSelectedId(parentLoginInfo.parent_id)}
                className={`cursor-pointer p-5 rounded-xl border text-center transition-all duration-200 ${
                  selectedId === parentLoginInfo.parent_id
                    ? 'border-rose-500 bg-rose-500/10 shadow-lg shadow-rose-500/10'
                    : 'border-slate-800 bg-slate-900/50 hover:border-slate-700'
                }`}
              >
                <div className="font-bold text-white text-base mb-1">{parentLoginInfo.name}</div>
                <div className="text-xs text-rose-400 font-semibold tracking-wider uppercase">Parent (Default)</div>
              </div>

              {/* Children Options */}
              {childrenLogins.map((child) => (
                <div
                  key={child.id}
                  onClick={() => setSelectedId(child.id)}
                  className={`cursor-pointer p-5 rounded-xl border text-center transition-all duration-200 ${
                    selectedId === child.id
                      ? 'border-rose-500 bg-rose-500/10 shadow-lg shadow-rose-500/10'
                      : 'border-slate-800 bg-slate-900/50 hover:border-slate-700'
                  }`}
                >
                  <div className="font-bold text-white text-base mb-1">{child.ledger_name}</div>
                  <div className="text-xs text-slate-400 font-medium">Sub-ledger</div>
                </div>
              ))}
            </div>

            <div className="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-800">
              <button
                onClick={handleSignIn}
                disabled={submitting}
                className="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-semibold py-3 px-4 rounded-xl transition shadow-lg hover:shadow-rose-600/30 flex items-center justify-center gap-2 disabled:opacity-50"
              >
                {submitting ? (
                  <>
                    <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span>Signing in...</span>
                  </>
                ) : (
                  <span>Sign In</span>
                )}
              </button>
              
              <button
                onClick={logout}
                disabled={submitting}
                className="px-6 py-3 border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-white rounded-xl transition disabled:opacity-50"
              >
                Back to Login
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
