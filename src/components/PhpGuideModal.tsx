import React, { useState } from 'react';
import { X, Copy, Check, Code2, Terminal, FolderTree, Package, ExternalLink, Download } from 'lucide-react';

interface PhpGuideModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export const PhpGuideModal: React.FC<PhpGuideModalProps> = ({ isOpen, onClose }) => {
  const [copiedCode, setCopiedCode] = useState(false);
  const [activeTab, setActiveTab] = useState<'guide' | 'code' | 'extensions'>('guide');

  if (!isOpen) return null;

  const phpSourceCode = `<?php
// index.php - Crystal Shipping Inc. Seafarer Application Form (Terms & Conditions Page)
session_start();

$isAccepted = isset($_POST['terms_accepted']) && $_POST['terms_accepted'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAccepted) {
    // Redirect or proceed to application guide page
    $_SESSION['terms_accepted'] = true;
    header('Location: guide.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crystal Shipping Inc. - Terms & Condition</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(226, 232, 240, 0.5);
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.8);
            border-radius: 9999px;
        }
    </style>
</head>
<body className="bg-slate-100 font-sans min-h-screen flex flex-col">

    <!-- HEADER BAR -->
    <header className="bg-white sticky top-0 z-50 shadow-sm border-b border-slate-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
            <div className="flex items-center gap-3">
                <!-- Company Logo -->
                <div className="w-11 h-11 relative shrink-0">
                    <svg viewBox="0 0 200 200" className="w-full h-full drop-shadow-sm">
                        <circle cx="100" cy="100" r="98" fill="#FFFFFF"/>
                        <path d="M 100 100 H 198 A 98 98 0 0 0 2 100 C 5 32, 52 18, 100 100 Z" fill="#060088"/>
                        <path d="M 100 100 H 2 A 98 98 0 0 0 198 100 C 195 168, 148 182, 100 100 Z" fill="#E50000"/>
                        <circle cx="100" cy="100" r="97" fill="none" stroke="#E2E8F0" stroke-width="2"/>
                    </svg>
                </div>
                <div className="flex flex-col">
                    <span className="font-black tracking-tight text-slate-900 text-base md:text-lg leading-none">
                        CRYSTAL SHIPPING INC.
                    </span>
                    <span className="text-slate-600 font-medium tracking-wide text-xs md:text-sm mt-1 leading-none">
                        Seafarer Application Form
                    </span>
                </div>
            </div>
        </div>
        <!-- RED LINE DIVIDER -->
        <div className="h-1.5 w-full bg-[#D32F2F]"></div>
    </header>

    <!-- MAIN CONTENT WITH CARGO SHIP BACKGROUND -->
    <main className="relative flex-1 flex flex-col justify-between items-center py-6 px-4 md:px-8 bg-cover bg-center" style="background-image: url('assets/cargo_ship.jpg');">
        <!-- BACKGROUND OVERLAY -->
        <div className="absolute inset-0 bg-gradient-to-b from-slate-200/70 via-slate-100/75 to-slate-300/80 backdrop-blur-[2px]"></div>

        <div className="relative z-10 w-full max-w-4xl mx-auto flex flex-col items-center my-auto space-y-5">
            <!-- TITLE -->
            <div className="text-center space-y-1">
                <p className="text-slate-800 text-xs sm:text-sm md:text-base font-semibold tracking-wider uppercase">
                    CRYSTAL SHIPPING INC - IEAC APPLICATION
                </p>
                <h1 className="text-slate-950 text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight uppercase">
                    TERMS & CONDITION
                </h1>
            </div>

            <!-- TERMS CARD BOX -->
            <div className="w-full bg-white/85 backdrop-blur-md rounded-3xl p-6 sm:p-8 md:p-10 shadow-2xl border border-white/60 max-h-[380px] overflow-y-auto custom-scrollbar text-slate-800 space-y-5 text-sm leading-relaxed">
                <h2 className="font-bold text-slate-900 text-base border-b border-slate-300 pb-2">
                    Official Seafarer Recruitment & IEAC Application Terms
                </h2>

                <section>
                    <h3 className="font-bold text-slate-900">1. Purpose and Scope of Application</h3>
                    <p>This Application Form is issued by Crystal Shipping Inc. for seafarers applying for maritime deployment and International Educational and Assessment Clearance (IEAC). All information requested is vital for qualification verification and compliance with POEA and IMO standards.</p>
                </section>

                <section>
                    <h3 className="font-bold text-slate-900">2. Truthfulness and Accuracy Declaration</h3>
                    <p>The applicant declares that all personal details, sea service records, STCW certificates, medical records, and travel documents submitted are authentic, complete, and accurate. Any misrepresentation shall be grounds for immediate disqualification.</p>
                </section>

                <section>
                    <h3 className="font-bold text-slate-900">3. Data Privacy and Consent</h3>
                    <p>Pursuant to Republic Act No. 10173 (Data Privacy Act of 2012), the applicant grants explicit consent to Crystal Shipping Inc. to process and share submitted records strictly for recruitment and deployment processing.</p>
                </section>

                <section>
                    <h3 className="font-bold text-slate-900">4. Zero Placement Fee Policy</h3>
                    <p>Crystal Shipping Inc. adheres strictly to a Zero Placement Fee Policy. No fee or monetary commission shall be solicited or collected from any seafarer at any stage of recruitment.</p>
                </section>
            </div>

            <!-- CHECKBOX & FORM -->
            <form action="index.php" method="POST" className="w-full space-y-5">
                <label className="w-full bg-white rounded-2xl shadow-xl border border-slate-200/90 p-5 flex items-start gap-4 cursor-pointer hover:bg-slate-50 transition-all select-none block">
                    <input 
                        type="checkbox" 
                        id="terms-checkbox" 
                        name="terms_accepted" 
                        value="1" 
                        onchange="toggleProceedBtn()" 
                        className="w-6 h-6 rounded border-2 border-slate-800 text-blue-600 focus:ring-blue-500 cursor-pointer accent-blue-600 mt-0.5"
                    >
                    <span className="text-slate-800 font-semibold text-xs sm:text-sm md:text-base leading-snug">
                        I have read and fully understood the Terms and Conditions stated above. I agree that all the information I will provide in this application is true and correct to the best of my knowledge.
                    </span>
                </label>

                <!-- PROCEED BUTTON -->
                <button 
                    type="submit" 
                    id="proceed-btn" 
                    disabled 
                    className="w-full py-4 px-6 rounded-full font-extrabold text-sm sm:text-base md:text-lg tracking-wider uppercase transition-all duration-300 shadow-xl bg-blue-600/50 text-white cursor-not-allowed opacity-90 block text-center"
                >
                    PROCEED TO APPLICATION GUIDE
                </button>
            </form>
        </div>

        <footer className="mt-6 text-center text-xs font-semibold text-slate-700">
            © <?php echo date('Y'); ?> Crystal Shipping Inc. All Rights Reserved.
        </footer>
    </main>

    <script>
        function toggleProceedBtn() {
            const checkbox = document.getElementById('terms-checkbox');
            const btn = document.getElementById('proceed-btn');
            if (checkbox.checked) {
                btn.disabled = false;
                btn.className = "w-full py-4 px-6 rounded-full font-extrabold text-sm sm:text-base md:text-lg tracking-wider uppercase transition-all duration-300 shadow-xl bg-[#0042FB] hover:bg-blue-700 text-white cursor-pointer hover:shadow-2xl ring-2 ring-blue-400/50 block text-center";
            } else {
                btn.disabled = true;
                btn.className = "w-full py-4 px-6 rounded-full font-extrabold text-sm sm:text-base md:text-lg tracking-wider uppercase transition-all duration-300 shadow-xl bg-blue-600/50 text-white cursor-not-allowed opacity-90 block text-center";
            }
        }
    </script>
</body>
</html>`;

  const copyToClipboard = () => {
    navigator.clipboard.writeText(phpSourceCode);
    setCopiedCode(true);
    setTimeout(() => setCopiedCode(false), 2000);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-200">
      <div className="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden text-slate-200">
        
        {/* Modal Header */}
        <div className="p-5 sm:p-6 bg-slate-900 border-b border-slate-800 flex items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <div className="p-2.5 bg-blue-500/10 text-blue-400 rounded-2xl border border-blue-500/20">
              <Code2 className="w-6 h-6" />
            </div>
            <div>
              <h2 className="text-lg sm:text-xl font-bold text-white tracking-tight">
                VS Code & PHP Setup Guide
              </h2>
              <p className="text-xs text-slate-400">
                Step-by-step instructions, recommended VS Code extensions & full PHP code.
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Tab Switcher */}
        <div className="flex border-b border-slate-800 bg-slate-950/50 px-6 pt-2">
          <button
            onClick={() => setActiveTab('guide')}
            className={`px-4 py-3 text-xs sm:text-sm font-bold border-b-2 transition-all flex items-center gap-2 cursor-pointer ${
              activeTab === 'guide'
                ? 'border-blue-500 text-blue-400'
                : 'border-transparent text-slate-400 hover:text-slate-200'
            }`}
          >
            <Terminal className="w-4 h-4" />
            1. Step-by-Step Instructions
          </button>
          <button
            onClick={() => setActiveTab('extensions')}
            className={`px-4 py-3 text-xs sm:text-sm font-bold border-b-2 transition-all flex items-center gap-2 cursor-pointer ${
              activeTab === 'extensions'
                ? 'border-blue-500 text-blue-400'
                : 'border-transparent text-slate-400 hover:text-slate-200'
            }`}
          >
            <Package className="w-4 h-4" />
            2. Recommended VS Code Extensions
          </button>
          <button
            onClick={() => setActiveTab('code')}
            className={`px-4 py-3 text-xs sm:text-sm font-bold border-b-2 transition-all flex items-center gap-2 cursor-pointer ${
              activeTab === 'code'
                ? 'border-blue-500 text-blue-400'
                : 'border-transparent text-slate-400 hover:text-slate-200'
            }`}
          >
            <Code2 className="w-4 h-4" />
            3. PHP Code (index.php)
          </button>
        </div>

        {/* Modal Content */}
        <div className="p-6 overflow-y-auto flex-1 custom-scrollbar space-y-6 text-sm">
          
          {/* TAB 1: GUIDE */}
          {activeTab === 'guide' && (
            <div className="space-y-6">
              <div className="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/80 space-y-2">
                <h3 className="font-bold text-blue-400 flex items-center gap-2 text-base">
                  <FolderTree className="w-5 h-5" /> Recommended Project Structure
                </h3>
                <pre className="bg-slate-950 p-3.5 rounded-xl font-mono text-xs text-sky-300 overflow-x-auto">
{`crystal-shipping-app/
├── assets/
│   ├── logo.png         <-- (Put your company logo image here)
│   └── cargo_ship.jpg   <-- (Background container ship photo)
├── guide.php            <-- (Application Guide page)
├── form.php             <-- (Seafarer Application form)
└── index.php            <-- (First page: Terms & Conditions)`}
                </pre>
              </div>

              <div className="space-y-4">
                <h3 className="font-bold text-white text-base">Detailed Setup Steps:</h3>

                <div className="space-y-3">
                  <div className="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex gap-3.5">
                    <div className="w-7 h-7 rounded-full bg-blue-600 text-white font-black flex items-center justify-center shrink-0 text-xs">
                      1
                    </div>
                    <div>
                      <h4 className="font-bold text-white text-sm">Install PHP Environment</h4>
                      <p className="text-slate-400 text-xs mt-1">
                        Download & install <strong>XAMPP</strong> (Windows/Mac/Linux) or <strong>Laragon</strong>. This provides PHP and Apache web server out of the box.
                      </p>
                    </div>
                  </div>

                  <div className="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex gap-3.5">
                    <div className="w-7 h-7 rounded-full bg-blue-600 text-white font-black flex items-center justify-center shrink-0 text-xs">
                      2
                    </div>
                    <div>
                      <h4 className="font-bold text-white text-sm">Open Project Folder in VS Code</h4>
                      <p className="text-slate-400 text-xs mt-1">
                        Open VS Code, click <code>File &gt; Open Folder</code>, and select or create <code>crystal-shipping-app/</code>.
                      </p>
                    </div>
                  </div>

                  <div className="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex gap-3.5">
                    <div className="w-7 h-7 rounded-full bg-blue-600 text-white font-black flex items-center justify-center shrink-0 text-xs">
                      3
                    </div>
                    <div>
                      <h4 className="font-bold text-white text-sm">Create <code>index.php</code> and Paste Code</h4>
                      <p className="text-slate-400 text-xs mt-1">
                        Create a new file named <code>index.php</code> and switch to the "3. PHP Code" tab above to copy the source code.
                      </p>
                    </div>
                  </div>

                  <div className="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex gap-3.5">
                    <div className="w-7 h-7 rounded-full bg-blue-600 text-white font-black flex items-center justify-center shrink-0 text-xs">
                      4
                    </div>
                    <div>
                      <h4 className="font-bold text-white text-sm">Run Local Server & Preview</h4>
                      <p className="text-slate-400 text-xs mt-1">
                        In VS Code Terminal, run: <code className="text-emerald-400 bg-slate-900 px-2 py-0.5 rounded">php -S localhost:8000</code> or right click <code>index.php</code> and select <strong>PHP Server: Serve project</strong>.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* TAB 2: EXTENSIONS */}
          {activeTab === 'extensions' && (
            <div className="space-y-4">
              <p className="text-slate-300 text-xs sm:text-sm">
                Install these top extensions in VS Code by opening the Extensions sidebar (<code>Ctrl+Shift+X</code> or <code>Cmd+Shift+X</code>):
              </p>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div className="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-1">
                  <div className="flex items-center justify-between">
                    <span className="font-bold text-blue-400 text-sm">PHP Intelephense</span>
                    <span className="text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full">bmewburn</span>
                  </div>
                  <p className="text-xs text-slate-400">
                    Provides fast PHP code intelligence, auto-completion, parameter hints, and syntax checks.
                  </p>
                </div>

                <div className="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-1">
                  <div className="flex items-center justify-between">
                    <span className="font-bold text-blue-400 text-sm">PHP Server</span>
                    <span className="text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full">btechco</span>
                  </div>
                  <p className="text-xs text-slate-400">
                    Serve your PHP project with 1-click directly inside VS Code without needing complex Apache config.
                  </p>
                </div>

                <div className="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-1">
                  <div className="flex items-center justify-between">
                    <span className="font-bold text-sky-400 text-sm">Tailwind CSS IntelliSense</span>
                    <span className="text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full">bradlc</span>
                  </div>
                  <p className="text-xs text-slate-400">
                    Auto-completes Tailwind utility classes, linting, and color previews in HTML and PHP.
                  </p>
                </div>

                <div className="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-1">
                  <div className="flex items-center justify-between">
                    <span className="font-bold text-emerald-400 text-sm">Prettier - Code Formatter</span>
                    <span className="text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full">esbenp</span>
                  </div>
                  <p className="text-xs text-slate-400">
                    Keeps HTML and JavaScript code clean and perfectly formatted on save.
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* TAB 3: CODE */}
          {activeTab === 'code' && (
            <div className="space-y-3">
              <div className="flex items-center justify-between bg-slate-950 p-3 rounded-2xl border border-slate-800">
                <span className="text-xs font-mono text-slate-400">index.php</span>
                <button
                  onClick={copyToClipboard}
                  className="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl transition-all cursor-pointer shadow-md"
                >
                  {copiedCode ? <Check className="w-4 h-4 text-emerald-300" /> : <Copy className="w-4 h-4" />}
                  <span>{copiedCode ? 'Copied PHP Code!' : 'Copy index.php Code'}</span>
                </button>
              </div>

              <div className="bg-slate-950 p-4 rounded-2xl border border-slate-800 max-h-[380px] overflow-y-auto custom-scrollbar">
                <pre className="font-mono text-xs text-slate-300 leading-relaxed whitespace-pre-wrap">
                  {phpSourceCode}
                </pre>
              </div>
            </div>
          )}
        </div>

        {/* Modal Footer */}
        <div className="p-4 bg-slate-950 border-t border-slate-800 flex justify-between items-center text-xs text-slate-400">
          <span>Crystal Shipping Inc. Seafarer Application Portal</span>
          <button
            onClick={onClose}
            className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl transition-all cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  );
};
