import React, { useState } from 'react';
import { Outlet } from 'react-router-dom';
import Header from '../common/Header';
import Sidebar from '../common/Sidebar';
import Footer from '../common/Footer';

export default function DashboardLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(true);

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
      <Header onSidebarToggle={() => setSidebarOpen(!sidebarOpen)} isSidebarOpen={sidebarOpen} />

      <div className="flex flex-1 relative pt-16">
        <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

        <main className={`flex-1 transition-all duration-300 ${sidebarOpen ? 'lg:pl-64' : 'pl-0'}`}>
          <div className="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto min-h-[calc(100vh-9rem)]">
            <Outlet />
          </div>
          <Footer />
        </main>
      </div>
    </div>
  );
}
