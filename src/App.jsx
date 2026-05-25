import { useEffect, useMemo, useState } from 'react';
import { AuthProvider, useAuth } from './context/AuthContext.jsx';
import Login from './pages/Login.jsx';
import ParentSelection from './pages/ParentSelection.jsx';
import DashboardLayout from './components/DashboardLayout.jsx';
import DataEntry from './pages/DataEntry.jsx';
import ViewResults from './pages/ViewResults.jsx';
import Reports from './pages/Reports.jsx';
import Home from './pages/Home.jsx';

function AppRoutes() {
  const { user, parentSelection, loading } = useAuth();
  const [activePage, setActivePage] = useState('home');
  const pages = {
    home: () => <Home onNavigate={setActivePage} />,
    entry: () => <DataEntry initialMode="Num-Akhar" />,
    fromto: () => <DataEntry initialMode="From-To" />,
    cross: () => <DataEntry initialMode="Cross" />,
    jantri: () => <DataEntry initialMode="Jantri" />,
    results: ViewResults,
    hisab: () => <Reports initialView="hisab" />,
    statement: () => <Reports initialView="statement" />
  };
  const Page = pages[activePage] || pages.home;

  useEffect(() => {
    if (!user) setActivePage('home');
  }, [user]);

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
    <DashboardLayout activePage={activePage} onNavigate={setActivePage}>
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
    <AuthProvider>
      <AppRoutes />
    </AuthProvider>
  );
}
