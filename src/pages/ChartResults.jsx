import { useEffect, useState } from 'react';
import { api } from '../lib/api.js';
import { EmptyState, StatusBadge } from '../components/DashboardLayout.jsx';

function displayDate(value) {
  if (!value) return '--';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

export default function ChartResults() {
  const [hisabs, setHisabs] = useState([]);
  const [shifts, setShifts] = useState([]);
  const [transactions, setTransactions] = useState([]);

  useEffect(() => {
    Promise.all([api.hisabs(), api.shifts(), api.transactions()]).then(([hisabResult, shiftResult, transactionResult]) => {
      if (hisabResult.success) setHisabs(hisabResult.hisabs || []);
      if (shiftResult.success) setShifts(shiftResult.shifts || []);
      if (transactionResult.success) setTransactions(transactionResult.transactions || []);
    });
  }, []);

  return (
    <div className="premium-page">
      <section className="premium-section">
        <div className="premium-title-row">
          <div>
            <span className="eyebrow">Market Results</span>
            <h1>Chart</h1>
            <p className="title-subline">{hisabs.length} results · {shifts.length} shifts · {transactions.length} entries</p>
          </div>
        </div>
        <div className="result-grid">
          {hisabs.map((row, index) => (
            <article className="result-card" key={row.date || row.id}>
              <div>
                <h3>{row.shift_name || row.market_name || shifts[index % Math.max(shifts.length, 1)]?.name || 'Result'}</h3>
                <p>{displayDate(row.date || row.t_date)}</p>
              </div>
              <strong>{row.result || row.result_number || row.today_hisab || row.total || '--'}</strong>
              <StatusBadge open={Number(row.today_hisab || 0) >= 0} label="Declared" />
            </article>
          ))}
        </div>
        {!hisabs.length ? (
          <EmptyState title="No chart results found." detail="No hisab/result rows were returned by the existing API." />
        ) : null}
      </section>
    </div>
  );
}
