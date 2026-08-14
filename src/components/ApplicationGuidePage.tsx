import React from 'react';
import containerShipBg from '../assets/images/cargo_container_ship_1785313984424.jpg';
import { FileText, ArrowRight, ArrowLeft, CheckCircle2, AlertCircle, FileCheck, Anchor, ShieldCheck } from 'lucide-react';

interface ApplicationGuidePageProps {
  onProceed: () => void;
  onBack: () => void;
}

export const ApplicationGuidePage: React.FC<ApplicationGuidePageProps> = ({ onProceed, onBack }) => {
  return (
    <div className="relative min-h-[calc(100vh-70px)] flex flex-col justify-between items-center py-8 px-4 md:px-8 font-sans">
      {/* Background Image with Overlay */}
      <div 
        className="absolute inset-0 bg-cover bg-center bg-no-repeat -z-10"
        style={{ backgroundImage: `url(${containerShipBg})` }}
      >
        <div className="absolute inset-0 bg-gradient-to-b from-slate-200/70 via-slate-100/80 to-slate-300/90 backdrop-blur-[2px]" />
      </div>

      <div className="w-full max-w-4xl mx-auto space-y-6 my-auto">
        {/* Navigation & Header */}
        <div className="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white/90 backdrop-blur-md p-4 sm:p-6 rounded-3xl shadow-xl border border-white/60">
          <div>
            <p className="text-blue-700 text-xs font-bold uppercase tracking-wider">STEP 2 OF 4</p>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
              Application Guide & Document Checklist
            </h1>
            <p className="text-slate-600 text-xs sm:text-sm mt-1">
              Please review all required documents and guidelines before completing your application.
            </p>
          </div>
          <button
            onClick={onBack}
            className="flex items-center gap-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-xl transition-all shrink-0"
          >
            <ArrowLeft className="w-4 h-4" />
            Back to Terms
          </button>
        </div>

        {/* Requirements Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Box 1: Required Original Documents */}
          <div className="bg-white/90 backdrop-blur-md p-6 rounded-3xl shadow-lg border border-slate-200/80 space-y-4">
            <div className="flex items-center gap-3 border-b border-slate-100 pb-3">
              <div className="p-2 bg-blue-50 text-blue-700 rounded-2xl">
                <FileCheck className="w-5 h-5" />
              </div>
              <h2 className="font-bold text-slate-900 text-base">Required Standard Documents</h2>
            </div>
            <ul className="space-y-2.5 text-xs sm:text-sm text-slate-700">
              <li className="flex items-start gap-2.5">
                <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                <span><strong>Philippine Passport</strong> (Valid for at least 1 year)</span>
              </li>
              <li className="flex items-start gap-2.5">
                <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                <span><strong>Seaman's Identification & Record Book (SIRB/SID)</strong></span>
              </li>
              <li className="flex items-start gap-2.5">
                <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                <span><strong>STCW '95 / 2010 Certificates</strong> & Certificate of Proficiency (COP)</span>
              </li>
              <li className="flex items-start gap-2.5">
                <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                <span><strong>Valid PEME Medical Certificate</strong> (DOH & Principal accredited)</span>
              </li>
              <li className="flex items-start gap-2.5">
                <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                <span><strong>Sea Service Records / Discharge Book</strong> (Last 3 years)</span>
              </li>
            </ul>
          </div>

          {/* Box 2: Important Instructions */}
          <div className="bg-white/90 backdrop-blur-md p-6 rounded-3xl shadow-lg border border-slate-200/80 space-y-4">
            <div className="flex items-center gap-3 border-b border-slate-100 pb-3">
              <div className="p-2 bg-amber-50 text-amber-700 rounded-2xl">
                <AlertCircle className="w-5 h-5" />
              </div>
              <h2 className="font-bold text-slate-900 text-base">Application Submission Guidelines</h2>
            </div>
            <ul className="space-y-2.5 text-xs sm:text-sm text-slate-700">
              <li className="flex items-start gap-2.5">
                <Anchor className="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                <span>Ensure scanned documents are in <strong>PDF or high-resolution JPG/PNG format</strong>.</span>
              </li>
              <li className="flex items-start gap-2.5">
                <Anchor className="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                <span>Double-check your <strong>Seafarer Registration Number (SRN)</strong> before submitting.</span>
              </li>
              <li className="flex items-start gap-2.5">
                <Anchor className="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                <span>Keep your active mobile number and email updated for interview invitations.</span>
              </li>
              <li className="flex items-start gap-2.5">
                <ShieldCheck className="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                <span>All recruitment services are <strong>100% free of charge</strong> (No placement fees).</span>
              </li>
            </ul>
          </div>
        </div>

        {/* Action Button */}
        <button
          onClick={onProceed}
          className="w-full py-4 px-6 rounded-full font-extrabold text-sm sm:text-base md:text-lg tracking-wider uppercase transition-all duration-300 shadow-xl bg-[#0042FB] hover:bg-blue-700 text-white flex items-center justify-center gap-2 cursor-pointer hover:shadow-2xl hover:shadow-blue-500/30"
        >
          <span>PROCEED TO SEAFARER APPLICATION FORM</span>
          <ArrowRight className="w-5 h-5" />
        </button>
      </div>

      <footer className="mt-6 text-center text-xs font-semibold text-slate-700">
        © {new Date().getFullYear()} Crystal Shipping Inc. All Rights Reserved.
      </footer>
    </div>
  );
};
