const API_ROOT = import.meta.env.VITE_API_ROOT || '/index.php/api/v1';

const TOKEN_KEY = 'ci3_api_token';
const PARENT_TOKEN_KEY = 'ci3_parent_selection_token';

function getToken() {
  return localStorage.getItem(TOKEN_KEY) || '';
}

function getParentToken() {
  return localStorage.getItem(PARENT_TOKEN_KEY) || '';
}

function setToken(token) {
  if (token) localStorage.setItem(TOKEN_KEY, token);
  else localStorage.removeItem(TOKEN_KEY);
}

function setParentToken(token) {
  if (token) localStorage.setItem(PARENT_TOKEN_KEY, token);
  else localStorage.removeItem(PARENT_TOKEN_KEY);
}

function clearTokens() {
  setToken('');
  setParentToken('');
}

function buildUrl(path, params) {
  const base = API_ROOT.startsWith('http')
    ? API_ROOT
    : `${window.location.origin}${API_ROOT.startsWith('/') ? '' : '/'}${API_ROOT}`;
  const url = new URL(`${base.replace(/\/$/, '')}/${path.replace(/^\//, '')}`);

  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        url.searchParams.set(key, value);
      }
    });
  }

  return url;
}

function normalizeResponse(json, fallbackStatus) {
  const ok = json?.status === true || json?.success === true || json?.logged_in === true;
  const data = json?.data;
  const dataObject = data && !Array.isArray(data) && typeof data === 'object' ? data : {};

  return {
    ...dataObject,
    data,
    success: ok,
    status: json?.status ?? ok,
    message: json?.message || '',
    error: json?.message || json?.error || (fallbackStatus >= 400 ? `Request failed: ${fallbackStatus}` : ''),
    errors: json?.errors || null,
    logged_in: dataObject.logged_in ?? json?.logged_in ?? false,
    parent_selection_required: dataObject.parent_selection_required ?? json?.parent_selection_required ?? false
  };
}

async function request(path, options = {}) {
  const url = buildUrl(path, {
    ...(options.params || {}),
    _ts: Date.now()
  });
  const token = options.parentAuth ? getParentToken() : getToken();

  const init = {
    method: options.method || 'GET',
    cache: 'no-store',
    headers: {
      Accept: 'application/json',
      'Cache-Control': 'no-cache, no-store, max-age=0',
      Pragma: 'no-cache',
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
      ...(token ? { Authorization: `Bearer ${token}` } : {})
    }
  };

  if (options.body) {
    init.body = JSON.stringify(options.body);
  }

  const response = await fetch(url.toString(), init);
  const text = await response.text();
  let json = {};

  try {
    json = text ? JSON.parse(text) : {};
  } catch {
    throw new Error(text || `Request failed: ${response.status}`);
  }

  return normalizeResponse(json, response.status);
}

export const api = {
  login: async (username, password) => {
    clearTokens();
    const result = await request('login', { method: 'POST', body: { username, password } });

    if (result.success && result.token) {
      if (result.parent_selection_required) {
        setParentToken(result.token);
      } else {
        setToken(result.token);
      }
    }

    return result;
  },

  selectChild: async (child_id) => {
    const result = await request('auth/select-child', {
      method: 'POST',
      parentAuth: true,
      body: { child_id }
    });

    if (result.success && result.token) {
      setToken(result.token);
    }

    return result;
  },

  parentSession: async () => {
    if (!getParentToken()) {
      return { success: false, logged_in: false };
    }

    return request('me', { parentAuth: true });
  },

  session: async () => {
    if (!getToken() && !getParentToken()) {
      return { success: false, logged_in: false };
    }

    if (getToken()) {
      return request('me');
    }

    return request('me', { parentAuth: true });
  },

  logout: async () => {
    const result = getToken()
      ? await request('logout', { method: 'POST' })
      : { success: true };
    clearTokens();
    return result;
  },

  children: async (parent_id) => {
    const result = await request('ledgers/children', {
      parentAuth: !getToken(),
      params: { parent_id }
    });
    return { ...result, children: Array.isArray(result.data) ? result.data : [] };
  },

  ledger: (id) => request(`ledgers/${id}`),
  balance: () => request('balance'),
  parties: async () => {
    const result = await request('parties');
    return { ...result, parties: Array.isArray(result.data) ? result.data : [] };
  },
  shifts: async () => {
    const result = await request('shifts');
    return { ...result, shifts: Array.isArray(result.data) ? result.data : [] };
  },
  transactions: async () => {
    const result = await request('transactions', { cache: 'no-store' });
    return { ...result, transactions: Array.isArray(result.data) ? result.data : [] };
  },
  transactionDetails: (id) => request(`transactions/${id}`),
  hisabs: async () => {
    const result = await request('hisabs');
    return { ...result, hisabs: Array.isArray(result.data) ? result.data : [] };
  },
  hisabTillDateReport: async (params) => {
    const result = await request('hisabs/report', {
      params
    });
    const report = result.data && typeof result.data === 'object' ? result.data : {};
    return {
      ...result,
      report,
      rows: Array.isArray(report.rows) ? report.rows : [],
      summary: report.summary || {}
    };
  },
  declaredResults: async (limit = 10) => {
    const result = await request('results/latest', { params: { limit } });
    return { ...result, results: Array.isArray(result.data) ? result.data : [] };
  },
  jantriPartyMeta: async () => {
    const result = await request('party-jantri/meta');
    return {
      ...result,
      shifts: Array.isArray(result.shifts) ? result.shifts : []
    };
  },
  jantriPartyReport: async (params) => {
    const result = await request('party-jantri/report', { params });
    const report = result.data && typeof result.data === 'object' ? result.data : {};
    return {
      ...result,
      rows: Array.isArray(report.rows) ? report.rows : [],
      filters: report.filters || {},
      summary: report.summary || { totalAmount: 0, totalRows: 0 }
    };
  },
  getMyProfile: async () => {
    const result = await request('me');
    const raw = result.user || result.data || result;
    const profile = {
      ledger_name: raw.ledger_name || raw.name || '',
      username: raw.username || raw.user || '',
      real_name: raw.real_name || '',
      owner_name: raw.owner_name || '',
      mobile: raw.mobile || '',
      address: raw.address || ''
    };
    return { ...result, success: result.success || result.logged_in, profile };
  },
  updateMyProfile: async (body) => {
    const result = await request('account/profile', { method: 'POST', body });
    return { ...result, profile: result.profile || null };
  },
  changeMyPassword: (body) => request('account/change-password', { method: 'POST', body }),
  submitTransaction: (body) => request('transactions', { method: 'POST', body }),
  submitJantri: (body) => request('jantri', { method: 'POST', body }),
  deleteTransaction: (id) => request(`transactions/${id}`, { method: 'DELETE' })
};

export function todayIso() {
  const now = new Date(Date.now() - 7 * 60 * 60 * 1000);
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
}

export function todayDisplay() {
  const now = new Date(Date.now() - 7 * 60 * 60 * 1000);
  return `${String(now.getDate()).padStart(2, '0')}-${String(now.getMonth() + 1).padStart(2, '0')}-${now.getFullYear()}`;
}
