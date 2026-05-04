import React from 'react';
import { motion } from 'framer-motion';
import { Trophy, ArrowLeft, Medal, Star, TrendingUp, ShieldCheck } from 'lucide-react';
import AudioButton from './AudioButton';

const Leaderboard = ({ onBack }) => {
  const players = [
    { id: 1, name: "Koffi Adjovi", score: 99, city: "Cotonou", rank: 1, trend: 'up' },
    { id: 2, name: "Amina Soule", score: 97, city: "Parakou", rank: 2, trend: 'up' },
    { id: 3, name: "Mourchid F.", score: 95, city: "Abomey", rank: 3, trend: 'stable' },
    { id: 4, name: "Jean Dogbe", score: 92, city: "Porto-Novo", rank: 4, trend: 'down' },
    { id: 5, name: "Sarah B.", score: 89, city: "Ouidah", rank: 5, trend: 'up' },
    { id: 6, name: "Paul K.", score: 88, city: "Cotonou", rank: 6, trend: 'stable' },
    { id: 7, name: "Fati H.", score: 85, city: "Natitingou", rank: 7, trend: 'down' },
    { id: 8, name: "Gildas M.", score: 84, city: "Bohicon", rank: 8, trend: 'up' },
    { id: 9, name: "Rachid T.", score: 82, city: "Kandi", rank: 9, trend: 'stable' },
    { id: 10, name: "Noelie Z.", score: 81, city: "Djougou", rank: 10, trend: 'up' },
  ];

  return (
    <div className="min-h-screen bg-tontine-darker text-white font-inter p-6 relative overflow-hidden">
      {/* Background decoration */}
      <div className="absolute top-0 right-0 w-64 h-64 bg-tontine-gold/5 rounded-full blur-[80px] pointer-events-none" />

      <div className="max-w-md mx-auto relative z-10">
        <button onClick={onBack} className="mb-6 flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
          <ArrowLeft className="w-5 h-5" /> Retour
        </button>

        <div className="text-center mb-10">
          <div className="inline-flex items-center justify-center w-16 h-16 bg-tontine-gold/20 rounded-full mb-4 ring-4 ring-tontine-gold/10">
            <Trophy className="w-8 h-8 text-tontine-gold" />
          </div>
          <div className="flex items-center justify-center gap-2">
            <h1 className="text-3xl font-playfair font-bold mb-2">Classement Elite</h1>
            <AudioButton label="Xó xá mì (Fon)" />
          </div>
          <p className="text-gray-400 text-sm">Les membres les plus fiables du Bénin</p>
        </div>

        <div className="space-y-3">
          {players.map((p, i) => (
            <motion.div 
              key={p.id}
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: i * 0.05 }}
              className={`glass-panel p-4 rounded-2xl flex items-center justify-between border ${p.name.includes('Mourchid') ? 'border-tontine-orange/40 bg-tontine-orange/5' : 'border-white/5'}`}
            >
              <div className="flex items-center gap-4">
                <div className="relative">
                  <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold
                    ${p.rank === 1 ? 'bg-tontine-gold text-tontine-darker' : 
                      p.rank === 2 ? 'bg-gray-300 text-tontine-darker' : 
                      p.rank === 3 ? 'bg-amber-600 text-white' : 'bg-white/10 text-gray-400'}`}>
                    {p.rank}
                  </div>
                  {p.rank <= 3 && <Medal className="absolute -top-1 -right-1 w-4 h-4 text-tontine-gold" />}
                </div>
                <div>
                  <h4 className="font-bold text-sm flex items-center gap-1">
                    {p.name}
                    {p.score > 90 && <ShieldCheck className="w-3 h-3 text-blue-400" />}
                  </h4>
                  <p className="text-[10px] text-gray-500 uppercase tracking-wider">{p.city}</p>
                </div>
              </div>
              <div className="text-right">
                <div className="text-tontine-gold font-bold text-lg">{p.score}</div>
                <div className="text-[9px] text-gray-500 font-medium">SCORE</div>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default Leaderboard;
