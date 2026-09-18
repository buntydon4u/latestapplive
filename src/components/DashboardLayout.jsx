import { useMemo, useState } from 'react';
import { useAuth } from '../context/AuthContext.jsx';
import { sanitizeWhatsappNumber } from '../lib/api.js';

const bottomNavItems = [
  { id: 'home', label: 'Home', icon: 'H' },
  { id: 'jantri', label: 'Jantri', icon: 'J' },
  { id: 'history', label: 'Game History', icon: 'G' },
  { id: 'help', label: 'Helpline', icon: 'whatsapp' }
];

const drawerItems = [
  { id: 'hisab', label: 'Hisab Report' },
  { id: 'statement', label: 'Statement' },
  { type: 'section', label: 'Account' },
  { id: 'changePassword', label: 'Change Password' }
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

export function BalanceCard({ balance, rate = 10, compact = false }) {
  return (
    <section className={`premium-balance-card ${compact ? 'is-compact' : ''}`}>
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
      <button className="header-icon" onClick={onMenu} type="button" aria-label="Open menu">
        <span aria-hidden="true">☰</span>
      </button>
      <BrandLogo initials={initials} />
      <div className="header-wallet">
        <button className="wallet-pill" onClick={() => onNavigate('wallet')} type="button">
          <span>W</span>
          <b>{formatMoney(balance)}</b>
        </button>
        {/* Notifications are temporarily disabled. */}
      </div>
      <span className="screen-reader-only">{activePage}</span>
    </header>
  );
}

function WhatsAppNavIcon() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="bottom-nav-whatsapp">
      <path d="M12 2.3a9.4 9.4 0 0 0-8 14.3L3 21l4.5-1.1A9.4 9.4 0 1 0 12 2.3Zm0 1.9a7.5 7.5 0 0 1 0 15c-1.3 0-2.6-.3-3.7-1l-.4-.2-2.3.6.6-2.2-.3-.4A7.5 7.5 0 0 1 12 4.2Z" />
      <path d="M9 7.7c-.2-.5-.4-.5-.7-.5H7.7c-.3 0-.7.1-1 .5-.4.4-1.3 1.2-1.3 3s1.3 3.5 1.5 3.7c.2.3 2.5 4 6.2 5.4 3.1 1.2 3.7.8 4.4.8.7-.1 2.1-.9 2.4-1.7.3-.8.3-1.5.2-1.7-.1-.2-.4-.3-.8-.5l-2.4-1.2c-.4-.1-.7-.2-.9.2l-.8 1c-.2.3-.5.3-.9.1-.4-.2-1.6-.6-3-1.9-1.1-1-1.9-2.2-2.1-2.6-.2-.4 0-.6.2-.8l.6-.7c.2-.2.2-.4.3-.6.1-.2 0-.5 0-.7L9 7.7Z" />
    </svg>
  );
}

function BottomTabNavigation({ activePage, onNavigate, helpline }) {
  function handleNavigate(item) {
    if (item.id === 'help') {
      const whatsappNumber = sanitizeWhatsappNumber(helpline?.helpline_number);
      if (!whatsappNumber) {
        window.alert('Helpline is not set. Please contact master.');
        return;
      }

      const message = encodeURIComponent('Hello, I need help with my account.');
      window.open(`https://wa.me/${whatsappNumber}?text=${message}`, '_blank', 'noopener,noreferrer');
      return;
    }
    onNavigate(item.id);
  }

  return (
    <nav className="auth-bottom-nav" aria-label="Authenticated navigation">
      {bottomNavItems.map((item) => (
        <button
          className={`bottom-nav-item ${item.id === 'help' ? 'helpline-item' : ''} ${activePage === item.id ? 'active' : ''}`}
          key={item.id}
          onClick={() => handleNavigate(item)}
          type="button"
        >
          <span>{item.icon === 'whatsapp' ? <WhatsAppNavIcon /> : item.icon}</span>
          <b>{item.label}</b>
        </button>
      ))}
    </nav>
  );
}

export default function DashboardLayout({ children, activePage, onNavigate }) {
  const { user, logout, parentSelection, switchAccount, helpline } = useAuth();
  const [drawerOpen, setDrawerOpen] = useState(false);

  function navigate(page) {
    setDrawerOpen(false);
    onNavigate(page);
  }

  async function handleSwitchAccount() {
    const result = await switchAccount();
    if (result?.success) {
      setDrawerOpen(false);
    }
  }

  return (
    <div className="premium-shell">
      <AuthHeader activePage={activePage} onNavigate={navigate} onMenu={() => setDrawerOpen(true)} />

      {drawerOpen ? (
        <div className="drawer-backdrop premium-drawer-backdrop" onClick={() => setDrawerOpen(false)}>
          <aside className="premium-menu-drawer" onClick={(event) => event.stopPropagation()}>
            <div className="premium-drawer-head">
              <BrandLogo initials={(user?.name || '55').slice(0, 2).toUpperCase()} />
              <div className="premium-drawer-user">
                <b>{user?.name || 'User'}</b>
                <span>Authenticated</span>
              </div>
              <button className="header-icon" onClick={() => setDrawerOpen(false)} type="button">X</button>
            </div>
            <div className="premium-drawer-list">
              {parentSelection ? (
                <button className="switch-account-action drawer-switch-account" onClick={handleSwitchAccount} type="button">
                  Switch account
                </button>
              ) : null}
              {drawerItems.map((item) => (
                item.type === 'section'
                  ? <span className="drawer-section-title" key={item.label}>{item.label}</span>
                  : (
                    <button key={item.id} onClick={() => navigate(item.id)} type="button">
                      {item.label}
                    </button>
                  )
              ))}
              <button className="danger-drawer-action" onClick={logout} type="button">Logout</button>
            </div>
          </aside>
        </div>
      ) : null}

      <main className="premium-main">{children}</main>
      <BottomTabNavigation activePage={activePage} onNavigate={navigate} helpline={helpline} />
    </div>
  );
}
