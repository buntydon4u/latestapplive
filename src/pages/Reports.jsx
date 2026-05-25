import { useEffect, useMemo, useState } from 'react';
import { api } from '../lib/api.js';
import { useAuth } from '../context/AuthContext.jsx';

function toInputDate(displayDate) {
  const [day, month, year] = String(displayDate || '').split('-');
  return year && month && day ? `${year}-${month}-${day}` : '';
}

function displayDate(value) {
  const parsed = new Date(toInputDate(value) || value);
  if (Number.isNaN(parsed.getTime())) return value;
  return parsed.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).replace(/^(\d{2} \w{3}) (\d{4})$/, '$1, $2');
}

function monthStatementRange() {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
  const fmt = (date) => date.toISOString().slice(0, 10);
  return { start: fmt(start), end: fmt(now) };
}

export default function Reports({ initialView = 'hisab' }) {
  const { user } = useAuth();
  const [hisabs, setHisabs] = useState([]);
  const [viewer, setViewer] = useState(null);

  const statementUrl = useMemo(() => {
    const range = monthStatementRange();
    return `https://new.555xch.pro/app_statement/${user?.id}?start_date=${range.start}&end_date=${range.end}`;
  }, [user]);

  useEffect(() => {
    api.hisabs().then((result) => {
      if (result.success) setHisabs(result.hisabs || []);
    });
  }, []);

  useEffect(() => {
    if (initialView === 'statement') {
      setViewer({ title: 'Statement', url: statementUrl });
    } else {
      setViewer(null);
    }
  }, [initialView, statementUrl]);

  function openHisab(hisab) {
    const master = encodeURIComponent(hisab.updated_by || user.updated_by || '');
    const date = encodeURIComponent(hisab.date);
    setViewer({
      title: `Hisab ${displayDate(hisab.date)}`,
      url: `https://new.555xch.pro/ledger_till_date_reports_app?ledger_id=${user.id}&date=${date}&master=${master}`
    });
  }

  return (
    <div className="page-stack reports-layout">
      <section className="section-heading inline">
        <div>
          <span className="eyebrow">Reports</span>
          <h1>{initialView === 'statement' ? 'Statement' : 'Hisab'}</h1>
        </div>
        <button className="secondary-button" onClick={() => setViewer({ title: 'Statement', url: statementUrl })}>Statement</button>
      </section>

      {initialView === 'hisab' ? (
        <section className="hisab-card">
          <table className="hisab-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              {hisabs.map((hisab) => (
                <tr key={hisab.date}>
                  <td data-label={displayDate(hisab.date)}>{displayDate(hisab.date)}</td>
                  <td data-label="Action">
                    <button className="badge-button" onClick={() => openHisab(hisab)}>View Hisab</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          {!hisabs.length ? <p className="empty-state">No hisab rows found.</p> : null}
        </section>
      ) : null}

      <section className="panel iframe-panel">
        <div className="panel-title">
          <h3>{viewer?.title || (initialView === 'statement' ? 'Statement' : 'Hisab viewer')}</h3>
          {viewer ? <a className="ghost-button" href={viewer.url} target="_blank" rel="noreferrer">Open</a> : null}
        </div>
        {viewer ? <iframe title={viewer.title} src={viewer.url} /> : <p className="empty-state">Click View Hisab to load the portal report.</p>}
      </section>
    </div>
  );
}
