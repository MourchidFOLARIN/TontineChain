import React from 'react';
import { motion } from 'framer-motion';
import { ArrowLeft, Users, TrendingUp, ShieldCheck, Clock, ChevronRight, Plus, Search } from 'lucide-react';

const MesTontines = ({ groups, onSelectGroup, onNewGroup, onBack }) => {
  const getProgressColor = (pct) => {
    if (pct >= 75) return 'text-green-400';
    if (pct >= 40) return 'text-tontine-orange';
    return 'text-blue-400';
  };

  const getProgressBg = (pct) => {
    if (pct >= 75) return 'bg-green-400';
    if (pct >= 40) return 'bg-tontine-orange';
    return 'bg-blue-400';
  };

  return (
    <div className="flex flex-col h-full bg-tontine-darker min-h-screen">
      {/* Header */}
      <div className="p-4 md:p-6 glass-panel flex items-center justify-between border-b border-white/5 sticky top-0 z-30">
        <div className="flex items-center gap-4">
          <button onClick={onBack} className="p-2 hover:bg-white/5 rounded-full transition-colors text-gray-400 hover:text-white">
            <ArrowLeft size={22} />
          </button>
          <div>
            <h2 className="text-xl md:text-2xl font-bold font-playfair">Mes Tontines</h2>
            <p className="text-[10px] md:text-xs text-gray-500 uppercase font-black tracking-widest">
              {groups.length} Groupe{groups.length > 1 ? 's' : ''} Actif{groups.length > 1 ? 's' : ''}
            </p>
          </div>
        </div>
        <button onClick={onNewGroup} className="btn-primary p-3 md:px-6 md:py-3 rounded-2xl flex items-center gap-2 text-xs md:text-sm">
          <Plus size={18} /> <span className="hidden sm:inline">Créer</span>
        </button>
      </div>

      {/* Main Content */}
      <div className="flex-1 p-4 md:p-8 max-w-5xl mx-auto w-full">
        
        {/* Search Bar (Static for UI) */}
        <div className="relative mb-8">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500" size={18} />
          <input 
            type="text" 
            placeholder="Rechercher une tontine..." 
            className="w-full bg-white/5 border border-white/10 rounded-2xl py-4 pl-12 pr-6 outline-none focus:ring-2 focus:ring-tontine-orange transition-all text-sm"
          />
        </div>

        {groups.length === 0 ? (
          <div className="flex flex-col items-center justify-center pt-20 text-center">
            <div className="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mb-6 border border-white/5">
              <Users size={40} className="text-gray-600" />
            </div>
            <h3 className="text-xl font-bold mb-2">Aucune tontine trouvée</h3>
            <p className="text-sm text-gray-500 mb-8 max-w-xs">
              Vous n'avez pas encore rejoint de tontine. Commencez par en créer une ou rejoignez un groupe.
            </p>
            <button onClick={onNewGroup} className="btn-primary px-8 py-4 rounded-2xl font-bold shadow-xl shadow-tontine-orange/20">
              Démarrer Maintenant
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            {groups.map((group, i) => {
              const pct = Math.round((group.current_cycle / group.members) * 100);
              const colorClass = getProgressColor(pct);
              const bgClass = getProgressBg(pct);
              
              return (
                <motion.div
                  key={group.id}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: i * 0.1 }}
                  onClick={() => onSelectGroup(group)}
                  className="glass-panel p-6 rounded-3xl hover:border-tontine-orange/30 transition-all cursor-pointer group flex flex-col justify-between"
                >
                  <div>
                    <div className="flex justify-between items-start mb-6">
                      <div className="flex items-center gap-4">
                        <div className={`w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center group-hover:scale-110 transition-transform ${colorClass}`}>
                          <Users size={24} />
                        </div>
                        <div className="min-w-0 pr-4">
                          <h3 className="font-bold text-base md:text-lg truncate group-hover:text-tontine-orange transition-colors">{group.name}</h3>
                          <span className="text-[10px] text-gray-500 uppercase font-black tracking-widest">{group.cycle}</span>
                        </div>
                      </div>
                      <div className="text-right flex-shrink-0">
                        <div className="text-lg font-black text-white">{group.amount.toLocaleString()} <span className="text-xs text-tontine-gold">F</span></div>
                        <p className="text-[10px] text-gray-600 font-bold">{group.members} MEMBRES</p>
                      </div>
                    </div>

                    <div className="space-y-3 mb-8">
                      <div className="flex justify-between text-[10px] font-bold uppercase tracking-tighter">
                        <span className="text-gray-500">Collecte Cycle {group.current_cycle}/{group.members}</span>
                        <span className={colorClass}>{pct}%</span>
                      </div>
                      <div className="w-full h-1.5 bg-white/5 rounded-full overflow-hidden">
                        <div 
                          className={`h-full ${bgClass}`} 
                          style={{ width: `${pct}%` }}
                        />
                      </div>
                    </div>
                  </div>

                  <div className="flex justify-between items-center pt-4 border-t border-white/5">
                    <div className="flex items-center gap-1.5 text-[10px] text-gray-500">
                      <ShieldCheck size={14} className="text-green-500" />
                      <span>Immuable</span>
                    </div>
                    <div className="flex items-center gap-1 text-xs font-bold text-tontine-orange opacity-0 group-hover:opacity-100 transition-opacity">
                      Gérer <ChevronRight size={14} />
                    </div>
                  </div>
                </motion.div>
              );
            })}
          </div>
        )}
      </div>

      {/* Footer Space for Bottom Nav */}
      <div className="h-20 md:hidden" />
    </div>
  );
};

export default MesTontines;
