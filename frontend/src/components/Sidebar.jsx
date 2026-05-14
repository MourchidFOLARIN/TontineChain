import React from 'react';
import { LayoutDashboard, FolderOpen, PlusCircle, UserPlus, CreditCard, MessageCircle, Bot, User, Settings, LogOut, Sun, Moon, ShieldCheck, X } from 'lucide-react';
import logoOfficial from '../assets/logo_official.png';

const Sidebar = ({ currentView, onNavigate, onLogout, theme, toggleTheme, mobileOpen, onCloseMobile }) => {
  const links = [
    { id: 'dashboard', icon: <LayoutDashboard size={18} />, label: 'Dashboard' },
    { id: 'mes_tontines', icon: <FolderOpen size={18} />, label: 'Mes Tontines' },
    { id: 'create_group', icon: <PlusCircle size={18} />, label: 'Créer une Tontine' },
    { id: 'join', icon: <UserPlus size={18} />, label: 'Rejoindre' },
    { divider: true },
    { id: 'messagerie', icon: <MessageCircle size={18} />, label: 'Messagerie', badge: 3 },
    { id: 'assistant_yao', icon: <Bot size={18} />, label: 'Assistant YAO', badgeNew: true },
    { divider: true },
    { id: 'profile', icon: <User size={18} />, label: 'Mon Profil' },
  ];

  return (
    <>
      {/* Overlay for mobile */}
      {mobileOpen && (
        <div onClick={onCloseMobile} style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', zIndex: 39 }} className="md:hidden" />
      )}
      
      <aside className={`sidebar ${mobileOpen ? 'open' : ''}`}>
        {/* Logo */}
        <div style={{ padding: '24px 20px 20px', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <img src={logoOfficial} alt="TontineChain" style={{ width: 44, height: 44, borderRadius: '50%', border: '2px solid var(--TontineChain-orange)' }} />
            <span style={{ fontFamily: 'Playfair Display, serif', fontWeight: 800, fontSize: 18, color: 'var(--TontineChain-blue)' }}>TontineChain</span>
          </div>
          <button onClick={onCloseMobile} className="md:hidden" style={{ background: 'none', border: 'none', color: 'var(--text-muted)', cursor: 'pointer' }}>
            <X size={20} />
          </button>
        </div>

        {/* Navigation */}
        <nav style={{ padding: '8px 12px', flex: 1 }}>
          {links.map((link, i) => {
            if (link.divider) return <div key={i} style={{ height: 1, background: 'var(--border-color)', margin: '10px 4px' }} />;
            return (
              <div key={link.id}
                className={`sidebar-link ${currentView === link.id ? 'active' : ''}`}
                onClick={() => { onNavigate(link.id); onCloseMobile?.(); }}>
                {link.icon}
                <span>{link.label}</span>
                {link.badge && (
                  <span style={{ marginLeft: 'auto', width: 20, height: 20, borderRadius: '50%', background: 'linear-gradient(135deg, #f39c12, #f1c40f)', color: '#0b1120', fontSize: 11, fontWeight: 700, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    {link.badge}
                  </span>
                )}
                {link.badgeNew && <span className="badge badge-new" style={{ marginLeft: 'auto' }}>NEW</span>}
              </div>
            );
          })}
        </nav>

        {/* Bottom section */}
        <div style={{ padding: '12px', borderTop: '1px solid var(--border-color)' }}>
          {/* Theme toggle */}
          <div className="sidebar-link" onClick={toggleTheme} style={{ marginBottom: 4 }}>
            {theme === 'dark' ? <Sun size={18} /> : <Moon size={18} />}
            <span>{theme === 'dark' ? 'Mode Clair' : 'Mode Sombre'}</span>
          </div>
          {/* Logout */}
          <div className="sidebar-link" onClick={onLogout} style={{ color: '#ef4444' }}>
            <LogOut size={18} />
            <span>Déconnexion</span>
          </div>
        </div>
      </aside>
    </>
  );
};

export default Sidebar;
