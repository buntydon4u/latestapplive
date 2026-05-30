import { useMemo, useState } from 'react';
import { motion } from 'framer-motion';
import { useAuth } from '../context/AuthContext.jsx';

const fieldClass =
  'h-[62px] rounded-[10px] border border-[#8a5b06] bg-[#242424] px-5 text-[1.05rem] font-medium text-white outline-none transition duration-200 placeholder:text-[#adc2df] focus:border-[#ffcc00] focus:shadow-[0_0_0_3px_rgba(255,204,0,0.14)]';

function LoginInput({ label, ...inputProps }) {
  return (
    <label className="block">
      <span className="mb-3 block text-[1rem] font-bold leading-none text-[#ffcc00]">{label}</span>
      <input className={fieldClass} {...inputProps} />
    </label>
  );
}

function ReloadIcon() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
      <path d="M21 12a9 9 0 1 1-2.64-6.36" />
      <path d="M21 3v6h-6" />
    </svg>
  );
}

function createCaptcha() {
  const operation = Math.random() > 0.5 ? '+' : '-';
  const first = Math.floor(Math.random() * 9) + 1;
  const second = Math.floor(Math.random() * 9) + 1;

  if (operation === '+') {
    return { first, second, operation, answer: first + second };
  }

  const bigger = Math.max(first, second);
  const smaller = Math.min(first, second);
  return { first: bigger, second: smaller, operation, answer: bigger - smaller };
}

export default function Login() {
  const { login } = useAuth();
  const [form, setForm] = useState({ username: '', password: '', captcha: '' });
  const [captcha, setCaptcha] = useState(() => createCaptcha());
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const captchaLabel = useMemo(
    () => `${captcha.first} ${captcha.operation} ${captcha.second} = ?`,
    [captcha]
  );

  function refreshCaptcha() {
    setCaptcha(createCaptcha());
    setForm((current) => ({ ...current, captcha: '' }));
  }

  async function handleSubmit(event) {
    event.preventDefault();
    setError('');

    if (!form.username.trim() || !form.password.trim()) {
      setError('Username and password are required.');
      return;
    }

    if (!form.captcha.trim()) {
      setError('Captcha answer is required.');
      return;
    }

    if (Number(form.captcha) !== captcha.answer) {
      setError('Captcha answer is incorrect.');
      refreshCaptcha();
      return;
    }

    setBusy(true);
    try {
      const result = await login(form.username, form.password);
      if (!result.success) {
        setError(result.error || 'Invalid login.');
        refreshCaptcha();
      }
    } catch (err) {
      setError(err.message || 'Unable to login.');
      refreshCaptcha();
    } finally {
      setBusy(false);
    }
  }

  return (
    <main className="admin-login-page min-h-[100svh] w-full overflow-hidden bg-[#111] px-0 py-0 text-white">
      <motion.section
        className="admin-login-panel mx-auto flex min-h-[100svh] w-full max-w-[558px] flex-col justify-center rounded-[10px] border border-[#9a6506] px-10 py-12 shadow-[0_22px_70px_rgba(0,0,0,0.55)]"
        initial={{ opacity: 0, y: 14 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.36, ease: 'easeOut' }}
      >
        <header className="mb-10 text-center">
          <h1 className="m-0 text-[2.25rem] font-black leading-tight text-[#ffcc00] sm:text-[2.45rem]">
            555xch
          </h1>
        </header>

        <form onSubmit={handleSubmit} className="grid gap-8">
          <LoginInput
            label="Username"
            value={form.username}
            onChange={(event) => setForm((current) => ({ ...current, username: event.target.value }))}
            placeholder="Enter username"
            autoComplete="username"
            name="username"
          />

          <LoginInput
            label="Password"
            type="password"
            value={form.password}
            onChange={(event) => setForm((current) => ({ ...current, password: event.target.value }))}
            placeholder="Enter password"
            autoComplete="current-password"
            name="password"
          />

          <div>
            <div className="mb-3 flex items-center justify-between gap-4">
              <span className="block text-[1rem] font-bold leading-none text-[#ffcc00]">Captcha</span>
              <button
                type="button"
                className="grid h-9 w-9 place-items-center rounded-full border border-[#8a5b06] bg-[#242424] text-[#adc2df] transition hover:border-[#ffcc00] hover:text-[#ffcc00] focus:outline-none focus:ring-4 focus:ring-[#ffcc00]/20"
                onClick={refreshCaptcha}
                aria-label="Refresh captcha"
              >
                <ReloadIcon />
              </button>
            </div>
            <div className="grid grid-cols-[1fr_1.1fr] gap-3">
              <div className="grid h-[62px] place-items-center rounded-[10px] border border-[#8a5b06] bg-[#1b1b1b] px-4 text-[1.15rem] font-black text-[#ffcc00]">
                {captchaLabel}
              </div>
              <input
                className={fieldClass}
                type="number"
                min="0"
                inputMode="numeric"
                value={form.captcha}
                onChange={(event) => setForm((current) => ({ ...current, captcha: event.target.value }))}
                placeholder="Answer"
                autoComplete="off"
                name="captcha"
              />
            </div>
          </div>

          {error ? (
            <motion.div
              className="rounded-[10px] border border-[#8a5b06] bg-[#2a1710] px-4 py-3 text-sm font-semibold text-[#ffcc00]"
              initial={{ opacity: 0, y: -5 }}
              animate={{ opacity: 1, y: 0 }}
            >
              {error}
            </motion.div>
          ) : null}

          <motion.button
            type="submit"
            className="mt-[2px] h-[60px] w-full rounded-[10px] border-0 bg-[#e69100] text-[1.12rem] font-black text-white shadow-[0_12px_28px_rgba(230,145,0,0.24)] transition hover:bg-[#f09b05] focus:outline-none focus:ring-4 focus:ring-[#ffcc00]/25 disabled:opacity-65"
            whileTap={{ scale: 0.985 }}
            disabled={busy}
          >
            {busy ? 'Checking...' : 'Login'}
          </motion.button>
        </form>

      </motion.section>
    </main>
  );
}
