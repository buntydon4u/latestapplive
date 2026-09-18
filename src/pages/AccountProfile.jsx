import { useEffect, useState } from 'react';
import { LoadingState } from '../components/DashboardLayout.jsx';
import { useAuth } from '../context/AuthContext.jsx';
import { api, buildHelplineHref } from '../lib/api.js';

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

function PhoneIcon() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="helpline-icon">
      <path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.8-.4 1.2-.3 1.3.4 2.6.6 4 .6.7 0 1.2.5 1.2 1.2v3.5c0 .7-.5 1.2-1.2 1.2C10.4 21.9 2.1 13.6 2.1 3.4c0-.7.5-1.2 1.2-1.2h3.5c.7 0 1.2.5 1.2 1.2 0 1.4.2 2.7.6 4 .1.4 0 .9-.3 1.2l-1.7 2.2Z" />
    </svg>
  );
}

function WhatsAppIcon() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="helpline-icon">
      <path d="M12 2.3a9.4 9.4 0 0 0-8 14.3L3 21l4.5-1.1A9.4 9.4 0 1 0 12 2.3Zm0 1.9a7.5 7.5 0 0 1 0 15c-1.3 0-2.6-.3-3.7-1l-.4-.2-2.3.6.6-2.2-.3-.4A7.5 7.5 0 0 1 12 4.2Z" />
      <path d="M9 7.7c-.2-.5-.4-.5-.7-.5H7.7c-.3 0-.7.1-1 .5-.4.4-1.3 1.2-1.3 3s1.3 3.5 1.5 3.7c.2.3 2.5 4 6.2 5.4 3.1 1.2 3.7.8 4.4.8.7-.1 2.1-.9 2.4-1.7.3-.8.3-1.5.2-1.7-.1-.2-.4-.3-.8-.5l-2.4-1.2c-.4-.1-.7-.2-.9.2l-.8 1c-.2.3-.5.3-.9.1-.4-.2-1.6-.6-3-1.9-1.1-1-1.9-2.2-2.1-2.6-.2-.4 0-.6.2-.8l.6-.7c.2-.2.2-.4.3-.6.1-.2 0-.5 0-.7L9 7.7Z" />
    </svg>
  );
}

export default function AccountProfile() {
  const { user, helpline, refreshHelpline } = useAuth();
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

  useEffect(() => {
    refreshHelpline(user?.id);
  }, [refreshHelpline, user?.id]);

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

  function openHelpline() {
    const href = buildHelplineHref(helpline);
    if (!href) return;
    if (href.startsWith('tel:')) {
      window.location.href = href;
    } else {
      window.open(href, '_blank', 'noopener,noreferrer');
    }
  }

  const hasHelpline = Boolean(helpline?.helpline_number);
  const isWhatsapp = helpline?.helpline_type === 'whatsapp';

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
            {hasHelpline ? (
              <div className="account-helpline">
                <div>
                  <span>Master Helpline</span>
                  <b>{helpline.helpline_number}</b>
                </div>
                <button
                  className={`helpline-action ${isWhatsapp ? 'whatsapp' : 'phone'}`}
                  onClick={openHelpline}
                  title={isWhatsapp ? 'Open WhatsApp' : 'Call master'}
                  type="button"
                  aria-label={isWhatsapp ? 'Open WhatsApp helpline' : 'Call master helpline'}
                >
                  {isWhatsapp ? <WhatsAppIcon /> : <PhoneIcon />}
                </button>
              </div>
            ) : null}
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
