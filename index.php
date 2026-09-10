<?php
/**
 * Crystal Shipping Inc. - Seafarer Application Portal
 * Full Pure PHP Implementation (No Node/npm required)
 * Designed for XAMPP, WAMP, or `php -S localhost:8000`
 */

session_start();

// Handle Form Submission POST with Strict Sanitization
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    $clean = [];

    // Sanitize Single-Value Fields
    $scalarFields = [
        'position_applied', 'application_date', 'how_known', 'referral_name', 'others_how_known',
        'last_name', 'first_name', 'middle_name', 'suffix', 'dob', 'pob', 'age', 'address',
        'email', 'phone', 'civil_status', 'wife_name', 'pagibig_no', 'sss_no', 'philhealth_no',
        'facebook', 'viber', 'whatsapp', 'skype', 'passport_no', 'passport_issue', 'passport_expiry',
        'passport_place', 'sirb_no', 'sirb_issue', 'sirb_expiry', 'sirb_place', 'goc_no', 'goc_issue',
        'goc_expiry', 'goc_place', 'coc_type', 'coc_no', 'coc_issue', 'coc_expiry', 'e_reg_no',
        'sid_no', 'additional_details', 'is_cadet', 'photo_base64'
    ];

    foreach ($scalarFields as $field) {
        $clean[$field] = isset($_POST[$field]) ? trim((string)$_POST[$field]) : '';
    }

    // Server-Side Minimum Age Verification
    if (!empty($clean['dob'])) {
        try {
            $dobDate = new DateTime($clean['dob']);
            $now = new DateTime();
            $ageDiff = $now->diff($dobDate);
            if ($ageDiff->y < 18) {
                header('Location: index.php?step=terms&error=underage');
                exit;
            }
            $clean['calculated_age'] = $ageDiff->y;
        } catch (Exception $e) {
            $clean['calculated_age'] = null;
        }
    }

    // Normalize 1:N Training Certificates
    $clean['certificates'] = [];
    if (!empty($_POST['training_name']) && is_array($_POST['training_name'])) {
        foreach ($_POST['training_name'] as $idx => $name) {
            $nameTrim = trim((string)$name);
            $noTrim = isset($_POST['training_no'][$idx]) ? trim((string)$_POST['training_no'][$idx]) : '';
            if ($nameTrim !== '' || $noTrim !== '') {
                $clean['certificates'][] = [
                    'cert_name'   => $nameTrim,
                    'cert_no'     => $noTrim,
                    'issue_date'  => $_POST['training_issue'][$idx] ?? null,
                    'expiry_date' => $_POST['training_expiry'][$idx] ?? null,
                ];
            }
        }
    }

    // Normalize 1:N Sea Experiences
    $clean['sea_service'] = [];
    $isCadet = ($clean['is_cadet'] === '1');

    if (!$isCadet && !empty($_POST['vessel_name']) && is_array($_POST['vessel_name'])) {
        foreach ($_POST['vessel_name'] as $idx => $vessel) {
            $vesselTrim = trim((string)$vessel);
            if ($vesselTrim !== '') {
                $clean['sea_service'][] = [
                    'vessel_name'    => $vesselTrim,
                    'principal_name' => trim($_POST['principal_name'][$idx] ?? ''),
                    'flag'           => trim($_POST['vessel_flag'][$idx] ?? ''),
                    'nationality'    => trim($_POST['vessel_nat'][$idx] ?? ''),
                    'manning_agency' => trim($_POST['manning_agency'][$idx] ?? ''),
                    'rank'           => trim($_POST['exp_rank'][$idx] ?? ''),
                    'vessel_type'    => trim($_POST['vessel_type'][$idx] ?? ''),
                    'grt'            => trim($_POST['vessel_grt'][$idx] ?? ''),
                    'engine_power'   => trim($_POST['engine_power'][$idx] ?? ''),
                    'salary_usd'     => trim($_POST['salary'][$idx] ?? ''),
                    'date_from'      => $_POST['date_from'][$idx] ?? null,
                    'date_to'        => $_POST['date_to'][$idx] ?? null,
                ];
            }
        }
    }

    $_SESSION['application_data'] = $clean;
    $_SESSION['submitted_at'] = date('F j, Y, g:i a');
    header('Location: index.php?step=success');
    exit;
}

$step = $_GET['step'] ?? 'terms';

// Guard against direct ?step=success URL visits when no session data exists
if ($step === 'success' && empty($_SESSION['application_data'])) {
    header('Location: index.php?step=terms');
    exit;
}

$appData = $_SESSION['application_data'] ?? [];
$submittedAt = $_SESSION['submitted_at'] ?? '';
$todayDate = date('Y-m-d');
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
        <!-- Client-Side Form Wizard Handler -->
        <form id="seafarerForm" action="index.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="action" value="submit_application">
            <input type="hidden" name="photo_base64" id="photoBase64">
            <input type="hidden" name="is_cadet" id="isCadetInput" value="0">
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
                                    <select name="position_applied" id="positionApplied" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                                        <option value="">Select Position...</option>
                                        <optgroup label="Cadetship Program">
                                            <option value="Deck Cadet">Deck Cadet</option>
                                            <option value="Engine Cadet">Engine Cadet</option>
                                        </optgroup>
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
                                            <option value="4th Engineer">4th Engineer</option>
                                            <option value="Electro-Technical Officer (ETO)">Electro-Technical Officer (ETO)</option>
                                            <option value="Oiler / Motorman">Oiler / Motorman</option>
                                            <option value="Wiper">Wiper</option>
                                        </optgroup>
                                        <optgroup label="Catering & Hospitality">
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
                                <input type="tel" name="phone" id="phoneInput" required 
                                    placeholder="e.g. +63 917 123 4567"
                                    pattern="^\+?[0-9\s\-\(\)]{7,20}$"
                                    title="Please enter a valid phone number (7-20 digits, may include + prefix)."
                                    inputmode="tel"
                                    autocomplete="tel"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                                <p class="text-[10px] text-slate-500 mt-1">Include country code (e.g. +63 for Philippines).</p>
                            </div>
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
                                        <td class="p-2"><input type="date" name="passport_issue" id="passportIssue" max="<?php echo $todayDate; ?>" class="w-full p-2 border border-slate-300 rounded-lg" onchange="validateDatePair(this, document.getElementById('passportExpiry'), 'Passport', document.getElementById('passportDateError'))"></td>
                                        <td class="p-2"><input type="date" name="passport_expiry" id="passportExpiry" class="w-full p-2 border border-slate-300 rounded-lg" onchange="validateDatePair(document.getElementById('passportIssue'), this, 'Passport', document.getElementById('passportDateError'))"></td>
                                        <td class="p-2"><input type="text" name="passport_place" required placeholder="Place Issued" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                    </tr>
                                    <tr><td colspan="5" class="px-3 py-0"><p id="passportDateError" class="text-red-600 text-xs font-semibold hidden"></p></td></tr>
                                    <!-- Seaman's Book -->
                                    <tr>
                                        <td class="p-3 font-bold text-slate-900">SEAMAN'S BOOK</td>
                                        <td class="p-2"><input type="text" name="sirb_no" required placeholder="SIRB / SID No." class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="date" name="sirb_issue" id="sirbIssue" max="<?php echo $todayDate; ?>" class="w-full p-2 border border-slate-300 rounded-lg" onchange="validateDatePair(this, document.getElementById('sirbExpiry'), 'Seaman\'s Book', document.getElementById('sirbDateError'))"></td>
                                        <td class="p-2"><input type="date" name="sirb_expiry" id="sirbExpiry" class="w-full p-2 border border-slate-300 rounded-lg" onchange="validateDatePair(document.getElementById('sirbIssue'), this, 'Seaman\'s Book', document.getElementById('sirbDateError'))"></td>
                                        <td class="p-2"><input type="text" name="sirb_place" required placeholder="Place Issued" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                    </tr>
                                    <tr><td colspan="5" class="px-3 py-0"><p id="sirbDateError" class="text-red-600 text-xs font-semibold hidden"></p></td></tr>
                                    <!-- GOC License -->
                                    <tr>
                                        <td class="p-3 font-bold text-slate-900">GOC LICENSE</td>
                                        <td class="p-2"><input type="text" name="goc_no" placeholder="GOC License No." class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="date" name="goc_issue" id="gocIssue" max="<?php echo $todayDate; ?>" class="w-full p-2 border border-slate-300 rounded-lg" onchange="validateDatePair(this, document.getElementById('gocExpiry'), 'GOC License', document.getElementById('gocDateError'))"></td>
                                        <td class="p-2"><input type="date" name="goc_expiry" id="gocExpiry" class="w-full p-2 border border-slate-300 rounded-lg" onchange="validateDatePair(document.getElementById('gocIssue'), this, 'GOC License', document.getElementById('gocDateError'))"></td>
                                        <td class="p-2"><input type="text" name="goc_place" placeholder="Place Issued" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                    </tr>
                                    <tr><td colspan="5" class="px-3 py-0"><p id="gocDateError" class="text-red-600 text-xs font-semibold hidden"></p></td></tr>
                                </tbody>
                            </table>
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
                                <input type="date" name="coc_issue" id="cocIssue" max="<?php echo $todayDate; ?>" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs" onchange="validateDatePair(this, document.getElementById('cocExpiry'), 'COC / License', document.getElementById('cocDateError'))">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">EXPIRY DATE</label>
                                <input type="date" name="coc_expiry" id="cocExpiry" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs" onchange="validateDatePair(document.getElementById('cocIssue'), this, 'COC / License', document.getElementById('cocDateError'))">
                            </div>
                        </div>
                        <p id="cocDateError" class="text-red-600 text-xs font-semibold mt-2 hidden"></p>
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
                            <div class="training-row grid grid-cols-1 sm:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                                <input type="text" name="training_name[]" placeholder="Certificate Name (e.g. BST, ECDIS)" class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                                <input type="text" name="training_no[]" placeholder="Certificate No." class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                                <input type="date" name="training_issue[]" max="<?php echo $todayDate; ?>" class="px-3 py-2 rounded-lg border border-slate-300 text-xs training-issue" onchange="validateTrainingDates(this)">
                                <input type="date" name="training_expiry[]" class="px-3 py-2 rounded-lg border border-slate-300 text-xs training-expiry" onchange="validateTrainingDates(this)">
                            </div>
                            <p class="training-date-error text-red-600 text-xs font-semibold hidden"></p>
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
                    
                    <!-- Cadet / First-Timer Toggle -->
                    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="cadetToggle" class="w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500 cursor-pointer accent-blue-600 shrink-0" onchange="toggleCadetMode(this.checked)">
                            <div>
                                <label for="cadetToggle" class="text-xs sm:text-sm font-bold text-blue-950 cursor-pointer select-none">
                                    I am a First-Time Applicant / Cadet with No Prior Sea Experience
                                </label>
                                <p class="text-[11px] text-blue-700">Check this if you are a fresh maritime academy graduate applying for your first vessel assignment.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sea Experience Section -->
                    <div id="experienceSection" class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                                SEA SERVICE RECORDS
                            </h3>
                            <button type="button" onclick="addExperienceRow()" id="addExpBtn" class="bg-blue-600 text-white font-bold text-xs px-4 py-1.5 rounded-lg hover:bg-blue-700 shadow-sm">
                                + ADD EXPERIENCE
                            </button>
                        </div>
                        <p id="experienceError" class="hidden text-red-600 text-xs font-semibold mt-2 bg-red-50 p-2.5 rounded-xl border border-red-200">
                            &#9888; Please complete the required fields (Vessel Name, Rank, Date From) before adding another record.
                        </p>
                        <div id="experienceContainer" class="space-y-6">
                            <!-- Experience Entry #1 -->
                            <div class="experience-card bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-black text-blue-700 uppercase exp-title">
                                        EXPERIENCE #1 <span class="text-red-600 exp-required-mark">*</span>
                                    </p>
                                    <button type="button" onclick="clearOrRemoveFirstExp(this)" class="text-slate-400 hover:text-red-600 text-xs font-bold transition-colors">
                                        &#x2715; Clear
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">PRINCIPAL NAME</label>
                                        <input type="text" name="principal_name[]" placeholder="e.g. Evergreen Marine" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">VESSEL NAME <span class="text-red-600 exp-required-mark">*</span></label>
                                        <input type="text" name="vessel_name[]" placeholder="e.g. M/V Crystal Grace" class="w-full p-2.5 rounded-xl border border-slate-300 exp-req">
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
                                        <label class="block font-bold text-slate-700 mb-1">RANK <span class="text-red-600 exp-required-mark">*</span></label>
                                        <input type="text" name="exp_rank[]" placeholder="Rank Onboard" class="w-full p-2.5 rounded-xl border border-slate-300 exp-req">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">VESSEL TYPE</label>
                                        <input type="text" name="vessel_type[]" placeholder="Container / Bulk Carrier" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">GRT</label>
                                        <input type="text" name="vessel_grt[]" placeholder="Gross Tonnage" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">KW/BHP</label>
                                        <input type="text" name="engine_power[]" placeholder="Engine Output" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">SALARY (USD)</label>
                                        <input type="text" name="salary[]" placeholder="Monthly Salary" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">DATE FROM <span class="text-red-600 exp-required-mark">*</span></label>
                                        <input type="date" name="date_from[]" class="w-full p-2.5 rounded-xl border border-slate-300 exp-req">
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

        const stepOrder = ['terms', 'guide', 'personal', 'documents', 'shipboard', 'review'];
        let isSubmitting = false;

        function updateHeaderProgress(stepId) {
            const bar = document.getElementById('headerProgressBar');
            if (!bar) return;
            if (stepId === 'terms') { bar.style.width = '0%'; return; }
            const wizardSteps = ['guide', 'personal', 'documents', 'shipboard', 'review'];
            const idx = wizardSteps.indexOf(stepId);
            if (idx === -1) { bar.style.width = '100%'; return; }
            const pct = ((idx + 1) / wizardSteps.length) * 100;
            bar.style.width = pct + '%';
        }

        function goToStep(stepId, pushHistory) {
            if (pushHistory === undefined) pushHistory = true;
            const target = document.getElementById('step-' + stepId);
            if (!target) return;
            document.querySelectorAll('.step-page').forEach(function(el) { el.classList.add('hidden'); });
            target.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            if (stepId === 'review') { showReviewTab('personal'); populateReview(); }
            updateHeaderProgress(stepId);
            if (pushHistory && window.history) {
                var newUrl = window.location.pathname + '?step=' + stepId;
                window.history.pushState({ step: stepId }, '', newUrl);
            }
        }

        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.step) {
                goToStep(e.state.step, false);
            } else {
                var params = new URLSearchParams(window.location.search);
                var step = params.get('step') || 'terms';
                goToStep(step, false);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            var params = new URLSearchParams(window.location.search);
            var initialStep = params.get('step') || 'terms';
            if (initialStep === 'success') return;
            if (!document.getElementById('step-' + initialStep)) { initialStep = 'terms'; }
            document.querySelectorAll('.step-page').forEach(function(el) { el.classList.add('hidden'); });
            var startElement = document.getElementById('step-' + initialStep);
            if (startElement) { startElement.classList.remove('hidden'); updateHeaderProgress(initialStep); }
            if (window.history.state === null) {
                window.history.replaceState({ step: initialStep }, '', window.location.href);
            }
            var posSelect = document.getElementById('positionApplied');
            if (posSelect) {
                posSelect.addEventListener('change', function() {
                    if (this.value === 'Deck Cadet' || this.value === 'Engine Cadet') {
                        var cadetCheck = document.getElementById('cadetToggle');
                        if (cadetCheck && !cadetCheck.checked) {
                            cadetCheck.checked = true;
                            toggleCadetMode(true);
                        }
                    }
                });
            }
        });

        function toggleCadetMode(isCadet) {
            var expSection = document.getElementById('experienceSection');
            var isCadetField = document.getElementById('isCadetInput');
            var expReqMarks = document.querySelectorAll('.exp-required-mark');
            var expReqInputs = document.querySelectorAll('.exp-req');
            var errorMsg = document.getElementById('experienceError');
            isCadetField.value = isCadet ? '1' : '0';
            if (isCadet) {
                expSection.classList.add('opacity-50', 'pointer-events-none');
                expReqMarks.forEach(function(el) { el.classList.add('hidden'); });
                expReqInputs.forEach(function(el) { el.required = false; el.setCustomValidity(''); });
                if (errorMsg) errorMsg.classList.add('hidden');
            } else {
                expSection.classList.remove('opacity-50', 'pointer-events-none');
                expReqMarks.forEach(function(el) { el.classList.remove('hidden'); });
                expReqInputs.forEach(function(el) { el.required = true; });
            }
        }

        function validateDatePair(issueInput, expiryInput, docLabel, errorEl) {
            var issueVal = issueInput ? issueInput.value : '';
            var expiryVal = expiryInput ? expiryInput.value : '';
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            [issueInput, expiryInput].forEach(function(inp) {
                if (!inp) return;
                inp.classList.remove('border-red-500', 'bg-red-50', 'border-amber-500');
                inp.setCustomValidity('');
            });
            if (errorEl) { errorEl.classList.add('hidden'); errorEl.textContent = ''; }
            if (issueVal) {
                var issueDate = new Date(issueVal);
                if (issueDate > today) {
                    var msg = docLabel + ': Issue date cannot be in the future.';
                    if (issueInput) { issueInput.classList.add('border-red-500', 'bg-red-50'); issueInput.setCustomValidity(msg); }
                    if (errorEl) { errorEl.textContent = '\u26A0 ' + msg; errorEl.classList.remove('hidden'); }
                    return false;
                }
            }
            if (issueVal && expiryVal) {
                var issueDateC = new Date(issueVal);
                var expiryDate = new Date(expiryVal);
                if (expiryDate <= issueDateC) {
                    var msg2 = docLabel + ': Expiry date must be later than the issue date.';
                    if (expiryInput) { expiryInput.classList.add('border-red-500', 'bg-red-50'); expiryInput.setCustomValidity(msg2); }
                    if (errorEl) { errorEl.textContent = '\u26A0 ' + msg2; errorEl.classList.remove('hidden'); }
                    return false;
                }
            }
            if (expiryVal) {
                var expiryDateW = new Date(expiryVal);
                if (expiryDateW < today && expiryInput) { expiryInput.classList.add('border-amber-500'); }
            }
            return true;
        }

        function validateTrainingDates(changedInput) {
            var row = changedInput.closest('.training-row');
            if (!row) return;
            var issueInput = row.querySelector('.training-issue');
            var expiryInput = row.querySelector('.training-expiry');
            var errorEl = null;
            var parent = row.parentElement;
            if (parent) {
                errorEl = parent.querySelector('.training-date-error');
                if (!errorEl) errorEl = row.nextElementSibling;
                if (errorEl && !errorEl.classList.contains('training-date-error')) errorEl = null;
            }
            var certName = row.querySelector('input[name="training_name[]"]');
            var label = (certName && certName.value.trim()) ? certName.value.trim() : 'Training Certificate';
            validateDatePair(issueInput, expiryInput, label, errorEl);
        }

        function validateAllDocumentDates() {
            var allValid = true;
            var docPairs = [
                { issue: 'passportIssue', expiry: 'passportExpiry', label: 'Passport', error: 'passportDateError' },
                { issue: 'sirbIssue', expiry: 'sirbExpiry', label: "Seaman's Book", error: 'sirbDateError' },
                { issue: 'gocIssue', expiry: 'gocExpiry', label: 'GOC License', error: 'gocDateError' },
                { issue: 'cocIssue', expiry: 'cocExpiry', label: 'COC / License', error: 'cocDateError' }
            ];
            for (var p = 0; p < docPairs.length; p++) {
                var pair = docPairs[p];
                var issueEl = document.getElementById(pair.issue);
                var expiryEl = document.getElementById(pair.expiry);
                var errorEl = document.getElementById(pair.error);
                if ((issueEl && issueEl.value) || (expiryEl && expiryEl.value)) {
                    if (!validateDatePair(issueEl, expiryEl, pair.label, errorEl)) {
                        if (allValid) { (issueEl || expiryEl).scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                        allValid = false;
                    }
                }
            }
            var trainingRows = document.querySelectorAll('.training-row');
            trainingRows.forEach(function(row) {
                var tIssue = row.querySelector('.training-issue');
                var tExpiry = row.querySelector('.training-expiry');
                if ((tIssue && tIssue.value) || (tExpiry && tExpiry.value)) {
                    var cName = row.querySelector('input[name="training_name[]"]');
                    var lbl = (cName && cName.value.trim()) ? cName.value.trim() : 'Training Certificate';
                    var tErr = null;
                    var tParent = row.parentElement;
                    if (tParent) tErr = tParent.querySelector('.training-date-error');
                    if (!validateDatePair(tIssue, tExpiry, lbl, tErr)) {
                        if (allValid) { tIssue.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                        allValid = false;
                    }
                }
            });
            return allValid;
        }

        function validateExperiences() {
            var isCadet = document.getElementById('isCadetInput').value === '1';
            if (isCadet) return true;
            var experiences = document.querySelectorAll('#experienceContainer > .experience-card');
            var errorMsg = document.getElementById('experienceError');
            if (errorMsg) errorMsg.classList.add('hidden');
            for (var i = 0; i < experiences.length; i++) {
                var reqFields = experiences[i].querySelectorAll('.exp-req');
                for (var j = 0; j < reqFields.length; j++) {
                    if (reqFields[j].value.trim() === '') {
                        if (errorMsg) errorMsg.classList.remove('hidden');
                        reqFields[j].focus();
                        return false;
                    }
                }
            }
            return validateDateRanges();
        }

        function validateDateRanges() {
            var dateFromFields = document.querySelectorAll('input[name="date_from[]"]');
            var dateToFields = document.querySelectorAll('input[name="date_to[]"]');
            for (var i = 0; i < dateFromFields.length; i++) {
                var from = dateFromFields[i].value;
                var to = dateToFields[i] ? dateToFields[i].value : '';
                if (from && to && new Date(to) < new Date(from)) {
                    alert('Experience #' + (i + 1) + ': "Date To" cannot be earlier than "Date From".');
                    dateToFields[i].focus();
                    return false;
                }
            }
            return true;
        }

        function goToNextStep(nextStepId) {
            var currentStep = document.querySelector('.step-page:not(.hidden)');
            if (!currentStep) { goToStep(nextStepId); return; }
            var fields = currentStep.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), select, textarea');
            for (var i = 0; i < fields.length; i++) {
                if (fields[i].offsetParent !== null && !fields[i].checkValidity()) {
                    fields[i].reportValidity();
                    return;
                }
            }
            if (currentStep.id === 'step-documents') {
                if (!validateAllDocumentDates()) return;
            }
            if (currentStep.id === 'step-shipboard') {
                if (!validateExperiences()) return;
                var certify = document.getElementById('certifyCheck');
                if (certify && !certify.checked) {
                    alert('Please certify that all information is correct before proceeding.');
                    certify.focus();
                    return;
                }
            }
            goToStep(nextStepId);
        }

        function previewPhoto(event) {
            var input = event.target;
            var file = input.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                alert('Please upload an image file only (JPG, PNG, WebP).');
                input.value = '';
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.onload = function() {
                    var canvas = document.createElement('canvas');
                    var MAX_DIM = 800;
                    var width = img.width;
                    var height = img.height;
                    if (width > height && width > MAX_DIM) {
                        height = Math.round((height * MAX_DIM) / width);
                        width = MAX_DIM;
                    } else if (height > MAX_DIM) {
                        width = Math.round((width * MAX_DIM) / height);
                        height = MAX_DIM;
                    }
                    canvas.width = width;
                    canvas.height = height;
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    var compressedDataUrl = canvas.toDataURL('image/jpeg', 0.85);
                    document.getElementById('photoBase64').value = compressedDataUrl;
                    document.getElementById('photoPreview').innerHTML = '<img src="' + compressedDataUrl + '" class="w-24 h-24 object-cover rounded-xl shadow-md border-2 border-white mb-1"><span class="text-[10px] text-blue-600 font-bold">Change Photo</span>';
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        var MAX_TRAINING_ROWS = 5;

        function addTrainingRow() {
            var container = document.getElementById('trainingContainer');
            var rowCount = container.querySelectorAll('.training-row').length;
            if (rowCount >= MAX_TRAINING_ROWS) {
                document.getElementById('training_limit_msg').classList.remove('hidden');
                document.getElementById('addTrainingBtn').disabled = true;
                document.getElementById('addTrainingBtn').classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }
            var wrapper = document.createElement('div');
            wrapper.className = 'training-row-wrapper';
            var div = document.createElement('div');
            div.className = 'training-row relative grid grid-cols-1 sm:grid-cols-4 gap-3 bg-slate-50 p-3 pt-7 rounded-xl border border-slate-200';
            div.innerHTML = '<input type="text" name="training_name[]" placeholder="Certificate Name" class="px-3 py-2 rounded-lg border border-slate-300 text-xs">' +
                '<input type="text" name="training_no[]" placeholder="Certificate No." class="px-3 py-2 rounded-lg border border-slate-300 text-xs">' +
                '<input type="date" name="training_issue[]" class="px-3 py-2 rounded-lg border border-slate-300 text-xs training-issue" onchange="validateTrainingDates(this)">' +
                '<input type="date" name="training_expiry[]" class="px-3 py-2 rounded-lg border border-slate-300 text-xs training-expiry" onchange="validateTrainingDates(this)">';
            var errorP = document.createElement('p');
            errorP.className = 'training-date-error text-red-600 text-xs font-semibold hidden';
            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.innerHTML = '\u2715 Remove';
            removeBtn.className = 'absolute top-2 right-3 text-red-600 text-xs font-bold hover:underline';
            removeBtn.onclick = function() {
                if (confirm('Remove this certificate?')) { wrapper.remove(); checkTrainingLimit(); }
            };
            div.appendChild(removeBtn);
            wrapper.appendChild(div);
            wrapper.appendChild(errorP);
            container.appendChild(wrapper);
            checkTrainingLimit();
        }

        function checkTrainingLimit() {
            var container = document.getElementById('trainingContainer');
            var rowCount = container.querySelectorAll('.training-row').length;
            var addBtn = document.getElementById('addTrainingBtn');
            var limitMsg = document.getElementById('training_limit_msg');
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
            var isCadet = document.getElementById('isCadetInput').value === '1';
            if (isCadet) { alert('Cadet mode is active. Disable it to add sea service records.'); return; }
            var container = document.getElementById('experienceContainer');
            var lastExp = container.lastElementChild;
            var errorMsg = document.getElementById('experienceError');
            if (errorMsg) errorMsg.classList.add('hidden');
            if (lastExp) {
                var reqFields = lastExp.querySelectorAll('.exp-req');
                for (var i = 0; i < reqFields.length; i++) {
                    if (reqFields[i].value.trim() === '') {
                        if (errorMsg) errorMsg.classList.remove('hidden');
                        reqFields[i].focus();
                        return;
                    }
                }
            }
            var count = container.children.length + 1;
            var div = document.createElement('div');
            div.className = 'experience-card bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4';
            div.innerHTML = '<div class="flex items-center justify-between"><p class="text-xs font-black text-blue-700 uppercase exp-title">EXPERIENCE #' + count + ' <span class="text-red-600 exp-required-mark">*</span></p><button type="button" class="remove-experience-btn text-red-600 text-xs font-bold hover:underline">\u2715 Remove</button></div>' +
                '<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">' +
                '<div><label class="block font-bold text-slate-700 mb-1">PRINCIPAL NAME</label><input type="text" name="principal_name[]" placeholder="e.g. Evergreen Marine" class="w-full p-2.5 rounded-xl border border-slate-300"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">VESSEL NAME <span class="text-red-600 exp-required-mark">*</span></label><input type="text" name="vessel_name[]" required placeholder="e.g. M/V Star" class="w-full p-2.5 rounded-xl border border-slate-300 exp-req"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">FLAG</label><input type="text" name="vessel_flag[]" placeholder="e.g. Panama" class="w-full p-2.5 rounded-xl border border-slate-300"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">NATIONALITY</label><input type="text" name="vessel_nat[]" placeholder="e.g. Japanese" class="w-full p-2.5 rounded-xl border border-slate-300"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">MANNING AGENCY</label><input type="text" name="manning_agency[]" placeholder="Agency" class="w-full p-2.5 rounded-xl border border-slate-300"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">RANK <span class="text-red-600 exp-required-mark">*</span></label><input type="text" name="exp_rank[]" required placeholder="Rank" class="w-full p-2.5 rounded-xl border border-slate-300 exp-req"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">VESSEL TYPE</label><input type="text" name="vessel_type[]" placeholder="Vessel Type" class="w-full p-2.5 rounded-xl border border-slate-300"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">GRT</label><input type="text" name="vessel_grt[]" placeholder="GRT" class="w-full p-2.5 rounded-xl border border-slate-300"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">KW/BHP</label><input type="text" name="engine_power[]" placeholder="KW/BHP" class="w-full p-2.5 rounded-xl border border-slate-300"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">SALARY (USD)</label><input type="text" name="salary[]" placeholder="Salary" class="w-full p-2.5 rounded-xl border border-slate-300"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">DATE FROM <span class="text-red-600 exp-required-mark">*</span></label><input type="date" name="date_from[]" required class="w-full p-2.5 rounded-xl border border-slate-300 exp-req"></div>' +
                '<div><label class="block font-bold text-slate-700 mb-1">DATE TO</label><input type="date" name="date_to[]" class="w-full p-2.5 rounded-xl border border-slate-300"></div>' +
                '</div>';
            var removeBtn2 = div.querySelector('.remove-experience-btn');
            removeBtn2.addEventListener('click', function() {
                if (confirm('Remove this sea service entry?')) { div.remove(); renumberExperiences(); }
            });
            container.appendChild(div);
        }

        function clearOrRemoveFirstExp(btn) {
            var card = btn.closest('.experience-card');
            var inputs = card.querySelectorAll('input');
            inputs.forEach(function(input) { input.value = ''; });
        }

        function renumberExperiences() {
            var cards = document.querySelectorAll('#experienceContainer > .experience-card');
            cards.forEach(function(card, index) {
                var title = card.querySelector('.exp-title');
                if (title) { title.innerHTML = 'EXPERIENCE #' + (index + 1) + ' <span class="text-red-600 exp-required-mark">*</span>'; }
            });
        }

        // ============ REVIEW TABS ============

        var reviewTabs = ['personal', 'documents', 'shipboard'];
        var currentReviewTab = 'personal';

        function showReviewTab(tabName) {
            currentReviewTab = tabName;
            reviewTabs.forEach(function(t) {
                var btn = document.getElementById('reviewTabBtn-' + t);
                var content = document.getElementById('reviewTabContent-' + t);
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
            var nextBtn = document.getElementById('reviewNextBtn');
            if (tabName === 'shipboard') {
                nextBtn.textContent = 'SUBMIT APPLICATION NOW \u2713';
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
            var idx = reviewTabs.indexOf(currentReviewTab);
            if (idx < reviewTabs.length - 1) {
                showReviewTab(reviewTabs[idx + 1]);
            } else {
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
            if (isSubmitting) return;
            var submitBtn = document.querySelector('#confirmSubmitModal button.bg-emerald-600');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            }
            isSubmitting = true;
            closeConfirmModal();
            document.getElementById('seafarerForm').requestSubmit();
        }

        function editCurrentReviewTab() {
            if (currentReviewTab === 'personal') goToStep('personal');
            else if (currentReviewTab === 'documents') goToStep('documents');
            else if (currentReviewTab === 'shipboard') goToStep('shipboard');
        }

        // Builds one label/value block
        function reviewField(label, value) {
            var hasValue = value && value.toString().trim() !== '';
            return '<div>' +
                '<p class="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">' + label + '</p>' +
                '<p class="text-sm ' + (hasValue ? 'font-semibold text-slate-800' : 'font-normal italic text-slate-400') + '">' + (hasValue ? value : 'Not provided') + '</p>' +
                '</div>';
        }

        function reviewSectionHeader(title) {
            return '<h3 class="font-black text-slate-900 text-xs sm:text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3 mb-3">' + title + '</h3>';
        }

        function populateReview() {
            var form = document.getElementById('seafarerForm');
            var formData = new FormData(form);
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
        // === populateShipboardReview (NEW - with cadet mode support) ===
        function populateShipboardReview(formData) {
            var isCadet = formData.get('is_cadet') === '1';
            var experiencesHtml = '';
            if (isCadet) {
                experiencesHtml = '<div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 text-center text-blue-900">' +
                    '<span class="text-2xl block mb-1">\u2693</span>' +
                    '<p class="font-bold text-sm">First-Time Applicant / Cadet</p>' +
                    '<p class="text-xs text-blue-700 mt-1">Applicant has indicated zero prior sea service.</p>' +
                    '</div>';
            } else {
                var principalNames = formData.getAll('principal_name[]');
                var vesselNames = formData.getAll('vessel_name[]');
                var vesselFlags = formData.getAll('vessel_flag[]');
                var vesselNats = formData.getAll('vessel_nat[]');
                var manningAgencies = formData.getAll('manning_agency[]');
                var ranks = formData.getAll('exp_rank[]');
                var vesselTypes = formData.getAll('vessel_type[]');
                var grts = formData.getAll('vessel_grt[]');
                var enginePowers = formData.getAll('engine_power[]');
                var salaries = formData.getAll('salary[]');
                var datesFrom = formData.getAll('date_from[]');
                var datesTo = formData.getAll('date_to[]');
                var validCount = 0;
                for (var i = 0; i < vesselNames.length; i++) {
                    if (!vesselNames[i] || vesselNames[i].trim() === '') continue;
                    validCount++;
                    experiencesHtml += '<div class="bg-slate-50 rounded-2xl border border-slate-200 p-5 mb-4">' +
                        '<p class="text-xs font-black text-blue-700 uppercase mb-3">Sea Service #' + validCount + '</p>' +
                        '<div class="grid grid-cols-1 sm:grid-cols-4 gap-4">' +
                        reviewField('Vessel Name', vesselNames[i]) +
                        reviewField('Rank', ranks[i]) +
                        reviewField('Principal Name', principalNames[i]) +
                        reviewField('Manning Agency', manningAgencies[i]) +
                        reviewField('Flag', vesselFlags[i]) +
                        reviewField('Nationality', vesselNats[i]) +
                        reviewField('Vessel Type', vesselTypes[i]) +
                        reviewField('GRT', grts[i]) +
                        reviewField('KW/BHP', enginePowers[i]) +
                        reviewField('Salary (USD)', salaries[i]) +
                        reviewField('Date From', datesFrom[i]) +
                        reviewField('Date To', datesTo[i]) +
                        '</div></div>';
                }
                if (validCount === 0) {
                    experiencesHtml = '<p class="text-sm italic text-slate-400">No sea service records provided.</p>';
                }
            }
            var html = '<div>' + reviewSectionHeader('SEA SERVICE & SHIPBOARD EXPERIENCES') + experiencesHtml + '</div>' +
                '<hr class="border-slate-200">' +
                '<div>' + reviewSectionHeader('ADDITIONAL DETAILS') + reviewField('Additional Information', formData.get('additional_details')) + '</div>';
            document.getElementById('reviewTabContent-shipboard').innerHTML = html;
        }
    </script>
</body>
</html>
