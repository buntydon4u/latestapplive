import { useMemo } from 'react';
import { useAuth } from '../context/AuthContext.jsx';

function monthStatementRange() {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
  const fmt = (d) => d.toISOString().slice(0, 10);
  return { start: fmt(start), end: fmt(now) };
}

export default function Statement() {
  const { user } = useAuth();

  const iframeUrl = useMemo(() => {
    const range = monthStatementRange();
    return `https://new.555xch.pro/app_statement/${user?.id}?start_date=${range.start}&end_date=${range.end}`;
  }, [user]);

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
      <div style={{ padding: '8px 12px', borderBottom: '1px solid var(--border)' }}>
        <span style={{ fontWeight: 600 }}>Statement</span>
        <a
          className="ghost-button"
          href={iframeUrl}
          target="_blank"
          rel="noreferrer"
          style={{ marginLeft: 12 }}
        >
          Open
        </a>
      </div>
      <iframe
        title="Statement"
        src={iframeUrl}
        style={{ flex: 1, width: '100%', border: 'none' }}
      />
    </div>
  );
}
