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

  const slashDate = text.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  if (slashDate) {
    return `${slashDate[3]}-${slashDate[2]}-${slashDate[1]}`;
  }

  const shortYear = text.match(/^(\d{2})-(\d{2})-(\d{2})$/);
  if (shortYear) {
    const year = Number(shortYear[3]) >= 70 ? `19${shortYear[3]}` : `20${shortYear[3]}`;
    return `${year}-${shortYear[2]}-${shortYear[1]}`;
  }

  const parsed = new Date(text.includes('/') ? text.replace(/\//g, '-') : text);
  const slashDate = text.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  if (slashDate) {
    return `${slashDate[3]}-${slashDate[2]}-${slashDate[1]}`;
  }

  const shortYear = text.match(/^(\d{2})-(\d{2})-(\d{2})$/);
  if (shortYear) {
    const year = Number(shortYear[3]) >= 70 ? `19${shortYear[3]}` : `20${shortYear[3]}`;
    return `${year}-${shortYear[2]}-${shortYear[1]}`;
  }

  const parsed = new Date(text.includes('/') ? text.replace(/\//g, '-') : text);
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

  if (!params.toString() && window.history.state?.search) {
    const stateParams = window.history.state.search instanceof URLSearchParams
      ? window.history.state.search
      : new URLSearchParams(String(window.history.state.search));
    stateParams.forEach((value, key) => params.set(key, value));
  }

  const date = params.get('date')
    || params.get('selected_date')
    || params.get('selectedDate')
    || params.get('hisab_date')
    || '';

  return {
    ledger_id: params.get('ledger_id') || '',
    date,
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

function toAmount(value) {
  const amount = Number(String(value ?? 0).replace(/,/g, ''));
  return Number.isFinite(amount) ? amount : 0;
}

function pickValue(row, keys, fallback = '--') {
  for (const key of keys) {
    const value = row?.[key];
    if (value !== undefined && value !== null && String(value).trim() !== '') {
      return String(value).trim();
    }
  }
  return fallback;
}

function normalizeLedgerRow(row, index, kind, fallbackDate) {
  // Desktop ledger_till_date_reports displays the transaction-derived finalsum.
  const total = toAmount(
    row?.finalsum
      ?? row?.tamnt
      ?? row?.Total_Number_amount
      ?? row?.total
      ?? row?.amount
      ?? 0
  );
  // Desktop ledger_till_date_reports displays the transaction-derived finalsum.
  const transactionTotal = toAmount(row?.finalsum);
  const total = transactionTotal || toAmount(
    row?.Total_Number_amount
      ?? row?.tamnt
      ?? row?.total
      ?? row?.amount
      ?? 0
  );
  const debit = toAmount(row?.oamnt ?? row?.debit ?? 0);
  const credit = toAmount(row?.akmnt ?? row?.credit ?? 0);
  const date = row?.Date || row?.date || row?.opendate || fallbackDate || '';

  return {
    id: `${kind}-${row?.id || row?.number || row?.shift_id || index}`,
    number: pickValue(row, ['number', 'khaber', 'PartyId', 'party_id']),
    game: pickValue(row, ['shift_name', 'game_name', 'game', 'PartyName', 'ledger_name']),
    total,
    debit,
    credit,
    rate: toAmount(row?.pattiperc ?? row?.commission ?? 0),
    date,
    label: pickValue(row, ['ledger_name', 'PartyName', 'game_name', 'shift_name'], '-'),
    source: kind
  };
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
        const reportDate = toIsoDate(nextQuery.date);
        if (!reportDate) {
          setReport(null);
          setNotice('Selected hisab date is missing. Please return to the hisab list and select a date.');
          setLoading(false);
          return;
        }

        const reportDate = toIsoDate(nextQuery.date);
        if (!reportDate) {
          setReport(null);
          setNotice('Selected hisab date is missing. Please return to the hisab list and select a date.');
          setLoading(false);
          return;
        }

        const result = await api.ledgerTillDateReport({
          ledger_id: nextQuery.ledger_id || user?.id || '',
          date: reportDate,
          master: nextQuery.master || user?.updated_by || ''
        });

        if (!active) return;

        if (result.success) {
          setReport(result);
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
  }, [query.date, query.ledger_id, query.master, user?.id, user?.updated_by]);
  }, [query.date, query.ledger_id, query.master, user?.id, user?.updated_by]);

  useEffect(() => {
    function syncFromLocation() {
      setQuery(readQuery());
    }

    window.addEventListener('popstate', syncFromLocation);
    return () => window.removeEventListener('popstate', syncFromLocation);
  }, []);

  const ledger = report?.report?.ledger || report?.ledger || {};
  const firstTodayRow = Array.isArray(report?.today) ? report.today[0] || {} : {};
  const firstTodayRow = Array.isArray(report?.today) ? report.today[0] || {} : {};
  const partyName = pickName(
    ledger.ledger_name,
    ledger.name,
    firstTodayRow.ledger_name,
    firstTodayRow.ledger_name,
    user?.ledger_name,
    user?.name,
    user?.username
  );

  const selectedDate = query.date || report?.report?.selected_date || report?.selected_date || '';
  const selectedDateLabel = displayDate(selectedDate);

  const todayRows = useMemo(() => {
    const rows = Array.isArray(report?.today) ? report.today : [];
    return rows.map((row, index) => normalizeLedgerRow(row, index, 'today', selectedDate));
  }, [report, selectedDate]);

  const todayTotals = useMemo(() => {
    return todayRows.reduce((acc, row) => {
      acc.total += Number(row.total || 0);
      acc.debit += Number(row.debit || 0);
      acc.credit += Number(row.credit || 0);
      return acc;
    }, { total: 0, debit: 0, credit: 0 });
  }, [todayRows]);

  const summary = report?.summary || {};
  const totalToday = toAmount(summary.total_today ?? todayTotals.total);
  const dAmount = toAmount(summary.total_d ?? todayTotals.debit);
  const aAmount = toAmount(summary.total_a ?? todayTotals.credit);
  const commissionRate = todayRows[0]?.rate || toAmount(summary.commission_rate || 0);
  const commission = Number((todayTotals.total * commissionRate) / 100);
  const netBalance = Number(report?.summary?.running_total ?? (todayTotals.total - todayTotals.debit + todayTotals.credit));
  const opening = toAmount(
    summary.opening
      ?? summary.netbalance
      ?? summary.net_balance
      ?? netBalance
  );
  const closing = Number(report?.summary?.closing ?? netBalance);

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
            Native in-app report for {partyName} - {selectedDateLabel}
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

      <section className="hisab-summary-grid">
        <article className="hisab-summary-card">
          <span>Commission</span>
          <b>{formatMoney(commission)}</b>
        </article>
        <article className="hisab-summary-card">
          <span>Total Today</span>
          <b>{formatMoney(totalToday)}</b>
        </article>
        <article className="hisab-summary-card">
          <span>D-Amount</span>
          <b>{formatMoney(dAmount)}</b>
        </article>
        <article className="hisab-summary-card">
          <span>A-Amount</span>
          <b>{formatMoney(aAmount)}</b>
        </article>
        <article className="hisab-summary-card is-accent">
          <span>Net Balance</span>
          <b>{formatMoney(opening)}</b>
        </article>
        <article className="hisab-summary-card is-good">
          <span>Closing</span>
          <b>{formatMoney(closing)}</b>
        </article>
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
              {todayRows.map((row) => (
                <tr key={row.id} className="hisab-row-transaction">
                  <td data-label="Khaber">{row.number}</td>
                  <td data-label="Game">{row.game}</td>
                  <td data-label="Total" className="hisab-amount-cell">{formatMoney(row.total)}</td>
                  <td data-label="D-Amount" className="hisab-amount-cell">{row.debit ? formatMoney(row.debit) : '0'}</td>
                  <td data-label="A-Amount" className="hisab-amount-cell">{row.credit ? formatMoney(row.credit) : '0'}</td>
                </tr>
              ))}

              <tr className="hisab-total-row">
                <td colSpan="2" className="hisab-total-label">Totals</td>
                <td className="hisab-amount-cell">{formatMoney(todayTotals.total)}</td>
                <td className="hisab-amount-cell">{formatMoney(summary.total_d ?? todayTotals.debit)}</td>
                <td className="hisab-amount-cell">{formatMoney(summary.total_a ?? todayTotals.credit)}</td>
                <td className="hisab-amount-cell">{formatMoney(todayTotals.total || summary.total_today || 0)}</td>
                <td className="hisab-amount-cell">{formatMoney(summary.total_d ?? todayTotals.debit)}</td>
                <td className="hisab-amount-cell">{formatMoney(summary.total_a ?? todayTotals.credit)}</td>
              </tr>
            </tbody>
          </table>
        </div>

        {!todayRows.length ? (
          <EmptyState
            title="No ledger rows found."
            detail="There are no transactions or hisab rows available for the selected date."
          />
        ) : null}
      </section>

    </div>
  );
}
