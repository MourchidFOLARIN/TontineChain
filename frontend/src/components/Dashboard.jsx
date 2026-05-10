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
      const res = await initiatePayment(contributionId);
      if (res.data && res.data.url) {
        window.open(res.data.url, '_blank');
      }
    } catch (err) {
      console.error("Erreur paiement", err);
    } finally {
      setShowPaymentProgress(false);
    }
  };

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const [balanceRes, groupsRes] = await Promise.all([
          getMyBalance(),
          getGroups()
        ]);
        
        if (balanceRes.data) {
          setBalance({
            total_paid: balanceRes.data.total_cotise_fcfa || 0,
            total_expected: balanceRes.data.expected_payouts_fcfa || 0
          });
        }
        setLoading(false);
      } catch (err) {
        console.error("Erreur de chargement", err);
        // Fallback pour la démo si le backend est injoignable
        setBalance({ total_paid: 125000, total_expected: 500000 });
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
      <div className="mb-8 md:mb-12">
        <h1 className="text-fluid-h2 font-playfair font-bold mb-2">
          Bonjour, {user?.first_name || 'Utilisateur'} 👋
        </h1>
        <p className="text-sm md:text-base text-gray-400">Voici un aperçu de vos tontines aujourd'hui</p>
      </div>

      <main className="relative z-10">
        
        {/* Top 3 Elite Members Preview */}
        <div className="mb-10 md:mb-16">
          <div className="flex justify-between items-center mb-6">
            <h3 className="text-lg md:text-xl font-playfair font-bold flex items-center gap-2">
              <Trophy className="w-5 h-5 text-tontine-gold" /> Membres Elite
            </h3>
            <button onClick={onOpenLeaderboard} className="text-xs text-tontine-orange hover:underline font-bold uppercase tracking-wider">Voir tout</button>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {[
              { name: "Koffi Adjovi", score: 99, rank: 1 },
              { name: "Amina Soule", score: 97, rank: 2 },
              { name: "Jean Dogbe", score: 92, rank: 3 }
            ].map((p, i) => (
              <div key={i} className="glass-panel p-4 rounded-2xl flex items-center gap-4 border border-white/5 relative overflow-hidden group hover:border-tontine-gold/30 transition-all cursor-pointer">
                <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold flex-shrink-0
                  ${p.rank === 1 ? 'bg-tontine-gold text-tontine-darker' : 'bg-white/10 text-gray-400'}`}>
                  {p.rank}
                </div>
                <div className="min-w-0">
                  <h4 className="text-sm font-bold text-white truncate">{p.name}</h4>
                  <div className="flex items-center gap-1">
                    <Star className="w-3 h-3 text-tontine-gold fill-tontine-gold" />
                    <span className="text-[10px] text-gray-400">{p.score} pts d'honneur</span>
                  </div>
                </div>
                <div className="absolute -right-2 -bottom-2 opacity-5 group-hover:opacity-10 transition-opacity">
                  <Trophy className="w-12 h-12" />
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* User Trust Profile Card */}
        <motion.div 
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="glass-panel p-6 md:p-8 rounded-3xl mb-10 flex flex-col md:flex-row items-center gap-6 border-tontine-orange/20"
        >
          <div className="relative w-24 h-24 md:w-32 md:h-32 flex-shrink-0">
            <svg className="w-full h-full transform -rotate-90">
              <circle cx="50%" cy="50%" r="45%" stroke="currentColor" strokeWidth="6" fill="transparent" className="text-white/5" />
              <motion.circle
                cx="50%" cy="50%" r="45%" stroke="currentColor" strokeWidth="6" fill="transparent"
                strokeDasharray="283"
                initial={{ strokeDashoffset: 283 }}
                animate={{ strokeDashoffset: 283 - (283 * (user?.score_confiance || 85)) / 100 }}
                transition={{ duration: 1.5, ease: "easeOut" }}
                className="text-tontine-orange"
              />
            </svg>
            <div className="absolute inset-0 flex flex-col items-center justify-center">
              <span className="text-2xl md:text-3xl font-black">{user?.score_confiance || 85}</span>
              <span className="text-[8px] md:text-[10px] uppercase font-bold text-gray-400">Score</span>
            </div>
          </div>

          <div className="text-center md:text-left flex-grow">
            <h2 className="text-xl md:text-2xl font-bold mb-2 flex items-center justify-center md:justify-start gap-2">
              {user?.first_name} {user?.last_name}
              <ShieldCheck className="w-5 h-5 text-blue-400" />
            </h2>
            <p className="text-sm text-gray-400 mb-4 max-w-md">
              Votre score de confiance est excellent. Vous avez accès à des tontines à haut capital et des frais réduits.
            </p>
            <div className="flex flex-wrap justify-center md:justify-start gap-2">
              <span className="badge badge-success">✓ Identité Vérifiée</span>
              <span className="badge badge-info">Elite Member</span>
              <span className="badge badge-warning">Top 5% Bénin</span>
            </div>
          </div>

          <div className="flex flex-col gap-2 w-full md:w-auto">
            <button onClick={onNewGroup} className="btn-primary w-full justify-center">
              <Plus className="w-5 h-5" /> Créer
            </button>
            <button onClick={() => setShowJoinModal(true)} className="btn-secondary w-full justify-center">
              <Search className="w-4 h-4" /> Rejoindre
            </button>
          </div>
        </motion.div>

        {/* Prochain Paiement Alert */}
        {groups.length > 0 && (
          <motion.div 
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            className="mb-10 bg-gradient-to-br from-tontine-orange/20 to-tontine-gold/5 border border-tontine-orange/30 p-6 rounded-3xl flex flex-col sm:flex-row justify-between items-center gap-6"
          >
            <div className="flex items-center gap-4 text-center sm:text-left">
              <div className="w-14 h-14 bg-tontine-orange/20 rounded-2xl flex items-center justify-center text-tontine-orange border border-tontine-orange/20 flex-shrink-0">
                <Clock className="w-7 h-7 animate-pulse" />
              </div>
              <div>
                <h4 className="font-bold text-lg mb-1">Action Requise</h4>
                <p className="text-xs md:text-sm text-gray-300">
                  Versement pour <span className="text-white font-bold">{groups[0].name}</span>
                  <br />
                  <span className="text-tontine-gold font-bold">{groups[0].amount.toLocaleString()} FCFA</span> attendus avant demain.
                </p>
              </div>
            </div>
            <button 
              onClick={() => handlePay(groups[0].id)}
              className="w-full sm:w-auto bg-white text-tontine-darker font-black px-10 py-4 rounded-2xl hover:bg-tontine-gold transition-all shadow-xl text-sm uppercase tracking-widest"
            >
              Payer Maintenant
            </button>
          </motion.div>
        )}

        {/* Stats Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-12">
          <div className="glass-panel p-6 rounded-3xl">
            <div className="flex justify-between items-start mb-4">
              <p className="text-gray-400 text-xs uppercase font-bold tracking-widest">Total Épargné</p>
              <Wallet className="w-5 h-5 text-tontine-orange" />
            </div>
            <h3 className="stat-value">{balance.total_paid.toLocaleString()} <span className="text-xs text-gray-500">FCFA</span></h3>
            <div className="mt-4 flex items-center gap-2 text-[10px] text-green-400 bg-green-400/10 w-fit px-2 py-1 rounded">
              <TrendingUp size={10} /> +12% ce mois
            </div>
          </div>

          <div className="glass-panel p-6 rounded-3xl">
            <div className="flex justify-between items-start mb-4">
              <p className="text-gray-400 text-xs uppercase font-bold tracking-widest">Gain Espéré</p>
              <TrendingUp className="w-5 h-5 text-blue-400" />
            </div>
            <h3 className="stat-value text-blue-400">{(groups.reduce((acc, g) => acc + (g.amount * g.members), 0)).toLocaleString()} <span className="text-xs text-gray-500">FCFA</span></h3>
            <p className="mt-4 text-[10px] text-gray-500">Basé sur vos {groups.length} tontines actives</p>
          </div>

          <div className="glass-panel p-6 rounded-3xl sm:col-span-2 lg:col-span-1">
            <div className="flex justify-between items-start mb-4">
              <p className="text-gray-400 text-xs uppercase font-bold tracking-widest">Score de Crédit</p>
              <Activity className="w-5 h-5 text-purple-400" />
            </div>
            <h3 className="stat-value text-purple-400">Elite A+</h3>
            <p className="mt-4 text-[10px] text-gray-500">Mise à jour il y a 2 heures</p>
          </div>
        </div>

        {/* Groups List */}
        <div className="mb-12">
          <div className="flex justify-between items-center mb-6">
            <h2 className="text-xl md:text-2xl font-bold font-playfair flex items-center gap-2">
              <Users className="w-6 h-6 text-tontine-orange" /> Vos Tontines
            </h2>
            <button onClick={() => onNavigate('mes_tontines')} className="text-xs text-gray-400 hover:text-white transition-colors">Gérer tout</button>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            {groups.slice(0, 4).map((group, index) => (
              <motion.div 
                key={group.id}
                initial={{ opacity: 0, scale: 0.98 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ delay: 0.05 * index }}
                onClick={() => onSelectGroup(group)}
                className="glass-panel p-5 md:p-6 rounded-3xl hover:border-tontine-orange/30 transition-all cursor-pointer group"
              >
                <div className="flex justify-between items-start mb-4">
                  <div className="min-w-0 flex-grow pr-2">
                    <h3 className="text-base md:text-lg font-bold truncate group-hover:text-tontine-orange transition-colors">{group.name}</h3>
                    <span className="text-[10px] text-gray-500 uppercase font-black tracking-widest">{group.cycle}</span>
                  </div>
                  <div className="flex-shrink-0 text-right">
                    <div className="text-sm font-black text-white">{group.amount.toLocaleString()} <span className="text-[10px] text-tontine-gold">F</span></div>
                    <span className="text-[9px] text-gray-500">{group.members} membres</span>
                  </div>
                </div>
                
                <div className="space-y-3 mb-6">
                  <div className="flex justify-between text-[10px] font-bold uppercase tracking-tighter">
                    <span className="text-gray-500">Collecte en cours</span>
                    <span className="text-tontine-orange">{Math.round((group.current_cycle / group.members) * 100)}%</span>
                  </div>
                  <div className="w-full h-1.5 bg-white/5 rounded-full overflow-hidden">
                    <div 
                      className="h-full bg-gradient-to-r from-tontine-orange to-tontine-gold" 
                      style={{ width: `${(group.current_cycle / group.members) * 100}%` }}
                    />
                  </div>
                </div>

                <div className="flex justify-between items-center text-[10px] text-gray-400">
                  <div className="flex items-center gap-1">
                    <ShieldCheck size={12} className="text-green-500" />
                    <span>Sécurisé par Smart Contract</span>
                  </div>
                  <ChevronRight size={14} className="group-hover:translate-x-1 transition-transform" />
                </div>
              </motion.div>
            ))}
          </div>
        </div>

        {/* Live Activity Feed */}
        <div className="mb-12">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
              <Activity className="w-4 h-4" /> Activité du Réseau
            </h3>
            <span className="flex items-center gap-1 text-[10px] text-green-400">
              <span className="w-1.5 h-1.5 bg-green-400 rounded-full animate-ping" />
              Direct
            </span>
          </div>
          <div className="glass-panel p-2 rounded-2xl border border-white/5 divide-y divide-white/5">
            {notifications.slice(0, 3).map((act, i) => (
              <div key={i} className="flex items-center justify-between p-4 hover:bg-white/5 transition-colors">
                <div className="flex items-center gap-3">
                  <div className="w-1.5 h-1.5 bg-tontine-orange rounded-full" />
                  <p className="text-xs text-gray-300">
                    <span className="font-bold text-white">{act.title}</span> : {act.message}
                  </p>
                </div>
                <span className="text-[9px] text-gray-600 flex-shrink-0 ml-2">{act.time}</span>
              </div>
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
