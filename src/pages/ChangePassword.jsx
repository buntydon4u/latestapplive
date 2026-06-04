import { useState } from 'react';
import { api } from '../lib/api.js';

const emptyForm = {
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
};

export default function ChangePassword() {
  const [form, setForm] = useState(emptyForm);
  const [visible, setVisible] = useState({});
  const [saving, setSaving] = useState(false);
  const [notice, setNotice] = useState('');
  const [errors, setErrors] = useState({});

  function update(field, value) {
    setForm((current) => ({ ...current, [field]: value }));
    setErrors((current) => ({ ...current, [field]: '' }));
  }

  async function submit() {
    setSaving(true);
    setNotice('');
    setErrors({});

    try {
      const result = await api.changeMyPassword(form);
      if (result.success) {
        setForm(emptyForm);
        setNotice('Password changed successfully.');
      } else {
        setErrors(result.errors || {});
        setNotice(result.error || 'Password change failed.');
      }
    } catch (error) {
      setNotice(error.message || 'Password change failed.');
    } finally {
      setSaving(false);
    }
  }

  function PasswordField({ field, label }) {
    return (
      <label>
        <span>{label}</span>
        <div className="password-input-row">
          <input
            type={visible[field] ? 'text' : 'password'}
            value={form[field]}
            onChange={(event) => update(field, event.target.value)}
          />
          <button
            className="secondary-button"
            onClick={() => setVisible((current) => ({ ...current, [field]: !current[field] }))}
            type="button"
          >
            {visible[field] ? 'Hide' : 'Show'}
          </button>
        </div>
        {errors[field] ? <small>{errors[field]}</small> : null}
      </label>
    );
  }

  return (
    <div className="page-stack account-page">
      <section className="section-heading inline">
        <div>
          <span className="eyebrow">Account &gt; Change Password</span>
          <h1>Change Password</h1>
        </div>
      </section>

      {notice ? <div className="notice full">{notice}</div> : null}

      <section className="panel account-card">
        <div className="account-form-grid single">
          <PasswordField field="currentPassword" label="Current Password" />
          <PasswordField field="newPassword" label="New Password" />
          <PasswordField field="confirmPassword" label="Confirm Password" />
        </div>
        <div className="account-actions">
          <button className="primary-button" onClick={submit} disabled={saving}>{saving ? 'Saving...' : 'Change Password'}</button>
          <button className="secondary-button" onClick={() => { setForm(emptyForm); setErrors({}); setNotice(''); }} disabled={saving}>Clear</button>
        </div>
      </section>
    </div>
  );
}
