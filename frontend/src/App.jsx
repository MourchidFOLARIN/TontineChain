import React, { useState, useEffect } from 'react';
import LandingPage from './components/LandingPage';
import Login from './components/Login';
import BottomNav from './components/BottomNav';
import Dashboard from './components/Dashboard';
import GroupDetails from './components/GroupDetails';
import Profile from './components/Profile';
import CreateGroup from './components/CreateGroup';
import Notifications from './components/Notifications';
import AssistantYAO from './components/AssistantYAO';
import Messagerie from './components/Messagerie';
import MesTontines from './components/MesTontines';
import JoindreGroupe from './components/JoindreGroupe';
import DemoNotice from './components/DemoNotice';
import api from './services/api';
import { Bell, Search } from 'lucide-react';

import logoOfficial from './assets/logo_official.png';

function App() {
  const [page, setPage] = useState(() => localStorage.getItem('tontine_page') || 'landing');
  const [isAuthenticated, setIsAuthenticated] = useState(() => !!localStorage.getItem('tontine_token'));
  const [view, setView] = useState(() => localStorage.getItem('tontine_view') || 'dashboard');
  const [currentGroup, setCurrentGroup] = useState(null);
  const [demoNotice, setDemoNotice] = useState(null);
  
  const [user, setUser] = useState(() => {
    const saved = localStorage.getItem('tontine_user');
    return saved ? JSON.parse(saved) : null;
  });

  const [groups, setGroups] = useState(() => {
    const saved = localStorage.getItem('tontine_groups');
    return saved ? JSON.parse(saved) : [];
  });

  const [notifications, setNotifications] = useState([]);

  // Persistence
  useEffect(() => {
    localStorage.setItem('tontine_page', page);
    localStorage.setItem('tontine_view', view);
    if (user) localStorage.setItem('tontine_user', JSON.stringify(user));
  }, [page, view, user]);

  // Global Demo Notice Listener
  useEffect(() => {
    const interceptor = api.interceptors.response.use(
      response => {
        if (response.data && response.data.demo_notice) {
          setDemoNotice(response.data.demo_notice);
        }
        return response;
      },
      error => {
        if (error.response && error.response.data && error.response.data.demo_notice) {
          setDemoNotice(error.response.data.demo_notice);
        }
        return Promise.reject(error);
      }
    );
    return () => api.interceptors.response.eject(interceptor);
  }, []);

  const handleLoginSuccess = (userData) => {
    setUser(userData);
    setIsAuthenticated(true);
    setPage('app');
    setView('dashboard');
  };

  const handleLogout = () => {
    localStorage.removeItem('tontine_token');
    localStorage.removeItem('tontine_user');
    setIsAuthenticated(false);
    setPage('landing');
  };

  // ===== RENDER LOGIC =====
  if (page === 'landing' && !isAuthenticated) {
    return <LandingPage onNavigateLogin={() => setPage('login')} theme="light" toggleTheme={() => {}} />;
  }

  if (page === 'login' && !isAuthenticated) {
    return <Login onLoginSuccess={handleLoginSuccess} onBack={() => setPage('landing')} />;
  }

  let content;
  switch (view) {
    case 'assistant_yao': content = <AssistantYAO onBack={() => setView('dashboard')} user={user} />; break;
    case 'messagerie': content = <Messagerie onBack={() => setView('dashboard')} user={user} />; break;
    case 'group': content = <GroupDetails group={currentGroup} user={user} onBack={() => setView('dashboard')} />; break;
    case 'profile': content = <Profile user={user} onBack={() => setView('dashboard')} onUpdate={(u) => { setUser(u); setView('dashboard'); }} />; break;
    case 'notifications': content = <Notifications notifications={notifications} onBack={() => setView('dashboard')} />; break;
    case 'create': content = <CreateGroup onBack={() => setView('dashboard')} onCreate={() => setView('tontines')} />; break;
    case 'tontines': content = <MesTontines groups={groups} onSelectGroup={(g) => { setCurrentGroup(g); setView('group'); }} onNewGroup={() => setView('create')} onBack={() => setView('dashboard')} />; break;
    default: content = <Dashboard user={user} onSelectGroup={(g) => { setCurrentGroup(g); setView('group'); }} onNavigate={setView} />;
  }

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col">
      
      {/* Top Header TontineChain Style */}
      {view !== 'assistant_yao' && (
        <header className="fixed top-0 left-0 right-0 h-[70px] bg-white border-b border-slate-100 z-40 px-6 flex justify-between items-center shadow-sm">
          <div className="flex items-center gap-3">
            <img src={logoOfficial} alt="Logo" className="w-10 h-10 rounded-full border-2 border-TontineChain-orange shadow-md" />
            <div>
              <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none">Bonjour,</p>
              <h2 className="text-sm font-bold text-slate-800">{user?.first_name || 'Utilisateur'}</h2>
            </div>
          </div>
          
          <div className="flex items-center gap-4">
            <button className="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:text-TontineChain-orange transition-colors">
              <Search size={20} />
            </button>
            <button onClick={() => setView('notifications')} className="relative w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:text-TontineChain-orange transition-colors">
              <Bell size={20} />
              {notifications.length > 0 && <span className="absolute top-2.5 right-2.5 w-2 h-2 bg-TontineChain-orange rounded-full border-2 border-white" />}
            </button>
          </div>
        </header>
      )}

      {/* Main Content Area */}
      <main className={`flex-1 w-full max-w-lg mx-auto ${view !== 'assistant_yao' ? 'pt-[90px] pb-[100px]' : ''}`}>
        {content}
      </main>

      {/* TontineChain Navigation */}
      {view !== 'assistant_yao' && (
        <BottomNav activeTab={view} onTabChange={setView} />
      )}

      {/* Demo Notice */}
      <DemoNotice notice={demoNotice} onClose={() => setDemoNotice(null)} />
    </div>
  );
}

export default App;
