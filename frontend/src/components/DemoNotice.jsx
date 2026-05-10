import React, { useState, useEffect } from 'react';
import { Info, X, Zap } from 'lucide-react';

const DemoNotice = ({ notice, onClose }) => {
  if (!notice) return null;

  return (
    <div className="fixed bottom-24 md:bottom-8 left-1/2 -translate-x-1/2 z-[100] w-[90%] max-w-md animate-in fade-in slide-in-from-bottom-4 duration-300">
      <div className="glass-panel border-tontine-orange/30 p-5 shadow-2xl relative overflow-hidden group">
        {/* Animated accent */}
        <div className="absolute top-0 left-0 w-1 h-full bg-tontine-orange"></div>
        <div className="absolute top-0 left-0 w-full h-full bg-tontine-orange/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        
        <div className="flex gap-4">
          <div className="flex-shrink-0 w-10 h-10 rounded-full bg-tontine-orange/20 flex items-center justify-center text-tontine-orange">
            <Zap size={20} />
          </div>
          
          <div className="flex-1">
            <h4 className="text-tontine-orange font-bold text-sm uppercase tracking-widest flex items-center gap-2 mb-1">
              Mode Démonstration
            </h4>
            <p className="text-gray-200 text-sm leading-relaxed">
              {notice.message}
            </p>
            
            {notice.otp_code && (
              <div className="mt-3 bg-black/40 rounded-lg p-2 border border-white/5 flex items-center justify-between">
                <span className="text-[10px] text-gray-500 uppercase">Code de simulation :</span>
                <span className="text-tontine-orange font-mono font-bold tracking-tighter text-lg">
                  {notice.otp_code}
                </span>
              </div>
            )}
          </div>
          
          <button 
            onClick={onClose}
            className="text-gray-500 hover:text-white transition-colors"
          >
            <X size={18} />
          </button>
        </div>
      </div>
    </div>
  );
};

export default DemoNotice;
