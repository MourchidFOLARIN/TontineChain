import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ArrowRight, ShieldCheck, Phone, CheckCircle2, Loader2 } from 'lucide-react';
import api from '../services/api';
import AudioButton from './AudioButton';

import logoOfficial from '../assets/logo_official.png';

const Login = ({ onLoginSuccess }) => {
  const [step, setStep] = useState('phone'); // 'phone' | 'otp'
  const [phone, setPhone] = useState('');
  const [otp, setOtp] = useState(['', '', '', '', '', '']);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [locale, setLocale] = useState('fr');

  const handleRequestOtp = async (e) => {
    e.preventDefault();
    if (phone.length < 8) {
      setError('Veuillez entrer un numéro valide.');
      return;
    }
    
    setLoading(true);
    setError('');
    try {
      await api.post('/auth/request-otp', { phone, locale });
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
      const response = await api.post('/auth/verify-otp', { phone, code });
      const { access_token, user } = response.data;
      
      localStorage.setItem('tontine_token', access_token);
      onLoginSuccess(user);
    } catch (err) {
      console.error("Erreur OTP:", err);
      setError(err.response?.data?.error || 'Code incorrect ou expiré.');
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
          <p className="text-gray-400 text-sm font-inter mb-6">Accès sécurisé sans mot de passe</p>
          
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
          {step === 'phone' ? (
            <motion.form 
              key="phone"
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: 20 }}
              onSubmit={handleRequestOtp}
            >
              <div className="space-y-6">
                <div>
                  <div className="flex justify-between items-center mb-2">
                    <label className="block text-sm font-medium text-gray-300">Numéro de téléphone</label>
                    <AudioButton label="Xó xá mì (Fon)" />
                  </div>
                  <div className="relative">
                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                      <Phone className="h-5 w-5 text-gray-500" />
                    </div>
                    <input
                      type="tel"
                      value={phone}
                      onChange={(e) => setPhone(e.target.value)}
                      className="block w-full pl-12 pr-4 py-4 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-tontine-orange focus:border-transparent transition-all outline-none"
                      placeholder="+229 00000000"
                      required
                    />
                  </div>
                </div>

                {error && <p className="text-red-400 text-sm">{error}</p>}

                <button
                  type="submit"
                  disabled={loading}
                  className="group relative w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl text-md font-bold text-tontine-darker bg-gradient-to-r from-tontine-orange to-tontine-gold hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tontine-orange transition-all shadow-lg shadow-tontine-orange/20 disabled:opacity-50"
                >
                  {loading ? <Loader2 className="w-6 h-6 animate-spin" /> : (
                    <>
                      Continuer <ArrowRight className="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" />
                    </>
                  )}
                </button>
              </div>
            </motion.form>
          ) : (
            <motion.form 
              key="otp"
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: 20 }}
              onSubmit={handleVerifyOtp}
            >
              <div className="text-center mb-8">
                <p className="text-gray-300 mb-2">Code envoyé au</p>
                <p className="text-tontine-gold font-bold tracking-wider">{phone}</p>
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
                    Vérifier <CheckCircle2 className="ml-2 w-5 h-5" />
                  </>
                )}
              </button>
              
              <button 
                type="button" 
                onClick={() => setStep('phone')}
                className="w-full mt-4 text-gray-400 hover:text-white text-sm transition-colors"
              >
                Changer de numéro
              </button>
            </motion.form>
          )}
        </AnimatePresence>
      </motion.div>
    </div>
  );
};

export default Login;
