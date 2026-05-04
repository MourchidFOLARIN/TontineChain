import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Users, Wallet, Calendar, Shield, ArrowLeft, Loader2, Plus } from 'lucide-react';
import { createGroup } from '../services/api';

const CreateGroup = ({ onBack, onCreate }) => {
  const [formData, setFormData] = useState({
    name: '',
    amount: 10000,
    frequency: 'monthly',
    max_members: 10,
    insurance_percent: 5
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

        <h1 className="text-3xl font-playfair font-bold mb-8">Créer une nouvelle Tontine</h1>

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
                  value={formData.amount}
                  onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
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

            <div>
              <label className="block text-sm text-gray-400 mb-2">Caisse de Secours (%)</label>
              <div className="relative">
                <Shield className="absolute left-3 top-3.5 w-5 h-5 text-gray-500" />
                <input
                  type="number"
                  value={formData.insurance_percent}
                  onChange={(e) => setFormData({ ...formData, insurance_percent: e.target.value })}
                  className="w-full bg-white/5 border border-white/10 rounded-xl py-3 pl-10 pr-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all"
                  required
                />
              </div>
              <p className="text-[10px] text-gray-500 mt-2">Part prélevée pour les urgences du groupe.</p>
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
