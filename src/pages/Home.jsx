import { useEffect, useState } from 'react';
import { api } from '../lib/api.js';
import { useAuth } from '../context/AuthContext.jsx';

const quickLinks = [
  { id: 'entry', label: 'Entry', hint: 'Num-Akhar entry' },
  { id: 'fromto', label: 'From To', hint: 'Range entry' },
  { id: 'cross', label: 'Cross', hint: 'Cross combinations' },
  { id: 'jantri', label: 'Jantri', hint: 'Full Jantri grid' },
  { id: 'results', label: 'View Transaction', hint: 'Past 30 days' },
  { id: 'hisab', label: 'Hisab', hint: 'Daily hisab list' },
  { id: 'statement', label: 'Statement', hint: 'Ledger statement' }
];

export default function Home({ onNavigate }) {
  const { user, balance } = useAuth();
  const [summary, setSummary] = useState({ shifts: 0, transactions: 0, hisabs: 0 });

  useEffect(() => {
    Promise.all([api.shifts(), api.transactions(), api.hisabs()]).then(([shiftResult, transactionResult, hisabResult]) => {
      setSummary({
        shifts: shiftResult.success ? (shiftResult.shifts || []).filter((shift) => !shift.expired).length : 0,
        transactions: transactionResult.success ? (transactionResult.transactions || []).length : 0,
        hisabs: hisabResult.success ? (hisabResult.hisabs || []).length : 0
      });
    });
  }, []);

  return (
    <div className="page-stack">
      <section className="home-hero">
        <div>
          <span className="eyebrow">Home</span>
          <h1>{user?.name}</h1>
          <p>Choose an entry or report section from the menu.</p>
        </div>
        <div className="home-balance">
          <small>Coin balance</small>
          <b>{balance === null || balance === undefined ? '--' : new Intl.NumberFormat('en-IN').format(balance)}</b>
        </div>
      </section>

      <section className="stats-grid">
        <article className="panel stat-card"><span>Active shifts</span><b>{summary.shifts}</b></article>
        <article className="panel stat-card"><span>Recent transactions</span><b>{summary.transactions}</b></article>
        <article className="panel stat-card"><span>Hisab records</span><b>{summary.hisabs}</b></article>
      </section>

      <section className="home-actions">
        {quickLinks.map((item) => (
          <button className="selection-card" key={item.id} onClick={() => onNavigate(item.id)}>
            <b>{item.label}</b>
            <small>{item.hint}</small>
          </button>
        ))}
      </section>
    </div>
  );
}
