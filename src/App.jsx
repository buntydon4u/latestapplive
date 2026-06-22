import { useEffect, useMemo, useState } from 'react';
import { AuthProvider, useAuth } from './context/AuthContext.jsx';
import Login from './pages/Login.jsx';
import ParentSelection from './pages/ParentSelection.jsx';
import DashboardLayout from './components/DashboardLayout.jsx';
import DataEntry from './pages/DataEntry.jsx';
import ViewResults from './pages/ViewResults.jsx';
import Hisab from './pages/Hisab.jsx';
import Statement from './pages/Statement.jsx';
import Home from './pages/Home.jsx';
import Wallet from './pages/Wallet.jsx';
import GameHistory from './pages/GameHistory.jsx';
import ChartResults from './pages/ChartResults.jsx';
import PartyJantri from './pages/PartyJantri.jsx';
import AccountProfile from './pages/AccountProfile.jsx';
import ChangePassword from './pages/ChangePassword.jsx';
import WebPhoneFrame from './components/WebPhoneFrame.jsx';

const pagePaths = {
  home: '/',
  wallet: '/wallet',
  history: '/history',
  chart: '/chart',
  entry: '/entry',
  fromto: '/entry/from-to',
  cross: '/entry/cross',
  jantri: '/jantri/party',
  results: '/results',
  hisab: '/reports/hisab',
  statement: '/reports/statement',
  accountProfile: '/account/profile',
  changePassword: '/account/change-password'
};

const pathPages = Object.fromEntries(Object.entries(pagePaths).map(([page, path]) => [path, page]));

function pageFromPath() {
  return pathPages[window.location.pathname] || 'home';
}

function AppRoutes() {
  const { user, parentSelection, loading } = useAuth();
  const [activePage, setActivePage] = useState(pageFromPath);
  const [selectedShiftId, setSelectedShiftId] = useState('');

  function navigate(page) {
    setActivePage(page);
    const path = pagePaths[page];
    if (path && window.location.pathname !== path) {
      window.history.pushState({ page }, '', path);
    }
  }

  function navigateToEntry(shiftId, mode = 'entry') {
    setSelectedShiftId(shiftId ? String(shiftId) : '');
    navigate(mode);
  }

  const pages = {
    home: () => <Home onNavigate={navigate} onPlayShift={navigateToEntry} />,
    wallet: Wallet,
    history: GameHistory,
    chart: ChartResults,
    entry: () => <DataEntry initialMode="Entry" initialShift={selectedShiftId} />,
    fromto: () => <DataEntry initialMode="From-To" initialShift={selectedShiftId} />,
    cross: () => <DataEntry initialMode="Cross" initialShift={selectedShiftId} />,
    jantri: PartyJantri,
    results: ViewResults,
    hisab: Hisab,
    statement: Statement,
    accountProfile: AccountProfile,
    changePassword: ChangePassword
  };
  const Page = pages[activePage] || pages.home;

  useEffect(() => {
    if (!user) {
      setActivePage('home');
      setSelectedShiftId('');
    }
  }, [user]);

  useEffect(() => {
    function handlePopState() {
      setActivePage(pageFromPath());
    }

    window.addEventListener('popstate', handlePopState);
    return () => window.removeEventListener('popstate', handlePopState);
  }, []);

  if (loading) {
    return <div className="boot-screen"><span className="loader" />Loading</div>;
  }

  if (parentSelection) {
    return <ParentSelection />;
  }

  if (!user) {
    return <Login />;
  }

  return (
    <DashboardLayout activePage={activePage} onNavigate={navigate}>
      {typeof Page === 'function' ? <Page /> : <Page />}
    </DashboardLayout>
  );
}

export default function App() {
  const prefersDark = useMemo(() => window.matchMedia?.('(prefers-color-scheme: dark)').matches, []);

  useEffect(() => {
    document.documentElement.classList.toggle('dark', prefersDark);
  }, [prefersDark]);

  return (
    <WebPhoneFrame>
      <AuthProvider>
        <AppRoutes />
      </AuthProvider>
    </WebPhoneFrame>
  );
}
