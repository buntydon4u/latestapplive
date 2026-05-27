import { useEffect, useState } from 'react';
import { api } from '../lib/api.js';
import { BalanceCard, EmptyState, PremiumButton, StatusBadge, formatMoney } from '../components/DashboardLayout.jsx';
import { useAuth } from '../context/AuthContext.jsx';

function formatDate(value) {
  if (!value) return '--';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

export default function Wallet() {
  const { balance, refreshBalance } = useAuth();
  const [transactions, setTransactions] = useState([]);
  const [hisabs, setHisabs] = useState([]);

  useEffect(() => {
    Promise.all([refreshBalance(), api.transactions(), api.hisabs()]).then(([, transactionResult, hisabResult]) => {
      if (transactionResult.success) setTransactions(transactionResult.transactions || []);
      if (hisabResult.success) setHisabs(hisabResult.hisabs || []);
    });
  }, [refreshBalance]);

  return (
    <div className="premium-page">
      <BalanceCard balance={balance} />
      <section className="wallet-actions">
        <PremiumButton className="green-cta">+ Add Money</PremiumButton>
        <PremiumButton className="gold-cta">Withdraw</PremiumButton>
      </section>
      <section className="dashboard-api-summary">
        <article>
          <b>{transactions.length}</b>
          <span>Entries</span>
        </article>
        <article>
          <b>{hisabs.length}</b>
          <span>Hisabs</span>
        </article>
      </section>
      <section className="premium-section">
        <h2>Transaction History</h2>
        <div className="premium-card-list">
          {transactions.slice(0, 20).map((transaction) => (
            <article className="premium-record-card" key={transaction.id}>
              <div>
                <h3>{transaction.shift_name || 'Game Entry'}</h3>
                <p>{transaction.display_date || formatDate(transaction.t_date || transaction.dateoftrn)}</p>
              </div>
              <div className="record-amount">
                <b>RS {formatMoney(transaction.total_amount)}</b>
                <StatusBadge open label={transaction.status || 'Success'} />
              </div>
            </article>
          ))}
        </div>
        {!transactions.length ? <EmptyState title="No wallet history found." detail="No transactions were returned by the existing API." /> : null}
      </section>
    </div>
  );
}
