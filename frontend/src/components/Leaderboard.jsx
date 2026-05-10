import React from 'react';
import { motion } from 'framer-motion';
import { Trophy, ArrowLeft, Medal, Star, TrendingUp, ShieldCheck, MapPin, ChevronRight, Activity } from 'lucide-react';
import AudioButton from './AudioButton';

const Leaderboard = ({ onBack }) => {
  const players = [
    { id: 1, name: "Koffi Adjovi", score: 99, city: "Cotonou", rank: 1, trend: '+0.5' },
    { id: 2, name: "Amina Soule", score: 97, city: "Parakou", rank: 2, trend: '+1.2' },
    { id: 3, name: "Mourchid F.", score: 95, city: "Abomey", rank: 3, trend: 'STABLE' },
    { id: 4, name: "Jean Dogbe", score: 92, city: "Porto-Novo", rank: 4, trend: '-0.2' },
    { id: 5, name: "Sarah B.", score: 89, city: "Ouidah", rank: 5, trend: '+2.1' },
    { id: 6, name: "Paul K.", score: 88, city: "Cotonou", rank: 6, trend: 'STABLE' },
    { id: 7, name: "Fati H.", score: 85, city: "Natitingou", rank: 7, trend: '-1.5' },
    { id: 8, name: "Gildas M.", score: 84, city: "Bohicon", rank: 8, trend: '+0.8' },
  ];

  return (
    <div className="flex flex-col h-full bg-tontine-darker min-h-screen">
      {/* Header */}
      <div className="p-4 md:p-6 glass-panel flex items-center justify-between border-b border-white/5 sticky top-0 z-30">
        <div className="flex items-center gap-4">
          <button onClick={onBack} className="p-2 hover:bg-white/5 rounded-full transition-colors text-gray-400 hover:text-white">
            <ArrowLeft size={22} />
          </button>
          <div>
            <h2 className="text-xl md:text-2xl font-bold font-playfair flex items-center gap-2 text-tontine-gold">
              Elite Leaderboard
              <Trophy size={20} />
            </h2>
            <p className="text-[10px] md:text-xs text-gray-500 uppercase font-black tracking-widest flex items-center gap-2">
              <Activity size={10} className="text-tontine-orange" /> Les membres les plus fiables
            </p>
          </div>
        </div>
        <AudioButton label="Consultez le classement des membres les plus fidèles du réseau." />
      </div>

      {/* Hero Podiums (Desktop/Tablet) */}
      <div className="hidden sm:flex justify-center items-end gap-4 p-8 bg-gradient-to-b from-tontine-gold/5 to-transparent border-b border-white/5">
        {/* Rank 2 */}
        <motion.div initial={{ height: 0 }} animate={{ height: 140 }} className="w-32 glass-panel border-tontine-gold/20 flex flex-col items-center justify-end pb-4 rounded-t-2xl">
          <div className="w-12 h-12 bg-gray-300 rounded-full mb-2 border-2 border-white/10" />
          <span className="text-[10px] font-black text-gray-400 uppercase">Amina S.</span>
          <span className="text-sm font-bold text-gray-300">#2</span>
        </motion.div>
        {/* Rank 1 */}
        <motion.div initial={{ height: 0 }} animate={{ height: 180 }} className="w-40 glass-panel border-tontine-gold/40 flex flex-col items-center justify-end pb-6 rounded-t-3xl bg-tontine-gold/5">
          <Trophy className="text-tontine-gold mb-2 animate-bounce" />
          <div className="w-16 h-16 bg-tontine-gold rounded-full mb-2 border-4 border-tontine-gold/20" />
          <span className="text-[10px] font-black text-tontine-gold uppercase">Koffi A.</span>
          <span className="text-lg font-black text-tontine-gold">#1</span>
        </motion.div>
        {/* Rank 3 */}
        <motion.div initial={{ height: 0 }} animate={{ height: 120 }} className="w-32 glass-panel border-tontine-gold/20 flex flex-col items-center justify-end pb-4 rounded-t-2xl">
          <div className="w-12 h-12 bg-amber-600 rounded-full mb-2 border-2 border-white/10" />
          <span className="text-[10px] font-black text-amber-600 uppercase">Mourchid F.</span>
          <span className="text-sm font-bold text-amber-600">#3</span>
        </motion.div>
      </div>

      {/* List */}
      <div className="flex-1 p-4 md:p-8 max-w-2xl mx-auto w-full">
        <div className="space-y-3">
          {players.map((p, i) => (
            <motion.div 
              key={p.id} 
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ delay: i * 0.05 }}
              className={`glass-panel p-4 md:p-5 rounded-3xl flex items-center justify-between border group hover:border-tontine-gold/30 transition-all ${p.name.includes('Mourchid') ? 'border-tontine-orange/40 bg-tontine-orange/5' : 'border-white/5'}`}
            >
              <div className="flex items-center gap-4">
                <div className="relative">
                  <div className={`w-10 h-10 md:w-12 md:h-12 rounded-2xl flex items-center justify-center font-black text-base md:text-lg
                    ${p.rank === 1 ? 'bg-tontine-gold text-tontine-darker shadow-lg shadow-tontine-gold/20' : 
                      p.rank === 2 ? 'bg-gray-300 text-tontine-darker shadow-lg shadow-white/10' : 
                      p.rank === 3 ? 'bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'bg-white/5 text-gray-500'}`}>
                    {p.rank}
                  </div>
                </div>
                <div>
                  <h4 className="font-bold text-sm md:text-base flex items-center gap-2 text-white">
                    {p.name}
                    <ShieldCheck className="w-4 h-4 text-blue-400" />
                  </h4>
                  <div className="flex items-center gap-3 text-[10px] text-gray-500 uppercase font-bold tracking-widest mt-1">
                    <span className="flex items-center gap-1"><MapPin size={10} /> {p.city}</span>
                    <span className={`flex items-center gap-1 ${p.trend.includes('+') ? 'text-green-400' : p.trend.includes('-') ? 'text-red-400' : 'text-gray-600'}`}>
                      <TrendingUp size={10} /> {p.trend}
                    </span>
                  </div>
                </div>
              </div>
              
              <div className="text-right">
                <div className="flex items-center gap-1 justify-end">
                  <Star size={14} className="text-tontine-gold fill-tontine-gold" />
                  <span className="text-xl md:text-2xl font-black text-white">{p.score}</span>
                </div>
                <div className="text-[8px] md:text-[10px] text-gray-600 font-black uppercase tracking-tighter">Points de Confiance</div>
              </div>
            </motion.div>
          ))}
        </div>

        {/* Footer Info */}
        <p className="mt-8 text-center text-[10px] text-gray-600 uppercase font-black tracking-widest leading-relaxed">
          Le classement est mis à jour toutes les 24h sur la base de la ponctualité blockchain ⛓️
        </p>
      </div>
      
      {/* Space for bottom nav */}
      <div className="h-20 md:hidden" />
    </div>
  );
};

export default Leaderboard;
