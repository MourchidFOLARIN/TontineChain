import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  ArrowLeft, 
  Users, 
  ShieldAlert, 
  CalendarDays, 
  Gavel, 
  RefreshCcw, 
  CheckCircle2,
  Clock,
  Wallet,
  Loader2,
  ArrowRightLeft,
  ShieldCheck
} from 'lucide-react';
import { getGroupStats, submitBid, proposeSwap, castVote } from '../services/api';

const GroupDetails = ({ group, onBack, user }) => {
  const [activeTab, setActiveTab] = useState('membres'); // membres, encheres, votes
  const [bidAmount, setBidAmount] = useState(0);
  const [loadingAction, setLoadingAction] = useState(false);

  const handleProposeSwap = async (targetPosition) => {
    setLoadingAction(true);
    try {
      await proposeSwap(group.id, { target_position: targetPosition });
      alert("Proposition de permutation envoyée au vote !");
    } catch (err) {
      console.error(err);
      alert("Proposition envoyée (Démo)");
    } finally {
      setLoadingAction(false);
    }
  };

  // Mock data for members
  const members = [
    { id: 1, name: "Koffi A.", status: "paid", position: 1, payout_date: "15 Mai 2026", has_taken: true },
    { id: 2, name: "Amina S.", status: "paid", position: 2, payout_date: "15 Juin 2026", has_taken: false },
    { id: 3, name: user?.first_name + " " + user?.last_name, status: "pending", position: 3, payout_date: "15 Juillet 2026", has_taken: false, isMe: true },
    { id: 4, name: "Jean D.", status: "late", position: 4, payout_date: "15 Aout 2026", has_taken: false },
  ];

  const handleBid = async () => {
    setLoadingAction(true);
    try {
      await submitBid(group.id, { amount: bidAmount });
      alert("Enchère soumise avec succès !");
    } catch (err) {
      console.error(err);
      alert("Enchère soumise (Démo)");
    } finally {
      setLoadingAction(false);
    }
  };

  const handleVote = async (voteId, type) => {
    setLoadingAction(true);
    try {
      await castVote(voteId, { type });
      alert("Vote enregistré !");
    } catch (err) {
      console.error(err);
      alert("Vote enregistré (Démo)");
    } finally {
      setLoadingAction(false);
    }
  };

  return (
    <div className="min-h-screen bg-tontine-darker text-white font-inter relative overflow-hidden pb-20">
      {/* Background elements */}
      <div className="absolute top-0 right-0 w-full h-64 bg-gradient-to-b from-tontine-orange/20 to-transparent pointer-events-none" />

      {/* Header */}
      <nav className="glass-panel border-b border-white/5 px-6 py-4 sticky top-0 z-50 flex items-center gap-4">
        <button 
          onClick={onBack}
          className="p-2 bg-white/5 hover:bg-white/10 rounded-full transition-colors"
        >
          <ArrowLeft className="w-5 h-5 text-gray-300" />
        </button>
        <div className="flex-1">
          <div className="flex items-center gap-2">
            <h1 className="font-playfair font-bold text-xl">{group?.name || 'Détails du Groupe'}</h1>
            <span className="text-[10px] bg-tontine-orange/20 text-tontine-orange px-2 py-0.5 rounded-lg border border-tontine-orange/30 font-mono font-bold tracking-wider">
              CODE: {group?.code || 'TON-782'}
            </span>
          </div>
          <p className="text-xs text-tontine-orange">Cycle {group?.current_cycle}/{group?.members}</p>
        </div>
      </nav>

      <main className="max-w-4xl mx-auto px-4 py-6 relative z-10">
        
        {/* Key Stats Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <div className="glass-panel p-4 rounded-2xl border-l-4 border-tontine-orange">
            <Wallet className="w-5 h-5 text-gray-400 mb-2" />
            <p className="text-xs text-gray-400">Cotisation</p>
            <p className="font-bold">{group?.amount?.toLocaleString() || 50000} F</p>
          </div>
          <div className="glass-panel p-4 rounded-2xl border-l-4 border-blue-500">
            <ShieldAlert className="w-5 h-5 text-gray-400 mb-2" />
            <p className="text-xs text-gray-400">Caisse Secours</p>
            <p className="font-bold">25 000 F</p>
          </div>
          <div className="glass-panel p-4 rounded-2xl border-l-4 border-green-500">
            <Users className="w-5 h-5 text-gray-400 mb-2" />
            <p className="text-xs text-gray-400">Membres</p>
            <p className="font-bold">{group?.members || 10}</p>
          </div>
          <div className="glass-panel p-4 rounded-2xl border-l-4 border-purple-500">
            <CalendarDays className="w-5 h-5 text-gray-400 mb-2" />
            <p className="text-xs text-gray-400">Fréquence</p>
            <p className="font-bold">{group?.cycle || 'Mensuel'}</p>
          </div>
        </div>

        {/* Action Tabs */}
        <div className="flex gap-2 p-1 bg-white/5 rounded-xl mb-6 overflow-x-auto hide-scrollbar">
          <button 
            onClick={() => setActiveTab('membres')}
            className={`flex-1 min-w-[120px] py-2.5 px-4 rounded-lg text-sm font-medium transition-all ${activeTab === 'membres' ? 'bg-tontine-orange text-white shadow-lg' : 'text-gray-400 hover:text-white'}`}
          >
            Membres & Statut
          </button>
          <button 
            onClick={() => setActiveTab('encheres')}
            className={`flex-1 min-w-[120px] flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg text-sm font-medium transition-all ${activeTab === 'encheres' ? 'bg-gradient-to-r from-blue-600 to-blue-400 text-white shadow-lg' : 'text-gray-400 hover:text-white'}`}
          >
            <Gavel className="w-4 h-4" /> Enchères
          </button>
          <button 
            onClick={() => setActiveTab('votes')}
            className={`flex-1 min-w-[120px] flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg text-sm font-medium transition-all ${activeTab === 'votes' ? 'bg-gradient-to-r from-purple-600 to-purple-400 text-white shadow-lg' : 'text-gray-400 hover:text-white'}`}
          >
            <RefreshCcw className="w-4 h-4" /> Swaps
          </button>
        </div>

        {/* Tab Content */}
        <AnimatePresence mode="wait">
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className="glass-panel p-5 rounded-xl mb-6 border border-yellow-500/20 bg-yellow-500/5"
          >
            <div className="flex justify-between items-center mb-3">
              <h4 className="text-sm font-bold flex items-center gap-2">
                <ShieldAlert className="w-4 h-4 text-yellow-500" />
                Analyse de Risque IA
              </h4>
              <span className="text-[10px] bg-yellow-500 text-black px-2 py-0.5 rounded-full font-bold uppercase">Modéré</span>
            </div>
            
            <div className="space-y-3">
              <p className="text-[11px] text-gray-300 leading-relaxed">
                L'IA a scanné les membres : <span className="text-white font-medium">2 membres</span> sans KYC vérifié et <span className="text-white font-medium">1 incident</span> de retard détecté.
              </p>
              
              <div className="w-full bg-white/5 h-1.5 rounded-full overflow-hidden">
                <div className="bg-yellow-500 h-full" style={{ width: '42%' }}></div>
              </div>

              <div className="bg-black/20 p-3 rounded-lg border-l-2 border-yellow-500">
                <p className="text-[10px] text-yellow-500 font-bold uppercase mb-1">Recommandation IA :</p>
                <p className="text-[11px] text-gray-400 italic">"Risque de défaut modéré. Nous conseillons l'activation du fonds de garantie pour ce cycle."</p>
              </div>
            </div>
          </motion.div>

          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.1 }}
            className="glass-panel p-4 rounded-xl mb-8 border border-red-500/10"
          >
            <h4 className="text-[10px] font-bold text-red-400 uppercase tracking-widest mb-3 flex items-center gap-2">
              <ShieldAlert className="w-3 h-3" /> Historique des Incidents
            </h4>
            <div className="space-y-2">
              <div className="flex justify-between items-center text-[11px] py-2 border-b border-white/5">
                <span className="text-gray-300">Retard de paiement - Jean D.</span>
                <span className="text-red-400 font-bold">+2.500 F Amende</span>
              </div>
              <div className="flex justify-between items-center text-[11px] py-2 border-b border-white/5 opacity-50">
                <span className="text-gray-300">Régularisation - Jean D.</span>
                <span className="text-green-400 font-bold">Soldé</span>
              </div>
            </div>
          </motion.div>

          {activeTab === 'membres' && (
            <motion.div 
              key="membres"
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="space-y-3"
            >
              {members.map((m) => (
                <div key={m.id} className={`glass-panel p-4 rounded-xl flex items-center justify-between ${m.isMe ? 'border border-tontine-orange/50 bg-tontine-orange/5' : ''}`}>
                  <div className="flex items-center gap-4">
                    <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg
                      ${m.has_taken ? 'bg-green-500/20 text-green-400' : 'bg-white/10 text-gray-300'}`}>
                      {m.position}
                    </div>
                    <div>
                      <div className="flex items-center gap-2">
                        <h4 className="font-bold text-white">{m.name} {m.isMe && '(Moi)'}</h4>
                        {!m.isMe && !m.has_taken && (
                          <button 
                            onClick={() => handleProposeSwap(m.position)}
                            className="p-1 bg-white/5 hover:bg-tontine-gold/20 rounded-md text-tontine-gold transition-colors"
                            title="Proposer une permutation"
                          >
                            <ArrowRightLeft className="w-3.5 h-3.5" />
                          </button>
                        )}
                      </div>
                      <p className="text-xs text-gray-400">{m.payout_date}</p>
                    </div>
                  </div>
                  <div className="text-right">
                    {m.status === 'paid' && <span className="inline-flex items-center gap-1 text-xs text-green-400 bg-green-400/10 px-2 py-1 rounded-full mb-1"><CheckCircle2 className="w-3 h-3"/> Payé</span>}
                    {m.status === 'pending' && <span className="inline-flex items-center gap-1 text-xs text-yellow-400 bg-yellow-400/10 px-2 py-1 rounded-full mb-1"><Clock className="w-3 h-3"/> En attente</span>}
                    {m.status === 'late' && <span className="inline-flex items-center gap-1 text-xs text-red-400 bg-red-400/10 px-2 py-1 rounded-full mb-1">Retard</span>}
                    
                    {m.has_taken && (
                      <div className="mt-1">
                        <a 
                          href={`https://polygonscan.com/tx/0x${Math.random().toString(16).slice(2, 10)}...`} 
                          target="_blank" 
                          rel="noopener noreferrer"
                          className="text-[9px] text-blue-400 hover:text-blue-300 underline flex items-center justify-end gap-1"
                        >
                          <ShieldCheck className="w-2.5 h-2.5" />
                          Preuve Blockchain
                        </a>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </motion.div>
          )}

          {activeTab === 'encheres' && (
            <motion.div 
              key="enchères"
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="space-y-6"
            >
              <div className="glass-panel p-6 rounded-2xl border border-tontine-gold/20">
                <h3 className="text-lg font-bold text-tontine-gold mb-4 flex items-center gap-2">
                  <Gavel className="w-5 h-5" /> Enchère en cours
                </h3>
                
                {/* Active Bids List */}
                <div className="space-y-3 mb-6">
                  <div className="flex justify-between items-center p-3 bg-white/5 rounded-xl border border-white/5">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-full bg-tontine-gold/20 flex items-center justify-center text-tontine-gold font-bold text-xs">1</div>
                      <span className="text-sm font-medium">Amina Soule</span>
                    </div>
                    <span className="text-tontine-gold font-bold">5.000 F Décote</span>
                  </div>
                  <div className="flex justify-between items-center p-3 bg-tontine-gold/10 rounded-xl border border-tontine-gold/20">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-full bg-tontine-gold flex items-center justify-center text-tontine-darker font-bold text-xs">2</div>
                      <span className="text-sm font-bold">Vous</span>
                    </div>
                    <span className="text-tontine-gold font-bold">{bidAmount.toLocaleString()} F Décote</span>
                  </div>
                </div>

                <p className="text-xs text-gray-400 mb-6 leading-relaxed">
                  En proposant une décote, vous pouvez ramasser le pot immédiatement. La décote sera redistribuée aux autres membres.
                </p>
              
              <div className="bg-white/5 p-4 rounded-xl mb-6 text-left">
                <label className="block text-sm text-gray-400 mb-2">Décote proposée (FCFA)</label>
                <input 
                  type="range" 
                  min="0" 
                  max="25000" 
                  step="1000" 
                  value={bidAmount}
                  onChange={(e) => setBidAmount(parseInt(e.target.value))}
                  className="w-full accent-blue-500 mb-2" 
                />
                <div className="flex justify-between font-bold">
                  <span>Pot Final : {(group?.amount * group?.members - bidAmount).toLocaleString()} F</span>
                  <span className="text-blue-400">-{bidAmount.toLocaleString()} F</span>
                </div>
              </div>

              <button 
                disabled={loadingAction || bidAmount === 0}
                onClick={handleBid}
                className="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-4 rounded-xl transition duration-300 flex items-center justify-center gap-2"
              >
                {loadingAction ? <Loader2 className="w-5 h-5 animate-spin" /> : "Soumettre mon enchère"}
              </button>
            </div>
          </motion.div>
        )}

          {activeTab === 'votes' && (
            <motion.div 
              key="votes"
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.95 }}
              className="glass-panel p-6 rounded-2xl"
            >
              <div className="flex items-center gap-3 mb-6">
                <div className="w-10 h-10 bg-purple-500/20 text-purple-400 rounded-full flex items-center justify-center">
                  <RefreshCcw className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="font-bold">Demandes de Permutation</h3>
                  <p className="text-xs text-gray-400">Votez pour autoriser les échanges.</p>
                </div>
              </div>

              <div className="bg-white/5 border border-purple-500/30 p-4 rounded-xl">
                <p className="text-sm mb-4">
                  <strong className="text-white">Jean D.</strong> (Position 4) souhaite échanger sa position avec <strong className="text-white">Amina S.</strong> (Position 3).
                </p>
                <div className="w-full bg-tontine-dark rounded-full h-2 mb-4">
                  <div className="bg-purple-500 h-2 rounded-full" style={{ width: '40%' }}></div>
                </div>
                <p className="text-xs text-gray-400 mb-4">4/10 votes recueillis (Majorité requise : 6)</p>
                
                <div className="flex gap-2">
                  <button 
                    disabled={loadingAction}
                    onClick={() => handleVote(1, 'yes')}
                    className="flex-1 bg-green-500/20 hover:bg-green-500/40 text-green-400 py-2 rounded-lg text-sm font-bold transition-colors flex items-center justify-center gap-2"
                  >
                    {loadingAction ? <Loader2 className="w-4 h-4 animate-spin" /> : "Pour"}
                  </button>
                  <button 
                    disabled={loadingAction}
                    onClick={() => handleVote(1, 'no')}
                    className="flex-1 bg-red-500/20 hover:bg-red-500/40 text-red-400 py-2 rounded-lg text-sm font-bold transition-colors flex items-center justify-center gap-2"
                  >
                    {loadingAction ? <Loader2 className="w-4 h-4 animate-spin" /> : "Contre"}
                  </button>
                </div>
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </main>
    </div>
  );
};

export default GroupDetails;
