import { useEffect, useMemo, useRef, useState } from 'react';
import { api, todayDisplay, todayIso } from '../lib/api.js';
import { useAuth } from '../context/AuthContext.jsx';

const tabs = ['Num-Akhar', 'From-To', 'Cross', 'Jantri'];

function normalizeNumber(value) {
  return String(value || '').replace(/\D/g, '').slice(-2).padStart(2, '0');
}

function uniqueRows(rows) {
  const seen = new Set();
  return rows.filter((row) => {
    const key = `${row.number}-${row.amount}`;
    if (seen.has(key) || !row.number || !row.amount) return false;
    seen.add(key);
    return true;
  });
}

function parseNumAkhar(text) {
  const tokens = text.replace(/[,\n\r]+/g, ' ').split(/\s+/).filter(Boolean);
  const rows = [];

  for (let index = 0; index < tokens.length; index += 2) {
    const number = normalizeNumber(tokens[index]);
    const amount = Number(String(tokens[index + 1] || '').replace(/\D/g, ''));
    if (number && amount > 0) rows.push({ number, amount });
  }
  return uniqueRows(rows);
}

function buildRange(from, to, amount) {
  const start = Number(from);
  const end = Number(to);
  const amt = Number(amount);
  if (!Number.isFinite(start) || !Number.isFinite(end) || amt <= 0) return [];
  const min = Math.min(start, end);
  const max = Math.max(start, end);
  return Array.from({ length: max - min + 1 }, (_, offset) => ({
    number: normalizeNumber(min + offset),
    amount: amt
  }));
}

function buildCross(left, right, amount) {
  const amt = Number(amount);
  if (!left || !right || amt <= 0) return [];
  const rows = [];
  for (const first of left.replace(/\D/g, '')) {
    for (const second of right.replace(/\D/g, '')) {
      rows.push({ number: `${first}${second}`, amount: amt });
      rows.push({ number: `${second}${first}`, amount: amt });
    }
  }
  return uniqueRows(rows);
}

function EntryRows({ rows, setRows }) {
  const total = rows.reduce((sum, row) => sum + Number(row.amount || 0), 0);

  return (
    <section className="panel table-panel">
      <div className="panel-title">
        <h3>Prepared entries</h3>
        <b>{rows.length} / {total}</b>
      </div>
      <div className="entry-table">
        {rows.map((row, index) => (
          <div className="entry-row" key={`${row.number}-${index}`}>
            <input value={row.number} maxLength="2" onChange={(event) => setRows((current) => current.map((item, rowIndex) => rowIndex === index ? { ...item, number: normalizeNumber(event.target.value) } : item))} />
            <input value={row.amount} inputMode="numeric" onChange={(event) => setRows((current) => current.map((item, rowIndex) => rowIndex === index ? { ...item, amount: event.target.value.replace(/\D/g, '') } : item))} />
            <button className="icon-button danger" title="Delete row" onClick={() => setRows((current) => current.filter((_, rowIndex) => rowIndex !== index))}>×</button>
          </div>
        ))}
        {!rows.length ? <p className="empty-state">No entries prepared.</p> : null}
      </div>
    </section>
  );
}

function JantriGrid({ grid, setGrid }) {
  const refs = useRef([]);
  const numbers = useMemo(() => Array.from({ length: 100 }, (_, index) => String(index).padStart(2, '0')), []);
  const total = Object.values(grid).reduce((sum, value) => sum + Number(value || 0), 0);

  function update(number, value) {
    setGrid((current) => ({ ...current, [number]: value.replace(/\D/g, '') }));
  }

  function handleKeyDown(event, index) {
    if (event.key === 'Enter' || event.key === 'ArrowRight') {
      event.preventDefault();
      refs.current[index + 1]?.focus();
    } else if (event.key === 'ArrowLeft') {
      refs.current[index - 1]?.focus();
    }
  }

  return (
    <section className="panel jantri-panel">
      <div className="panel-title">
        <h3>Jantri table</h3>
        <b>Total {total}</b>
      </div>
      <div className="jantri-grid">
        {numbers.map((number, index) => (
          <label key={number} className="jantri-cell">
            <span>{number}</span>
            <input
              ref={(node) => { refs.current[index] = node; }}
              value={grid[number] || ''}
              inputMode="numeric"
              onChange={(event) => update(number, event.target.value)}
              onKeyDown={(event) => handleKeyDown(event, index)}
            />
          </label>
        ))}
      </div>
    </section>
  );
}

export default function DataEntry({ initialMode = tabs[0], initialShift = '' }) {
  const { user, refreshBalance } = useAuth();
  const [active, setActive] = useState(initialMode);
  const [shifts, setShifts] = useState([]);
  const [meta, setMeta] = useState({ shift: '' });
  const [text, setText] = useState('');
  const [range, setRange] = useState({ from: '', to: '', amount: '' });
  const [cross, setCross] = useState({ left: '', right: '', amount: '' });
  const [rows, setRows] = useState([]);
  const [grid, setGrid] = useState({});
  const [notice, setNotice] = useState('');
  const ledgerId = user?.id;
  const currentDateDisplay = todayDisplay();

  useEffect(() => {
    api.shifts().then((shiftResult) => {
      if (shiftResult.success) setShifts(shiftResult.shifts || []);
    });
  }, []);

  useEffect(() => {
    setActive(initialMode);
  }, [initialMode]);

  useEffect(() => {
    if (initialShift) {
      setMeta((current) => ({ ...current, shift: String(initialShift) }));
    }
  }, [initialShift]);

  useEffect(() => {
    const selected = shifts.find((shift) => String(shift.id) === String(meta.shift));
    window.dispatchEvent(new CustomEvent('shift-change', { detail: { deadline: selected?.time_limit_timestamp } }));
  }, [meta.shift, shifts]);

  function selectedShiftExpired() {
    return shifts.find((shift) => String(shift.id) === String(meta.shift))?.expired;
  }

  function addRows(newRows) {
    setRows((current) => uniqueRows([...current, ...newRows]));
  }

  async function submit() {
    setNotice('');
    if (!ledgerId || !meta.shift) {
      setNotice('Select shift before submitting.');
      return;
    }
    if (selectedShiftExpired()) {
      setNotice('Selected shift is expired.');
      return;
    }

    const payload = {
      party: ledgerId,
      shift: meta.shift,
      dateoftrnforapponly: todayDisplay(),
      dateoftrn: todayIso(),
      trn_number: rows.map((row) => row.number),
      trn_amount: rows.map((row) => row.amount)
    };

    const result = await api.submitTransaction(payload);
    setNotice(result.success ? 'Entry submitted.' : (result.error || 'Submission failed.'));
    if (result.success) {
      setRows([]);
      refreshBalance();
    }
  }

  async function submitJantri() {
    setNotice('');
    if (!ledgerId || !meta.shift) {
      setNotice('Select shift before submitting.');
      return;
    }
    if (selectedShiftExpired()) {
      setNotice('Selected shift is expired.');
      return;
    }
    const amounts = Array.from({ length: 100 }, (_, index) => grid[String(index).padStart(2, '0')] || '');
    const gtotal = amounts.reduce((sum, value) => sum + Number(value || 0), 0);
    if (gtotal <= 0) {
      setNotice('Enter Jantri amounts before submitting.');
      return;
    }
    const result = await api.submitJantri({
      party: ledgerId,
      shift: meta.shift,
      dateoftrnforapponly: todayDisplay(),
      dateoftrn: todayIso(),
      trn_amount: amounts,
      b: [],
      a: [],
      gtotal
    });
    setNotice(result.success ? 'Jantri submitted.' : (result.error || 'Jantri submission failed.'));
    if (result.success) {
      setGrid({});
      refreshBalance();
    }
  }

  return (
    <div className="page-stack">
      <div className="entry-page-head">
        <span />
        <div className="entry-date-chip">
          <span>Date</span>
          <b>{currentDateDisplay}</b>
        </div>
      </div>

      <section className="toolbar panel">
        <label>
          <span>Shift</span>
          <select value={meta.shift} onChange={(event) => setMeta((current) => ({ ...current, shift: event.target.value }))}>
            <option value="">Choose shift</option>
            {shifts.map((shift) => <option key={`${shift.id}-${shift.open_date}`} value={shift.id} disabled={shift.expired}>{shift.name} {shift.expired ? '(expired)' : ''}</option>)}
          </select>
        </label>
      </section>

      <div className="tabs">
        {tabs.map((tab) => <button key={tab} className={active === tab ? 'active' : ''} onClick={() => setActive(tab)}>{tab}</button>)}
      </div>

      {active === 'Num-Akhar' && (
        <section className="panel form-panel">
          <textarea value={text} onChange={(event) => setText(event.target.value)} placeholder="Example: 12 50 34 100" />
          <button className="primary-button" onClick={() => addRows(parseNumAkhar(text))}>Parse</button>
        </section>
      )}

      {active === 'From-To' && (
        <section className="panel form-grid">
          <input placeholder="From" value={range.from} onChange={(event) => setRange((current) => ({ ...current, from: event.target.value.replace(/\D/g, '') }))} />
          <input placeholder="To" value={range.to} onChange={(event) => setRange((current) => ({ ...current, to: event.target.value.replace(/\D/g, '') }))} />
          <input placeholder="Amount" value={range.amount} onChange={(event) => setRange((current) => ({ ...current, amount: event.target.value.replace(/\D/g, '') }))} />
          <button className="primary-button" onClick={() => addRows(buildRange(range.from, range.to, range.amount))}>Build</button>
        </section>
      )}

      {active === 'Cross' && (
        <section className="panel form-grid">
          <input placeholder="Left digits" value={cross.left} onChange={(event) => setCross((current) => ({ ...current, left: event.target.value }))} />
          <input placeholder="Right digits" value={cross.right} onChange={(event) => setCross((current) => ({ ...current, right: event.target.value }))} />
          <input placeholder="Amount" value={cross.amount} onChange={(event) => setCross((current) => ({ ...current, amount: event.target.value.replace(/\D/g, '') }))} />
          <button className="primary-button" onClick={() => addRows(buildCross(cross.left, cross.right, cross.amount))}>Combine</button>
        </section>
      )}

      {active === 'Jantri' ? <JantriGrid grid={grid} setGrid={setGrid} /> : <EntryRows rows={rows} setRows={setRows} />}

      <div className="submit-bar">
        {notice ? <span className="notice">{notice}</span> : <span />}
        <button className="secondary-button" onClick={() => active === 'Jantri' ? setGrid({}) : setRows([])}>Reset</button>
        <button className="primary-button" onClick={active === 'Jantri' ? submitJantri : submit}>{active === 'Jantri' ? 'Submit Jantri' : 'Submit Entry'}</button>
      </div>
    </div>
  );
}
