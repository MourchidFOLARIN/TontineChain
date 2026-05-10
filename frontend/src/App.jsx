import React, { useState, useEffect } from 'react';
import LandingPage from './components/LandingPage';
import Login from './components/Login';
import Sidebar from './components/Sidebar';
import Dashboard from './components/Dashboard';
import GroupDetails from './components/GroupDetails';
import Profile from './components/Profile';
import CreateGroup from './components/CreateGroup';
import Notifications from './components/Notifications';
import Leaderboard from './components/Leaderboard';
import AssistantYAO from './components/AssistantYAO';
import Messagerie from './components/Messagerie';
import MesTontines from './components/MesTontines';
import JoindreGroupe from './components/JoindreGroupe';
import DemoNotice from './components/DemoNotice';
import api from './services/api';
import { getGroups, getNotificationsList, joinGroup } from './services/api';
import { Menu, Bell } from 'lucide-react';

function App() {
  const [page, setPage] = useState(() => localStorage.getItem('tontine_page') || 'landing');
  const [theme, setTheme] = useState(() => localStorage.getItem('tontine_theme') || 'dark');
  const [isAuthenticated, setIsAuthenticated] = useState(() => !!localStorage.getItem('tontine_token'));
  
  const [user, setUser] = useState(() => {
    const saved = localStorage.getItem('tontine_user');
    return saved ? JSON.parse(saved) : { 
      first_name: "Mourchid", 
      last_name: "F.", 
      npi: "",
      score_confiance: 95, 
      profession: "Commerçant Import-Export",
      preferred_language: 'fr' 
    };
  });

  const [view, setView] = useState(() => localStorage.getItem('tontine_view') || 'dashboard');
  const [currentGroup, setCurrentGroup] = useState(null);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [demoNotice, setDemoNotice] = useState(null);

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

  const [groups, setGroups] = useState(() => {
    const saved = localStorage.getItem('tontine_groups');
    return saved ? JSON.parse(saved) : [
      { id: 1, code: 'DAN-01', name: "Tontine des Femmes de Dantokpa", amount: 50000, cycle: "Mensuel", members: 10, current_cycle: 4, leader: "Fatou M.", description: "Groupe d'épargne pour les commerçantes du marché" },
      { id: 2, code: 'ART-02', name: "Association des Artisans", amount: 25000, cycle: "Mensuel", members: 8, current_cycle: 2, leader: "Jean D.", description: "Épargne collective pour achats de matériel" },
      { id: 3, code: 'MIA-03', name: "Famille Miabe", amount: 10000, cycle: "Hebdomadaire", members: 5, current_cycle: 1, leader: "Mourchid F.", description: "Tontine familiale pour événements" },
    ];
  });

  const [notifications, setNotifications] = useState(() => {
    const saved = localStorage.getItem('tontine_notifications');
    return saved ? JSON.parse(saved) : [
      { id: 1, type: 'success', title: 'Paiement confirmé', message: 'Sarah B. a versé sa cotisation dans Tontine Diamant.', time: 'À l\'instant' },
      { id: 2, type: 'info', title: 'Nouveau Membre', message: 'Jean D. a rejoint dans Marché Dantokpa.', time: 'Il y a 2 min' },
      { id: 4, type: 'warning', title: 'Rappel de paiement', message: 'La date limite pour "Association des Artisans" est dans 24 heures.', time: 'Il y a 5h' },
    ];
  });

  // Persistence
  useEffect(() => {
    localStorage.setItem('tontine_page', page);
    localStorage.setItem('tontine_view', view);
    localStorage.setItem('tontine_groups', JSON.stringify(groups));
    localStorage.setItem('tontine_user', JSON.stringify(user));
    localStorage.setItem('tontine_notifications', JSON.stringify(notifications));
  }, [page, view, groups, user, notifications]);

  // Theme management
  useEffect(() => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('tontine_theme', theme);
  }, [theme]);

  const toggleTheme = () => setTheme(prev => prev === 'dark' ? 'light' : 'dark');

  const handleCreateGroup = (groupData) => {
    const newGroup = {
      id: Date.now(),
      ...groupData,
      current_cycle: 1,
      members: groupData.max_members || 10,
      leader: `${user.first_name} ${user.last_name[0]}.`
    };
    setGroups([newGroup, ...groups]);
    addNotification('info', 'Groupe Créé', `La tontine "${groupData.name}" a été créée avec succès.`);
  };

  const addNotification = (type, title, message) => {
    setNotifications([{ id: Date.now(), type, title, message, time: 'À l\'instant' }, ...notifications]);
  };

  const handleJoinGroup = (code) => {
    const alreadyJoined = groups.find(g => g.code === code);
    if (alreadyJoined) {
      alert("Vous êtes déjà membre de ce groupe.");
      return;
    }
    const fakeGroup = { 
      id: Date.now(), 
      code, 
      name: `Tontine ${code}`, 
      amount: 25000, 
      cycle: "Mensuel", 
      members: 12, 
      current_cycle: 1,
      leader: "Admin"
    };
    setGroups([fakeGroup, ...groups]);
    addNotification('success', 'Bienvenue !', `Vous avez rejoint le groupe ${code}.`);
  };

  const handleLoginSuccess = (userData) => {
    // Le token est déjà stocké par Login.jsx
    setUser(prev => ({ ...prev, ...userData }));
    setIsAuthenticated(true);
    setPage('app');
    setView('dashboard');
  };

  const handleLogout = () => {
    localStorage.removeItem('tontine_token');
    setIsAuthenticated(false);
    setPage('landing');
    setView('dashboard');
  };

  // Nav items for consistency
  const navItems = [
    { id: 'dashboard', label: 'Tableau de Bord', icon: <DashboardIcon /> },
    { id: 'mes_tontines', label: 'Mes Tontines', icon: <TontineIcon /> },
    { id: 'leaderboard', label: 'Elite Leaderboard', icon: <TrophyIcon /> },
    { id: 'assistant_yao', label: 'Assistant YAO', icon: <AssistantIcon /> }
  ];

  // Helper icons
  function DashboardIcon() { return <svg className="bottom-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>; }
  function TontineIcon() { return <svg className="bottom-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>; }
  function TrophyIcon() { return <svg className="bottom-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>; }
  function AssistantIcon() { return <svg className="bottom-nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>; }

  // ===== RENDER LOGIC =====
  if (page === 'landing' && !isAuthenticated) {
    return <LandingPage onNavigateLogin={() => setPage('login')} theme={theme} toggleTheme={toggleTheme} />;
  }

  if (page === 'login' && !isAuthenticated) {
    return <Login onLoginSuccess={handleLoginSuccess} onBack={() => setPage('landing')} />;
  }

  // Determine current component
  let content;
  const isKycComplete = user?.npi && user?.npi.length > 5;
  const effectiveView = (isKycComplete || ['profile', 'assistant_yao'].includes(view)) ? view : 'profile';

  switch (effectiveView) {
    case 'assistant_yao': content = <AssistantYAO onBack={() => setView('dashboard')} user={user} />; break;
    case 'messagerie': content = <Messagerie onBack={() => setView('dashboard')} user={user} />; break;
    case 'group': content = <GroupDetails group={currentGroup} user={user} onBack={() => setView('dashboard')} />; break;
    case 'profile': content = <Profile user={user} onBack={() => setView('dashboard')} onUpdate={(u) => { setUser(u); if (u.npi) setView('dashboard'); }} />; break;
    case 'notifications': content = <Notifications notifications={notifications} onBack={() => setView('dashboard')} />; break;
    case 'leaderboard': content = <Leaderboard onBack={() => setView('dashboard')} />; break;
    case 'create_group': content = <CreateGroup onBack={() => setView('dashboard')} onCreate={(d) => { handleCreateGroup(d); setView('mes_tontines'); }} />; break;
    case 'mes_tontines': content = <MesTontines groups={groups} onSelectGroup={(g) => { setCurrentGroup(g); setView('group'); }} onNewGroup={() => setView('create_group')} onBack={() => setView('dashboard')} />; break;
    case 'join': content = <JoindreGroupe onBack={() => setView('dashboard')} onJoin={(c) => { handleJoinGroup(c); setView('mes_tontines'); }} />; break;
    default: content = <Dashboard user={user} groups={groups} onLogout={handleLogout} onSelectGroup={(g) => { setCurrentGroup(g); setView('group'); }} onOpenProfile={() => setView('profile')} onNewGroup={() => setView('create_group')} onOpenNotifications={() => setView('notifications')} onOpenLeaderboard={() => setView('leaderboard')} onJoinGroup={handleJoinGroup} onNavigate={setView} />;
  }

  return (
    <div className="app-container">
      <Sidebar 
        currentView={view}
        onNavigate={setView}
        onLogout={handleLogout}
        theme={theme}
        toggleTheme={toggleTheme}
        mobileOpen={sidebarOpen}
        onCloseMobile={() => setSidebarOpen(false)}
      />

      {/* Mobile Header */}
      <div className="md:hidden fixed top-0 left-0 right-0 z-30 glass-panel px-6 py-4 flex justify-between items-center">
        <div className="flex items-center gap-3">
          <button onClick={() => setSidebarOpen(true)} className="text-tontine-orange">
            <Menu size={24} />
          </button>
          <span className="font-playfair font-bold text-lg">TontineChain</span>
        </div>
        <button onClick={() => setView('notifications')} className="relative">
          <Bell size={20} className="text-gray-400" />
          {notifications.length > 0 && <span className="absolute -top-1 -right-1 w-2 h-2 bg-tontine-orange rounded-full" />}
        </button>
      </div>

      <div className="main-content">
        {content}
      </div>

      <DemoNotice 
        notice={demoNotice} 
        onClose={() => setDemoNotice(null)} 
      />

      {/* Bottom Nav (Mobile Only) */}
      <div className="md:hidden bottom-nav">
        {navItems.map(item => (
          <div 
            key={item.id} 
            className={`bottom-nav-item ${view === item.id ? 'active' : ''}`}
            onClick={() => setView(item.id)}
          >
            {item.icon}
            <span>{item.id === 'dashboard' ? 'Accueil' : item.label.split(' ')[0]}</span>
          </div>
        ))}
      </div>
    </div>
  );
}

export default App;
