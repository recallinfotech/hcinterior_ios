import React, { useState, useEffect, useMemo } from 'react';
import { BOMRecord, ClientProject } from '../../types';
import { CornerDownRight, Image as ImageIcon, RefreshCw, Filter, Search } from 'lucide-react';

interface BOMSectionProps {
  bomRecords: BOMRecord[];
  clients?: ClientProject[];
  client?: ClientProject;
  showAllClients?: boolean;
  authToken?: string;
  showToast?: (msg: string) => void;
  onRefresh?: () => void;
  onAddBOM: () => void;
  onAddChildBOM: (parentId: string) => void;
}

export const BOMSection: React.FC<BOMSectionProps> = ({
  bomRecords,
  clients = [],
  client,
  showAllClients = true,
  authToken,
  showToast,
  onRefresh,
  onAddBOM,
  onAddChildBOM,
}) => {
  const [dataMode, setDataMode] = useState<'all' | 'clientWise'>(
    showAllClients ? 'all' : 'clientWise'
  );

  const [selectedClientFilterId, setSelectedClientFilterId] = useState<string>(
    client?.id || ''
  );

  useEffect(() => {
    if (client) {
      setSelectedClientFilterId(client.id);
    }
    setDataMode(showAllClients ? 'all' : 'clientWise');
  }, [client, showAllClients]);

  const [isRefreshing, setIsRefreshing] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [remarkInput, setRemarkInput] = useState('');
  const [remarks, setRemarks] = useState<string[]>([
    'No Remarks',
  ]);

  const handleRefreshClick = async () => {
    if (onRefresh) {
      setIsRefreshing(true);
      await onRefresh();
      setTimeout(() => setIsRefreshing(false), 500);
      if (showToast) showToast('Refreshed BOM List from CRM API');
    }
  };

  const handleAddRemark = () => {
    if (remarkInput.trim()) {
      setRemarks([...remarks, remarkInput.trim()]);
      setRemarkInput('');
    }
  };

  // Build unique client list for dropdown selector
  const uniqueClients = useMemo(() => {
    const map = new Map<string, ClientProject>();
    if (client) {
      map.set(client.id, client);
    }
    clients.forEach((c) => {
      if (!map.has(c.id)) {
        map.set(c.id, c);
      }
    });

    // Also extract clients directly present in bomRecords if missing
    bomRecords.forEach((item) => {
      if (item.clientId && !map.has(item.clientId)) {
        map.set(item.clientId, {
          id: item.clientId,
          clientIdNum: item.client_id,
          name: item.clientName || item.clientId,
          date: item.date || '',
          salesManager: 'CRM',
          assignedTeam: { designer: 'N/A', projectManager: 'N/A' },
          ktRequest: { status: 'Accepted', date: '' },
          validationDate: '',
          phase: 'Phase 1',
          overallProgress: 100,
          freezeBOQAmount: 0,
          amountReceived: 0,
        });
      }
    });

    return Array.from(map.values());
  }, [client, clients, bomRecords]);

  // Filtered BOM records
  const filteredBOMRecords = useMemo(() => {
    let list = [...bomRecords];

    if (dataMode === 'clientWise') {
      const activeClientObj = uniqueClients.find((c) => c.id === selectedClientFilterId) || client;
      list = list.filter((i) => {
        if (!activeClientObj) return false;
        const itemSrId = (i.clientId || i.client_sr_id || '').toLowerCase();
        const itemNumId = i.client_id ? String(i.client_id) : itemSrId.replace(/\D/g, '');
        const itemName = (i.clientName || '').toLowerCase();

        const targetSrId = (activeClientObj.id || '').toLowerCase();
        const targetNumId = (activeClientObj as any).clientIdNum ? String((activeClientObj as any).clientIdNum) : targetSrId.replace(/\D/g, '');
        const targetName = (activeClientObj.name || '').toLowerCase();

        if (targetNumId && itemNumId && itemNumId === targetNumId) return true;
        if (targetSrId && itemSrId && (itemSrId === targetSrId || itemSrId.includes(targetSrId) || targetSrId.includes(itemSrId))) return true;
        if (targetName && itemName && (itemName.includes(targetName) || targetName.includes(itemName))) return true;

        return false;
      });
    }

    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      list = list.filter(
        (b) =>
          b.fileName.toLowerCase().includes(q) ||
          b.category.toLowerCase().includes(q) ||
          b.type.toLowerCase().includes(q) ||
          b.status.toLowerCase().includes(q)
      );
    }

    return list;
  }, [bomRecords, dataMode, selectedClientFilterId, client, uniqueClients, searchQuery]);

  const activeClientObj = uniqueClients.find((c) => c.id === selectedClientFilterId) || client;

  return (
    <div className="space-y-4">
      {/* Top Filter Controls Bar */}
      <div className="bg-slate-900 text-white p-3.5 rounded-2xl shadow-sm space-y-3 border border-slate-800">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
          <div className="flex items-center space-x-2">
            <Filter className="w-4 h-4 text-orange-400" />
            <h3 className="text-xs font-bold text-slate-100">BOM & Checklist Data Mode</h3>
          </div>

          <div className="flex items-center space-x-2">
            <button
              onClick={() => setDataMode('all')}
              className={`px-3 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer ${
                dataMode === 'all'
                  ? 'bg-orange-500 text-white shadow-2xs'
                  : 'bg-slate-800 text-slate-400 hover:text-slate-200'
              }`}
            >
              All Clients Mode
            </button>
            <button
              onClick={() => setDataMode('clientWise')}
              className={`px-3 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer ${
                dataMode === 'clientWise'
                  ? 'bg-orange-500 text-white shadow-2xs'
                  : 'bg-slate-800 text-slate-400 hover:text-slate-200'
              }`}
            >
              Client-Wise Mode
            </button>

            {onRefresh && (
              <button
                onClick={handleRefreshClick}
                disabled={isRefreshing}
                className="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition-colors cursor-pointer border border-slate-700"
                title="Refresh from CRM API"
              >
                <RefreshCw className={`w-3.5 h-3.5 ${isRefreshing ? 'animate-spin text-orange-400' : ''}`} />
              </button>
            )}
          </div>
        </div>

        {/* Client Selector dropdown when in clientWise mode */}
        {dataMode === 'clientWise' && (
          <div className="pt-2 border-t border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs">
            <div className="flex items-center space-x-2 w-full sm:w-auto">
              <span className="text-slate-400 text-[11px] font-medium whitespace-nowrap">Selected Client:</span>
              <select
                value={selectedClientFilterId}
                onChange={(e) => setSelectedClientFilterId(e.target.value)}
                className="bg-slate-800 text-white border border-slate-700 rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:border-orange-500 w-full sm:w-64"
              >
                {uniqueClients.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name} ({c.id})
                  </option>
                ))}
              </select>
            </div>

            {activeClientObj && (
              <span className="text-[11px] text-orange-400 font-medium">
                Filtering for: <strong>{activeClientObj.name}</strong> ({activeClientObj.id})
              </span>
            )}
          </div>
        )}
      </div>

      {/* Header & Main BOM List */}
      <div className="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-3">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between border-b-2 border-orange-500 pb-2 gap-2">
          <div className="flex items-center space-x-2">
            <h2 className="text-base font-bold text-slate-900">
              Upload BOM / Check List {dataMode === 'clientWise' && activeClientObj ? `— ${activeClientObj.id}` : ''}
            </h2>
            <span className="text-xs bg-orange-100 text-orange-800 font-bold px-2 py-0.5 rounded-full">
              {filteredBOMRecords.length} Items
            </span>
          </div>

          <div className="flex items-center space-x-2">
            <div className="relative">
              <Search className="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search BOM..."
                className="pl-8 pr-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 focus:outline-none focus:border-orange-500 w-36 sm:w-48"
              />
            </div>
          </div>
        </div>

        {/* Hierarchy List View */}
        <div className="space-y-3">
          {filteredBOMRecords.length === 0 ? (
            <div className="bg-slate-50 rounded-xl p-8 text-center border border-slate-200 space-y-2">
              <h3 className="text-xs font-bold text-slate-800">No BOM / Check List Records Found</h3>
              <p className="text-[11px] text-slate-500 max-w-sm mx-auto">
                {dataMode === 'clientWise' && activeClientObj
                  ? `No Bill of Materials or checklist records found for ${activeClientObj.name} (${activeClientObj.id}).`
                  : 'No BOM records found.'}
              </p>
            </div>
          ) : (
            filteredBOMRecords.map((bom) => (
              <div key={bom.id} className="space-y-2">
                {/* Parent BOM Card */}
                <div className="bg-slate-50 rounded-xl p-3 border border-slate-200 text-xs space-y-2.5">
                  <div className="flex items-start justify-between gap-2">
                    <div className="flex items-center space-x-3">
                      <div className="w-12 h-12 rounded-lg border border-slate-300 bg-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                        {bom.imageUrl ? (
                          <img src={bom.imageUrl} alt={bom.fileName} className="w-full h-full object-cover" />
                        ) : (
                          <ImageIcon className="w-6 h-6 text-slate-400" />
                        )}
                      </div>
                      <div>
                        <h4 className="font-bold text-slate-900 text-xs">{bom.fileName}</h4>
                        <p className="text-[10px] text-slate-500 mt-0.5">
                          Category: <span className="font-medium text-slate-700">{bom.category}</span> • Type: <span className="font-medium text-slate-700">{bom.type}</span>
                        </p>
                        {bom.clientName && (
                          <p className="text-[10px] text-slate-500">
                            Client: <span className="font-medium text-slate-700">{bom.clientName}</span> ({bom.clientId || bom.client_sr_id})
                          </p>
                        )}
                        {(bom.vendor || bom.design_type) && (
                          <p className="text-[10px] text-slate-500">
                            {bom.vendor ? `Vendor: ${bom.vendor}` : ''} {bom.design_type ? `• Design: ${bom.design_type}` : ''}
                          </p>
                        )}
                      </div>
                    </div>

                    <span className="px-2.5 py-1 bg-emerald-500 text-white font-bold text-[10px] rounded-md uppercase tracking-wide shrink-0">
                      {bom.status}
                    </span>
                  </div>

                  {/* Attached Files List */}
                  {((bom.upload_file && bom.upload_file.length > 0) || (bom.fileUrl && bom.fileUrl !== '#')) && (
                    <div className="pt-2 border-t border-slate-200/80">
                      <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Attached Files:</span>
                      <div className="flex flex-wrap gap-1.5">
                        {bom.upload_file && bom.upload_file.length > 0 ? (
                          bom.upload_file.map((file, idx) => (
                            <a
                              key={idx}
                              href={file}
                              target="_blank"
                              rel="noreferrer"
                              className="inline-flex items-center space-x-1 px-2.5 py-1 bg-white hover:bg-slate-100 text-sky-600 border border-slate-300 rounded text-[11px] font-medium transition-colors"
                            >
                              <ImageIcon className="w-3 h-3 text-sky-500" />
                              <span className="max-w-[200px] truncate">{file.split('/').pop() || `File ${idx + 1}`}</span>
                            </a>
                          ))
                        ) : bom.fileUrl && bom.fileUrl !== '#' ? (
                          <a
                            href={bom.fileUrl}
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex items-center space-x-1 px-2.5 py-1 bg-white hover:bg-slate-100 text-sky-600 border border-slate-300 rounded text-[11px] font-medium transition-colors"
                          >
                            <ImageIcon className="w-3 h-3 text-sky-500" />
                            <span>View Attached File</span>
                          </a>
                        ) : null}
                      </div>
                    </div>
                  )}

                  {bom.remark && (
                    <div className="text-[11px] text-slate-600 bg-white p-2 rounded border border-slate-200 font-medium">
                      <span className="font-bold text-slate-700">Remark:</span> {bom.remark}
                    </div>
                  )}

                  <div className="flex items-center justify-between pt-1.5 border-t border-slate-200 text-[10px] text-slate-400">
                    <span>Date: {bom.date || 'N/A'}</span>
                    <span>BOM ID: #{bom.id}</span>
                  </div>
                </div>

                {/* Children BOM Indented Sub-cards */}
                {bom.children &&
                  bom.children.map((child) => (
                    <div
                      key={child.id}
                      className="ml-5 bg-sky-50/60 rounded-xl p-3 border border-sky-200 text-xs space-y-2 relative"
                    >
                      <CornerDownRight className="w-4 h-4 text-sky-500 absolute -left-4 top-3" />

                      <div className="flex items-start justify-between gap-2">
                        <div className="flex items-center space-x-2.5">
                          <div className="w-9 h-9 rounded border border-sky-200 bg-white overflow-hidden shrink-0 flex items-center justify-center">
                            {child.imageUrl ? (
                              <img src={child.imageUrl} alt={child.fileName} className="w-full h-full object-cover" />
                            ) : (
                              <span className="font-bold text-[10px] text-sky-600">BOM</span>
                            )}
                          </div>
                          <div>
                            <h5 className="font-bold text-slate-900 text-xs">↳ {child.fileName}</h5>
                            <p className="text-[10px] text-slate-500">
                              {child.category} • {child.type}
                            </p>
                          </div>
                        </div>

                        <span
                          className={`px-2 py-0.5 font-bold text-[10px] rounded text-white shrink-0 ${
                            child.status === 'Accepted' ? 'bg-emerald-500' : 'bg-orange-500'
                          }`}
                        >
                          {child.status}
                        </span>
                      </div>

                      {/* Child Attached Files */}
                      {((child.upload_file && child.upload_file.length > 0) || (child.fileUrl && child.fileUrl !== '#')) && (
                        <div className="pt-1.5 border-t border-sky-200/60">
                          <div className="flex flex-wrap gap-1.5">
                            {child.upload_file && child.upload_file.length > 0 ? (
                              child.upload_file.map((file, idx) => (
                                <a
                                  key={idx}
                                  href={file}
                                  target="_blank"
                                  rel="noreferrer"
                                  className="inline-flex items-center space-x-1 px-2 py-0.5 bg-white hover:bg-sky-100 text-sky-700 border border-sky-300 rounded text-[10px] font-medium"
                                >
                                  <ImageIcon className="w-3 h-3 text-sky-500" />
                                  <span className="max-w-[180px] truncate">{file.split('/').pop() || `File ${idx + 1}`}</span>
                                </a>
                              ))
                            ) : child.fileUrl && child.fileUrl !== '#' ? (
                              <a
                                href={child.fileUrl}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex items-center space-x-1 px-2 py-0.5 bg-white hover:bg-sky-100 text-sky-700 border border-sky-300 rounded text-[10px] font-medium"
                              >
                                <ImageIcon className="w-3 h-3 text-sky-500" />
                                <span>View File</span>
                              </a>
                            ) : null}
                          </div>
                        </div>
                      )}

                      <div className="flex items-center justify-between pt-1 border-t border-sky-200/60 text-[10px] text-slate-400">
                        <span>Date: {child.date || 'N/A'}</span>
                        <span>ID: #{child.id}</span>
                      </div>
                    </div>
                  ))}
              </div>
            ))
          )}
        </div>
      </div>

      {/* Bottom Remarks Box */}
      {remarks.length > 0 && (
        <div className="bg-sky-50 rounded-2xl p-4 border border-sky-200 space-y-2">
          <h3 className="text-xs font-bold text-sky-900 border-b border-sky-200 pb-1.5">Remarks / Notes</h3>

          <div className="space-y-1.5">
            {remarks.map((r, i) => (
              <div key={i} className="p-2 bg-white rounded-lg text-xs text-slate-700 border border-sky-100 font-medium">
                • {r}
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

