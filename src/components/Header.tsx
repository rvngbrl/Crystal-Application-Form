import React from 'react';
import { Code2 } from 'lucide-react';
import { Step } from '../types';
import { CrystalLogo } from './CrystalLogo';

interface HeaderProps {
  currentStep: Step;
  onNavigate: (step: Step) => void;
  onTogglePhpGuide: () => void;
  customLogoUrl: string | null;
  onLogoChange: (url: string) => void;
}

const steps: { id: Step; label: string }[] = [
  { id: 'terms', label: 'Terms' },
  { id: 'guide', label: 'Guide' },
  { id: 'form', label: 'Application' },
  { id: 'submitted', label: 'Complete' },
];

export const Header: React.FC<HeaderProps> = ({
  currentStep,
  onNavigate,
  onTogglePhpGuide,
  customLogoUrl,
  onLogoChange,
}) => {
  const currentIndex = steps.findIndex((step) => step.id === currentStep);

  return (
    <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
      <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6">
        <CrystalLogo
          customLogoUrl={customLogoUrl}
          onLogoChange={onLogoChange}
          allowUpload
        />

        <nav aria-label="Application steps" className="hidden items-center gap-1 md:flex">
          {steps.map((step, index) => (
            <button
              key={step.id}
              type="button"
              onClick={() => onNavigate(step.id)}
              aria-current={currentStep === step.id ? 'step' : undefined}
              className={`rounded-lg px-3 py-2 text-xs font-bold transition-colors ${
                currentStep === step.id
                  ? 'bg-blue-700 text-white'
                  : index < currentIndex
                    ? 'text-blue-800 hover:bg-blue-50'
                    : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'
              }`}
            >
              {step.label}
            </button>
          ))}
        </nav>

        <button
          type="button"
          onClick={onTogglePhpGuide}
          className="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 transition-colors hover:bg-slate-100"
        >
          <Code2 className="h-4 w-4" aria-hidden="true" />
          PHP Guide
        </button>
      </div>

      <div className="h-1 w-full bg-slate-200" aria-hidden="true">
        <div
          className="h-full bg-red-600 transition-[width] duration-300"
          style={{ width: `${((currentIndex + 1) / steps.length) * 100}%` }}
        />
      </div>
    </header>
  );
};