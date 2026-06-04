import { useEffect, useState } from 'react';
import { LoadingState } from '../components/DashboardLayout.jsx';
import { api } from '../lib/api.js';

const emptyProfile = {
  ledger_name: '',
  username: '',
  real_name: '',
  owner_name: '',
  mobile: '',
  address: ''
};

function fieldValue(profile, field) {
  return profile?.[field] ?? '';
}

export default function AccountProfile() {
  const [profile, setProfile] = useState(emptyProfile);
  const [draft, setDraft] = useState(emptyProfile);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [notice, setNotice] = useState('');
  const [errors, setErrors] = useState({});

  useEffect(() => {
    let active = true;
    setLoading(true);
    api.getMyProfile()
      .then((result) => {
        if (!active) return;
        if (result.success && result.profile) {
          setProfile(result.profile);
          setDraft(result.profile);
        } else {
          setNotice(result.error || 'Unable to load profile.');
        }
      })
      .catch((error) => {
        if (active) setNotice(error.message || 'Unable to load profile.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => {
      active = false;
    };
  }, []);

  function update(field, value) {
    setDraft((current) => ({ ...current, [field]: value }));
    setErrors((current) => ({ ...current, [field]: '' }));
  }

  function reset() {
    setDraft(profile);
    setErrors({});
    setNotice('');
  }

  async function save() {
    setSaving(true);
    setNotice('');
    setErrors({});

    const payload = {
      real_name: fieldValue(draft, 'real_name'),
      owner_name: fieldValue(draft, 'owner_name'),
      mobile: fieldValue(draft, 'mobile'),
      address: fieldValue(draft, 'address')
    };

    try {
      const result = await api.updateMyProfile(payload);
      if (result.success && result.profile) {
        setProfile(result.profile);
        setDraft(result.profile);
        setNotice('Profile updated successfully.');
      } else {
        setErrors(result.errors || {});
        setNotice(result.error || 'Profile update failed.');
      }
    } catch (error) {
      setNotice(error.message || 'Profile update failed.');
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="page-stack account-page">
      <section className="section-heading inline">
        <div>
          <span className="eyebrow">Account &gt; Profile</span>
          <h1>My Profile</h1>
        </div>
      </section>

      {notice ? <div className="notice full">{notice}</div> : null}

      <section className="panel account-card">
        {loading ? <LoadingState label="Loading profile..." /> : (
          <>
            <div className="account-form-grid">
              <label>
                <span>Party Name</span>
                <input value={fieldValue(draft, 'ledger_name')} readOnly />
              </label>
              <label>
                <span>Username</span>
                <input value={fieldValue(draft, 'username')} readOnly />
              </label>
              <label>
                <span>Real Name</span>
                <input value={fieldValue(draft, 'real_name')} onChange={(event) => update('real_name', event.target.value)} />
                {errors.real_name ? <small>{errors.real_name}</small> : null}
              </label>
              <label>
                <span>Owner Name</span>
                <input value={fieldValue(draft, 'owner_name')} onChange={(event) => update('owner_name', event.target.value)} />
                {errors.owner_name ? <small>{errors.owner_name}</small> : null}
              </label>
              <label>
                <span>Mobile</span>
                <input inputMode="numeric" value={fieldValue(draft, 'mobile')} onChange={(event) => update('mobile', event.target.value.replace(/\D/g, '').slice(0, 10))} />
                {errors.mobile ? <small>{errors.mobile}</small> : null}
              </label>
              <label className="account-wide">
                <span>Address</span>
                <textarea value={fieldValue(draft, 'address')} onChange={(event) => update('address', event.target.value)} />
                {errors.address ? <small>{errors.address}</small> : null}
              </label>
            </div>
            <div className="account-actions">
              <button className="primary-button" onClick={save} disabled={saving}>{saving ? 'Saving...' : 'Save'}</button>
              <button className="secondary-button" onClick={reset} disabled={saving}>Reset</button>
            </div>
          </>
        )}
      </section>
    </div>
  );
}
