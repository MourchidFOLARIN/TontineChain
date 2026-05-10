import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ArrowRight, ShieldCheck, Mail, CheckCircle2, Loader2, User, Phone } from 'lucide-react';
import api from '../services/api';
import AudioButton from './AudioButton';

import logoOfficial from '../assets/logo_official.png';

const Login = ({ onLoginSuccess }) => {
  const [step, setStep] = useState('email'); // 'email' | 'otp' | 'profile'
  const [email, setEmail] = useState('');
  const [otp, setOtp] = useState(['', '', '', '', '', '']);
  const [profileData, setProfileData] = useState({ first_name: '', last_name: '', phone: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [locale, setLocale] = useState('fr');

  const handleRequestOtp = async (e) => {
    e.preventDefault();
    if (!email.includes('@')) {
      setError('Veuillez entrer une adresse email valide.');
      return;
    }
    
    setLoading(true);
    setError('');
    try {
      await api.post('/auth/request-otp', { email, locale });
      setStep('otp');
    } catch (err) {
      setError(err.response?.data?.message || 'Erreur lors de la demande OTP.');
    } finally {
      setLoading(false);
    }
  };

  const handleOtpChange = (element, index) => {
    if (isNaN(element.value)) return;
    const newOtp = [...otp];
    newOtp[index] = element.value;
    setOtp(newOtp);

    // Focus next input
    if (element.nextSibling && element.value !== '') {
      element.nextSibling.focus();
    }
  };

  const handleVerifyOtp = async (e) => {
    e.preventDefault();
    const code = otp.join('');
    if (code.length < 6) return;

    setLoading(true);
    setError('');
    try {
      const response = await api.post('/auth/verify-otp', { email, code });
      const { access_token, user, needs_profile_completion } = response.data;
      
      localStorage.setItem('tontine_token', access_token);
      localStorage.setItem('tontine_user', JSON.stringify(user));
      
      if (needs_profile_completion) {
        setStep('profile');
      } else {
        onLoginSuccess(user);
      }
    } catch (err) {
      console.error("Erreur OTP:", err);
      setError(err.response?.data?.error || 'Code incorrect ou expiré.');
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateProfile = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const response = await api.post('/user/profile', profileData);
      localStorage.setItem('tontine_user', JSON.stringify(response.data.user));
      onLoginSuccess(response.data.user);
    } catch (err) {
      setError(err.response?.data?.message || 'Erreur lors de la mise à jour du profil.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-tontine-darker relative overflow-hidden px-4">
      
      {/* Background Glow Effects */}
      <div className="absolute top-[-10%] left-[-10%] w-96 h-96 bg-tontine-orange/20 rounded-full blur-[120px] pointer-events-none" />
      <div className="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-blue-900/20 rounded-full blur-[120px] pointer-events-none" />

      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, ease: "easeOut" }}
        className="glass-panel p-8 sm:p-12 rounded-3xl max-w-md w-full relative z-10 shadow-2xl shadow-black/50 border border-white/5"
      >
        
        {/* Header Logo */}
        <div className="text-center mb-10">
          <motion.div 
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            transition={{ type: "spring", stiffness: 200, delay: 0.2 }}
            className="w-24 h-24 mx-auto mb-6 relative"
          >
            <div className="absolute inset-0 bg-tontine-orange/20 rounded-full blur-xl animate-pulse" />
            <img src={logoOfficial} alt="Logo" className="w-full h-full rounded-full border-2 border-tontine-gold shadow-2xl relative z-10" />
          </motion.div>
          <h1 className="text-3xl font-playfair font-bold text-white mb-2">TontineChain</h1>
          <p className="text-gray-400 text-sm font-inter mb-6 uppercase tracking-widest">Élite & Sécurité</p>
          
          <div className="flex justify-center gap-4 mb-2">
            {['fr', 'yor', 'fon'].map((l) => (
              <button 
                key={l}
                onClick={() => setLocale(l)}
                className={`px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest transition-all ${locale === l ? 'bg-tontine-orange text-tontine-darker shadow-lg shadow-tontine-orange/20' : 'bg-white/5 text-gray-500 hover:text-gray-300'}`}
              >
                {l === 'yor' ? 'Yoruba' : l === 'fon' ? 'Fongbe' : 'Français'}
              </button>
            ))}
          </div>
        </div>

        <AnimatePresence mode="wait">
          {step === 'email' ? (
            <motion.form 
              key="email"
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: 20 }}
              onSubmit={handleRequestOtp}
            >
              <div className="space-y-6">
                <div>
                  <label className="block text-sm font-medium text-gray-400 mb-2 ml-1">Identifiant Email</label>
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                      <Mail className="h-5 w-5 text-tontine-orange" />
                    </div>
                    <input
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      className="block w-full pl-12 pr-4 py-4 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-600 focus:ring-2 focus:ring-tontine-orange focus:border-transparent transition-all outline-none"
                      placeholder="votre@email.com"
                      required
                    />
                  </div>
                </div>

                {error && <p className="text-red-400 text-sm text-center">{error}</p>}

                <button
                  type="submit"
                  disabled={loading}
                  className="group relative w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl text-md font-bold text-tontine-darker bg-gradient-to-r from-tontine-orange to-tontine-gold hover:opacity-90 focus:outline-none transition-all shadow-lg shadow-tontine-orange/20 disabled:opacity-50"
                >
                  {loading ? <Loader2 className="w-6 h-6 animate-spin" /> : (
                    <>
                      Recevoir le code <ArrowRight className="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" />
                    </>
                  )}
                </button>
              </div>
            </motion.form>
          ) : step === 'otp' ? (
            <motion.form 
              key="otp"
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: 20 }}
              onSubmit={handleVerifyOtp}
            >
              <div className="text-center mb-8">
                <p className="text-gray-400 mb-2">Clé de sécurité envoyée à</p>
                <p className="text-tontine-gold font-bold tracking-wider">{email}</p>
              </div>

              <div className="flex justify-between mb-8 gap-2">
                {otp.map((data, index) => (
                  <input
                    key={index}
                    type="text"
                    maxLength="1"
                    value={data}
                    onChange={e => handleOtpChange(e.target, index)}
                    onFocus={e => e.target.select()}
                    className="w-12 h-14 text-center text-2xl font-bold bg-white/5 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-tontine-orange outline-none transition-all"
                  />
                ))}
              </div>

              {error && <p className="text-red-400 text-sm text-center mb-4">{error}</p>}

              <button
                type="submit"
                disabled={loading || otp.join('').length < 6}
                className="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl text-md font-bold text-tontine-darker bg-gradient-to-r from-tontine-orange to-tontine-gold hover:opacity-90 transition-all shadow-lg shadow-tontine-orange/20 disabled:opacity-50"
              >
                {loading ? <Loader2 className="w-6 h-6 animate-spin" /> : (
                  <>
                    Vérifier l'identité <ShieldCheck className="ml-2 w-5 h-5" />
                  </>
                )}
              </button>
              
              <button 
                type="button" 
                onClick={() => setStep('email')}
                className="w-full mt-4 text-gray-500 hover:text-white text-sm transition-colors"
              >
                Modifier l'email
              </button>
            </motion.form>
          ) : (
            <motion.form 
              key="profile"
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              onSubmit={handleUpdateProfile}
              className="space-y-5"
            >
              <div className="text-center mb-6">
                <h3 className="text-xl font-bold text-white font-playfair">Bienvenue parmi nous !</h3>
                <p className="text-xs text-gray-500">Une dernière étape pour sécuriser votre compte.</p>
              </div>

              <div className="space-y-4">
                <div className="relative">
                  <User className="absolute left-4 top-3.5 h-5 w-5 text-gray-500" />
                  <input
                    type="text"
                    placeholder="Votre Prénom"
                    value={profileData.first_name}
                    onChange={(e) => setProfileData({...profileData, first_name: e.target.value})}
                    className="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white outline-none focus:ring-2 focus:ring-tontine-orange transition-all"
                    required
                  />
                </div>
                <div className="relative">
                  <User className="absolute left-4 top-3.5 h-5 w-5 text-gray-500" />
                  <input
                    type="text"
                    placeholder="Votre Nom"
                    value={profileData.last_name}
                    onChange={(e) => setProfileData({...profileData, last_name: e.target.value})}
                    className="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white outline-none focus:ring-2 focus:ring-tontine-orange transition-all"
                    required
                  />
                </div>
                <div className="relative">
                  <Phone className="absolute left-4 top-3.5 h-5 w-5 text-gray-500" />
                  <input
                    type="tel"
                    placeholder="Téléphone (ex: +22960000000)"
                    value={profileData.phone}
                    onChange={(e) => setProfileData({...profileData, phone: e.target.value})}
                    className="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white outline-none focus:ring-2 focus:ring-tontine-orange transition-all"
                    required
                  />
                </div>
              </div>

              <button
                type="submit"
                disabled={loading}
                className="w-full mt-4 py-4 bg-gradient-to-r from-tontine-orange to-tontine-gold text-tontine-darker font-bold rounded-xl shadow-lg hover:opacity-90 transition-all"
              >
                {loading ? <Loader2 className="w-5 h-5 animate-spin mx-auto" /> : "Créer mon profil d'Élite"}
              </button>
            </motion.form>
          )}
        </AnimatePresence>
      </motion.div>
    </div>
  );
};

export default Login;
