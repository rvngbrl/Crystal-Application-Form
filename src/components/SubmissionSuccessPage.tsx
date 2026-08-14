import React from 'react';
import containerShipBg from '../assets/images/cargo_container_ship_1785313984424.jpg';
import { SeafarerFormData } from '../types';
import { CheckCircle2, FileCheck, Anchor, Printer, RefreshCw, Mail, Phone } from 'lucide-react';

interface SubmissionSuccessPageProps {
  formData: SeafarerFormData;
  onReset: () => void;
}

export const SubmissionSuccessPage: React.FC<SubmissionSuccessPageProps> = ({ formData, onReset }) => {
  const referenceNumber = `CSI-${Math.floor(100000 + Math.random() * 900000)}`;
  const dateSubmitted = new Date().toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });

  return (
    <div className="relative min-h-[calc(100vh-70px)] py-10 px-4 md:px-8 font-sans">
      {/* Background Image with Overlay */}
      <div 
        className="fixed inset-0 bg-cover bg-center bg-no-repeat -z-10"
        style={{ backgroundImage: `url(${containerShipBg})` }}
      >
        <div className="absolute inset-0 bg-gradient-to-b from-slate-200/80 via-slate-100/85 to-slate-300/90 backdrop-blur-[2px]" />
      </div>

      <div className="w-full max-w-2xl mx-auto space-y-6">
        {/* Success Card */}
        <div className="bg-white/95 backdrop-blur-md p-6 sm:p-10 rounded-3xl shadow-2xl border border-white/60 text-center space-y-6">
          <div className="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
            <CheckCircle2 className="w-10 h-10" />
          </div>

          <div>
            <span className="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200 uppercase tracking-wider">
              APPLICATION SUBMITTED SUCCESSFULLY
            </span>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3 tracking-tight">
              Thank You, Seafarer!
            </h1>
            <p className="text-slate-600 text-xs sm:text-sm mt-2">
              Your application for <strong>{formData.appliedRank || 'Able Seaman'}</strong> has been received by Crystal Shipping Inc. Recruitment Division.
            </p>
          </div>

          {/* Reference Slip Box */}
          <div className="bg-slate-50 border border-slate-200 rounded-2xl p-5 text-left space-y-3">
            <div className="flex justify-between items-center border-b border-slate-200 pb-2">
              <span className="text-xs font-semibold text-slate-500 uppercase">Application Ref No.</span>
              <span className="text-base font-black text-blue-700 tracking-wider font-mono">{referenceNumber}</span>
            </div>
            
            <div className="grid grid-cols-2 gap-2 text-xs text-slate-700">
              <div>
                <span className="text-slate-400 block">Applicant Name</span>
                <span className="font-bold text-slate-900">{formData.firstName} {formData.lastName}</span>
              </div>
              <div>
                <span className="text-slate-400 block">Date Submitted</span>
                <span className="font-bold text-slate-900">{dateSubmitted}</span>
              </div>
              <div>
                <span className="text-slate-400 block">Date of Birth / Age</span>
                <span className="font-bold text-slate-900">
                  {formData.dob ? `${formData.dob}${formData.age ? ` (${formData.age})` : ''}` : 'N/A'}
                </span>
              </div>
              <div>
                <span className="text-slate-400 block">SRN Number</span>
                <span className="font-bold text-slate-900">{formData.srnNumber || 'N/A'}</span>
              </div>
              <div>
                <span className="text-slate-400 block">Contact Email</span>
                <span className="font-bold text-slate-900">{formData.email}</span>
              </div>
            </div>
          </div>

          {/* Next Steps */}
          <div className="text-left space-y-2 bg-blue-50/70 p-4 rounded-2xl border border-blue-100 text-xs text-slate-700">
            <h3 className="font-bold text-blue-900 flex items-center gap-1.5">
              <Anchor className="w-4 h-4 text-blue-600" /> What Happens Next?
            </h3>
            <p>
              1. Our crewing officers will review your credentials and sea service history.
            </p>
            <p>
              2. Qualified candidates will receive an interview invitation via email or SMS.
            </p>
            <p>
              3. Please keep your original documents ready for physical verification.
            </p>
          </div>

          {/* Buttons */}
          <div className="flex flex-col sm:flex-row gap-3 pt-2">
            <button
              onClick={() => window.print()}
              className="flex-1 py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all cursor-pointer shadow-md"
            >
              <Printer className="w-4 h-4" />
              Print Reference Slip
            </button>
            <button
              onClick={onReset}
              className="flex-1 py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all cursor-pointer"
            >
              <RefreshCw className="w-4 h-4" />
              Submit Another Application
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
