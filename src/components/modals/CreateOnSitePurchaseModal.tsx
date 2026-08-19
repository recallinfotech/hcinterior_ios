import React, { useState, useEffect } from 'react';
import { ClientProject } from '../../types';
import { createOnSitePurchase } from '../../services/clientApi';
import { ShoppingBag, X, FileText, Upload, Send, AlertCircle } from 'lucide-react';

interface CreateOnSitePurchaseModalProps {
  isOpen: boolean;
  onClose: () => void;
  client: ClientProject | null;
  authToken?: string;
  showToast?: (msg: string) => void;
  onSuccess?: () => void;
}

export const CreateOnSitePurchaseModal: React.FC<CreateOnSitePurchaseModalProps> = ({
  isOpen,
  onClose,
  client,
  authToken,
  showToast,
  onSuccess,
}) => {
  const [fileNameInput, setFileNameInput] = useState('');
  const [brandInput, setBrandInput] = useState('');
  const [messageInput, setMessageInput] = useState('');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);

  useEffect(() => {
    if (isOpen) {
      setFileNameInput('');
      setBrandInput('');
      setMessageInput('');
      setSelectedFile(null);
      setFormError(null);
    }
  }, [isOpen, client]);

  if (!isOpen || !client) return null;

  const numericClientId = client.clientIdNum
    ? String(client.clientIdNum)
    : client.id
    ? client.id.replace(/\D/g, '')
    : '';

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormError(null);

    if (!numericClientId) {
      setFormError('Could not determine client ID for this purchase request.');
      return;
    }

    if (!fileNameInput.trim()) {
      setFormError('Please enter a Request Title / Description.');
      return;
    }

    setIsSubmitting(true);
    try {
      const res = await createOnSitePurchase(
        authToken || '',
        numericClientId,
        fileNameInput.trim(),
        selectedFile,
        brandInput.trim(),
        messageInput.trim()
      );

      if (res.success) {
        showToast?.(res.message || 'On site purchase request created successfully!');
        if (onSuccess) {
          onSuccess();
        }
        onClose();
      } else {
        setFormError(res.message || 'Failed to create purchase request.');
      }
    } catch (err: any) {
      console.error('Error creating on site purchase from modal:', err);
      setFormError(err.message || 'Error submitting request. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-4 animate-in fade-in zoom-in-95 duration-200">
        {/* Header */}
        <div className="flex items-center justify-between border-b border-slate-200 pb-3">
          <div className="flex items-center space-x-2.5">
            <div className="w-9 h-9 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center font-bold shrink-0">
              <ShoppingBag className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-bold text-sm text-slate-900">New On-Site Purchase Request</h3>
              <p className="text-[11px] text-slate-500 font-medium">
                Client: <strong className="text-orange-600">{client.name}</strong> ({client.id})
              </p>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 cursor-pointer transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Error Alert */}
        {formError && (
          <div className="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center space-x-2">
            <AlertCircle className="w-4 h-4 text-rose-600 shrink-0" />
            <span>{formError}</span>
          </div>
        )}

        {/* Form Body */}
        <form onSubmit={handleSubmit} className="space-y-3.5 text-xs">
          {/* Request Title / Description */}
          <div>
            <label className="block text-[11px] font-bold text-slate-700 mb-1">
              Request Title / Description <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              value={fileNameInput}
              onChange={(e) => setFileNameInput(e.target.value)}
              className="w-full bg-white border border-slate-300 font-medium text-slate-900 text-xs rounded-xl p-2.5 focus:outline-none focus:border-orange-500 shadow-2xs"
              required
            />
          </div>

          {/* Brand Name */}
          <div>
            <label className="block text-[11px] font-bold text-slate-700 mb-1">
              Brand Name
            </label>
            <input
              type="text"
              value={brandInput}
              onChange={(e) => setBrandInput(e.target.value)}
              className="w-full bg-white border border-slate-300 font-medium text-slate-900 text-xs rounded-xl p-2.5 focus:outline-none focus:border-orange-500 shadow-2xs"
            />
          </div>

          {/* Site Message / Note */}
          <div>
            <label className="block text-[11px] font-bold text-slate-700 mb-1">
              Site Message / Note
            </label>
            <textarea
              rows={2}
              value={messageInput}
              onChange={(e) => setMessageInput(e.target.value)}
              className="w-full bg-white border border-slate-300 font-medium text-slate-900 text-xs rounded-xl p-2.5 focus:outline-none focus:border-orange-500 shadow-2xs resize-none"
            />
          </div>

          {/* File Attachment */}
          <div>
            <label className="block text-[11px] font-bold text-slate-700 mb-1">
              Attach Document / Photo
            </label>
            <div className="border-2 border-dashed border-slate-300 hover:border-orange-400 rounded-xl p-4 bg-slate-50 hover:bg-orange-50/40 transition-colors text-center cursor-pointer relative">
              <input
                type="file"
                accept="image/*,application/pdf"
                onChange={(e) => setSelectedFile(e.target.files?.[0] || null)}
                className="absolute inset-0 opacity-0 w-full h-full cursor-pointer"
              />
              {selectedFile ? (
                <div className="flex items-center justify-center space-x-2 text-slate-800 font-bold">
                  <FileText className="w-4.5 h-4.5 text-orange-600 shrink-0" />
                  <span className="truncate max-w-[200px] text-xs">{selectedFile.name}</span>
                  <span className="text-[10px] text-slate-500 font-normal font-mono">
                    ({(selectedFile.size / 1024).toFixed(1)} KB)
                  </span>
                </div>
              ) : (
                <div className="space-y-1">
                  <Upload className="w-5 h-5 text-slate-400 mx-auto" />
                  <p className="text-xs text-slate-600 font-semibold">Click or drag file to attach photo/PDF</p>
                  <p className="text-[10px] text-slate-400 font-mono">Supports PNG, JPG, PDF up to 10MB</p>
                </div>
              )}
            </div>
          </div>

          {/* Actions */}
          <div className="pt-3 flex items-center justify-end space-x-2 border-t border-slate-200">
            <button
              type="button"
              onClick={onClose}
              className="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-md flex items-center space-x-1.5 transition-colors cursor-pointer disabled:opacity-50"
            >
              {isSubmitting ? (
                <>
                  <span className="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                  <span>Submitting...</span>
                </>
              ) : (
                <>
                  <Send className="w-3.5 h-3.5" />
                  <span>Submit Request</span>
                </>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
