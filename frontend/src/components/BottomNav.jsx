import React from 'react';
import { Home, Users, MessageSquare, User, PlusCircle } from 'lucide-react';
import { motion } from 'framer-motion';

const BottomNav = ({ activeTab, onTabChange }) => {
  const tabs = [
    { id: 'dashboard', icon: <Home size={22} />, label: 'Accueil' },
    { id: 'tontines', icon: <Users size={22} />, label: 'Tontines' },
    { id: 'create', icon: <PlusCircle size={28} />, label: '', special: true },
    { id: 'messagerie', icon: <MessageSquare size={22} />, label: 'Chat' },
    { id: 'profile', icon: <User size={22} />, label: 'Profil' },
  ];

  return (
    <nav className="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 px-6 py-2 z-50 flex justify-between items-end h-[75px] safe-area-bottom shadow-[0_-4px_10px_rgba(0,0,0,0.03)]">
      {tabs.map((tab) => (
        <button
          key={tab.id}
          onClick={() => onTabChange(tab.id)}
          className="relative flex flex-col items-center justify-center min-w-[50px] transition-all"
        >
          {tab.special ? (
            <div className="absolute -top-12 bg-TontineChain-orange text-white p-4 rounded-full shadow-lg shadow-orange-500/30 border-4 border-white active:scale-95 transition-transform">
              {tab.icon}
            </div>
          ) : (
            <motion.div
              animate={{ 
                color: activeTab === tab.id ? '#FF8C00' : '#94A3B8',
                y: activeTab === tab.id ? -2 : 0 
              }}
              className="flex flex-col items-center gap-1"
            >
              {tab.icon}
              <span className={`text-[10px] font-bold uppercase tracking-wider ${activeTab === tab.id ? 'text-TontineChain-orange' : 'text-slate-400'}`}>
                {tab.label}
              </span>
            </motion.div>
          )}
          
          {activeTab === tab.id && !tab.special && (
            <motion.div 
              layoutId="activeTab"
              className="absolute -top-2 w-1 h-1 bg-TontineChain-orange rounded-full" 
            />
          )}
        </button>
      ))}
    </nav>
  );
};

export default BottomNav;
