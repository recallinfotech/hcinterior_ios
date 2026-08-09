import React from 'react';

interface MobileFrameProps {
  children: React.ReactNode;
  activeClientName?: string;
}

export const MobileFrame: React.FC<MobileFrameProps> = ({ children }) => {
  return (
    <div className="min-h-screen bg-slate-100 text-slate-900 flex flex-col w-full font-sans">
      <div className="flex-1 flex flex-col w-full bg-white min-h-screen relative">
        {children}
      </div>
    </div>
  );
};

