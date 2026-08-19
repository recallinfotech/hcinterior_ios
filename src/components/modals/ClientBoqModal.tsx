import React, { useState, useEffect } from 'react';
import { ClientProject, ApiBoqListResponse, ApiBoqItem } from '../../types';
import { fetchBoqList } from '../../services/clientApi';
import {
  X,
  FileText,
  Download,
  ExternalLink,
  Eye,
  Loader2,
  Calendar,
  User,
  AlertCircle,
  RotateCcw,
  Sparkles,
  ChevronDown,
} from 'lucide-react';

interface ClientBoqModalProps {
  isOpen: boolean;
  onClose: () => void;
  client: ClientProject | null;
  token: string;
}

export const ClientBoqModal: React.FC<ClientBoqModalProps> = ({
  isOpen,
  onClose,
  client,
  token,
}) => {
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [boqData, setBoqData] = useState<ApiBoqListResponse | null>(null);
  const [isPreviousBoqExpanded, setIsPreviousBoqExpanded] = useState<boolean>(false);

  // Embedded PDF State
  const [embedPdfUrl, setEmbedPdfUrl] = useState<string | null>(null);
  const [embedPdfTitle, setEmbedPdfTitle] = useState<string>('');

  const loadBoqData = async () => {
    if (!client) return;
    setLoading(true);
    setError(null);

    const clientIdNum = client.clientIdNum || parseInt(client.id.replace(/\D/g, ''), 10) || 513;

    try {
      const response = await fetchBoqList(token, clientIdNum, 1, 20);
      if (response && response.status) {
        setBoqData(response);
      } else {
        // Mock fallback if API returns false or no network
        setError(response?.message || 'Unable to fetch BOQ list from server.');
        setBoqData({
          status: true,
          message: 'Fallback BOQ list',
          client: {
            client_id: clientIdNum,
            client_sr_id: client.id,
            client_name: client.name,
            email: client.email || 'client@hcinterior.in',
            mobile: client.mobile || '9876543210',
            lead_id: 43983,
          },
          permissions: {
            can_view_price: false,
            can_view_without_price_pdf: true,
          },
          latest_boq: {
            id: 13801,
            client_id: clientIdNum,
            lead_id: 43983,
            ref_no: `HCIPPL/Quote/26-27/N/${client.id}`,
            proposal_date: client.date ? client.date.slice(0, 10) : '2026-07-08',
            phone: client.mobile || '3213211111111',
            status: 'draft',
            pdf_with_price: null,
            pdf_without_price: `https://crm.hcinterior.in/mobileapi/client/operation_pdf/13801/${clientIdNum}`,
            grand_total: null,
            site_handling: null,
            total_to_be_paid: null,
          },
          old_boq: [
            {
              id: 13570,
              client_id: clientIdNum,
              lead_id: 43983,
              ref_no: `HCIPPL/Quote/26-27/N/${client.id}/3`,
              proposal_date: '2026-07-03',
              phone: client.mobile || '3213211111111',
              status: 'draft',
              pdf_with_price: null,
              pdf_without_price: `https://crm.hcinterior.in/mobileapi/client/operation_pdf/13570/${clientIdNum}`,
              grand_total: null,
              site_handling: null,
              total_to_be_paid: null,
            },
            {
              id: 13568,
              client_id: clientIdNum,
              lead_id: 43983,
              ref_no: `HCIPPL/Quote/26-27/N/${client.id}/2`,
              proposal_date: '2026-07-02',
              phone: client.mobile || '3213211111111',
              status: 'draft',
              pdf_with_price: null,
              pdf_without_price: `https://crm.hcinterior.in/mobileapi/client/operation_pdf/13568/${clientIdNum}`,
              grand_total: null,
              site_handling: null,
              total_to_be_paid: null,
            },
          ],
        });
      }
    } catch (err: any) {
      console.error('Error fetching BOQ list:', err);
      setError('Failed to connect to BOQ endpoint.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (isOpen && client) {
      loadBoqData();
      setEmbedPdfUrl(null);
    }
  }, [isOpen, client]);

  if (!isOpen || !client) return null;

  const handleOpenEmbedPdf = (boqItem: ApiBoqItem) => {
    const pdfUrl = boqItem.pdf_without_price || boqItem.pdf_with_price;
    if (!pdfUrl) {
      alert('PDF link is not available for this BOQ item.');
      return;
    }
    setEmbedPdfUrl(pdfUrl);
    setEmbedPdfTitle(boqItem.ref_no || `BOQ #${boqItem.id}`);
  };

  const handleDownloadPdf = (pdfUrl: string, refNo: string) => {
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.download = `${refNo.replace(/[/\\?%*:|"<>]/g, '_')}.pdf`;
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <div className="fixed inset-0 bg-zinc-950/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 z-50">
      <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden border border-zinc-200 animate-in fade-in zoom-in duration-200">
        {/* Header */}
        <div className="bg-zinc-900 text-white px-4 py-3.5 flex items-center justify-between border-b border-zinc-800">
          <div className="flex items-center space-x-2.5">
            <div className="w-8 h-8 rounded-lg bg-amber-400/20 text-amber-400 flex items-center justify-center font-bold text-sm border border-amber-400/30">
              <FileText className="w-4.5 h-4.5" />
            </div>
            <div>
              <div className="flex items-center space-x-2">
                <h3 className="text-sm font-bold text-white leading-tight">BOQ List</h3>
                <span className="bg-amber-400 text-zinc-950 text-[10px] font-extrabold px-1.5 py-0.5 rounded font-mono">
                  {client.id}
                </span>
              </div>
              <p className="text-[11px] text-zinc-400 flex items-center space-x-1 mt-0.5">
                <User className="w-3 h-3 text-amber-400 inline" />
                <span>{client.name}</span>
              </p>
            </div>
          </div>

          <div className="flex items-center space-x-2">
            <button
              onClick={loadBoqData}
              disabled={loading}
              className="p-1.5 text-zinc-400 hover:text-amber-400 hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
              title="Refresh BOQ List"
            >
              <RotateCcw className={`w-4 h-4 ${loading ? 'animate-spin text-amber-400' : ''}`} />
            </button>
            <button
              onClick={onClose}
              className="p-1.5 text-zinc-400 hover:text-white hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Modal Body */}
        <div className="p-4 overflow-y-auto space-y-4 flex-1 text-xs text-zinc-800">
          {/* Client Details Summary Banner */}
          <div className="bg-zinc-50 rounded-xl p-3 border border-zinc-200 grid grid-cols-2 gap-2">
            <div>
              <span className="text-[10px] text-zinc-400 font-bold block uppercase tracking-wider">Client Name</span>
              <span className="font-bold text-zinc-900 text-xs truncate block">{client.name}</span>
            </div>
            <div>
              <span className="text-[10px] text-zinc-400 font-bold block uppercase tracking-wider">Client SR ID</span>
              <span className="font-mono font-bold text-amber-600 text-xs block">
                {boqData?.client?.client_sr_id || client.id}
              </span>
            </div>
          </div>

          {/* Loading Indicator */}
          {loading && (
            <div className="py-12 text-center space-y-3">
              <Loader2 className="w-8 h-8 text-amber-500 animate-spin mx-auto" />
              <p className="text-xs font-bold text-zinc-600">Fetching BOQ records from CRM...</p>
            </div>
          )}

          {/* Error Banner */}
          {error && !loading && (
            <div className="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-900 flex items-start space-x-2">
              <AlertCircle className="w-4 h-4 text-amber-600 mt-0.5 shrink-0" />
              <div>
                <p className="font-bold">Notice</p>
                <p className="text-[11px] text-amber-800">{error}</p>
              </div>
            </div>
          )}

          {/* Main BOQ Content */}
          {!loading && boqData && (
            <div className="space-y-4">
              {/* Latest BOQ Card */}
              {boqData.latest_boq && (
                <div className="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-white rounded-xl p-3.5 border-2 border-amber-400/50 shadow-xs space-y-3 relative overflow-hidden">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                      <span className="bg-amber-400 text-zinc-950 font-extrabold px-2 py-0.5 rounded text-[10px] uppercase tracking-wider flex items-center space-x-1 shadow-2xs">
                        <Sparkles className="w-3 h-3" />
                        <span>Latest BOQ</span>
                      </span>
                      <span className="text-[11px] font-bold text-amber-900 uppercase">
                        Status: {boqData.latest_boq.status}
                      </span>
                    </div>
                    <span className="text-[11px] font-mono font-semibold text-zinc-500">
                      ID: #{boqData.latest_boq.id}
                    </span>
                  </div>

                  <div className="bg-white rounded-lg p-3 border border-amber-200 shadow-2xs space-y-2">
                    <div className="flex items-start justify-between">
                      <div>
                        <span className="text-[10px] font-bold text-zinc-400 block uppercase">Ref No</span>
                        <span className="font-bold text-zinc-900 text-xs font-mono select-all">
                          {boqData.latest_boq.ref_no}
                        </span>
                      </div>
                      <div className="text-right">
                        <span className="text-[10px] font-bold text-zinc-400 block uppercase">BOQ Date</span>
                        <span className="font-medium text-zinc-800 text-xs flex items-center justify-end space-x-1">
                          <Calendar className="w-3 h-3 text-amber-500 inline" />
                          <span>{boqData.latest_boq.proposal_date}</span>
                        </span>
                      </div>
                    </div>

                    {/* Action Buttons */}
                    <div className="pt-2 border-t border-zinc-100 flex flex-wrap items-center justify-end gap-2">
                      {(boqData.latest_boq.pdf_without_price || boqData.latest_boq.pdf_with_price) && (
                        <>
                          <button
                            onClick={() => handleOpenEmbedPdf(boqData.latest_boq!)}
                            className="px-3 py-1.5 bg-amber-400 hover:bg-amber-500 text-zinc-950 font-extrabold rounded-lg text-xs flex items-center space-x-1.5 shadow-xs transition-colors cursor-pointer"
                          >
                            <Eye className="w-3.5 h-3.5" />
                            <span>Embed &amp; View PDF</span>
                          </button>

                          <a
                            href={boqData.latest_boq.pdf_without_price || boqData.latest_boq.pdf_with_price || '#'}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow-xs transition-colors cursor-pointer"
                          >
                            <ExternalLink className="w-3.5 h-3.5 text-amber-400" />
                            <span>Open PDF Link</span>
                          </a>

                          <button
                            onClick={() =>
                              handleDownloadPdf(
                                (boqData.latest_boq?.pdf_without_price || boqData.latest_boq?.pdf_with_price)!,
                                boqData.latest_boq?.ref_no!
                              )
                            }
                            className="p-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 rounded-lg text-xs border border-zinc-300 cursor-pointer"
                            title="Download PDF"
                          >
                            <Download className="w-3.5 h-3.5" />
                          </button>
                        </>
                      )}
                    </div>
                  </div>
                </div>
              )}

              {/* Old BOQ List */}
              {boqData.old_boq && boqData.old_boq.length > 0 && (
                <div className="space-y-2.5">
                  <button
                    onClick={() => setIsPreviousBoqExpanded(!isPreviousBoqExpanded)}
                    className="w-full font-bold text-zinc-900 text-xs flex items-center justify-between border-b border-zinc-200 pb-2 cursor-pointer group hover:text-amber-600 transition-colors"
                  >
                    <div className="flex items-center space-x-2">
                      <span>Previous BOQ Quotations ({boqData.old_boq.length})</span>
                      <span className="text-[10px] text-zinc-400 font-normal">Revisions History</span>
                    </div>
                    <div className="flex items-center space-x-1 text-zinc-500 group-hover:text-amber-600">
                      <span className="text-[10px] font-semibold">{isPreviousBoqExpanded ? 'Collapse' : 'Expand'}</span>
                      <ChevronDown className={`w-4 h-4 transition-transform duration-200 ${isPreviousBoqExpanded ? 'rotate-180' : ''}`} />
                    </div>
                  </button>

                  {isPreviousBoqExpanded && (
                    <div className="space-y-2 max-h-[300px] overflow-y-auto pr-1 animate-in fade-in duration-200">
                      {boqData.old_boq.map((boq, idx) => (
                        <div
                          key={boq.id || idx}
                          className="bg-zinc-50 hover:bg-zinc-100/80 rounded-xl p-3 border border-zinc-200 transition-colors space-y-2"
                        >
                          <div className="flex items-start justify-between">
                            <div>
                              <span className="text-[10px] font-mono text-zinc-400 block">
                                Ref: {boq.ref_no}
                              </span>
                              <span className="text-[10px] text-zinc-500 font-medium">
                                BOQ Date: {boq.proposal_date}
                              </span>
                            </div>
                            <span className="px-2 py-0.5 rounded bg-zinc-200 text-zinc-800 text-[10px] font-bold uppercase">
                              {boq.status}
                            </span>
                          </div>

                          <div className="flex items-center justify-end space-x-2 pt-1 border-t border-zinc-200/60">
                            {(boq.pdf_without_price || boq.pdf_with_price) && (
                              <>
                                <button
                                  onClick={() => handleOpenEmbedPdf(boq)}
                                  className="px-2.5 py-1 bg-amber-400/20 hover:bg-amber-400/30 text-amber-900 font-bold rounded-md text-[11px] flex items-center space-x-1 cursor-pointer border border-amber-400/40"
                                >
                                  <Eye className="w-3 h-3 text-amber-600" />
                                  <span>Embed PDF</span>
                                </button>

                                <a
                                  href={boq.pdf_without_price || boq.pdf_with_price || '#'}
                                  target="_blank"
                                  rel="noopener noreferrer"
                                  className="px-2.5 py-1 bg-white hover:bg-zinc-100 text-zinc-800 font-bold rounded-md text-[11px] flex items-center space-x-1 cursor-pointer border border-zinc-300"
                                >
                                  <ExternalLink className="w-3 h-3 text-zinc-600" />
                                  <span>Open Link</span>
                                </a>
                              </>
                            )}
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              )}
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="bg-zinc-50 border-t border-zinc-200 px-4 py-3 flex items-center justify-between text-xs text-zinc-500">
          <span>Client ID: #{client.clientIdNum || client.id}</span>
          <button
            onClick={onClose}
            className="px-4 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white font-bold rounded-xl transition-colors cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>

      {/* Embedded PDF Viewer Modal */}
      {embedPdfUrl && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center p-2 sm:p-6 z-[60] animate-in fade-in duration-200">
          <div className="bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden border border-zinc-700">
            {/* Viewer Header */}
            <div className="bg-zinc-950 px-4 py-3 border-b border-zinc-800 flex items-center justify-between text-white">
              <div className="flex items-center space-x-2.5 truncate">
                <FileText className="w-5 h-5 text-amber-400 shrink-0" />
                <div className="truncate">
                  <h4 className="text-xs font-bold truncate">{embedPdfTitle}</h4>
                  <p className="text-[10px] text-zinc-400 truncate">Embedded BOQ Document Preview</p>
                </div>
              </div>

              <div className="flex items-center space-x-2 shrink-0">
                <a
                  href={embedPdfUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="px-3 py-1.5 bg-amber-400 hover:bg-amber-500 text-zinc-950 font-extrabold rounded-lg text-xs flex items-center space-x-1 shadow-xs transition-colors"
                >
                  <ExternalLink className="w-3.5 h-3.5" />
                  <span>Open in New Tab</span>
                </a>
                <button
                  onClick={() => setEmbedPdfUrl(null)}
                  className="p-1.5 text-zinc-400 hover:text-white hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>
            </div>

            {/* Viewer Body with Embedded iframe */}
            <div className="flex-1 bg-zinc-800 relative flex flex-col">
              <iframe
                src={embedPdfUrl}
                className="w-full flex-1 border-0 bg-slate-100"
                title="BOQ PDF Preview"
              />

              <div className="bg-zinc-900 border-t border-zinc-800 p-2 text-center text-[11px] text-zinc-400 flex items-center justify-between px-4">
                <span>PDF Document Source: {embedPdfUrl}</span>
                <a
                  href={embedPdfUrl}
                  download
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-amber-400 font-bold hover:underline flex items-center space-x-1"
                >
                  <Download className="w-3 h-3" />
                  <span>Direct Download</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
