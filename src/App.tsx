import React, { useState, useMemo, useEffect } from 'react';
import { MobileFrame } from './components/MobileFrame';
import { HeaderBar } from './components/HeaderBar';
import { BottomNav, TabType } from './components/BottomNav';
import { ClientListCard } from './components/ClientListCard';
import { ProjectWorkflowTracker } from './components/ProjectWorkflowTracker';

// Section components
import { BOQSection } from './components/sections/BOQSection';
import { MOMSection } from './components/sections/MOMSection';
import { CivilDrawingSection } from './components/sections/CivilDrawingSection';
import { KTSection } from './components/sections/KTSection';
import { Design3DSection } from './components/sections/Design3DSection';
import { ValidationSection } from './components/sections/ValidationSection';
import { BOMSection } from './components/sections/BOMSection';
import { PurchaseSection } from './components/sections/PurchaseSection';
import { DispatchSection } from './components/sections/DispatchSection';
import { LooseFurnitureSection } from './components/sections/LooseFurnitureSection';
import { PaymentRecordsSection } from './components/sections/PaymentRecordsSection';
import { FinalProductionDrawingSection } from './components/sections/FinalProductionDrawingSection';
import { QCDesignSection } from './components/sections/QCDesignSection';
import { FinalValidationSection } from './components/sections/FinalValidationSection';
import { OnSitePurchaseSection } from './components/sections/OnSitePurchaseSection';
import { AssignedTeamSection } from './components/sections/AssignedTeamSection';
import { ExecutionTimelineSection } from './components/sections/ExecutionTimelineSection';
import { HandoverSection } from './components/sections/HandoverSection';
import { PushNotificationManager } from './components/PushNotificationManager';
import { EscalationSection } from './components/sections/EscalationSection';
import { ClientEscalationSection } from './components/sections/ClientEscalationSection';

// Modals
import { AddQuotationModal } from './components/modals/AddQuotationModal';
import { AddPaymentModal } from './components/modals/AddPaymentModal';
import { AddBOMModal } from './components/modals/AddBOMModal';
import { ClientBoqModal } from './components/modals/ClientBoqModal';

// Mock Data
import {
  INITIAL_CLIENTS,
  INITIAL_ESCALATIONS,
  MOCK_DETAILS_CHECKLIST,
  INITIAL_BOQ_LIST,
  INITIAL_MOM_LIST,
  INITIAL_CIVIL_DRAWINGS,
  INITIAL_KT_RECORD,
  INITIAL_3D_REQUEST,
  INITIAL_POST_VALIDATION_DRAWING,
  INITIAL_BOM_RECORDS,
  INITIAL_PURCHASE_REQUESTS,
  INITIAL_SITE_PURCHASES,
  INITIAL_DISPATCH_ITEMS,
  INITIAL_LOOSE_FURNITURE,
  INITIAL_PAYMENT_RECORDS,
  INITIAL_QC_DESIGNS,
  INITIAL_FINAL_VALIDATIONS,
  INITIAL_ON_SITE_PURCHASES,
  INITIAL_LOOSE_FURNITURE_ITEMS,
} from './mockData';

import { ClientFilterPanel, FilterState, INITIAL_FILTERS } from './components/ClientFilterPanel';
import { ClientProject, BOQItem, PaymentRecord, BOMRecord, DetailSectionItem, EscalationItem, QCDesignItem, FinalValidationItem, OnSitePurchaseItem, LooseFurnitureItem, DispatchItem } from './types';
import {
  SlidersHorizontal,
  ChevronRight,
  ChevronLeft,
  ChevronsLeft,
  ChevronsRight,
  CheckCircle2,
  FileText,
  X,
  AlertCircle,
  AlertTriangle,
  Filter,
  RotateCcw,
  ArrowLeft,
  Calendar,
  Package,
  Truck,
  Sparkles,
  ShieldCheck,
  ShoppingCart,
  Clock,
  Users,
  CreditCard,
  Grid,
  Layers,
  FileCheck,
  Bell,
} from 'lucide-react';
import { LoginPage } from './components/LoginPage';
import { logoutUser, UserData } from './services/authApi';
import { fetchClientList, fetchAllClientList, mapApiClientToClientProject, ClientListFilters, fetchQcDesignList, fetchEscalationList, fetchFinalValidationDesignList, fetchOnSitePurchaseList, fetchLooseFurnitureList, fetchDispatchList, fetchBOMList } from './services/clientApi';
import { initPushNotificationListeners } from './services/fcmService';

const MENU_MODULE_ITEMS = [
  {
    key: 'boq',
    title: 'BOQ',
    desc: 'Bill of Quantities & quotations',
    icon: FileText,
    color: 'text-amber-600 bg-amber-50 border-amber-200',
  },
  {
    key: 'bom',
    title: 'BOM',
    desc: 'Bill of Materials & component tree',
    icon: Package,
    color: 'text-amber-600 bg-amber-50 border-amber-200',
  },
  {
    key: 'dispatch',
    title: 'Dispatch',
    desc: 'Material shipment & delivery logs',
    icon: Truck,
    color: 'text-zinc-900 bg-zinc-100 border-zinc-300',
  },
  {
    key: 'looseFurniture',
    title: 'Loose Furniture',
    desc: 'Custom furniture orders & status',
    icon: Sparkles,
    color: 'text-amber-600 bg-amber-50 border-amber-200',
  },
  {
    key: 'qcDesign',
    title: 'QC Design',
    desc: 'Quality control checklist files',
    icon: CheckCircle2,
    color: 'text-amber-500 bg-amber-400/10 border-amber-400/30',
  },
  {
    key: 'finalValidation',
    title: 'Final Production Drawing',
    desc: 'Post-site validation drawings',
    icon: ShieldCheck,
    color: 'text-zinc-900 bg-zinc-100 border-zinc-300',
  },
  {
    key: 'onSitePurchaseRequest',
    title: 'On Site Purchase Request',
    desc: 'Local material purchase logs',
    icon: ShoppingCart,
    color: 'text-rose-600 bg-rose-50 border-rose-200',
  },
  {
    key: 'executionTimeline',
    title: 'Execution Timeline',
    desc: 'Site execution tasks & schedule timeline',
    icon: Clock,
    color: 'text-amber-600 bg-amber-50 border-amber-200',
  },
  {
    key: 'handover',
    title: 'Handover',
    desc: 'Client handover documents & sign-off files',
    icon: FileCheck,
    color: 'text-emerald-600 bg-emerald-50 border-emerald-200',
  },
  {
    key: 'escalation',
    title: 'Escalation',
    desc: 'Client issue logs, remarks & comments thread',
    icon: AlertTriangle,
    color: 'text-rose-600 bg-rose-50 border-rose-200',
  },
];

const getSectionTitle = (key: string) => {
  const found = MENU_MODULE_ITEMS.find((m) => m.key === key);
  if (found) return found.title;
  const mockFound = MOCK_DETAILS_CHECKLIST.find((m) => m.key === key);
  if (mockFound) return mockFound.title;
  return key;
};

export default function App() {
  // Authentication State
  const [isLoggedIn, setIsLoggedIn] = useState<boolean>(false);
  const [currentUser, setCurrentUser] = useState<string>('Nishant Singh');
  const [userData, setUserData] = useState<UserData | null>(null);
  const [authToken, setAuthToken] = useState<string>('');

  // Navigation & View States
  const [activeTab, setActiveTab] = useState<TabType>('dashboard');
  const [clients, setClients] = useState<ClientProject[]>([]);
  const [selectedClient, setSelectedClient] = useState<ClientProject | null>(null);
  const [qcDesignItems, setQcDesignItems] = useState<QCDesignItem[]>([]);
  const [selectedChecklistKey, setSelectedChecklistKey] = useState<string>('menu');
  
  // Search & Filter
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [filterState, setFilterState] = useState<FilterState>(INITIAL_FILTERS);
  const [isFilterModalOpen, setIsFilterModalOpen] = useState<boolean>(false);

  // Dynamic Datasets
  const [escalations, setEscalations] = useState<EscalationItem[]>(INITIAL_ESCALATIONS);
  const [finalValidations, setFinalValidations] = useState<FinalValidationItem[]>(INITIAL_FINAL_VALIDATIONS);
  const [onSitePurchases, setOnSitePurchases] = useState<OnSitePurchaseItem[]>(INITIAL_ON_SITE_PURCHASES);
  const [looseFurnitures, setLooseFurnitures] = useState<LooseFurnitureItem[]>(INITIAL_LOOSE_FURNITURE_ITEMS);
  const [dispatchItems, setDispatchItems] = useState<DispatchItem[]>(INITIAL_DISPATCH_ITEMS);
  const [boqList, setBoqList] = useState<BOQItem[]>(INITIAL_BOQ_LIST);
  const [paymentRecords, setPaymentRecords] = useState<PaymentRecord[]>(INITIAL_PAYMENT_RECORDS);
  const [bomRecords, setBomRecords] = useState<BOMRecord[]>(INITIAL_BOM_RECORDS);
  const [design3dRequest, setDesign3dRequest] = useState(INITIAL_3D_REQUEST);

  // Active filter count calculation
  const activeFilterCount = useMemo(() => {
    let count = 0;
    if (filterState.siteValidationFrom || filterState.siteValidationTo) count++;
    if (filterState.dispatchFrom || filterState.dispatchTo) count++;
    if (filterState.siteStartFrom || filterState.siteStartTo) count++;
    if (filterState.handOverFrom || filterState.handOverTo) count++;
    if (filterState.ktMeetingFrom || filterState.ktMeetingTo) count++;
    return count;
  }, [filterState]);

  // Modals
  const [isAddQuotationOpen, setIsAddQuotationOpen] = useState(false);
  const [isAddPaymentOpen, setIsAddPaymentOpen] = useState(false);
  const [isAddBOMOpen, setIsAddBOMOpen] = useState(false);
  const [bomParentTargetId, setBomParentTargetId] = useState<string | undefined>(undefined);
  const [isBoqModalOpen, setIsBoqModalOpen] = useState(false);
  const [boqModalClient, setBoqModalClient] = useState<ClientProject | null>(null);

  const handleOpenBoqModal = (client: ClientProject) => {
    setBoqModalClient(client);
    setIsBoqModalOpen(true);
  };

  // Toast message
  const [toastMessage, setToastMessage] = useState<string | null>(null);
  const [isLoadingClients, setIsLoadingClients] = useState<boolean>(false);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(null), 3000);
  };

  const loadClientsFromApi = async (tokenToUse?: string, searchOverride?: string) => {
    const activeToken = tokenToUse || authToken;
    if (!activeToken) return;

    setIsLoadingClients(true);
    try {
      const filters: ClientListFilters = {
        page: 1,
        limit: 1000,
        search: searchOverride !== undefined ? searchOverride : searchQuery,
      };

      if (filterState.paymentFilter === 'Red Alert') filters.payment_filter = 'red';
      if (filterState.paymentFilter === 'Green Ok') filters.payment_filter = 'green';
      if (filterState.ktMeetingFrom) filters.kt_meeting_from = filterState.ktMeetingFrom;
      if (filterState.ktMeetingTo) filters.kt_meeting_to = filterState.ktMeetingTo;
      if (filterState.dispatchFrom) filters.dispatch_date_from = filterState.dispatchFrom;
      if (filterState.dispatchTo) filters.dispatch_date_to = filterState.dispatchTo;
      if (filterState.handOverFrom) filters.hand_over_request_date_from = filterState.handOverFrom;
      if (filterState.handOverTo) filters.hand_over_request_date_to = filterState.handOverTo;

      const res = await fetchAllClientList(activeToken, filters);

      // Fetch QC Design list from API
      try {
        const qcList = await fetchQcDesignList(activeToken);
        setQcDesignItems(qcList);
      } catch (qcErr) {
        console.warn('Failed to fetch QC Design list:', qcErr);
      }

      // Fetch Escalations from API
      try {
        const escList = await fetchEscalationList(activeToken);
        if (escList && Array.isArray(escList)) {
          setEscalations(escList);
        }
      } catch (escErr) {
        console.warn('Failed to fetch Escalations list:', escErr);
      }

      // Fetch Final Validation Design list from API
      try {
        const fvList = await fetchFinalValidationDesignList(activeToken);
        if (fvList && fvList.length > 0) {
          setFinalValidations(fvList);
        }
      } catch (fvErr) {
        console.warn('Failed to fetch Final Validation Design list:', fvErr);
      }

      // Fetch On Site Purchase list from API
      try {
        const ospList = await fetchOnSitePurchaseList(activeToken);
        if (ospList && ospList.length > 0) {
          setOnSitePurchases(ospList);
        }
      } catch (ospErr) {
        console.warn('Failed to fetch On Site Purchase list:', ospErr);
      }

      // Fetch Loose Furniture list from API
      try {
        const lfList = await fetchLooseFurnitureList(activeToken);
        if (lfList && lfList.length > 0) {
          setLooseFurnitures(lfList);
        }
      } catch (lfErr) {
        console.warn('Failed to fetch Loose Furniture list:', lfErr);
      }

      // Fetch Dispatch list from API
      try {
        const dList = await fetchDispatchList(activeToken);
        if (dList && dList.length > 0) {
          setDispatchItems(dList);
        }
      } catch (dErr) {
        console.warn('Failed to fetch Dispatch list:', dErr);
      }

      // Fetch BOM list from API
      try {
        const bomList = await fetchBOMList(activeToken);
        if (bomList && bomList.length > 0) {
          setBomRecords(bomList);
        }
      } catch (bomErr) {
        console.warn('Failed to fetch BOM list:', bomErr);
      }

      if (res && res.data && Array.isArray(res.data) && res.data.length > 0) {
        const mapped = res.data.map(mapApiClientToClientProject);
        setClients(mapped);
        clientsRef.current = mapped;

        // Process any pending push notification redirection automatically!
        processPendingNotification(mapped);

        if (mapped.length > 0 && !selectedClient) {
          setSelectedClient(mapped[0]);
        }
        showToast(`Loaded all ${mapped.length} clients from CRM API`);
      } else {
        if (res && res.message) {
          showToast(`CRM API: ${res.message}`);
        } else {
          showToast('CRM API returned 0 clients. Using fallback dataset.');
        }
        setClients(INITIAL_CLIENTS);
        setSelectedClient(INITIAL_CLIENTS[0] || null);
      }
    } catch (err: any) {
      console.warn('CRM API load failed, using local dataset fallback:', err?.message || err);
      setClients(INITIAL_CLIENTS);
      setSelectedClient(INITIAL_CLIENTS[0] || null);
      showToast(err?.message || 'Using local dataset (CRM connection issue)');
    } finally {
      setIsLoadingClients(false);
    }
  };

  const clientsRef = React.useRef(clients);
  useEffect(() => {
    clientsRef.current = clients;
  }, [clients]);

  const pendingNotificationRef = React.useRef<{ clientId: string; section?: string } | null>(null);

  const processPendingNotification = React.useCallback((clientsList?: ClientProject[]) => {
    const pending = pendingNotificationRef.current;
    const targetList = (clientsList && clientsList.length > 0) ? clientsList : clientsRef.current;

    if (!pending || !pending.clientId || targetList.length === 0) return;

    const rawTargetStr = String(pending.clientId).trim();
    const numTarget = rawTargetStr.replace(/\D/g, '');

    const matchingClient = targetList.find(
      c => (numTarget && String(c.clientIdNum) === numTarget) ||
           c.id.toLowerCase() === rawTargetStr.toLowerCase()
    );

    if (matchingClient) {
      console.log('Processing pending notification for client:', matchingClient.name);

      setSelectedClient(matchingClient);
      setShowAllClients(false);
      setSelectedChecklistKey('menu');
      setActiveTab('checklist');

      if (!window.history.state?.clientView) {
        window.history.pushState({ clientView: true, clientId: matchingClient.id }, '', `#client-${matchingClient.id}`);
      }

      const targetSection = pending.section;
      if (targetSection === 'onSitePurchase' || targetSection === 'onSitePurchaseRequest') {
        setSelectedChecklistKey('onSitePurchase');
      } else if (targetSection === 'escalation') {
        setSelectedChecklistKey('escalation');
      } else if (targetSection === 'boq') {
        handleOpenBoqModal(matchingClient);
      } else if (targetSection === 'finalValidation') {
        setSelectedChecklistKey('finalValidation');
      } else if (targetSection === 'qcDesign') {
        setSelectedChecklistKey('qcDesign');
      } else if (targetSection === 'dispatch') {
        setSelectedChecklistKey('dispatch');
      } else if (targetSection === 'looseFurniture') {
        setSelectedChecklistKey('looseFurniture');
      } else if (targetSection === 'bom') {
        setSelectedChecklistKey('bom');
      } else if (targetSection === 'executionTimeline') {
        setSelectedChecklistKey('executionTimeline');
      } else if (targetSection === 'handover') {
        setSelectedChecklistKey('handover');
      }

      showToast(`Redirected to ${matchingClient.name}`);
      pendingNotificationRef.current = null;
    }
  }, []);

  useEffect(() => {
    // Listen to browser / native popstate back navigation (swipe back / back button)
    const handlePopState = (e: PopStateEvent) => {
      if (!e.state?.clientView) {
        setShowAllClients(true);
      }
    };
    window.addEventListener('popstate', handlePopState);
    return () => window.removeEventListener('popstate', handlePopState);
  }, []);

  useEffect(() => {
    // Initialize push notifications on native app startup
    initPushNotificationListeners((data) => {
      console.log('App received push notification data:', data);
      const targetClientId = data?.client_id || data?.clientId || data?.client_sr_id;
      const targetSection = data?.section;

      if (targetClientId) {
        pendingNotificationRef.current = { clientId: String(targetClientId), section: targetSection };
        processPendingNotification();
      }
    });

    const handleSessionExpired = (e: Event) => {
      const customEvent = e as CustomEvent<{ message?: string }>;
      const msg = customEvent.detail?.message || 'Authorization header missing. Session expired or logged in elsewhere.';

      // Clear all stored authentication tokens & credentials
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_data');
      localStorage.removeItem('user_id');

      // Logout and reset app state to display LoginPage
      setIsLoggedIn(false);
      setUserData(null);
      setAuthToken('');
      setClients([]);
      setSelectedClient(null);

      showToast(`⚠️ ${msg}`);
    };

    window.addEventListener('auth:session_expired', handleSessionExpired);

    const savedToken = localStorage.getItem('auth_token');
    const savedUserData = localStorage.getItem('user_data');
    if (savedToken && savedUserData && !isLoggedIn) {
      try {
        const parsed = JSON.parse(savedUserData);
        setIsLoggedIn(true);
        setUserData(parsed);
        setAuthToken(savedToken);
        setCurrentUser(parsed.fullname || parsed.username || 'User');
        loadClientsFromApi(savedToken);
      } catch (e) {
        console.warn('Failed to restore session from localStorage:', e);
      }
    } else if (isLoggedIn && authToken) {
      loadClientsFromApi(authToken);
    }

    return () => {
      window.removeEventListener('auth:session_expired', handleSessionExpired);
    };
  }, [isLoggedIn, authToken]);

  // Filtered Client List
  const filteredClients = useMemo(() => {
    return clients.filter((c) => {
      const q = searchQuery.trim().toLowerCase();

      // User filter
      let matchesUser = true;
      if (filterState.user !== 'All Users') {
        matchesUser =
          c.salesManager.toLowerCase().includes(filterState.user.toLowerCase()) ||
          c.name.toLowerCase().includes(filterState.user.toLowerCase());
      }

      if (!q) {
        return matchesUser;
      }

      const idMatch = c.id.toLowerCase().includes(q);
      const numIdMatch = c.clientIdNum ? String(c.clientIdNum).toLowerCase().includes(q) : false;
      const nameMatch = c.name.toLowerCase().includes(q);
      const salesMatch = c.salesManager.toLowerCase().includes(q);
      const phaseMatch = c.phase.toLowerCase().includes(q);
      const emailMatch = c.email ? c.email.toLowerCase().includes(q) : false;
      const mobileMatch = c.mobile ? c.mobile.includes(q) : false;
      const designerMatch = c.assignedTeam?.designer ? c.assignedTeam.designer.toLowerCase().includes(q) : false;
      const pmMatch = c.assignedTeam?.projectManager ? c.assignedTeam.projectManager.toLowerCase().includes(q) : false;

      const matchesSearch =
        idMatch ||
        numIdMatch ||
        nameMatch ||
        salesMatch ||
        phaseMatch ||
        emailMatch ||
        mobileMatch ||
        designerMatch ||
        pmMatch;

      return matchesSearch && matchesUser;
    });
  }, [clients, searchQuery, filterState]);

  // Client List Pagination State
  const [clientListPage, setClientListPage] = useState<number>(1);
  const [clientsPerPage, setClientsPerPage] = useState<number>(10);

  // Reset page to 1 when search query, filters, client count, or items per page change
  useEffect(() => {
    setClientListPage(1);
  }, [searchQuery, filterState, clients.length, clientsPerPage]);

  const totalClients = filteredClients.length;
  const totalClientPages = Math.ceil(totalClients / clientsPerPage) || 1;
  const safeClientPage = Math.min(Math.max(1, clientListPage), totalClientPages);

  const clientStartIndex = (safeClientPage - 1) * clientsPerPage;
  const clientEndIndex = Math.min(clientStartIndex + clientsPerPage, totalClients);

  const paginatedClients = useMemo(() => {
    return filteredClients.slice(clientStartIndex, clientEndIndex);
  }, [filteredClients, clientStartIndex, clientEndIndex]);
  const handleSelectClient = (client: ClientProject) => {
    setSelectedClient(client);
    setShowAllClients(false);
    setSelectedChecklistKey('menu');
    setActiveTab('checklist');
    if (!window.history.state?.clientView) {
      window.history.pushState({ clientView: true, clientId: client.id }, '', `#client-${client.id}`);
    }
    showToast(`Loaded Client Details: ${client.name} (${client.id})`);
  };

  const handleOpenDetails = (client: ClientProject) => {
    setSelectedClient(client);
    setShowAllClients(false);
    setSelectedChecklistKey('menu');
    setActiveTab('checklist');
    if (!window.history.state?.clientView) {
      window.history.pushState({ clientView: true, clientId: client.id }, '', `#client-${client.id}`);
    }
  };

  const handleOpenWorkflow = (client: ClientProject) => {
    setSelectedClient(client);
    setShowAllClients(false);
    setSelectedChecklistKey('menu');
    setActiveTab('checklist');
    if (!window.history.state?.clientView) {
      window.history.pushState({ clientView: true, clientId: client.id }, '', `#client-${client.id}`);
    }
  };

  const handleRefreshFinalValidation = async () => {
    if (!authToken) return;
    try {
      const fvList = await fetchFinalValidationDesignList(authToken);
      if (fvList) {
        setFinalValidations(fvList);
      }
    } catch (err) {
      console.error('Error refreshing Final Validation Design list:', err);
    }
  };

  const handleRefreshOnSitePurchase = async () => {
    if (!authToken) return;
    try {
      const ospList = await fetchOnSitePurchaseList(authToken);
      if (ospList) {
        setOnSitePurchases(ospList);
      }
    } catch (err) {
      console.error('Error refreshing On Site Purchase list:', err);
    }
  };

  const handleRefreshLooseFurniture = async () => {
    if (!authToken) return;
    try {
      const lfList = await fetchLooseFurnitureList(authToken);
      if (lfList) {
        setLooseFurnitures(lfList);
      }
    } catch (err) {
      console.error('Error refreshing Loose Furniture list:', err);
    }
  };

  const handleRefreshDispatch = async () => {
    if (!authToken) return;
    try {
      const dList = await fetchDispatchList(authToken);
      if (dList) {
        setDispatchItems(dList);
      }
    } catch (err) {
      console.error('Error refreshing Dispatch list:', err);
    }
  };

  const handleRefreshBOM = async () => {
    if (!authToken) return;
    try {
      const bomList = await fetchBOMList(authToken);
      if (bomList) {
        setBomRecords(bomList);
      }
    } catch (err) {
      console.error('Error refreshing BOM list:', err);
    }
  };

  const handleAddQuotation = (newQuote: BOQItem) => {
    setBoqList([newQuote, ...boqList]);
    showToast('New Quotation Added Successfully!');
  };

  const handleAddPayment = (newPay: PaymentRecord) => {
    setPaymentRecords([newPay, ...paymentRecords]);
    showToast(`Recorded Payment of ₹${newPay.amount.toLocaleString('en-IN')}`);
  };

  const handleAddBOM = (newBOM: BOMRecord, parentId?: string) => {
    if (parentId) {
      setBomRecords((prev) =>
        prev.map((b) => {
          if (b.id === parentId) {
            return {
              ...b,
              children: [...(b.children || []), newBOM],
            };
          }
          return b;
        })
      );
    } else {
      setBomRecords([newBOM, ...bomRecords]);
    }
    showToast('BOM Record Saved Successfully!');
  };

  const [showAllClients, setShowAllClients] = useState<boolean>(false);

  // Detail section items mapper
  const renderDetailSectionContent = () => {
    switch (selectedChecklistKey) {
      case 'workflow':
      case 'clientDetail':
      case 'details':
        return (
          <ProjectWorkflowTracker
            client={selectedClient}
            authToken={authToken}
            onSelectSection={(secKey) => {
              setSelectedChecklistKey(secKey);
            }}
          />
        );
      case 'assignedTeam':
        return (
          <AssignedTeamSection
            client={selectedClient}
            onUpdateTeam={(clientId, roleKey, memberName) => {
              setClients((prev) =>
                prev.map((c) => {
                  if (c.id === clientId) {
                    return {
                      ...c,
                      assignedTeam: {
                        ...c.assignedTeam,
                        [roleKey]: memberName,
                      },
                    };
                  }
                  return c;
                })
              );
              showToast(`Assigned ${memberName} to ${clientId}`);
            }}
          />
        );
      case 'boq':
        return <BOQSection boqList={boqList} client={selectedClient} showAllClients={showAllClients} token={authToken} />;
      case 'mom':
        return <MOMSection momList={INITIAL_MOM_LIST} />;
      case 'civilDrawing':
        return <CivilDrawingSection drawings={INITIAL_CIVIL_DRAWINGS} />;
      case 'kt':
        return <KTSection ktData={INITIAL_KT_RECORD} />;
      case 'design3d':
        return (
          <Design3DSection
            request={design3dRequest}
            onUpdateRequestStatus={(status) => {
              setDesign3dRequest((prev) => ({ ...prev, status }));
              showToast(`3D Design Request status updated to ${status}`);
            }}
          />
        );
      case 'validation':
        return <ValidationSection postDrawing={INITIAL_POST_VALIDATION_DRAWING} />;
      case 'bom':
        return (
          <BOMSection
            bomRecords={bomRecords}
            clients={clients}
            client={selectedClient}
            showAllClients={showAllClients}
            authToken={authToken}
            showToast={showToast}
            onRefresh={handleRefreshBOM}
            onAddBOM={() => {
              setBomParentTargetId(undefined);
              setIsAddBOMOpen(true);
            }}
            onAddChildBOM={(parentId) => {
              setBomParentTargetId(parentId);
              setIsAddBOMOpen(true);
            }}
          />
        );
      case 'purchaseRequest':
        return (
          <PurchaseSection
            purchases={INITIAL_PURCHASE_REQUESTS}
            sitePurchases={INITIAL_SITE_PURCHASES}
            mode="purchaseRequest"
            onAddPurchase={() =>
              showToast(`Add Purchase Request`)
            }
          />
        );
      case 'onSitePurchase':
        return (
          <PurchaseSection
            purchases={INITIAL_PURCHASE_REQUESTS}
            sitePurchases={INITIAL_SITE_PURCHASES}
            mode="onSitePurchase"
            onAddPurchase={() =>
              showToast(`Add On Site Purchase Request`)
            }
          />
        );
      case 'dispatch':
        return (
          <DispatchSection
            dispatchItems={dispatchItems}
            clients={clients}
            client={selectedClient}
            showAllClients={showAllClients}
            authToken={authToken}
            showToast={showToast}
            onRefresh={handleRefreshDispatch}
          />
        );
      case 'looseFurniture':
        return (
          <LooseFurnitureSection
            items={looseFurnitures}
            clients={clients}
            client={selectedClient}
            showAllClients={showAllClients}
            authToken={authToken}
            showToast={showToast}
            onRefresh={handleRefreshLooseFurniture}
          />
        );
      case 'qcDesign':
        return <QCDesignSection items={qcDesignItems} client={selectedClient} showAllClients={showAllClients} />;
      case 'finalValidation':
        return (
          <FinalValidationSection
            items={finalValidations}
            clients={clients}
            client={selectedClient}
            showAllClients={showAllClients}
            authToken={authToken}
            showToast={showToast}
            onRefresh={handleRefreshFinalValidation}
          />
        );
      case 'onSitePurchaseRequest':
        return (
          <OnSitePurchaseSection
            items={onSitePurchases}
            clients={clients}
            client={selectedClient}
            showAllClients={showAllClients}
            authToken={authToken}
            showToast={showToast}
            onRefresh={handleRefreshOnSitePurchase}
          />
        );
      case 'executionTimeline':
        return <ExecutionTimelineSection client={selectedClient} showAllClients={showAllClients} token={authToken} />;
      case 'handover':
        return <HandoverSection client={selectedClient} showAllClients={showAllClients} showToast={showToast} />;
      case 'pushNotification':
        return <PushNotificationManager showToast={showToast} />;
      case 'escalation':
        return (
          <EscalationSection
            escalations={escalations}
            clients={clients}
            client={selectedClient}
            showAllClients={showAllClients}
            authToken={authToken}
            showToast={showToast}
            onUpdateStatus={(id, status) => {
              setEscalations((prev) =>
                prev.map((e) => (e.id === id ? { ...e, status } : e))
              );
              showToast(`Escalation ${id} status updated to ${status}`);
            }}
            onRefreshEscalations={async () => {
              if (authToken) {
                const list = await fetchEscalationList(authToken);
                if (list && list.length > 0) {
                  setEscalations(list);
                }
              }
            }}
          />
        );
      case 'clientEscalation':
        return (
          <ClientEscalationSection
            client={selectedClient || clients[0]}
            authToken={authToken}
            showToast={showToast}
            onEscalationCreated={(newItem) => {
              setEscalations((prev) => [newItem, ...prev]);
            }}
          />
        );
      case 'paymentRecords':
        return (
          <PaymentRecordsSection
            payments={paymentRecords}
            onAddPayment={() => setIsAddPaymentOpen(true)}
          />
        );
      default:
        return (
          <div className="bg-white rounded-2xl p-6 border border-slate-200 text-center space-y-2">
            <AlertCircle className="w-8 h-8 text-orange-500 mx-auto" />
            <h3 className="text-sm font-bold text-slate-800">
              Section: {MOCK_DETAILS_CHECKLIST.find((i) => i.key === selectedChecklistKey)?.title || selectedChecklistKey}
            </h3>
            <p className="text-xs text-slate-500">
              Details and active files logged for client {selectedClient.id}.
            </p>
          </div>
        );
    }
  };

  if (!isLoggedIn) {
    return (
      <LoginPage
        onLoginSuccess={(data, token) => {
          const safeData = data && typeof data === 'object' ? data : { username: 'User', user_id: '5' };
          const effectiveToken = token || (safeData as any)?.token || (safeData as any)?.user_id || 'active_session';
          const nameToUse = (safeData as any)?.fullname || (safeData as any)?.username || 'User';

          setIsLoggedIn(true);
          setUserData(safeData);
          setAuthToken(String(effectiveToken));
          setCurrentUser(nameToUse);
          localStorage.setItem('auth_token', String(effectiveToken));
          localStorage.setItem('user_data', JSON.stringify(safeData));
          showToast(`Welcome, ${nameToUse}! Logged in via API`);
          loadClientsFromApi(String(effectiveToken));
        }}
      />
    );
  }

  const handleLogoutApi = async () => {
    const uid = userData?.user_id || '5';
    try {
      showToast('Logging out...');
      const res = await logoutUser(uid);
      setIsLoggedIn(false);
      setUserData(null);
      setAuthToken('');
      showToast(res.message || 'Logout successful. Token invalidated.');
    } catch (e: any) {
      setIsLoggedIn(false);
      setUserData(null);
      setAuthToken('');
      showToast('Logged out successfully');
    }
  };

  return (
    <MobileFrame>
      {/* Dynamic Header */}
      <HeaderBar
        title={
          activeTab === 'dashboard'
            ? 'Client Directory'
            : activeTab === 'escalation'
            ? 'Escalation Tracker'
            : activeTab === 'checklist'
            ? 'Modules & Details'
            : 'Site & Dispatch'
        }
        showBack={activeTab !== 'dashboard'}
        onBack={() => setActiveTab('dashboard')}
        searchQuery={activeTab === 'dashboard' ? searchQuery : undefined}
        onSearchChange={activeTab === 'dashboard' ? setSearchQuery : undefined}
        onOpenFilter={() => setIsFilterModalOpen(true)}
        currentUser={currentUser}
        onLogout={handleLogoutApi}
        onNotificationClick={() => {
          showToast('Push Notifications active in APK (FCM connected)');
        }}
      />

      {/* Floating Toast Notification */}
      {toastMessage && (
        <div className="fixed top-14 left-1/2 -translate-x-1/2 z-50 bg-zinc-950 text-white font-bold text-xs px-4 py-2 rounded-full shadow-2xl border border-amber-400/50 flex items-center space-x-2 animate-in fade-in slide-in-from-top duration-300">
          <CheckCircle2 className="w-4 h-4 text-amber-400" />
          <span>{toastMessage}</span>
        </div>
      )}

      {/* Main Screen Content Router */}
      <div className="flex-1 pb-4">
        {/* TAB 1: CLIENT */}
        {activeTab === 'dashboard' && (
          <div className="p-3.5 space-y-3">
            {/* Header Client Count & Filter Trigger Button */}
            <div className="flex items-center justify-between text-xs px-1 bg-white p-2.5 rounded-xl border border-zinc-200 shadow-2xs">
              <span className="text-zinc-600 font-medium flex items-center space-x-1.5">
                <span>
                  Showing <strong className="text-zinc-950 font-extrabold">{totalClients === 0 ? 0 : clientStartIndex + 1}</strong> to{' '}
                  <strong className="text-zinc-950 font-extrabold">{clientEndIndex}</strong> of{' '}
                  <strong className="text-zinc-950 font-extrabold">{totalClients}</strong> Clients
                </span>
                {isLoadingClients && (
                  <span className="inline-block w-3 h-3 border-2 border-amber-400/30 border-t-amber-400 rounded-full animate-spin" />
                )}
              </span>

              <div className="flex items-center space-x-2">
                <button
                  onClick={() => loadClientsFromApi()}
                  disabled={isLoadingClients}
                  title="Sync with CRM API"
                  className="p-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 rounded-lg transition-colors cursor-pointer disabled:opacity-50 flex items-center space-x-1 text-[11px] font-semibold border border-zinc-200"
                >
                  <RotateCcw className={`w-3.5 h-3.5 text-zinc-700 ${isLoadingClients ? 'animate-spin text-amber-500' : ''}`} />
                  <span className="hidden sm:inline">Sync Data</span>
                </button>

                {activeFilterCount > 0 && (
                  <button
                    onClick={() => setFilterState(INITIAL_FILTERS)}
                    className="text-[11px] text-zinc-500 hover:text-amber-600 font-semibold transition-colors cursor-pointer"
                  >
                    Reset
                  </button>
                )}

                <button
                  onClick={() => setIsFilterModalOpen(true)}
                  className="px-3 py-1.5 bg-zinc-950 hover:bg-zinc-900 text-white font-bold text-xs rounded-lg shadow-xs flex items-center space-x-1.5 transition-colors cursor-pointer border border-zinc-800"
                >
                  <Filter className="w-3.5 h-3.5 text-amber-400" />
                  <span>Filters</span>
                  {activeFilterCount > 0 && (
                    <span className="bg-amber-400 text-zinc-950 text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-extrabold">
                      {activeFilterCount}
                    </span>
                  )}
                </button>
              </div>
            </div>

            {/* Clients Stack */}
            <div className="space-y-3">
              {filteredClients.length === 0 ? (
                <div className="bg-white rounded-2xl p-8 border border-zinc-200 text-center space-y-3 shadow-2xs my-2">
                  <div className="w-12 h-12 rounded-full bg-zinc-100 text-zinc-400 flex items-center justify-center mx-auto border border-zinc-200">
                    <Users className="w-6 h-6 text-zinc-400" />
                  </div>
                  <div className="space-y-1">
                    <h3 className="text-sm font-bold text-zinc-900">No Client Data Found</h3>
                    <p className="text-xs text-zinc-500 max-w-xs mx-auto leading-relaxed">
                      {clients.length === 0
                        ? 'No client records fetched from CRM API. Click "Sync Data" or check your filters.'
                        : 'No clients matched your search or filter parameters. Try clearing your filters.'}
                    </p>
                  </div>
                  <button
                    onClick={() => {
                      setSearchQuery('');
                      setFilterState(INITIAL_FILTERS);
                      if (authToken) {
                        loadClientsFromApi(authToken, '');
                      }
                    }}
                    className="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-zinc-950 hover:bg-zinc-800 text-white font-bold rounded-xl text-xs transition-colors cursor-pointer shadow-2xs"
                  >
                    <RotateCcw className="w-3.5 h-3.5 text-amber-400" />
                    <span>Reset Filters &amp; Reload</span>
                  </button>
                </div>
              ) : (
                paginatedClients.map((client) => (
                  <ClientListCard
                    key={client.id}
                    client={client}
                    onSelectClient={handleSelectClient}
                    onOpenDetails={handleOpenDetails}
                    onOpenWorkflow={handleOpenWorkflow}
                    onOpenBoq={handleOpenBoqModal}
                    onOpenFinalValidation={(c) => {
                      setSelectedClient(c);
                      setShowAllClients(false);
                      setSelectedChecklistKey('finalValidation');
                      setActiveTab('checklist');
                      showToast(`Opened Final Production Drawing for ${c.name}`);
                    }}
                  />
                ))
              )}
            </div>

            {/* Client List Pagination Controls */}
            {totalClients > 0 && (
              <div className="bg-white border border-zinc-200 rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-zinc-600 shadow-2xs">
                {/* Info & Items Per Page Selector */}
                <div className="flex flex-wrap items-center justify-between sm:justify-start w-full sm:w-auto gap-3">
                  <span className="font-medium text-zinc-700">
                    Showing <strong className="text-zinc-950 font-bold">{totalClients === 0 ? 0 : clientStartIndex + 1}</strong> to{' '}
                    <strong className="text-zinc-950 font-bold">{clientEndIndex}</strong> of{' '}
                    <strong className="text-zinc-950 font-bold">{totalClients}</strong> entries
                  </span>

                  <div className="flex items-center space-x-1.5 pl-2 border-l border-zinc-200">
                    <span className="text-zinc-500 text-[11px]">Per page:</span>
                    <select
                      value={clientsPerPage}
                      onChange={(e) => setClientsPerPage(Number(e.target.value))}
                      className="bg-zinc-50 border border-zinc-300 font-bold text-zinc-800 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500 cursor-pointer shadow-2xs"
                    >
                      <option value={5}>5</option>
                      <option value={10}>10</option>
                      <option value={20}>20</option>
                      <option value={50}>50</option>
                      <option value={100}>100</option>
                    </select>
                  </div>
                </div>

                {/* Page Navigation Buttons */}
                <div className="flex items-center space-x-1">
                  <button
                    onClick={() => setClientListPage(1)}
                    disabled={safeClientPage === 1}
                    className="p-1.5 rounded-lg border border-zinc-200 bg-white hover:bg-zinc-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-zinc-700 cursor-pointer"
                    title="First Page"
                  >
                    <ChevronsLeft className="w-4 h-4" />
                  </button>
                  <button
                    onClick={() => setClientListPage((p) => Math.max(1, p - 1))}
                    disabled={safeClientPage === 1}
                    className="p-1.5 rounded-lg border border-zinc-200 bg-white hover:bg-zinc-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-zinc-700 cursor-pointer flex items-center space-x-1 px-2 font-medium"
                    title="Previous Page"
                  >
                    <ChevronLeft className="w-4 h-4" />
                    <span className="hidden md:inline">Prev</span>
                  </button>

                  {/* Page Number Buttons */}
                  <div className="flex items-center space-x-1 px-1">
                    {(() => {
                      const pages: (number | string)[] = [];
                      if (totalClientPages <= 3) {
                        for (let i = 1; i <= totalClientPages; i++) pages.push(i);
                      } else {
                        pages.push(1, 2);
                        if (safeClientPage > 2 && safeClientPage < totalClientPages) {
                          if (safeClientPage > 3) pages.push('...');
                          pages.push(safeClientPage);
                          if (safeClientPage < totalClientPages - 1) pages.push('...');
                        } else {
                          pages.push('...');
                        }
                        pages.push(totalClientPages);
                      }

                      return pages.map((item, idx) => {
                        if (typeof item === 'string') {
                          return (
                            <span key={`ellipsis-${idx}`} className="px-1 text-zinc-400 font-bold text-xs select-none">
                              {item}
                            </span>
                          );
                        }
                        return (
                          <button
                            key={item}
                            onClick={() => setClientListPage(item)}
                            className={`min-w-[32px] h-8 rounded-lg font-bold text-xs transition-all cursor-pointer ${
                              item === safeClientPage
                                ? 'bg-zinc-950 text-white shadow-2xs'
                                : 'bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-100'
                            }`}
                          >
                            {item}
                          </button>
                        );
                      });
                    })()}
                  </div>

                  <button
                    onClick={() => setClientListPage((p) => Math.min(totalClientPages, p + 1))}
                    disabled={safeClientPage === totalClientPages}
                    className="p-1.5 rounded-lg border border-zinc-200 bg-white hover:bg-zinc-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-zinc-700 cursor-pointer flex items-center space-x-1 px-2 font-medium"
                    title="Next Page"
                  >
                    <span className="hidden md:inline">Next</span>
                    <ChevronRight className="w-4 h-4" />
                  </button>
                  <button
                    onClick={() => setClientListPage(totalClientPages)}
                    disabled={safeClientPage === totalClientPages}
                    className="p-1.5 rounded-lg border border-zinc-200 bg-white hover:bg-zinc-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-zinc-700 cursor-pointer"
                    title="Last Page"
                  >
                    <ChevronsRight className="w-4 h-4" />
                  </button>
                </div>
              </div>
            )}
          </div>
        )}

        {/* TAB 2: ESCALATION */}
        {activeTab === 'escalation' && (
          <div className="p-3.5">
            <EscalationSection
              escalations={escalations}
              clients={clients}
              client={selectedClient}
              showAllClients={showAllClients}
              authToken={authToken}
              showToast={showToast}
              onAddEscalation={(newEsc) => {
                setEscalations([newEsc, ...escalations]);
                showToast(`Escalation ${newEsc.id} logged for ${newEsc.clientName || newEsc.client_name}`);
              }}
              onUpdateStatus={(id, status) => {
                setEscalations((prev) =>
                  prev.map((e) => (e.id === id ? { ...e, status } : e))
                );
                showToast(`Escalation ${id} status updated to ${status}`);
              }}
              onRefreshEscalations={async () => {
                if (authToken) {
                  const list = await fetchEscalationList(authToken);
                  if (list && list.length > 0) {
                    setEscalations(list);
                  }
                }
              }}
            />
          </div>
        )}

        {/* TAB 3: MENU (CHECKLIST) */}
        {activeTab === 'checklist' && (
          <div className="p-3.5 space-y-3">
            {selectedChecklistKey === 'menu' ? (
              <div className="space-y-3">
                <div className="bg-zinc-950 text-white p-3.5 rounded-2xl border border-zinc-800 shadow-sm flex items-center justify-between">
                  <div>
                    <h2 className="text-sm font-bold text-white flex items-center space-x-2">
                      <Users className="w-4 h-4 text-amber-400 shrink-0" />
                      <span>{selectedClient ? `${selectedClient.name} (${selectedClient.id})` : 'All Clients'}</span>
                    </h2>
                    <p className="text-[11px] text-zinc-400 mt-0.5 font-medium">
                      Client Details
                    </p>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                  {MENU_MODULE_ITEMS.map((item) => {
                    const Icon = item.icon;
                    return (
                      <button
                        key={item.key}
                        onClick={() => {
                          setSelectedChecklistKey(item.key);
                          showToast(`Opened ${item.title}`);
                        }}
                        className="bg-white p-3.5 rounded-xl border border-zinc-200 shadow-2xs hover:shadow-md hover:border-amber-400 transition-all flex items-center justify-between text-left group cursor-pointer"
                      >
                        <div className="flex items-center space-x-3">
                          <div className={`p-2.5 rounded-xl border ${item.color} shadow-2xs shrink-0`}>
                            <Icon className="w-4 h-4" />
                          </div>
                          <div>
                            <h3 className="text-xs font-bold text-zinc-900 group-hover:text-amber-600 transition-colors">
                              {item.title}
                            </h3>
                            <p className="text-[10px] text-zinc-400 mt-0.5">{item.desc}</p>
                          </div>
                        </div>
                        <ChevronRight className="w-4 h-4 text-zinc-400 group-hover:text-amber-500 group-hover:translate-x-0.5 transition-all shrink-0 ml-1" />
                      </button>
                    );
                  })}
                </div>
              </div>
            ) : (
              <div className="space-y-3">
                {/* Section Header Bar */}
                <div className="bg-zinc-950 text-white p-2.5 rounded-xl border border-zinc-800 shadow-xs flex items-center justify-between gap-2">
                  <div className="flex items-center space-x-2.5 min-w-0">
                    <button
                      onClick={() => {
                        setSelectedChecklistKey('menu');
                      }}
                      className="p-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white transition-colors cursor-pointer shrink-0 flex items-center space-x-1 text-xs font-bold border border-zinc-800"
                      title="Back to Menu Directory"
                    >
                      <ArrowLeft className="w-3.5 h-3.5 text-amber-400" />
                      <span className="hidden sm:inline">Menu</span>
                    </button>

                    <div className="min-w-0">
                      <div className="text-xs font-extrabold text-amber-400 truncate">
                        {getSectionTitle(selectedChecklistKey)}
                      </div>
                      <div className="text-[10px] text-zinc-400 truncate">
                        Client: <strong className="text-zinc-200">{selectedClient?.name || 'All Clients'}</strong> {selectedClient?.id ? `(${selectedClient.id})` : ''}
                      </div>
                    </div>
                  </div>
                </div>

                {/* ONLY ACTIVE SECTION CONTENT */}
                <div>{renderDetailSectionContent()}</div>
              </div>
            )}
          </div>
        )}

        {/* TAB 4: DISPATCH */}
        {activeTab === 'dispatch' && (
          <div className="p-3.5 space-y-4">
            <DispatchSection
              dispatchItems={dispatchItems}
              clients={clients}
              client={selectedClient}
              showAllClients={showAllClients}
              authToken={authToken}
              showToast={showToast}
              onRefresh={handleRefreshDispatch}
            />
          </div>
        )}
      </div>

      {/* Bottom Mobile Navigation */}
      <BottomNav
        activeTab={activeTab}
        onChangeTab={(tab) => {
          setActiveTab(tab);
        }}
        activeSectionKey={selectedChecklistKey}
        onSelectSection={(secKey) => {
          setSelectedChecklistKey(secKey);
          setActiveTab('checklist');
          const title = MOCK_DETAILS_CHECKLIST.find((i) => i.key === secKey)?.title || secKey;
          showToast(`Opened ${title} Section`);
        }}
      />

      {/* Filter Modal */}
      {isFilterModalOpen && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 z-50">
          <div className="bg-white rounded-t-2xl sm:rounded-2xl max-w-lg w-full p-4 space-y-3 border border-slate-200 shadow-2xl animate-in slide-in-from-bottom duration-200 max-h-[85vh] overflow-y-auto">
            <div className="flex items-center justify-between border-b border-slate-200 pb-2">
              <h3 className="font-bold text-sm text-slate-900 flex items-center space-x-1.5">
                <SlidersHorizontal className="w-4 h-4 text-orange-500" />
                <span>Filter Client Projects</span>
              </h3>
              <button
                onClick={() => setIsFilterModalOpen(false)}
                className="p-1 text-slate-400 hover:text-slate-600 cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <ClientFilterPanel
              filters={filterState}
              onFilterChange={setFilterState}
              onResetFilters={() => setFilterState(INITIAL_FILTERS)}
            />

            <div className="pt-2 flex justify-end">
              <button
                onClick={() => setIsFilterModalOpen(false)}
                className="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md cursor-pointer transition-colors"
              >
                Apply Filters
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modals */}
      <AddQuotationModal
        isOpen={isAddQuotationOpen}
        onClose={() => setIsAddQuotationOpen(false)}
        onAdd={handleAddQuotation}
        clientId={selectedClient?.id || ''}
      />

      <AddPaymentModal
        isOpen={isAddPaymentOpen}
        onClose={() => setIsAddPaymentOpen(false)}
        onAdd={handleAddPayment}
      />

      <AddBOMModal
        isOpen={isAddBOMOpen}
        onClose={() => setIsAddBOMOpen(false)}
        onAdd={handleAddBOM}
        parentId={bomParentTargetId}
      />

      <ClientBoqModal
        isOpen={isBoqModalOpen}
        onClose={() => setIsBoqModalOpen(false)}
        client={boqModalClient}
        token={authToken}
      />
    </MobileFrame>
  );
}
