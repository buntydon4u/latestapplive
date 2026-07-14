import { useEffect, useState } from 'react';

const PREPARE_TIME_MS = 5500;

function isStandalone() {
  return window.matchMedia?.('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

function isIos() {
  return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
}

export default function InstallGate({ children }) {
  const [installPrompt, setInstallPrompt] = useState(null);
  const [stage, setStage] = useState('landing');
  const [installed, setInstalled] = useState(isStandalone);
  const [message, setMessage] = useState('');
  const [continueToApp, setContinueToApp] = useState(
    () => isStandalone() || sessionStorage.getItem('xch555-onboarding-complete') === 'true'
  );

  useEffect(() => {
    function handleBeforeInstallPrompt(event) {
      event.preventDefault();
      setInstallPrompt(event);
    }

    function handleInstalled() {
      setInstalled(true);
      setStage('ready');
      setMessage('XCH555 was installed successfully and is ready to open.');
    }

    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('appinstalled', handleInstalled);
    return () => {
      window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
      window.removeEventListener('appinstalled', handleInstalled);
    };
  }, []);

  function prepareApp() {
    setStage('preparing');
    setMessage('');
    window.setTimeout(() => setStage('ready'), PREPARE_TIME_MS);
  }

  async function requestInstall() {
    if (!installPrompt) {
      setMessage(
        isIos()
          ? 'Open the device Share menu and choose Add to Home Screen to confirm installation.'
          : 'Tap the three dots (⋮) at the top right, then tap Install app.'
      );
      return;
    }

    await installPrompt.prompt();
    const choice = await installPrompt.userChoice;
    setInstallPrompt(null);

    if (choice.outcome === 'accepted') {
      setInstalled(true);
      setMessage('XCH555 was installed successfully and is ready to open.');
    } else {
      setMessage('Installation was not completed. Confirm installation to finish setup.');
    }
  }

  function openLogin() {
    sessionStorage.setItem('xch555-onboarding-complete', 'true');
    setContinueToApp(true);
  }

  if (continueToApp) return children;

  return (
    <main className="install-gate">
      <section className="install-card" aria-live="polite">
        <img className="install-logo" src="/pwa-192x192.png" alt="XCH555" />

        {stage === 'landing' && (
          <>
            <p className="install-eyebrow">XCH555</p>
            <h1>Install XCH555</h1>
            <p className="install-copy">Set up XCH555 on this device to continue.</p>
            <button className="install-primary" type="button" onClick={prepareApp}>Install</button>
          </>
        )}

        {stage === 'preparing' && (
          <>
            <div className="install-spinner" aria-hidden="true" />
            <h1>Installing XCH555</h1>
            <p className="install-copy">Please wait while installation is prepared…</p>
            <div className="install-progress"><span /></div>
          </>
        )}

        {stage === 'ready' && (
          <>
            <div className="install-check" aria-hidden="true">✓</div>
            <h1>{installed ? 'Installation complete' : 'Ready to install'}</h1>
            <p className="install-copy">
              {installed
                ? 'XCH555 has been added to your device.'
                : 'Confirm installation on your device to complete the setup.'}
            </p>
            {message && <p className="install-message">{message}</p>}
            {!installed && (
              <button className="install-primary" type="button" onClick={requestInstall}>
                Confirm Installation
              </button>
            )}
            <button className="install-secondary" type="button" onClick={openLogin}>
              Open XCH555
            </button>
          </>
        )}
      </section>
    </main>
  );
}
