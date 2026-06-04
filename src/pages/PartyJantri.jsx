import { useEffect, useMemo, useState } from 'react';
import { EmptyState, LoadingState, formatMoney } from '../components/DashboardLayout.jsx';
import { useAuth } from '../context/AuthContext.jsx';
import { api, todayIso } from '../lib/api.js';

function formatDate(value) {
  if (!value) return '';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return String(value).slice(0, 10);
  return parsed.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).replace(/^(\d{2} \w{3}) (\d{4})$/, '$1, $2');
}

function shiftId(shift) {
  return String(shift.report_pid || shift.user_shift_timing_id || shift.id || '');
}

function shiftName(shift) {
  const name = shift.shift_name || shift.name || `Shift ${shift.tbl_shift_id || shiftId(shift)}`;
  const date = shift.user_shift_open_date || shift.open_date || shift.tbl_shift_open_date;
  return date ? `${name} - ${formatDate(date)}` : name;
}

function rowAmount(row) {
  return String(row.trn_amt || '')
    .split(',')
    .reduce((sum, amount) => sum + Number(amount || 0), 0);
}

export default function PartyJantri() {
  const { user } = useAuth();
  const [shifts, setShifts] = useState([]);
  const [filters, setFilters] = useState({ pid: '', date: todayIso() });
  const [rows, setRows] = useState([]);
  const [summary, setSummary] = useState({ totalAmount: 0, totalRows: 0 });
  const [loadingMeta, setLoadingMeta] = useState(true);
  const [loadingReport, setLoadingReport] = useState(false);
  const [notice, setNotice] = useState('');

  const selectedShift = useMemo(
    () => shifts.find((shift) => shiftId(shift) === String(filters.pid)),
    [filters.pid, shifts]
  );

  useEffect(() => {
    let active = true;
    setLoadingMeta(true);
    api.jantriPartyMeta()
      .then((result) => {
        if (!active) return;
        if (result.success) {
          setShifts(result.shifts || []);
        } else {
          setNotice(result.error || 'Unable to load Party Jantri filters.');
        }
      })
      .catch((error) => {
        if (active) setNotice(error.message || 'Unable to load Party Jantri filters.');
      })
      .finally(() => {
        if (active) setLoadingMeta(false);
      });

    return () => {
      active = false;
    };
  }, []);

  function updateFilter(field, value) {
    setFilters((current) => ({ ...current, [field]: value }));
  }

  function reset() {
    setFilters({ pid: '', date: todayIso() });
    setRows([]);
    setSummary({ totalAmount: 0, totalRows: 0 });
    setNotice('');
  }

  async function search() {
    setNotice('');
    if (!filters.pid || !filters.date) {
      setNotice('Select shift and date before searching.');
      return;
    }

    setLoadingReport(true);
    try {
      const result = await api.jantriPartyReport(filters);
      if (result.success) {
        setRows(result.rows);
        setSummary(result.summary);
      } else {
        setRows([]);
        setSummary({ totalAmount: 0, totalRows: 0 });
        setNotice(result.error || 'Unable to fetch Party Jantri report.');
      }
    } catch (error) {
      setRows([]);
      setSummary({ totalAmount: 0, totalRows: 0 });
      setNotice(error.message || 'Unable to fetch Party Jantri report.');
    } finally {
      setLoadingReport(false);
    }
  }

  return (
    <div className="page-stack party-jantri-page">
      <section className="section-heading inline">
        <div>
          <span className="eyebrow">Jantri</span>
          <h1>Party Jantri</h1>
        </div>
      </section>

      <section className="panel party-jantri-filters">
        <label>
          <span>Shift</span>
          <select value={filters.pid} onChange={(event) => updateFilter('pid', event.target.value)}>
            <option value="">Choose shift</option>
            {shifts.map((shift) => (
              <option key={`${shiftId(shift)}-${shift.tbl_shift_id}`} value={shiftId(shift)}>
                {shiftName(shift)}
              </option>
            ))}
          </select>
        </label>
        <label>
          <span>Date</span>
          <input type="date" value={filters.date} onChange={(event) => updateFilter('date', event.target.value)} />
        </label>
        <div className="party-jantri-actions">
          <button className="primary-button" onClick={search} disabled={loadingMeta || loadingReport}>Search</button>
          <button className="secondary-button" onClick={reset}>Reset</button>
        </div>
      </section>

      {notice ? <div className="notice full">{notice}</div> : null}

      <section className="party-jantri-summary">
        <article>
          <span>Total rows</span>
          <b>{summary.totalRows || rows.length}</b>
        </article>
        <article>
          <span>Total amount</span>
          <b>{formatMoney(summary.totalAmount || rows.reduce((sum, row) => sum + rowAmount(row), 0))}</b>
        </article>
        <article>
          <span>Party</span>
          <b>{user?.name || user?.ledger_name || user?.username || '-'}</b>
        </article>
        <article>
          <span>Shift / Date</span>
          <b>{selectedShift ? `${shiftName(selectedShift)} / ${formatDate(filters.date)}` : formatDate(filters.date)}</b>
        </article>
      </section>

      <section className="transaction-card party-jantri-card">
        {loadingMeta || loadingReport ? <LoadingState label={loadingMeta ? 'Loading filters...' : 'Loading report...'} /> : (
          <div className="transaction-table-wrap">
            <table className="transaction-table party-jantri-table">
              <colgroup>
                <col className="party-jantri-sno-col" />
                <col className="party-jantri-amount-col" />
                <col className="party-jantri-number-col" />
              </colgroup>
              <thead>
                <tr>
                  <th>S. No.</th>
                  <th>Amount</th>
                  <th>Number</th>
                </tr>
              </thead>
              <tbody>
                {rows.map((row, index) => (
                  <tr key={`${row.id}-${index}`}>
                    <td data-label="S. No.">{index + 1}</td>
                    <td data-label="Amount">{row.trn_amt}</td>
                    <td data-label="Number">{row.trnno}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            {!rows.length ? <EmptyState title="No transactions found." detail="Search with shift, party, and date to view Party Jantri rows." /> : null}
          </div>
        )}
      </section>
    </div>
  );
}
