import { useMemo, useState } from 'react';
import { useAuth } from '../context/AuthContext.jsx';

const bottomNavItems = [
  { id: 'home', label: 'Home', icon: 'H' },
  { id: 'wallet', label: 'Wallet', icon: 'W' },
  { id: 'history', label: 'Game History', icon: 'G' },
  { id: 'chart', label: 'Chart', icon: 'C' }
];

const drawerItems = [
  { id: 'entry', label: 'Num Akhar Entry' },
  { id: 'fromto', label: 'From-To Entry' },
  { id: 'cross', label: 'Cross Entry' },
  { id: 'jantri', label: 'Jantri Entry' },
  { id: 'hisab', label: 'Hisab Report' },
  { id: 'statement', label: 'Statement' }
];

export function formatMoney(value) {
  if (value === null || value === undefined || value === '') return '0';
  return new Intl.NumberFormat('en-IN', { maximumFractionDigits: 2 }).format(Number(value || 0));
}

function BrandLogo({ initials }) {
  return (
    <div className="auth-logo" aria-label="App logo">
      <span>{initials}</span>
    </div>
  );
}

export function StatusBadge({ open, label }) {
  return (
    <span className={`premium-status ${open ? 'is-open' : 'is-closed'}`}>
      {label || (open ? 'Betting Open' : 'Betting Closed')}
    </span>
  );
}

export function EmptyState({ title = 'No records found.', detail }) {
  return (
    <div className="premium-empty">
      <b>{title}</b>
      {detail ? <span>{detail}</span> : null}
    </div>
  );
}

export function LoadingState({ label = 'Loading...' }) {
  return (
    <div className="premium-empty">
      <span className="loader" />
      <b>{label}</b>
    </div>
  );
}

export function BalanceCard({ balance, rate = 10 }) {
  return (
    <section className="premium-balance-card">
      <span>GAME RATE {rate}</span>
      <div>
        <small>RS</small>
        <b>{formatMoney(balance)} Balance</b>
      </div>
    </section>
  );
}

export function PremiumButton({ children, className = '', onClick, disabled, title }) {
  return (
    <button
      className={`premium-pressable ${className}`}
      disabled={disabled}
      onClick={onClick}
      title={title}
      type="button"
    >
      {children}
    </button>
  );
}

function AuthHeader({ activePage, onNavigate, onMenu }) {
  const { user, balance } = useAuth();
  const initials = useMemo(() => (user?.name || user?.username || '555').slice(0, 2).toUpperCase(), [user]);

  return (
    <header className="auth-header">
      <button className="header-icon" onClick={onMenu} type="button" aria-label="Open menu">Menu</button>
      <BrandLogo initials={initials} />
      <div className="header-wallet">
        <button className="wallet-pill" onClick={() => onNavigate('wallet')} type="button">
          <span>W</span>
          <b>{formatMoney(balance)}</b>
        </button>
        <button className="add-money-link" onClick={() => onNavigate('wallet')} type="button">Add Money</button>
        <button className="header-icon notification" type="button" aria-label="Notifications">
          !
          <small>1</small>
        </button>
      </div>
      <span className="screen-reader-only">{activePage}</span>
    </header>
  );
}

function BottomTabNavigation({ activePage, onNavigate }) {
  return (
    <nav className="auth-bottom-nav" aria-label="Authenticated navigation">
      {bottomNavItems.map((item) => (
        <button
          className={`bottom-nav-item ${activePage === item.id ? 'active' : ''}`}
          key={item.id}
          onClick={() => onNavigate(item.id)}
          type="button"
        >
          <span>{item.icon}</span>
          <b>{item.label}</b>
        </button>
      ))}
    </nav>
  );
}

export default function DashboardLayout({ children, activePage, onNavigate }) {
  const { user, logout } = useAuth();
  const [drawerOpen, setDrawerOpen] = useState(false);

  function navigate(page) {
    setDrawerOpen(false);
    onNavigate(page);
  }

  return (
    <div className="premium-shell">
      <AuthHeader activePage={activePage} onNavigate={navigate} onMenu={() => setDrawerOpen(true)} />

      {drawerOpen ? (
        <div className="drawer-backdrop premium-drawer-backdrop" onClick={() => setDrawerOpen(false)}>
          <aside className="premium-menu-drawer" onClick={(event) => event.stopPropagation()}>
            <div className="premium-drawer-head">
              <BrandLogo initials={(user?.name || '55').slice(0, 2).toUpperCase()} />
              <div>
                <b>{user?.name || 'User'}</b>
                <span>Authenticated</span>
              </div>
              <button className="header-icon" onClick={() => setDrawerOpen(false)} type="button">X</button>
            </div>
            <div className="premium-drawer-list">
              {drawerItems.map((item) => (
                <button key={item.id} onClick={() => navigate(item.id)} type="button">
                  {item.label}
                </button>
              ))}
              <button className="danger-drawer-action" onClick={logout} type="button">Logout</button>
            </div>
          </aside>
        </div>
      ) : null}

      <main className="premium-main">{children}</main>
      <BottomTabNavigation activePage={activePage} onNavigate={navigate} />
    </div>
  );
}
