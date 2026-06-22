import { useEffect, useState } from 'react';
import { api } from '../lib/api.js';
import { EmptyState, StatusBadge, formatMoney } from '../components/DashboardLayout.jsx';

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

function GameDetailModal({ transaction, onClose }) {
  const [details, setDetails] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.transactionDetails(transaction.id).then((result) => {
      setDetails(result);
      setLoading(false);
    });
  }, [transaction.id]);

  const shiftName = details?.shift_name || transaction.shift_name || '--';
  const date = details?.display_date || formatDate(details?.t_date || transaction.t_date || transaction.dateoftrn);
  const items = Array.isArray(details?.items) ? details.items
    : Array.isArray(details?.data?.items) ? details.data.items
    : [];
  const total = items.reduce((sum, item) => sum + Number(item.amount || 0), 0);

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-box" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>Game Details</h2>
          <button className="modal-close" onClick={onClose}>✕</button>
        </div>
        <div className="modal-body">
          {loading ? (
            <p className="modal-loading">Loading…</p>
          ) : (
            <>
              <div className="game-detail-meta">
                <div className="game-detail-meta-row">
                  <span className="detail-label">Game</span>
                  <span className="detail-value">{shiftName}</span>
                </div>
                <div className="game-detail-meta-row">
                  <span className="detail-label">Date</span>
                  <span className="detail-value">{date}</span>
                </div>
              </div>

              {items.length > 0 ? (
                <table className="game-items-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Number</th>
                      <th>Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    {items.map((item, i) => (
                      <tr key={i}>
                        <td>{i + 1}</td>
                        <td>{item.number ?? '--'}</td>
                        <td>RS {formatMoney(item.amount)}</td>
                      </tr>
                    ))}
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colSpan={2}>Total</td>
                      <td>RS {formatMoney(total)}</td>
                    </tr>
                  </tfoot>
                </table>
              ) : (
                <p className="modal-loading">No number entries found.</p>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
}

export default function GameHistory() {
  const [transactions, setTransactions] = useState([]);
  const [viewing, setViewing] = useState(null);
  const [deletingId, setDeletingId] = useState(null);

  useEffect(() => {
    api.transactions().then((result) => {
      if (result.success) setTransactions(result.transactions || []);
    });
  }, []);

  async function handleDelete(transaction) {
    if (!window.confirm(`Delete this game entry (${transaction.shift_name || 'Market'})?`)) return;
    setDeletingId(transaction.id);
    const result = await api.deleteTransaction(transaction.id);
    if (result.success) {
      setTransactions((prev) => prev.filter((t) => t.id !== transaction.id));
    } else {
      alert(result.message || 'Failed to delete game.');
    }
    setDeletingId(null);
  }

  return (
    <div className="premium-page">
      <section className="premium-section">
        <div className="premium-title-row">
          <div>
            <span className="eyebrow">Played Games</span>
            <h1>Game History</h1>
            <p className="title-subline">{transactions.length} entries</p>
          </div>
        </div>

        <div className="premium-card-list">
          {transactions.map((transaction) => (
            <article className="premium-record-card" key={transaction.id}>
              <div>
                <h3>{transaction.shift_name || 'Market'}</h3>
                <p>{transaction.display_date || formatDate(transaction.t_date || transaction.dateoftrn)}</p>
              </div>
              <div className="record-amount">
                <b>RS {formatMoney(transaction.total_amount)}</b>
                <StatusBadge
                  open={String(transaction.status || '').toLowerCase() !== 'loss'}
                  label={transaction.status || 'Submitted'}
                />
              </div>
              <div className="record-actions">
                <button
                  className="action-btn view-btn"
                  onClick={() => setViewing(transaction)}
                >
                  View
                </button>
                {transaction.can_delete ? (
                  <button
                    className="action-btn delete-btn"
                    onClick={() => handleDelete(transaction)}
                    disabled={deletingId === transaction.id}
                  >
                    {deletingId === transaction.id ? '…' : 'Delete'}
                  </button>
                ) : null}
              </div>
            </article>
          ))}
        </div>

        {!transactions.length && <EmptyState title="No game history found." />}
      </section>

      {viewing && (
        <GameDetailModal
          transaction={viewing}
          onClose={() => setViewing(null)}
        />
      )}
    </div>
  );
}
