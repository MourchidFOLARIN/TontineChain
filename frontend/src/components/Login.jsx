import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ArrowRight, ShieldCheck, Mail, Loader2, User, Phone } from 'lucide-react';
import api from '../services/api';

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
    <div className="min-h-screen flex items-center justify-center bg-slate-50 px-4">
      
      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-white p-8 sm:p-12 rounded-[32px] max-w-md w-full shadow-xl shadow-slate-200/50 border border-slate-100"
      >
        
        {/* Header Logo */}
        <div className="text-center mb-10">
          <img src={logoOfficial} alt="Logo" className="w-20 h-20 mx-auto mb-6 rounded-full border-2 border-tontigo-orange shadow-lg" />
          <h1 className="text-2xl font-bold text-slate-800 mb-2 tracking-tight">TontiGo</h1>
          <p className="text-slate-400 text-sm mb-8 font-medium">L'épargne solidaire nouvelle génération</p>
          
          <div className="flex justify-center gap-2 mb-2">
            {['fr', 'yor', 'fon'].map((l) => (
              <button 
                key={l}
                onClick={() => setLocale(l)}
                className={`px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider transition-all ${locale === l ? 'bg-tontigo-orange text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'}`}
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
              initial={{ opacity: 0, x: -10 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: 10 }}
              onSubmit={handleRequestOtp}
              className="space-y-6"
            >
              <div>
                <label className="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Adresse Email</label>
                <div className="relative">
                  <Mail className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="tontigo-input pl-12"
                    placeholder="nom@exemple.com"
                    required
                  />
                </div>
              </div>

              {error && <p className="text-red-500 text-xs text-center font-medium bg-red-50 p-3 rounded-xl">{error}</p>}

              <button
                type="submit"
                disabled={loading}
                className="btn-tontigo w-full justify-center py-4"
              >
                {loading ? <Loader2 className="w-6 h-6 animate-spin" /> : (
                  <>
                    Continuer <ArrowRight className="w-5 h-5" />
                  </>
                )}
              </button>
            </motion.form>
          ) : step === 'otp' ? (
            <motion.form 
              key="otp"
              initial={{ opacity: 0, x: -10 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: 10 }}
              onSubmit={handleVerifyOtp}
            >
              <div className="text-center mb-8">
                <p className="text-slate-400 text-sm mb-1">Entrez le code envoyé à</p>
                <p className="text-slate-800 font-bold">{email}</p>
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
                    className="w-12 h-14 text-center text-xl font-bold bg-slate-100 border-2 border-transparent rounded-xl text-slate-800 focus:bg-white focus:border-tontigo-orange outline-none transition-all"
                  />
                ))}
              </div>

              {error && <p className="text-red-500 text-xs text-center font-medium bg-red-50 p-3 rounded-xl mb-4">{error}</p>}

              <button
                type="submit"
                disabled={loading || otp.join('').length < 6}
                className="btn-tontigo w-full justify-center py-4"
              >
                {loading ? <Loader2 className="w-6 h-6 animate-spin" /> : (
                  <>
                    Vérifier <ShieldCheck className="w-5 h-5" />
                  </>
                )}
              </button>
              
              <button 
                type="button" 
                onClick={() => setStep('email')}
                className="w-full mt-4 text-slate-400 hover:text-slate-600 text-xs font-bold uppercase tracking-widest transition-colors"
              >
                Changer d'email
              </button>
            </motion.form>
          ) : (
            <motion.form 
              key="profile"
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              onSubmit={handleUpdateProfile}
              className="space-y-4"
            >
              <div className="text-center mb-6">
                <h3 className="text-xl font-bold text-slate-800">Presque fini !</h3>
                <p className="text-xs text-slate-400 font-medium">Complétez vos informations de profil.</p>
              </div>

              <div className="space-y-4">
                <div className="relative">
                  <User className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                  <input
                    type="text"
                    placeholder="Prénom"
                    value={profileData.first_name}
                    onChange={(e) => setProfileData({...profileData, first_name: e.target.value})}
                    className="tontigo-input pl-12"
                    required
                  />
                </div>
                <div className="relative">
                  <User className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                  <input
                    type="text"
                    placeholder="Nom"
                    value={profileData.last_name}
                    onChange={(e) => setProfileData({...profileData, last_name: e.target.value})}
                    className="tontigo-input pl-12"
                    required
                  />
                </div>
                <div className="relative">
                  <Phone className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                  <input
                    type="tel"
                    placeholder="Téléphone"
                    value={profileData.phone}
                    onChange={(e) => setProfileData({...profileData, phone: e.target.value})}
                    className="tontigo-input pl-12"
                    required
                  />
                </div>
              </div>

              <button
                type="submit"
                disabled={loading}
                className="btn-tontigo w-full justify-center py-4 mt-4"
              >
                {loading ? <Loader2 className="w-5 h-5 animate-spin mx-auto" /> : "Accéder à mon espace"}
              </button>
            </motion.form>
          )}
        </AnimatePresence>
      </motion.div>
    </div>
  );
};

export default Login;
