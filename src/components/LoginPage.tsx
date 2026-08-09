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
    <div className="min-h-screen bg-zinc-950 flex items-center justify-center p-4 text-zinc-100">
      <div className="w-full max-w-sm bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-2xl space-y-6 relative overflow-hidden backdrop-blur-md">
        
        {/* Decorative background glow in deep yellow & dark grey */}
        <div className="absolute -top-12 -right-12 w-36 h-36 bg-amber-400/10 rounded-full blur-2xl pointer-events-none" />
        <div className="absolute -bottom-12 -left-12 w-36 h-36 bg-yellow-500/10 rounded-full blur-2xl pointer-events-none" />

        {/* Brand Header */}
        <div className="text-center space-y-3">
          <div className="flex justify-center">
            <CompanyLogo className="h-12" variant="badge" />
          </div>
          <div>
            <h1 className="text-lg font-bold text-white tracking-tight">HC Interior Operation</h1>
          </div>
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
        <form onSubmit={handleSubmit} className="space-y-4">
          {/* Username Field */}
          <div className="space-y-1.5">
            <label className="block text-xs font-semibold text-zinc-300">Username</label>
            <div className="relative flex items-center">
              <User className="w-4 h-4 absolute left-3 text-zinc-400 pointer-events-none" />
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="Enter username"
                className="w-full bg-zinc-950 border border-zinc-800 rounded-xl pl-9 pr-3 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-all font-medium"
                required
              />
            </div>
          </div>

          {/* Password Field */}
          <div className="space-y-1.5">
            <div className="flex items-center justify-between">
              <label className="block text-xs font-semibold text-zinc-300">Password</label>
              <button
                type="button"
                onClick={() => alert('Password reset request logged for mobile API.')}
                className="text-[11px] font-bold text-amber-400 hover:text-amber-300 cursor-pointer transition-colors"
              >
                Forgot?
              </button>
            </div>
            <div className="relative flex items-center">
              <Lock className="w-4 h-4 absolute left-3 text-zinc-400 pointer-events-none" />
              <input
                type={showPassword ? 'text' : 'password'}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Enter password"
                className="w-full bg-zinc-950 border border-zinc-800 rounded-xl pl-9 pr-10 py-2.5 text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-all font-medium"
                required
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-3 text-zinc-400 hover:text-zinc-200 cursor-pointer p-0.5"
              >
                {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
              </button>
            </div>
          </div>

          {/* Remember Me */}
          <div className="flex items-center justify-between text-xs pt-1">
            <label className="flex items-center space-x-2 cursor-pointer text-zinc-400 hover:text-zinc-200">
              <input
                type="checkbox"
                checked={rememberMe}
                onChange={(e) => setRememberMe(e.target.checked)}
                className="w-3.5 h-3.5 rounded bg-zinc-950 border-zinc-800 text-amber-400 focus:ring-amber-400 focus:ring-offset-zinc-950"
              />
              <span>Remember session</span>
            </label>
          </div>

          {/* Submit Button */}
          <button
            type="submit"
            disabled={isLoading}
            className="w-full py-2.5 bg-amber-400 hover:bg-amber-500 text-zinc-950 font-extrabold text-xs rounded-xl shadow-lg shadow-amber-500/20 flex items-center justify-center space-x-2 transition-all cursor-pointer disabled:opacity-50"
          >
            {isLoading ? (
              <span className="inline-block w-4 h-4 border-2 border-zinc-950/30 border-t-zinc-950 rounded-full animate-spin" />
            ) : (
              <>
                <span>Login</span>
                <ArrowRight className="w-4 h-4" />
              </>
            )}
          </button>
        </form>

        {/* Footer Security Badge */}
        <div className="flex items-center justify-center space-x-1.5 text-[10px] text-zinc-500 pt-2">
          <ShieldCheck className="w-3.5 h-3.5 text-amber-400" />
          <span>HC Interior Operation</span>
        </div>
      </div>
    </div>
  );
};
