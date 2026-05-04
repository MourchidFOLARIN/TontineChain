import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { Bell, ArrowLeft, CheckCircle, AlertCircle, Info, Clock, Loader2 } from 'lucide-react';
import { getNotificationsList } from '../services/api';
import AudioButton from './AudioButton';

const Notifications = ({ notifications, onBack }) => {
  const getIcon = (type) => {
    switch (type) {
      case 'success': return <CheckCircle className="w-5 h-5 text-green-400" />;
      case 'warning': return <AlertCircle className="w-5 h-5 text-yellow-400" />;
      case 'info': return <Info className="w-5 h-5 text-blue-400" />;
      default: return <Bell className="w-5 h-5 text-gray-400" />;
    }
  };

  return (
    <div className="min-h-screen bg-tontine-darker text-white font-inter p-6 relative overflow-hidden">
      <div className="max-w-md mx-auto relative z-10">
        <button onClick={onBack} className="mb-6 flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
          <ArrowLeft className="w-5 h-5" /> Retour
        </button>

        <h1 className="text-3xl font-playfair font-bold mb-8 flex items-center gap-3">
          <Bell className="text-tontine-orange" /> Notifications
          <AudioButton label="Xó xá mì (Fon)" />
        </h1>

        <div className="space-y-4">
          {notifications.map((n, i) => (
            <motion.div 
              key={n.id}
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ delay: i * 0.1 }}
              className="glass-panel p-4 rounded-2xl flex gap-4 border border-white/5 hover:border-white/10 transition-all"
            >
              <div className="mt-1">{getIcon(n.type)}</div>
              <div className="flex-1">
                <div className="flex justify-between items-start mb-1">
                  <h4 className="font-bold text-sm">{n.title}</h4>
                  <span className="text-[10px] text-gray-500 flex items-center gap-1">
                    <Clock className="w-3 h-3" /> {n.time}
                  </span>
                </div>
                <p className="text-xs text-gray-400 leading-relaxed">{n.message}</p>
              </div>
            </motion.div>
          ))}
          </div>
      </div>
    </div>
  );
};

export default Notifications;
