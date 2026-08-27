<?php
/**
 * Crystal Shipping Inc. - Seafarer Application Portal
 * Full Pure PHP Implementation (No Node/npm required)
 * Designed for XAMPP, WAMP, or `php -S localhost:8000`
 */

session_start();

// Handle Form Submission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    $_SESSION['application_data'] = $_POST;
    $_SESSION['submitted_at'] = date('F j, Y, g:i a');
    header('Location: index.php?step=success');
    exit;
}

$step = $_GET['step'] ?? 'terms';
$appData = $_SESSION['application_data'] ?? [];
$submittedAt = $_SESSION['submitted_at'] ?? '';
$todayDate = date('Y-m-d'); // used to cap date pickers so tomorrow/future dates are disabled
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crystal Shipping Inc. - Seafarer Application Portal</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .bg-ocean-port {
            background-image: linear-gradient(to bottom, rgba(15, 23, 42, 0.65), rgba(15, 23, 42, 0.75)), url('src/assets/images/application-background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.2); border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.5); border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.8); }
    </style>
</head>
<body class="min-h-screen bg-slate-900 bg-ocean-port text-slate-800 flex flex-col font-sans selection:bg-blue-600 selection:text-white">

    <!-- Header Bar -->
    <header class="bg-white border-b border-slate-200 shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <!-- Company Logo -->
                <div class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 drop-shadow-sm">
                    <img src="src/assets/images/logo.png" alt="Crystal Shipping Inc. Logo" class="w-full h-full object-contain rounded-full border border-slate-200/80 bg-white">
                </div>
                <div class="flex flex-col">
                    <span class="font-black tracking-tight text-slate-900 text-base md:text-lg leading-none">
                        CRYSTAL SHIPPING INC.
                    </span>
                    <span class="text-[11px] md:text-xs font-semibold tracking-wider text-slate-600 uppercase mt-0.5">
                        Seafarer Application Form
                    </span>
                </div>
            </div>

            <!-- Quick Step Badge -->
            <div class="hidden sm:flex items-center gap-2 bg-slate-100 px-3 py-1.5 rounded-full text-xs font-bold text-slate-700">

            </div>
        </div>
        <div class="h-1.5 w-full bg-slate-200">
            <div id="headerProgressBar" class="h-full bg-[#E50000] transition-all duration-500 ease-out" style="width: 0%;"></div>
        </div>
    </header>

    <!-- Main Section -->
    <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto w-full">

        <!-- Client-Side Form Wizard Handler -->
        <form id="seafarerForm" action="index.php" method="POST" class="space-y-6">
            <input type="hidden" name="action" value="submit_application">

            <!-- ================= STEP 0: TERMS & CONDITIONS ================= -->
            <div id="step-terms" class="step-page space-y-6 <?php echo $step !== 'terms' && $step !== '' ? 'hidden' : ''; ?>">
                <div class="text-center space-y-1">
                    <p class="text-white/90 text-xs sm:text-sm font-bold tracking-wider uppercase drop-shadow-sm">
                        CRYSTAL SHIPPING INC - IEAC APPLICATION
                    </p>
                    <h1 class="text-white text-3xl sm:text-4xl md:text-5xl font-black tracking-tight uppercase drop-shadow-md">
                        TERMS & CONDITION
                    </h1>
                </div>

                <!-- Glassmorphism Scrollable Box -->
                <div class="w-full bg-white/90 backdrop-blur-md rounded-3xl p-6 sm:p-8 md:p-10 shadow-2xl border border-white/40 max-h-[420px] overflow-y-auto custom-scrollbar text-slate-800 space-y-5 text-sm leading-relaxed">
                    <p class="font-bold text-slate-900 text-base border-b border-slate-300 pb-2">
                        Official Seafarer Recruitment & IEAC Application Terms
                    </p>

                    <p><strong>1. Purpose and Scope of Application:</strong> This Application Form is issued by Crystal Shipping Inc. for seafarers applying for maritime deployment and International Educational and Assessment Clearance (IEAC). All information requested is vital for evaluation, qualification verification, and compliance with POEA, DMW, and IMO standards.</p>

                    <p><strong>2. Accuracy and Authenticity Declaration:</strong> By completing this form, the applicant declares under penalty of administrative or legal disqualification that all personal details, sea service records, STCW certificates, medical records, and travel documents submitted are authentic, complete, and accurate.</p>

                    <p><strong>3. Data Privacy Consent:</strong> I hereby certify that all information that I will encode are correct and accurate and that I give my consent to processing of my personal data in accordance with the DATA PRIVACY Act of the Philippines and its Implementing Rules and Regulations (IRR) from September 9, 2016 for employment on vessels of foreign shipowners.</p>

                    <p><strong>4. Zero Placement Fee Policy:</strong> Crystal Shipping Inc. adheres strictly to a <strong>Zero Placement Fee Policy</strong>. No fee, charge, or monetary commission shall be solicited or collected from any seafarer at any stage of recruitment.</p>
                </div>

                <!-- Terms Checkbox Card -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-xl border border-slate-200 flex items-start gap-4">
                    <input type="checkbox" id="termsCheck" class="w-6 h-6 mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer accent-blue-600 shrink-0" onchange="toggleTermsBtn()">
                    <label for="termsCheck" class="text-slate-800 font-semibold text-xs sm:text-sm leading-snug cursor-pointer select-none">
                        I have read and fully understood the Terms and Conditions stated above. I agree that all the information I will provide in this application is true and correct to the best of my knowledge.
                    </label>
                </div>

                <button type="button" id="proceedGuideBtn" disabled onclick="goToStep('guide')" class="w-full py-4 px-6 rounded-full font-black text-sm sm:text-base tracking-wider uppercase transition-all duration-300 shadow-xl bg-blue-600 text-white flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-blue-700">
                    PROCEED TO APPLICATION GUIDE
                </button>
            </div>

            

            <!-- ================= STEP 1: HOW THE APPLICATION WORKS ================= -->
            <div id="step-guide" class="step-page space-y-10 hidden">
                <div class="text-center space-y-1">
                    <p class="text-white/90 text-xs sm:text-sm font-bold tracking-wider uppercase drop-shadow-sm">
                        CRYSTAL SHIPPING INC - IEAC APPLICATION
                    </p>
                    <h1 class="text-white text-3xl sm:text-4xl md:text-5xl font-black tracking-tight uppercase drop-shadow-md">
                        HOW THE APPLICATION WORKS
                    </h1>
                </div>

                <!-- 3 Numbered Steps -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Step 01 -->
                    <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-2xl border border-white/50 relative pt-12 text-slate-800">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-14 h-14 bg-[#E30000] text-white rounded-full flex items-center justify-center font-black text-xl shadow-lg border-4 border-white">
                            01
                        </div>
                        <div class="flex items-center gap-3 mb-3 border-b border-slate-200 pb-3">
                            <span class="text-2xl">👤</span>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 tracking-wider uppercase">STEP 1</p>
                                <h3 class="font-black text-slate-900 text-base">Personal Information</h3>
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 mb-3">Provide your basic personal details including your full name, date of birth, contact information, civil status, and social media accounts.</p>
                        <ul class="text-xs space-y-1.5 text-slate-700 font-medium">
                            <li>• Full name (Last, First, Middle)</li>
                            <li>• Date & Place of Birth</li>
                            <li>• Complete home address</li>
                            <li>• Email address & contact number</li>
                            <li>• Civil status & wife's name (if applicable)</li>
                        </ul>
                    </div>

                    <!-- Step 02 -->
                    <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-2xl border border-white/50 relative pt-12 text-slate-800">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-14 h-14 bg-[#FF0000] text-white rounded-full flex items-center justify-center font-black text-xl shadow-lg border-4 border-white">
                            02
                        </div>
                        <div class="flex items-center gap-3 mb-3 border-b border-slate-200 pb-3">
                            <span class="text-2xl">📑</span>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 tracking-wider uppercase">STEP 2</p>
                                <h3 class="font-black text-slate-900 text-base">Documents & Licenses</h3>
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 mb-3">Enter the details of your maritime documents, licenses, certifications, and training certificates. Make sure all expiry dates are current.</p>
                        <ul class="text-xs space-y-1.5 text-slate-700 font-medium">
                            <li>• Primary documents: Passport, SIRB, SID</li>
                            <li>• COC / License (MARINA-issued)</li>
                            <li>• E-Registration details</li>
                            <li>• Training certificates (BST, GMDSS, ECDIS)</li>
                            <li>• Flag State endorsements</li>
                        </ul>
                    </div>

                    <!-- Step 03 -->
                    <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-2xl border border-white/50 relative pt-12 text-slate-800">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-14 h-14 bg-[#FF6666] text-white rounded-full flex items-center justify-center font-black text-xl shadow-lg border-4 border-white">
                            03
                        </div>
                        <div class="flex items-center gap-3 mb-3 border-b border-slate-200 pb-3">
                            <span class="text-2xl">🚢</span>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 tracking-wider uppercase">STEP 3</p>
                                <h3 class="font-black text-slate-900 text-base">Shipboard Experiences</h3>
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 mb-3">List your sea service history starting from your most recent vessel assignment down to your earliest. Provide at least one completed entry.</p>
                        <ul class="text-xs space-y-1.5 text-slate-700 font-medium">
                            <li>• Principal name & vessel name</li>
                            <li>• Flag, nationality & manning agency</li>
                            <li>• Rank / position onboard</li>
                            <li>• Vessel type, GRT & KW/BHP</li>
                            <li>• Contract dates (From - To)</li>
                        </ul>
                    </div>
                </div>

                <!-- Before You Start Card -->
                <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 shadow-xl border border-white/60 relative overflow-hidden flex flex-col md:flex-row gap-6 items-center">
                    <div class="w-full md:w-auto bg-[#E50000] text-white font-black text-sm px-6 py-4 rounded-2xl text-center shrink-0 uppercase tracking-wider">
                        Before you start - have these ready!
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-xs font-semibold text-slate-800 w-full">
                        <div class="flex items-center gap-2">✓ Recent ID Photo (Digital)</div>
                        <div class="flex items-center gap-2">✓ Passport number & expiry date</div>
                        <div class="flex items-center gap-2">✓ SIRB / Seaman's Book Details</div>
                        <div class="flex items-center gap-2">✓ COC / License details</div>
                        <div class="flex items-center gap-2">✓ Training certificates numbers & expiry dates</div>
                        <div class="flex items-center gap-2">✓ Previous vessel names & contract dates</div>
                        <div class="flex items-center gap-2">✓ Active email address</div>
                    </div>
                </div>

                <!-- Nav Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <button type="button" onclick="goToStep('terms')" class="w-full sm:w-auto py-3.5 px-8 rounded-full font-extrabold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg">
                        BACK TO TERMS
                    </button>
                    <button type="button" onclick="goToStep('personal')" class="w-full sm:w-auto py-3.5 px-8 rounded-full font-extrabold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg">
                        PROCEED TO APPLICATION
                    </button>
                </div>
            </div>

            <!-- ================= STEP 2: PERSONAL INFORMATION ================= -->
            <div id="step-personal" class="step-page space-y-6 hidden">
                <!-- Page Title Header -->
                <div class="bg-white/90 backdrop-blur-md rounded-2xl p-4 sm:p-5 shadow-lg border border-white/50 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center text-2xl font-black shrink-0">
                        👤
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">STEP 1 out of 3</p>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight uppercase">PERSONAL INFORMATION</h2>
                        <p class="text-xs text-slate-600">Fields marked with <span class="text-red-600 font-bold">*</span> are required.</p>
                    </div>
                </div>

                <!-- Main Form Container -->
                <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 sm:p-8 shadow-2xl border border-white/60 space-y-8 text-slate-800">
                    
                    <!-- Applicant's Profile -->
                    <div class="space-y-4">
                        <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                            APPLICANT'S PROFILE
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Photo Upload -->
                            <div class="md:col-span-1 flex flex-col items-center justify-center border-2 border-dashed border-slate-300 rounded-2xl p-4 bg-slate-50 hover:bg-slate-100 transition-all text-center min-h-[160px] cursor-pointer" onclick="document.getElementById('photoInput').click()">
                                <input type="file" id="photoInput" name="photo" accept="image/*" class="hidden" onchange="previewPhoto(event)">
                                <div id="photoPreview" class="flex flex-col items-center">
                                    <span class="text-3xl mb-1">📷</span>
                                    <span class="text-xs font-bold text-slate-600 uppercase">CLICK TO UPLOAD</span>
                                    <span class="text-[10px] text-slate-400">ID Photo (JPG/PNG)</span>
                                </div>
                            </div>

                            <!-- Inputs -->
                            <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">POSITION APPLIED <span class="text-red-600">*</span></label>
                                    <select name="position_applied" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="">Select Position...</option>
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
                                        <optgroup label="Catering">
                                            <option value="Chief Cook">Chief Cook</option>
                                            <option value="Messman">Messman</option>
                                        </optgroup>
                                    </select>
                                </div>

                                 <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">DATE OF APPLICATION </label>
                                    <input type="date" name="application_date" value="<?php echo date('Y-m-d'); ?>" required readonly
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-100 text-xs font-semibold outline-none required-field pointer-events-none text-slate-600"
                                        onkeydown="return false">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">HOW DID YOU KNOW ABOUT CRYSTAL? <span class="text-red-600">*</span></label>
                                    <select name="how_known" id="howKnownSelect" required onchange="toggleHowKnownFields()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="">Select Option...</option>
                                        <option value="Facebook / Social Media">Facebook / Social Media</option>
                                        <option value="Referral / Recommendation">Referral / Recommendation</option>
                                        <option value="Walk-in Applicant">Walk-in Applicant</option>
                                        <option value="Official Website">Official Website</option>
                                        <option value="Maritime Job Fair">Maritime Job Fair</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>

                                <div id="referralNameField" class="sm:col-span-2 hidden">
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">NAME WHO REFERRED YOU</label>
                                    <input type="text" name="referral_name" placeholder="Enter Referrer's Name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>

                                <div id="othersHowKnownField" class="sm:col-span-2 hidden">
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">WHERE DID YOU SEE CRYSTAL?</label>
                                    <input type="text" name="others_how_known" placeholder="Please specify" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div class="space-y-4">
                        <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                            FULL NAME
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">LAST NAME <span class="text-red-600">*</span></label>
                                <input type="text" name="last_name" required placeholder="Enter Last Name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">FIRST NAME <span class="text-red-600">*</span></label>
                                <input type="text" name="first_name" required placeholder="Enter First Name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">MIDDLE NAME</label>
                                <input type="text" name="middle_name" placeholder="Enter Middle Name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SUFFIX</label>
                                <input type="text" name="suffix" placeholder="e.g. Jr., III" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Birth & Address -->
                    <div class="space-y-4">
                        <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                            BIRTH & ADDRESS
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">DATE OF BIRTH <span class="text-red-600">*</span></label>
                            <input type="date" id="dobInput" name="dob" required max="<?php echo $todayDate; ?>" onchange="calcAge()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            <p class="text-[10px] text-slate-500 mt-1">Applicant must be at least 18 years old.</p>
                            <p id="age_error" class="text-red-600 text-xs font-semibold mt-1 hidden">⚠ You must be at least 18 years old to apply.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">PLACE OF BIRTH <span class="text-red-600">*</span></label>
                            <input type="text" name="pob" required placeholder="Enter Place of Birth" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">AGE</label>
                            <input type="text" id="ageField" name="age" readonly placeholder="[AUTO CALCULATED AGE]" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-100 text-xs font-semibold text-slate-600 outline-none">
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">CURRENT ADDRESS <span class="text-red-600">*</span></label>
                            <input type="text" name="address" required placeholder="House No., Street, Barangay, City, Province" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const dobInput = document.getElementById('dobInput');
                        const today = new Date();
                        const eighteenYearsAgo = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
                        const maxDate = eighteenYearsAgo.toISOString().split('T')[0];
                        dobInput.setAttribute('max', maxDate);
                    });

                    function calcAge() {
                        const dobInput = document.getElementById('dobInput');
                        const errorMsg = document.getElementById('age_error');
                        const ageField = document.getElementById('ageField');
                        const dob = new Date(dobInput.value);
                        const today = new Date();

                        let age = today.getFullYear() - dob.getFullYear();
                        const monthDiff = today.getMonth() - dob.getMonth();
                        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                            age--;
                        }

                        if (!dobInput.value || isNaN(age)) {
                            ageField.value = '';
                            ageField.classList.remove('border-red-500', 'bg-red-50', 'text-red-700');
                            ageField.classList.add('bg-slate-100', 'text-slate-600');
                            errorMsg.classList.add('hidden');
                            dobInput.setCustomValidity('');
                            return age;
                        }

                        ageField.value = age + ' yrs old';

                        if (age < 18) {
                            errorMsg.classList.remove('hidden');
                            dobInput.setCustomValidity('You must be at least 18 years old to apply.');
                            ageField.classList.add('border-red-500', 'bg-red-50', 'text-red-700');
                            ageField.classList.remove('bg-slate-100', 'text-slate-600');
                        } else {
                            errorMsg.classList.add('hidden');
                            dobInput.setCustomValidity('');
                            ageField.classList.remove('border-red-500', 'bg-red-50', 'text-red-700');
                            ageField.classList.add('bg-slate-100', 'text-slate-600');
                        }

                        return age;
                    }
                    </script>
                    </div>

                    <!-- Contact Information -->
                    <div class="space-y-4">
                        <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                            CONTACT INFORMATION
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">EMAIL ADDRESS <span class="text-red-600">*</span></label>
                                <input type="email" name="email" required placeholder="seafarer@example.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">CONTACT NUMBER <span class="text-red-600">*</span></label>
                                <input type="tel" name="phone" id="phoneInput" required placeholder="e.g. 09171234567"
                                    inputmode="numeric"
                                    pattern="[0-9]{11}"
                                    maxlength="11"
                                    aria-describedby="phone_error"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11); validatePhone(this);"
                                    onblur="validatePhone(this)"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                                <p id="phone_error" class="text-red-600 text-xs font-semibold mt-1 hidden">⚠ Contact number must be exactly 11 digits.</p>
                            </div>

                            <script>
                            function validatePhone(input) {
                                const errorMsg = document.getElementById('phone_error');
                                if (input.value.length > 0 && input.value.length !== 11) {
                                    errorMsg.classList.remove('hidden');
                                    input.setCustomValidity('Contact number must be exactly 11 digits.');
                                } else {
                                    errorMsg.classList.add('hidden');
                                    input.setCustomValidity('');
                                }
                            }
                            </script>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">CIVIL STATUS <span class="text-red-600">*</span></label>
                                <select name="civil_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Separated">Separated</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">WIFE'S NAME <span class="text-slate-400 font-normal">(if applicable)</span></label>
                                <input type="text" name="wife_name" placeholder="Enter Spouse Name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Government IDs -->
                    <div class="space-y-4">
                        <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                            GOVERNMENT IDS
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">PAG-IBIG NO.</label>
                                <input type="text" name="pagibig_no" placeholder="Enter Pag-IBIG Number" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SSS NO.</label>
                                <input type="text" name="sss_no" placeholder="Enter SSS Number" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">PHILHEALTH NO.</label>
                                <input type="text" name="philhealth_no" placeholder="Enter PhilHealth Number" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Social Media & Messaging -->
                    <div class="space-y-4">
                        <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                            SOCIAL MEDIA & MESSAGING
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">FACEBOOK</label>
                                <input type="text" name="facebook" placeholder="FB Profile Link / Username" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">VIBER</label>
                                <input type="text" name="viber" placeholder="Viber Number" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">WHATSAPP</label>
                                <input type="text" name="whatsapp" placeholder="WhatsApp Number" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SKYPE</label>
                                <input type="text" name="skype" placeholder="Skype ID" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Nav Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <button type="button" onclick="goToStep('guide')" class="w-full sm:w-auto py-3.5 px-8 rounded-full font-extrabold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg">
                        &lt; APPLICATION GUIDE
                    </button>
                    <button type="button" onclick="goToNextStep('documents')" class="w-full sm:w-auto py-3.5 px-8 rounded-full font-extrabold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg">
                        DOCUMENTS &gt;
                    </button>
                </div>
            </div>

            <!-- ================= STEP 3: DOCUMENTS & LICENSES ================= -->
            <div id="step-documents" class="step-page space-y-6 hidden">
                <!-- Page Title Header -->
                <div class="bg-white/90 backdrop-blur-md rounded-2xl p-4 sm:p-5 shadow-lg border border-white/50 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center text-2xl font-black shrink-0">
                        📑
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">STEP 2 out of 3</p>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight uppercase">DOCUMENTS &amp; LICENSES</h2>
                        <p class="text-xs text-slate-600">Fields marked with <span class="text-red-600 font-bold">*</span> are required.</p>
                    </div>
                </div>

                <!-- Main Form Container -->
                <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 sm:p-8 shadow-2xl border border-white/60 space-y-8 text-slate-800">
                    
                    <!-- Primary Documents -->
                    <div class="space-y-4">
                        <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                            PRIMARY DOCUMENTS
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 text-slate-700 uppercase font-black">
                                        <th class="p-3 rounded-l-xl">DOCUMENT NAME</th>
                                        <th class="p-3">DOCUMENT NO. <span class="text-red-600">*</span></th>
                                        <th class="p-3">ISSUE DATE</th>
                                        <th class="p-3">EXPIRY DATE</th>
                                        <th class="p-3 rounded-r-xl">PLACE ISSUED <span class="text-red-600">*</span></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 font-medium">
                                    <!-- Passport -->
                                    <tr>
                                        <td class="p-3 font-bold text-slate-900">PASSPORT</td>
                                        <td class="p-2"><input type="text" name="passport_no" required placeholder="Passport No." class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="date" name="passport_issue" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="date" name="passport_expiry" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="text" name="passport_place" required placeholder="Place Issued" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                    </tr>
                                    <!-- Seaman's Book -->
                                    <tr>
                                        <td class="p-3 font-bold text-slate-900">SEAMAN'S BOOK</td>
                                        <td class="p-2"><input type="text" name="sirb_no" required placeholder="SIRB / SID No." class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="date" name="sirb_issue" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="date" name="sirb_expiry" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="text" name="sirb_place" required placeholder="Place Issued" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                    </tr>
                                    <!-- GOC License -->
                                    <tr>
                                        <td class="p-3 font-bold text-slate-900">GOC LICENSE</td>
                                        <td class="p-2"><input type="text" name="goc_no" placeholder="GOC License No." class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="date" name="goc_issue" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="date" name="goc_expiry" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="text" name="goc_place" placeholder="Place Issued" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- COC / License -->
                    <div class="space-y-4">
                        <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                            COC / LICENSE
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">LICENSE TYPE</label>
                                <select name="coc_type" class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-xs">
                                    <option value="">Choose...</option>
                                    <option value="Master Mariner">Master Mariner</option>
                                    <option value="Chief Mate">Chief Mate</option>
                                    <option value="OIC Navigational Watch">OIC Navigational Watch</option>
                                    <option value="Chief Engineer Officer">Chief Engineer Officer</option>
                                    <option value="Second Engineer Officer">Second Engineer Officer</option>
                                    <option value="OIC Engineering Watch">OIC Engineering Watch</option>
                                    <option value="Able Seafarer Deck">Able Seafarer Deck</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">NO.</label>
                                <input type="text" name="coc_no" placeholder="COC / License No." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">ISSUE DATE</label>
                                <input type="date" name="coc_issue" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">EXPIRY DATE</label>
                                <input type="date" name="coc_expiry" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- E-Registration -->
                    <div class="space-y-4">
                        <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                            E-REGISTRATION AND SID 
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">E-REGISTRATION NO.</label>
                                <input type="text" name="e_reg_no" placeholder="Enter E-Reg Number" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SID NO.</label>
                                <input type="text" name="sid_no" placeholder="Enter SID Number" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs">
                            </div>
                        </div>    
                    </div>

                    <!-- Training Certificates -->
                   <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                                TRAINING CERTIFICATES
                            </h3>
                        </div>
                        <div id="trainingContainer" class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                                <input type="text" name="training_name[]" placeholder="Certificate Name (e.g. BST, ECDIS)" class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                                <input type="text" name="training_no[]" placeholder="Certificate No." class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                                <input type="date" name="training_issue[]" class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                                <input type="date" name="training_expiry[]" class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="button" onclick="addTrainingRow()" id="addTrainingBtn" class="bg-blue-600 text-white font-bold text-xs px-4 py-1.5 rounded-lg hover:bg-blue-700">
                                + ADD CERTIFICATE
                            </button>
                            <p id="training_limit_msg" class="text-red-600 text-xs font-semibold hidden">Maximum of 5 certificates reached.</p>
                        </div>
                    </div>

                </div>

                <!-- Nav Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <button type="button" onclick="goToStep('personal')" class="w-full sm:w-auto py-3.5 px-8 rounded-full font-extrabold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg">
                        &lt; PERSONAL INFORMATION
                    </button>
                    <button type="button" onclick="goToNextStep('shipboard')" class="w-full sm:w-auto py-3.5 px-8 rounded-full font-extrabold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg">
                        NEXT: SHIPBOARD &gt;
                    </button>
                </div>
            </div>

            <!-- ================= STEP 4: SHIPBOARD EXPERIENCES ================= -->
            <div id="step-shipboard" class="step-page space-y-6 hidden">
                <!-- Page Title Header -->
                <div class="bg-white/90 backdrop-blur-md rounded-2xl p-4 sm:p-5 shadow-lg border border-white/50 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center text-2xl font-black shrink-0">
                        🚢
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">STEP 3 out of 3</p>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight uppercase">SHIPBOARD EXPERIENCES</h2>
                        <p class="text-xs text-slate-600">Fields marked with <span class="text-red-600 font-bold">*</span> are required. Please arrange in chronological order starting from present to past service.</p>
                    </div>
                </div>

                <!-- Main Form Container -->
                <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 sm:p-8 shadow-2xl border border-white/60 space-y-8 text-slate-800">
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                                EXPERIENCES
                            </h3>
                            <button type="button" onclick="addExperienceRow()" class="bg-blue-600 text-white font-bold text-xs px-4 py-1.5 rounded-lg hover:bg-blue-700">
                                + ADD EXPERIENCE
                            </button>
                        </div>
                    <p id="experienceError" class="hidden text-red-600 text-xs font-semibold mt-2">
                        Please fill out all fields in the current Experience before adding a new one.
                    </p>
                        <div id="experienceContainer" class="space-y-6">
                            <!-- Experience Entry #1 -->
                            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                                <p class="text-xs font-black text-blue-700 uppercase">EXPERIENCE #1 <span class="text-red-600">*</span> </p>
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">PRINCIPAL NAME</label>
                                        <input type="text" name="principal_name[]" placeholder="e.g. Evergreen Marine" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">VESSEL NAME <span class="text-red-600">*</span></label>
                                        <input type="text" name="vessel_name[]" required placeholder="e.g. M/V Crystal Grace" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">FLAG</label>
                                        <input type="text" name="vessel_flag[]" placeholder="e.g. Panama, Liberia" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">NATIONALITY</label>
                                        <input type="text" name="vessel_nat[]" placeholder="e.g. Japanese" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">MANNING AGENCY</label>
                                        <input type="text" name="manning_agency[]" placeholder="Agency Name" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">RANK</label>
                                        <input type="text" name="exp_rank[]" placeholder="Rank Onboard" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">VESSEL TYPE</label>
                                        <input type="text" name="vessel_type[]" placeholder="Container / Tanker" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">GRT</label>
                                        <input type="text" name="vessel_grt[]" placeholder="Gross Tonnage" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>

                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">KW/BHP</label>
                                        <input type="text" name="engine_power[]" placeholder="Engine Power" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">SALARY (USD)</label>
                                        <input type="text" name="salary[]" placeholder="Monthly Salary" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">DATE FROM</label>
                                        <input type="date" name="date_from[]" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">DATE TO</label>
                                        <input type="date" name="date_to[]" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="space-y-4">
                        <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                            ADDITIONAL DETAILS
                        </h3>
                        <textarea name="additional_details" rows="4" placeholder="Please give any additional details you feel we should know in connection with your application..." class="w-full p-4 rounded-2xl border border-slate-300 text-xs bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>

                        <div class="flex items-start gap-3 pt-2">
                            <input type="checkbox" id="certifyCheck" required class="w-5 h-5 mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer accent-blue-600 shrink-0">
                            <label for="certifyCheck" class="text-xs font-semibold text-slate-700 leading-relaxed cursor-pointer select-none">
                                I certify that all my inputted information is correct and complete to the best of my knowledge. I understand that providing false information may result in cancellation of my application.
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Nav Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <button type="button" onclick="goToStep('documents')" class="w-full sm:w-auto py-3.5 px-8 rounded-full font-extrabold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg">
                        &lt; DOCUMENTS
                    </button>
                    <button type="button" onclick="goToNextStep('review')" class="w-full sm:w-auto py-3.5 px-8 rounded-full font-extrabold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg">
                        NEXT: REVIEW APPLICATION &gt;
                    </button>
                </div>
            </div>

            <!-- ================= STEP 5: REVIEW YOUR APPLICATION ================= -->
            <div id="step-review" class="step-page space-y-6 hidden">
                <div class="text-left space-y-1">
                    <p class="text-white/90 text-xs sm:text-sm font-bold tracking-wider uppercase drop-shadow-sm">
                        FINAL REVIEW
                    </p>
                    <h1 class="text-white text-2xl sm:text-3xl md:text-4xl font-black tracking-tight uppercase drop-shadow-md">
                        REVIEW YOUR APPLICATION
                    </h1>
                    <p class="text-white/80 text-xs sm:text-sm font-medium">
                        Check all your details carefully. Use the tabs to switch between sections. Once submitted, changes cannot be made.
                    </p>
                </div>

                <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl border border-white/60 overflow-hidden text-slate-800">

                    <!-- Review Tabs -->
                    <div class="flex flex-col sm:flex-row w-full">
                        <button type="button" onclick="showReviewTab('personal')" id="reviewTabBtn-personal" class="flex-1 py-3 px-4 text-xs sm:text-sm font-black uppercase tracking-wider transition-all bg-blue-600 text-white">
                            PERSONAL INFORMATION
                        </button>
                        <button type="button" onclick="showReviewTab('documents')" id="reviewTabBtn-documents" class="flex-1 py-3 px-4 text-xs sm:text-sm font-black uppercase tracking-wider transition-all bg-slate-200 text-slate-700 hover:bg-slate-300">
                            DOCUMENTS &amp; LICENSES
                        </button>
                        <button type="button" onclick="showReviewTab('shipboard')" id="reviewTabBtn-shipboard" class="flex-1 py-3 px-4 text-xs sm:text-sm font-black uppercase tracking-wider transition-all bg-slate-200 text-slate-700 hover:bg-slate-300">
                            SHIPBOARD EXPERIENCES
                        </button>
                    </div>

                    <!-- Review Tab Content -->
                    <div class="p-6 sm:p-8">
                        <div id="reviewTabContent-personal" class="space-y-6 text-xs sm:text-sm"></div>
                        <div id="reviewTabContent-documents" class="space-y-6 text-xs sm:text-sm hidden"></div>
                        <div id="reviewTabContent-shipboard" class="space-y-6 text-xs sm:text-sm hidden"></div>

                        <!-- Tab Nav Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-200">
                            <button type="button" onclick="editCurrentReviewTab()" class="py-3 px-6 rounded-full font-extrabold text-xs sm:text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg flex items-center gap-2">
                                EDIT ✏️
                            </button>
                            <button type="button" id="reviewNextBtn" onclick="nextReviewTab()" class="py-3 px-8 rounded-full font-extrabold text-xs sm:text-sm bg-slate-200 text-slate-800 hover:bg-slate-300 transition-all shadow-lg">
                                NEXT
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>

        <?php if ($step === 'success'): ?>
        <!-- ================= STEP 6: SUBMISSION SUCCESS ================= -->
        <div class="space-y-6 max-w-3xl mx-auto">
            <div class="bg-white/95 backdrop-blur-md rounded-3xl p-8 sm:p-10 shadow-2xl border border-white/60 text-center space-y-6 text-slate-800">
                <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-4xl font-black shadow-inner">
                    ✓
                </div>
                <div>
                    <span class="bg-emerald-100 text-emerald-800 text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-wider">
                        APPLICATION SUBMITTED
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 mt-3 tracking-tight">
                        Thank You, <?php echo htmlspecialchars($appData['first_name'] ?? 'Applicant'); ?>!
                    </h1>
                    <p class="text-slate-600 text-xs sm:text-sm mt-1">
                        Your application has been received and forwarded to Crystal Shipping Inc. Recruitment Officers.
                    </p>
                </div>

                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 text-left text-xs space-y-2 font-medium">
                    <p class="font-bold text-slate-900 border-b pb-2 text-sm uppercase">Applicant Overview</p>
                    <p><strong>Position Applied:</strong> <?php echo htmlspecialchars($appData['position_applied'] ?? 'N/A'); ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars(($appData['first_name'] ?? '') . ' ' . ($appData['middle_name'] ?? '') . ' ' . ($appData['last_name'] ?? '')); ?></p>
                    <p><strong>Email Address:</strong> <?php echo htmlspecialchars($appData['email'] ?? 'N/A'); ?></p>
                    <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($appData['phone'] ?? 'N/A'); ?></p>
                    <p><strong>Submission Time:</strong> <?php echo $submittedAt; ?></p>
                </div>

                <a href="index.php?step=terms" class="inline-block py-3.5 px-8 rounded-full font-extrabold text-xs sm:text-sm bg-slate-900 text-white hover:bg-slate-800 transition-all shadow-lg">
                    Submit Another Application
                </a>
            </div>
        </div>
        <?php endif; ?>

    </main>

    <!-- Submission Confirmation Modal -->
    <div id="confirmSubmitModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-8 text-center space-y-5">
            <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto text-2xl font-black">
                ?
            </div>
            <div>
                <h3 class="text-lg sm:text-xl font-black text-slate-900">Are you sure with the information you provided?</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-2">Please double-check your details. Once submitted, changes cannot be made.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="button" onclick="closeConfirmModal()" class="w-full py-3 px-6 rounded-full font-extrabold text-sm bg-slate-200 text-slate-800 hover:bg-slate-300 transition-all">
                    No, Go Back
                </button>
                <button type="button" onclick="confirmSubmitApplication()" class="w-full py-3 px-6 rounded-full font-extrabold text-sm bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-lg">
                    Yes, Submit
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 text-center text-xs font-semibold text-white/80 border-t border-white/10 bg-slate-950/60 backdrop-blur-md">
        © <?php echo date('Y'); ?> Crystal Shipping Inc. All Rights Reserved. | Seafarer IEAC Application Portal
    </footer>

    <!-- JavaScript Navigation & Dynamics -->
    <script>
        function toggleTermsBtn() {
            const check = document.getElementById('termsCheck');
            const btn = document.getElementById('proceedGuideBtn');
            btn.disabled = !check.checked;
        }

        function toggleHowKnownFields() {
            const select = document.getElementById('howKnownSelect');
            const referralField = document.getElementById('referralNameField');
            const othersField = document.getElementById('othersHowKnownField');

            referralField.classList.add('hidden');
            othersField.classList.add('hidden');

            if (select.value === 'Referral / Recommendation') {
                referralField.classList.remove('hidden');
            } else if (select.value === 'Others') {
                othersField.classList.remove('hidden');
            }
        }

        // Order of steps used to calculate how full the header progress bar should be.
        // "terms" is intentionally excluded — the bar stays empty until the user
        // proceeds past Terms & Conditions, then starts counting from "guide" onward.
        const stepOrder = ['guide', 'personal', 'documents', 'shipboard', 'review'];

        function updateHeaderProgress(stepId) {
            const bar = document.getElementById('headerProgressBar');
            if (!bar) return;

            if (stepId === 'terms') {
                bar.style.width = '0%';
                return;
            }

            const idx = stepOrder.indexOf(stepId);
            if (idx === -1) {
                // Not a wizard step (e.g. success page) — treat as fully complete
                bar.style.width = '100%';
                return;
            }

            const pct = ((idx + 1) / stepOrder.length) * 100;
            bar.style.width = pct + '%';
        }

        function goToStep(stepId) {
            document.querySelectorAll('.step-page').forEach(el => el.classList.add('hidden'));
            const target = document.getElementById('step-' + stepId);
            if (target) {
                target.classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            if (stepId === 'review') {
                showReviewTab('personal');
                populateReview();
            }
            updateHeaderProgress(stepId);
        }

        // Set the initial progress bar fill based on whichever step is showing on page load
        document.addEventListener('DOMContentLoaded', function() {
            const visibleStep = document.querySelector('.step-page:not(.hidden)');
            if (visibleStep) {
                updateHeaderProgress(visibleStep.id.replace('step-', ''));
            } else {
                // No wizard step visible means we're on the success page
                updateHeaderProgress('success');
            }
        });

        function validateExperiences() {

            const experiences = document.querySelectorAll("#experienceContainer > div");
            const errorMsg = document.getElementById("experienceError");
            errorMsg.classList.add("hidden");
            for (const experience of experiences) {
                const fields = experience.querySelectorAll("input, select, textarea");
                for (const field of fields) {
                    if (field.value.trim() === "") {
                        errorMsg.classList.remove("hidden");
                        field.focus();
                        return false;
                    }
                }
            }
            return true;
        }
        // Blocks navigation to the next step if any required (*) field on the
        // CURRENT visible step is empty/invalid, or if age validation fails.
        function goToNextStep(nextStepId) {
            // Find the currently visible step only
            const currentStep = document.querySelector('.step-page:not(.hidden)');

            if (!currentStep) {
                goToStep(nextStepId);
                return;
            }

            // Check only inputs/selects/textareas inside the current step
            const fields = currentStep.querySelectorAll(
                'input, select, textarea'
             );

             for (const field of fields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    return;
                }
            }

            // Leaving Shipboard?
                if (nextStepId === "review") {

                    if (!validateExperiences()) {
                        return;
                    }

                }

            // Current step is valid, so proceed
            goToStep(nextStepId);
        }

        function previewPhoto(event) {
            const input = event.target;
            const file = input.files[0];
            if (!file) return;

            const maxSizeBytes = 10 * 1024 * 1024; // 10MB

            if (!file.type.startsWith('image/')) {
                alert('Please upload an image file only (JPG, PNG, etc.).');
                input.value = '';
                return;
            }

            if (file.size > maxSizeBytes) {
                alert('Image is too large. Maximum file size is 10MB.');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photoPreview').innerHTML = `
                    <img src="${e.target.result}" class="w-24 h-24 object-cover rounded-xl shadow-md border-2 border-white mb-1">
                    <span class="text-[10px] text-blue-600 font-bold">Change Photo</span>
                `;
            }
            reader.readAsDataURL(file);
        }

        const MAX_TRAINING_ROWS = 5;

            function addTrainingRow() {
                const container = document.getElementById('trainingContainer');
                const rowCount = container.children.length;

                if (rowCount >= MAX_TRAINING_ROWS) {
                    document.getElementById('training_limit_msg').classList.remove('hidden');
                    document.getElementById('addTrainingBtn').disabled = true;
                    document.getElementById('addTrainingBtn').classList.add('opacity-50', 'cursor-not-allowed');
                    return;
                }

                const div = document.createElement('div');
                div.className = 'relative grid grid-cols-1 sm:grid-cols-4 gap-3 bg-slate-50 p-3 pt-7 rounded-xl border border-slate-200';
                div.innerHTML = `
                    <input type="text" name="training_name[]" placeholder="Certificate Name" class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    <input type="text" name="training_no[]" placeholder="Certificate No." class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    <input type="date" name="training_issue[]" class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    <input type="date" name="training_expiry[]" class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                `;

                // Add a remove button, positioned top-right of the row
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.innerHTML = '✕ Remove';
                removeBtn.className = 'absolute top-2 right-3 text-red-600 text-xs font-bold hover:underline';
                removeBtn.onclick = function() {
                    if (confirm('Are you sure you want to remove this certificate?')) {
                    div.remove();
                    checkTrainingLimit();
                    }
                };
                div.appendChild(removeBtn);

                container.appendChild(div);
                checkTrainingLimit();
            }

            function checkTrainingLimit() {
                const container = document.getElementById('trainingContainer');
                const rowCount = container.children.length;
                const addBtn = document.getElementById('addTrainingBtn');
                const limitMsg = document.getElementById('training_limit_msg');

                if (rowCount >= MAX_TRAINING_ROWS) {
                    addBtn.disabled = true;
                    addBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    limitMsg.classList.remove('hidden');
                } else {
                    addBtn.disabled = false;
                    addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    limitMsg.classList.add('hidden');
                }
            }

            function addExperienceRow() {

                const container = document.getElementById('experienceContainer');

                // Validate the current (last) experience before adding another
                const errorMsg = document.getElementById("experienceError");
                errorMsg.classList.add("hidden");

                const lastExperience = container.lastElementChild;

                if (lastExperience) {
                    const fields = lastExperience.querySelectorAll("input, select, textarea");

                    for (const field of fields) {
                        if (field.value.trim() === "") {
                            errorMsg.classList.remove("hidden");
                            field.focus();
                            return;
                        }
                    }
                }

                const count = container.children.length + 1;
                const div = document.createElement('div');
                div.className = 'bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4';

                div.innerHTML = `
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-black text-blue-700 uppercase">
                            EXPERIENCE #${count} <span class="text-red-600">*</span>
                        </p>
                        <button type="button" class="remove-experience-btn text-red-600 text-xs font-bold hover:underline">
                            ✕ Remove
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">PRINCIPAL NAME</label>
                            <input type="text" name="principal_name[]" placeholder="e.g. Evergreen" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">VESSEL NAME *</label>
                            <input type="text" name="vessel_name[]" required placeholder="e.g. M/V Star" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">FLAG</label>
                            <input type="text" name="vessel_flag[]" placeholder="e.g. Panama" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">NATIONALITY</label>
                            <input type="text" name="vessel_nat[]" placeholder="e.g. Japanese" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">MANNING AGENCY</label>
                            <input type="text" name="manning_agency[]" placeholder="Agency" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">RANK</label>
                            <input type="text" name="exp_rank[]" placeholder="Rank" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">VESSEL TYPE</label>
                            <input type="text" name="vessel_type[]" placeholder="Vessel Type" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">GRT</label>
                            <input type="text" name="vessel_grt[]" placeholder="GRT" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">KW/BHP</label>
                            <input type="text" name="engine_power[]" placeholder="KW/BHP" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">SALARY (USD)</label>
                            <input type="text" name="salary[]" placeholder="Salary" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">DATE FROM</label>
                            <input type="date" name="date_from[]" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">DATE TO</label>
                            <input type="date" name="date_to[]" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                    </div>
                        `;

                        const removeBtn = div.querySelector('.remove-experience-btn');
                        removeBtn.addEventListener('click', function() {
                            if (confirm('Are you sure you want to remove this experience?')) {
                                div.remove();
                                renumberExperiences();
                            }
                        });

                        container.appendChild(div);
                    }

                    function renumberExperiences() {
                        const container = document.getElementById('experienceContainer');
                        const cards = container.children;
                        for (let i = 0; i < cards.length; i++) {
                            const numberLabel = cards[i].querySelector('p');
                            if (numberLabel && numberLabel.textContent.includes('EXPERIENCE #')) {
                                numberLabel.innerHTML = `EXPERIENCE #${i + 1} <span class="text-red-600">*</span>`;
                            }
                        }
                    }

        // ============ REVIEW TABS ============

        const reviewTabs = ['personal', 'documents', 'shipboard'];
        let currentReviewTab = 'personal';

        function showReviewTab(tabName) {
            currentReviewTab = tabName;

            reviewTabs.forEach(t => {
                const btn = document.getElementById('reviewTabBtn-' + t);
                const content = document.getElementById('reviewTabContent-' + t);
                if (t === tabName) {
                    btn.classList.add('bg-blue-600', 'text-white');
                    btn.classList.remove('bg-slate-200', 'text-slate-700', 'hover:bg-slate-300');
                    content.classList.remove('hidden');
                } else {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('bg-slate-200', 'text-slate-700', 'hover:bg-slate-300');
                    content.classList.add('hidden');
                }
            });

            const nextBtn = document.getElementById('reviewNextBtn');
            if (tabName === 'shipboard') {
                nextBtn.textContent = 'SUBMIT APPLICATION NOW ✓';
                nextBtn.classList.remove('bg-slate-200', 'text-slate-800', 'hover:bg-slate-300');
                nextBtn.classList.add('bg-emerald-600', 'text-white', 'hover:bg-emerald-700');
            } else {
                nextBtn.textContent = 'NEXT';
                nextBtn.classList.remove('bg-emerald-600', 'text-white', 'hover:bg-emerald-700');
                nextBtn.classList.add('bg-slate-200', 'text-slate-800', 'hover:bg-slate-300');
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function nextReviewTab() {
            const idx = reviewTabs.indexOf(currentReviewTab);
            if (idx < reviewTabs.length - 1) {
                showReviewTab(reviewTabs[idx + 1]);
            } else {
                // Final tab — ask for confirmation before submitting
                openConfirmModal();
            }
        }

        function openConfirmModal() {
            document.getElementById('confirmSubmitModal').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirmSubmitModal').classList.add('hidden');
        }

        function confirmSubmitApplication() {
            closeConfirmModal();
            document.getElementById('seafarerForm').requestSubmit();
        }

        function editCurrentReviewTab() {
            goToStep(currentReviewTab);
        }

        // Builds one label/value block
        function reviewField(label, value) {
            const hasValue = value && value.toString().trim() !== '';
            return `
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">${label}</p>
                    <p class="text-sm ${hasValue ? 'font-semibold text-slate-800' : 'font-normal italic text-slate-400'}">${hasValue ? value : 'Not provided'}</p>
                </div>
            `;
        }

        function reviewSectionHeader(title) {
            return `<h3 class="font-black text-slate-900 text-xs sm:text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3 mb-3">${title}</h3>`;
        }

        function populateReview() {
            const form = document.getElementById('seafarerForm');
            const formData = new FormData(form);

            populatePersonalReview(formData);
            populateDocumentsReview(formData);
            populateShipboardReview(formData);
        }

        function populatePersonalReview(formData) {
            const photoImg = document.querySelector('#photoPreview img');
            const photoSrc = photoImg ? photoImg.src : '';

            const howKnown = formData.get('how_known') || '';
            let howKnownExtra = '';
            if (howKnown === 'Referral / Recommendation') {
                howKnownExtra = reviewField('Name Who Referred You', formData.get('referral_name'));
            } else if (howKnown === 'Others') {
                howKnownExtra = reviewField('Where Did You See Crystal?', formData.get('others_how_known'));
            }

            const html = `
                <div>
                    ${reviewSectionHeader("APPLICANT'S PROFILE")}
                    <div class="flex flex-col sm:flex-row gap-4 items-start">
                        <div class="w-20 h-20 rounded-xl border border-slate-300 bg-slate-50 overflow-hidden flex items-center justify-center shrink-0">
                            ${photoSrc ? `<img src="${photoSrc}" class="w-full h-full object-cover">` : `<span class="text-2xl">📷</span>`}
                        </div>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${reviewField('Position Applied', formData.get('position_applied'))}
                            ${reviewField('Date of Application', formData.get('application_date'))}
                            <div class="sm:col-span-2">${reviewField('How Did You Know About Crystal?', howKnown)}</div>
                            ${howKnownExtra}
                        </div>
                    </div>
                </div>

                <hr class="border-slate-200">

                <div>
                    ${reviewSectionHeader('FULL NAME')}
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        ${reviewField('Last Name', formData.get('last_name'))}
                        ${reviewField('First Name', formData.get('first_name'))}
                        ${reviewField('Middle Name', formData.get('middle_name'))}
                        ${reviewField('Suffix', formData.get('suffix'))}
                    </div>
                </div>

                <hr class="border-slate-200">

                <div>
                    ${reviewSectionHeader('BIRTH & ADDRESS')}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        ${reviewField('Date of Birth', formData.get('dob'))}
                        ${reviewField('Place of Birth', formData.get('pob'))}
                        ${reviewField('Age', formData.get('age'))}
                        <div class="sm:col-span-3">${reviewField('Current Address', formData.get('address'))}</div>
                    </div>
                </div>

                <hr class="border-slate-200">

                <div>
                    ${reviewSectionHeader('CONTACT INFORMATION')}
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        ${reviewField('Email Address', formData.get('email'))}
                        ${reviewField('Contact Number', formData.get('phone'))}
                        ${reviewField('Civil Status', formData.get('civil_status'))}
                        ${reviewField("Wife's Name", formData.get('wife_name'))}
                    </div>
                </div>

                <hr class="border-slate-200">

                <div>
                    ${reviewSectionHeader('GOVERNMENT IDS')}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        ${reviewField('Pag-IBIG No.', formData.get('pagibig_no'))}
                        ${reviewField('SSS No.', formData.get('sss_no'))}
                        ${reviewField('PhilHealth No.', formData.get('philhealth_no'))}
                    </div>
                </div>

                <hr class="border-slate-200">

                <div>
                    ${reviewSectionHeader('SOCIAL MEDIA & MESSAGING')}
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        ${reviewField('Facebook', formData.get('facebook'))}
                        ${reviewField('WhatsApp', formData.get('whatsapp'))}
                        ${reviewField('Viber', formData.get('viber'))}
                        ${reviewField('Skype', formData.get('skype'))}
                    </div>
                </div>
            `;

            document.getElementById('reviewTabContent-personal').innerHTML = html;
        }

        function populateDocumentsReview(formData) {
            const trainingNames = formData.getAll('training_name[]');
            const trainingNos = formData.getAll('training_no[]');
            const trainingIssues = formData.getAll('training_issue[]');
            const trainingExpiries = formData.getAll('training_expiry[]');

            let trainingHtml = '';
            let hasTraining = false;
            for (let i = 0; i < trainingNames.length; i++) {
                if (trainingNames[i].trim() === '' && trainingNos[i].trim() === '') continue;
                hasTraining = true;
                trainingHtml += `
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 bg-slate-50 rounded-xl border border-slate-200 p-4 mb-3">
                        ${reviewField('Certificate Name', trainingNames[i])}
                        ${reviewField('Certificate No.', trainingNos[i])}
                        ${reviewField('Issue Date', trainingIssues[i])}
                        ${reviewField('Expiry Date', trainingExpiries[i])}
                    </div>
                `;
            }
            if (!hasTraining) {
                trainingHtml = `<p class="text-sm italic text-slate-400">No training certificates added.</p>`;
            }

            const html = `
                <div>
                    ${reviewSectionHeader('PRIMARY DOCUMENTS')}
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <p class="sm:col-span-4 text-xs font-black text-blue-700 uppercase">Passport</p>
                            ${reviewField('Document No.', formData.get('passport_no'))}
                            ${reviewField('Issue Date', formData.get('passport_issue'))}
                            ${reviewField('Expiry Date', formData.get('passport_expiry'))}
                            ${reviewField('Place Issued', formData.get('passport_place'))}
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <p class="sm:col-span-4 text-xs font-black text-blue-700 uppercase">Seaman's Book (SIRB)</p>
                            ${reviewField('Document No.', formData.get('sirb_no'))}
                            ${reviewField('Issue Date', formData.get('sirb_issue'))}
                            ${reviewField('Expiry Date', formData.get('sirb_expiry'))}
                            ${reviewField('Place Issued', formData.get('sirb_place'))}
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <p class="sm:col-span-4 text-xs font-black text-blue-700 uppercase">GOC License</p>
                            ${reviewField('Document No.', formData.get('goc_no'))}
                            ${reviewField('Issue Date', formData.get('goc_issue'))}
                            ${reviewField('Expiry Date', formData.get('goc_expiry'))}
                            ${reviewField('Place Issued', formData.get('goc_place'))}
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <p class="sm:col-span-4 text-xs font-black text-blue-700 uppercase">SID</p>
                            ${reviewField('Document No.', formData.get('sid_no'))}
                            ${reviewField('Issue Date', formData.get('sid_issue'))}
                            ${reviewField('Expiry Date', formData.get('sid_expiry'))}
                            ${reviewField('Place Issued', formData.get('sid_place'))}
                        </div>
                    </div>
                </div>

                <hr class="border-slate-200">

                <div>
                    ${reviewSectionHeader('COC / LICENSE')}
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        ${reviewField('License Type', formData.get('coc_type'))}
                        ${reviewField('No.', formData.get('coc_no'))}
                        ${reviewField('Issue Date', formData.get('coc_issue'))}
                        ${reviewField('Expiry Date', formData.get('coc_expiry'))}
                    </div>
                </div>

                <hr class="border-slate-200">

                <div>
                    ${reviewSectionHeader('E-REGISTRATION')}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        ${reviewField('E-Registration No.', formData.get('e_reg_no'))}
                    </div>
                </div>

                <hr class="border-slate-200">

                <div>
                    ${reviewSectionHeader('TRAINING CERTIFICATES')}
                    ${trainingHtml}
                </div>
            `;

            document.getElementById('reviewTabContent-documents').innerHTML = html;
        }

        function populateShipboardReview(formData) {
            const principalNames = formData.getAll('principal_name[]');
            const vesselNames = formData.getAll('vessel_name[]');
            const vesselFlags = formData.getAll('vessel_flag[]');
            const vesselNats = formData.getAll('vessel_nat[]');
            const manningAgencies = formData.getAll('manning_agency[]');
            const ranks = formData.getAll('exp_rank[]');
            const vesselTypes = formData.getAll('vessel_type[]');
            const grts = formData.getAll('vessel_grt[]');
            const enginePowers = formData.getAll('engine_power[]');
            const salaries = formData.getAll('salary[]');
            const datesFrom = formData.getAll('date_from[]');
            const datesTo = formData.getAll('date_to[]');

            let experiencesHtml = '';
            for (let i = 0; i < vesselNames.length; i++) {
                experiencesHtml += `
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5 mb-4">
                        <p class="text-xs font-black text-blue-700 uppercase mb-3">Experience #${i + 1}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            ${reviewField('Principal Name', principalNames[i])}
                            ${reviewField('Vessel Name', vesselNames[i])}
                            ${reviewField('Flag', vesselFlags[i])}
                            ${reviewField('Nationality', vesselNats[i])}
                            ${reviewField('Manning Agency', manningAgencies[i])}
                            ${reviewField('Rank', ranks[i])}
                            ${reviewField('Vessel Type', vesselTypes[i])}
                            ${reviewField('GRT', grts[i])}
                            ${reviewField('KW/BHP', enginePowers[i])}
                            ${reviewField('Salary (USD)', salaries[i])}
                            ${reviewField('Date From', datesFrom[i])}
                            ${reviewField('Date To', datesTo[i])}
                        </div>
                    </div>
                `;
            }
            if (experiencesHtml === '') {
                experiencesHtml = `<p class="text-sm italic text-slate-400">No shipboard experience added.</p>`;
            }

            const html = `
                <div>
                    ${reviewSectionHeader('EXPERIENCES')}
                    ${experiencesHtml}
                </div>

                <hr class="border-slate-200">

                <div>
                    ${reviewSectionHeader('ADDITIONAL DETAILS')}
                    ${reviewField('Additional Details', formData.get('additional_details'))}
                </div>
            `;

            document.getElementById('reviewTabContent-shipboard').innerHTML = html;
        }
    </script>
</body>
</html>
