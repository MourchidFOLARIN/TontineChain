import React from 'react';
import {
  AbsoluteFill,
  interpolate,
  useCurrentFrame,
  useVideoConfig,
  Img,
  staticFile,
} from 'remotion';

export const MyComposition: React.FC = () => {
  const frame = useCurrentFrame();
  const {fps, width, height} = useVideoConfig();

  // Animations progress
  const opacity = interpolate(frame, [0, 20], [0, 1], {extrapolateRight: 'clamp'});
  const logoScale = interpolate(frame, [0, 30], [0, 1], {extrapolateRight: 'clamp'});
  
  // Scene timings (30fps)
  // 0-60: Intro
  // 60-150: The Problem
  // 150-240: The Solution (Blockchain)
  // 240-360: Trust (NPI/IA)
  // 360-450: Outro/CTA

  return (
    <AbsoluteFill style={{backgroundColor: '#050505', color: '#e5e7eb', fontFamily: 'Inter, sans-serif'}}>
      {/* Intro Scene (0-60) */}
      {frame < 70 && (
        <AbsoluteFill style={{justifyContent: 'center', alignItems: 'center', opacity: interpolate(frame, [50, 70], [1, 0])}}>
          <Img 
            src={staticFile('logo.jpeg')} 
            style={{
              width: 300, 
              height: 300, 
              borderRadius: '50%', 
              transform: `scale(${logoScale})`,
              boxShadow: '0 0 50px rgba(16, 185, 129, 0.4)'
            }} 
          />
          <h1 style={{marginTop: 40, fontSize: 80, fontFamily: 'Playfair Display', color: '#d4af37'}}>TontineChain</h1>
        </AbsoluteFill>
      )}

      {/* Scene 1: The Problem (60-150) */}
      {frame >= 60 && frame < 160 && (
        <AbsoluteFill style={{justifyContent: 'center', alignItems: 'center', padding: 100, opacity: interpolate(frame, [60, 80, 140, 160], [0, 1, 1, 0])}}>
          <h2 style={{fontSize: 60, textAlign: 'center', lineHeight: 1.2}}>
            Les tontines classiques sont <span style={{color: '#ef4444'}}>risquées</span> et <span style={{color: '#ef4444'}}>opaques</span>.
          </h2>
          <p style={{fontSize: 30, color: '#9ca3af', marginTop: 40}}>Fraude, vols, manque de confiance...</p>
        </AbsoluteFill>
      )}

      {/* Scene 2: The Solution (150-240) */}
      {frame >= 150 && frame < 250 && (
        <AbsoluteFill style={{justifyContent: 'center', alignItems: 'center', padding: 100, opacity: interpolate(frame, [150, 170, 230, 250], [0, 1, 1, 0])}}>
          <h2 style={{fontSize: 60, textAlign: 'center', lineHeight: 1.2}}>
            TontineChain sécurise tout sur la <span style={{color: '#10b981'}}>Blockchain Polygon</span>.
          </h2>
          <p style={{fontSize: 30, color: '#9ca3af', marginTop: 40}}>Immuabilité. Transparence. Sécurité.</p>
        </AbsoluteFill>
      )}

      {/* Scene 3: Trust (240-360) */}
      {frame >= 240 && frame < 370 && (
        <AbsoluteFill style={{justifyContent: 'center', alignItems: 'center', padding: 100, opacity: interpolate(frame, [240, 260, 350, 370], [0, 1, 1, 0])}}>
          <h2 style={{fontSize: 50, textAlign: 'center', lineHeight: 1.2}}>
            Identité <span style={{color: '#d4af37'}}>NPI Vérifiée</span> + <span style={{color: '#d4af37'}}>IA de Risque</span>.
          </h2>
          <p style={{fontSize: 30, color: '#9ca3af', marginTop: 40}}>Votre épargne entre de bonnes mains.</p>
        </AbsoluteFill>
      )}

      {/* Outro / CTA (360-450) */}
      {frame >= 360 && (
        <AbsoluteFill style={{justifyContent: 'center', alignItems: 'center', opacity: interpolate(frame, [360, 380], [0, 1])}}>
          <Img 
            src={staticFile('logo.jpeg')} 
            style={{
              width: 200, 
              height: 200, 
              borderRadius: '50%', 
              boxShadow: '0 0 30px rgba(16, 185, 129, 0.3)'
            }} 
          />
          <h2 style={{marginTop: 40, fontSize: 60, textAlign: 'center'}}>Prêt à rejoindre l'élite ?</h2>
          <p style={{fontSize: 40, color: '#d4af37', marginTop: 30, fontWeight: 'bold'}}>tontine-benin-backend.onrender.com</p>
        </AbsoluteFill>
      )}
    </AbsoluteFill>
  );
};
