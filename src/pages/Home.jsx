import { useEffect, useMemo, useState } from 'react';
import { api } from '../lib/api.js';
import { BalanceCard, EmptyState, LoadingState, PremiumButton, StatusBadge, formatMoney } from '../components/DashboardLayout.jsx';
import { useAuth } from '../context/AuthContext.jsx';

function getShiftTime(shift, keys) {
  for (const key of keys) {
    if (shift?.[key]) return shift[key];
  }
  return '';
}

function formatTime(value) {
  if (!value) return '--';
  const raw = String(value).trim();
  if (/am|pm/i.test(raw)) return raw.toLowerCase();

  const date = new Date(raw);
  if (!Number.isNaN(date.getTime())) {
    return date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true }).toLowerCase();
  }

  const match = raw.match(/^(\d{1,2}):(\d{2})/);
  if (!match) return raw;
  const hours = Number(match[1]);
  const minutes = match[2];
  const suffix = hours >= 12 ? 'pm' : 'am';
  const hour12 = hours % 12 || 12;
  return `${String(hour12).padStart(2, '0')}:${minutes} ${suffix}`;
}

function normalizeMarket(shift) {
  const closeTime = getShiftTime(shift, ['app_time', 'close_time', 'closing_time', 'time_limit', 'end_time', 'time_limit_display']);
  const resultTime = getShiftTime(shift, ['super_admin', 'result_time', 'open_time', 'draw_time', 'resultTime']);
  const timestamp = Number(shift.time_limit_timestamp || shift.close_timestamp || 0);
  const isOpen = shift.isOpen ?? shift.is_open ?? !shift.expired;

  return {
    id: shift.id || shift.shift_id || shift.name,
    masterShiftId: shift.tbl_shift_id || shift.shift_id || shift.id || shift.name,
    name: shift.name || shift.shift_name || shift.market_name || 'Market',
    closeTime,
    resultTime,
    openDate: shift.open_date || shift.date || '',
    status: isOpen ? 'Betting Open' : 'Betting Closed',
    rate: shift.rate || shift.game_rate || 10,
    logo: shift.logo || '',
    isOpen: Boolean(isOpen),
    sortValue: timestamp || Date.parse(`1970-01-01 ${closeTime}`) || 0
  };
}

function formatDate(value) {
  if (!value) return '';
  const legacyDate = String(value).match(/^(\d{2})-(\d{2})-(\d{4})$/);
  if (legacyDate) {
    return new Date(`${legacyDate[3]}-${legacyDate[2]}-${legacyDate[1]}`).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
  }
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
}

function getResultValue(row) {
  return row.number || row.open_no || row.openno || row.open_number || row.result || row.result_number || row.today_hisab || row.total || '--';
}

function getResultDate(row) {
  // declared_date is set by backend from tbl_openno's own date, not shift schedule date
  return row.declared_date || row.t_date || row.date || row.date_parsed || '';
}

function getISTNow() {
  const now = new Date();
  const utc = now.getTime() + now.getTimezoneOffset() * 60000;
  return new Date(utc + 5.5 * 60 * 60000);
}

function getTodayISTString() {
  const ist = getISTNow();
  return `${ist.getFullYear()}-${String(ist.getMonth() + 1).padStart(2, '0')}-${String(ist.getDate()).padStart(2, '0')}`;
}

function isBeforeResetTime() {
  const ist = getISTNow();
  return ist.getHours() < 10;
}

function normalizeToISODate(raw) {
  if (!raw) return '';
  const s = String(raw).trim();
  // YYYY-MM-DD
  if (/^\d{4}-\d{2}-\d{2}/.test(s)) return s.slice(0, 10);
  // DD-MM-YYYY
  const dmy = s.match(/^(\d{2})-(\d{2})-(\d{4})/);
  if (dmy) return `${dmy[3]}-${dmy[2]}-${dmy[1]}`;
  // Try native parse
  const d = new Date(s);
  if (!Number.isNaN(d.getTime())) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  }
  return '';
}

function isResultFromToday(row) {
  const raw = getResultDate(row);
  const normalized = normalizeToISODate(raw);
  return normalized === getTodayISTString();
}

function parseTimeToMinutes(raw) {
  if (!raw) return null;
  const s = String(raw).trim();
  const match = s.match(/(\d{1,2}):(\d{2})(?::\d{2})?\s*(am|pm)?/i);
  if (!match) return null;
  let hours = Number(match[1]);
  const minutes = Number(match[2]);
  const meridiem = (match[3] || '').toLowerCase();
  if (meridiem === 'pm' && hours !== 12) hours += 12;
  if (meridiem === 'am' && hours === 12) hours = 0;
  return hours * 60 + minutes;
}

function isShiftClosedByTime(market, nowMinutes) {
  const closeMinutes = parseTimeToMinutes(market.closeTime);
  if (closeMinutes === null) return !market.isOpen;
  return nowMinutes >= closeMinutes;
}

function getResultSortTime(row) {
  const raw = row.open_time || row.result_time || row.close_time || row.shift_time || '';
  if (!raw) return Infinity;
  const match = String(raw).match(/(\d{1,2}):(\d{2})(?::(\d{2}))?\s*(am|pm)?/i);
  if (!match) return Infinity;
  let hours = Number(match[1]);
  const minutes = Number(match[2]);
  const meridiem = (match[4] || '').toLowerCase();
  if (meridiem === 'pm' && hours !== 12) hours += 12;
  if (meridiem === 'am' && hours === 12) hours = 0;
  return hours * 60 + minutes;
}

function GameMarketCard({ market, transaction, onPlay }) {
  return (
    <article className="game-market-card">
      <div className="market-emblem">
        {market.logo ? <img src={market.logo} alt="" /> : <span>{String(market.name).slice(0, 2).toUpperCase()}</span>}
      </div>
      <div className="market-copy">
        <h3>{market.name}</h3>
        {market.openDate ? <p>Shift Date: <b>{formatDate(market.openDate)}</b></p> : null}
        <p>Close Time: <b>{formatTime(market.closeTime)}</b></p>
        <p>Result Time: <b>{formatTime(market.resultTime)}</b></p>
        <div className="market-meta-row">
          <StatusBadge open={market.isOpen} label={market.status} />
          {transaction ? <span>Played RS {formatMoney(transaction.total_amount)}</span> : null}
        </div>
      </div>
      <PremiumButton className="play-button" disabled={!market.isOpen} onClick={onPlay}>
        <span>▶</span>
        Play
      </PremiumButton>
    </article>
  );
}

function DeclaredResultCard({ shift, result }) {
  const value = result ? getResultValue(result) : null;
  const hasResult = value && value !== '--';

  // Before 10am: show previous result if available
  // After 10am: force Awaiting unless today's fresh result is declared
  const afterReset = !isBeforeResetTime();
  const waiting = afterReset ? (!hasResult || !isResultFromToday(result)) : !hasResult;

  return (
    <article className="result-card">
      <div className="result-card-header">
        <h3>{shift.name}</h3>
      </div>
      <div className="result-card-body">
        {waiting
          ? <strong className="result-waiting">⏳ Awaiting</strong>
          : <strong>{value}</strong>
        }
      </div>
    </article>
  );
}

export default function Home({ onNavigate, onPlayShift }) {
  const { user, balance, refreshBalance } = useAuth();
  const [dashboardData, setDashboardData] = useState({
    shifts: [],
    transactions: [],
    declaredResults: [],
    ledger: null
  });
  const [loading, setLoading] = useState(true);
  const [nowMinutes, setNowMinutes] = useState(() => {
    const ist = getISTNow();
    return ist.getHours() * 60 + ist.getMinutes();
  });

  useEffect(() => {
    const tick = () => {
      const ist = getISTNow();
      setNowMinutes(ist.getHours() * 60 + ist.getMinutes());
    };
    const id = setInterval(tick, 60000);
    return () => clearInterval(id);
  }, []);

  useEffect(() => {
    let alive = true;
    async function loadDashboard() {
      const requests = [
        api.shifts(),
        api.transactions(),
        api.declaredResults(6).catch(() => ({ success: false, results: [] })),
        refreshBalance(),
        user?.id ? api.ledger(user.id) : Promise.resolve({ success: false })
      ];
      const [shiftResult, transactionResult, declaredResult, , ledgerResult] = await Promise.all(requests);
      if (!alive) return;
      setDashboardData({
        shifts: shiftResult.success ? (shiftResult.shifts || []) : [],
        transactions: transactionResult.success ? (transactionResult.transactions || []) : [],
        declaredResults: declaredResult.success ? (declaredResult.results || []) : [],
        ledger: ledgerResult.success ? ledgerResult.data || ledgerResult : null
      });
      setLoading(false);
    }

    loadDashboard().catch(() => {
      if (alive) setLoading(false);
    });
    return () => { alive = false; };
  }, [refreshBalance, user?.id]);

  const allNormalizedShifts = useMemo(() => {
    return dashboardData.shifts
      .map(normalizeMarket)
      .sort((a, b) => a.sortValue - b.sortValue || a.name.localeCompare(b.name));
  }, [dashboardData.shifts]);

  const markets = useMemo(() => {
    return allNormalizedShifts.filter((market) => !isShiftClosedByTime(market, nowMinutes));
  }, [allNormalizedShifts, nowMinutes]);

  const closedShifts = useMemo(() => {
    return allNormalizedShifts.filter((market) => isShiftClosedByTime(market, nowMinutes));
  }, [allNormalizedShifts, nowMinutes]);

  const transactionByShift = useMemo(() => {
    const map = new Map();
    dashboardData.transactions.forEach((transaction) => {
      if (!map.has(String(transaction.shift_id))) {
        map.set(String(transaction.shift_id), transaction);
      }
    });
    return map;
  }, [dashboardData.transactions]);

  const gameRate = markets.find((market) => market.rate)?.rate || 10;

  // Build a lookup of declared results by shift name and master_shift_id
  const resultByShift = useMemo(() => {
    const map = new Map();
    dashboardData.declaredResults.forEach((r) => {
      const key = String(r.master_shift_id || r.shift_id || '');
      const nameKey = String(r.shift_name || '').toLowerCase();
      if (key) map.set(key, r);
      if (nameKey) map.set(nameKey, r);
    });
    return map;
  }, [dashboardData.declaredResults]);


  return (
    <div className="premium-page dashboard-page">
      <BalanceCard balance={balance} rate={gameRate} />

      <section className="dashboard-api-summary">
        <article>
          <b>{markets.length}</b>
          <span>Total Shifts</span>
        </article>
        <article>
          <b>{dashboardData.declaredResults.length}</b>
          <span>Results</span>
        </article>
      </section>

      <section className="live-results-section">
        <h2>Live Shifts</h2>
        {loading ? <LoadingState label="Loading markets..." /> : null}
        {!loading && markets.length ? (
          <div className="market-list">
            {markets.map((market) => (
              <GameMarketCard
                key={market.id}
                market={market}
                transaction={transactionByShift.get(String(market.masterShiftId))}
                onPlay={() => onPlayShift ? onPlayShift(market.id) : onNavigate('entry')}
              />
            ))}
          </div>
        ) : null}
        {!loading && !markets.length ? (
          <EmptyState
            title="No live markets available."
            detail="No shifts were returned by the existing shifts API."
          />
        ) : null}
      </section>

      {!loading && closedShifts.length > 0 ? (
        <section className="live-results-section">
          <h2>Live Results</h2>
          <div className="result-grid">
            {closedShifts.map((shift) => {
              const result = resultByShift.get(String(shift.masterShiftId))
                || resultByShift.get(shift.name.toLowerCase());
              return (
                <DeclaredResultCard key={shift.id} shift={shift} result={result} />
              );
            })}
          </div>
        </section>
      ) : null}
    </div>
  );
}
