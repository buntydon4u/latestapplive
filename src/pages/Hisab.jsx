import { useEffect, useState } from 'react';
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

export default function Hisab() {
  const { user } = useAuth();
  const [hisabs, setHisabs] = useState([]);
  const [iframeUrl, setIframeUrl] = useState(null);
  const [iframeTitle, setIframeTitle] = useState('');

  useEffect(() => {
    api.hisabs().then((result) => {
      if (result.success) setHisabs(result.hisabs || []);
    });
  }, []);

  function openHisab(hisab) {
    const master = encodeURIComponent(hisab.updated_by || user.updated_by || '');
    const date = encodeURIComponent(hisab.date);
    setIframeTitle(`Hisab ${displayDate(hisab.date)}`);
    setIframeUrl(
      `https://new.555xch.pro/ledger_till_date_reports_app?ledger_id=${user.id}&date=${date}&master=${master}`
    );
  }

  if (iframeUrl) {
    return (
      <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
        <div style={{ padding: '8px 12px', borderBottom: '1px solid var(--border)' }}>
          <button className="ghost-button" onClick={() => setIframeUrl(null)}>← Back</button>
          <span style={{ marginLeft: 12, fontWeight: 600 }}>{iframeTitle}</span>
        </div>
        <iframe
          title={iframeTitle}
          src={iframeUrl}
          style={{ flex: 1, width: '100%', border: 'none' }}
        />
      </div>
    );
  }

  return (
    <div className="page-stack">
      <section className="section-heading">
        <span className="eyebrow">Reports</span>
        <h1>Hisab</h1>
      </section>

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
                <td>{displayDate(hisab.date)}</td>
                <td>
                  <button className="badge-button" onClick={() => openHisab(hisab)}>
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
