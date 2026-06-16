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
      .catch((err) => { if (active) setNotice(err.message || 'Unable to load profile.'); })
      .finally(() => { if (active) setLoading(false); });
    return () => { active = false; };
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
      if (result.success) {
        const updated = result.profile || { ...profile, ...payload };
        setProfile(updated);
        setDraft(updated);
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
            <div className="notice full" style={{marginBottom: '12px', opacity: 0.8}}>
              Profile editing is not available — the server does not support this endpoint yet. Contact your administrator to update profile details.
            </div>
            <div className="account-form-grid">
              <label>
                <span>Party Name</span>
                <input value={fieldValue(profile, 'ledger_name')} readOnly />
              </label>
              <label>
                <span>Username</span>
                <input value={fieldValue(profile, 'username')} readOnly />
              </label>
              <label>
                <span>Real Name</span>
                <input value={fieldValue(profile, 'real_name')} readOnly />
              </label>
              <label>
                <span>Owner Name</span>
                <input value={fieldValue(profile, 'owner_name')} readOnly />
              </label>
              <label>
                <span>Mobile</span>
                <input value={fieldValue(profile, 'mobile')} readOnly />
              </label>
              <label className="account-wide">
                <span>Address</span>
                <textarea value={fieldValue(profile, 'address')} readOnly />
              </label>
            </div>
          </>
        )}
      </section>
    </div>
  );
}
