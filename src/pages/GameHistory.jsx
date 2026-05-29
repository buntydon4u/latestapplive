import { useEffect, useMemo, useState } from 'react';
import { api } from '../lib/api.js';
import { EmptyState, PremiumButton, StatusBadge, formatMoney } from '../components/DashboardLayout.jsx';

function formatDate(value) {
  if (!value) return '--';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString('en-GB', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
}

export default function GameHistory() {
  const [transactions, setTransactions] = useState([]);
  const [shifts, setShifts] = useState([]);
  const [filter, setFilter] = useState('all');

  useEffect(() => {
    Promise.all([api.transactions(), api.shifts()]).then(([transactionResult, shiftResult]) => {
      if (transactionResult.success) setTransactions(transactionResult.transactions || []);
      if (shiftResult.success) setShifts(shiftResult.shifts || []);
    });
  }, []);

  const filtered = useMemo(() => {
    if (filter === 'all') return transactions;
    return transactions.filter((transaction) => String(transaction.status || '').toLowerCase() === filter);
  }, [filter, transactions]);

  return (
    <div className="premium-page">
      <section className="premium-section">
        <div className="premium-title-row">
          <div>
            <span className="eyebrow">Played Games</span>
            <h1>Game History</h1>
            <p className="title-subline">{transactions.length} entries · {shifts.length} shifts</p>
          </div>
          <div className="filter-tabs">
            {['all', 'win', 'loss'].map((item) => (
              <PremiumButton key={item} className={filter === item ? 'filter-active' : ''} onClick={() => setFilter(item)}>
                {item}
              </PremiumButton>
            ))}
          </div>
        </div>
        <div className="premium-card-list">
          {filtered.map((transaction) => (
            <article className="premium-record-card" key={transaction.id}>
              <div>
                <h3>{transaction.shift_name || 'Market'}</h3>
                <p>{transaction.display_date || formatDate(transaction.t_date || transaction.dateoftrn)}</p>
              </div>
              <div className="record-amount">
                <b>RS {formatMoney(transaction.total_amount)}</b>
                <StatusBadge open={String(transaction.status || '').toLowerCase() !== 'loss'} label={transaction.status || 'Submitted'} />
              </div>
            </article>
          ))}
        </div>
        {!filtered.length ? <EmptyState title="No game history found." /> : null}
      </section>
    </div>
  );
}
