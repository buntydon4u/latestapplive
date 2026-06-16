import { useState } from 'react';
import { api } from '../lib/api.js';

const emptyForm = {
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
};

function PasswordField({ field, label, value, onChange, visible, onToggleVisible, error }) {
  return (
    <label>
      <span>{label}</span>
      <div className="password-input-row">
        <input
          type={visible ? 'text' : 'password'}
          value={value}
          onChange={onChange}
        />
        <button
          className="secondary-button"
          onClick={onToggleVisible}
          type="button"
        >
          {visible ? 'Hide' : 'Show'}
        </button>
      </div>
      {error ? <small>{error}</small> : null}
    </label>
  );
}

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

  function toggleVisible(field) {
    setVisible((current) => ({ ...current, [field]: !current[field] }));
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

  function clear() {
    setForm(emptyForm);
    setErrors({});
    setNotice('');
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
          <PasswordField
            field="currentPassword"
            label="Current Password"
            value={form.currentPassword}
            onChange={(e) => update('currentPassword', e.target.value)}
            visible={visible.currentPassword}
            onToggleVisible={() => toggleVisible('currentPassword')}
            error={errors.currentPassword}
          />
          <PasswordField
            field="newPassword"
            label="New Password"
            value={form.newPassword}
            onChange={(e) => update('newPassword', e.target.value)}
            visible={visible.newPassword}
            onToggleVisible={() => toggleVisible('newPassword')}
            error={errors.newPassword}
          />
          <PasswordField
            field="confirmPassword"
            label="Confirm Password"
            value={form.confirmPassword}
            onChange={(e) => update('confirmPassword', e.target.value)}
            visible={visible.confirmPassword}
            onToggleVisible={() => toggleVisible('confirmPassword')}
            error={errors.confirmPassword}
          />
        </div>
        <div className="account-actions">
          <button className="primary-button" onClick={submit} disabled={saving}>{saving ? 'Saving...' : 'Change Password'}</button>
          <button className="secondary-button" onClick={clear} disabled={saving}>Clear</button>
        </div>
      </section>
    </div>
  );
}
