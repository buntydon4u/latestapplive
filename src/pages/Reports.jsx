import { useEffect, useState } from 'react';
import { api } from '../lib/api.js';
import { useAuth } from '../context/AuthContext.jsx';
import StatementReport from './StatementReport.jsx';

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

export default function Reports({ initialView = 'hisab' }) {
  const { user } = useAuth();
  const [hisabs, setHisabs] = useState([]);
  const [viewer, setViewer] = useState(null);

  useEffect(() => {
    api.hisabs().then((result) => {
      if (result.success) setHisabs(result.hisabs || []);
    });
  }, []);

  useEffect(() => {
    if (initialView === 'statement') {
      setViewer({ kind: 'statement', title: 'Statement' });
    } else {
      setViewer(null);
    }
  }, [initialView]);

  function openHisab(hisab) {
    const master = encodeURIComponent(hisab.updated_by || user.updated_by || '');
    const date = encodeURIComponent(hisab.date);
    setViewer({
      kind: 'iframe',
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
        <button className="secondary-button" onClick={() => setViewer({ kind: 'statement', title: 'Statement' })}>Statement</button>
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
          {viewer?.kind === 'iframe' ? <a className="ghost-button" href={viewer.url} target="_blank" rel="noreferrer">Open</a> : null}
        </div>
        {viewer?.kind === 'statement' ? (
          <StatementReport embedded />
        ) : viewer?.kind === 'iframe' ? (
          <iframe title={viewer.title} src={viewer.url} />
        ) : (
          <p className="empty-state">Click View Hisab to load the portal report.</p>
        )}
      </section>
    </div>
  );
}
