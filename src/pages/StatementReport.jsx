import { useEffect, useMemo, useState } from 'react';
import { api } from '../lib/api.js';
import { EmptyState, LoadingState, formatMoney } from '../components/DashboardLayout.jsx';
import { useAuth } from '../context/AuthContext.jsx';

function pad(value) {
  return String(value).padStart(2, '0');
}

function defaultRange() {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth() - 1, 1);

  return {
    start: `${start.getFullYear()}-${pad(start.getMonth() + 1)}-${pad(start.getDate())}`,
    end: `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`
  };
}

function displayDate(value) {
  if (!value) return '-';
  const text = String(value).trim();
  const normalized = /^\d{2}-\d{2}-\d{4}$/.test(text)
    ? `${text.slice(6, 10)}-${text.slice(3, 5)}-${text.slice(0, 2)}`
    : text.slice(0, 10);
  const parsed = new Date(`${normalized}T00:00:00`);

  if (Number.isNaN(parsed.getTime())) return text;

  return parsed.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).replace(/^(\d{2} \w{3}) (\d{4})$/, '$1, $2');
}

function comparableDate(value) {
  if (!value) return '';
  const text = String(value).trim();
  if (/^\d{4}-\d{2}-\d{2}/.test(text)) return text.slice(0, 10);
  if (/^\d{2}-\d{2}-\d{4}$/.test(text)) return `${text.slice(6, 10)}-${text.slice(3, 5)}-${text.slice(0, 2)}`;

  const parsed = new Date(text);
  if (Number.isNaN(parsed.getTime())) return '';
  return `${parsed.getFullYear()}-${pad(parsed.getMonth() + 1)}-${pad(parsed.getDate())}`;
}

function toAmount(value) {
  const amount = Number(value ?? 0);
  return Number.isFinite(amount) ? amount : 0;
}

function formatStatementCell(value, { parens = false } = {}) {
  if (value === null || value === undefined || value === '') return '-';
  const money = formatMoney(value);
  return parens ? `(${money})` : money;
}

export default function StatementReport({ embedded = false }) {
  const { user } = useAuth();
  const [ledger, setLedger] = useState(null);
  const [transactions, setTransactions] = useState([]);
  const [hisabs, setHisabs] = useState([]);
  const [reportRows, setReportRows] = useState([]);
  const [draftRange, setDraftRange] = useState(defaultRange);
  const [range, setRange] = useState(defaultRange);
  const [loading, setLoading] = useState(true);
  const [notice, setNotice] = useState('');

  useEffect(() => {
    let active = true;

    async function load() {
      if (!user?.id) return;

      setLoading(true);
      setNotice('');

      try {
        const reportResult = await api.hisabTillDateReport({
          ledger_id: user.id,
          start_date: range.start,
          end_date: range.end
        }).catch(() => ({ success: false, rows: [], report: {} }));

        if (!active) return;

        if (reportResult.success) {
          setLedger(reportResult.report?.ledger || reportResult.ledger || null);
          setReportRows(Array.isArray(reportResult.rows) ? reportResult.rows : []);
          setTransactions([]);
          setHisabs([]);
          return;
        }

        const [ledgerResult, transactionResult, hisabResult] = await Promise.all([
          api.ledger(user.id).catch(() => ({ success: false })),
          api.transactions().catch(() => ({ success: false, transactions: [] })),
          api.hisabs().catch(() => ({ success: false, hisabs: [] }))
        ]);

        if (ledgerResult.success) {
          setLedger(ledgerResult.data || ledgerResult);
        }

        setTransactions(transactionResult.success ? (transactionResult.transactions || []) : []);
        setHisabs(hisabResult.success ? (hisabResult.hisabs || []) : []);
        setReportRows([]);

        if (!ledgerResult.success && !transactionResult.success && !hisabResult.success) {
          setNotice('Unable to load statement data.');
        }
      } catch (error) {
        if (active) {
          setNotice(error.message || 'Unable to load statement data.');
        }
      } finally {
        if (active) setLoading(false);
      }
    }

    load();
    return () => {
      active = false;
    };
  }, [range.end, range.start, user?.id]);

  const partyName = ledger?.ledger_name || user?.ledger_name || user?.name || user?.username || '-';
  const rangeLabel = `${displayDate(range.start)} - ${displayDate(range.end)}`;

  const tableRows = useMemo(() => {
    if (reportRows.length) {
      return reportRows;
    }

    const statementRows = [
      ...transactions
        .filter((row) => {
          const value = comparableDate(row.t_date || row.date || row.created_date || row.display_date);
          if (!value) return true;
          if (range.start && value < range.start) return false;
          if (range.end && value > range.end) return false;
          return true;
        })
        .map((row) => {
          const amount = toAmount(row.total_amount || row.amount);
          const ledgerLabel = user?.ledger_name || user?.username || user?.updated_by || 'User';

          return {
            id: `tx-${row.id}`,
            sortKey: `${comparableDate(row.t_date || row.display_date)} 00:00:00`,
            dateText: row.display_date || displayDate(row.t_date),
            deposit: '',
            withdraw: amount,
            pl: '',
            movement: -amount,
            flow: `${ledgerLabel} -> ${partyName}`,
            type: 'transaction'
          };
        }),
      ...hisabs
        .filter((row) => {
          const value = comparableDate(row.date || row.t_date || row.created_date);
          if (!value) return true;
          if (range.start && value < range.start) return false;
          if (range.end && value > range.end) return false;
          return true;
        })
        .map((row) => {
          const amount = toAmount(row.today_hisab ?? row.final_hisab);

          return {
            id: `pl-${row.date}`,
            sortKey: `${comparableDate(row.date)} 23:59:59`,
            dateText: `${displayDate(row.date)} 11:59 PM (P/L)`,
            deposit: '',
            withdraw: '',
            pl: amount,
            movement: amount < 0 ? Math.abs(amount) : -amount,
            flow: 'P/L Adjustment',
            type: 'pl'
          };
        })
    ]
      .sort((a, b) => a.sortKey.localeCompare(b.sortKey))
      .map((row) => row);

    let runningBalance = 0;
    return statementRows.map((row) => {
      runningBalance += row.movement || 0;
      return {
        ...row,
        balance: runningBalance
      };
    });
  }, [hisabs, partyName, range.end, range.start, reportRows, transactions, user?.ledger_name, user?.updated_by, user?.username]);

  function updateRange(field, value) {
    setDraftRange((current) => ({ ...current, [field]: value }));
  }

  function applyRange() {
    setRange(draftRange);
  }

  function resetRange() {
    const nextRange = defaultRange();
    setDraftRange(nextRange);
    setRange(nextRange);
  }

  return (
    <div className={`premium-page statement-page ${embedded ? 'is-embedded' : ''}`}>
      <section className="section-heading inline statement-heading">
        <div>
          <span className="eyebrow">Reports</span>
          <h1>Statement of {partyName}</h1>
          <p className="title-subline">{rangeLabel}</p>
        </div>
      </section>

      <section className="panel statement-toolbar statement-filters">
        <label>
          <span>From Date</span>
          <input type="date" value={draftRange.start} onChange={(event) => updateRange('start', event.target.value)} />
        </label>
        <label>
          <span>To Date</span>
          <input type="date" value={draftRange.end} onChange={(event) => updateRange('end', event.target.value)} />
        </label>
        <div className="statement-actions">
          <button className="primary-button statement-filter-button" onClick={applyRange} type="button">
            Filter
          </button>
          <button className="secondary-button statement-filter-button" onClick={resetRange} type="button">
            Reset
          </button>
        </div>
      </section>

      {notice ? <div className="notice full statement-notice">{notice}</div> : null}

      <section className="panel statement-table-shell">
        {loading ? (
          <LoadingState label="Loading statement..." />
        ) : tableRows.length ? (
          <div className="statement-table-wrap">
            <table className="statement-table">
              <thead>
                <tr>
                  <th>Date/Time</th>
                  <th>Deposit</th>
                  <th>Withdraw</th>
                  <th>P/L</th>
                  <th>Balance</th>
                  <th>From {'->'} To</th>
                </tr>
              </thead>
              <tbody>
                {tableRows.map((row) => (
                  <tr key={row.id} className={row.type === 'pl' ? 'statement-pl-row' : 'statement-tx-row'}>
                    <td data-label="Date/Time">{row.dateText}</td>
                    <td data-label="Deposit" className="statement-amount-cell">{formatStatementCell(row.deposit)}</td>
                    <td data-label="Withdraw" className="statement-amount-cell">{row.withdraw !== '' && row.withdraw !== null && row.withdraw !== undefined ? formatStatementCell(row.withdraw, { parens: true }) : '-'}</td>
                    <td data-label="P/L" className={row.type === 'pl' ? 'statement-pl-value' : ''}>
                      {row.pl !== '' && row.pl !== null && row.pl !== undefined ? formatStatementCell(row.pl) : '-'}
                    </td>
                    <td data-label="Balance" className={`statement-amount-cell statement-balance-cell ${Number(row.balance) < 0 ? 'is-negative' : ''}`}>
                      {row.balance !== '' && row.balance !== null && row.balance !== undefined ? formatStatementCell(row.balance) : '-'}
                    </td>
                    <td data-label="From -> To">{row.flow}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <EmptyState title="No records in range." detail="Adjust the date range to view statement rows." />
        )}
      </section>
    </div>
  );
}
