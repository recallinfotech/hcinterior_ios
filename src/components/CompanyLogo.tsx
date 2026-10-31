import React from 'react';

interface CompanyLogoProps {
  className?: string;
  variant?: 'dark' | 'light' | 'badge' | 'glowing';
}

export const CompanyLogo: React.FC<CompanyLogoProps> = ({ className = 'h-9', variant = 'badge' }) => {
  if (variant === 'glowing') {
    return (
      <div className={`inline-flex items-center justify-center bg-black border-2 border-amber-500/90 rounded-2xl p-2.5 shadow-[0_0_30px_rgba(245,158,11,0.4)] shrink-0 ${className}`}>
        <svg
          viewBox="0 0 140 140"
          className="h-full w-auto aspect-square shrink-0"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          {/* Main Logo Frame */}
          <path
            d="M 18 18 H 122 V 122 H 18 Z"
            stroke="#ffffff"
            strokeWidth="3.5"
            strokeLinecap="square"
          />

          {/* Lamp 1 (Left) */}
          <line x1="42" y1="18" x2="42" y2="48" stroke="#f59e0b" strokeWidth="3" />
          <path d="M34 48 Q42 40 50 48 L53 57 H31 Z" fill="#f59e0b" />
          <path d="M31 57 L22 80 H62 L53 57 Z" fill="url(#glowingLamp1)" />

          {/* Lamp 2 (Right) */}
          <line x1="68" y1="18" x2="68" y2="34" stroke="#f59e0b" strokeWidth="3" />
          <path d="M62 34 Q68 28 74 34 L77 42 H59 Z" fill="#f59e0b" />
          <path d="M59 42 L52 62 H84 L77 42 Z" fill="url(#glowingLamp2)" />

          {/* Text Labels inside frame */}
          <text x="70" y="86" textAnchor="middle" fill="#ffffff" fontSize="9.5" fontWeight="900" letterSpacing="2">HIGH</text>
          <text x="70" y="99" textAnchor="middle" fill="#ffffff" fontSize="8.5" fontWeight="900" letterSpacing="1.5">CREATION</text>
          <text x="70" y="111" textAnchor="middle" fill="#ffffff" fontSize="7.5" fontWeight="800" letterSpacing="2">INTERIOR</text>

          <defs>
            <linearGradient id="glowingLamp1" x1="42" y1="57" x2="42" y2="80" gradientUnits="userSpaceOnUse">
              <stop stopColor="#fbbf24" stopOpacity="0.8" />
              <stop offset="1" stopColor="#fbbf24" stopOpacity="0.05" />
            </linearGradient>
            <linearGradient id="glowingLamp2" x1="68" y1="42" x2="68" y2="62" gradientUnits="userSpaceOnUse">
              <stop stopColor="#fbbf24" stopOpacity="0.8" />
              <stop offset="1" stopColor="#fbbf24" stopOpacity="0.05" />
            </linearGradient>
          </defs>
        </svg>
      </div>
    );
  }

  if (variant === 'badge') {
    return (
      <div className={`inline-flex items-center justify-center bg-black border border-amber-500/80 rounded-xl p-1.5 shadow-sm shrink-0 ${className}`}>
        <svg
          viewBox="0 0 140 140"
          className="h-full w-auto aspect-square shrink-0"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M 18 18 H 122 V 122 H 18 Z"
            stroke="#ffffff"
            strokeWidth="3.5"
            strokeLinecap="square"
          />
          <line x1="42" y1="18" x2="42" y2="48" stroke="#f59e0b" strokeWidth="3" />
          <path d="M34 48 Q42 40 50 48 L53 57 H31 Z" fill="#f59e0b" />
          <line x1="68" y1="18" x2="68" y2="34" stroke="#f59e0b" strokeWidth="3" />
          <path d="M62 34 Q68 28 74 34 L77 42 H59 Z" fill="#f59e0b" />
          <text x="70" y="86" textAnchor="middle" fill="#ffffff" fontSize="9.5" fontWeight="900" letterSpacing="2">HIGH</text>
          <text x="70" y="99" textAnchor="middle" fill="#ffffff" fontSize="8.5" fontWeight="900" letterSpacing="1.5">CREATION</text>
          <text x="70" y="111" textAnchor="middle" fill="#ffffff" fontSize="7.5" fontWeight="800" letterSpacing="2">INTERIOR</text>
        </svg>
      </div>
    );
  }

  // Pure SVG standalone icon without container box
  const mainColor = variant === 'dark' ? '#0f172a' : '#ffffff';

  return (
    <svg
      viewBox="0 0 120 120"
      className={`${className} w-auto aspect-square shrink-0`}
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M 10 10 H 110 V 55 M 10 10 V 110 H 22 M 35 110 H 110"
        stroke={mainColor}
        strokeWidth="5"
        strokeLinecap="square"
      />

      <line x1="36" y1="10" x2="36" y2="52" stroke="#f59e0b" strokeWidth="4" />
      <path d="M26 52 Q36 42 46 52 L49 62 H23 Z" fill="#f59e0b" />
      <path d="M23 62 L12 88 H60 L49 62 Z" fill="url(#lightGlow1_standalone)" />

      <line x1="68" y1="10" x2="68" y2="34" stroke="#f59e0b" strokeWidth="4" />
      <path d="M60 34 Q68 26 76 34 L79 43 H57 Z" fill="#f59e0b" />
      <path d="M57 43 L48 64 H88 L79 43 Z" fill="url(#lightGlow2_standalone)" />

      <defs>
        <linearGradient id="lightGlow1_standalone" x1="36" y1="62" x2="36" y2="88" gradientUnits="userSpaceOnUse">
          <stop stopColor="#f59e0b" stopOpacity="0.8" />
          <stop offset="1" stopColor="#f59e0b" stopOpacity="0.05" />
        </linearGradient>
        <linearGradient id="lightGlow2_standalone" x1="68" y1="43" x2="68" y2="64" gradientUnits="userSpaceOnUse">
          <stop stopColor="#f59e0b" stopOpacity="0.8" />
          <stop offset="1" stopColor="#f59e0b" stopOpacity="0.05" />
        </linearGradient>
      </defs>
    </svg>
  );
};
