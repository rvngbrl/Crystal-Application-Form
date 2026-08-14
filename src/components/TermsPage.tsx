import React, { useState } from 'react';
import containerShipBg from '../assets/images/cargo_container_ship_1785313984424.jpg';
import { ShieldCheck, ArrowRight, FileText, CheckCircle2 } from 'lucide-react';

interface TermsPageProps {
  onProceed: () => void;
}

export const TermsPage: React.FC<TermsPageProps> = ({ onProceed }) => {
  const [isAccepted, setIsAccepted] = useState(false);

  return (
    <div className="relative min-h-[calc(100vh-70px)] flex flex-col justify-between items-center py-6 px-4 md:px-8 overflow-hidden font-sans">
      {/* Background Image with Overlay */}
      <div 
        className="absolute inset-0 bg-cover bg-center bg-no-repeat -z-10"
        style={{ backgroundImage: `url(${containerShipBg})` }}
      >
        {/* Soft light veil layer matching photo atmosphere */}
        <div className="absolute inset-0 bg-gradient-to-b from-slate-200/60 via-slate-100/70 to-slate-300/80 backdrop-blur-[2px]" />
      </div>

      <div className="w-full max-w-4xl mx-auto flex flex-col items-center my-auto space-y-5">
        {/* Title Header Section */}
        <div className="text-center space-y-1">
          <p className="text-slate-800 text-xs sm:text-sm md:text-base font-semibold tracking-wider uppercase">
            CRYSTAL SHIPPING INC - IEAC APPLICATION
          </p>
          <h1 className="text-slate-950 text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight uppercase">
            TERMS & CONDITION
          </h1>
        </div>

        {/* Main Terms Container (Frosted Semi-Translucent Rounded Box) */}
        <div className="w-full bg-white/85 backdrop-blur-md rounded-3xl p-6 sm:p-8 md:p-10 shadow-2xl border border-white/60 max-h-[380px] sm:max-h-[420px] overflow-y-auto custom-scrollbar text-slate-800 space-y-6 text-sm leading-relaxed">
          <div className="flex items-center gap-3 border-b border-slate-300/80 pb-3">
            <ShieldCheck className="w-6 h-6 text-blue-700 shrink-0" />
            <h2 className="font-bold text-slate-900 text-base sm:text-lg">
              Official Seafarer Recruitment & IEAC Application Terms
            </h2>
          </div>

          <section className="space-y-2">
            <h3 className="font-bold text-slate-900 text-sm">1. Purpose and Scope of Application</h3>
            <p>
              This Application Form is issued by <strong>Crystal Shipping Inc.</strong> for seafarers applying for maritime deployment and International Educational and Assessment Clearance (IEAC). All information requested is vital for evaluation, qualification verification, and compliance with Philippine Overseas Employment Administration (POEA) and International Maritime Organization (IMO) standards.
            </p>
          </section>

          <section className="space-y-2">
            <h3 className="font-bold text-slate-900 text-sm">2. Truthfulness and Accuracy Declaration</h3>
            <p>
              By completing this form, the applicant declares under penalty of administrative or legal disqualification that all personal details, sea service records, STCW certificates, medical records, and travel documents submitted are authentic, complete, and accurate. Any misrepresentation or falsification of records shall be grounds for immediate rejection or termination of employment contract.
            </p>
          </section>

          <section className="space-y-2">
            <h3 className="font-bold text-slate-900 text-sm">3. Data Privacy and Consent</h3>
            <p>
              Pursuant to Republic Act No. 10173 (Data Privacy Act of 2012), the applicant grants explicit consent to Crystal Shipping Inc. to collect, store, process, and share submitted documents with accredited medical facilities, principal vessel owners, training centers, government regulatory bodies (MARINA, DMW/POEA, PCG), and immigration authorities strictly for recruitment and deployment processing.
            </p>
          </section>

          <section className="space-y-2">
            <h3 className="font-bold text-slate-900 text-sm">4. Qualifications and Medical Fitness</h3>
            <p>
              Applicants must meet the physical, mental, and medical standards required for sea service under MLC 2006 regulations. Final deployment is contingent upon passing the accredited Pre-Employment Medical Examination (PEME), drug and alcohol screening, and securing valid STCW certificate endorsements.
            </p>
          </section>

          <section className="space-y-2">
            <h3 className="font-bold text-slate-900 text-sm">5. Code of Conduct and Zero Placement Fee Policy</h3>
            <p>
              Crystal Shipping Inc. adheres strictly to a <strong>Zero Placement Fee Policy</strong>. No fee, charge, or monetary commission shall be solicited or collected from any seafarer at any stage of recruitment. Any unauthorized demand for payment by any representative should be reported immediately to management.
            </p>
          </section>
        </div>

        {/* Checkbox Card Container */}
        <label 
          htmlFor="terms-checkbox"
          className="w-full bg-white rounded-2xl shadow-xl border border-slate-200/90 p-5 sm:p-6 flex items-start gap-4 cursor-pointer hover:bg-slate-50/90 transition-all select-none"
        >
          <div className="pt-0.5 shrink-0">
            <input
              id="terms-checkbox"
              type="checkbox"
              checked={isAccepted}
              onChange={(e) => setIsAccepted(e.target.checked)}
              className="w-6 h-6 rounded border-2 border-slate-800 text-blue-600 focus:ring-blue-500 cursor-pointer accent-blue-600 transition-all"
            />
          </div>
          <span className="text-slate-800 font-semibold text-xs sm:text-sm md:text-base leading-snug">
            I have read and fully understood the Terms and Conditions stated above. I agree that all the information I will provide in this application is true and correct to the best of my knowledge.
          </span>
        </label>

        {/* Action Button: PROCEED TO APPLICATION GUIDE */}
        <button
          onClick={() => {
            if (isAccepted) {
              onProceed();
            }
          }}
          disabled={!isAccepted}
          className={`w-full py-4 px-6 rounded-full font-extrabold text-sm sm:text-base md:text-lg tracking-wider uppercase transition-all duration-300 shadow-xl flex items-center justify-center gap-2 ${
            isAccepted
              ? 'bg-[#0042FB] hover:bg-blue-700 active:scale-[0.99] text-white cursor-pointer hover:shadow-2xl hover:shadow-blue-500/30 ring-2 ring-blue-400/50'
              : 'bg-[#1D4ED8]/50 text-white/70 cursor-not-allowed opacity-90'
          }`}
        >
          <span>PROCEED TO APPLICATION GUIDE</span>
          {isAccepted && <ArrowRight className="w-5 h-5" />}
        </button>
      </div>

      {/* Footer subtle text */}
      <footer className="mt-6 text-center text-xs font-semibold text-slate-700 drop-shadow-sm">
        © {new Date().getFullYear()} Crystal Shipping Inc. All Rights Reserved. | Seafarer IEAC Application Portal
      </footer>
    </div>
  );
};
