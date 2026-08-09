import {
  ClientProject,
  DetailSectionItem,
  BOQItem,
  MOMItem,
  CivilDrawing,
  KTRecord,
  Design3DRequest,
  ValidationDrawing,
  BOMRecord,
  PurchaseRequestItem,
  DispatchItem,
  LooseFurnitureRecord,
  PaymentRecord,
  RemarkItem,
  QCDesignItem,
  FinalValidationItem,
  OnSitePurchaseItem,
  LooseFurnitureItem,
  HandoverItem
} from './types';

export const INITIAL_CLIENTS: ClientProject[] = [];

export const INITIAL_ESCALATIONS = [
  {
    id: 'ESC-101',
    clientId: 'HC101804',
    clientName: 'Aviral Saxena',
    category: 'Drawing Approval' as const,
    severity: 'High' as const,
    status: 'In Progress' as const,
    description: 'Civil drawing approval delayed by 3 days. Client requested updated electrical layout.',
    assignedTo: 'Mansi Goyal',
    createdAt: '2026-07-29 10:15 AM',
  },
  {
    id: 'ESC-102',
    clientId: 'HC101806',
    clientName: 'Shubhra Chauhan',
    category: 'Site Delay' as const,
    severity: 'Critical' as const,
    status: 'Open' as const,
    description: 'Site engineer pending assignment. Civil work team waiting on location.',
    assignedTo: 'Abhishek Bhati',
    createdAt: '2026-07-30 02:40 PM',
  },
  {
    id: 'ESC-103',
    clientId: 'HC101791',
    clientName: 'Test Lead Digital 10 july',
    category: 'Material Dispatch' as const,
    severity: 'Medium' as const,
    status: 'Resolved' as const,
    description: 'Partial furniture dispatch verified with site manager.',
    assignedTo: 'Nishant Singh',
    createdAt: '2026-07-26 11:00 AM',
    resolvedAt: '2026-07-27 04:30 PM',
  },
];

export const MOCK_DETAILS_CHECKLIST: DetailSectionItem[] = [
  { id: '3', title: 'Escalation', key: 'escalation', completed: true },
  { id: '4', title: 'BOM', key: 'bom', completed: true },
  { id: '5', title: 'Dispatch', key: 'dispatch', completed: true },
  { id: '6', title: 'Loose Furniture', key: 'looseFurniture', completed: true },
  { id: '7', title: 'QC Design', key: 'qcDesign', completed: true },
  { id: '8', title: 'Final Production Drawing', key: 'finalValidation', completed: true },
  { id: '9', title: 'On Site Purchase Request', key: 'onSitePurchaseRequest', completed: true },
  { id: '10', title: 'Execution Timeline', key: 'executionTimeline', completed: true },
  { id: '11', title: 'Handover', key: 'handover', completed: true },
];

export const INITIAL_HANDOVER_LIST: HandoverItem[] = [
  {
    id: 'ho-101',
    clientId: 'HC101806',
    clientName: 'Shubhra Chauhan',
    title: 'Site Handover & Key Acceptance Certificate',
    handoverType: 'Final Site Handover',
    fileName: 'HC101806_Final_Handover_Certificate.pdf',
    fileUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
    fileSize: '2.4 MB',
    fileType: 'PDF Document',
    handoverDate: '2026-08-01',
    handoverBy: 'Abhishek Bhati (Project Manager)',
    handoverTo: 'Shubhra Chauhan',
    status: 'Completed',
    remarks: 'Keys handed over along with site defect sign-off sheet and appliance manuals.',
    createdAt: '2026-08-01 11:30 AM',
  },
  {
    id: 'ho-102',
    clientId: 'HC101806',
    clientName: 'Shubhra Chauhan',
    title: 'Warranty Cards & Appliance Manuals Handover',
    handoverType: 'Warranty & Manuals',
    fileName: 'HC101806_Warranty_Manuals_Bundle.pdf',
    fileUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
    fileSize: '4.8 MB',
    fileType: 'PDF Document',
    handoverDate: '2026-08-02',
    handoverBy: 'Nishant Singh',
    handoverTo: 'Shubhra Chauhan',
    status: 'Approved',
    remarks: 'Modular fittings & hardware warranty cards verified and signed.',
    createdAt: '2026-08-02 02:15 PM',
  },
  {
    id: 'ho-103',
    clientId: '520',
    clientName: 'Test Client',
    title: 'Interim Civil & Electrical Handover Sheet',
    handoverType: 'Interim Handover',
    fileName: 'TestClient_Civil_Handover_Draft.pdf',
    fileUrl: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
    fileSize: '1.2 MB',
    fileType: 'PDF Document',
    handoverDate: '2026-08-05',
    handoverBy: 'Site Supervisor',
    handoverTo: 'Test Client',
    status: 'Pending Sign-off',
    remarks: 'Awaiting client final signature on electrical point check.',
    createdAt: '2026-08-05 04:00 PM',
  },
];

export const INITIAL_BOQ_LIST: BOQItem[] = [
  {
    quotationNo: 'HCIPPL/Quote/26-27/N/10026850/2',
    date: '2026-07-22',
    phone: '3030303030',
    gTotal: 188125.80,
    siteHandling: 0.00,
    toBePaid: 188125.80,
    status: 'Draft',
  },
];

export const INITIAL_MOM_LIST: MOMItem[] = [
  {
    id: 'mom-1',
    fileName: 'Mom/CheckList',
    fileUrl: '#',
    date: '2026-07-22 12:29 PM',
    thumbnailUrl: 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=400&q=80',
  },
];

export const INITIAL_CIVIL_DRAWINGS: CivilDrawing[] = [
  {
    id: 'cd-1',
    fileName: 'Test Client : 22 july_Drawing_file',
    fileUrl: '1784715410_6a609892c80b9.pdf',
    designType: 'Civil Design',
    status: 'Approved',
    uploadedBy: 'Nishant Singh',
    date: '2026-07-22 3:46 PM',
  },
];

export const INITIAL_KT_RECORD: KTRecord = {
  requestType: 'KT',
  requestDate: '22-07-2026',
  status: 'Approved',
  ktCheck: 'Done',
};

export const INITIAL_3D_REQUEST: Design3DRequest = {
  id: '3d-1',
  requestType: '3d Design',
  requestDate: '29-07-2026',
  status: 'Pending',
  files: [],
};

export const INITIAL_POST_VALIDATION_DRAWING: ValidationDrawing = {
  id: 'val-1',
  fileName: 'Test Client : 22 july_Drawing_file',
  fileUrl: '1784712840_6a608e8889330.pdf',
  designType: 'Modular Design',
  url: 'gefuigjiodfjgjskehn',
  status: 'Reject',
  uploadedBy: 'Nishant Singh',
  date: '2026-07-22 3:04 PM',
  rejectionReason: 'bvhsdbjhfvsd',
};

export const INITIAL_BOM_RECORDS: BOMRecord[] = [
  {
    id: 'bom-1',
    fileName: 'BOM1',
    category: 'Category 1',
    type: 'BOM 1',
    fileUrl: '#',
    imageUrl: 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=300&q=80',
    date: '2026-06-24 3:34 PM',
    status: 'Accepted',
    children: [
      {
        id: 'bom-1-child-1',
        fileName: 'Additional 1',
        category: 'Category 1',
        type: 'BOM 1',
        fileUrl: '#',
        imageUrl: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=300&q=80',
        date: '2026-07-29 1:35 PM',
        status: 'Pending',
        parentId: 'bom-1',
      },
    ],
  },
  {
    id: 'bom-2',
    fileName: 'BOM2',
    category: 'Category 1',
    type: 'BOM 2',
    fileUrl: '#',
    imageUrl: 'https://images.unsplash.com/photo-1618219908412-a29a1bb7b86e?auto=format&fit=crop&w=300&q=80',
    date: '2026-06-24 3:34 PM',
    status: 'Accepted',
  },
];

export const INITIAL_PURCHASE_REQUESTS: PurchaseRequestItem[] = [
  {
    id: 'pr-1',
    fileName: 'Purchase',
    bomName: 'BOM1',
    vendorName: 'hhjjhnjnhbgjhguy',
    vendorImage: 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=300&q=80',
    fileUrl: '#',
    date: '2026-06-24 3:36 PM',
    status: 'Order Placed',
  },
  {
    id: 'pr-2',
    fileName: 'Purchase',
    bomName: 'BOM2',
    vendorName: 'bhjbjhh',
    vendorImage: 'https://images.unsplash.com/photo-1618219908412-a29a1bb7b86e?auto=format&fit=crop&w=300&q=80',
    fileUrl: '#',
    date: '2026-06-24 3:37 PM',
    status: 'Pending',
  },
];

export const INITIAL_SITE_PURCHASES: PurchaseRequestItem[] = [
  {
    id: 'spr-1',
    fileName: 'ttrjhghj Recieved',
    bomName: 'Site Material',
    vendorName: 'Local Hardware Supplier',
    fileUrl: '#',
    date: '2026-05-31 2:25 PM',
    status: 'Received',
    isOnSite: true,
  },
];

export const INITIAL_DISPATCH_ITEMS: DispatchItem[] = [
  {
    id: '18',
    client_id: 395,
    clientName: 'Test Lead Digital',
    clientId: 'HC395',
    itemName: 'pan card',
    file_name: 'pan card',
    status: 'Approved',
    status_approve_by: '5',
    status_approve_date: '2026-08-04 22:50:39',
    upload_file: ['https://crm.hcinterior.in/uploads/09f14f84-07fe-4e84-a436-9daa8b617cd9-2.jpg'],
    created_date: '2026-08-04 22:50:31',
    updated_date: '2026-08-04 22:50:31',
    queries: [
      {
        id: 13,
        client_id: 395,
        dispatch_id: 18,
        remarks: 'Hello this is from postment',
        uploaded_by: 5,
        uploaded_by_name: 'Nishant Singh',
        file_name: '0ee9a9e03bcc8d289a130e7186a6eba1.png',
        file_url: 'https://crm.hcinterior.in/uploads/0ee9a9e03bcc8d289a130e7186a6eba1.png',
        created_date: '2026-08-04 18:30:23',
      },
    ],
    total_queries: 1,
    imageUrl: 'https://crm.hcinterior.in/uploads/09f14f84-07fe-4e84-a436-9daa8b617cd9-2.jpg',
    date: '2026-08-04 22:50:31',
    requestType: 'dispatch',
    dispatchType: 'Full',
  },
  {
    id: '17',
    client_id: 531,
    clientName: 'test designer Demo',
    clientId: 'HC101802',
    client_sr_id: 'HC101802',
    itemName: 'Test Lead Digital_Drawing_file',
    file_name: 'Test Lead Digital_Drawing_file',
    status: 'Pending',
    upload_file: ['https://crm.hcinterior.in/uploads/logo.png'],
    created_date: '2026-08-04 22:51:41',
    updated_date: '2026-08-04 22:51:41',
    queries: [],
    total_queries: 0,
    imageUrl: 'https://crm.hcinterior.in/uploads/logo.png',
    date: '2026-08-04 22:51:41',
    requestType: 'dispatch',
    dispatchType: 'Full',
  },
];

export const INITIAL_LOOSE_FURNITURE: LooseFurnitureRecord = {
  id: 'lf-1',
  requestType: 'Loose Furniture',
  requestDate: '24-06-2026',
  dispatchDate: '24-06-2026',
  status: 'Approved',
  purchases: [],
};

export const INITIAL_PAYMENT_RECORDS: PaymentRecord[] = [
  {
    id: 'pay-1',
    amount: 1000.00,
    paymentMode: 'Cash',
    paymentDate: '17 May 2026',
    transactionDetail: 'cbzcbj',
    status: 'Paid',
    date: '2026-05-17',
  },
  {
    id: 'pay-2',
    amount: 2000.00,
    paymentMode: 'Cash',
    paymentDate: '03 Jun 2026',
    transactionDetail: 'test',
    status: 'Paid',
    date: '2026-06-03',
  },
  {
    id: 'pay-3',
    amount: 100000.00,
    paymentMode: 'Cash',
    paymentDate: '05 Jun 2026',
    transactionDetail: 'test jhdfsdh',
    status: 'Paid',
    date: '2026-06-05',
  },
  {
    id: 'pay-4',
    amount: 50000.00,
    paymentMode: 'Cash',
    paymentDate: '05 Jun 2026',
    transactionDetail: 'vxjhbcvhsdbk',
    status: 'Paid',
    date: '2026-06-05',
  },
];

export const INITIAL_REMARKS: RemarkItem[] = [
  {
    id: 'rem-1',
    text: 'Client requested light oak wood texture for living room paneling.',
    author: 'Nishant Singh',
    date: '2026-07-22 10:30 AM',
  },
  {
    id: 'rem-2',
    text: 'Electrical civil points approved by client on site.',
    author: 'Abhishek Bhati',
    date: '2026-07-23 04:15 PM',
  },
];

export const INITIAL_QC_DESIGNS: QCDesignItem[] = [];

export const INITIAL_FINAL_VALIDATIONS: FinalValidationItem[] = [
  {
    id: 'fv-1',
    clientName: 'Deepak Abrol',
    clientId: 'HC101717',
    fileName: 'Deepak Abrol_Drawing_file',
    fileUrl: '1785647604_6a6ed1f472f04.pdf',
    isFinal: 'Yes',
    designType: 'Post Validation Design',
    url: '#',
    status: 'Approved',
    uploadedBy: 'Shreyashi Kishor',
    date: '02 Aug 2026 10:43 AM',
  },
  {
    id: 'fv-2',
    clientName: 'Onkar',
    clientId: 'HC101645',
    fileName: 'Onkar_Drawing_file',
    fileUrl: '1785561899_6a6d832b292a2.pdf',
    isFinal: 'Yes',
    designType: 'Post Validation Design',
    url: '#',
    status: 'Approved',
    uploadedBy: 'Ayush',
    date: '01 Aug 2026 10:54 AM',
  },
  {
    id: 'fv-3',
    clientName: 'Shobit Aggarwal',
    clientId: 'HC101683',
    fileName: 'Shobit Aggarwal_Drawing_file',
    fileUrl: '1784983879_6a64b1476c00b.pdf',
    isFinal: 'Yes',
    designType: 'Post Validation Design',
    url: '#',
    status: 'Approved',
    uploadedBy: 'Ahetasham Naseem',
    date: '25 Jul 2026 06:21 PM',
  },
  {
    id: 'fv-4',
    clientName: 'Anil',
    clientId: 'HC101723',
    fileName: 'Anil_Drawing_file',
    fileUrl: '1784874201_6a6304d9134d2.pdf',
    isFinal: 'Yes',
    designType: 'Post Validation Design',
    url: '#',
    status: 'Approved',
    uploadedBy: 'Himani Jain',
    date: '24 Jul 2026 11:53 AM',
  },
];

export const INITIAL_LOOSE_FURNITURE_ITEMS: LooseFurnitureItem[] = [
  {
    id: 'lf-20',
    clientName: 'Test Lead Digital',
    clientId: 'HC395',
    client_id: 395,
    fileName: 'pan card',
    status: 'Recieved',
    date: '2026-08-04 22:50:11',
    created_date: '2026-08-04 22:50:11',
    upload_file: ['https://crm.hcinterior.in/uploads/items.png'],
    upload_url: 'test',
  },
  {
    id: 'lf-19',
    clientName: 'test designer Demo',
    clientId: 'HC101802',
    client_id: 531,
    client_sr_id: 'HC101802',
    fileName: 'Test Lead Digital_Drawing_file',
    status: 'Pending',
    date: '2026-08-04 19:21:44',
    created_date: '2026-08-04 19:21:44',
    upload_file: [],
    upload_url: 'testasdfs s fasdf ',
  },
  {
    id: 'lf-18',
    clientName: 'test designer Demo',
    clientId: 'HC101802',
    client_id: 531,
    client_sr_id: 'HC101802',
    fileName: 'Test Lead Digital_Drawing_file',
    status: 'Recieved',
    status_approve_by: '5',
    status_approve_date: '2026-08-04 19:21:06',
    date: '2026-08-04 19:20:58',
    created_date: '2026-08-04 19:20:58',
    upload_file: [],
    upload_url: 'http://localhost/hcinterior.recallinfotech.com/admin/client/details/37/2ddesign',
  },
];

export const INITIAL_ON_SITE_PURCHASES: OnSitePurchaseItem[] = [
  {
    id: 'osp-1',
    clientName: 'Test Lead Digital',
    clientId: 'HC101806',
    fileName: 'Test Lead Digital_Drawing_file',
    fileUrl: 'PV5K-307.pdf',
    status: 'Recieved',
    date: '02 Aug 2026 07:07 PM',
  },
  {
    id: 'osp-2',
    clientName: 'Shubhra Chauhan',
    clientId: 'HC101806',
    fileName: 'Shubhra Chauhan_Site_Purchase',
    fileUrl: 'PV5K-308.pdf',
    status: 'Recieved',
    date: '02 Aug 2026 05:15 PM',
  },
  {
    id: 'osp-3',
    clientName: 'Aman Juneja',
    clientId: 'HC101670',
    fileName: 'Aman Juneja_Site_Purchase',
    fileUrl: 'PV5K-309.pdf',
    status: 'Pending',
    date: '02 Aug 2026 02:20 PM',
  },
];

