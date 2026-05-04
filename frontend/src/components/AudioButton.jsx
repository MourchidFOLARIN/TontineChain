import React, { useState } from 'react';
import { Volume2, VolumeX } from 'lucide-react';

const AudioButton = ({ audioUrl, label }) => {
  const [playing, setPlaying] = useState(false);

  const toggleAudio = () => {
    if (!window.speechSynthesis) {
      alert("Votre navigateur ne supporte pas la synthèse vocale.");
      return;
    }

    if (playing) {
      window.speechSynthesis.cancel();
      setPlaying(false);
      return;
    }

    const textToSpeak = label || "Instruction audio disponible";
    const utterance = new SpeechSynthesisUtterance(textToSpeak);
    utterance.lang = 'fr-FR';
    utterance.rate = 0.9; // Légèrement plus lent pour une meilleure compréhension
    utterance.pitch = 1.0;
    
    utterance.onend = () => setPlaying(false);
    utterance.onerror = () => setPlaying(false);
    
    setPlaying(true);
    window.speechSynthesis.speak(utterance);
  };

  return (
    <button 
      onClick={toggleAudio}
      className="inline-flex items-center gap-1.5 px-2 py-1 bg-white/5 hover:bg-white/10 rounded-lg text-[10px] text-gray-400 hover:text-tontine-orange transition-all"
    >
      {playing ? <VolumeX className="w-3 h-3 animate-pulse" /> : <Volume2 className="w-3 h-3" />}
      <span>{label || 'Écouter'}</span>
    </button>
  );
};

export default AudioButton;
