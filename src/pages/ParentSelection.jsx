import { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext.jsx';
import { api } from '../lib/api.js';

export default function ParentSelection() {
  const { parentSelection, selectChild, logout } = useAuth();
  const [children, setChildren] = useState([]);
  const [error, setError] = useState('');

  useEffect(() => {
    api.children(parentSelection?.parent_id)
      .then((result) => {
        if (result.success) setChildren(result.children || []);
        else setError(result.error || 'Unable to load logins.');
      })
      .catch((err) => setError(err.message));
  }, [parentSelection]);

  async function choose(id) {
    const result = await selectChild(id);
    if (!result.success) setError(result.error || 'Unable to select login.');
  }

  return (
    <main className="selection-page">
      <section className="section-heading">
        <span className="eyebrow">Available logins</span>
        <h1>{parentSelection?.name}</h1>
      </section>
      {error ? <div className="form-error wide">{error}</div> : null}
      <div className="selection-grid">
        <button className="selection-card" onClick={() => choose(parentSelection.parent_id)}>
          <span className="avatar large">{String(parentSelection?.name || 'P').slice(0, 2).toUpperCase()}</span>
          <b>{parentSelection?.name}</b>
          <small>Parent login</small>
        </button>
        {children.map((child) => (
          <button className="selection-card" key={child.id} onClick={() => choose(child.id)}>
            <span className="avatar large">{String(child.ledger_name || 'L').slice(0, 2).toUpperCase()}</span>
            <b>{child.ledger_name}</b>
            <small>Sub login</small>
          </button>
        ))}
      </div>
      <button className="ghost-button centered" onClick={logout}>Back to login</button>
    </main>
  );
}
