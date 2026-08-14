import React, { useState } from 'react';
import { Header } from './components/Header';
import { TermsPage } from './components/TermsPage';
import { ApplicationGuidePage } from './components/ApplicationGuidePage';
import { ApplicationFormPage } from './components/ApplicationFormPage';
import { SubmissionSuccessPage } from './components/SubmissionSuccessPage';
import { PhpGuideModal } from './components/PhpGuideModal';
import { Step, SeafarerFormData } from './types';

export default function App() {
  const [currentStep, setCurrentStep] = useState<Step>('terms');
  const [isPhpGuideOpen, setIsPhpGuideOpen] = useState<boolean>(false);
  const [submittedData, setSubmittedData] = useState<SeafarerFormData | null>(null);
  const [customLogoUrl, setCustomLogoUrl] = useState<string | null>(() => {
    return localStorage.getItem('csi_custom_logo') || null;
  });

  const handleLogoChange = (url: string) => {
    setCustomLogoUrl(url);
    try {
      localStorage.setItem('csi_custom_logo', url);
    } catch {
      // ignore quota errors if image data url is very large
    }
  };

  const handleFormSubmit = (data: SeafarerFormData) => {
    setSubmittedData(data);
    setCurrentStep('submitted');
  };

  return (
    <div className="min-h-screen bg-slate-100 text-slate-900 flex flex-col font-sans selection:bg-blue-600 selection:text-white">
      {/* Top Header with Logo & Red Accent Line */}
      <Header
        currentStep={currentStep}
        onNavigate={(step) => setCurrentStep(step)}
        onTogglePhpGuide={() => setIsPhpGuideOpen(true)}
        customLogoUrl={customLogoUrl}
        onLogoChange={handleLogoChange}
      />

      {/* Main Page Rendering */}
      <main className="flex-1">
        {currentStep === 'terms' && (
          <TermsPage onProceed={() => setCurrentStep('guide')} />
        )}

        {currentStep === 'guide' && (
          <ApplicationGuidePage
            onProceed={() => setCurrentStep('form')}
            onBack={() => setCurrentStep('terms')}
          />
        )}

        {currentStep === 'form' && (
          <ApplicationFormPage
            onSubmit={handleFormSubmit}
            onBack={() => setCurrentStep('guide')}
          />
        )}

        {currentStep === 'submitted' && submittedData && (
          <SubmissionSuccessPage
            formData={submittedData}
            onReset={() => setCurrentStep('terms')}
          />
        )}
      </main>

      {/* PHP & VS Code Helper Modal */}
      <PhpGuideModal
        isOpen={isPhpGuideOpen}
        onClose={() => setIsPhpGuideOpen(false)}
      />
    </div>
  );
}
