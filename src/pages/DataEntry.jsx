import { useEffect, useMemo, useRef, useState } from 'react';
import { api, todayDisplay, todayIso } from '../lib/api.js';
import { useAuth } from '../context/AuthContext.jsx';

const tabs = ['Entry', 'Num-Akhar', 'From-To', 'Cross', 'Jantri'];
const akharTokenPattern = String.raw`(?:\d{1,4}|[AB]\d|\d[AB])`;
const akharSeparatorPattern = String.raw`(?:[\s,!@#$%^&_\-/|.]+)`;
const akharConnectorPattern = String.raw`(?:\*|\(|=|x|X|\u00d7|\bINTO\b|\bINTU\b|\bIN\b)`;
const akharExpressionPattern = new RegExp(
  `${akharTokenPattern}(?:${akharSeparatorPattern}${akharTokenPattern})*\\s*${akharConnectorPattern}\\s*\\d+\\s*\\)?`,
  'gi'
);

function normalizeNumber(value) {
  return String(value || '').replace(/\D/g, '').slice(-2).padStart(2, '0');
}

function sanitizeEntryNumber(value) {
  const digits = String(value || '').replace(/\D/g, '');
  if (digits.length <= 2) return digits;
  if (digits[0] !== digits[1]) return digits.slice(0, 2);

  let repeated = '';
  for (const digit of digits) {
    if (digit !== digits[0]) break;
    repeated += digit;
    if (repeated.length === 4) break;
  }
  return repeated;
}

function normalizeEntryNumber(value) {
  const number = sanitizeEntryNumber(value);
  return number.length <= 2 ? normalizeNumber(number) : number;
}

function shouldAdvanceNumberFocus(number) {
  if (number.length === 4) return true;
  return number.length === 2 && number[0] !== number[1];
}

function cleanWhatsAppText(rawText) {
  return String(rawText || '')
    .split('\n')
    .map((line) => line
      .replace(/^\[[^\]]+\]\s*[^:]+:\s*/i, '')
      .replace(/>{2,}/g, '')
      .replace(/[\/.]/g, ',')
      .replace(/\*{2,}/g, '*')
      .trim())
    .filter(Boolean)
    .join('\n');
}

function normalizeAkharText(rawText) {
  return cleanWhatsAppText(rawText)
    .toUpperCase()
    .replace(/\b(INTO|INTU|IN)\b/g, '*')
    .replace(/[=X\u00d7(]/g, '*')
    .replace(/\)/g, '')
    .replace(/[!@#$%^&_\-/|. \t\r]+/g, ',')
    .replace(/,+/g, ',')
    .replace(/,\*/g, '*')
    .replace(/\*,/g, '*')
    .replace(/^,|,$/gm, '');
}

function normalizeAkharNumber(token) {
  const value = String(token || '').trim().toUpperCase();
  if (!value) return { error: 'Number is missing.' };
  if (value === '100') return { number: '00' };

  // Support short AB notations (e.g. A1 -> 1111, 1B -> 111)
  if (/^[AB]\d$|^\d[AB]$/.test(value)) {
    const digit = value.replace(/[AB]/g, '');
    return { number: digit.repeat(value.includes('A') ? 4 : 3) };
  }

  // Support legacy A-prefixed 3-digit tokens (e.g. A111 -> 1111, A222 -> 2222)
  if (/^A(\d)\1{2}$/.test(value)) {
    const digit = value[1];
    return { number: digit.repeat(4) };
  }

  if (!/^\d+$/.test(value)) {
    return { error: `${value} is not a valid number.` };
  }

  if (value.length > 2 && !value.split('').every((digit) => digit === value[0])) {
    return { error: `${value} is not a valid number.` };
  }

  return { number: value.length <= 2 ? normalizeNumber(value) : value };
}

function uniqueRows(rows) {
  return rows.filter((row) => row.number && row.amount);
}

function parseAkharExpression(expression) {
  const normalized = normalizeAkharText(expression);
  const parts = normalized.split('*');
  if (parts.length === 1) return { rows: [], errors: [`Amount not found for ${expression.trim()}.`] };
  if (parts.length > 2) return { rows: [], errors: [`Multiple amounts found for ${parts[0]}.`] };

  const amount = Number(String(parts[1] || '').replace(/\D/g, ''));
  if (!Number.isFinite(amount) || amount <= 0) {
    return { rows: [], errors: [`Amount not found for ${parts[0]}.`] };
  }

  const rows = [];
  const errors = [];
  const tokens = String(parts[0] || '').split(',').map((token) => token.trim()).filter(Boolean);

  for (const token of tokens) {
    const result = normalizeAkharNumber(token);
    if (result.error) {
      errors.push(result.error);
    } else {
      rows.push({ number: result.number, amount });
    }
  }

  return { rows, errors };
}

function parseNumAkhar(text) {
  const cleanedText = cleanWhatsAppText(text);
  const rows = [];
  const errors = [];

  // Split into lines and process each
  for (let line of cleanedText.split('\n')) {
    line = line.trim();
    if (!line) continue;
    
    // Try to match multiple comma-separated amount pairs: 20,28*100,29,74*50
    // Split by comma, then find which items are amounts (preceded by numbers)
    const items = line.split(',').map((item) => item.trim());
    let currentNumbers = [];
    
    for (const item of items) {
      if (item.includes('*')) {
        // This item has an amount
        const [numsStr, amountStr] = item.split('*');
        
        // Add any number from this item before the *
        if (numsStr.trim()) {
          currentNumbers.push(numsStr.trim());
        }
        
        // Process accumulated numbers with this amount
        const amount = Number(String(amountStr || '').replace(/\D/g, ''));
        if (Number.isFinite(amount) && amount > 0) {
          for (const token of currentNumbers) {
            const result = normalizeAkharNumber(token);
            if (result.error) {
              errors.push(result.error);
            } else {
              rows.push({ number: result.number, amount });
            }
          }
          currentNumbers = [];
        } else {
          errors.push(`Invalid amount: ${amountStr}`);
        }
      } else {
        // This is just a number, accumulate it
        if (item && /^\d+[AB]?|[AB]\d+$/.test(item)) {
          currentNumbers.push(item);
        } else if (item) {
          errors.push(`Invalid number format: ${item}`);
        }
      }
    }
    
    // If there are remaining numbers without an amount, that's an error
    if (currentNumbers.length) {
      errors.push(`Numbers without amount: ${currentNumbers.join(',')}`);
    }
  }

  return { rows: uniqueRows(rows), cleanedText: normalizeAkharText(cleanedText), errors: [...new Set(errors)] };
}

function buildRange(from, to, amount) {
  const start = Number(from);
  const end = String(to) === '00' ? 100 : Number(to);
  const amt = Number(amount);
  if (!Number.isFinite(start) || !Number.isFinite(end) || amt <= 0) return [];
  if (start > end) return [];
  return Array.from({ length: end - start + 1 }, (_, offset) => ({
    number: normalizeNumber(start + offset),
    amount: amt
  }));
}

function buildCross(left, right, amount, joda = 'Yes') {
  const amt = Number(amount);
  if (!left || !right || amt <= 0) return [];
  const rows = [];
  for (const first of left.replace(/\D/g, '')) {
    for (const second of right.replace(/\D/g, '')) {
      if (joda === 'No' && first === second) continue;
      rows.push({ number: `${first}${second}`, amount: amt });
    }
  }
  return uniqueRows(rows);
}

function buildJantriRows(grid) {
  return Array.from({ length: 100 }, (_, index) => {
    const number = String(index).padStart(2, '0');
    const amount = String(grid[number] || '').replace(/\D/g, '');
    return amount ? { number, amount } : null;
  }).filter(Boolean);
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
            <input value={row.number} maxLength="4" onChange={(event) => setRows((current) => current.map((item, rowIndex) => rowIndex === index ? { ...item, number: sanitizeEntryNumber(event.target.value) } : item))} />
            <input value={row.amount} inputMode="numeric" onChange={(event) => setRows((current) => current.map((item, rowIndex) => rowIndex === index ? { ...item, amount: event.target.value.replace(/\D/g, '') } : item))} />
            <button className="icon-button danger" title="Delete row" onClick={() => setRows((current) => current.filter((_, rowIndex) => rowIndex !== index))}>X</button>
          </div>
        ))}
        {!rows.length ? <p className="empty-state">No entries prepared.</p> : null}
      </div>
    </section>
  );
}

function ManualEntryRows({ rows, setRows, draft, setDraft, setNotice }) {
  const numberRef = useRef(null);
  const amountRef = useRef(null);
  const rowAmountRefs = useRef([]);
  const total = rows.reduce((sum, row) => sum + Number(row.amount || 0), 0);

  function updateRow(index, field, value) {
    setRows((current) => current.map((row, rowIndex) => (
      rowIndex === index ? { ...row, [field]: value } : row
    )));
  }

  function updateRowNumber(index, value) {
    const number = sanitizeEntryNumber(value);
    updateRow(index, 'number', number);
    if (shouldAdvanceNumberFocus(number)) rowAmountRefs.current[index]?.focus();
  }

  function updateDraftNumber(value) {
    const number = sanitizeEntryNumber(value);
    setDraft((current) => ({ ...current, number }));
    if (shouldAdvanceNumberFocus(number)) amountRef.current?.focus();
  }

  function addDraftRow() {
    const number = sanitizeEntryNumber(draft.number);
    const amount = draft.amount.replace(/\D/g, '');

    if (!number || !amount) {
      setNotice('Enter number and amount before adding.');
      return;
    }

    setRows((current) => [...current, { number: normalizeEntryNumber(number), amount }]);
    setDraft({ number: '', amount: '' });
    setNotice('');
    window.setTimeout(() => numberRef.current?.focus(), 0);
  }

  return (
    <section className="panel table-panel">
      <div className="panel-title">
        <h3>Entry</h3>
        <b>{rows.length} / {total}</b>
      </div>
      <div className="entry-table manual-entry-table">
        {rows.map((row, index) => (
          <div className="entry-row" key={`${row.number}-${index}`}>
            <input
              value={row.number}
              maxLength="4"
              inputMode="numeric"
              placeholder="Number"
              onChange={(event) => updateRowNumber(index, event.target.value)}
            />
            <input
              ref={(node) => { rowAmountRefs.current[index] = node; }}
              value={row.amount}
              inputMode="numeric"
              placeholder="Amount"
              onChange={(event) => updateRow(index, 'amount', event.target.value.replace(/\D/g, ''))}
            />
            <button className="icon-button danger" title="Delete row" onClick={() => setRows((current) => current.filter((_, rowIndex) => rowIndex !== index))}>X</button>
          </div>
        ))}
        <div className="entry-row">
          <input
            ref={numberRef}
            value={draft.number}
            maxLength="4"
            inputMode="numeric"
            placeholder="Number"
            onChange={(event) => updateDraftNumber(event.target.value)}
          />
          <input
            ref={amountRef}
            value={draft.amount}
            inputMode="numeric"
            placeholder="Amount"
            onChange={(event) => setDraft((current) => ({ ...current, amount: event.target.value.replace(/\D/g, '') }))}
            onKeyDown={(event) => {
              if (event.key === 'Enter') {
                event.preventDefault();
                addDraftRow();
              }
            }}
          />
          <button className="icon-button" title="Add row" onClick={addDraftRow}>+</button>
        </div>
      </div>
    </section>
  );
}

function JantriGrid({ grid, setGrid, onBuild }) {
  const refs = useRef([]);
  const numbers = useMemo(() => [...Array.from({ length: 99 }, (_, index) => String(index + 1).padStart(2, '0')), '00'], []);
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
      <button className="primary-button" onClick={onBuild}>Done</button>
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
  const [cross, setCross] = useState({ left: '', right: '', amount: '', joda: 'Yes' });
  const [rows, setRows] = useState([]);
  const [manualDraft, setManualDraft] = useState({ number: '', amount: '' });
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

  function getManualDraftRow() {
    const number = sanitizeEntryNumber(manualDraft.number);
    const amount = manualDraft.amount.replace(/\D/g, '');
    return number && amount ? { number: normalizeEntryNumber(number), amount } : null;
  }

  function handleParseNumAkhar() {
    setNotice('');
    const result = parseNumAkhar(text);
    if (result.errors.length) {
      if (result.cleanedText) setText(result.cleanedText);
      setNotice(result.errors[0]);
    } else if (!result.rows.length) {
      setNotice('Enter Num-Akhar patterns before parsing.');
    } else {
      setRows((current) => [...current, ...result.rows]);
      setText('');
      setActive('Entry');
    }
  }

  function handleBuildRange() {
    setNotice('');
    const newRows = buildRange(range.from, range.to, range.amount);
    if (!newRows.length) {
      setNotice('Enter valid From-To numbers and amount before building.');
      return;
    }
    setRows((current) => [...current, ...uniqueRows(newRows)]);
    setRange({ from: '', to: '', amount: '' });
    setActive('Entry');
  }

  function handleBuildCross() {
    setNotice('');
    const newRows = buildCross(cross.left, cross.right, cross.amount, cross.joda);
    if (!newRows.length) {
      setNotice('Enter valid Cross digits and amount before building.');
      return;
    }
    setRows((current) => [...current, ...newRows]);
    setCross({ left: '', right: '', amount: '', joda: cross.joda });
    setActive('Entry');
  }

  function handleBuildJantri() {
    setNotice('');
    const newRows = buildJantriRows(grid);
    if (!newRows.length) {
      setNotice('Enter Jantri amounts before building.');
      return;
    }
    setRows((current) => [...current, ...newRows]);
    setGrid({});
    setActive('Entry');
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
    const draftRow = active === 'Entry' ? getManualDraftRow() : null;
    const rowsForSubmit = draftRow ? [...rows, draftRow] : rows;

    if (!rowsForSubmit.length) {
      setNotice('Enter number and amount before submitting.');
      return;
    }

    const payload = {
      party: ledgerId,
      shift: meta.shift,
      dateoftrnforapponly: todayDisplay(),
      dateoftrn: todayIso(),
      trn_number: rowsForSubmit.map((row) => row.number),
      trn_amount: rowsForSubmit.map((row) => row.amount)
    };

    const result = await api.submitTransaction(payload);
    setNotice(result.success ? 'Entry submitted.' : (result.error || 'Submission failed.'));
    if (result.success) {
      setRows([]);
      setManualDraft({ number: '', amount: '' });
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
          <textarea
            value={text}
            onBlur={() => setText((current) => normalizeAkharText(current))}
            onChange={(event) => setText(event.target.value)}
            placeholder="Example: 1,2,3*10 or 11,22(100)"
          />
          <button className="primary-button" onClick={handleParseNumAkhar}>Done</button>
        </section>
      )}

      {active === 'From-To' && (
        <section className="panel form-grid">
          <input placeholder="From" value={range.from} onChange={(event) => setRange((current) => ({ ...current, from: event.target.value.replace(/\D/g, '') }))} />
          <input placeholder="To" value={range.to} onChange={(event) => setRange((current) => ({ ...current, to: event.target.value.replace(/\D/g, '') }))} />
          <input placeholder="Amount" value={range.amount} onChange={(event) => setRange((current) => ({ ...current, amount: event.target.value.replace(/\D/g, '') }))} />
          <button className="primary-button" onClick={handleBuildRange}>Done</button>
        </section>
      )}

      {active === 'Cross' && (
        <section className="panel form-grid">
          <input placeholder="Left digits" value={cross.left} onChange={(event) => setCross((current) => ({ ...current, left: event.target.value }))} />
          <input placeholder="Right digits" value={cross.right} onChange={(event) => setCross((current) => ({ ...current, right: event.target.value }))} />
          <input placeholder="Amount" value={cross.amount} onChange={(event) => setCross((current) => ({ ...current, amount: event.target.value.replace(/\D/g, '') }))} />
          <select value={cross.joda} onChange={(event) => setCross((current) => ({ ...current, joda: event.target.value }))}>
            <option value="Yes">Joda yes</option>
            <option value="No">Joda no</option>
          </select>
          <button className="primary-button" onClick={handleBuildCross}>Done</button>
        </section>
      )}

      {active === 'Entry' ? <ManualEntryRows rows={rows} setRows={setRows} draft={manualDraft} setDraft={setManualDraft} setNotice={setNotice} /> : null}
      {active === 'Jantri' ? <JantriGrid grid={grid} setGrid={setGrid} onBuild={handleBuildJantri} /> : null}

      {active === 'Entry' && (
        <div className="submit-bar">
          {notice ? <span className="notice">{notice}</span> : <span />}
          <button
            className="secondary-button"
            onClick={() => {
              setRows([]);
              setManualDraft({ number: '', amount: '' });
            }}
          >
            Reset
          </button>
          <button className="primary-button" onClick={submit}>Submit Entry</button>
        </div>
      )}
    </div>
  );
}
