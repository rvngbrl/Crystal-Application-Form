import React, { useState } from 'react';
import containerShipBg from '../assets/images/cargo_container_ship_1785313984424.jpg';
import { SeafarerFormData } from '../types';
import { User, Anchor, FileText, Upload, CheckCircle2, ArrowRight, ArrowLeft, AlertCircle } from 'lucide-react';

interface ApplicationFormPageProps {
  onSubmit: (formData: SeafarerFormData) => void;
  onBack: () => void;
}

export const ApplicationFormPage: React.FC<ApplicationFormPageProps> = ({ onSubmit, onBack }) => {
  const calculateAge = (dobString: string): string => {
    if (!dobString) return '';
    const birthDate = new Date(dobString);
    if (isNaN(birthDate.getTime())) return '';
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }
    return age >= 0 ? `${age} yrs` : '';
  };

  const [formData, setFormData] = useState<SeafarerFormData>({
    firstName: '',
    lastName: '',
    middleName: '',
    dob: '',
    pob: '',
    nationality: 'Filipino',
    gender: 'Male',
    civilStatus: 'Single',
    email: '',
    phone: '',
    address: '',
    appliedRank: 'Able Seaman (AB)',
    vesselTypePreference: 'Container Ship',
    availableDate: '',
    srnNumber: '',
    yearsExperience: '3-5 years',
    lastVesselName: '',
    lastCompany: '',
    termsAccepted: true,
    accuracyDeclared: true,
  });

  const [passportFile, setPassportFile] = useState<File | null>(null);
  const [seamanBookFile, setSeamanBookFile] = useState<File | null>(null);
  const [stcwFile, setStcwFile] = useState<File | null>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit(formData);
  };

  return (
    <div className="relative min-h-[calc(100vh-70px)] py-8 px-4 md:px-8 font-sans">
      {/* Background Image with Overlay */}
      <div 
        className="fixed inset-0 bg-cover bg-center bg-no-repeat -z-10"
        style={{ backgroundImage: `url(${containerShipBg})` }}
      >
        <div className="absolute inset-0 bg-gradient-to-b from-slate-200/80 via-slate-100/85 to-slate-300/90 backdrop-blur-[2px]" />
      </div>

      <div className="w-full max-w-4xl mx-auto space-y-6">
        {/* Header Bar */}
        <div className="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white/95 backdrop-blur-md p-6 rounded-3xl shadow-xl border border-white/60">
          <div>
            <p className="text-blue-700 text-xs font-bold uppercase tracking-wider">STEP 3 OF 4</p>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
              Seafarer Application Form
            </h1>
            <p className="text-slate-600 text-xs sm:text-sm mt-1">
              Please enter your accurate personal details and sea service experience.
            </p>
          </div>
          <button
            type="button"
            onClick={onBack}
            className="flex items-center gap-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-xl transition-all shrink-0 cursor-pointer"
          >
            <ArrowLeft className="w-4 h-4" />
            Back to Guide
          </button>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Section 1: Personal Information */}
          <div className="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-3xl shadow-xl border border-slate-200/80 space-y-5">
            <div className="flex items-center gap-3 border-b border-slate-200 pb-3">
              <div className="p-2 bg-blue-50 text-blue-700 rounded-2xl">
                <User className="w-5 h-5" />
              </div>
              <h2 className="font-bold text-slate-900 text-lg">1. Personal Information</h2>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs sm:text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">First Name *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Juan"
                  value={formData.firstName}
                  onChange={(e) => setFormData({ ...formData, firstName: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Last Name *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Dela Cruz"
                  value={formData.lastName}
                  onChange={(e) => setFormData({ ...formData, lastName: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Middle Name</label>
                <input
                  type="text"
                  placeholder="e.g. Santos"
                  value={formData.middleName}
                  onChange={(e) => setFormData({ ...formData, middleName: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Date of Birth *</label>
                <input
                  type="date"
                  required
                  value={formData.dob}
                  onChange={(e) => {
                    const dobValue = e.target.value;
                    const calculatedAge = calculateAge(dobValue);
                    setFormData({ ...formData, dob: dobValue, age: calculatedAge });
                  }}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white font-medium"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Age</label>
                <input
                  type="text"
                  readOnly
                  placeholder=""
                  value={formData.age ? `${formData.age}` : ''}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-100 text-slate-900 font-medium outline-none cursor-not-allowed"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Place of Birth *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Manila, Philippines"
                  value={formData.pob}
                  onChange={(e) => setFormData({ ...formData, pob: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Civil Status</label>
                <select
                  value={formData.civilStatus}
                  onChange={(e) => setFormData({ ...formData, civilStatus: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
                >
                  <option value="Single">Single</option>
                  <option value="Married">Married</option>
                  <option value="Widowed">Widowed</option>
                </select>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Email Address *</label>
                <input
                  type="email"
                  required
                  placeholder="seafarer@example.com"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Contact Number *</label>
                <input
                  type="tel"
                  required
                  placeholder="+63 917 123 4567"
                  value={formData.phone}
                  onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">SRN Number *</label>
                <input
                  type="text"
                  required
                  placeholder="Seafarer Reg No. (e.g. 012345678)"
                  value={formData.srnNumber}
                  onChange={(e) => setFormData({ ...formData, srnNumber: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                />
              </div>
            </div>
          </div>

          {/* Section 2: Rank & Sea Service */}
          <div className="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-3xl shadow-xl border border-slate-200/80 space-y-5">
            <div className="flex items-center gap-3 border-b border-slate-200 pb-3">
              <div className="p-2 bg-blue-50 text-blue-700 rounded-2xl">
                <Anchor className="w-5 h-5" />
              </div>
              <h2 className="font-bold text-slate-900 text-lg">2. Rank & Sea Experience</h2>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
              <div>
                <label className="block font-semibold text-slate-700 mb-1">Applied Rank / Position *</label>
                <select
                  value={formData.appliedRank}
                  onChange={(e) => setFormData({ ...formData, appliedRank: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white font-medium"
                >
                  <optgroup label="Deck Department">
                    <option value="Captain / Master">Captain / Master</option>
                    <option value="Chief Mate">Chief Mate</option>
                    <option value="2nd Mate">2nd Mate</option>
                    <option value="3rd Mate">3rd Mate</option>
                    <option value="Bosun">Bosun</option>
                    <option value="Able Seaman (AB)">Able Seaman (AB)</option>
                    <option value="Ordinary Seaman (OS)">Ordinary Seaman (OS)</option>
                  </optgroup>
                  <optgroup label="Engine Department">
                    <option value="Chief Engineer">Chief Engineer</option>
                    <option value="2nd Engineer">2nd Engineer</option>
                    <option value="3rd Engineer">3rd Engineer</option>
                    <option value="Oiler / Motorman">Oiler / Motorman</option>
                    <option value="Wiper">Wiper</option>
                  </optgroup>
                  <optgroup label="Catering / Galley">
                    <option value="Chief Cook">Chief Cook</option>
                    <option value="Messman">Messman</option>
                  </optgroup>
                </select>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Preferred Vessel Type *</label>
                <select
                  value={formData.vesselTypePreference}
                  onChange={(e) => setFormData({ ...formData, vesselTypePreference: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white font-medium"
                >
                  <option value="Container Ship">Container Ship</option>
                  <option value="Bulk Carrier">Bulk Carrier</option>
                  <option value="Oil / Chemical Tanker">Oil / Chemical Tanker</option>
                  <option value="LPG / LNG Carrier">LPG / LNG Carrier</option>
                  <option value="General Cargo">General Cargo</option>
                </select>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Years of Sea Experience</label>
                <select
                  value={formData.yearsExperience}
                  onChange={(e) => setFormData({ ...formData, yearsExperience: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
                >
                  <option value="First Timer (Cadet)">First Timer (Cadet)</option>
                  <option value="1-2 years">1-2 years</option>
                  <option value="3-5 years">3-5 years</option>
                  <option value="6-10 years">6-10 years</option>
                  <option value="10+ years">10+ years</option>
                </select>
              </div>

              <div>
                <label className="block font-semibold text-slate-700 mb-1">Last Vessel Served</label>
                <input
                  type="text"
                  placeholder="e.g. M/V Crystal Explorer"
                  value={formData.lastVesselName}
                  onChange={(e) => setFormData({ ...formData, lastVesselName: e.target.value })}
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                />
              </div>
            </div>
          </div>

          {/* Section 3: Document Uploads */}
          <div className="bg-white/95 backdrop-blur-md p-6 sm:p-8 rounded-3xl shadow-xl border border-slate-200/80 space-y-5">
            <div className="flex items-center gap-3 border-b border-slate-200 pb-3">
              <div className="p-2 bg-blue-50 text-blue-700 rounded-2xl">
                <Upload className="w-5 h-5" />
              </div>
              <h2 className="font-bold text-slate-900 text-lg">3. Attach Documents</h2>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
              {/* Upload 1: Passport */}
              <div className="border-2 border-dashed border-slate-300 rounded-2xl p-4 flex flex-col items-center justify-center text-center bg-slate-50/50 hover:bg-blue-50/50 hover:border-blue-400 transition-all">
                <FileText className="w-8 h-8 text-blue-600 mb-2" />
                <p className="font-bold text-slate-800">Passport Copy</p>
                <p className="text-[11px] text-slate-500 mb-3">PDF, JPG up to 5MB</p>
                <label className="cursor-pointer bg-slate-800 hover:bg-slate-900 text-white font-semibold px-3 py-1.5 rounded-xl transition-all">
                  {passportFile ? 'Change File' : 'Browse File'}
                  <input
                    type="file"
                    className="hidden"
                    accept=".pdf,.jpg,.jpeg,.png"
                    onChange={(e) => e.target.files?.[0] && setPassportFile(e.target.files[0])}
                  />
                </label>
                {passportFile && (
                  <p className="text-emerald-700 font-semibold text-[11px] mt-2 truncate max-w-full">
                    ✓ {passportFile.name}
                  </p>
                )}
              </div>

              {/* Upload 2: Seaman's Book */}
              <div className="border-2 border-dashed border-slate-300 rounded-2xl p-4 flex flex-col items-center justify-center text-center bg-slate-50/50 hover:bg-blue-50/50 hover:border-blue-400 transition-all">
                <FileText className="w-8 h-8 text-blue-600 mb-2" />
                <p className="font-bold text-slate-800">Seaman's Book (SIRB)</p>
                <p className="text-[11px] text-slate-500 mb-3">PDF, JPG up to 5MB</p>
                <label className="cursor-pointer bg-slate-800 hover:bg-slate-900 text-white font-semibold px-3 py-1.5 rounded-xl transition-all">
                  {seamanBookFile ? 'Change File' : 'Browse File'}
                  <input
                    type="file"
                    className="hidden"
                    accept=".pdf,.jpg,.jpeg,.png"
                    onChange={(e) => e.target.files?.[0] && setSeamanBookFile(e.target.files[0])}
                  />
                </label>
                {seamanBookFile && (
                  <p className="text-emerald-700 font-semibold text-[11px] mt-2 truncate max-w-full">
                    ✓ {seamanBookFile.name}
                  </p>
                )}
              </div>

              {/* Upload 3: STCW Certs */}
              <div className="border-2 border-dashed border-slate-300 rounded-2xl p-4 flex flex-col items-center justify-center text-center bg-slate-50/50 hover:bg-blue-50/50 hover:border-blue-400 transition-all">
                <FileText className="w-8 h-8 text-blue-600 mb-2" />
                <p className="font-bold text-slate-800">STCW Certificates</p>
                <p className="text-[11px] text-slate-500 mb-3">PDF, JPG up to 5MB</p>
                <label className="cursor-pointer bg-slate-800 hover:bg-slate-900 text-white font-semibold px-3 py-1.5 rounded-xl transition-all">
                  {stcwFile ? 'Change File' : 'Browse File'}
                  <input
                    type="file"
                    className="hidden"
                    accept=".pdf,.jpg,.jpeg,.png"
                    onChange={(e) => e.target.files?.[0] && setStcwFile(e.target.files[0])}
                  />
                </label>
                {stcwFile && (
                  <p className="text-emerald-700 font-semibold text-[11px] mt-2 truncate max-w-full">
                    ✓ {stcwFile.name}
                  </p>
                )}
              </div>
            </div>
          </div>

          {/* Submit Button */}
          <button
            type="submit"
            className="w-full py-4 px-6 rounded-full font-extrabold text-sm sm:text-base md:text-lg tracking-wider uppercase transition-all duration-300 shadow-xl bg-[#0042FB] hover:bg-blue-700 text-white flex items-center justify-center gap-2 cursor-pointer hover:shadow-2xl hover:shadow-blue-500/30 active:scale-[0.99]"
          >
            <span>SUBMIT SEAFARER APPLICATION</span>
            <ArrowRight className="w-5 h-5" />
          </button>
        </form>
      </div>

      <footer className="mt-8 text-center text-xs font-semibold text-slate-700 pb-4">
        © {new Date().getFullYear()} Crystal Shipping Inc. All Rights Reserved.
      </footer>
    </div>
  );
};
