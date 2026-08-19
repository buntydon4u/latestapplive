import { useEffect, useMemo, useState } from 'react';
import { api } from '../lib/api.js';
import { EmptyState, LoadingState, formatMoney } from '../components/DashboardLayout.jsx';
import { useAuth } from '../context/AuthContext.jsx';

function pad(value) {
  return String(value).padStart(2, '0');
}

function toIsoDate(value) {
  if (!value) return '';

  const text = String(value).trim();
  if (/^\d{4}-\d{2}-\d{2}/.test(text)) {
    return text.slice(0, 10);
  }

  const legacy = text.match(/^(\d{2})-(\d{2})-(\d{4})$/);
  if (legacy) {
    return `${legacy[3]}-${legacy[2]}-${legacy[1]}`;
  }

  const parsed = new Date(text);
  if (Number.isNaN(parsed.getTime())) return '';
  return `${parsed.getFullYear()}-${pad(parsed.getMonth() + 1)}-${pad(parsed.getDate())}`;
}

function displayDate(value) {
  const iso = toIsoDate(value);
  if (!iso) return '--';

  const parsed = new Date(`${iso}T00:00:00`);
  if (Number.isNaN(parsed.getTime())) return String(value || '--');

  return parsed.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).replace(/^(\d{2} \w{3}) (\d{4})$/, '$1, $2');
}

function readQuery() {
  const params = new URLSearchParams(window.location.search);
  return {
    ledger_id: params.get('ledger_id') || '',
    date: params.get('date') || '',
    master: params.get('master') || ''
  };
}

function pickName(...values) {
  for (const value of values) {
    if (value !== undefined && value !== null && String(value).trim()) {
      return String(value).trim();
    }
  }
  return '-';
}

export default function HisabTillDate({ onNavigate }) {
  const { user } = useAuth();
  const [query, setQuery] = useState(readQuery);
  const [report, setReport] = useState(null);
  const [loading, setLoading] = useState(true);
  const [notice, setNotice] = useState('');

  useEffect(() => {
    let active = true;

    async function load() {
      const nextQuery = readQuery();
      setQuery(nextQuery);
      setLoading(true);
      setNotice('');

      try {
        const result = await api.hisabTillDateReport({
          ledger_id: nextQuery.ledger_id || user?.id || '',
          date: nextQuery.date,
          master: nextQuery.master || user?.updated_by || ''
        });

        if (!active) return;

        if (result.success) {
          setReport(result.report || result.data || {});
        } else {
          setReport(null);
          setNotice(result.error || 'Unable to load hisab report.');
        }
      } catch (error) {
        if (active) {
          setReport(null);
          setNotice(error.message || 'Unable to load hisab report.');
        }
      } finally {
        if (active) setLoading(false);
      }
    }

    load();
    return () => {
      active = false;
    };
  }, [user?.id, user?.updated_by]);

  useEffect(() => {
    function syncFromLocation() {
      setQuery(readQuery());
    }

    window.addEventListener('popstate', syncFromLocation);
    return () => window.removeEventListener('popstate', syncFromLocation);
  }, []);

  const rows = report?.rows || [];
  const summary = report?.summary || {};
  const ledger = report?.ledger || {};
  const partyName = pickName(
    ledger.ledger_name,
    ledger.name,
    user?.ledger_name,
    user?.name,
    user?.username
  );
  const selectedDate = query.date || report?.selected_date || '';
  const selectedDateLabel = displayDate(selectedDate);
  const selectedHisab = report?.selected_hisab || null;
  const selectedHisabValue = Number(selectedHisab?.today_hisab ?? selectedHisab?.final_hisab ?? 0);

  const tableRows = useMemo(() => {
    return rows.map((row, index) => {
      const rowType = String(row.type || '').trim();
      const amount = Number(row.amount || 0);
      const delta = Number(row.delta || 0);
      const debit = delta > 0 ? delta : 0;
      const credit = delta < 0 ? Math.abs(delta) : 0;
      const rowDate = row.date || row.t_date || row.created_date || report?.selected_date || '';

      return {
        id: row.id || `${rowType}-${index}`,
        date: displayDate(rowDate),
        khaber: row.khaber || row.shift_name || row.label || rowType || '--',
        game: row.label || row.shift_name || rowType || '--',
        total: amount,
        debit,
        credit,
        type: rowType
      };
    });
  }, [report?.selected_date, rows]);

  const totals = useMemo(() => {
    return tableRows.reduce((acc, row) => {
      acc.total += Number(row.total || 0);
      acc.debit += Number(row.debit || 0);
      acc.credit += Number(row.credit || 0);
      return acc;
    }, { total: 0, debit: 0, credit: 0 });
  }, [tableRows]);

  const commission = Number(summary.transaction_total || totals.total) * 0.1;
  const pattiAmount = Number(summary.net_movement ?? (totals.total - totals.credit));
  const finalTotal = Number(summary.running_total ?? (totals.total - totals.credit));
  const closing = Number.isFinite(selectedHisabValue) ? selectedHisabValue : finalTotal;

  function backToHisab() {
    if (typeof onNavigate === 'function') {
      onNavigate('hisab');
      return;
    }

    window.history.pushState({ page: 'hisab' }, '', '/reports/hisab');
    window.dispatchEvent(new PopStateEvent('popstate'));
  }

  if (loading) {
    return <LoadingState label="Loading hisab report..." />;
  }

  return (
    <div className="premium-page hisab-report-page">
      <section className="section-heading inline hisab-report-heading">
        <div>
          <span className="eyebrow">Reports</span>
          <h1>Hisab till date</h1>
          <p className="title-subline">
            Native in-app report for {partyName} · {selectedDateLabel}
          </p>
        </div>
        <button className="secondary-button" onClick={backToHisab} type="button">
          Back to Hisab List
        </button>
      </section>

      {notice ? <div className="notice full hisab-notice">{notice}</div> : null}

      <section className="panel hisab-hero-panel">
        <div className="hisab-hero-copy">
          <span className="eyebrow">PartyName</span>
          <h2>{partyName}</h2>
          <p>Detail Of Date</p>
        </div>
        <div className="hisab-hero-date">
          <span>{selectedDateLabel}</span>
          <small>Selected master: {query.master || ledger.updated_by || user?.updated_by || '-'}</small>
        </div>
      </section>

      <section className="panel hisab-report-table-panel">
        <div className="hisab-report-table-wrap">
          <table className="hisab-report-table">
            <thead>
              <tr>
                <th colSpan="2">PartyName</th>
                <th colSpan="3">{partyName}</th>
              </tr>
              <tr>
                <th colSpan="2">Detail Of Date</th>
                <th colSpan="3">{selectedDateLabel}</th>
              </tr>
              <tr>
                <th>Khaber</th>
                <th>Game</th>
                <th>Total</th>
                <th>D-Amount</th>
                <th>A-Amount</th>
              </tr>
            </thead>
            <tbody>
              {tableRows.map((row) => (
                <tr key={row.id} className={row.type === 'P/L' ? 'hisab-row-pl' : 'hisab-row-transaction'}>
                  <td data-label="Khaber">{row.khaber}</td>
                  <td data-label="Game">{row.game}</td>
                  <td data-label="Total" className="hisab-amount-cell">{formatMoney(row.total)}</td>
                  <td data-label="D-Amount" className="hisab-amount-cell">{row.debit ? formatMoney(row.debit) : '0'}</td>
                  <td data-label="A-Amount" className="hisab-amount-cell">{row.credit ? formatMoney(row.credit) : '0'}</td>
                </tr>
              ))}

              <tr className="hisab-total-row">
                <td colSpan="2" className="hisab-total-label">Totals</td>
                <td className="hisab-amount-cell">{formatMoney(totals.total)}</td>
                <td className="hisab-amount-cell">{formatMoney(totals.debit)}</td>
                <td className="hisab-amount-cell">{formatMoney(totals.credit)}</td>
              </tr>
            </tbody>
          </table>
        </div>

        {!tableRows.length ? (
          <EmptyState
            title="No ledger rows found."
            detail="There are no transactions or hisab rows available for the selected date."
          />
        ) : null}
      </section>

      <section className="hisab-summary-grid">
        <article className="hisab-summary-card">
          <span>Commission</span>
          <b>{formatMoney(commission)}</b>
        </article>
        <article className="hisab-summary-card">
          <span>D-Amount</span>
          <b>{formatMoney(summary.transaction_total ?? totals.total)}</b>
        </article>
        <article className="hisab-summary-card">
          <span>A-Amount</span>
          <b>{formatMoney(summary.hisab_total ?? totals.credit)}</b>
        </article>
        <article className="hisab-summary-card">
          <span>Total</span>
          <b>{formatMoney(summary.transaction_total ?? totals.total)}</b>
        </article>
        <article className="hisab-summary-card">
          <span>Patti AMT.</span>
          <b>{formatMoney(pattiAmount)}</b>
        </article>
        <article className="hisab-summary-card">
          <span>Kisht</span>
          <b>{formatMoney(summary.hisab_total ?? totals.credit)}</b>
        </article>
        <article className="hisab-summary-card is-accent">
          <span>Final Total</span>
          <b>{formatMoney(finalTotal)}</b>
        </article>
        <article className="hisab-summary-card is-good">
          <span>Closing</span>
          <b>{formatMoney(closing)}</b>
        </article>
      </section>
    </div>
  );
}
