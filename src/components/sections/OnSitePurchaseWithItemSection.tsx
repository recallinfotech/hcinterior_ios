import React, { useState, useMemo, useEffect, useRef } from 'react';
import {
  OnSitePurchaseItem,
  OnSitePurchaseRequestItem,
  OnSitePurchaseLineItem,
  OnSitePurchasePhoto,
  OnSitePurchaseFormRow,
  ClientProject,
  PurchaseItemCategory,
  PurchaseItemBrand,
} from '../../types';
import {
  fetchOnSitePurchaseListNew,
  createOnSitePurchaseRequestNew,
  acceptRejectPurchaseRequestNew,
  updateParentRequestStatusNew,
  updateOnSitePurchaseDeliveryStatusNew,
  toggleItemMaterialOrderNew,
  updateItemStatusNew,
  uploadOnSitePurchasePhotosNew,
  deleteOnSitePurchasePhotoNew,
  deleteOnSitePurchaseRequestNew,
  fetchPurchaseItemCategories,
  fetchPurchaseItemBrands,
} from '../../services/clientApi';
import { INITIAL_ON_SITE_PURCHASE_REQUESTS_NEW } from '../../mockData';
import {
  Plus,
  Trash2,
  Save,
  CheckCircle2,
  XCircle,
  Clock,
  FileText,
  Camera,
  Download,
  ExternalLink,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  Filter,
  RotateCcw,
  Search,
  X,
  AlertCircle,
  Eye,
  Upload,
  Check,
  Building,
  User,
  Package,
  Layers,
  Sparkles,
} from 'lucide-react';

const CATEGORY_OPTIONS = [
  'Civil',
  'Wooden',
  'Electrical',
  'Plumbing',
  'Paint',
  'Hardware',
  'Fabric',
  'Glass',
  'Sanitary',
  'False Ceiling',
  'Tile',
  'Marble',
  'Wallpaper',
  'Other',
];

const UNIT_OPTIONS = [
  'Pcs',
  'Nos',
  'Rft',
  'Sqft',
  'Kg',
  'Ltr',
  'Box',
  'Set',
  'Packet',
  'Meter',
  'Bundle',
  'Bag',
  'Roll',
  'Sheet',
  'Pair',
  'Other',
];

const COMMON_BRANDS = [
  'Century Ply',
  'Greenply',
  'Havells',
  'Girish',
  'Anchor',
  'Schneider',
  'Asian Paints',
  'Berger',
  'Supreme',
  'Astral',
  'Kohler',
  'Jaquar',
  'Hettich',
  'Ebco',
  'Hafele',
];

const DELIVERY_STATUS_PARTIAL = 'Partial Recived at site';
const DELIVERY_STATUS_COMPLETE = 'Complete Recvied at site';

interface OnSitePurchaseWithItemSectionProps {
  items?: OnSitePurchaseItem[];
  requestItems?: OnSitePurchaseRequestItem[];
  clients?: ClientProject[];
  client?: ClientProject;
  showAllClients?: boolean;
  authToken?: string;
  showToast?: (msg: string) => void;
  onRefresh?: () => Promise<void>;
}

export const OnSitePurchaseWithItemSection: React.FC<OnSitePurchaseWithItemSectionProps> = ({
  items = [],
  requestItems,
  clients = [],
  client,
  showAllClients = true,
  authToken,
  showToast,
  onRefresh,
}) => {
  // Main Request items list
  const [requests, setRequests] = useState<OnSitePurchaseRequestItem[]>(() => {
    if (requestItems && requestItems.length > 0) return requestItems;
    return INITIAL_ON_SITE_PURCHASE_REQUESTS_NEW;
  });

  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [isRefreshing, setIsRefreshing] = useState<boolean>(false);

  // Filter & Data mode
  const [dataMode, setDataMode] = useState<'all' | 'clientWise'>(
    showAllClients ? 'all' : 'clientWise'
  );
  const [selectedClientFilterId, setSelectedClientFilterId] = useState<string>(
    client?.id || ''
  );
  const [statusFilter, setStatusFilter] = useState<string>('All');
  const [searchQuery, setSearchQuery] = useState<string>('');

  // Pagination state
  const [currentPage, setCurrentPage] = useState<number>(1);
  const [itemsPerPage, setItemsPerPage] = useState<number>(10);

  // Dynamic Categories and Brands state from API
  const [categories, setCategories] = useState<PurchaseItemCategory[]>([]);
  const [isLoadingCategories, setIsLoadingCategories] = useState<boolean>(false);
  const [allBrands, setAllBrands] = useState<PurchaseItemBrand[]>([]);
  const [brandsByCategoryMap, setBrandsByCategoryMap] = useState<Record<string, PurchaseItemBrand[]>>({});
  const [isLoadingBrands, setIsLoadingBrands] = useState<boolean>(false);

  // Load dynamic purchase item categories from API
  const loadCategories = async () => {
    setIsLoadingCategories(true);
    try {
      const res = await fetchPurchaseItemCategories(authToken);
      if (res.success && Array.isArray(res.data) && res.data.length > 0) {
        setCategories(res.data);
      }
    } catch (err) {
      console.warn('Failed to load purchase item categories:', err);
    } finally {
      setIsLoadingCategories(false);
    }
  };

  // Load all purchase item brands from API
  const loadAllBrands = async () => {
    setIsLoadingBrands(true);
    try {
      const res = await fetchPurchaseItemBrands(authToken);
      if (res.success && Array.isArray(res.data) && res.data.length > 0) {
        setAllBrands(res.data);
      }
    } catch (err) {
      console.warn('Failed to load purchase item brands:', err);
    } finally {
      setIsLoadingBrands(false);
    }
  };

  // Load brands for a specific category ID or category name
  const loadBrandsForCategory = async (categoryIdOrName: string | number) => {
    if (!categoryIdOrName) return;
    const catKey = String(categoryIdOrName).trim();
    if (brandsByCategoryMap[catKey] && brandsByCategoryMap[catKey].length > 0) {
      return brandsByCategoryMap[catKey];
    }

    let categoryId = catKey;
    const matchedCat = categories.find(
      (c) =>
        String(c.id) === catKey ||
        c.category_name.toLowerCase() === catKey.toLowerCase()
    );
    if (matchedCat && matchedCat.id) {
      categoryId = String(matchedCat.id);
    }

    try {
      const res = await fetchPurchaseItemBrands(authToken, categoryId);
      if (res.success && Array.isArray(res.data)) {
        setBrandsByCategoryMap((prev) => ({
          ...prev,
          [catKey]: res.data,
          ...(matchedCat ? { [matchedCat.category_name]: res.data, [String(matchedCat.id)]: res.data } : {}),
        }));
        return res.data;
      }
    } catch (e) {
      console.warn('Failed to load brands for category', categoryId, e);
    }
    return [];
  };

  // Load categories and brands on mount and when authToken changes
  useEffect(() => {
    loadCategories();
    loadAllBrands();
  }, [authToken]);

  // Derived category list (from API or fallback options)
  const categoryList = useMemo(() => {
    if (categories && categories.length > 0) {
      return categories;
    }
    return CATEGORY_OPTIONS.map((name, idx) => ({
      id: String(idx + 1),
      category_name: name,
    }));
  }, [categories]);

  // Dynamic Brand options helper for a given row's category
  const getRowBrandOptions = (categoryNameOrId: string): string[] => {
    if (categoryNameOrId) {
      // 1. Check cached category-specific brands
      if (brandsByCategoryMap[categoryNameOrId] && brandsByCategoryMap[categoryNameOrId].length > 0) {
        return brandsByCategoryMap[categoryNameOrId].map((b) => b.brand_name).filter(Boolean);
      }

      // 2. Find matching category object
      const matchedCat = categories.find(
        (c) =>
          c.category_name.toLowerCase() === categoryNameOrId.toLowerCase() ||
          String(c.id) === String(categoryNameOrId)
      );

      if (matchedCat) {
        const catId = String(matchedCat.id);
        if (brandsByCategoryMap[catId] && brandsByCategoryMap[catId].length > 0) {
          return brandsByCategoryMap[catId].map((b) => b.brand_name).filter(Boolean);
        }
        if (brandsByCategoryMap[matchedCat.category_name] && brandsByCategoryMap[matchedCat.category_name].length > 0) {
          return brandsByCategoryMap[matchedCat.category_name].map((b) => b.brand_name).filter(Boolean);
        }

        // Filter from allBrands
        const matched = allBrands.filter(
          (b) =>
            b.purchase_item_category_id !== undefined &&
            b.purchase_item_category_id !== null &&
            String(b.purchase_item_category_id) === catId
        );
        if (matched.length > 0) {
          return matched.map((b) => b.brand_name).filter(Boolean);
        }
      }
    }

    // Fallback to all dynamic brands if available
    if (allBrands && allBrands.length > 0) {
      return allBrands.map((b) => b.brand_name).filter(Boolean);
    }

    return COMMON_BRANDS;
  };

  // Add Request Form / Drawer State (Screenshot 1)
  const [isAddFormOpen, setIsAddFormOpen] = useState<boolean>(false);
  const [formClientId, setFormClientId] = useState<string>(() => {
    if (client?.clientIdNum) return String(client.clientIdNum);
    if (client?.id) return client.id.replace(/\D/g, '');
    return '';
  });

  const createInitialRow = (): OnSitePurchaseFormRow => ({
    id: `row-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
    item_category: categories.length > 0 ? categories[0].category_name : 'Civil',
    item: '',
    qty: '',
    unit: 'Pcs',
    brand: '',
    remarks: '',
  });

  const [formRows, setFormRows] = useState<OnSitePurchaseFormRow[]>([createInitialRow()]);
  const [isSubmittingForm, setIsSubmittingForm] = useState<boolean>(false);
  const [formError, setFormError] = useState<string | null>(null);

  // Detail / Line Items Modal State (Screenshot 3)
  const [selectedRequestForModal, setSelectedRequestForModal] =
    useState<OnSitePurchaseRequestItem | null>(null);
  const [isItemModalOpen, setIsItemModalOpen] = useState<boolean>(false);
  const [modalDeliveryStatus, setModalDeliveryStatus] = useState<string>(DELIVERY_STATUS_PARTIAL);
  const [isUpdatingDeliveryStatus, setIsUpdatingDeliveryStatus] = useState<boolean>(false);

  // Check if all items in the selected request are received
  const areAllItemsReceived = useMemo(() => {
    if (!selectedRequestForModal?.items || selectedRequestForModal.items.length === 0) {
      return false;
    }
    return selectedRequestForModal.items.every(
      (it) => (it.status || '').trim().toLowerCase() === 'recieved'
    );
  }, [selectedRequestForModal]);

  // Photo Gallery / Upload Modal State
  const [isPhotosModalOpen, setIsPhotosModalOpen] = useState<boolean>(false);
  const [selectedRequestForPhotos, setSelectedRequestForPhotos] =
    useState<OnSitePurchaseRequestItem | null>(null);
  const [isUploadingPhotos, setIsUploadingPhotos] = useState<boolean>(false);
  const [previewPhotoUrl, setPreviewPhotoUrl] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Reject Prompt Modal State (For Parent Request Rejection)
  const [rejectModalState, setRejectModalState] = useState<{
    isOpen: boolean;
    purchaseId: number | null;
    purchaseNo: string;
    remark: string;
  }>({
    isOpen: false,
    purchaseId: null,
    purchaseNo: '',
    remark: '',
  });

  // Item Status Prompt Modal State (For Item Receive / Reject)
  const [itemStatusModalState, setItemStatusModalState] = useState<{
    isOpen: boolean;
    itemId: number | null;
    itemName: string;
    targetStatus: 'Recieved' | 'Reject';
    remarks: string;
  }>({
    isOpen: false,
    itemId: null,
    itemName: '',
    targetStatus: 'Recieved',
    remarks: '',
  });

  // Delete Confirm Modal State
  const [deleteConfirmState, setDeleteConfirmState] = useState<{
    isOpen: boolean;
    purchaseId: number | null;
    purchaseNo: string;
  }>({
    isOpen: false,
    purchaseId: null,
    purchaseNo: '',
  });

  // Extract unique clients list for filter & creator dropdown
  const uniqueClients = useMemo(() => {
    const map = new Map<string, { id: string; numericId: string; name: string }>();
    if (client) {
      const numId = client.clientIdNum ? String(client.clientIdNum) : client.id.replace(/\D/g, '');
      map.set(client.id, { id: client.id, numericId: numId, name: client.name });
    }
    clients.forEach((c) => {
      if (c.id && !map.has(c.id)) {
        const numId = c.clientIdNum ? String(c.clientIdNum) : c.id.replace(/\D/g, '');
        map.set(c.id, { id: c.id, numericId: numId, name: c.name });
      }
    });
    requests.forEach((r) => {
      const key = r.client_sr_id || (r.client_id ? `HC${r.client_id}` : '');
      if (key && !map.has(key)) {
        const numId = String(r.client_id);
        map.set(key, { id: key, numericId: numId, name: r.client_name || `Client ${numId}` });
      }
    });
    return Array.from(map.values());
  }, [requests, clients, client]);

  // Sync client selection when client prop or clients array changes
  useEffect(() => {
    if (client) {
      setSelectedClientFilterId(client.id);
      const numId = client.clientIdNum ? String(client.clientIdNum) : client.id.replace(/\D/g, '');
      setFormClientId(numId);
    } else if (clients.length > 0 && !selectedClientFilterId) {
      setSelectedClientFilterId(clients[0].id);
      const numId = clients[0].clientIdNum ? String(clients[0].clientIdNum) : clients[0].id.replace(/\D/g, '');
      setFormClientId(numId);
    }
    setDataMode(showAllClients ? 'all' : 'clientWise');
  }, [client, showAllClients, clients]);

  // Currently active client object
  const currentActiveClient = useMemo(() => {
    if (dataMode === 'clientWise') {
      const targetSelection = selectedClientFilterId || client?.id;
      if (targetSelection) {
        return (
          uniqueClients.find(
            (c) => c.id === targetSelection || c.numericId === targetSelection
          ) || (client ? { id: client.id, numericId: String(client.clientIdNum || ''), name: client.name } : null)
        );
      }
    }
    return client ? { id: client.id, numericId: String(client.clientIdNum || ''), name: client.name } : null;
  }, [dataMode, selectedClientFilterId, uniqueClients, client]);

  // Load live data from API with dynamic client_id payload
  const loadRequestsFromApi = async (quiet = false) => {
    if (!quiet) setIsLoading(true);
    try {
      let effectiveClientId: string | number | undefined = undefined;

      if (dataMode === 'clientWise') {
        const targetSelection = selectedClientFilterId || (client?.id ? client.id : '');
        const matched = uniqueClients.find(
          (c) => c.id === targetSelection || c.numericId === targetSelection
        );
        if (matched && matched.numericId) {
          effectiveClientId = matched.numericId;
        } else if (targetSelection) {
          effectiveClientId = targetSelection.replace(/\D/g, '');
        } else if (client?.clientIdNum) {
          effectiveClientId = String(client.clientIdNum);
        }
      }

      console.log('Sending client_id into payload for On Site Purchase list:', effectiveClientId);

      const res = await fetchOnSitePurchaseListNew(
        authToken || '',
        effectiveClientId,
        statusFilter !== 'All' ? statusFilter : undefined,
        1,
        100
      );

      if (res && Array.isArray(res.list)) {
        setRequests(res.list);
      } else if (!requestItems || requestItems.length === 0) {
        if (!authToken) {
          if (effectiveClientId) {
            const num = String(effectiveClientId);
            const mockFiltered = INITIAL_ON_SITE_PURCHASE_REQUESTS_NEW.filter(
              (r) => String(r.client_id) === num || r.client_sr_id === selectedClientFilterId
            );
            setRequests(mockFiltered.length > 0 ? mockFiltered : INITIAL_ON_SITE_PURCHASE_REQUESTS_NEW);
          } else {
            setRequests(INITIAL_ON_SITE_PURCHASE_REQUESTS_NEW);
          }
        } else {
          setRequests([]);
        }
      }
    } catch (err) {
      console.warn('Could not load on site purchase list from API:', err);
    } finally {
      if (!quiet) setIsLoading(false);
      setIsRefreshing(false);
    }
  };

  useEffect(() => {
    loadRequestsFromApi();
  }, [authToken, dataMode, selectedClientFilterId, statusFilter]);

  const handleRefresh = async () => {
    setIsRefreshing(true);
    await Promise.all([
      loadRequestsFromApi(true),
      loadCategories(),
      loadAllBrands(),
    ]);
    if (onRefresh) {
      try {
        await onRefresh();
      } catch (e) {}
    }
    showToast?.('On Site Purchase requests refreshed');
  };

  // Filtered requests list
  const filteredRequests = useMemo(() => {
    const matchedClient = uniqueClients.find(
      (c) => c.id === selectedClientFilterId || c.numericId === selectedClientFilterId
    );
    const selectedNumId = matchedClient?.numericId || selectedClientFilterId.replace(/\D/g, '');

    return requests.filter((req) => {
      // Client filter
      if (dataMode === 'clientWise') {
        const targetNum = selectedNumId || (client?.clientIdNum ? String(client.clientIdNum) : '');
        if (targetNum) {
          const reqNum = String(req.client_id || '');
          const matchesNum = reqNum === targetNum;
          const matchesSr = Boolean(
            req.client_sr_id &&
              (req.client_sr_id === selectedClientFilterId ||
                req.client_sr_id === matchedClient?.id)
          );
          if (!matchesNum && !matchesSr && requests.length > 1) {
            // Note: If API already returned a filtered list, we don't overly restrict unless multiple clients mixed
            return false;
          }
        }
      }

      // Status filter
      if (statusFilter !== 'All') {
        const s = (req.request_status || req.status || '').toLowerCase();
        if (s !== statusFilter.toLowerCase()) return false;
      }

      // Search Query
      if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase().trim();
        const noMatch = req.purchase_no.toLowerCase().includes(q);
        const nameMatch = (req.client_name || '').toLowerCase().includes(q);
        const creatorMatch = (req.creator_name || '').toLowerCase().includes(q);
        const itemMatch = req.items?.some(
          (it) =>
            it.item.toLowerCase().includes(q) ||
            it.item_category.toLowerCase().includes(q) ||
            (it.brand || '').toLowerCase().includes(q)
        );
        if (!noMatch && !nameMatch && !creatorMatch && !itemMatch) return false;
      }

      return true;
    });
  }, [requests, dataMode, selectedClientFilterId, client, statusFilter, searchQuery, uniqueClients]);

  // Pagination calculation
  const totalRecords = filteredRequests.length;
  const totalPages = Math.ceil(totalRecords / itemsPerPage) || 1;
  const safePage = Math.min(Math.max(1, currentPage), totalPages);
  const startIndex = (safePage - 1) * itemsPerPage;
  const endIndex = Math.min(startIndex + itemsPerPage, totalRecords);
  const paginatedRequests = useMemo(() => {
    return filteredRequests.slice(startIndex, endIndex);
  }, [filteredRequests, startIndex, endIndex]);

  // Form row actions
  const handleAddFormRow = () => {
    setFormRows((prev) => [...prev, createInitialRow()]);
  };

  const handleRemoveFormRow = (index: number) => {
    if (formRows.length <= 1) return;
    setFormRows((prev) => prev.filter((_, i) => i !== index));
  };

  const handleUpdateFormRow = (
    index: number,
    field: keyof OnSitePurchaseFormRow,
    value: string
  ) => {
    setFormRows((prev) =>
      prev.map((row, i) => (i === index ? { ...row, [field]: value } : row))
    );

    if (field === 'item_category' && value) {
      loadBrandsForCategory(value);
    }
  };

  // Submit New Purchase Request (Screenshot 1)
  const handleSavePurchaseRequest = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormError(null);

    const targetClientId = formClientId || (client?.clientIdNum ? String(client.clientIdNum) : '');
    if (!targetClientId) {
      setFormError('Please select a client for this purchase request.');
      return;
    }

    for (let i = 0; i < formRows.length; i++) {
      const row = formRows[i];
      if (!row.item_category) {
        setFormError(`Row #${i + 1}: Please select an Item Category.`);
        return;
      }
      if (!row.item.trim()) {
        setFormError(`Row #${i + 1}: Please enter Item Name / Details.`);
        return;
      }
      if (!row.qty || String(row.qty).trim() === '') {
        setFormError(`Row #${i + 1}: Please enter a valid Quantity.`);
        return;
      }
    }

    setIsSubmittingForm(true);
    try {
      const payloadItems = formRows.map((r) => ({
        item_category: r.item_category,
        item: r.item.trim(),
        qty: r.qty,
        unit: r.unit || 'Pcs',
        brand: r.brand.trim() || undefined,
        remarks: r.remarks.trim() || undefined,
      }));

      const res = await createOnSitePurchaseRequestNew(
        authToken || '',
        targetClientId,
        payloadItems
      );

      if (res.success) {
        showToast?.(res.message || 'On site purchase request created successfully!');

        const newPurchaseId = res.purchase_id || Date.now();
        const newPurchaseNo = res.purchase_no || `OSPR-${String(newPurchaseId).padStart(4, '0')}`;
        const matchedClient = uniqueClients.find(
          (c) => c.numericId === targetClientId || c.id === targetClientId
        );

        const newRequestObj: OnSitePurchaseRequestItem = {
          id: newPurchaseId,
          client_id: Number(targetClientId),
          client_name: matchedClient?.name || client?.name || `Client ${targetClientId}`,
          client_sr_id: matchedClient?.id || `HC${targetClientId}`,
          purchase_no: newPurchaseNo,
          request_status: 'Pending',
          status: 'Pending',
          remark: '',
          creator_name: 'Current User',
          created_date: new Date().toISOString().replace('T', ' ').slice(0, 19),
          items_count: payloadItems.length,
          items: payloadItems.map((it, idx) => ({
            id: Date.now() + idx,
            purchase_id: newPurchaseId,
            item_category: it.item_category,
            item: it.item,
            qty: it.qty,
            unit: it.unit,
            brand: it.brand,
            remarks: it.remarks,
            status: 'Pending',
            is_ordered: 0,
          })),
          photos_count: 0,
          photos: [],
          pdf_url: `https://crm.hcinterior.in/admin/client/on_site_purchase_pdf/${newPurchaseId}/0`,
          pdf_download: `https://crm.hcinterior.in/admin/client/on_site_purchase_pdf/${newPurchaseId}/1`,
        };

        setRequests((prev) => [newRequestObj, ...prev]);
        setFormRows([createInitialRow()]);
        setIsAddFormOpen(false);
        loadRequestsFromApi(true);
      } else {
        setFormError(res.message || 'Failed to save purchase request.');
      }
    } catch (err: any) {
      console.error('Error creating purchase request:', err);
      setFormError(err.message || 'Error communicating with server.');
    } finally {
      setIsSubmittingForm(false);
    }
  };

  // Accept Parent Request
  const handleAcceptRequest = async (purchaseId: number) => {
    try {
      const res = await acceptRejectPurchaseRequestNew(authToken || '', purchaseId, 'accept');
      if (res.success) {
        showToast?.('Purchase request accepted successfully');
        setRequests((prev) =>
          prev.map((r) =>
            r.id === purchaseId
              ? { ...r, request_status: 'Accepted', status: 'Accepted' }
              : r
          )
        );
        if (selectedRequestForModal && selectedRequestForModal.id === purchaseId) {
          setSelectedRequestForModal((prev) =>
            prev ? { ...prev, request_status: 'Accepted', status: 'Accepted' } : null
          );
        }
      } else {
        showToast?.(res.message || 'Failed to accept purchase request');
      }
    } catch (e: any) {
      showToast?.(e.message || 'Error accepting request');
    }
  };

  // Open Rejection Dialog
  const handleOpenRejectDialog = (req: OnSitePurchaseRequestItem) => {
    setRejectModalState({
      isOpen: true,
      purchaseId: req.id,
      purchaseNo: req.purchase_no,
      remark: '',
    });
  };

  // Submit Rejection
  const handleSubmitRejection = async () => {
    if (!rejectModalState.purchaseId) return;
    if (!rejectModalState.remark.trim()) {
      showToast?.('Please enter a rejection reason.');
      return;
    }

    try {
      const res = await acceptRejectPurchaseRequestNew(
        authToken || '',
        rejectModalState.purchaseId,
        'reject',
        rejectModalState.remark.trim()
      );
      if (res.success) {
        showToast?.('Purchase request rejected');
        setRequests((prev) =>
          prev.map((r) =>
            r.id === rejectModalState.purchaseId
              ? {
                  ...r,
                  request_status: 'Rejected',
                  status: 'Rejected',
                  remark: rejectModalState.remark.trim(),
                }
              : r
          )
        );
        if (
          selectedRequestForModal &&
          selectedRequestForModal.id === rejectModalState.purchaseId
        ) {
          setSelectedRequestForModal((prev) =>
            prev
              ? {
                  ...prev,
                  request_status: 'Rejected',
                  status: 'Rejected',
                  remark: rejectModalState.remark.trim(),
                }
              : null
          );
        }
        setRejectModalState({ isOpen: false, purchaseId: null, purchaseNo: '', remark: '' });
      } else {
        showToast?.(res.message || 'Failed to reject purchase request');
      }
    } catch (e: any) {
      showToast?.(e.message || 'Error rejecting purchase request');
    }
  };

  // Update Parent Request Status from Dropdown inside Modal
  const handleUpdateParentStatus = async (newStatus: string) => {
    if (!selectedRequestForModal) return;
    try {
      const res = await updateParentRequestStatusNew(
        authToken || '',
        selectedRequestForModal.id,
        newStatus
      );
      if (res.success) {
        showToast?.(`Request status updated to ${newStatus}`);
        const updated = {
          ...selectedRequestForModal,
          request_status: newStatus,
          status: newStatus,
        };
        setSelectedRequestForModal(updated);
        setRequests((prev) =>
          prev.map((r) => (r.id === selectedRequestForModal.id ? updated : r))
        );
      } else {
        showToast?.(res.message || 'Failed to update parent status');
      }
    } catch (e: any) {
      showToast?.(e.message || 'Error updating status');
    }
  };

  // Save Delivery Status from Manual Button at the bottom of Modal
  const handleSaveDeliveryStatus = async () => {
    if (!selectedRequestForModal) return;

    if (modalDeliveryStatus === DELIVERY_STATUS_COMPLETE && !areAllItemsReceived) {
      showToast?.('Cannot set Complete Recvied at site because some items are not yet received.');
      setModalDeliveryStatus(DELIVERY_STATUS_PARTIAL);
      return;
    }

    setIsUpdatingDeliveryStatus(true);
    try {
      const res = await updateOnSitePurchaseDeliveryStatusNew(
        authToken || '',
        selectedRequestForModal.id,
        modalDeliveryStatus
      );
      if (res.success) {
        showToast?.(res.message || `Delivery status updated to ${modalDeliveryStatus}`);
        const updatedDeliveryStatus = res.delivery_status || modalDeliveryStatus;
        const updated = {
          ...selectedRequestForModal,
          delivery_status: updatedDeliveryStatus,
          ...(res.request_status ? { request_status: res.request_status } : {}),
        };
        setSelectedRequestForModal(updated);
        setRequests((prev) =>
          prev.map((r) => (r.id === selectedRequestForModal.id ? updated : r))
        );
        // Automatically close modal on successful delivery status update
        setIsItemModalOpen(false);
      } else {
        showToast?.(res.message || 'Failed to update delivery status');
      }
    } catch (e: any) {
      showToast?.(e.message || 'Error updating delivery status');
    } finally {
      setIsUpdatingDeliveryStatus(false);
    }
  };

  // Toggle Item Material Order Status (Screenshot 3 table - Send button)
  const handleToggleMaterialOrder = async (item: OnSitePurchaseLineItem) => {
    const nextOrdered = item.is_ordered === 1 ? 0 : 1;
    try {
      const res = await toggleItemMaterialOrderNew(authToken || '', item.id, nextOrdered);
      if (res.success) {
        showToast?.(`Item marked as ${nextOrdered === 1 ? 'Ordered / Send' : 'Pending'}`);
        const newStatus = nextOrdered === 1 ? 'Send' : 'Pending';

        const updateItemInRequest = (r: OnSitePurchaseRequestItem) => ({
          ...r,
          items: r.items.map((it) =>
            it.id === item.id ? { ...it, is_ordered: nextOrdered, status: newStatus } : it
          ),
        });

        if (selectedRequestForModal) {
          setSelectedRequestForModal((prev) => (prev ? updateItemInRequest(prev) : null));
        }
        setRequests((prev) =>
          prev.map((r) =>
            r.id === (item.purchase_id || selectedRequestForModal?.id)
              ? updateItemInRequest(r)
              : r
          )
        );
      } else {
        showToast?.(res.message || 'Failed to toggle material order');
      }
    } catch (e: any) {
      showToast?.(e.message || 'Error toggling order');
    }
  };

  // Open Item Receive/Reject Modal
  const handleOpenItemStatusModal = (
    item: OnSitePurchaseLineItem,
    targetStatus: 'Recieved' | 'Reject'
  ) => {
    setItemStatusModalState({
      isOpen: true,
      itemId: item.id,
      itemName: item.item,
      targetStatus,
      remarks: '',
    });
  };

  // Submit Item Receive/Reject Status
  const handleSubmitItemStatus = async () => {
    if (!itemStatusModalState.itemId || !selectedRequestForModal) return;
    try {
      const res = await updateItemStatusNew(
        authToken || '',
        itemStatusModalState.itemId,
        itemStatusModalState.targetStatus,
        itemStatusModalState.remarks.trim()
      );
      if (res.success) {
        showToast?.(`Item marked as ${itemStatusModalState.targetStatus}`);
        const updatedStatus = itemStatusModalState.targetStatus;
        const approverName = res.approver_name || 'Current User';
        const approveDate =
          res.approve_date || new Date().toISOString().replace('T', ' ').slice(0, 19);

        // Calculate updated items array
        const currentItems = selectedRequestForModal.items || [];
        const nextItems = currentItems.map((it) =>
          it.id === itemStatusModalState.itemId
            ? {
                ...it,
                status: updatedStatus,
                approver_name: approverName,
                status_approve_date: approveDate,
                approve_date: approveDate,
                approval_remarks: itemStatusModalState.remarks.trim(),
              }
            : it
        );

        // Auto-calculate Delivery Status:
        // If ALL items are received -> "Completed" ("Complete Received")
        // If some items are received -> "Partial" ("Partial Received")
        let nextDeliveryStatus = selectedRequestForModal.delivery_status || 'Partial';
        const totalItemsCount = nextItems.length;
        const receivedItemsCount = nextItems.filter(
          (it) => (it.status || '').toLowerCase() === 'recieved'
        ).length;

        if (totalItemsCount > 0) {
          if (receivedItemsCount === totalItemsCount) {
            nextDeliveryStatus = 'Completed';
          } else if (receivedItemsCount > 0) {
            nextDeliveryStatus = 'Partial';
          }
        }

        const updateItemInRequest = (r: OnSitePurchaseRequestItem): OnSitePurchaseRequestItem => ({
          ...r,
          items: r.items.map((it) =>
            it.id === itemStatusModalState.itemId
              ? {
                  ...it,
                  status: updatedStatus,
                  approver_name: approverName,
                  status_approve_date: approveDate,
                  approve_date: approveDate,
                  approval_remarks: itemStatusModalState.remarks.trim(),
                }
              : it
          ),
        });

        const updatedReq = updateItemInRequest(selectedRequestForModal);
        setSelectedRequestForModal(updatedReq);
        setRequests((prev) =>
          prev.map((r) =>
            r.id === selectedRequestForModal.id ? updateItemInRequest(r) : r
          )
        );

        // Adjust modal delivery status selection based on whether all items are now received
        const allRec =
          nextItems.length > 0 &&
          nextItems.every((it) => (it.status || '').trim().toLowerCase() === 'recieved');

        if (allRec) {
          setModalDeliveryStatus(DELIVERY_STATUS_COMPLETE);
        } else {
          setModalDeliveryStatus(DELIVERY_STATUS_PARTIAL);
        }

        setItemStatusModalState({
          isOpen: false,
          itemId: null,
          itemName: '',
          targetStatus: 'Recieved',
          remarks: '',
        });
      } else {
        showToast?.(res.message || 'Failed to update item status');
      }
    } catch (e: any) {
      showToast?.(e.message || 'Error updating item status');
    }
  };

  // Upload Site Photos
  const handleUploadPhotos = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;

    const targetReq = selectedRequestForModal || selectedRequestForPhotos;
    if (!targetReq) return;

    // Validation: Photos can only be added after item list is added
    const hasItems =
      (targetReq.items && targetReq.items.length > 0) ||
      (targetReq.items_count && targetReq.items_count > 0);
    if (!hasItems) {
      showToast?.('Please add items to the item list before uploading photos.');
      if (fileInputRef.current) fileInputRef.current.value = '';
      return;
    }

    setIsUploadingPhotos(true);
    try {
      const res = await uploadOnSitePurchasePhotosNew(authToken || '', targetReq.id, files);
      if (res.success && res.photos) {
        showToast?.(res.message || 'Photos uploaded successfully');
        const updatedPhotos = [...targetReq.photos, ...res.photos];

        const updatedReq: OnSitePurchaseRequestItem = {
          ...targetReq,
          photos: updatedPhotos,
          photos_count: updatedPhotos.length,
        };

        if (selectedRequestForModal) setSelectedRequestForModal(updatedReq);
        if (selectedRequestForPhotos) setSelectedRequestForPhotos(updatedReq);
        setRequests((prev) => prev.map((r) => (r.id === targetReq.id ? updatedReq : r)));
      } else {
        showToast?.(res.message || 'Failed to upload photos');
      }
    } catch (err: any) {
      showToast?.(err.message || 'Error uploading photos');
    } finally {
      setIsUploadingPhotos(false);
      if (fileInputRef.current) fileInputRef.current.value = '';
    }
  };

  // Delete Site Photo
  const handleDeletePhoto = async (photo: OnSitePurchasePhoto) => {
    const targetReq = selectedRequestForModal || selectedRequestForPhotos;
    if (!targetReq) return;

    try {
      const res = await deleteOnSitePurchasePhotoNew(
        authToken || '',
        targetReq.id,
        photo.fileName
      );
      if (res.success) {
        showToast?.('Photo deleted successfully');
        const updatedPhotos = targetReq.photos.filter((p) => p.fileName !== photo.fileName);
        const updatedReq: OnSitePurchaseRequestItem = {
          ...targetReq,
          photos: updatedPhotos,
          photos_count: updatedPhotos.length,
        };

        if (selectedRequestForModal) setSelectedRequestForModal(updatedReq);
        if (selectedRequestForPhotos) setSelectedRequestForPhotos(updatedReq);
        setRequests((prev) => prev.map((r) => (r.id === targetReq.id ? updatedReq : r)));
      } else {
        showToast?.(res.message || 'Failed to delete photo');
      }
    } catch (err: any) {
      showToast?.(err.message || 'Error deleting photo');
    }
  };

  // Delete Purchase Request
  const handleConfirmDeleteRequest = async () => {
    if (!deleteConfirmState.purchaseId) return;
    try {
      const res = await deleteOnSitePurchaseRequestNew(
        authToken || '',
        deleteConfirmState.purchaseId
      );
      if (res.success) {
        showToast?.('Purchase request deleted');
        setRequests((prev) => prev.filter((r) => r.id !== deleteConfirmState.purchaseId));
        setDeleteConfirmState({ isOpen: false, purchaseId: null, purchaseNo: '' });
      } else {
        showToast?.(res.message || 'Failed to delete purchase request');
      }
    } catch (e: any) {
      showToast?.(e.message || 'Error deleting purchase request');
    }
  };

  // Open Item List Modal (Screenshot 3)
  const handleOpenItemListModal = (req: OnSitePurchaseRequestItem) => {
    setSelectedRequestForModal(req);
    setIsItemModalOpen(true);
    const allRec =
      Boolean(req.items &&
      req.items.length > 0 &&
      req.items.every((it) => (it.status || '').trim().toLowerCase() === 'recieved'));

    const currentDelStatus = (req.delivery_status || '').toLowerCase();
    if (allRec && (currentDelStatus.includes('complete') || currentDelStatus === 'completed')) {
      setModalDeliveryStatus(DELIVERY_STATUS_COMPLETE);
    } else {
      setModalDeliveryStatus(DELIVERY_STATUS_PARTIAL);
    }
  };

  // Open Photos Modal (View Only)
  const handleOpenPhotosModal = (req: OnSitePurchaseRequestItem) => {
    setSelectedRequestForPhotos(req);
    setIsPhotosModalOpen(true);
  };

  // Status badge styling helper
  const getStatusBadge = (status: string) => {
    const s = (status || 'Pending').toLowerCase();
    if (s === 'accepted') {
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
          Accepted
        </span>
      );
    }
    if (s === 'completed') {
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-teal-100 text-teal-800 border border-teal-300">
          Completed
        </span>
      );
    }
    if (s === 'partial') {
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-300">
          Partial
        </span>
      );
    }
    if (s === 'rejected' || s === 'reject') {
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
          Rejected
        </span>
      );
    }
    if (s === 'send' || s === 'ordered') {
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-300">
          Send
        </span>
      );
    }
    if (s === 'approved') {
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
          Approved
        </span>
      );
    }
    if (s === 'partially received') {
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-300">
          Partially Received
        </span>
      );
    }
    if (s === 'recieved') {
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-green-100 text-green-800 border border-green-300">
          Recieved
        </span>
      );
    }
    return (
      <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
        Pending
      </span>
    );
  };

  // Delivery Status badge styling helper
  const getDeliveryStatusBadge = (deliveryStatus?: string) => {
    if (!deliveryStatus) return null;
    const s = deliveryStatus.toLowerCase();
    if (s.includes('complete')) {
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-teal-100 text-teal-800 border border-teal-300">
          Complete Recvied at site
        </span>
      );
    }
    if (s.includes('partial')) {
      return (
        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
          Partial Recived at site
        </span>
      );
    }
    return (
      <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-300">
        {deliveryStatus}
      </span>
    );
  };

  return (
    <div className="space-y-4 text-slate-800">
      {/* 1. SECTION CARD HEADER */}
      <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 gap-3 border-b-2 border-orange-500 bg-white">
          <div className="flex items-center space-x-2.5">
            <div className="w-9 h-9 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center font-bold">
              <Package className="w-5 h-5" />
            </div>
            <div>
              <div className="flex items-center space-x-2">
                <h2 className="text-base font-bold text-slate-900 leading-none">
                  On Site Purchase Request
                </h2>
              </div>
              <p className="text-xs text-slate-500 mt-1">
                {currentActiveClient
                  ? `Client: ${currentActiveClient.name} (${currentActiveClient.id})`
                  : 'Manage on-site itemized purchase requests & receiving logs'}
              </p>
            </div>
          </div>

          <div className="flex items-center space-x-2 w-full sm:w-auto justify-end">
            <button
              onClick={handleRefresh}
              disabled={isRefreshing}
              className="p-2 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer border border-slate-200 text-xs flex items-center space-x-1"
              title="Refresh requests"
            >
              <RotateCcw className={`w-3.5 h-3.5 ${isRefreshing ? 'animate-spin text-orange-500' : ''}`} />
              <span className="hidden md:inline">Sync</span>
            </button>

            <button
              onClick={() => setIsAddFormOpen(!isAddFormOpen)}
              className="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow-xs flex items-center space-x-1.5 transition-colors cursor-pointer"
            >
              {isAddFormOpen ? (
                <>
                  <X className="w-4 h-4" />
                  <span>Close Form</span>
                </>
              ) : (
                <>
                  <Plus className="w-4 h-4" />
                  <span>+ Add Purchase Request</span>
                </>
              )}
            </button>
          </div>
        </div>

        {/* 2. ADD PURCHASE REQUEST INLINE FORM (Screenshot 1) */}
        {isAddFormOpen && (
          <div className="p-4 bg-slate-50 border-b border-slate-200 animate-in fade-in slide-in-from-top-2 duration-200">
            <form onSubmit={handleSavePurchaseRequest} className="space-y-4">
              <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 pb-2 border-b border-slate-200">
                <div className="flex items-center space-x-2">
                  <Sparkles className="w-4 h-4 text-blue-600" />
                  <h3 className="font-bold text-sm text-slate-900">
                    Create New On-Site Purchase Request
                  </h3>
                </div>

                <div className="flex items-center space-x-2 w-full md:w-auto">
                  <label className="text-xs font-semibold text-slate-600 shrink-0">
                    Select Client <span className="text-rose-500">*</span>:
                  </label>
                  <select
                    value={formClientId}
                    onChange={(e) => setFormClientId(e.target.value)}
                    className="bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500 shadow-2xs w-full md:w-64"
                    required
                  >
                    <option value="">-- Choose Client --</option>
                    {uniqueClients.map((c) => (
                      <option key={c.numericId || c.id} value={c.numericId}>
                        {c.name} ({c.id})
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              {formError && (
                <div className="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-xs flex items-center space-x-2">
                  <AlertCircle className="w-4 h-4 text-rose-600 shrink-0" />
                  <span>{formError}</span>
                </div>
              )}

              {/* Desktop Items Table (hidden on mobile, visible on md+) */}
              <div className="hidden md:block overflow-x-auto rounded-lg border border-slate-300 shadow-2xs bg-white">
                <table className="w-full text-xs text-left border-collapse">
                  <thead>
                    <tr className="bg-blue-600 text-white font-bold text-[11px] uppercase tracking-wider">
                      <th className="p-2.5 border-r border-blue-500 w-44">
                        Item Category <span className="text-amber-300">*</span>
                      </th>
                      <th className="p-2.5 border-r border-blue-500 min-w-44">
                        Item <span className="text-amber-300">*</span>
                      </th>
                      <th className="p-2.5 border-r border-blue-500 w-24">
                        Qty <span className="text-amber-300">*</span>
                      </th>
                      <th className="p-2.5 border-r border-blue-500 w-28">Unit</th>
                      <th className="p-2.5 border-r border-blue-500 w-40">Brand</th>
                      <th className="p-2.5 border-r border-blue-500 min-w-36">Remarks</th>
                      <th className="p-2.5 text-center w-16">Action</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-200">
                    {formRows.map((row, idx) => (
                      <tr key={row.id} className="hover:bg-blue-50/40 transition-colors">
                        <td className="p-2 border-r border-slate-200">
                          <select
                            value={row.item_category}
                            onChange={(e) =>
                              handleUpdateFormRow(idx, 'item_category', e.target.value)
                            }
                            className="w-full bg-white border border-slate-300 rounded px-2 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500"
                            required
                          >
                            <option value="">Select Category</option>
                            {categoryList.map((cat) => (
                              <option key={cat.id || cat.category_name} value={cat.category_name}>
                                {cat.category_name}
                              </option>
                            ))}
                          </select>
                        </td>

                        <td className="p-2 border-r border-slate-200">
                          <input
                            type="text"
                            value={row.item}
                            onChange={(e) =>
                              handleUpdateFormRow(idx, 'item', e.target.value)
                            }
                            placeholder="Item Name / Details"
                            className="w-full bg-white border border-slate-300 rounded px-2 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500 placeholder:text-slate-400 font-medium"
                            required
                          />
                        </td>

                        <td className="p-2 border-r border-slate-200">
                          <input
                            type="number"
                            step="any"
                            min="0.1"
                            value={row.qty}
                            onChange={(e) =>
                              handleUpdateFormRow(idx, 'qty', e.target.value)
                            }
                            placeholder="Qty"
                            className="w-full bg-white border border-slate-300 rounded px-2 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500 placeholder:text-slate-400 font-semibold"
                            required
                          />
                        </td>

                        <td className="p-2 border-r border-slate-200">
                          <select
                            value={row.unit}
                            onChange={(e) =>
                              handleUpdateFormRow(idx, 'unit', e.target.value)
                            }
                            className="w-full bg-white border border-slate-300 rounded px-2 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500 font-medium"
                          >
                            {UNIT_OPTIONS.map((u) => (
                              <option key={u} value={u}>
                                {u}
                              </option>
                            ))}
                          </select>
                        </td>

                        <td className="p-2 border-r border-slate-200">
                          <div className="relative">
                            <input
                              type="text"
                              list={`brand-list-desktop-${idx}`}
                              value={row.brand}
                              onChange={(e) =>
                                handleUpdateFormRow(idx, 'brand', e.target.value)
                              }
                              placeholder="Select / Enter Brand"
                              className="w-full bg-white border border-slate-300 rounded px-2 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500 placeholder:text-slate-400 font-medium"
                            />
                            <datalist id={`brand-list-desktop-${idx}`}>
                              {getRowBrandOptions(row.item_category).map((bName, bIdx) => (
                                <option key={`${bIdx}-${bName}`} value={bName} />
                              ))}
                            </datalist>
                          </div>
                        </td>

                        <td className="p-2 border-r border-slate-200">
                          <input
                            type="text"
                            value={row.remarks}
                            onChange={(e) =>
                              handleUpdateFormRow(idx, 'remarks', e.target.value)
                            }
                            placeholder="Remarks / Note"
                            className="w-full bg-white border border-slate-300 rounded px-2 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500 placeholder:text-slate-400 font-normal"
                          />
                        </td>

                        <td className="p-2 text-center">
                          <button
                            type="button"
                            onClick={() => handleRemoveFormRow(idx)}
                            disabled={formRows.length <= 1}
                            className="p-1.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:hover:bg-rose-600 text-white rounded transition-colors cursor-pointer"
                            title="Delete Row"
                          >
                            <X className="w-3.5 h-3.5" />
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {/* Mobile Items Cards View (visible on mobile, hidden on md+) */}
              <div className="md:hidden space-y-3.5">
                {formRows.map((row, idx) => (
                  <div
                    key={row.id}
                    className="bg-white rounded-xl border border-slate-300 p-3.5 space-y-3 shadow-2xs relative animate-in fade-in duration-150"
                  >
                    {/* Row Header */}
                    <div className="flex items-center justify-between pb-2 border-b border-slate-200">
                      <div className="flex items-center space-x-2">
                        <span className="w-6 h-6 rounded-md bg-blue-600 text-white font-extrabold text-xs flex items-center justify-center">
                          #{idx + 1}
                        </span>
                        <span className="font-extrabold text-xs text-slate-900 uppercase tracking-wider">
                          Item #{idx + 1} Details
                        </span>
                      </div>

                      {formRows.length > 1 && (
                        <button
                          type="button"
                          onClick={() => handleRemoveFormRow(idx)}
                          className="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-md text-xs font-bold flex items-center space-x-1 cursor-pointer transition-colors"
                        >
                          <Trash2 className="w-3 h-3" />
                          <span>Remove</span>
                        </button>
                      )}
                    </div>

                    {/* Item Category */}
                    <div className="space-y-1">
                      <label className="text-xs font-bold text-slate-700 block">
                        Item Category <span className="text-rose-500">*</span>
                      </label>
                      <select
                        value={row.item_category}
                        onChange={(e) =>
                          handleUpdateFormRow(idx, 'item_category', e.target.value)
                        }
                        className="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500"
                        required
                      >
                        <option value="">Select Category</option>
                        {categoryList.map((cat) => (
                          <option key={cat.id || cat.category_name} value={cat.category_name}>
                            {cat.category_name}
                          </option>
                        ))}
                      </select>
                    </div>

                    {/* Item Name / Details */}
                    <div className="space-y-1">
                      <label className="text-xs font-bold text-slate-700 block">
                        Item Name / Description <span className="text-rose-500">*</span>
                      </label>
                      <input
                        type="text"
                        value={row.item}
                        onChange={(e) =>
                          handleUpdateFormRow(idx, 'item', e.target.value)
                        }
                        placeholder="e.g. 50kg UltraTech Cement / 1.5mm Wire"
                        className="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 placeholder:text-slate-400"
                        required
                      />
                    </div>

                    {/* Qty & Unit */}
                    <div className="grid grid-cols-2 gap-2.5">
                      <div className="space-y-1">
                        <label className="text-xs font-bold text-slate-700 block">
                          Quantity <span className="text-rose-500">*</span>
                        </label>
                        <input
                          type="number"
                          step="any"
                          min="0.1"
                          value={row.qty}
                          onChange={(e) =>
                            handleUpdateFormRow(idx, 'qty', e.target.value)
                          }
                          placeholder="Qty"
                          className="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 placeholder:text-slate-400"
                          required
                        />
                      </div>

                      <div className="space-y-1">
                        <label className="text-xs font-bold text-slate-700 block">
                          Unit
                        </label>
                        <select
                          value={row.unit}
                          onChange={(e) =>
                            handleUpdateFormRow(idx, 'unit', e.target.value)
                          }
                          className="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500"
                        >
                          {UNIT_OPTIONS.map((u) => (
                            <option key={u} value={u}>
                              {u}
                            </option>
                          ))}
                        </select>
                      </div>
                    </div>

                    {/* Brand & Remarks */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                      <div className="space-y-1">
                        <label className="text-xs font-bold text-slate-700 block">
                          Brand
                        </label>
                        <input
                          type="text"
                          list={`brand-list-mobile-${idx}`}
                          value={row.brand}
                          onChange={(e) =>
                            handleUpdateFormRow(idx, 'brand', e.target.value)
                          }
                          placeholder="Select / Enter Brand"
                          className="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 placeholder:text-slate-400"
                        />
                        <datalist id={`brand-list-mobile-${idx}`}>
                          {getRowBrandOptions(row.item_category).map((bName, bIdx) => (
                            <option key={`${bIdx}-${bName}`} value={bName} />
                          ))}
                        </datalist>
                      </div>

                      <div className="space-y-1">
                        <label className="text-xs font-bold text-slate-700 block">
                          Remarks / Note
                        </label>
                        <input
                          type="text"
                          value={row.remarks}
                          onChange={(e) =>
                            handleUpdateFormRow(idx, 'remarks', e.target.value)
                          }
                          placeholder="Optional Remarks"
                          className="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-normal text-slate-800 focus:bg-white focus:outline-none focus:border-blue-500 placeholder:text-slate-400"
                        />
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              {/* Form Actions (Add Row + Cancel + Save Purchase Request) */}
              <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2">
                <button
                  type="button"
                  onClick={handleAddFormRow}
                  className="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs rounded-lg shadow-2xs flex items-center justify-center space-x-1.5 transition-colors cursor-pointer"
                >
                  <Plus className="w-4 h-4" />
                  <span>+ Add Row</span>
                </button>

                <div className="flex items-center space-x-2">
                  <button
                    type="button"
                    onClick={() => setIsAddFormOpen(false)}
                    className="flex-1 sm:flex-initial px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-lg transition-colors cursor-pointer"
                  >
                    Cancel
                  </button>

                  <button
                    type="submit"
                    disabled={isSubmittingForm}
                    className="flex-1 sm:flex-initial px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold text-xs rounded-lg shadow-xs flex items-center justify-center space-x-1.5 transition-colors cursor-pointer disabled:opacity-50"
                  >
                    {isSubmittingForm ? (
                      <>
                        <span className="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                        <span>Saving...</span>
                      </>
                    ) : (
                      <>
                        <Save className="w-4 h-4" />
                        <span>Save Purchase Request</span>
                      </>
                    )}
                  </button>
                </div>
              </div>
            </form>
          </div>
        )}

        {/* 3. FILTER & SEARCH CONTROLS */}
        <div className="p-3.5 bg-slate-50/70 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
          <div className="flex flex-wrap items-center gap-2.5">
            {clients.length > 0 && (
              <div className="flex items-center space-x-1 bg-white p-1 rounded-lg border border-slate-200 shadow-2xs">
                <button
                  onClick={() => setDataMode('all')}
                  className={`px-2.5 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer ${
                    dataMode === 'all'
                      ? 'bg-orange-500 text-white shadow-2xs'
                      : 'text-slate-600 hover:bg-slate-100'
                  }`}
                >
                  All Clients
                </button>
                <button
                  onClick={() => {
                    setDataMode('clientWise');
                    if (!selectedClientFilterId && uniqueClients.length > 0) {
                      const firstId = client?.id || uniqueClients[0].id;
                      setSelectedClientFilterId(firstId);
                      const matched = uniqueClients.find(
                        (c) => c.id === firstId || c.numericId === firstId
                      );
                      if (matched?.numericId) {
                        setFormClientId(matched.numericId);
                      }
                    }
                    setCurrentPage(1);
                  }}
                  className={`px-2.5 py-1 rounded-md text-[11px] font-bold transition-all cursor-pointer ${
                    dataMode === 'clientWise'
                      ? 'bg-orange-500 text-white shadow-2xs'
                      : 'text-slate-600 hover:bg-slate-100'
                  }`}
                >
                  Client-Wise
                </button>
              </div>
            )}

            {dataMode === 'clientWise' && (
              <select
                value={selectedClientFilterId}
                onChange={(e) => {
                  const newId = e.target.value;
                  setSelectedClientFilterId(newId);
                  const matched = uniqueClients.find(
                    (c) => c.id === newId || c.numericId === newId
                  );
                  if (matched?.numericId) {
                    setFormClientId(matched.numericId);
                  }
                  setCurrentPage(1);
                }}
                className="bg-white border border-slate-300 font-semibold text-slate-800 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 shadow-2xs max-w-xs"
              >
                {uniqueClients.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.name} ({c.id})
                  </option>
                ))}
              </select>
            )}

            <div className="flex items-center space-x-1.5">
              <span className="text-slate-500 font-medium">Status:</span>
              <select
                value={statusFilter}
                onChange={(e) => {
                  setStatusFilter(e.target.value);
                  setCurrentPage(1);
                }}
                className="bg-white border border-slate-300 font-semibold text-slate-800 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 shadow-2xs"
              >
                <option value="All">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Accepted">Accepted</option>
                <option value="Completed">Completed</option>
                <option value="Partial">Partial</option>
                <option value="Rejected">Rejected</option>
              </select>
            </div>
          </div>

          <div className="relative w-full sm:w-64">
            <Search className="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => {
                setSearchQuery(e.target.value);
                setCurrentPage(1);
              }}
              placeholder="Search Request No, Client, Item..."
              className="w-full bg-white border border-slate-300 pl-8 pr-3 py-1.5 rounded-lg text-xs text-slate-800 focus:outline-none focus:border-orange-500 shadow-2xs font-medium placeholder:text-slate-400"
            />
          </div>
        </div>

        {/* 4. PURCHASE REQUESTS CARD VIEW (Mobile-Optimized) */}
        <div className="p-3.5 bg-slate-50/50">
          {isLoading ? (
            <div className="p-12 text-center space-y-3 bg-white rounded-xl border border-slate-200">
              <div className="w-8 h-8 border-2 border-orange-500/20 border-t-orange-500 rounded-full animate-spin mx-auto" />
              <p className="text-xs font-semibold text-slate-500">Loading purchase requests...</p>
            </div>
          ) : filteredRequests.length === 0 ? (
            <div className="p-12 text-center space-y-2 bg-white rounded-xl border border-slate-200 shadow-2xs">
              <Package className="w-10 h-10 text-slate-300 mx-auto" />
              <p className="text-xs font-semibold text-slate-500">
                No purchase requests found for this client.
              </p>
              <button
                onClick={() => setIsAddFormOpen(true)}
                className="mt-2 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow-2xs transition-colors cursor-pointer"
              >
                + Add Purchase Request Now
              </button>
            </div>
          ) : (
            <div className="space-y-3.5">
              {paginatedRequests.map((req, idx) => {
                const s = (req.request_status || req.status || '').toLowerCase();
                const isPending = s === 'pending' || s === '';

                return (
                  <div
                    key={req.id}
                    className="bg-white rounded-xl border border-slate-200 hover:border-orange-300 hover:shadow-xs transition-all p-3.5 sm:p-4 space-y-3 text-xs text-slate-800 shadow-2xs"
                  >
                    {/* 1. Card Top Bar: Index + Request No + Total Items + Status Badge */}
                    <div className="flex items-start justify-between gap-2">
                      <div className="flex items-center space-x-2 flex-wrap gap-y-1">
                        <span className="w-6 h-6 rounded-md bg-slate-100 text-slate-600 font-extrabold text-[11px] flex items-center justify-center border border-slate-200 shrink-0">
                          #{startIndex + idx + 1}
                        </span>

                        <button
                          type="button"
                          onClick={() => handleOpenItemListModal(req)}
                          className="font-extrabold text-blue-600 hover:text-blue-800 hover:underline text-sm sm:text-base flex items-center space-x-1 cursor-pointer"
                          title="Click to view line items"
                        >
                          <span>{req.purchase_no}</span>
                        </button>

                        <span className="inline-flex items-center px-2 py-0.5 rounded-md bg-cyan-600 text-white font-bold text-[11px] shadow-2xs">
                          {req.items_count || req.items?.length || 0} Items
                        </span>
                      </div>

                      {/* Status Badge */}
                      <div className="shrink-0 flex items-center space-x-1.5 flex-wrap justify-end gap-1">
                        {getStatusBadge(req.request_status || req.status || 'Pending')}
                        {req.delivery_status && getDeliveryStatusBadge(req.delivery_status)}
                      </div>
                    </div>

                    {/* 2. Client Info (if 'all' mode or multiple clients) */}
                    {(dataMode === 'all' || !client) && (
                      <div className="bg-slate-50 rounded-lg p-2 px-2.5 border border-slate-200/80 flex items-center justify-between text-xs">
                        <div className="flex items-center space-x-2 min-w-0">
                          <Building className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                          <span className="font-bold text-slate-900 truncate">
                            {req.client_name || `Client #${req.client_id}`}
                          </span>
                        </div>
                        <span className="text-[11px] font-mono font-semibold text-slate-500 shrink-0 ml-2">
                          {req.client_sr_id || `HC${req.client_id}`}
                        </span>
                      </div>
                    )}

                    {/* 3. Key Meta Grid: Requested By & Date */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-1 border-t border-slate-100">
                      <div className="flex items-center space-x-2">
                        <div className="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                          <User className="w-3.5 h-3.5" />
                        </div>
                        <div>
                          <span className="text-[10px] text-slate-400 font-semibold block uppercase tracking-wider">
                            Requested By
                          </span>
                          <span className="font-bold text-slate-800">{req.creator_name || 'Staff'}</span>
                        </div>
                      </div>

                      <div className="flex items-center space-x-2">
                        <div className="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                          <Clock className="w-3.5 h-3.5" />
                        </div>
                        <div>
                          <span className="text-[10px] text-slate-400 font-semibold block uppercase tracking-wider">
                            Date &amp; Time
                          </span>
                          <span className="font-semibold text-slate-700">{req.created_date || 'N/A'}</span>
                        </div>
                      </div>
                    </div>

                    {/* 4. Items Summary Preview Chips */}
                    {req.items && req.items.length > 0 && (
                      <div className="bg-slate-50/90 rounded-lg p-2.5 border border-slate-200/90 space-y-1.5">
                        <div className="flex items-center justify-between text-[11px]">
                          <span className="font-bold text-slate-600 uppercase tracking-wider flex items-center space-x-1">
                            <Package className="w-3 h-3 text-orange-500" />
                            <span>Included Items ({req.items.length})</span>
                          </span>
                          <button
                            type="button"
                            onClick={() => handleOpenItemListModal(req)}
                            className="text-blue-600 hover:text-blue-800 font-bold hover:underline cursor-pointer"
                          >
                            View All &rarr;
                          </button>
                        </div>
                        <div className="flex flex-wrap gap-1.5">
                          {req.items.slice(0, 4).map((it, itemIdx) => (
                            <span
                              key={it.id || itemIdx}
                              className="inline-flex items-center space-x-1 px-2 py-0.5 rounded bg-white border border-slate-200 text-[11px] text-slate-700 shadow-2xs"
                            >
                              <span className="font-bold text-slate-900">{it.item}</span>
                              <span className="text-cyan-700 font-bold">
                                ({it.qty} {it.unit || ''})
                              </span>
                              {it.brand ? <span className="text-slate-400">· {it.brand}</span> : null}
                            </span>
                          ))}
                          {req.items.length > 4 && (
                            <span className="inline-flex items-center px-2 py-0.5 rounded bg-slate-200 text-[11px] font-bold text-slate-600">
                              +{req.items.length - 4} more
                            </span>
                          )}
                        </div>
                      </div>
                    )}

                    {/* 5. Remarks / Note */}
                    {req.remark && (
                      <div className="p-2 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-xs flex items-start space-x-1.5">
                        <AlertCircle className="w-3.5 h-3.5 text-rose-600 shrink-0 mt-0.5" />
                        <span className="italic">
                          <strong>Rejection Note:</strong> {req.remark}
                        </span>
                      </div>
                    )}

                    {/* 6. Card Action Bar (Formatted for Mobile) */}
                    <div className="pt-2 border-t border-slate-200 flex flex-wrap items-center justify-end gap-2">
                      {/* Accept / Reject actions temporarily hidden - will be handled by another user via CRM web */}

                      {/* Utility actions: Item List, Photos, View PDF, Delete */}
                      <div className="flex flex-wrap items-center gap-1.5 w-full sm:w-auto justify-end">
                        <button
                          type="button"
                          onClick={() => handleOpenItemListModal(req)}
                          className="flex-1 sm:flex-initial px-3 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs rounded-lg shadow-2xs flex items-center justify-center space-x-1 cursor-pointer transition-colors"
                        >
                          <FileText className="w-3.5 h-3.5" />
                          <span>Item List</span>
                        </button>

                        <button
                          type="button"
                          onClick={() => handleOpenPhotosModal(req)}
                          className="flex-1 sm:flex-initial px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-300 font-bold text-xs rounded-lg shadow-2xs flex items-center justify-center space-x-1 cursor-pointer transition-colors"
                          title="View Photos"
                        >
                          <Camera className="w-3.5 h-3.5 text-blue-600" />
                          <span>Photos ({req.photos_count || req.photos?.length || 0})</span>
                        </button>

                        {req.pdf_url ? (
                          <a
                            href={req.pdf_url}
                            target="_blank"
                            rel="noreferrer"
                            className="flex-1 sm:flex-initial px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs rounded-lg shadow-2xs flex items-center justify-center space-x-1 transition-colors"
                          >
                            <Download className="w-3.5 h-3.5" />
                            <span>View PDF</span>
                          </a>
                        ) : (
                          <button
                            disabled
                            className="flex-1 sm:flex-initial px-3 py-1.5 bg-slate-200 text-slate-400 font-bold text-xs rounded-lg opacity-60 cursor-not-allowed flex items-center justify-center space-x-1"
                          >
                            <Download className="w-3.5 h-3.5" />
                            <span>View PDF</span>
                          </button>
                        )}

                        <button
                          type="button"
                          onClick={() =>
                            setDeleteConfirmState({
                              isOpen: true,
                              purchaseId: req.id,
                              purchaseNo: req.purchase_no,
                            })
                          }
                          className="p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg shadow-2xs transition-colors cursor-pointer shrink-0"
                          title="Delete Request"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>

        {/* 5. PAGINATION CONTROLS */}
        {totalRecords > 0 && (
          <div className="bg-slate-50 border-t border-slate-200 p-3 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
            <div className="flex items-center space-x-2">
              <span>
                Showing <strong>{startIndex + 1}</strong> to <strong>{endIndex}</strong> of{' '}
                <strong>{totalRecords}</strong> requests
              </span>
              <div className="flex items-center space-x-1 pl-2 border-l border-slate-200">
                <span className="text-[11px] text-slate-400">Rows:</span>
                <select
                  value={itemsPerPage}
                  onChange={(e) => {
                    setItemsPerPage(Number(e.target.value));
                    setCurrentPage(1);
                  }}
                  className="bg-white border border-slate-300 rounded px-1.5 py-0.5 text-xs font-semibold focus:outline-none"
                >
                  <option value={5}>5</option>
                  <option value={10}>10</option>
                  <option value={20}>20</option>
                  <option value={50}>50</option>
                </select>
              </div>
            </div>

            <div className="flex items-center space-x-1">
              <button
                onClick={() => setCurrentPage(1)}
                disabled={safePage <= 1}
                className="p-1 rounded bg-white border border-slate-300 text-slate-600 disabled:opacity-40 hover:bg-slate-100 cursor-pointer"
              >
                <ChevronsLeft className="w-3.5 h-3.5" />
              </button>
              <button
                onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                disabled={safePage <= 1}
                className="p-1 rounded bg-white border border-slate-300 text-slate-600 disabled:opacity-40 hover:bg-slate-100 cursor-pointer"
              >
                <ChevronLeft className="w-3.5 h-3.5" />
              </button>
              <span className="px-2.5 py-1 bg-white border border-slate-300 font-bold text-slate-800 rounded">
                {safePage} / {totalPages}
              </span>
              <button
                onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
                disabled={safePage >= totalPages}
                className="p-1 rounded bg-white border border-slate-300 text-slate-600 disabled:opacity-40 hover:bg-slate-100 cursor-pointer"
              >
                <ChevronRight className="w-3.5 h-3.5" />
              </button>
              <button
                onClick={() => setCurrentPage(totalPages)}
                disabled={safePage >= totalPages}
                className="p-1 rounded bg-white border border-slate-300 text-slate-600 disabled:opacity-40 hover:bg-slate-100 cursor-pointer"
              >
                <ChevronsRight className="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        )}
      </div>

      {/* 6. MODAL: PURCHASE REQUEST ITEMS & ATTACHMENTS (Screenshot 3) */}
      {isItemModalOpen && selectedRequestForModal && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-3.5">
          <div className="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[92vh] flex flex-col overflow-hidden border border-slate-300 animate-in fade-in zoom-in-95 duration-200">
            <div className="bg-blue-600 text-white px-3.5 py-2.5 sm:px-4 sm:py-3 flex items-center justify-between gap-3 shrink-0 shadow-xs">
              <div className="flex items-center space-x-2 min-w-0">
                <FileText className="w-4 h-4 sm:w-5 sm:h-5 text-amber-300 shrink-0" />
                <div className="min-w-0">
                  <h3 className="font-extrabold text-xs sm:text-sm md:text-base leading-tight truncate">
                    Purchase Request Items: {selectedRequestForModal.purchase_no}
                  </h3>
                  <p className="text-[10px] sm:text-[11px] text-blue-100 truncate">
                    Client: {selectedRequestForModal.client_name} ({selectedRequestForModal.client_sr_id}) | Requested By: {selectedRequestForModal.creator_name}
                  </p>
                </div>
              </div>

              <div className="flex items-center space-x-3">
                <button
                  type="button"
                  onClick={() => setIsItemModalOpen(false)}
                  className="text-white hover:text-amber-300 p-1 rounded-lg transition-colors cursor-pointer shrink-0"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>
            </div>

            <div className="p-3 sm:p-4 space-y-4 sm:space-y-5 flex-1 overflow-y-auto">
              <div className="space-y-2">
                <div className="flex items-center justify-between">
                  <h4 className="font-bold text-xs text-slate-800 uppercase tracking-wider flex items-center space-x-1.5">
                    <Layers className="w-4 h-4 text-blue-600" />
                    <span>Item Breakdown ({selectedRequestForModal.items?.length || 0} Total)</span>
                  </h4>
                </div>

                {/* Desktop Table View */}
                <div className="hidden md:block overflow-x-auto rounded-lg border border-slate-300 shadow-2xs">
                  <table className="w-full text-xs text-left border-collapse">
                    <thead>
                      <tr className="bg-slate-100 text-slate-700 font-bold border-b border-slate-300 text-[11px] uppercase tracking-wider">
                        <th className="p-2.5 border-r border-slate-200 text-center w-10">#</th>
                        <th className="p-2.5 border-r border-slate-200 min-w-28">Item Category</th>
                        <th className="p-2.5 border-r border-slate-200 min-w-36">Item Description</th>
                        <th className="p-2.5 border-r border-slate-200 text-center w-16">Qty</th>
                        <th className="p-2.5 border-r border-slate-200 w-16">Unit</th>
                        <th className="p-2.5 border-r border-slate-200 w-28">Brand</th>
                        <th className="p-2.5 border-r border-slate-200 min-w-28">Remarks</th>
                        <th className="p-2.5 border-r border-slate-200 text-center min-w-32">Material</th>
                        <th className="p-2.5 border-r border-slate-200 min-w-28">Receiving Status</th>
                        <th className="p-2.5 border-r border-slate-200 min-w-44">Received / Approved By &amp; Date</th>
                        <th className="p-2.5 text-center min-w-36">Action</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-200 bg-white">
                      {(!selectedRequestForModal.items || selectedRequestForModal.items.length === 0) ? (
                        <tr>
                          <td colSpan={11} className="p-6 text-center text-slate-400 font-medium">
                            No items recorded in this purchase request.
                          </td>
                        </tr>
                      ) : (
                        selectedRequestForModal.items.map((it, idx) => {
                          const isOrdered = it.is_ordered === 1 || it.status === 'Send' || it.status === 'Ordered' || it.status === 'Recieved';
                          const isReceived = (it.status || '').toLowerCase() === 'recieved';
                          const isRejected = (it.status || '').toLowerCase() === 'reject';

                          return (
                            <tr key={it.id || idx} className="hover:bg-slate-50 transition-colors">
                              <td className="p-2.5 border-r border-slate-200 text-center font-bold text-slate-500">
                                {idx + 1}
                              </td>

                              <td className="p-2.5 border-r border-slate-200 font-semibold text-slate-800">
                                {it.item_category || 'Civil'}
                              </td>

                              <td className="p-2.5 border-r border-slate-200 font-bold text-slate-900">
                                {it.item}
                              </td>

                              <td className="p-2.5 border-r border-slate-200 text-center">
                                <span className="inline-flex items-center justify-center px-2 py-0.5 bg-cyan-600 text-white font-bold rounded text-[11px]">
                                  {it.qty}
                                </span>
                              </td>

                              <td className="p-2.5 border-r border-slate-200 text-slate-600 font-medium">
                                {it.unit || '-'}
                              </td>

                              <td className="p-2.5 border-r border-slate-200 text-slate-700 font-medium">
                                {it.brand || '-'}
                              </td>

                              <td className="p-2.5 border-r border-slate-200 text-slate-600">
                                {it.remarks || '-'}
                              </td>

                              <td className="p-2.5 border-r border-slate-200 text-center">
                                {/* 'Select to Send' action temporarily hidden - handled via CRM web */}
                                {isOrdered ? (
                                  <span className="inline-flex items-center space-x-1 px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[11px] font-bold">
                                    <CheckCircle2 className="w-3.5 h-3.5 text-blue-600" />
                                    <span>Sent</span>
                                  </span>
                                ) : (
                                  <span className="text-slate-400 text-[11px] font-medium">-</span>
                                )}
                              </td>

                              <td className="p-2.5 border-r border-slate-200">
                                {getStatusBadge(it.status || (isOrdered ? 'Send' : 'Pending'))}
                              </td>

                              <td className="p-2.5 border-r border-slate-200 text-slate-600 text-[11px]">
                                {it.approver_name ? (
                                  <div>
                                    <div className="font-bold text-slate-800">{it.approver_name}</div>
                                    <div className="text-[10px] text-slate-500">{it.status_approve_date || it.approve_date}</div>
                                    {it.approval_remarks && (
                                      <div className="text-[10px] italic text-slate-600 mt-0.5">
                                        &quot;{it.approval_remarks}&quot;
                                      </div>
                                    )}
                                  </div>
                                ) : (
                                  <span className="text-slate-400">-</span>
                                )}
                              </td>

                              <td className="p-2.5 text-center">
                                {isOrdered ? (
                                  <div className="flex items-center justify-center space-x-1.5">
                                    {isReceived ? (
                                      <span className="inline-flex items-center space-x-1 px-2.5 py-0.5 bg-green-50 text-green-700 border border-green-300 rounded text-[11px] font-bold">
                                        <Check className="w-3.5 h-3.5 text-green-600" />
                                        <span>Received</span>
                                      </span>
                                    ) : isRejected ? (
                                      <span className="inline-flex items-center space-x-1 px-2.5 py-0.5 bg-rose-50 text-rose-700 border border-rose-300 rounded text-[11px] font-bold">
                                        <X className="w-3.5 h-3.5 text-rose-600" />
                                        <span>Rejected</span>
                                      </span>
                                    ) : (
                                      <>
                                        <button
                                          type="button"
                                          onClick={() => handleOpenItemStatusModal(it, 'Recieved')}
                                          className="px-2.5 py-1 rounded text-[11px] font-bold flex items-center space-x-1 cursor-pointer transition-colors shadow-2xs bg-green-600 hover:bg-green-700 text-white"
                                        >
                                          <Check className="w-3 h-3" />
                                          <span>Receive</span>
                                        </button>

                                        <button
                                          type="button"
                                          onClick={() => handleOpenItemStatusModal(it, 'Reject')}
                                          className="px-2.5 py-1 rounded text-[11px] font-bold flex items-center space-x-1 cursor-pointer transition-colors shadow-2xs bg-rose-600 hover:bg-rose-700 text-white"
                                        >
                                          <X className="w-3 h-3" />
                                          <span>Reject</span>
                                        </button>
                                      </>
                                    )}
                                  </div>
                                ) : (
                                  <span className="text-slate-400 text-[11px] italic font-medium">
                                    Send first
                                  </span>
                                )}
                              </td>
                            </tr>
                          );
                        })
                      )}
                    </tbody>
                  </table>
                </div>

                {/* Mobile Item Breakdown Cards View */}
                <div className="md:hidden space-y-3">
                  {(!selectedRequestForModal.items || selectedRequestForModal.items.length === 0) ? (
                    <div className="p-6 text-center text-slate-400 font-medium bg-white rounded-lg border border-slate-200">
                      No items recorded in this purchase request.
                    </div>
                  ) : (
                    selectedRequestForModal.items.map((it, idx) => {
                      const isOrdered = it.is_ordered === 1 || it.status === 'Send' || it.status === 'Ordered' || it.status === 'Recieved';
                      const isReceived = (it.status || '').toLowerCase() === 'recieved';
                      const isRejected = (it.status || '').toLowerCase() === 'reject';

                      return (
                        <div
                          key={it.id || idx}
                          className="bg-white rounded-xl border border-slate-200 p-3.5 space-y-2.5 text-xs shadow-2xs"
                        >
                          <div className="flex items-start justify-between gap-2">
                            <div className="flex items-center space-x-2">
                              <span className="w-5 h-5 rounded bg-slate-100 text-slate-600 font-bold text-[10px] flex items-center justify-center border border-slate-200">
                                #{idx + 1}
                              </span>
                              <div>
                                <span className="font-extrabold text-slate-900 text-sm block leading-tight">
                                  {it.item}
                                </span>
                                <span className="text-[11px] font-semibold text-blue-600">
                                  {it.item_category || 'General'}
                                </span>
                              </div>
                            </div>
                            <div>
                              {getStatusBadge(it.status || (isOrdered ? 'Send' : 'Pending'))}
                            </div>
                          </div>

                          <div className="grid grid-cols-2 gap-2 text-[11px] bg-slate-50 p-2 rounded-lg border border-slate-200/80">
                            <div>
                              <span className="text-slate-400 block font-semibold">Qty &amp; Unit:</span>
                              <span className="font-bold text-slate-800">
                                {it.qty} {it.unit || 'Pcs'}
                              </span>
                            </div>
                            <div>
                              <span className="text-slate-400 block font-semibold">Brand:</span>
                              <span className="font-semibold text-slate-800">
                                {it.brand || 'N/A'}
                              </span>
                            </div>
                            {it.remarks && (
                              <div className="col-span-2 pt-1 border-t border-slate-200/60">
                                <span className="text-slate-400 font-semibold mr-1">Remarks:</span>
                                <span className="text-slate-700">{it.remarks}</span>
                              </div>
                            )}
                          </div>

                          {/* Material Order & Receive/Reject Controls */}
                          <div className="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
                            {/* 'Select to Send' action temporarily hidden - handled via CRM web */}
                            {isOrdered ? (
                              <span className="inline-flex items-center space-x-1 px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold">
                                <CheckCircle2 className="w-3.5 h-3.5 text-blue-600" />
                                <span>Material Sent</span>
                              </span>
                            ) : (
                              <div />
                            )}

                            {isOrdered && (
                              <div className="flex items-center space-x-1.5">
                                {isReceived ? (
                                  <span className="inline-flex items-center space-x-1 px-2.5 py-1 bg-green-50 text-green-700 border border-green-300 rounded-lg text-xs font-bold">
                                    <Check className="w-3.5 h-3.5 text-green-600" />
                                    <span>Received</span>
                                  </span>
                                ) : isRejected ? (
                                  <span className="inline-flex items-center space-x-1 px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-300 rounded-lg text-xs font-bold">
                                    <X className="w-3.5 h-3.5 text-rose-600" />
                                    <span>Rejected</span>
                                  </span>
                                ) : (
                                  <>
                                    <button
                                      type="button"
                                      onClick={() => handleOpenItemStatusModal(it, 'Recieved')}
                                      className="px-2.5 py-1.5 rounded-lg text-xs font-bold flex items-center space-x-1 cursor-pointer transition-colors shadow-2xs bg-green-600 hover:bg-green-700 text-white"
                                    >
                                      <Check className="w-3.5 h-3.5" />
                                      <span>Receive</span>
                                    </button>

                                    <button
                                      type="button"
                                      onClick={() => handleOpenItemStatusModal(it, 'Reject')}
                                      className="px-2.5 py-1.5 rounded-lg text-xs font-bold flex items-center space-x-1 cursor-pointer transition-colors shadow-2xs bg-rose-600 hover:bg-rose-700 text-white"
                                    >
                                      <X className="w-3.5 h-3.5" />
                                      <span>Reject</span>
                                    </button>
                                  </>
                                )}
                              </div>
                            )}
                          </div>

                          {it.approver_name && (
                            <div className="text-[10px] text-slate-500 bg-slate-50 p-1.5 rounded border border-slate-200/60">
                              <span className="font-bold text-slate-700">Approver: </span>
                              {it.approver_name} ({it.status_approve_date || it.approve_date})
                              {it.approval_remarks && (
                                <span className="italic block mt-0.5">&quot;{it.approval_remarks}&quot;</span>
                              )}
                            </div>
                          )}
                        </div>
                      );
                    })
                  )}
                </div>
              </div>

              {/* SITE PHOTOS & ATTACHMENTS (Bottom of Screenshot 3) */}
              <div className="bg-slate-50 rounded-xl border border-slate-300 p-4 space-y-3">
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 border-b border-slate-200 pb-2.5">
                  <div className="flex items-center space-x-2">
                    <Camera className="w-4 h-4 text-blue-600" />
                    <h4 className="font-bold text-xs text-slate-900">
                      Purchase Request Site Photos &amp; Attachments ({selectedRequestForModal.photos?.length || 0})
                    </h4>
                  </div>
                  <span className="text-[11px] text-slate-500 font-medium">
                    Upload photos for this purchase request
                  </span>
                </div>

                {(!selectedRequestForModal.items || selectedRequestForModal.items.length === 0) &&
                (!selectedRequestForModal.items_count || selectedRequestForModal.items_count === 0) ? (
                  <div className="p-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-lg text-xs flex items-center space-x-2">
                    <AlertCircle className="w-4 h-4 text-amber-600 shrink-0" />
                    <span className="font-semibold">
                      Please add items to the item list before uploading photos.
                    </span>
                  </div>
                ) : (
                  <div className="flex flex-wrap items-center gap-3">
                    <input
                      type="file"
                      ref={fileInputRef}
                      onChange={handleUploadPhotos}
                      multiple
                      accept="image/jpeg,image/png,image/webp"
                      className="hidden"
                      id="modal-photo-upload"
                    />
                    <label
                      htmlFor="modal-photo-upload"
                      className="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow-2xs flex items-center space-x-1.5 transition-colors cursor-pointer"
                    >
                      {isUploadingPhotos ? (
                        <>
                          <span className="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                          <span>Uploading...</span>
                        </>
                      ) : (
                        <>
                          <Camera className="w-4 h-4" />
                          <span>Click / Upload Photos</span>
                        </>
                      )}
                    </label>
                    <span className="text-[11px] text-slate-500">
                      ℹ Multiple images (JPG, PNG, WEBP) supported.
                    </span>
                  </div>
                )}

                {(!selectedRequestForModal.photos || selectedRequestForModal.photos.length === 0) ? (
                  <div className="p-6 text-center border-2 border-dashed border-slate-200 rounded-lg text-slate-400 text-xs">
                    No photos uploaded for this purchase request yet.
                  </div>
                ) : (
                  <div className="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 pt-2">
                    {selectedRequestForModal.photos.map((photo, pIdx) => (
                      <div
                        key={photo.fileName || pIdx}
                        className="group relative bg-white border border-slate-300 rounded-lg overflow-hidden shadow-2xs hover:shadow-md transition-all"
                      >
                        <img
                          src={photo.file_url}
                          alt={photo.fileName}
                          className="w-full h-24 object-cover cursor-pointer"
                          onClick={() => setPreviewPhotoUrl(photo.file_url)}
                          onError={(e) => {
                            (e.target as HTMLImageElement).src =
                              'https://placehold.co/300x200?text=Attachment';
                          }}
                        />
                        <div className="p-1.5 text-[10px] truncate text-slate-700 font-medium bg-white">
                          {photo.fileName}
                        </div>
                        <div className="absolute top-1 right-1 flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                          <button
                            type="button"
                            onClick={() => setPreviewPhotoUrl(photo.file_url)}
                            className="p-1 bg-black/70 text-white rounded hover:bg-black cursor-pointer"
                            title="View Fullsize"
                          >
                            <Eye className="w-3 h-3" />
                          </button>
                          <button
                            type="button"
                            onClick={() => handleDeletePhoto(photo)}
                            className="p-1 bg-rose-600 text-white rounded hover:bg-rose-700 cursor-pointer"
                            title="Delete Photo"
                          >
                            <Trash2 className="w-3 h-3" />
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            <div className="p-2.5 sm:p-3 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 shrink-0">
              {/* Delivery Status Selector & Manual Update Button */}
              <div className="flex items-center flex-wrap gap-2">
                <span className="text-xs font-bold text-slate-700 shrink-0">
                  Delivery Status:
                </span>
                <select
                  value={
                    modalDeliveryStatus.toLowerCase().includes('complete')
                      ? DELIVERY_STATUS_COMPLETE
                      : DELIVERY_STATUS_PARTIAL
                  }
                  onChange={(e) => {
                    const val = e.target.value;
                    if (val === DELIVERY_STATUS_COMPLETE && !areAllItemsReceived) {
                      showToast?.('Cannot select Complete Recvied at site: All items must be marked as Received first.');
                      return;
                    }
                    setModalDeliveryStatus(val);
                  }}
                  className="bg-white text-slate-900 border border-slate-300 font-bold text-xs rounded-lg px-2.5 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-2xs cursor-pointer"
                >
                  <option value={DELIVERY_STATUS_PARTIAL}>Partial Recived at site</option>
                  <option value={DELIVERY_STATUS_COMPLETE} disabled={!areAllItemsReceived}>
                    Complete Recvied at site {!areAllItemsReceived ? '(Pending items remain)' : ''}
                  </option>
                </select>

                <button
                  type="button"
                  onClick={handleSaveDeliveryStatus}
                  disabled={isUpdatingDeliveryStatus}
                  className="px-3 py-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-xs rounded-lg shadow-2xs flex items-center space-x-1.5 transition-colors cursor-pointer shrink-0"
                >
                  {isUpdatingDeliveryStatus ? (
                    <>
                      <span className="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      <span>Saving...</span>
                    </>
                  ) : (
                    <>
                      <Save className="w-3.5 h-3.5" />
                      <span>Update Delivery Status</span>
                    </>
                  )}
                </button>
              </div>

              <button
                type="button"
                onClick={() => setIsItemModalOpen(false)}
                className="px-4 py-1 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-lg shadow-2xs transition-colors cursor-pointer self-end sm:self-auto shrink-0"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 7. QUICK PHOTOS MANAGER MODAL */}
      {isPhotosModalOpen && selectedRequestForPhotos && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3">
          <div className="bg-white rounded-xl shadow-2xl max-w-2xl w-full p-4 space-y-4 animate-in fade-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between border-b border-slate-200 pb-3">
              <div className="flex items-center space-x-2">
                <Camera className="w-5 h-5 text-blue-600" />
                <div>
                  <h3 className="font-bold text-sm text-slate-900">
                    Photos for {selectedRequestForPhotos.purchase_no}
                  </h3>
                  <p className="text-[11px] text-slate-500">
                    Client: {selectedRequestForPhotos.client_name}
                  </p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setIsPhotosModalOpen(false)}
                className="text-slate-400 hover:text-slate-600 p-1 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Upload New Photos in Quick Photos Modal (Commented out - View only from list) */}
            {/*
            {(!selectedRequestForPhotos.items || selectedRequestForPhotos.items.length === 0) &&
            (!selectedRequestForPhotos.items_count || selectedRequestForPhotos.items_count === 0) ? (
              <div className="p-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-lg text-xs flex items-center space-x-2">
                <AlertCircle className="w-4 h-4 text-amber-600 shrink-0" />
                <span className="font-semibold">
                  Please add items to the item list before uploading photos.
                </span>
              </div>
            ) : (
              <div className="flex flex-wrap items-center gap-3 bg-slate-50 p-3 rounded-lg border border-slate-200">
                <input
                  type="file"
                  multiple
                  accept="image/jpeg,image/png,image/webp"
                  onChange={handleUploadPhotos}
                  className="hidden"
                  id="quick-photo-upload"
                />
                <label
                  htmlFor="quick-photo-upload"
                  className="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow-2xs flex items-center space-x-1.5 transition-colors cursor-pointer"
                >
                  {isUploadingPhotos ? (
                    <>
                      <span className="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      <span>Uploading...</span>
                    </>
                  ) : (
                    <>
                      <Upload className="w-4 h-4" />
                      <span>Upload New Photos</span>
                    </>
                  )}
                </label>
                <span className="text-[11px] text-slate-500">
                  Select one or multiple photos to attach.
                </span>
              </div>
            )}
            */}

            {(!selectedRequestForPhotos.photos || selectedRequestForPhotos.photos.length === 0) ? (
              <div className="p-8 text-center border-2 border-dashed border-slate-200 rounded-lg text-slate-400 text-xs">
                No photos attached yet.
              </div>
            ) : (
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 max-h-80 overflow-y-auto p-1">
                {selectedRequestForPhotos.photos.map((photo, idx) => (
                  <div
                    key={photo.fileName || idx}
                    className="relative group bg-white border border-slate-300 rounded-lg overflow-hidden shadow-2xs"
                  >
                    <img
                      src={photo.file_url}
                      alt={photo.fileName}
                      className="w-full h-28 object-cover cursor-pointer"
                      onClick={() => setPreviewPhotoUrl(photo.file_url)}
                      onError={(e) => {
                        (e.target as HTMLImageElement).src =
                          'https://placehold.co/300x200?text=Photo';
                      }}
                    />
                    <div className="p-1 text-[10px] truncate text-slate-700 font-medium">
                      {photo.fileName}
                    </div>
                    <div className="absolute top-1 right-1 flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button
                        type="button"
                        onClick={() => handleDeletePhoto(photo)}
                        className="p-1 bg-rose-600 text-white rounded hover:bg-rose-700 cursor-pointer"
                        title="Delete"
                      >
                        <Trash2 className="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}

            <div className="flex justify-end pt-2">
              <button
                type="button"
                onClick={() => setIsPhotosModalOpen(false)}
                className="px-4 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer"
              >
                Done
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 8. PHOTO FULLSIZE LIGHTBOX PREVIEW */}
      {previewPhotoUrl && (
        <div
          className="fixed inset-0 z-60 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4 cursor-pointer"
          onClick={() => setPreviewPhotoUrl(null)}
        >
          <div className="relative max-w-4xl max-h-[90vh]">
            <img
              src={previewPhotoUrl}
              alt="Preview"
              className="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain"
            />
            <button
              onClick={() => setPreviewPhotoUrl(null)}
              className="absolute -top-10 right-0 text-white hover:text-rose-400 p-1 cursor-pointer font-bold flex items-center space-x-1 text-xs"
            >
              <X className="w-5 h-5" />
              <span>Close (Esc)</span>
            </button>
          </div>
        </div>
      )}

      {/* 9. REJECT REASON PROMPT MODAL */}
      {rejectModalState.isOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-2xl max-w-md w-full p-4 space-y-3 animate-in fade-in zoom-in-95 duration-150">
            <div className="flex items-center space-x-2 text-rose-600">
              <XCircle className="w-5 h-5" />
              <h3 className="font-bold text-sm text-slate-900">
                Reject Purchase Request: {rejectModalState.purchaseNo}
              </h3>
            </div>
            <p className="text-xs text-slate-500">
              Please enter the reason for rejecting this purchase request:
            </p>
            <textarea
              value={rejectModalState.remark}
              onChange={(e) =>
                setRejectModalState((prev) => ({ ...prev, remark: e.target.value }))
              }
              placeholder="e.g. Budget exceeded / Incorrect brand specified / Duplicate request"
              rows={3}
              className="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-xs text-slate-800 focus:outline-none focus:border-rose-500"
              autoFocus
            />
            <div className="flex items-center justify-end space-x-2 pt-2">
              <button
                type="button"
                onClick={() =>
                  setRejectModalState({ isOpen: false, purchaseId: null, purchaseNo: '', remark: '' })
                }
                className="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-lg transition-colors cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={handleSubmitRejection}
                className="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer shadow-xs"
              >
                Confirm Rejection
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 10. ITEM RECEIVE / REJECT STATUS PROMPT MODAL */}
      {itemStatusModalState.isOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-2xl max-w-md w-full p-4 space-y-3 animate-in fade-in zoom-in-95 duration-150">
            <div className="flex items-center space-x-2">
              {itemStatusModalState.targetStatus === 'Recieved' ? (
                <CheckCircle2 className="w-5 h-5 text-green-600" />
              ) : (
                <XCircle className="w-5 h-5 text-rose-600" />
              )}
              <h3 className="font-bold text-sm text-slate-900">
                Mark Item as {itemStatusModalState.targetStatus}: {itemStatusModalState.itemName}
              </h3>
            </div>
            <p className="text-xs text-slate-500">
              Optional verification / approval remarks:
            </p>
            <input
              type="text"
              value={itemStatusModalState.remarks}
              onChange={(e) =>
                setItemStatusModalState((prev) => ({ ...prev, remarks: e.target.value }))
              }
              placeholder={
                itemStatusModalState.targetStatus === 'Recieved'
                  ? 'e.g. Material verified and received on site'
                  : 'e.g. Damaged material / Quantity mismatched'
              }
              className="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs text-slate-800 focus:outline-none focus:border-blue-500"
              autoFocus
            />
            <div className="flex items-center justify-end space-x-2 pt-2">
              <button
                type="button"
                onClick={() =>
                  setItemStatusModalState({
                    isOpen: false,
                    itemId: null,
                    itemName: '',
                    targetStatus: 'Recieved',
                    remarks: '',
                  })
                }
                className="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-lg transition-colors cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={handleSubmitItemStatus}
                className={`px-4 py-1.5 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer shadow-xs ${
                  itemStatusModalState.targetStatus === 'Recieved'
                    ? 'bg-green-600 hover:bg-green-700'
                    : 'bg-rose-600 hover:bg-rose-700'
                }`}
              >
                Confirm {itemStatusModalState.targetStatus}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 11. DELETE CONFIRMATION MODAL */}
      {deleteConfirmState.isOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-2xl max-w-sm w-full p-4 space-y-3 animate-in fade-in zoom-in-95 duration-150">
            <div className="flex items-center space-x-2 text-rose-600">
              <AlertCircle className="w-5 h-5" />
              <h3 className="font-bold text-sm text-slate-900">Delete Purchase Request</h3>
            </div>
            <p className="text-xs text-slate-600">
              Are you sure you want to permanently delete purchase request{' '}
              <strong className="text-slate-900">{deleteConfirmState.purchaseNo}</strong>?
              This action cannot be undone.
            </p>
            <div className="flex items-center justify-end space-x-2 pt-2">
              <button
                type="button"
                onClick={() =>
                  setDeleteConfirmState({ isOpen: false, purchaseId: null, purchaseNo: '' })
                }
                className="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-lg transition-colors cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={handleConfirmDeleteRequest}
                className="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer shadow-xs"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
