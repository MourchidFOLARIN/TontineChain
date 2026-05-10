import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Users, Wallet, Calendar, Shield, ArrowLeft, Loader2, Plus } from 'lucide-react';
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
    start_date: new Date(Date.now() + 86400000).toISOString().split('T')[0] // Demain par défaut
  });
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await createGroup(formData);
      onCreate(formData);
      setLoading(false);
    } catch (err) {
      console.error(err);
      // Fallback démo
      onCreate(formData);
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-tontine-darker text-white font-inter p-6 relative overflow-hidden">
      <div className="max-w-md mx-auto relative z-10">
        <button onClick={onBack} className="mb-6 flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
          <ArrowLeft className="w-5 h-5" /> Retour
        </button>

        <div className="flex items-center gap-4 mb-8">
          <img src={logoOfficial} alt="Logo" className="w-12 h-12 rounded-full border border-tontine-gold" />
          <h1 className="text-2xl md:text-3xl font-playfair font-bold">Nouvelle Tontine</h1>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="glass-panel p-6 rounded-2xl space-y-5">
            <div>
              <label className="block text-sm text-gray-400 mb-2">Nom de la Tontine</label>
              <input
                type="text"
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                className="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all"
                placeholder="Ex: Tontine des Commerçants"
                required
              />
            </div>

            <div>
              <label className="block text-sm text-gray-400 mb-2">Montant de la Cotisation (FCFA)</label>
              <div className="relative">
                <Wallet className="absolute left-3 top-3.5 w-5 h-5 text-gray-500" />
                <input
                  type="number"
                  value={formData.contribution_amount}
                  onChange={(e) => setFormData({ ...formData, contribution_amount: e.target.value })}
                  className="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-10 pr-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all font-bold"
                  required
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm text-gray-400 mb-2">Membres max</label>
                <div className="relative">
                  <Users className="absolute left-3 top-3.5 w-5 h-5 text-gray-500" />
                  <input
                    type="number"
                    value={formData.max_members}
                    onChange={(e) => setFormData({ ...formData, max_members: e.target.value })}
                    className="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-10 pr-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all"
                    required
                  />
                </div>
              </div>
              <div>
                <label className="block text-sm text-gray-400 mb-2">Fréquence</label>
                <div className="relative">
                  <Calendar className="absolute left-3 top-3.5 w-5 h-5 text-gray-500" />
                  <select
                    value={formData.frequency}
                    onChange={(e) => setFormData({ ...formData, frequency: e.target.value })}
                    className="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-10 pr-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all appearance-none"
                  >
                    <option value="weekly">Hebdo</option>
                    <option value="monthly">Mensuel</option>
                  </select>
                </div>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm text-gray-400 mb-2">Méthode de Gain</label>
                <select
                  value={formData.payout_method}
                  onChange={(e) => setFormData({ ...formData, payout_method: e.target.value })}
                  className="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all appearance-none"
                >
                  <option value="sequential">Séquentiel</option>
                  <option value="random">Aléatoire</option>
                </select>
              </div>
              <div>
                <label className="block text-sm text-gray-400 mb-2">Date de Début</label>
                <input
                  type="date"
                  value={formData.start_date}
                  onChange={(e) => setFormData({ ...formData, start_date: e.target.value })}
                  className="w-full bg-white/5 border border-white/10 rounded-xl py-3 px-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all"
                  required
                />
              </div>
            </div>

            <div className="flex items-center justify-between bg-white/5 p-4 rounded-xl border border-white/5">
              <div className="flex items-center gap-3">
                <Shield className="w-5 h-5 text-tontine-orange" />
                <div>
                  <p className="text-sm font-bold">Fonds de Garantie (1%)</p>
                  <p className="text-[10px] text-gray-500">Protection contre les impayés.</p>
                </div>
              </div>
              <input 
                type="checkbox" 
                checked={formData.insurance_opt_in}
                onChange={(e) => setFormData({ ...formData, insurance_opt_in: e.target.checked })}
                className="w-5 h-5 rounded border-gray-600 bg-gray-700 text-tontine-orange focus:ring-tontine-orange"
              />
            </div>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-gradient-to-r from-tontine-orange to-tontine-gold text-tontine-darker font-bold py-4 rounded-xl shadow-lg shadow-tontine-orange/20 flex items-center justify-center gap-2 text-lg hover:scale-[1.02] active:scale-[0.98] transition-all"
          >
            {loading ? <Loader2 className="w-6 h-6 animate-spin" /> : (
              <><Plus className="w-6 h-6" /> Créer le Groupe</>
            )}
          </button>
        </form>
      </div>
    </div>
  );
};

export default CreateGroup;
