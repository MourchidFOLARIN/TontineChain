import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Users, Wallet, Calendar, Shield, ArrowLeft, Loader2, Plus, Info } from 'lucide-react';
import { createGroup } from '../services/api';

import logoOfficial from '../assets/logo_official.png';

const CreateGroup = ({ onBack, onCreate }) => {
  const [formData, setFormData] = useState({
    name: '',
    contribution_amount: 10000,
    frequency: 'monthly',
    max_members: 10,
    payout_method: 'sequential',
    insurance_opt_in: true,
    start_date: new Date(Date.now() + 86400000).toISOString().split('T')[0]
  });
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await createGroup(formData);
      onCreate(formData);
    } catch (err) {
      console.error(err);
      onCreate(formData);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-slate-50 min-h-screen">
      {/* Header */}
      <div className="p-6 bg-white border-b border-slate-100 flex items-center justify-between sticky top-0 z-30">
        <div className="flex items-center gap-4">
          <button onClick={onBack} className="p-2 hover:bg-slate-50 rounded-full transition-colors text-slate-400">
            <ArrowLeft size={22} />
          </button>
          <h2 className="text-xl font-bold text-slate-800">Nouveau Groupe</h2>
        </div>
        <img src={logoOfficial} alt="Logo" className="w-8 h-8 rounded-full" />
      </div>

      <div className="p-6 max-w-lg mx-auto">
        <form onSubmit={handleSubmit} className="space-y-6">
          
          <div className="TontineChain-card p-6 space-y-5">
            <div>
              <label className="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Nom de la Tontine</label>
              <input
                type="text"
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                className="TontineChain-input"
                placeholder="Ex: Tontine Famille"
                required
              />
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Montant (FCFA)</label>
              <div className="relative">
                <Wallet className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300" />
                <input
                  type="number"
                  value={formData.contribution_amount}
                  onChange={(e) => setFormData({ ...formData, contribution_amount: e.target.value })}
                  className="TontineChain-input pl-12 font-bold"
                  required
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Membres</label>
                <div className="relative">
                  <Users className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300" />
                  <input
                    type="number"
                    value={formData.max_members}
                    onChange={(e) => setFormData({ ...formData, max_members: e.target.value })}
                    className="TontineChain-input pl-12"
                    required
                  />
                </div>
              </div>
              <div>
                <label className="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Fréquence</label>
                <div className="relative">
                  <Calendar className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-300" />
                  <select
                    value={formData.frequency}
                    onChange={(e) => setFormData({ ...formData, frequency: e.target.value })}
                    className="TontineChain-input pl-12 appearance-none"
                  >
                    <option value="weekly">Hebdo</option>
                    <option value="monthly">Mensuel</option>
                  </select>
                </div>
              </div>
            </div>

            <div className="bg-slate-50 p-4 rounded-2xl flex items-start gap-3">
              <Info size={16} className="text-TontineChain-orange mt-0.5" />
              <p className="text-[11px] text-slate-500 leading-relaxed">
                Le Smart Contract sera généré automatiquement sur la blockchain Polygon dès la création.
              </p>
            </div>
          </div>

          <div className="TontineChain-card p-6 flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center text-TontineChain-orange">
                <Shield size={20} />
              </div>
              <div>
                <p className="text-xs font-bold text-slate-800">Fonds de Garantie (1%)</p>
                <p className="text-[10px] text-slate-400">Assurance collective activée</p>
              </div>
            </div>
            <div className="relative inline-flex items-center cursor-pointer">
              <input 
                type="checkbox" 
                checked={formData.insurance_opt_in}
                onChange={(e) => setFormData({ ...formData, insurance_opt_in: e.target.checked })}
                className="sr-only peer"
              />
              <div className="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-TontineChain-orange"></div>
            </div>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="btn-TontineChain w-full justify-center py-5 shadow-xl shadow-orange-500/20"
          >
            {loading ? <Loader2 className="w-6 h-6 animate-spin" /> : (
              <><Plus className="w-6 h-6" /> Lancer la Tontine</>
            )}
          </button>
        </form>
      </div>

      <div className="h-24" />
    </div>
  );
};

export default CreateGroup;
