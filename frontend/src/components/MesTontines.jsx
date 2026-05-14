import React from 'react';
import { motion } from 'framer-motion';
import { ArrowLeft, Users, ShieldCheck, ChevronRight, Plus, Search } from 'lucide-react';

const MesTontines = ({ groups, onSelectGroup, onNewGroup, onBack }) => {
  return (
    <div className="bg-slate-50 min-h-screen">
      {/* Header */}
      <div className="p-6 bg-white border-b border-slate-100 flex items-center justify-between sticky top-0 z-30">
        <div className="flex items-center gap-4">
          <button onClick={onBack} className="p-2 hover:bg-slate-50 rounded-full transition-colors text-slate-400">
            <ArrowLeft size={22} />
          </button>
          <div>
            <h2 className="text-xl font-bold text-slate-800">Mes Tontines</h2>
            <p className="text-[10px] text-slate-400 uppercase font-black tracking-widest leading-none mt-1">
              {groups.length} Groupe{groups.length > 1 ? 's' : ''} Actif{groups.length > 1 ? 's' : ''}
            </p>
          </div>
        </div>
        <button onClick={onNewGroup} className="w-10 h-10 bg-orange-50 text-TontineChain-orange rounded-full flex items-center justify-center shadow-sm">
          <Plus size={20} strokeWidth={3} />
        </button>
      </div>

      {/* Main Content */}
      <div className="p-6 max-w-lg mx-auto">
        
        {/* Search Bar */}
        <div className="relative mb-8">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300" size={18} />
          <input 
            type="text" 
            placeholder="Rechercher une tontine..." 
            className="TontineChain-input pl-12"
          />
        </div>

        {groups.length === 0 ? (
          <div className="flex flex-col items-center justify-center pt-20 text-center">
            <div className="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-6">
              <Users size={32} className="text-slate-300" />
            </div>
            <h3 className="text-lg font-bold text-slate-800 mb-2">Aucune tontine</h3>
            <p className="text-sm text-slate-400 mb-8 max-w-[200px] mx-auto leading-relaxed">
              Vous n'avez pas encore rejoint de tontine pour le moment.
            </p>
            <button onClick={onNewGroup} className="btn-TontineChain w-full justify-center">
              Démarrer Maintenant
            </button>
          </div>
        ) : (
          <div className="space-y-4">
            {groups.map((group, i) => {
              const pct = Math.round((group.current_cycle / group.members) * 100);
              
              return (
                <motion.div
                  key={group.id}
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: i * 0.05 }}
                  onClick={() => onSelectGroup(group)}
                  className="TontineChain-card p-5 cursor-pointer flex items-center gap-4"
                >
                  <div className="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 flex-shrink-0 font-bold text-xl">
                    {group.name[0]}
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="flex justify-between items-start">
                       <h3 className="font-bold text-slate-800 truncate mb-1">{group.name}</h3>
                       <span className="text-[11px] font-black text-slate-800">{group.amount.toLocaleString()} F</span>
                    </div>
                    <div className="flex items-center gap-2 mb-3">
                      <span className="text-[9px] font-bold text-TontineChain-orange bg-orange-50 px-2 py-0.5 rounded uppercase">{group.cycle}</span>
                      <span className="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{group.members} Membres</span>
                    </div>
                    
                    {/* Progress */}
                    <div className="w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                      <div 
                        className="h-full bg-TontineChain-orange" 
                        style={{ width: `${pct || 10}%` }}
                      />
                    </div>
                  </div>
                  <ChevronRight size={18} className="text-slate-200" />
                </motion.div>
              );
            })}
          </div>
        )}
      </div>

      <div className="h-24" />
    </div>
  );
};

export default MesTontines;
