import React, { useState, useEffect } from 'react';
import { BOQItem, ClientProject, ApiBoqListResponse, ApiBoqItem } from '../../types';
import { fetchBoqList } from '../../services/clientApi';
import {
  User,
  Download,
  FileText,
  Eye,
  ExternalLink,
  Loader2,
  Calendar,
  Sparkles,
  X,
  AlertCircle,
  RotateCcw,
  ChevronDown,
} from 'lucide-react';

interface BOQSectionProps {
  boqList: BOQItem[];
  client?: ClientProject;
  showAllClients?: boolean;
  token?: string;
}

export const BOQSection: React.FC<BOQSectionProps> = ({
  boqList,
  client,
  showAllClients = false,
  token = '',
}) => {
  const [loading, setLoading] = useState<boolean>(false);
  const [apiBoqData, setApiBoqData] = useState<ApiBoqListResponse | null>(null);
  const [showOldBOQ, setShowOldBOQ] = useState(false);

  // PDF Embed State
  const [embedPdfUrl, setEmbedPdfUrl] = useState<string | null>(null);
  const [embedPdfTitle, setEmbedPdfTitle] = useState<string>('');

  const loadApiBoqs = async () => {
    if (!client) return;
    setLoading(true);
    const clientIdNum = client.clientIdNum || parseInt(client.id.replace(/\D/g, ''), 10) || 513;

    try {
      const res = await fetchBoqList(token, clientIdNum, 1, 20);
      if (res && res.status) {
        setApiBoqData(res);
      }
    } catch (e) {
      console.warn('Failed to load API BOQ in section:', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (client && !showAllClients) {
      loadApiBoqs();
    }
  }, [client, showAllClients]);

  // Fallback list from mock boqList if no API response
  const displayedBOQ = React.useMemo(() => {
    if (showAllClients || !client) {
      return boqList;
    }
    const matching = boqList.filter(
      (b) =>
        (client.mobile && b.phone === client.mobile) ||
        (client.id && b.quotationNo.includes(client.id))
    );
    if (matching.length > 0) return matching;

    return [
      {
        quotationNo: `HCIPPL/Quote/26-27/${client.id}`,
        date: client.date ? client.date.slice(0, 10) : '2026-07-28',
        phone: client.mobile || '9876543210',
        gTotal: 188125.8,
        siteHandling: 0.0,
        toBePaid: 188125.8,
        status: 'Approved' as const,
      },
    ];
  }, [boqList, client, showAllClients]);

  const handleOpenEmbedPdf = (url: string, title: string) => {
    setEmbedPdfUrl(url);
    setEmbedPdfTitle(title);
  };

  const handleDownloadPdf = (pdfUrl: string, title: string) => {
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.download = `${title.replace(/[/\\?%*:|"<>]/g, '_')}.pdf`;
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <div className="bg-white rounded-2xl p-4 border border-zinc-200 shadow-sm space-y-4">
      {/* Client Scope Banner */}
      {client && !showAllClients && (
        <div className="bg-zinc-950 text-white p-3 rounded-xl shadow-2xs flex items-center justify-between border border-zinc-800">
          <div className="flex items-center space-x-2.5">
            <div className="w-8 h-8 rounded-lg bg-amber-400/20 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-400/30">
              <User className="w-4 h-4" />
            </div>
            <div>
              <h4 className="text-xs font-bold text-zinc-100">{client.name}</h4>
              <p className="text-[10px] text-zinc-400">
                Client ID: <span className="text-amber-400 font-mono font-bold">{client.id}</span>
              </p>
            </div>
          </div>
          <div className="flex items-center space-x-2">
            <button
              onClick={loadApiBoqs}
              disabled={loading}
              className="p-1 text-zinc-400 hover:text-amber-400 cursor-pointer"
              title="Refresh BOQ"
            >
              <RotateCcw className={`w-3.5 h-3.5 ${loading ? 'animate-spin text-amber-400' : ''}`} />
            </button>
            <span className="px-2.5 py-0.5 bg-amber-400/20 border border-amber-400/40 text-amber-300 text-[10px] font-bold rounded-full uppercase tracking-wider">
              Client Data
            </span>
          </div>
        </div>
      )}

      {/* Top Header */}
      <div className="flex items-center justify-between gap-3 border-b-2 border-amber-400 pb-2.5">
        <h2 className="text-base font-bold text-zinc-900 truncate">
          BOQ (Bill of Quantities) {client && !showAllClients ? `— ${client.id}` : ''}
        </h2>
        <span className="text-xs font-semibold px-2.5 py-1 bg-zinc-100 text-zinc-700 rounded-lg border border-zinc-200">
          {apiBoqData?.latest_boq
            ? 1 + (apiBoqData.old_boq?.length || 0)
            : displayedBOQ.length}{' '}
          Quotations
        </span>
      </div>

      {loading && (
        <div className="py-8 text-center space-y-2">
          <Loader2 className="w-6 h-6 text-amber-500 animate-spin mx-auto" />
          <p className="text-xs font-semibold text-zinc-500">Loading BOQ records from CRM...</p>
        </div>
      )}

      {/* API BOQ View */}
      {!loading && apiBoqData && apiBoqData.latest_boq ? (
        <div className="space-y-4">
          {/* Latest BOQ Card */}
          <div className="bg-amber-500/10 rounded-xl p-3.5 border-2 border-amber-400/60 space-y-3">
            <div className="flex items-center justify-between">
              <span className="bg-amber-400 text-zinc-950 font-black px-2 py-0.5 rounded text-[10px] uppercase tracking-wider flex items-center space-x-1">
                <Sparkles className="w-3 h-3" />
                <span>Latest BOQ</span>
              </span>
              <span className="text-[11px] font-bold text-amber-900 uppercase">
                Status: {apiBoqData.latest_boq.status}
              </span>
            </div>

            <div className="bg-white rounded-lg p-3 border border-amber-200 space-y-2">
              <div className="flex items-start justify-between">
                <div>
                  <span className="text-[10px] font-bold text-zinc-400 block uppercase">Ref No</span>
                  <span className="font-bold text-zinc-900 text-xs font-mono">{apiBoqData.latest_boq.ref_no}</span>
                </div>
                <div className="text-right">
                  <span className="text-[10px] font-bold text-zinc-400 block uppercase">BOQ Date</span>
                  <span className="font-medium text-zinc-800 text-xs flex items-center justify-end space-x-1">
                    <Calendar className="w-3 h-3 text-amber-500 inline" />
                    <span>{apiBoqData.latest_boq.proposal_date}</span>
                  </span>
                </div>
              </div>

              {/* PDF Actions */}
              <div className="pt-2 border-t border-zinc-100 flex flex-wrap items-center justify-end gap-2">
                {(apiBoqData.latest_boq.pdf_without_price || apiBoqData.latest_boq.pdf_with_price) && (
                  <>
                    <button
                      onClick={() =>
                        handleOpenEmbedPdf(
                          (apiBoqData.latest_boq?.pdf_without_price || apiBoqData.latest_boq?.pdf_with_price)!,
                          apiBoqData.latest_boq?.ref_no!
                        )
                      }
                      className="px-3 py-1.5 bg-amber-400 hover:bg-amber-500 text-zinc-950 font-extrabold rounded-lg text-xs flex items-center space-x-1.5 shadow-xs transition-colors cursor-pointer"
                    >
                      <Eye className="w-3.5 h-3.5" />
                      <span>Embed &amp; View PDF</span>
                    </button>

                    <a
                      href={apiBoqData.latest_boq.pdf_without_price || apiBoqData.latest_boq.pdf_with_price || '#'}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="px-3 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow-xs transition-colors cursor-pointer"
                    >
                      <ExternalLink className="w-3.5 h-3.5 text-amber-400" />
                      <span>Open Link</span>
                    </a>
                  </>
                )}
              </div>
            </div>
          </div>

          {/* Previous BOQs */}
          {apiBoqData.old_boq && apiBoqData.old_boq.length > 0 && (
            <div className="space-y-2">
              <button
                onClick={() => setShowOldBOQ(!showOldBOQ)}
                className="w-full font-bold text-zinc-900 text-xs flex items-center justify-between border-b border-zinc-200 pb-2 cursor-pointer group hover:text-amber-600 transition-colors"
              >
                <div className="flex items-center space-x-2">
                  <span>Previous BOQs ({apiBoqData.old_boq.length})</span>
                  <span className="text-[10px] text-zinc-400 font-normal">Revisions History</span>
                </div>
                <div className="flex items-center space-x-1 text-zinc-500 group-hover:text-amber-600">
                  <span className="text-[10px] font-semibold">{showOldBOQ ? 'Collapse' : 'Expand'}</span>
                  <ChevronDown className={`w-4 h-4 transition-transform duration-200 ${showOldBOQ ? 'rotate-180' : ''}`} />
                </div>
              </button>

              {showOldBOQ && (
                <div className="space-y-2 animate-in fade-in duration-200">
                  {apiBoqData.old_boq.map((boq, idx) => (
                    <div key={boq.id || idx} className="bg-zinc-50 rounded-xl p-3 border border-zinc-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                      <div>
                        <span className="font-mono text-xs font-bold text-zinc-900 block">{boq.ref_no}</span>
                        <span className="text-[10px] text-zinc-500 font-medium">BOQ Date: {boq.proposal_date} | Status: {boq.status}</span>
                      </div>

                      <div className="flex items-center space-x-2">
                        {(boq.pdf_without_price || boq.pdf_with_price) && (
                          <>
                            <button
                              onClick={() => handleOpenEmbedPdf((boq.pdf_without_price || boq.pdf_with_price)!, boq.ref_no)}
                              className="px-2.5 py-1 bg-amber-400/20 text-amber-900 hover:bg-amber-400/30 font-bold rounded-md text-[11px] flex items-center space-x-1 cursor-pointer border border-amber-400/40"
                            >
                              <Eye className="w-3 h-3 text-amber-600" />
                              <span>Embed PDF</span>
                            </button>
                            <a
                              href={boq.pdf_without_price || boq.pdf_with_price || '#'}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="p-1 bg-white hover:bg-zinc-100 text-zinc-700 rounded border border-zinc-300"
                              title="Open Link"
                            >
                              <ExternalLink className="w-3.5 h-3.5" />
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
      ) : (
        /* Fallback Mock BOQ View */
        !loading && (
          <div className="space-y-3">
            {displayedBOQ.map((item, idx) => (
              <div
                key={idx}
                className="bg-zinc-50 rounded-xl p-3.5 border border-zinc-200 space-y-2.5 text-xs text-zinc-800"
              >
                <div className="flex items-start justify-between">
                  <div className="truncate pr-2">
                    <span className="text-[10px] text-zinc-400 font-semibold uppercase block">Quotation #</span>
                    <span className="font-bold text-zinc-900 truncate block text-xs">{item.quotationNo}</span>
                  </div>
                  <span className="px-2 py-0.5 rounded bg-emerald-600 text-white text-[10px] font-bold uppercase shadow-2xs">
                    {item.status}
                  </span>
                </div>

                <div className="grid grid-cols-2 gap-2 text-[11px] pt-1 border-t border-zinc-200/80">
                  <div>
                    <span className="text-zinc-400 block text-[10px]">BOQ Date</span>
                    <span className="font-medium text-zinc-800">{item.date}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block text-[10px]">Client SR ID</span>
                    <span className="font-mono font-bold text-amber-600">{client?.id || 'HC101784'}</span>
                  </div>
                </div>

                <div className="pt-2 border-t border-zinc-200/80 flex items-center justify-end space-x-2">
                  <button
                    onClick={() =>
                      handleOpenEmbedPdf(
                        `https://crm.hcinterior.in/mobileapi/client/operation_pdf/13801/${client?.clientIdNum || 513}`,
                        item.quotationNo
                      )
                    }
                    className="px-3 py-1.5 bg-amber-400 hover:bg-amber-500 text-zinc-950 font-extrabold rounded-lg text-xs flex items-center space-x-1.5 shadow-xs transition-colors cursor-pointer"
                  >
                    <Eye className="w-3.5 h-3.5" />
                    <span>Embed &amp; View PDF</span>
                  </button>
                  <button
                    onClick={() => handleDownloadPdf('https://www.w3.org/W3C/DesignIssues/diagrams/overview.pdf', item.quotationNo)}
                    className="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow-sm transition-colors cursor-pointer"
                  >
                    <Download className="w-3.5 h-3.5" />
                    <span>Download</span>
                  </button>
                </div>
              </div>
            ))}
          </div>
        )
      )}

      {/* Embedded PDF Viewer Modal */}
      {embedPdfUrl && (
        <div className="fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center p-2 sm:p-6 z-[60] animate-in fade-in duration-200">
          <div className="bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-5xl h-[85vh] flex flex-col overflow-hidden border border-zinc-700">
            {/* Viewer Header */}
            <div className="bg-zinc-950 px-4 py-3 border-b border-zinc-800 flex items-center justify-between text-white">
              <div className="flex items-center space-x-2.5 truncate">
                <FileText className="w-5 h-5 text-amber-400 shrink-0" />
                <div className="truncate">
                  <h4 className="text-xs font-bold truncate">{embedPdfTitle}</h4>
                  <p className="text-[10px] text-zinc-400 truncate">Embedded BOQ Document Viewer</p>
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

            {/* Viewer Body */}
            <div className="flex-1 bg-zinc-800 relative flex flex-col">
              <iframe
                src={embedPdfUrl}
                className="w-full flex-1 border-0 bg-slate-100"
                title="BOQ PDF Preview"
              />
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
