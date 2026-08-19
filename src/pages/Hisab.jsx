import { useEffect, useState } from 'react';
import { api } from '../lib/api.js';
import { formatMoney } from '../components/DashboardLayout.jsx';
import { useAuth } from '../context/AuthContext.jsx';

function displayDate(value) {
  if (!value) return '--';
  const text = String(value).trim();
  const normalized = /^\d{2}-\d{2}-\d{4}$/.test(text)
    ? `${text.slice(6, 10)}-${text.slice(3, 5)}-${text.slice(0, 2)}`
    : text.slice(0, 10);
  const date = new Date(`${normalized}T00:00:00`);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).replace(/^(\d{2} \w{3}) (\d{4})$/, '$1, $2');
}

export default function Hisab({ onNavigate }) {
  const { user } = useAuth();
  const [hisabs, setHisabs] = useState([]);
  const [notice, setNotice] = useState('');

  useEffect(() => {
    api.hisabs().then((result) => {
      if (result.success) setHisabs(result.hisabs || []);
      else setNotice(result.error || 'Unable to load hisab dates.');
    });
  }, []);

  function openHisab(hisab) {
    const search = new URLSearchParams({
      ledger_id: String(user?.id || ''),
      date: String(hisab.date || ''),
      master: String(hisab.updated_by || user?.updated_by || '')
    });

    if (typeof onNavigate === 'function') {
      onNavigate('hisabView', { search });
      return;
    }

    window.history.pushState({ page: 'hisabView' }, '', `/reports/hisab/view?${search.toString()}`);
    window.dispatchEvent(new PopStateEvent('popstate'));
  }

  return (
    <div className="page-stack">
      <section className="section-heading">
        <span className="eyebrow">Reports</span>
        <h1>Hisab</h1>
        <p className="title-subline">Open the dedicated till-date report page for each date.</p>
      </section>

      {notice ? <div className="notice full">{notice}</div> : null}

      <section className="hisab-card">
        <table className="hisab-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Hisab</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            {hisabs.map((hisab) => (
              <tr key={hisab.date}>
                <td>{displayDate(hisab.date)}</td>
                <td>{formatMoney(hisab.today_hisab ?? hisab.final_hisab ?? 0)}</td>
                <td>
                  <button className="badge-button" onClick={() => openHisab(hisab)} type="button">
                    View Hisab
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        {!hisabs.length ? <p className="empty-state">No hisab rows found.</p> : null}
      </section>
    </div>
  );
}
