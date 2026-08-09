import React from 'react';

interface CompanyLogoProps {
  className?: string;
  variant?: 'dark' | 'light' | 'badge';
}

export const CompanyLogo: React.FC<CompanyLogoProps> = ({ className = 'h-9', variant = 'badge' }) => {
  if (variant === 'badge') {
    return (
      <div className={`inline-flex items-center justify-center bg-white rounded-xl p-1 shadow-sm border border-slate-200/90 shrink-0 ${className}`}>
        <svg
          viewBox="0 0 120 120"
          className="h-full w-auto aspect-square shrink-0"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          {/* Frame line */}
          <path
            d="M 10 10 H 110 V 55 M 10 10 V 110 H 22 M 35 110 H 110"
            stroke="#0f172a"
            strokeWidth="5"
            strokeLinecap="square"
          />

          {/* Lamp 1 (Left - Longer) */}
          <line x1="36" y1="10" x2="36" y2="52" stroke="#f59e0b" strokeWidth="4" />
          <path d="M26 52 Q36 42 46 52 L49 62 H23 Z" fill="#f59e0b" />
          <path d="M23 62 L12 88 H60 L49 62 Z" fill="url(#lightGlow1)" />

          {/* Lamp 2 (Right - Shorter) */}
          <line x1="68" y1="10" x2="68" y2="34" stroke="#f59e0b" strokeWidth="4" />
          <path d="M60 34 Q68 26 76 34 L79 43 H57 Z" fill="#f59e0b" />
          <path d="M57 43 L48 64 H88 L79 43 Z" fill="url(#lightGlow2)" />

          {/* Glowing gradients */}
          <defs>
            <linearGradient id="lightGlow1" x1="36" y1="62" x2="36" y2="88" gradientUnits="userSpaceOnUse">
              <stop stopColor="#fbbf24" stopOpacity="0.75" />
              <stop offset="1" stopColor="#fbbf24" stopOpacity="0.05" />
            </linearGradient>
            <linearGradient id="lightGlow2" x1="68" y1="43" x2="68" y2="64" gradientUnits="userSpaceOnUse">
              <stop stopColor="#fbbf24" stopOpacity="0.75" />
              <stop offset="1" stopColor="#fbbf24" stopOpacity="0.05" />
            </linearGradient>
          </defs>
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
