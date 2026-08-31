import { useEffect, useState } from 'react';

const PREPARE_TIME_MS = 5500;
const INSTALL_FLAG_KEY = 'xch555_install_completed_v1';

function isStandalone() {
  return window.matchMedia?.('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

function isMobileDevice() {
  const ua = window.navigator.userAgent || '';
  return (
    /android|iphone|ipad|ipod|iemobile|opera mini|mobile/i.test(ua) ||
    window.navigator.userAgentData?.mobile === true ||
    window.matchMedia?.('(pointer: coarse)').matches === true
  );
}

function defaultInstallMessage() {
  return 'Installation was not completed. Confirm installation to finish setup.';
}

export default function InstallGate({ children }) {
  const [installPrompt, setInstallPrompt] = useState(null);
  const [stage, setStage] = useState('landing');
  const [installed, setInstalled] = useState(() => {
    if (typeof window === 'undefined') return false;
    return isStandalone() || window.localStorage.getItem(INSTALL_FLAG_KEY) === '1';
  });
  const [message, setMessage] = useState('');

  useEffect(() => {
    function handleBeforeInstallPrompt(event) {
      event.preventDefault();
      setInstallPrompt(event);
    }

    function handleInstalled() {
      window.localStorage.setItem(INSTALL_FLAG_KEY, '1');
      setInstalled(true);
      setStage('ready');
      setMessage('Close this browser tab and launch bull99exch from its new home-screen icon.');
    }

    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('appinstalled', handleInstalled);
    return () => {
      window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
      window.removeEventListener('appinstalled', handleInstalled);
    };
  }, []);

  function completeInstall(nextMessage) {
    window.localStorage.setItem(INSTALL_FLAG_KEY, '1');
    setInstalled(true);
    setMessage(nextMessage);
  }

  function prepareApp() {
    setStage('preparing');
    setMessage('');
    window.setTimeout(() => setStage('ready'), PREPARE_TIME_MS);
  }

  async function requestInstall() {
    if (!installPrompt) {
      completeInstall('Opening bull99exch now...');
      return;
    }

    try {
      await installPrompt.prompt();
      const choice = await installPrompt.userChoice;
      setInstallPrompt(null);

      if (choice.outcome === 'accepted') {
        completeInstall('Close this browser tab and launch bull99exch from its new home-screen icon.');
      } else {
        completeInstall(defaultInstallMessage());
      }
    } catch {
      completeInstall('Opening bull99exch now...');
    }
  }

  if (installed || !isMobileDevice()) return children;

  return (
    <main className="install-gate">
      <section className="install-card" aria-live="polite">
        <img className="install-logo" src="/pwa-192x192.png" alt="bull99exch" />

        {stage === 'landing' && (
          <>
            <p className="install-eyebrow">bull99exch</p>
            <h1>Install bull99exch</h1>
            <p className="install-copy">Set up bull99exch on this device to continue.</p>
            <button className="install-primary" type="button" onClick={prepareApp}>
              Install
            </button>
          </>
        )}

        {stage === 'preparing' && (
          <>
            <div className="install-spinner" aria-hidden="true" />
            <h1>Installing bull99exch</h1>
            <p className="install-copy">Please wait while installation is prepared...</p>
            <div className="install-progress">
              <span />
            </div>
          </>
        )}

        {stage === 'ready' && (
          <>
            <div className="install-check" aria-hidden="true">✓</div>
            <h1>{installed ? 'Installation complete' : 'Ready to install'}</h1>
            <p className="install-copy">
              {installed
                ? 'bull99exch has been added to your device.'
                : 'Confirm installation on your device to complete the setup.'}
            </p>
            {message && <p className="install-message">{message}</p>}
            {!installed && (
              <button className="install-primary" type="button" onClick={requestInstall}>
                Confirm Installation
              </button>
            )}
          </>
        )}
      </section>
    </main>
  );
}
