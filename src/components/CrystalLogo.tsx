import React, { useRef } from 'react';
import { Camera } from 'lucide-react';
import crystalLogoImg from '../assets/images/logo.png';

interface CrystalLogoProps {
  size?: 'sm' | 'md' | 'lg';
  className?: string;
  customLogoUrl?: string | null;
  onLogoChange?: (url: string) => void;
  allowUpload?: boolean;
}

export const CrystalLogo: React.FC<CrystalLogoProps> = ({
  size = 'md',
  className = '',
  customLogoUrl = null,
  onLogoChange,
  allowUpload = true,
}) => {
  const fileInputRef = useRef<HTMLInputElement>(null);

  const iconSize = size === 'sm' ? 'w-8 h-8' : size === 'lg' ? 'w-14 h-14' : 'w-11 h-11';
  const titleSize = size === 'sm' ? 'text-sm' : size === 'lg' ? 'text-xl' : 'text-base md:text-lg';
  const subtitleSize = size === 'sm' ? 'text-[10px]' : size === 'lg' ? 'text-xs' : 'text-xs md:text-sm';

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file && onLogoChange) {
      const reader = new FileReader();
      reader.onload = (event) => {
        if (event.target?.result) {
          onLogoChange(event.target.result as string);
        }
      };
      reader.readAsDataURL(file);
    }
  };

  // Uses custom uploaded logo if uploaded via UI, otherwise defaults to your new image
  const logoSrc = customLogoUrl || crystalLogoImg;

  return (
    <div className={`flex items-center gap-3 ${className}`}>
      {/* Hidden File Input for uploading custom logo */}
      {allowUpload && (
        <input
          type="file"
          ref={fileInputRef}
          accept="image/*"
          className="hidden"
          onChange={handleFileChange}
        />
      )}

      {/* Logo Container with Upload Overlay */}
      <div 
        className={`relative ${iconSize} shrink-0 group ${allowUpload ? 'cursor-pointer' : ''}`}
        onClick={() => allowUpload && fileInputRef.current?.click()}
        title={allowUpload ? "Click to upload your custom company logo image" : undefined}
      >
        <img
          src={logoSrc}
          alt="Crystal Shipping Inc. Logo"
          className="w-full h-full object-contain rounded-full drop-shadow-sm border border-slate-200/80 bg-white"
          referrerPolicy="no-referrer"
        />

        {/* Upload Overlay Badge on Hover */}
        {allowUpload && (
          <div className="absolute inset-0 bg-slate-900/60 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
            <Camera className="w-4 h-4" />
          </div>
        )}
      </div>

      <div className="flex flex-col justify-center">
        <div className="flex items-center gap-1.5">
          <span className={`font-black tracking-tight text-slate-900 leading-none ${titleSize}`}>
            CRYSTAL SHIPPING INC.
          </span>
        </div>
        <span className={`text-slate-600 font-medium tracking-wide mt-1 leading-none ${subtitleSize}`}>
          Seafarer Application Form
        </span>
      </div>
    </div>
  );
};