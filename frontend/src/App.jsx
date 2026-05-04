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
import { getGroups, getNotificationsList, joinGroup } from './services/api';
import { Menu } from 'lucide-react';

function App() {
  const [page, setPage] = useState('landing'); // 'landing' | 'login' | 'app'
  const [theme, setTheme] = useState(() => localStorage.getItem('tontine_theme') || 'dark');
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [user, setUser] = useState({ 
    first_name: "Mourchid", 
    last_name: "F.", 
    npi: "",
    score_confiance: 95, 
    profession: "Commerçant Import-Export",
    preferred_language: 'fr' 
  });
  const [view, setView] = useState('dashboard');
  const [currentGroup, setCurrentGroup] = useState(null);
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [groups, setGroups] = useState([
    { id: 1, code: 'DAN-01', name: "Tontine des Femmes de Dantokpa", amount: 50000, cycle: "Mensuel", members: 10, current_cycle: 4, leader: "Fatou M.", description: "Groupe d'épargne pour les commerçantes du marché" },
    { id: 2, code: 'ART-02', name: "Association des Artisans", amount: 25000, cycle: "Mensuel", members: 8, current_cycle: 2, leader: "Jean D.", description: "Épargne collective pour achats de matériel" },
    { id: 3, code: 'MIA-03', name: "Famille Miabe", amount: 10000, cycle: "Hebdomadaire", members: 5, current_cycle: 1, leader: "Mourchid F.", description: "Tontine familiale pour événements" },
  ]);
  const [notifications, setNotifications] = useState([
    { id: 1, type: 'success', title: 'Paiement confirmé', message: 'Sarah B. a versé sa cotisation dans Tontine Diamant.', time: 'À l\'instant' },
    { id: 2, type: 'info', title: 'Nouveau Membre', message: 'Jean D. a rejoint dans Marché Dantokpa.', time: 'Il y a 2 min' },
    { id: 4, type: 'warning', title: 'Rappel de paiement', message: 'La date limite pour "Association des Artisans" est dans 24 heures.', time: 'Il y a 5h' },
  ]);

  // Theme management
  useEffect(() => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('tontine_theme', theme);
  }, [theme]);

  const toggleTheme = () => setTheme(prev => prev === 'dark' ? 'light' : 'dark');

  // Auth check
  useEffect(() => {
    const token = localStorage.getItem('tontine_token');
    if (token) {
      setIsAuthenticated(true);
      setPage('app');
    }
  }, []);

  const fetchInitialData = async () => {
    try {
      const [groupsRes, notificationsRes] = await Promise.all([
        getGroups().catch(() => ({ data: groups })),
        getNotificationsList().catch(() => ({ data: notifications }))
      ]);
      setGroups(groupsRes.data);
      setNotifications(notificationsRes.data);
    } catch (err) {
      console.error("Erreur de chargement des données", err);
    }
  };

  const handleCreateGroup = (groupData) => {
    const newGroup = {
      id: groups.length + 1,
      ...groupData,
      current_cycle: 1,
      members: groupData.max_members
    };
    setGroups([...groups, newGroup]);
    setNotifications([{
      id: Date.now(), type: 'info', title: 'Groupe Créé',
      message: `La tontine "${groupData.name}" a été créée avec succès.`,
      time: 'À l\'instant'
    }, ...notifications]);
  };

  const handleJoinGroup = async (code) => {
    try {
      await joinGroup(code);
      fetchInitialData();
    } catch (err) {
      const joinedGroup = { id: Date.now(), name: `Groupe ${code}`, amount: 25000, cycle: "Mensuel", members: 12, current_cycle: 1 };
      setGroups([...groups, joinedGroup]);
    }
  };

  const handleLoginSuccess = (userData) => {
    setUser(userData);
    setIsAuthenticated(true);
    setPage('app');
    fetchInitialData();
  };

  const handleLogout = () => {
    localStorage.removeItem('tontine_token');
    setIsAuthenticated(false);
    setPage('landing');
    setView('dashboard');
  };

  // ===== LANDING PAGE =====
  if (page === 'landing' && !isAuthenticated) {
    return <LandingPage onNavigateLogin={() => setPage('login')} theme={theme} toggleTheme={toggleTheme} />;
  }

  // ===== LOGIN =====
  if (page === 'login' && !isAuthenticated) {
    return <Login onLoginSuccess={handleLoginSuccess} />;
  }

  // ===== FULL-SCREEN VIEWS (no sidebar) =====
  if (view === 'assistant_yao') {
    return <AssistantYAO onBack={() => setView('dashboard')} user={user} />;
  }

  if (view === 'messagerie') {
    return <Messagerie onBack={() => setView('dashboard')} user={user} />;
  }

  // KYC barrier
  const isKycComplete = user?.npi && user?.npi.length > 5;
  const effectiveView = isKycComplete ? view : 'profile';

  if (effectiveView === 'group' && currentGroup) {
    return <GroupDetails group={currentGroup} user={user} onBack={() => setView('dashboard')} />;
  }

  if (effectiveView === 'profile') {
    return (
      <Profile 
        user={user} 
        onBack={() => isKycComplete ? setView('dashboard') : alert("Veuillez d'abord renseigner votre NPI pour accéder à vos tontines.")} 
        onUpdate={(updatedUser) => { setUser(updatedUser); if (updatedUser.npi) setView('dashboard'); }}
      />
    );
  }

  if (effectiveView === 'notifications') {
    return <Notifications notifications={notifications} onBack={() => setView('dashboard')} />;
  }

  if (view === 'leaderboard') {
    return <Leaderboard onBack={() => setView('dashboard')} />;
  }

  if (view === 'create_group') {
    return (
      <CreateGroup 
        onBack={() => setView('dashboard')} 
        onCreate={(groupData) => { handleCreateGroup(groupData); setView('mes_tontines'); }}
      />
    );
  }

  if (view === 'mes_tontines') {
    return (
      <MesTontines
        groups={groups}
        onSelectGroup={(group) => { setCurrentGroup(group); setView('group'); }}
        onNewGroup={() => setView('create_group')}
        onBack={() => setView('dashboard')}
      />
    );
  }

  if (view === 'join') {
    return (
      <JoindreGroupe
        onBack={() => setView('dashboard')}
        onJoin={(code) => { handleJoinGroup(code); setView('mes_tontines'); }}
      />
    );
  }

  // ===== MAIN APP WITH SIDEBAR =====
  return (
    <div>
      <Sidebar 
        currentView={view}
        onNavigate={setView}
        onLogout={handleLogout}
        theme={theme}
        toggleTheme={toggleTheme}
        mobileOpen={sidebarOpen}
        onCloseMobile={() => setSidebarOpen(false)}
      />

      {/* Mobile top bar */}
      <div className="md:hidden" style={{ position: 'fixed', top: 0, left: 0, right: 0, zIndex: 30, background: 'var(--bg-secondary)', borderBottom: '1px solid var(--border-color)', padding: '12px 16px', display: 'flex', alignItems: 'center', gap: 12 }}>
        <button onClick={() => setSidebarOpen(true)} style={{ background: 'none', border: 'none', color: 'var(--text-primary)', cursor: 'pointer' }}>
          <Menu size={22} />
        </button>
        <span style={{ fontFamily: 'Playfair Display, serif', fontWeight: 700, fontSize: 16 }}>TontineChain</span>
      </div>

      <div className="main-content" style={{ paddingTop: typeof window !== 'undefined' && window.innerWidth < 768 ? 72 : 24 }}>
        <Dashboard 
          user={user} 
          groups={groups}
          onLogout={handleLogout} 
          onSelectGroup={(group) => { setCurrentGroup(group); setView('group'); }}
          onOpenProfile={() => setView('profile')}
          onNewGroup={() => setView('create_group')}
          onOpenNotifications={() => setView('notifications')}
          onOpenLeaderboard={() => setView('leaderboard')}
          onJoinGroup={handleJoinGroup}
          onNavigate={setView}
        />
      </div>
    </div>
  );
}

export default App;
