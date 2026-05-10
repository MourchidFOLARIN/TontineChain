import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Wallet, 
  TrendingUp, 
  Users, 
  ShieldCheck, 
  Plus, 
  ChevronRight,
  Activity,
  Loader2,
  Clock,
  Trophy,
  ArrowUpRight,
  PieChart
} from 'lucide-react';
import { getMyBalance, getGroups, initiatePayment } from '../services/api';

const Dashboard = ({ user, onSelectGroup, onNewGroup, onNavigate }) => {
  const [balance, setBalance] = useState({ total_paid: 0, total_expected: 0 });
  const [groups, setGroups] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showJoinModal, setShowJoinModal] = useState(false);
  const [inviteCode, setInviteCode] = useState('');
  const [joining, setJoining] = useState(false);
  const [showPaymentProgress, setShowPaymentProgress] = useState(false);

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
        if (groupsRes.data) {
            setGroups(groupsRes.data);
        }
        setLoading(false);
      } catch (err) {
        console.error("Erreur de chargement", err);
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

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

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[60vh]">
        <Loader2 className="w-12 h-12 text-tontigo-orange animate-spin" />
      </div>
    );
  }

  return (
    <div className="px-6 animate-in fade-in slide-in-from-bottom-4 duration-700">

      {/* WALLET SECTION - TONTIGO STYLE */}
      <div className="bg-tontigo-blue rounded-[32px] p-8 mb-8 text-white shadow-2xl shadow-slate-900/20 relative overflow-hidden">
        <div className="absolute top-0 right-0 p-4 opacity-10">
          <PieChart size={120} />
        </div>
        
        <div className="flex justify-between items-start mb-6 relative z-10">
          <div>
            <p className="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Épargné</p>
            <h1 className="text-3xl font-black">{balance.total_paid.toLocaleString()} <span className="text-sm font-normal text-slate-400">FCFA</span></h1>
          </div>
          <div className="bg-white/10 p-3 rounded-2xl backdrop-blur-md">
            <Wallet size={24} className="text-tontigo-orange" />
          </div>
        </div>

        <div className="grid grid-cols-2 gap-4 mt-8 pt-6 border-t border-white/5 relative z-10">
          <div>
            <p className="text-slate-400 text-[10px] uppercase font-bold tracking-wider mb-1">Gain Espéré</p>
            <p className="text-lg font-bold text-tontigo-orange">{balance.total_expected.toLocaleString()} F</p>
          </div>
          <div className="text-right">
            <p className="text-slate-400 text-[10px] uppercase font-bold tracking-wider mb-1">Score Confiance</p>
            <p className="text-lg font-bold text-emerald-400">{user?.score_confiance || 85}%</p>
          </div>
        </div>
      </div>

      {/* QUICK ACTIONS */}
      <div className="grid grid-cols-2 gap-4 mb-10">
        <button 
          onClick={onNewGroup}
          className="tontigo-card p-5 flex flex-col items-center gap-3 border-none bg-white shadow-sm hover:shadow-md transition-all active:scale-95"
        >
          <div className="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center text-tontigo-orange">
            <Plus size={24} strokeWidth={3} />
          </div>
          <span className="text-xs font-bold text-slate-700">Créer Tontine</span>
        </button>
        <button 
          onClick={() => setShowJoinModal(true)}
          className="tontigo-card p-5 flex flex-col items-center gap-3 border-none bg-white shadow-sm hover:shadow-md transition-all active:scale-95"
        >
          <div className="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-tontigo-blue">
            <Users size={24} strokeWidth={2.5} />
          </div>
          <span className="text-xs font-bold text-slate-700">Rejoindre</span>
        </button>
      </div>

      {/* TONNTINES LIST */}
      <div className="mb-8">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-xl font-bold text-slate-800">Mes Tontines</h2>
          <button onClick={() => onNavigate('tontines')} className="text-xs font-bold text-tontigo-orange flex items-center gap-1">
            Voir tout <ArrowUpRight size={14} />
          </button>
        </div>

        <div className="space-y-4">
          {groups.length === 0 ? (
            <div className="tontigo-card p-10 text-center border-dashed border-2 border-slate-200 bg-transparent">
              <p className="text-slate-400 text-sm mb-4">Vous n'avez pas encore de tontine active.</p>
              <button onClick={onNewGroup} className="btn-tontigo py-3 text-sm">Commencer maintenant</button>
            </div>
          ) : (
            groups.slice(0, 3).map((group) => (
              <div 
                key={group.id}
                onClick={() => onSelectGroup(group)}
                className="tontigo-card p-5 flex items-center gap-4 cursor-pointer"
              >
                <div className="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 flex-shrink-0 font-bold text-xl">
                  {group.name[0]}
                </div>
                <div className="flex-1 min-w-0">
                  <h3 className="font-bold text-slate-800 truncate mb-1">{group.name}</h3>
                  <div className="flex items-center gap-2">
                    <span className="text-[10px] font-bold text-tontigo-orange bg-orange-50 px-2 py-0.5 rounded uppercase">{group.cycle}</span>
                    <span className="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{group.amount.toLocaleString()} F</span>
                  </div>
                  
                  {/* Progress Bar */}
                  <div className="mt-3 w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div 
                      className="h-full bg-tontigo-orange" 
                      style={{ width: `${(group.current_cycle / group.members) * 100 || 10}%` }}
                    />
                  </div>
                </div>
                <ChevronRight size={18} className="text-slate-300" />
              </div>
            ))
          )}
        </div>
      </div>

      {/* YAO INSIGHT CARD */}
      <div 
        onClick={() => onNavigate('assistant_yao')}
        className="bg-white border-2 border-slate-100 rounded-[28px] p-6 mb-10 flex items-center gap-5 cursor-pointer hover:border-tontigo-orange/30 transition-all group shadow-sm"
      >
        <div className="w-14 h-14 bg-gradient-to-br from-tontigo-orange to-orange-300 rounded-full flex items-center justify-center text-white shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform">
          <Activity size={28} />
        </div>
        <div className="flex-1">
          <h4 className="font-bold text-slate-800 text-sm mb-1">Conseil de YAO</h4>
          <p className="text-xs text-slate-500 leading-relaxed">"Votre score est excellent ! Vous pouvez augmenter vos gains en parrainant des membres."</p>
        </div>
        <div className="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-300">
           <ArrowUpRight size={16} />
        </div>
      </div>

      {/* Join Group Modal */}
      <AnimatePresence>
        {showJoinModal && (
          <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <motion.div 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setShowJoinModal(false)}
              className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
            />
            <motion.div 
              initial={{ opacity: 0, scale: 0.9, y: 20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.9, y: 20 }}
              className="bg-white p-8 rounded-[32px] max-w-sm w-full relative z-10 shadow-2xl"
            >
              <h3 className="text-2xl font-bold text-slate-800 mb-2">Rejoindre</h3>
              <p className="text-slate-500 text-sm mb-8">Entrez le code d'invitation de votre groupe de tontine.</p>
              
              <div className="space-y-6">
                <input 
                  type="text" 
                  value={inviteCode}
                  onChange={(e) => setInviteCode(e.target.value.toUpperCase())}
                  placeholder="EX: TONTINE-2026"
                  className="tontigo-input text-center text-lg font-bold tracking-widest placeholder:font-normal placeholder:tracking-normal"
                />
                <button 
                  disabled={!inviteCode || joining}
                  className="btn-tontigo w-full justify-center py-4 shadow-xl"
                  onClick={() => {/* Appel API Join Group */}}
                >
                  {joining ? <Loader2 className="w-5 h-5 animate-spin" /> : 'Confirmer'}
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
