import React, { useState } from 'react';
import { User, Lock, Eye, EyeOff, ShieldCheck, ArrowRight, CheckCircle } from 'lucide-react';
import { CompanyLogo } from './CompanyLogo';
import { Capacitor } from '@capacitor/core';
import { loginUser, UserData } from '../services/authApi';
import { getDynamicFcmToken } from '../services/fcmService';

interface LoginPageProps {
  onLoginSuccess: (userData: UserData, token: string) => void;
}

export const LoginPage: React.FC<LoginPageProps> = ({ onLoginSuccess }) => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(true);
  const [error, setError] = useState('');
  const [successMsg, setSuccessMsg] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccessMsg('');

    if (!username.trim()) {
      setError('Please enter your username');
      return;
    }
    if (!password.trim()) {
      setError('Please enter your password');
      return;
    }

    setIsLoading(true);

    try {
      // Dynamically acquire unique FCM Token for current physical device
      const dynamicFcmToken = await getDynamicFcmToken();
      const platform = Capacitor.getPlatform();
      const deviceType = platform === 'ios' ? 'ios' : 'android';
      const res = await loginUser(username.trim(), password.trim(), dynamicFcmToken, deviceType);

      if (res && res.status && res.data) {
        const extractedToken =
          res.token ||
          (res as any).access_token ||
          (res.data as any)?.token ||
          (res.data as any)?.auth_token ||
          (res.data as any)?.access_token ||
          (res.data as any)?.user_id ||
          'authenticated_session';

        localStorage.setItem('auth_token', String(extractedToken));
        localStorage.setItem('user_data', JSON.stringify(res.data));

        setSuccessMsg(res.message || 'Login successful!');
        setTimeout(() => {
          setIsLoading(false);
          onLoginSuccess(res.data!, String(extractedToken));
        }, 500);
      } else {
        setIsLoading(false);
        setError(res.message || 'Login failed. Please check your credentials.');
      }
    } catch (err: any) {
      setIsLoading(false);
      setError(err?.message || 'Login error occurred. Please check network connection.');
    }
  };

  return (
    <div className="min-h-screen bg-[#080c14] flex flex-col justify-center px-6 py-8 text-zinc-100 relative overflow-hidden">
      {/* Ambient background glow */}
      <div className="absolute top-1/6 left-1/2 -translate-x-1/2 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none" />
      <div className="absolute bottom-10 right-10 w-60 h-60 bg-orange-600/10 rounded-full blur-3xl pointer-events-none" />

      <div className="w-full max-w-sm mx-auto relative z-10 space-y-6">
        {/* Glowing Brand Logo Card */}
        <div className="flex justify-center">
          <CompanyLogo className="h-28 w-28" variant="glowing" />
        </div>

        {/* Headings */}
        <div className="text-center space-y-1">
          <p className="text-amber-500 font-extrabold tracking-widest text-sm uppercase">HC INTERIOR</p>
          <h1 className="text-2xl font-black text-white tracking-tight">Welcome Back</h1>
          <p className="text-xs text-zinc-400 font-medium">Login to continue to your account</p>
        </div>

        {/* Error Alert */}
        {error && (
          <div className="p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-xs font-semibold flex items-center space-x-2 animate-in fade-in">
            <span>⚠️ {error}</span>
          </div>
        )}

        {/* Success Alert */}
        {successMsg && (
          <div className="p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-xs font-semibold flex items-center space-x-2 animate-in fade-in">
            <CheckCircle className="w-4 h-4 shrink-0 text-emerald-400" />
            <span>{successMsg}</span>
          </div>
        )}

        {/* Form */}
        <form onSubmit={handleSubmit} className="space-y-4 pt-2">
          {/* Username Field */}
          <div className="space-y-1.5">
            <label className="block text-[11px] font-black tracking-wider text-amber-500 uppercase">
              USERNAME
            </label>
            <div className="relative flex items-center">
              <User className="w-4 h-4 absolute left-4 text-amber-500/80 pointer-events-none" />
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="Enter username"
                className="w-full bg-[#101422] border border-amber-500/60 rounded-2xl pl-11 pr-4 py-3.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20 transition-all font-medium"
                required
              />
            </div>
          </div>

          {/* Password Field */}
          <div className="space-y-1.5">
            <div className="flex items-center justify-between">
              <label className="block text-[11px] font-black tracking-wider text-amber-500 uppercase">
                PASSWORD
              </label>
              <button
                type="button"
                onClick={() => alert('Please contact administrator at support@hcinterior.in to reset your password.')}
                className="text-xs font-bold text-amber-500 hover:text-amber-400 cursor-pointer transition-colors"
              >
                Forgot?
              </button>
            </div>
            <div className="relative flex items-center">
              <Lock className="w-4 h-4 absolute left-4 text-amber-500/80 pointer-events-none" />
              <input
                type={showPassword ? 'text' : 'password'}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Enter password"
                className="w-full bg-[#101422] border border-amber-500/60 rounded-2xl pl-11 pr-11 py-3.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-500/20 transition-all font-medium"
                required
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-4 text-amber-500/80 hover:text-amber-400 cursor-pointer p-0.5"
              >
                {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
              </button>
            </div>
          </div>

          {/* Remember Me */}
          <div className="flex items-center space-x-2 pt-1">
            <input
              type="checkbox"
              id="rememberMe"
              checked={rememberMe}
              onChange={(e) => setRememberMe(e.target.checked)}
              className="w-4 h-4 rounded bg-[#101422] border-amber-500/60 text-amber-500 focus:ring-amber-500 accent-amber-500 cursor-pointer"
            />
            <label htmlFor="rememberMe" className="text-xs font-medium text-zinc-400 cursor-pointer select-none">
              Remember Me
            </label>
          </div>

          {/* Submit Button */}
          <button
            type="submit"
            disabled={isLoading}
            className="w-full py-3.5 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 hover:from-amber-400 hover:to-orange-400 text-zinc-950 font-black text-sm rounded-2xl shadow-[0_8px_25px_rgba(245,158,11,0.35)] flex items-center justify-center space-x-2 cursor-pointer transition-all active:scale-98 disabled:opacity-50 mt-4"
          >
            {isLoading ? (
              <span className="inline-block w-4 h-4 border-2 border-zinc-950/30 border-t-zinc-950 rounded-full animate-spin" />
            ) : (
              <>
                <span>Login</span>
                <ArrowRight className="w-4 h-4 stroke-[2.5]" />
              </>
            )}
          </button>
        </form>

        {/* Footer Security Badge */}
        <div className="flex items-center justify-center space-x-1.5 text-[10px] text-zinc-500 pt-4">
          <ShieldCheck className="w-3.5 h-3.5 text-amber-500" />
          <span>HC Interior CRM & Attendance System</span>
        </div>
      </div>
    </div>
  );
};
