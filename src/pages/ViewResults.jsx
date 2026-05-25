import { useEffect, useState } from 'react';
import { api } from '../lib/api.js';
import { useAuth } from '../context/AuthContext.jsx';

function formatDisplayDate(value) {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).replace(/^(\d{2} \w{3}) (\d{4})$/, '$1, $2');
}

export default function ViewResults() {
  const { refreshBalance } = useAuth();
  const [transactions, setTransactions] = useState([]);
  const [selected, setSelected] = useState(null);
  const [details, setDetails] = useState(null);
  const [deleteId, setDeleteId] = useState(null);
  const [notice, setNotice] = useState('');

  async function load() {
    const result = await api.transactions();
    if (result.success) setTransactions(result.transactions || []);
  }

  useEffect(() => {
    load();
  }, []);

  async function openDetails(id) {
    setSelected(id);
    setDetails(null);
    const result = await api.transactionDetails(id);
    if (result.success) setDetails(result);
    else setNotice(result.error || 'Unable to load transaction.');
  }

  async function confirmDelete() {
    const result = await api.deleteTransaction(deleteId);
    setNotice(result.success ? 'Transaction deleted.' : (result.error || 'Delete failed.'));
    setDeleteId(null);
    if (result.success) {
      await load();
      await refreshBalance();
    }
  }

  return (
    <div className="page-stack">
      <section className="section-heading inline">
        <div>
          <span className="eyebrow">Past 30 days</span>
          <h1>View results</h1>
        </div>
        <button className="secondary-button" onClick={load}>Refresh</button>
      </section>

      {notice ? <div className="notice full">{notice}</div> : null}

      <section className="transaction-card">
        <div className="transaction-table-wrap">
          <table className="transaction-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Shift Name</th>
                <th>Amt</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              {transactions.map((transaction) => (
                <tr key={transaction.id}>
                  <td data-label="Date">{transaction.display_date || formatDisplayDate(transaction.t_date)}</td>
                  <td data-label="Shift Name">{transaction.shift_name}</td>
                  <td data-label="Amt">{transaction.total_amount}</td>
                  <td data-label="Action">
                    <div className="transaction-actions">
                      <button className="badge-button" onClick={() => openDetails(transaction.id)}>View</button>
                      {transaction.can_delete ? (
                        <button className="badge-button danger-badge" onClick={() => setDeleteId(transaction.id)}>Delete</button>
                      ) : null}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          {!transactions.length ? <p className="empty-state">No recent transactions found.</p> : null}
        </div>
      </section>

      {selected ? (
        <div className="drawer-backdrop" onClick={() => setSelected(null)}>
          <aside className="drawer" onClick={(event) => event.stopPropagation()}>
            <div className="panel-title">
              <h3>{details?.shift_name || 'Transaction'}</h3>
              <button className="icon-button" onClick={() => setSelected(null)}>×</button>
            </div>
            <small>{formatDisplayDate(details?.t_date)}</small>
            <div className="transaction-detail-table">
              <div className="detail-head">
                <b>Num</b>
                <b>Amt</b>
              </div>
              {details?.items?.map((item, index) => (
                <div key={`${item.number}-${index}`}>
                  <span>{item.number}</span>
                  <b>{item.amount}</b>
                </div>
              )) || <p className="empty-state">Loading...</p>}
              {details?.items?.length ? (
                <div className="detail-total">
                  <span>Total:</span>
                  <b>{details.items.reduce((sum, item) => sum + Number(item.amount || 0), 0)}</b>
                </div>
              ) : null}
            </div>
          </aside>
        </div>
      ) : null}

      {deleteId ? (
        <div className="modal-backdrop">
          <section className="modal">
            <h3>Delete transaction?</h3>
            <p>This will call the existing delete endpoint for transaction #{deleteId}.</p>
            <div className="modal-actions">
              <button className="secondary-button" onClick={() => setDeleteId(null)}>Cancel</button>
              <button className="danger-button" onClick={confirmDelete}>Delete</button>
            </div>
          </section>
        </div>
      ) : null}
    </div>
  );
}
