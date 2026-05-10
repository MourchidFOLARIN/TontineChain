import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Bell, ArrowLeft, CheckCircle, AlertCircle, Info, Clock, Trash2, ShieldCheck, Zap } from 'lucide-react';
import AudioButton from './AudioButton';

const Notifications = ({ notifications, onBack }) => {
  const getIcon = (type) => {
    switch (type) {
      case 'success': return <div className="w-10 h-10 rounded-xl bg-green-400/10 flex items-center justify-center text-green-400"><CheckCircle size={20} /></div>;
      case 'warning': return <div className="w-10 h-10 rounded-xl bg-tontine-orange/10 flex items-center justify-center text-tontine-orange"><AlertCircle size={20} /></div>;
      case 'info': return <div className="w-10 h-10 rounded-xl bg-blue-400/10 flex items-center justify-center text-blue-400"><Info size={20} /></div>;
      default: return <div className="w-10 h-10 rounded-xl bg-gray-400/10 flex items-center justify-center text-gray-400"><Bell size={20} /></div>;
    }
  };

  return (
    <div className="flex flex-col h-full bg-tontine-darker min-h-screen">
      {/* Header */}
      <div className="p-4 md:p-6 glass-panel flex items-center justify-between border-b border-white/5 sticky top-0 z-30">
        <div className="flex items-center gap-4">
          <button onClick={onBack} className="p-2 hover:bg-white/5 rounded-full transition-colors text-gray-400 hover:text-white">
            <ArrowLeft size={22} />
          </button>
          <div>
            <h2 className="text-xl md:text-2xl font-bold font-playfair flex items-center gap-3">
              Notifications
              <span className="bg-tontine-orange text-tontine-darker text-[10px] px-2 py-0.5 rounded-full font-black">
                {notifications.length}
              </span>
            </h2>
            <p className="text-[10px] md:text-xs text-gray-500 uppercase font-black tracking-widest flex items-center gap-2">
              <Zap size={10} className="text-tontine-gold" /> Activité de votre réseau
            </p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <AudioButton label="Consultez vos dernières alertes de sécurité." />
          <button className="p-2 text-gray-500 hover:text-red-400 transition-colors">
            <Trash2 size={20} />
          </button>
        </div>
      </div>

      {/* Content */}
      <div className="flex-1 p-4 md:p-8 max-w-2xl mx-auto w-full">
        {notifications.length === 0 ? (
          <div className="flex flex-col items-center justify-center pt-24 text-center">
            <div className="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mb-6">
              <Bell size={32} className="text-gray-700" />
            </div>
            <h3 className="text-lg font-bold text-gray-400">Aucune notification</h3>
            <p className="text-xs text-gray-600 mt-2">Nous vous préviendrons dès qu'il y aura du nouveau.</p>
          </div>
        ) : (
          <div className="space-y-4">
            <AnimatePresence>
              {notifications.map((n, i) => (
                <motion.div 
                  key={n.id}
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, scale: 0.95 }}
                  transition={{ delay: i * 0.05 }}
                  className="glass-panel p-5 rounded-3xl flex gap-4 border border-white/5 hover:border-tontine-orange/20 transition-all group relative overflow-hidden"
                >
                  <div className="flex-shrink-0">{getIcon(n.type)}</div>
                  <div className="flex-1 min-w-0">
                    <div className="flex justify-between items-start mb-1">
                      <h4 className="font-bold text-sm md:text-base text-white truncate pr-4">{n.title}</h4>
                      <span className="text-[9px] md:text-[10px] text-gray-500 font-bold whitespace-nowrap flex items-center gap-1">
                        <Clock size={10} /> {n.time}
                      </span>
                    </div>
                    <p className="text-xs text-gray-400 leading-relaxed">{n.message}</p>
                    
                    {n.type === 'success' && (
                      <div className="mt-3 flex items-center gap-1.5 text-[9px] text-green-400 font-bold uppercase tracking-wider">
                        <ShieldCheck size={12} /> Transaction Blockchain Confirmée
                      </div>
                    )}
                  </div>
                  
                  {/* Subtle hover effect */}
                  <div className="absolute right-0 top-0 bottom-0 w-1 bg-tontine-orange opacity-0 group-hover:opacity-100 transition-opacity" />
                </motion.div>
              ))}
            </AnimatePresence>
          </div>
        )}

        {/* Info Tip */}
        <div className="mt-12 p-6 rounded-3xl bg-blue-500/5 border border-blue-500/10 text-center">
          <p className="text-[10px] text-blue-400/60 uppercase font-black tracking-[0.2em] mb-2">Conseil de sécurité</p>
          <p className="text-xs text-gray-400 leading-relaxed">
            Activez les notifications **WhatsApp** dans vos paramètres pour recevoir vos codes de versement en temps réel.
          </p>
        </div>
      </div>
      
      {/* Space for bottom nav */}
      <div className="h-20 md:hidden" />
    </div>
  );
};

export default Notifications;
