import { useEffect, useState } from 'react';
import { api } from '../lib/api.js';
import { formatMoney } from '../components/DashboardLayout.jsx';
import { useAuth } from '../context/AuthContext.jsx';

function readHisabDate(hisab) {
  const directDate = hisab?.date || hisab?.Date || hisab?.hisab_date || hisab?.selected_date || hisab?.t_date || hisab?.final_date;
  if (directDate) return directDate;

  // Some legacy responses use an undocumented date key. Find a date-shaped value
  // rather than losing the selected date while building the detail URL.
  return Object.values(hisab || {}).find((value) =>
    /^(?:\d{4}[-/]\d{2}[-/]\d{2}|\d{2}[-/]\d{2}[-/]\d{4})/.test(String(value || '').trim())
  ) || '';
}

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

function toIsoDate(value) {
  const text = String(value || '').trim();
  if (/^\d{4}-\d{2}-\d{2}/.test(text)) return text.slice(0, 10);

  const legacy = text.match(/^(\d{2})-(\d{2})-(\d{4})$/);
  if (legacy) return `${legacy[3]}-${legacy[2]}-${legacy[1]}`;

  const slashDate = text.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  if (slashDate) return `${slashDate[3]}-${slashDate[2]}-${slashDate[1]}`;

  return '';
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
    const selectedDate = readHisabDate(hisab);
    const selectedDateIso = toIsoDate(hisab?.dateIso || selectedDate);
    const selectedDateValue = selectedDateIso || String(selectedDate || '').trim();
    const search = new URLSearchParams({
      ledger_id: String(user?.id || ''),
      date: selectedDateValue,
      selected_date: selectedDateValue,
      master: String(hisab.updated_by || user?.updated_by || '')
    });
    const searchString = search.toString();

    if (typeof onNavigate === 'function') {
      onNavigate('hisabView', { search: searchString });
      return;
    }

    window.history.pushState({ page: 'hisabView', search: searchString }, '', `/reports/hisab/view?${searchString}`);
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
              <tr key={readHisabDate(hisab)}>
                <td>{displayDate(readHisabDate(hisab))}</td>
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
