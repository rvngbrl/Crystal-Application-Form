export type Step = 'terms' | 'guide' | 'form' | 'submitted';

export interface SeafarerFormData {
  // Personal Info
  firstName: string;
  lastName: string;
  middleName: string;
  dob: string;
  age?: string;
  pob: string;
  nationality: string;
  gender: string;
  civilStatus: string;
  email: string;
  phone: string;
  address: string;
  
  // Application details
  appliedRank: string;
  vesselTypePreference: string;
  availableDate: string;
  srnNumber: string; // Seafarer Registration Number
  
  // Sea Experience Summary
  yearsExperience: string;
  lastVesselName: string;
  lastCompany: string;
  
  // Checkbox confirmation
  termsAccepted: boolean;
  accuracyDeclared: boolean;
}

export interface DocumentUpload {
  id: string;
  name: string;
  required: boolean;
  file?: File;
  status: 'pending' | 'uploaded';
}
