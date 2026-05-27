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
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
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

export default function Home({ onNavigate, onPlayShift }) {
  const { user, balance, refreshBalance } = useAuth();
  const [dashboardData, setDashboardData] = useState({
    shifts: [],
    transactions: [],
    hisabs: [],
    parties: [],
    ledger: null
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let alive = true;
    async function loadDashboard() {
      const requests = [
        api.shifts(),
        api.transactions(),
        api.hisabs(),
        api.parties(),
        refreshBalance(),
        user?.id ? api.ledger(user.id) : Promise.resolve({ success: false })
      ];
      const [shiftResult, transactionResult, hisabResult, partyResult, , ledgerResult] = await Promise.all(requests);
      if (!alive) return;
      setDashboardData({
        shifts: shiftResult.success ? (shiftResult.shifts || []) : [],
        transactions: transactionResult.success ? (transactionResult.transactions || []) : [],
        hisabs: hisabResult.success ? (hisabResult.hisabs || []) : [],
        parties: partyResult.success ? (partyResult.parties || []) : [],
        ledger: ledgerResult.success ? ledgerResult.data || ledgerResult : null
      });
      setLoading(false);
    }

    loadDashboard().catch(() => {
      if (alive) setLoading(false);
    });
    return () => { alive = false; };
  }, [refreshBalance, user?.id]);

  const markets = useMemo(() => {
    return dashboardData.shifts
      .map(normalizeMarket)
      .sort((left, right) => left.sortValue - right.sortValue || left.name.localeCompare(right.name));
  }, [dashboardData.shifts]);

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

  return (
    <div className="premium-page dashboard-page">
      <BalanceCard balance={balance} rate={gameRate} />

      <section className="dashboard-api-summary">
        <article>
          <b>{markets.length}</b>
          <span>Total Shifts</span>
        </article>
        <article>
          <b>{dashboardData.parties.length}</b>
          <span>Parties</span>
        </article>
        <article>
          <b>{dashboardData.transactions.length}</b>
          <span>Entries</span>
        </article>
        <article>
          <b>{dashboardData.hisabs.length}</b>
          <span>Hisabs</span>
        </article>
      </section>

      <section className="quick-actions-row">
        <PremiumButton className="quick-card trophy-card" onClick={() => onNavigate('history')}>
          <span>♛</span>
          <b>Top Winner</b>
        </PremiumButton>
        <PremiumButton className="quick-card green-card" onClick={() => window.open('https://wa.me/', '_blank', 'noopener,noreferrer')}>
          <span>?</span>
          <b>Help</b>
        </PremiumButton>
        <PremiumButton className="quick-card green-card" onClick={() => onNavigate('wallet')}>
          <span>+</span>
          <b>Add Money</b>
        </PremiumButton>
      </section>

      <section className="live-results-section">
        <h2>Live Results</h2>
        {loading ? <LoadingState label="Loading markets..." /> : null}
        {!loading && markets.length ? (
          <div className="market-list">
            {markets.map((market) => (
              <GameMarketCard
                key={market.id}
                market={market}
                transaction={transactionByShift.get(String(market.id))}
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
    </div>
  );
}
