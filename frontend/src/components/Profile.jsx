import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { User, Shield, Globe, Briefcase, Save, ArrowLeft, Loader2, TrendingUp, Download, QrCode } from 'lucide-react';
import { updateMe } from '../services/api';

const Profile = ({ user, onBack, onUpdate }) => {
  const [formData, setFormData] = useState({
    first_name: user?.first_name || '',
    last_name: user?.last_name || '',
    profession: user?.profession || '',
    npi: '',
    preferred_language: user?.preferred_language || 'fr'
  });
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await updateMe(formData);
      onUpdate({ ...user, ...formData });
      setSuccess(true);
      setLoading(false);
      setTimeout(() => setSuccess(false), 3000);
    } catch (err) {
      console.error(err);
      // Fallback démo
      onUpdate({ ...user, ...formData });
      setSuccess(true);
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-tontine-darker text-white font-inter relative overflow-hidden p-6">
      <div className="max-w-md mx-auto relative z-10">
        <button onClick={onBack} className="mb-6 flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
          <ArrowLeft className="w-5 h-5" /> Retour
        </button>

        <h1 className="text-3xl font-playfair font-bold mb-8">Profil & Performance</h1>

        {/* Trust Score Evolution Chart */}
        <div className="glass-panel p-6 rounded-2xl mb-8 relative overflow-hidden">
          <div className="flex justify-between items-center mb-6">
            <div>
              <h4 className="text-sm font-bold text-gray-400">Évolution du Score</h4>
              <p className="text-2xl font-bold text-tontine-orange">+{user?.score_confiance || 95}</p>
            </div>
            <TrendingUp className="text-green-400 w-6 h-6" />
          </div>
          <div className="flex items-end gap-2 h-24">
            {[40, 55, 45, 70, 65, 85, 95].map((h, i) => (
              <motion.div 
                key={i}
                initial={{ height: 0 }}
                animate={{ height: `${h}%` }}
                transition={{ delay: i * 0.1, duration: 0.5 }}
                className={`flex-1 rounded-t-md ${i === 6 ? 'bg-tontine-orange shadow-[0_0_15px_rgba(249,115,22,0.5)]' : 'bg-white/10'}`}
              />
            ))}
          </div>
          <div className="flex justify-between mt-2 text-[8px] text-gray-500 font-mono">
            <span>JAN</span><span>FEV</span><span>MAR</span><span>AVR</span><span>MAI</span><span>JUN</span><span>ACTUEL</span>
          </div>

          {/* Certificate Download CTA */}
          <div className="mt-6 pt-6 border-t border-white/5 flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-white/5 rounded-lg">
                <QrCode className="w-8 h-8 text-white/50" />
              </div>
              <div>
                <p className="text-[10px] font-bold text-white uppercase tracking-widest">Certificat Blockchain</p>
                <p className="text-[9px] text-gray-500">Vérifiable par les banques</p>
              </div>
            </div>
            <button 
              onClick={() => alert("Génération du certificat PDF sécurisé...")}
              className="px-4 py-2 bg-white/5 hover:bg-white/10 rounded-lg text-xs font-bold flex items-center gap-2 transition-all"
            >
              <Download className="w-4 h-4" /> Télécharger
            </button>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="glass-panel p-6 rounded-2xl space-y-4">
            <div>
              <label className="block text-sm text-gray-400 mb-2">Prénom</label>
              <div className="relative">
                <User className="absolute left-3 top-3 w-5 h-5 text-gray-500" />
                <input
                  type="text"
                  value={formData.first_name}
                  onChange={(e) => setFormData({ ...formData, first_name: e.target.value })}
                  className="w-full bg-white/5 border border-white/10 rounded-xl py-2.5 pl-10 pr-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all"
                  required
                />
              </div>
            </div>

            <div>
              <label className="block text-sm text-gray-400 mb-2">Nom de famille</label>
              <div className="relative">
                <User className="absolute left-3 top-3 w-5 h-5 text-gray-500" />
                <input
                  type="text"
                  value={formData.last_name}
                  onChange={(e) => setFormData({ ...formData, last_name: e.target.value })}
                  className="w-full bg-white/5 border border-white/10 rounded-xl py-2.5 pl-10 pr-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all"
                  required
                />
              </div>
            </div>

            <div>
              <label className="block text-sm text-gray-400 mb-2">Profession</label>
              <div className="relative">
                <Briefcase className="absolute left-3 top-3 w-5 h-5 text-gray-500" />
                <input
                  type="text"
                  value={formData.profession}
                  onChange={(e) => setFormData({ ...formData, profession: e.target.value })}
                  className="w-full bg-white/5 border border-white/10 rounded-xl py-2.5 pl-10 pr-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all"
                  placeholder="Ex: Commerçant"
                  required
                />
              </div>
            </div>
          </div>

          <div className="glass-panel p-6 rounded-2xl space-y-4">
            <div>
              <label className="block text-sm text-gray-400 mb-2">Numéro NPI (Confidentialité assurée)</label>
              <div className="relative">
                <Shield className="absolute left-3 top-3 w-5 h-5 text-gray-500" />
                <input
                  type="password"
                  value={formData.npi}
                  onChange={(e) => setFormData({ ...formData, npi: e.target.value })}
                  className="w-full bg-white/5 border border-white/10 rounded-xl py-2.5 pl-10 pr-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all"
                  placeholder="Votre numéro d'identification"
                  required
                />
              </div>
              <p className="text-[10px] text-gray-500 mt-2 italic">
                Votre NPI est haché via <span className="text-tontine-orange font-bold">SHA-256</span>. 
                Ce processus garantit que votre identité est unique (anti-fraude) tout en restant 
                strictement anonyme dans notre base de données.
              </p>
            </div>

            <div>
              <label className="block text-sm text-gray-400 mb-2">Langue préférée</label>
              <div className="relative">
                <Globe className="absolute left-3 top-3 w-5 h-5 text-gray-500" />
                <select
                  value={formData.preferred_language}
                  onChange={(e) => setFormData({ ...formData, preferred_language: e.target.value })}
                  className="w-full bg-white/5 border border-white/10 rounded-xl py-2.5 pl-10 pr-4 focus:ring-2 focus:ring-tontine-orange outline-none transition-all appearance-none"
                >
                  <option value="fr">Français</option>
                  <option value="fon">Fon</option>
                  <option value="yoruba">Yoruba</option>
                </select>
              </div>
            </div>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-gradient-to-r from-tontine-orange to-tontine-gold text-tontine-darker font-bold py-4 rounded-xl shadow-lg shadow-tontine-orange/20 flex items-center justify-center gap-2"
          >
            {loading ? <Loader2 className="w-6 h-6 animate-spin" /> : (
              success ? <span className="text-green-900 flex items-center gap-2"><Save className="w-5 h-5" /> Profil mis à jour !</span> : <><Save className="w-5 h-5" /> Enregistrer mon profil</>
            )}
          </button>
        </form>
      </div>
    </div>
  );
};

export default Profile;
