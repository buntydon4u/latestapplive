import { useEffect, useMemo, useState } from 'react';
import { useAuth } from '../context/AuthContext.jsx';

const navItems = [
  { id: 'home', label: 'Home', icon: 'H' },
  { id: 'entry', label: 'Entry', icon: 'E' },
  { id: 'fromto', label: 'From To', icon: 'F' },
  { id: 'cross', label: 'Cross', icon: 'C' },
  { id: 'jantri', label: 'Jantri', icon: 'J' },
  { id: 'results', label: 'View Transaction', icon: 'V' },
  { id: 'hisab', label: 'Hisab', icon: 'B' },
  { id: 'statement', label: 'Statement', icon: 'S' },
  { id: 'logout', label: 'Logout', icon: 'L' }
];

const primaryNavItems = navItems.slice(0, 5);
const secondaryNavItems = navItems.slice(5);

function formatMoney(value) {
  if (value === null || value === undefined) return '--';
  return new Intl.NumberFormat('en-IN', { maximumFractionDigits: 2 }).format(value);
}

function ShiftTimer() {
  const [remaining, setRemaining] = useState('Select shift');

  useEffect(() => {
    let interval;
    const handler = (event) => {
      window.clearInterval(interval);
      const deadline = Number(event.detail?.deadline || 0) * 1000;
      if (!deadline) {
        setRemaining('Select shift');
        return;
      }

      const tick = () => {
        const diff = deadline - Date.now();
        if (diff <= 0) {
          setRemaining('Expired');
          return;
        }
        const minutes = Math.floor(diff / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);
        setRemaining(`${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`);
      };

      tick();
      interval = window.setInterval(tick, 1000);
    };

    window.addEventListener('shift-change', handler);
    return () => {
      window.clearInterval(interval);
      window.removeEventListener('shift-change', handler);
    };
  }, []);

  return <span className={remaining === 'Expired' ? 'timer expired' : 'timer'}>{remaining}</span>;
}

export default function DashboardLayout({ children, activePage, onNavigate }) {
  const { user, balance, refreshBalance, logout } = useAuth();
  const [collapsed, setCollapsed] = useState(false);
  const initials = useMemo(() => (user?.name || 'U').slice(0, 2).toUpperCase(), [user]);

  return (
    <div className={`app-shell ${collapsed ? 'is-collapsed' : ''}`}>
      <aside className="sidebar">
        <button className="icon-button sidebar-toggle" onClick={() => setCollapsed((value) => !value)} title="Toggle sidebar">
          {collapsed ? '>' : '<'}
        </button>
        <nav className="nav-list primary-nav">
          {primaryNavItems.map((item) => (
            <button
              key={item.id}
              className={`nav-item ${activePage === item.id ? 'active' : ''}`}
              onClick={() => onNavigate(item.id)}
              title={item.label}
            >
              <span>{item.icon}</span>
              <strong>{item.label}</strong>
            </button>
          ))}
        </nav>
        <nav className="nav-list secondary-nav">
          {secondaryNavItems.map((item) => (
            <button
              key={item.id}
              className={`nav-item ${activePage === item.id ? 'active' : ''}`}
              onClick={() => item.id === 'logout' ? logout() : onNavigate(item.id)}
              title={item.label}
            >
              <span>{item.icon}</span>
              <strong>{item.label}</strong>
            </button>
          ))}
        </nav>
      </aside>

      <div className="workspace">
        <header className="topbar">
          <div className="user-chip">
            <span className="avatar">{initials}</span>
            <span>
              <b>{user?.name}</b>
              <small>Ledger login</small>
            </span>
          </div>
          <div className="topbar-actions">
            <div className="metric">
              <small>Shift</small>
              <ShiftTimer />
            </div>
            <div className="metric">
              <small>Coins</small>
              <b>{formatMoney(balance)}</b>
            </div>
            <button className="icon-button" onClick={refreshBalance} title="Reload balance">R</button>
            <button className="ghost-button" onClick={logout}>Logout</button>
          </div>
        </header>
        <main>{children}</main>
        <footer>React migration shell connected to root API router</footer>
      </div>
    </div>
  );
}
