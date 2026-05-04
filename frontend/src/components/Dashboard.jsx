import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  LogOut, 
  Wallet, 
  TrendingUp, 
  Users, 
  ShieldCheck, 
  Plus, 
  ChevronRight,
  Activity,
  Bell,
  Search,
  Loader2,
  Clock,
  CheckCircle2,
  Trophy,
  Star
} from 'lucide-react';
import api, { getMyBalance, getGroups, getLeaderboard, joinGroup, initiatePayment } from '../services/api';
import { translations } from '../services/translations';
import AudioButton from './AudioButton';

const Dashboard = ({ user, groups, onLogout, onSelectGroup, onOpenProfile, onNewGroup, onOpenNotifications, onOpenLeaderboard, onJoinGroup, onNavigate }) => {
  const t = translations[user?.preferred_language || 'fr'] || translations.fr;
  const [balance, setBalance] = useState({ total_paid: 0, total_expected: 0 });
  const [leaderboard, setLeaderboard] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showJoinModal, setShowJoinModal] = useState(false);
  const [inviteCode, setInviteCode] = useState('');
  const [joining, setJoining] = useState(false);
  const [payingId, setPayingId] = useState(null);
  const [showPaymentProgress, setShowPaymentProgress] = useState(false);

  const handlePay = async (contributionId) => {
    setShowPaymentProgress(true);
    try {
      await new Promise(r => setTimeout(r, 3000));
      window.open('https://fedapay.com/pay/demo', '_blank');
    } finally {
      setShowPaymentProgress(false);
    }
  };

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        setBalance({ total_paid: 1250000, total_expected: 2500000 });
        setLeaderboard([]);
        setLoading(false);
      } catch (err) {
        console.error("Erreur de chargement", err);
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  if (loading) {
    return (
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: '60vh' }}>
        <div className="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-tontine-orange"></div>
      </div>
    );
  }

  // Calculate trust score color
  const scoreColor = (user?.score_confiance || 85) >= 90 ? 'text-green-400' 
                   : (user?.score_confiance || 85) >= 70 ? 'text-yellow-400' 
                   : 'text-red-400';

  return (
    <div style={{ color: 'var(--text-primary)' }} className="font-inter relative">

      {/* Welcome Header */}
      <div style={{ marginBottom: 32 }}>
        <h1 style={{ fontFamily: 'Playfair Display, serif', fontSize: 28, fontWeight: 700, marginBottom: 4 }}>
          Bonjour, {user?.first_name || 'Utilisateur'} 👋
        </h1>
        <p style={{ color: 'var(--text-secondary)', fontSize: 15 }}>Voici un aperçu de vos tontines aujourd'hui</p>
      </div>

      <main className="relative z-10">

        
        {/* Top 3 Elite Members Preview */}
        <div className="mb-12">
          <div className="flex justify-between items-center mb-6">
            <h3 className="text-xl font-playfair font-bold flex items-center gap-2">
              <Trophy className="w-5 h-5 text-tontine-gold" /> Membres Elite
            </h3>
            <button onClick={onOpenLeaderboard} className="text-xs text-tontine-orange hover:underline">Voir tout</button>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {[
              { name: "Koffi Adjovi", score: 99, rank: 1 },
              { name: "Amina Soule", score: 97, rank: 2 },
              { name: "Jean Dogbe", score: 92, rank: 3 }
            ].map((p, i) => (
              <div key={i} className="glass-panel p-4 rounded-2xl flex items-center gap-4 border border-white/5 relative overflow-hidden group hover:border-tontine-gold/30 transition-all cursor-pointer">
                <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold
                  ${p.rank === 1 ? 'bg-tontine-gold text-tontine-darker' : 'bg-white/10 text-gray-400'}`}>
                  {p.rank}
                </div>
                <div>
                  <h4 className="text-sm font-bold text-white">{p.name}</h4>
                  <div className="flex items-center gap-1">
                    <Star className="w-3 h-3 text-tontine-gold fill-tontine-gold" />
                    <span className="text-[10px] text-gray-400">{p.score} pts</span>
                  </div>
                </div>
                <div className="absolute -right-2 -bottom-2 opacity-10 group-hover:opacity-20 transition-opacity">
                  <Trophy className="w-12 h-12" />
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Header Section */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4"
        >
            <div onClick={onOpenProfile} className="cursor-pointer group flex items-center gap-4">
              <div className="relative w-16 h-16 flex items-center justify-center">
                <svg className="w-full h-full transform -rotate-90">
                  <circle
                    cx="32"
                    cy="32"
                    r="28"
                    stroke="currentColor"
                    strokeWidth="4"
                    fill="transparent"
                    className="text-white/5"
                  />
                  <motion.circle
                    cx="32"
                    cy="32"
                    r="28"
                    stroke="currentColor"
                    strokeWidth="4"
                    fill="transparent"
                    strokeDasharray="175.9"
                    initial={{ strokeDashoffset: 175.9 }}
                    animate={{ strokeDashoffset: 175.9 - (175.9 * (user?.score_confiance || 85)) / 100 }}
                    transition={{ duration: 1.5, ease: "easeOut" }}
                    className="text-tontine-orange"
                  />
                </svg>
                <span className="absolute text-xs font-bold">{user?.score_confiance || 85}</span>
              </div>
              <div>
                <h1 className="text-3xl font-bold mb-1 transition-colors group-hover:text-tontine-gold flex items-center gap-2">
                  {t.welcome}, <span className="text-tontine-orange">{user?.first_name || 'Utilisateur'}</span>
                  <CheckCircle2 className="w-5 h-5 text-blue-400" />
                  👋
                </h1>
                <div className="flex items-center gap-3">
                  <p className="text-gray-400 flex items-center gap-2 group-hover:text-gray-200 transition-colors">
                    <TrendingUp className="w-4 h-4 text-green-400" />
                    {t.trust_score}: <span className="font-bold text-white">Top 5% mondial</span>
                  </p>
                  <AudioButton label={`Bienvenue ${user?.first_name}. Votre score de confiance est de ${user?.score_confiance || 85} sur 100. Vous faites partie de l'élite financière du pays.`} />
                </div>
              </div>
            </div>
          <div className="flex gap-3">
            <button 
              onClick={() => setShowJoinModal(true)} 
              className="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 px-5 py-3 rounded-xl transition-all text-sm"
            >
              <Search className="w-4 h-4 text-tontine-orange" />
              <span>{t.join_group}</span>
            </button>
            <button onClick={onNewGroup} className="flex items-center gap-2 bg-gradient-to-r from-tontine-orange to-tontine-gold text-tontine-darker px-5 py-3 rounded-xl transition-all font-bold shadow-lg shadow-tontine-orange/20">
              <Plus className="w-5 h-5" />
              <span>{t.new_group}</span>
            </button>
          </div>
        </motion.div>

        {/* Prochain Paiement Alert */}
        {groups.length > 0 && (
          <motion.div 
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            className="mb-10 bg-gradient-to-r from-tontine-orange/20 to-tontine-gold/10 border border-tontine-orange/30 p-5 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4 shadow-xl shadow-tontine-orange/5"
          >
            <div className="flex items-center gap-4">
              <div className="w-12 h-12 bg-tontine-orange rounded-full flex items-center justify-center text-tontine-darker">
                <Clock className="w-6 h-6 animate-pulse" />
              </div>
              <div>
                <div className="flex items-center gap-2">
                  <h4 className="font-bold text-lg">{t.next_payment}</h4>
                  <AudioButton />
                </div>
                <p className="text-sm text-gray-300">Groupe: <span className="text-white font-bold">{groups[0].name}</span> • 50.000 FCFA</p>
              </div>
            </div>
            <button 
              onClick={() => handlePay(groups[0].id)}
              disabled={payingId === groups[0].id}
              className="bg-white text-tontine-darker font-bold px-8 py-3 rounded-xl hover:bg-tontine-gold transition-all shadow-lg flex items-center gap-2"
            >
              {payingId === groups[0].id ? <Loader2 className="w-5 h-5 animate-spin" /> : t.pay_now}
            </button>
          </motion.div>
        )}

        {/* Groups List */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
          {groups.map((g, i) => (
            <motion.div 
              key={g.id}
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: i * 0.1 }}
              onClick={() => onSelectGroup(g)}
              className="glass-panel p-6 rounded-3xl group cursor-pointer hover:border-tontine-orange/30 transition-all relative overflow-hidden"
            >
              <div className="flex justify-between items-start mb-4">
                <div className="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center group-hover:bg-tontine-orange/10 transition-colors">
                  <Users className="w-6 h-6 text-tontine-orange" />
                </div>
                <div className="text-right">
                  <span className="block text-xs text-gray-500 uppercase font-bold tracking-widest">{g.cycle}</span>
                  <span className="text-sm font-bold text-tontine-gold">{g.amount.toLocaleString()} F</span>
                </div>
              </div>
              
              <h3 className="text-lg font-bold mb-1 group-hover:text-tontine-orange transition-colors">{g.name}</h3>
              <p className="text-xs text-gray-500 mb-4 flex items-center gap-1">
                <ShieldCheck className="w-3 h-3" /> Leader: {g.leader || 'Koffi A.'}
              </p>

              {/* Progress Bar */}
              <div className="space-y-2 mb-4">
                <div className="flex justify-between text-[10px] font-bold uppercase tracking-tighter">
                  <span className="text-gray-400">Progression Cycle</span>
                  <span className="text-tontine-orange">{Math.round((g.current_cycle / g.members) * 100)}%</span>
                </div>
                <div className="w-full bg-white/5 h-1.5 rounded-full overflow-hidden">
                  <div 
                    className="bg-gradient-to-r from-tontine-orange to-tontine-gold h-full" 
                    style={{ width: `${(g.current_cycle / g.members) * 100}%` }} 
                  />
                </div>
              </div>

              <div className="flex justify-between items-center text-xs">
                <div className="flex -space-x-2">
                  {[1,2,3].map(z => (
                    <div key={z} className="w-6 h-6 rounded-full border-2 border-tontine-darker bg-gray-700 flex items-center justify-center text-[8px] font-bold">U{z}</div>
                  ))}
                  <div className="w-6 h-6 rounded-full border-2 border-tontine-darker bg-tontine-orange text-tontine-darker flex items-center justify-center text-[8px] font-bold">+{g.members - 3}</div>
                </div>
                <ChevronRight className="w-4 h-4 text-gray-600 group-hover:text-tontine-orange transition-all group-hover:translate-x-1" />
              </div>
            </motion.div>
          ))}
        </div>

        {/* Live Activity Feed */}
        <div className="mb-12">
          <h3 className="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
            <Activity className="w-4 h-4" /> Activité en direct
          </h3>
          <div className="glass-panel p-2 rounded-2xl border border-white/5 divide-y divide-white/5">
            {[
              { user: "Sarah B.", action: "a versé sa cotisation", target: "Tontine Diamant", time: "À l'instant" },
              { user: "Jean D.", action: "a rejoint", target: "Marché Dantokpa", time: "Il y a 2 min" }
            ].map((act, i) => (
              <div key={i} className="flex items-center justify-between p-4 hover:bg-white/5 transition-colors">
                <div className="flex items-center gap-3">
                  <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse" />
                  <p className="text-xs">
                    <span className="font-bold text-white">{act.user}</span> {act.action} dans <span className="text-tontine-gold">{act.target}</span>
                  </p>
                </div>
                <span className="text-[10px] text-gray-500">{act.time}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
            className="glass-panel p-6 rounded-2xl relative overflow-hidden group"
          >
            <div className="absolute -right-4 -top-4 w-24 h-24 bg-tontine-orange/10 rounded-full group-hover:scale-150 transition-transform duration-500" />
            <div className="flex justify-between items-start mb-4">
              <div>
                <p className="text-gray-400 text-sm mb-1">{t.balance_paid}</p>
                <h3 className="text-3xl font-bold font-playfair">{balance.total_paid.toLocaleString()} <span className="text-lg text-tontine-orange">FCFA</span></h3>
              </div>
              <div className="p-3 bg-white/5 rounded-xl text-tontine-gold">
                <Wallet className="w-6 h-6" />
              </div>
            </div>
            <div className="text-sm text-green-400 flex items-center gap-1">
              <TrendingUp className="w-4 h-4" />
              <span>À jour sur tous les groupes</span>
            </div>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2 }}
            className="glass-panel p-6 rounded-2xl relative overflow-hidden group"
          >
            <div className="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full group-hover:scale-150 transition-transform duration-500" />
            <div className="flex justify-between items-start mb-4">
              <div>
                <p className="text-gray-400 text-sm mb-1">{t.expected_gains}</p>
                <h3 className="text-3xl font-bold font-playfair">{(groups.reduce((acc, g) => acc + (g.amount * g.members), 0)).toLocaleString()} <span className="text-lg text-tontine-orange">FCFA</span></h3>
              </div>
              <div className="p-3 bg-white/5 rounded-xl text-blue-400">
                <TrendingUp className="w-6 h-6" />
              </div>
            </div>
            <div className="text-sm text-gray-400">
              Sur {groups.length} tontine(s) active(s)
            </div>
          </motion.div>
          
          <motion.div 
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
            onClick={onOpenLeaderboard}
            className="glass-panel p-6 rounded-2xl md:col-span-2 lg:col-span-1 cursor-pointer hover:border-white/10 transition-colors"
          >
            <div className="flex justify-between items-center mb-4">
              <h3 className="font-bold flex items-center gap-2">
                <ShieldCheck className="w-5 h-5 text-tontine-gold" />
                Classement Confiance
              </h3>
            </div>
            <div className="space-y-3">
              {leaderboard.map((u, i) => (
                <div key={u.id} className="flex justify-between items-center p-2 hover:bg-white/5 rounded-lg transition-colors">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full bg-tontine-dark flex items-center justify-center font-bold text-xs border border-white/10">
                      {i + 1}
                    </div>
                    <span className="text-sm">{u.first_name} {u.last_name}</span>
                  </div>
                  <span className="text-sm font-bold text-tontine-gold">{u.score_confiance}</span>
                </div>
              ))}
            </div>
          </motion.div>
        </div>

        {/* Groups List */}
        <div className="mb-6">
          <h2 className="text-xl font-bold mb-6 flex items-center gap-2">
            <Users className="w-6 h-6 text-tontine-orange" />
            {t.active_tontines}
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {groups.map((group, index) => (
              <motion.div 
                key={group.id}
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ delay: 0.1 * index }}
                onClick={() => onSelectGroup(group)}
                className="glass-panel p-6 rounded-2xl hover:border-tontine-orange/30 transition-all cursor-pointer group"
              >
                <div className="flex justify-between items-start mb-4">
                  <h3 className="text-xl font-bold">{group.name}</h3>
                  <span className="bg-white/10 text-xs px-3 py-1 rounded-full text-tontine-gold">Cycle {group.current_cycle}/{group.members}</span>
                </div>
                
                <div className="space-y-2 mb-6">
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-400">Cotisation</span>
                    <span className="font-bold">{group.amount.toLocaleString()} FCFA</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-400">Fréquence</span>
                    <span>{group.cycle}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-400">Membres</span>
                    <span>{group.members} personnes</span>
                  </div>
                </div>

                <div className="w-full h-2 bg-tontine-dark rounded-full overflow-hidden mb-4">
                  <div 
                    className="h-full bg-gradient-to-r from-tontine-orange to-tontine-gold" 
                    style={{ width: `${(group.current_cycle / group.members) * 100}%` }}
                  />
                </div>

                <div className="flex justify-between items-center mt-4 pt-4 border-t border-white/5 text-sm">
                  <span className="text-tontine-orange group-hover:underline">Gérer ou payer</span>
                  <ChevronRight className="w-5 h-5 text-gray-500 group-hover:text-tontine-orange transition-colors" />
                </div>
              </motion.div>
            ))}
          </div>
        </div>
        
        {/* Google Translate Widget for Jury */}
        <div className="mt-8 flex justify-center opacity-70 hover:opacity-100 transition-opacity">
          <div id="google_translate_element" className="rounded-lg overflow-hidden border border-white/10 shadow-lg"></div>
        </div>

        {/* API Docs Link for Jury */}
        <footer className="mt-20 pt-8 border-t border-white/5 text-center">
          <a 
            href="https://tonnine-benin-backend.onrender.com/api/documentation" 
            target="_blank" 
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 text-[10px] text-gray-500 hover:text-tontine-gold transition-colors"
          >
            <Search className="w-3 h-3" />
            Accéder à la Documentation API (Swagger)
          </a>
        </footer>

        {/* FedaPay Redirection Overlay */}
        <AnimatePresence>
          {showPaymentProgress && (
            <motion.div 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="fixed inset-0 z-[100] bg-tontine-darker/95 backdrop-blur-md flex flex-items justify-center items-center p-6 text-center"
            >
              <div className="max-w-xs w-full">
                <div className="w-20 h-20 bg-white rounded-2xl mx-auto mb-6 flex items-center justify-center p-4 shadow-xl">
                  <img src="https://fedapay.com/assets/images/logo.png" alt="FedaPay" className="w-full h-auto" />
                </div>
                <h3 className="text-xl font-bold mb-2">Sécurisation du paiement</h3>
                <p className="text-gray-400 text-sm mb-8 italic">Redirection vers la passerelle Mobile Money de FedaPay...</p>
                <div className="flex justify-center gap-2">
                  <div className="w-2 h-2 bg-tontine-orange rounded-full animate-bounce [animation-delay:-0.3s]"></div>
                  <div className="w-2 h-2 bg-tontine-orange rounded-full animate-bounce [animation-delay:-0.15s]"></div>
                  <div className="w-2 h-2 bg-tontine-orange rounded-full animate-bounce"></div>
                </div>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </main>

      {/* Join Group Modal */}
      <AnimatePresence>
        {showJoinModal && (
          <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <motion.div 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setShowJoinModal(false)}
              className="absolute inset-0 bg-black/60 backdrop-blur-sm"
            />
            <motion.div 
              initial={{ opacity: 0, scale: 0.9, y: 20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.9, y: 20 }}
              className="glass-panel p-8 rounded-3xl max-w-sm w-full relative z-10 shadow-2xl border border-white/10"
            >
              <h3 className="text-2xl font-playfair font-bold mb-2">Rejoindre une Tontine</h3>
              <p className="text-gray-400 text-sm mb-6">Entrez le code d'invitation pour rejoindre un groupe existant.</p>
              
              <div className="space-y-4">
                <input 
                  type="text" 
                  value={inviteCode}
                  onChange={(e) => setInviteCode(e.target.value.toUpperCase())}
                  placeholder="CODE-XYZ"
                  className="w-full bg-white/5 border border-white/10 rounded-xl py-4 text-center text-xl font-bold tracking-widest focus:ring-2 focus:ring-tontine-orange outline-none transition-all"
                />
                <button 
                  disabled={!inviteCode || joining}
                  onClick={async () => {
                    setJoining(true);
                    try {
                      onJoinGroup(inviteCode);
                      await new Promise(r => setTimeout(r, 1000));
                      setShowJoinModal(false);
                      setInviteCode('');
                    } finally {
                      setJoining(false);
                    }
                  }}
                  className="w-full bg-tontine-orange text-tontine-darker font-bold py-4 rounded-xl shadow-lg shadow-tontine-orange/20 flex items-center justify-center gap-2 disabled:opacity-50"
                >
                  {joining ? <Loader2 className="w-5 h-5 animate-spin" /> : 'Rejoindre maintenant'}
                </button>
              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default Dashboard;
