import { useState } from 'react';
import { useAuth } from '../context/AuthContext.jsx';

export default function Login() {
  const { login } = useAuth();
  const [form, setForm] = useState({ username: '', password: '' });
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  async function handleSubmit(event) {
    event.preventDefault();
    setError('');

    if (!form.username.trim() || !form.password.trim()) {
      setError('Username and password are required.');
      return;
    }

    setBusy(true);
    try {
      const result = await login(form.username, form.password);
      if (!result.success) {
        setError(result.error || 'Invalid login.');
      }
    } catch (err) {
      setError(err.message || 'Unable to login.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <main className="auth-page">
      <section className="auth-panel glass">
        <div className="auth-brand">
          <span className="brand-mark">5</span>
          <div>
            <h1>XCH555</h1>
            <p>Ledger access</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="auth-form">
          <label className="floating-field">
            <input
              value={form.username}
              onChange={(event) => setForm((current) => ({ ...current, username: event.target.value }))}
              placeholder=" "
              autoComplete="username"
            />
            <span>Username</span>
          </label>
          <label className="floating-field">
            <input
              type="password"
              value={form.password}
              onChange={(event) => setForm((current) => ({ ...current, password: event.target.value }))}
              placeholder=" "
              autoComplete="current-password"
            />
            <span>Password</span>
          </label>
          {error ? <div className="form-error">{error}</div> : null}
          <button className="primary-button" disabled={busy}>{busy ? 'Checking...' : 'Login'}</button>
        </form>
      </section>
    </main>
  );
}
