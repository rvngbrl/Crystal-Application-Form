<?php
/**
 * Crystal Shipping Inc. - Seafarer Application Portal
 * Full Pure PHP Implementation (No Node/npm required)
 * Designed for XAMPP, WAMP, or `php -S localhost:8000`
 */

session_start();
require_once __DIR__ . '/config.php';

// Philippine Time (UTC+8) for all date()/time stamps on this page
date_default_timezone_set('Asia/Manila');

$submitError = $_SESSION['submit_error'] ?? '';
unset($_SESSION['submit_error']);

function cleanStr(mixed $val): string {
    return trim((string)($val ?? ''));
}
function nullIfBlank(string $val): ?string {
    return $val === '' ? null : $val;
}

// Handle Form Submission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    $lastName = cleanStr($_POST['last_name'] ?? '');
    $firstName = cleanStr($_POST['first_name'] ?? '');
    $middleName = cleanStr($_POST['middle_name'] ?? '');
    $suffix = cleanStr($_POST['suffix'] ?? '');
    $dob = cleanStr($_POST['dob'] ?? '');
    $pob = cleanStr($_POST['pob'] ?? '');
    $email = cleanStr($_POST['email'] ?? '');
    $countryCode = cleanStr($_POST['country_code'] ?? '');
    $phone = cleanStr($_POST['phone'] ?? '');
    $fullMobile = trim($countryCode . ' ' . $phone);
    $address = cleanStr($_POST['address'] ?? '');
    $civilStatus = cleanStr($_POST['civil_status'] ?? '');
    $wifeName = cleanStr($_POST['wife_name'] ?? '');
    $facebook = cleanStr($_POST['facebook'] ?? '');
    $viber = cleanStr($_POST['viber'] ?? '');
    $whatsapp = cleanStr($_POST['whatsapp'] ?? '');
    $skype = cleanStr($_POST['skype'] ?? '');
    $posApplied = cleanStr($_POST['position_applied'] ?? '');
    $howKnown = cleanStr($_POST['how_known'] ?? '');
    $refName = cleanStr($_POST['referral_name'] ?? '');
    $othersKnown = cleanStr($_POST['others_how_known'] ?? '');
    $details = cleanStr($_POST['additional_details'] ?? '');

    $fullSource = $howKnown;
    if ($refName !== '') $fullSource .= ' - ' . $refName;
    if ($othersKnown !== '') $fullSource .= ' - ' . $othersKnown;

    $dateApplied = date('Y-m-d');
    foreach (['date_from' => 'past', 'date_to' => 'future'] as $field => $allowedDirection) {
        $experienceDates = $_POST[$field] ?? [];
        if (!is_array($experienceDates)) {
            continue;
        }

        foreach ($experienceDates as $experienceDate) {
            $experienceDate = cleanStr($experienceDate);
            if ($experienceDate === '') {
                continue;
            }

            $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $experienceDate);
            $isValidDate = $parsedDate && $parsedDate->format('Y-m-d') === $experienceDate;
            $isOutOfRange = $allowedDirection === 'past'
                ? $experienceDate > $dateApplied
                : $experienceDate < $dateApplied;

            if (
                !$isValidDate
                || $isOutOfRange
            ) {
                $_SESSION['submit_error'] = 'Experience start dates cannot be in the future, and end dates cannot be in the past.';
                $_SESSION['application_data'] = $_POST;
                header('Location: index.php?step=form');
                exit;
            }
        }
    }

    try {
        // Duplicate check
        $dupStmt = $db->prepare(
            'SELECT App_ID FROM applicant_info WHERE App_LName = ? AND App_FName = ? AND App_Bday = ? AND App_MobileNo = ? AND App_EmailAdd = ?'
        );
        $dupStmt->bind_param('sssss', $lastName, $firstName, $dob, $fullMobile, $email);
        $dupStmt->execute();
        $dupResult = $dupStmt->get_result();
        if ($dupResult->num_rows > 0) {
            $dupStmt->close();
            $_SESSION['submit_error'] = 'An application with these personal details has already been submitted.';
            $_SESSION['application_data'] = $_POST;
            header('Location: index.php?step=form');
            exit;
        }
        $dupStmt->close();

        // Photo upload handling
        $imageName = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['photo']['tmp_name']);
            if (isset($allowedMime[$mime])) {
                if (!is_dir(UPLOAD_DIR)) {
                    @mkdir(UPLOAD_DIR, 0755, true);
                }
                $imageName = bin2hex(random_bytes(8)) . '.' . $allowedMime[$mime];
                move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_DIR . $imageName);
            }
        }

        $db->begin_transaction();

        $insStmt = $db->prepare(
            'INSERT INTO applicant_info
                (App_PositionApplied, App_LName, App_FName, App_MName, App_Bday, App_BPlace,
                 App_EmailAdd, App_MobileNo, App_DateApplied, App_Address, App_CivilStat,
                 App_WifeName, App_Facebook, App_Viber, App_WhatsApp, App_Skype, App_Status,
                 App_Image, App_AddDetails, App_Consent, App_Source)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $statusVal = 'Applicant';
        $consentVal = 'YES';
        $mNameVal = $middleName !== '' ? $middleName : null;
        $wNameVal = $wifeName !== '' ? $wifeName : null;
        $fbVal = $facebook !== '' ? $facebook : null;
        $vibVal = $viber !== '' ? $viber : null;
        $waVal = $whatsapp !== '' ? $whatsapp : null;
        $skyVal = $skype !== '' ? $skype : null;
        $detailsVal = $details !== '' ? $details : null;
        $sourceVal = $fullSource !== '' ? $fullSource : 'Pure PHP Portal';

        $insStmt->bind_param(
            'sssssssssssssssssssss',
            $posApplied, $lastName, $firstName, $mNameVal, $dob, $pob,
            $email, $fullMobile, $dateApplied, $address, $civilStatus,
            $wNameVal, $fbVal, $vibVal, $waVal, $skyVal, $statusVal,
            $imageName, $detailsVal, $consentVal, $sourceVal
        );
        $insStmt->execute();
        $insStmt->close();
        $appId = (int)$db->insert_id;

        // Helper for document insertion
        $docStmt = $db->prepare(
            'INSERT INTO applicant_doc (AppDoc_Name, AppDoc_Shortcut, AppDoc_No, AppDoc_DateIssued, AppDoc_DateExpired, AppDoc_Place, App_ID, Document_ID)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $addDoc = function(string $name, string $short, string $no, string $iss, string $exp, string $place, int $docId) use ($docStmt, $appId) {
            if ($no === '') return;
            $issVal = nullIfBlank($iss);
            $expVal = nullIfBlank($exp);
            $plcVal = nullIfBlank($place);
            $docStmt->bind_param('ssssssii', $name, $short, $no, $issVal, $expVal, $plcVal, $appId, $docId);
            $docStmt->execute();
        };

        // Standard Documents
        $addDoc('PASSPORT', 'PASSPORT', strtoupper(cleanStr($_POST['passport_no'] ?? '')), cleanStr($_POST['passport_issue'] ?? ''), cleanStr($_POST['passport_expiry'] ?? ''), cleanStr($_POST['passport_place'] ?? ''), 1003);
        $addDoc("Seaman's Book", 'SIRB', strtoupper(cleanStr($_POST['sirb_no'] ?? '')), cleanStr($_POST['sirb_issue'] ?? ''), cleanStr($_POST['sirb_expiry'] ?? ''), cleanStr($_POST['sirb_place'] ?? ''), 1004);
        $addDoc('GOC Licensed', 'GOC', cleanStr($_POST['goc_no'] ?? ''), cleanStr($_POST['goc_issue'] ?? ''), cleanStr($_POST['goc_expiry'] ?? ''), cleanStr($_POST['goc_place'] ?? ''), 1013);
        $cocType = cleanStr($_POST['coc_type'] ?? '');
        $addDoc($cocType !== '' ? $cocType : 'COC Marina', 'COC', cleanStr($_POST['coc_no'] ?? ''), cleanStr($_POST['coc_issue'] ?? ''), cleanStr($_POST['coc_expiry'] ?? ''), '', 1011);
        $addDoc('E-Registration Number', 'E-Reg Number', cleanStr($_POST['e_reg_no'] ?? ''), '', '', '', 1623);
        $addDoc('Seafarer Identification Document', 'SID', cleanStr($_POST['sid_no'] ?? ''), '', '', '', 1704);

        // Training Certificates
        $trNames = $_POST['training_name'] ?? [];
        $trNos = $_POST['training_no'] ?? [];
        $trIssues = $_POST['training_issue'] ?? [];
        $trExpiries = $_POST['training_expiry'] ?? [];
        if (is_array($trNames)) {
            for ($i = 0; $i < count($trNames); $i++) {
                $tName = cleanStr($trNames[$i] ?? '');
                $tNo = cleanStr($trNos[$i] ?? '');
                if ($tName !== '' || $tNo !== '') {
                    $addDoc($tName ?: 'Training Certificate', 'TRAIN', $tNo, cleanStr($trIssues[$i] ?? ''), cleanStr($trExpiries[$i] ?? ''), '', 1000);
                }
            }
        }
        $docStmt->close();

        // Shipboard Experience
        $pNames = $_POST['principal_name'] ?? [];
        $vNames = $_POST['vessel_name'] ?? [];
        $flags = $_POST['vessel_flag'] ?? [];
        $nats = $_POST['vessel_nat'] ?? [];
        $agencies = $_POST['manning_agency'] ?? [];
        $ranks = $_POST['exp_rank'] ?? [];
        $vTypes = $_POST['vessel_type'] ?? [];
        $grts = $_POST['vessel_grt'] ?? [];
        $bhps = $_POST['engine_power'] ?? [];
        $salaries = $_POST['salary'] ?? [];
        $fromDates = $_POST['date_from'] ?? [];
        $toDates = $_POST['date_to'] ?? [];

        if (is_array($pNames) && count($pNames) > 0) {
            $seaStmt = $db->prepare(
                'INSERT INTO applicant_seaservice
                    (App_PrincipalName, App_VesselName, App_ImportFlag, App_Nationality, App_Agency,
                     App_Rank, App_VesselType, App_Grt, App_BHP, App_Salary,
                     App_DateSignedON, App_DateSignedOFF, App_Duration, App_ID)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            for ($i = 0; $i < count($pNames); $i++) {
                $pn = cleanStr($pNames[$i] ?? '');
                $vn = cleanStr($vNames[$i] ?? '');
                if ($pn === '' && $vn === '') continue;

                $vf = cleanStr($flags[$i] ?? '');
                $vt = cleanStr($vTypes[$i] ?? '');
                $fr = cleanStr($fromDates[$i] ?? '');
                $to = cleanStr($toDates[$i] ?? '');
                // Sea service dates cannot be in the future
                if ($fr !== '' && $fr > $dateApplied) {
                    $fr = $dateApplied;
                }
                if ($to !== '' && $to > $dateApplied) {
                    $to = $dateApplied;
                }
                $dur = '';
                if ($fr !== '' && $to !== '') {
                    try {
                        $sDate = new DateTime($fr);
                        $eDate = new DateTime($to);
                        if ($eDate < $sDate) {
                            [$sDate, $eDate] = [$eDate, $sDate];
                        }
                        $diff = $sDate->diff($eDate);
                        $dur = (($diff->y * 12) + $diff->m) . '.' . $diff->d;
                    } catch (Throwable) {}
                }
                $frVal = nullIfBlank($fr);
                $toVal = nullIfBlank($to);
                $na = cleanStr($nats[$i] ?? '');
                $ag = cleanStr($agencies[$i] ?? '');
                $rk = cleanStr($ranks[$i] ?? '');
                $gr = cleanStr($grts[$i] ?? '');
                $bp = cleanStr($bhps[$i] ?? '');
                $sl = cleanStr($salaries[$i] ?? '');

                $seaStmt->bind_param('sssssssssssssi', $pn, $vn, $vf, $na, $ag, $rk, $vt, $gr, $bp, $sl, $frVal, $toVal, $dur, $appId);
                $seaStmt->execute();
            }
            $seaStmt->close();
        }

        $db->commit();

        $refNumber = 'CSI-' . str_pad((string)$appId, 6, '0', STR_PAD_LEFT);
        $_SESSION['application_data'] = $_POST;
        $_SESSION['submitted_at'] = date('F j, Y, g:i a');
        $_SESSION['ref_number'] = $refNumber;
        header('Location: index.php?step=success');
        exit;
    } catch (Throwable $e) {
        if ($db instanceof mysqli && @$db->ping()) {
            @$db->rollback();
        }
        error_log('Application submission failed in index.php: ' . $e->getMessage());
        $_SESSION['submit_error'] = 'An unexpected error occurred while saving your application. Please try again.';
        $_SESSION['application_data'] = $_POST;
        header('Location: index.php?step=form');
        exit;
    }
}

// Load ranks dynamically from remote database
$dbRanks = [];
try {
    $rRes = $db->query('SELECT rank_name FROM rank_list ORDER BY rank_name');
    while ($rRow = $rRes->fetch_assoc()) {
        $dbRanks[] = $rRow['rank_name'];
    }
} catch (Throwable $e) {
    error_log('Failed to fetch ranks from DB: ' . $e->getMessage());
}

$step = $_GET['step'] ?? 'terms';
$appData = $_SESSION['application_data'] ?? [];
$submittedAt = $_SESSION['submitted_at'] ?? '';
$refNumber = $_SESSION['ref_number'] ?? '';
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
            background-image: linear-gradient(to bottom, rgba(15, 23, 42, 0.65), rgba(15, 23, 42, 0.75)), url('src/assets/images/bg_new.jpg');
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

        <?php if (!empty($submitError)): ?>
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center gap-3 shadow-md">
                <span class="text-xl">⚠️</span>
                <span><?php echo htmlspecialchars($submitError); ?></span>
            </div>
        <?php endif; ?>

        <!-- Client-Side Form Wizard Handler -->
        <form id="seafarerForm" action="index.php" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="action" value="submit_application">

            <!-- ================= STEP 0: TERMS & CONDITIONS ================= -->
            <div id="step-terms" class="step-page space-y-6 <?php echo $step !== 'terms' && $step !== '' ? 'hidden' : ''; ?>">
                <div class="text-center space-y-1">
                    <p class="text-white/90 text-xs sm:text-sm font-bold tracking-wider uppercase drop-shadow-sm">
                        CRYSTAL SHIPPING INC - APPLICATION
                    </p>
                    <h1 class="text-white text-3xl sm:text-4xl md:text-5xl font-black tracking-tight uppercase drop-shadow-md">
                        TERMS & CONDITION
                    </h1>
                </div>

                <!-- Glassmorphism Scrollable Box -->
                <div class="w-full bg-white/90 backdrop-blur-md rounded-3xl p-6 sm:p-8 md:p-10 shadow-2xl border border-white/40 max-h-[420px] overflow-y-auto custom-scrollbar text-slate-800 space-y-5 text-base leading-relaxed">
                    <p>Crystal Shipping’s Data Privacy Manual establishes rules for protecting personal information in compliance with the Data Privacy Act of 2012 (RA 10173), its IRR, and NPC issuances. It follows the principles of transparency, legitimate purpose, and proportionality. Personal data must only be collected when necessary, used for legitimate purposes, and accessed by authorized personnel. Data subjects have rights to be informed, access their information, request corrections, and file complaints.</p>

                    <p>The Manual classifies information as Public, Confidential, or Classified, with stricter controls for sensitive and highly restricted information. Departments must obtain consent or have another lawful basis for processing and must ensure information remains accurate and secure.</p>

                    <p>IT and authorized personnel are responsible for protecting databases, systems, physical records, and network infrastructure from unauthorized access, loss, misuse, modification, or disclosure. Personal information must only be retained as necessary and securely destroyed when no longer required.</p>

                    <p>Any suspected breach, unauthorized access, loss, disclosure, or destruction of personal information must be reported immediately to the DPO/Data Privacy Response Team. Serious breaches may require notification to the NPC and affected individuals within 72 hours, when applicable. <a href="http://crystalshippinginc.com/PDF/Crystal_Privacy_Manual.pdf" target="_blank" rel="noopener noreferrer" class="font-bold text-blue-700 underline hover:text-blue-900">Click here to view the full content.</a></p>
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
                <div class="text-center space-y-1 -mt-4 sm:-mt-6">
                    <p class="text-white/90 text-xs sm:text-sm font-bold tracking-wider uppercase drop-shadow-sm">
                        CRYSTAL SHIPPING INC - APPLICATION
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
                            <li>• Civil status </li>
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
                            <li>• Primary documents: Passport, SIRB</li>
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
                    <div class="w-12 h-12 rounded-xl text-white flex items-center justify-center text-2xl font-black shrink-0">
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
                                        <?php if (!empty($dbRanks)): ?>
                                            <?php foreach ($dbRanks as $rName): ?>
                                                <option value="<?php echo htmlspecialchars($rName); ?>"><?php echo htmlspecialchars($rName); ?></option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
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
                                        <?php endif; ?>
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
                            <input type="text" id="ageField" name="age" readonly class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-100 text-xs font-semibold text-slate-600 outline-none">
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
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                CONTACT NUMBER <span class="text-red-600">*</span>
            </label>

            <div class="flex">
                <!-- Country Code -->
                <select
                    name="country_code"
                    id="countryCodeSelect"
                    required
                    onchange="updatePhoneConstraints()"
                    class="w-32 px-2 py-2.5 rounded-l-xl border border-slate-300 bg-slate-100 text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none"
                >
                    <option value="+63">PH +63</option>
                    <option value="+1">USA +1</option>
                    <option value="+44">UK +44</option>
                    <option value="+81">JPN +81</option>
                    <option value="+82">KOR +82</option>
                    <option value="+86">CHN +86</option>
                    <option value="+65">SGP +65</option>
                    <option value="+971">UAE +971</option>
                    <option value="+966">SAU +966</option>
                    <option value="+61">AUS +61</option>
                    <option value="+64">NZL +64</option>
                    <option value="+49">DEU +49</option>
                    <option value="+33">FRA +33</option>
                    <option value="+39">ITA +39</option>
                    <option value="+34">ESP +34</option>
                    <option value="+31">NLD +31</option>
                    <option value="+91">IND +91</option>
                    <option value="+62">IDN +62</option>
                    <option value="+60">MYS +60</option>
                    <option value="+66">THA +66</option>
                    <option value="+84">VNM +84</option>
                    <option value="+880">BGD +880</option>
                    <option value="+92">PAK +92</option>
                    <option value="+7">RUS +7</option>
                    <option value="+55">BRA +55</option>
                    <option value="+52">MEX +52</option>
                    <option value="+27">ZAF +27</option>
                </select>

                <!-- Phone Number -->
                <input
                    type="tel"
                    name="phone"
                    id="phoneInput"
                    required
                    placeholder="Enter contact number"
                    inputmode="numeric"
                    oninput="this.value = this.value.replace(/[^0-9]/g, ''); validatePhoneLength();"
                    class="w-full px-3.5 py-2.5 rounded-r-xl border border-l-0 border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none"
                >
            </div>

                <p id="phone_hint" class="text-[10px] text-slate-500 mt-1">
                    Select your country, then enter your contact number.
                </p>
            </div>

            <script>
                // Expected number of digits (excluding the country code) per country.
                // Used to cap how many digits can be typed AND to validate the exact length
                // before the form is allowed to proceed. Falls back to a generous 15 for
                // any country code not explicitly listed.
                const phoneDigitLengths = {
                    '+63': 10,  // Philippines
                    '+1': 10,   // USA / Canada
                    '+44': 10,  // United Kingdom
                    '+81': 10,  // Japan
                    '+82': 10,  // South Korea
                    '+86': 11,  // China
                    '+65': 8,   // Singapore
                    '+971': 9,  // UAE
                    '+966': 9,  // Saudi Arabia
                    '+61': 9,   // Australia
                    '+64': 9,   // New Zealand
                    '+49': 11,  // Germany
                    '+33': 9,   // France
                    '+39': 10,  // Italy
                    '+34': 9,   // Spain
                    '+31': 9,   // Netherlands
                    '+91': 10,  // India
                    '+62': 11,  // Indonesia
                    '+60': 10,  // Malaysia
                    '+66': 9,   // Thailand
                    '+84': 9,   // Vietnam
                    '+880': 10, // Bangladesh
                    '+92': 10,  // Pakistan
                    '+7': 10,   // Russia
                    '+55': 11,  // Brazil
                    '+52': 10,  // Mexico
                    '+27': 9    // South Africa
                };

                function updatePhoneConstraints() {
                    const countrySelect = document.getElementById('countryCodeSelect');
                    const phoneInput = document.getElementById('phoneInput');
                    const hint = document.getElementById('phone_hint');
                    const code = countrySelect.value;
                    const requiredLength = phoneDigitLengths[code] || 15;

                    phoneInput.setAttribute('maxlength', requiredLength);

                    validatePhoneLength();
                }

                function validatePhoneLength() {
                    const countrySelect = document.getElementById('countryCodeSelect');
                    const phoneInput = document.getElementById('phoneInput');
                    const code = countrySelect.value;
                    const requiredLength = phoneDigitLengths[code] || null;

                    if (!requiredLength) {
                        phoneInput.setCustomValidity('');
                        return;
                    }

                    if (phoneInput.value.length > 0 && phoneInput.value.length !== requiredLength) {
                        phoneInput.setCustomValidity(`Contact number must be exactly ${requiredLength} digits for the selected country.`);
                    } else {
                        phoneInput.setCustomValidity('');
                    }
                }

                // Set the correct maxlength/hint for the default selected country (Philippines) on load
                document.addEventListener('DOMContentLoaded', updatePhoneConstraints);
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
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">PARTNER'S NAME <span class="text-slate-400 font-normal">(if applicable)</span></label>
                                <input type="text" name="wife_name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
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
                                        <td class="p-2"><input type="text" name="passport_no" required placeholder="Passport No." oninput="forceUppercase(this)" autocapitalize="characters" class="w-full p-2 border border-slate-300 rounded-lg uppercase"></td>
                                        <td class="p-2"><input type="date" name="passport_issue" max="<?php echo $todayDate; ?>" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2">
                                            <input type="date" name="passport_expiry" id="passport_expiry" class="w-full p-2 border border-slate-300 rounded-lg">
                                        </td>
                                        <td class="p-2"><input type="text" name="passport_place" required placeholder="Place Issued" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                    </tr>
                                    <!-- Seaman's Book -->
                                    <tr>
                                        <td class="p-3 font-bold text-slate-900">SEAMAN'S BOOK</td>
                                        <td class="p-2"><input type="text" name="sirb_no" required placeholder="SIRB / SID No." oninput="forceUppercase(this)" autocapitalize="characters" class="w-full p-2 border border-slate-300 rounded-lg uppercase"></td>
                                        <td class="p-2"><input type="date" name="sirb_issue" max="<?php echo $todayDate; ?>" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2">
                                            <input type="date" name="sirb_expiry" id="sirb_expiry" class="w-full p-2 border border-slate-300 rounded-lg">
                                        </td>
                                        <td class="p-2"><input type="text" name="sirb_place" required placeholder="Place Issued" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                    </tr>
                                    <!-- GOC License -->
                                    <tr>
                                        <td class="p-3 font-bold text-slate-900">GOC LICENSE</td>
                                        <td class="p-2"><input type="text" name="goc_no" placeholder="GOC License No." class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2"><input type="date" name="goc_issue" max="<?php echo $todayDate; ?>" class="w-full p-2 border border-slate-300 rounded-lg"></td>
                                        <td class="p-2">
                                            <input type="date" name="goc_expiry" id="goc_expiry" class="w-full p-2 border border-slate-300 rounded-lg">
                                            
                                        </td>
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
                                <input type="date" name="coc_issue" max="<?php echo $todayDate; ?>" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">EXPIRY DATE</label>
                                <input type="date" name="coc_expiry" id="coc_expiry" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                                <label class="flex items-center gap-1 mt-1 text-[11px] font-semibold text-slate-500 cursor-pointer select-none">
                                    <input type="checkbox" name="coc_no_expiry" onchange="toggleNoExpiry(this, 'coc_expiry')" class="accent-blue-600">
                                    No Expiry
                                </label>
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
                                <input type="date" name="training_issue[]" max="<?php echo $todayDate; ?>" class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                                <div>
                                    <input type="date" name="training_expiry[]" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                                    <label class="flex items-center gap-1 mt-1 text-[11px] font-semibold text-slate-500 cursor-pointer select-none">
                                        <input type="checkbox" name="training_no_expiry[]" onchange="toggleTrainingNoExpiry(this)" class="accent-blue-600">
                                        No Expiry
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <p id="training_limit_msg" class="text-red-600 text-xs font-semibold hidden">
                                Maximum of 5 certificates reached.
                            </p>

                            <button
                                type="button"
                                onclick="addTrainingRow()"
                                id="addTrainingBtn"
                                class="bg-blue-600 text-white font-bold text-xs px-5 py-2 rounded-lg hover:bg-blue-700 transition-all shadow-md"
                            >
                                + ADD CERTIFICATE
                            </button>
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
                        <div class="border-b border-slate-200 pb-2">
                            <h3 class="font-black text-slate-900 text-sm uppercase tracking-wider border-l-4 border-slate-900 pl-3">
                                EXPERIENCES
                            </h3>
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
                                        <input type="date" name="date_from[]" max="<?php echo $todayDate; ?>" onchange="syncExperienceDateConstraints(this)" oninput="syncExperienceDateConstraints(this)" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">DATE TO</label>
                                        <input type="date" name="date_to[]" min="<?php echo $todayDate; ?>" onchange="syncExperienceDateConstraints(this)" oninput="syncExperienceDateConstraints(this)" class="w-full p-2.5 rounded-xl border border-slate-300">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Experience Button - Lower Right -->
                    <div class="flex items-center justify-end pt-2">
                        <button
                            type="button"
                            onclick="addExperienceRow()"
                            id="addExperienceBtn"
                            class="bg-blue-600 text-white font-bold text-xs px-5 py-2 rounded-lg hover:bg-blue-700 transition-all shadow-md"
                        >
                            + ADD EXPERIENCE
                        </button>
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
                    <p class="font-bold text-slate-900 border-b pb-2 text-sm uppercase flex items-center justify-between">
                        <span>Applicant Overview</span>
                        <?php if (!empty($refNumber)): ?>
                            <span class="text-blue-600 font-extrabold tracking-wider"><?php echo htmlspecialchars($refNumber); ?></span>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($refNumber)): ?>
                        <p><strong>Reference Number:</strong> <span class="font-bold text-blue-600"><?php echo htmlspecialchars($refNumber); ?></span></p>
                    <?php endif; ?>
                    <p><strong>Position Applied:</strong> <?php echo htmlspecialchars($appData['position_applied'] ?? 'N/A'); ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars(($appData['first_name'] ?? '') . ' ' . ($appData['middle_name'] ?? '') . ' ' . ($appData['last_name'] ?? '')); ?></p>
                    <p><strong>Email Address:</strong> <?php echo htmlspecialchars($appData['email'] ?? 'N/A'); ?></p>
                    <p><strong>Contact Number:</strong> <?php $countryCode = $appData['country_code'] ?? ''; $phone = $appData['phone'] ?? 'N/A'; echo htmlspecialchars(trim($countryCode . ' ' . $phone)); ?> </p>
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

    <!-- Remove Confirmation Modal -->
    <div
        id="removeConfirmModal"
        class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm"
    >
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-8 text-center space-y-5">

            <!-- Warning Icon -->
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto text-2xl font-black">
                !
            </div>

            <!-- Message -->
            <div>
                <h3 class="text-lg sm:text-xl font-black text-slate-900">
                    Remove this item?
                </h3>

                <p id="removeConfirmMessage" class="text-xs sm:text-sm text-slate-600 mt-2">
                    Are you sure you want to remove this item?
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-2">

                <button
                    type="button"
                    onclick="closeRemoveConfirmModal()"
                    class="w-full py-3 px-6 rounded-full font-extrabold text-sm bg-slate-200 text-slate-800 hover:bg-slate-300 transition-all"
                >
                    CANCEL
                </button>

                <button
                    type="button"
                    onclick="confirmRemoveItem()"
                    class="w-full py-3 px-6 rounded-full font-extrabold text-sm bg-red-600 text-white hover:bg-red-700 transition-all shadow-lg"
                >
                    YES, REMOVE
                </button>

            </div>

        </div>
    </div>

    <!-- Document Expiry Warning Modal -->
    <div
        id="expiryAlertModal"
        class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm"
    >
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 sm:p-8 text-center space-y-5">

            <!-- Warning Icon -->
            <div id="expiryAlertIcon" class="w-14 h-14 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto text-2xl font-black">
                ⚠
            </div>

            <!-- Message -->
            <div>
                <h3 id="expiryAlertTitle" class="text-lg sm:text-xl font-black text-slate-900">
                    Document Near Expiry
                </h3>

                <div id="expiryAlertMessage" class="text-xs sm:text-sm text-slate-600 mt-3 text-left">
                </div>
            </div>

            <!-- Button -->
            <button
                type="button"
                onclick="closeExpiryAlert()"
                class="w-full py-3 px-6 rounded-full font-extrabold text-sm bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg"
            >
                OK, GOT IT
            </button>

        </div>
    </div>

    <!-- Scroll To Top Button -->

    <button
        type="button"
        id="scrollTopBtn"
        onclick="scrollToTop()"
        class="hidden fixed bottom-6 right-6 z-50 w-20 h-20 rounded-full bg-blue-600 text-white text-5xl font-black shadow-xl hover:bg-blue-700 transition-all items-center justify-center"
        aria-label="Scroll to top"
    >
        ↑
    </button>

    <!-- Footer -->
    <footer class="py-4 text-center text-xs font-semibold text-white/80 border-t border-white/10 bg-slate-950/60 backdrop-blur-md">
        © <?php echo date('Y'); ?> Crystal Shipping Inc. All Rights Reserved. | Seafarer Application Portal
    </footer>

    <!-- JavaScript Navigation & Dynamics -->
    <script>
        const TODAY_DATE = '<?php echo $todayDate; ?>';

        function showExperienceError(msg) {
            const errorMsg = document.getElementById("experienceError");
            if (errorMsg) {
                errorMsg.textContent = msg;
                errorMsg.classList.remove("hidden");
            }
        }

        function syncExperienceDateConstraints(changedInput) {
            const card = changedInput ? (changedInput.closest('.grid') || changedInput.closest('.space-y-4')) : null;
            if (!card) return;
            const dateFrom = card.querySelector('input[name="date_from[]"]');
            const dateTo = card.querySelector('input[name="date_to[]"]');
            if (!dateFrom || !dateTo) return;

            const todayStr = TODAY_DATE || new Date().toISOString().split('T')[0];

            // Cap Date From at Date To (if Date To is earlier than today), otherwise cap at today
            const maxForFrom = (dateTo.value && dateTo.value < todayStr) ? dateTo.value : todayStr;
            dateFrom.setAttribute('max', maxForFrom);
            dateTo.setAttribute('max', todayStr);

            // Date To cannot start before Date From
            if (dateFrom.value) {
                dateTo.setAttribute('min', dateFrom.value);
            } else {
                dateTo.removeAttribute('min');
            }

            // HTML5 custom validity messaging
            if (dateFrom.value && dateFrom.value > todayStr) {
                dateFrom.setCustomValidity('Future dates are not allowed. Date From cannot be in the future.');
            } else {
                dateFrom.setCustomValidity('');
            }

            if (dateTo.value && dateTo.value > todayStr) {
                dateTo.setCustomValidity('Future dates are not allowed. Date To cannot be in the future.');
            } else if (dateFrom.value && dateTo.value && dateTo.value < dateFrom.value) {
                dateTo.setCustomValidity('Date To cannot be earlier than Date From.');
            } else {
                dateTo.setCustomValidity('');
            }
        }

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

            // Initialize experience date pickers with max constraint and event listeners
            document.querySelectorAll('#experienceContainer input[name="date_from[]"], #experienceContainer input[name="date_to[]"]').forEach(input => {
                input.setAttribute('max', TODAY_DATE);
                input.addEventListener('change', function() { syncExperienceDateConstraints(this); });
                input.addEventListener('input', function() { syncExperienceDateConstraints(this); });
                syncExperienceDateConstraints(input);
            });
        });

        function validateExperiences() {
            const experiences = document.querySelectorAll("#experienceContainer > div");
            const errorMsg = document.getElementById("experienceError");
            if (errorMsg) {
                errorMsg.classList.add("hidden");
                errorMsg.textContent = "Please fill out all fields in the current Experience before adding a new one.";
            }

            const todayStr = (typeof TODAY_DATE !== 'undefined' && TODAY_DATE) ? TODAY_DATE : new Date().toISOString().split('T')[0];

            for (const experience of experiences) {
                const fields = experience.querySelectorAll("input, select, textarea");
                for (const field of fields) {
                    if (field.value.trim() === "") {
                        showExperienceError("Please fill out all fields in the current Experience before proceeding.");
                        field.focus();
                        return false;
                    }
                }

                const dateFromInput = experience.querySelector('input[name="date_from[]"]');
                const dateToInput = experience.querySelector('input[name="date_to[]"]');
                if (dateFromInput && dateFromInput.value) {
                    if (dateFromInput.value > todayStr) {
                        showExperienceError("Future dates are not allowed. Date From cannot be in the future.");
                        dateFromInput.focus();
                        return false;
                    }
                }
                if (dateToInput && dateToInput.value) {
                    if (dateToInput.value > todayStr) {
                        showExperienceError("Future dates are not allowed. Date To cannot be in the future.");
                        dateToInput.focus();
                        return false;
                    }
                }
                if (dateFromInput && dateToInput && dateFromInput.value && dateToInput.value) {
                    if (dateToInput.value < dateFromInput.value) {
                        showExperienceError("Date To cannot be earlier than Date From.");
                        dateToInput.focus();
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

            // Leaving Documents? Check expiry dates now (once, on the way out)
            // instead of interrupting the applicant while they're still typing.
            // Expired or near-expiring documents only show a popup; the applicant
            // can still proceed once they close it. It's shown again at submission.
            if (currentStep.id === 'step-documents') {
                const { expired, nearExpiring } = collectExpiryWarnings();
                const flagged = expired.concat(nearExpiring);
                if (flagged.length > 0) {
                    showExpiryWarnings(flagged, () => goToStep(nextStepId));
                    return;
                }
            }

            // Current step is valid, so proceed
            goToStep(nextStepId);
        }

        function calcAge() {
            const dobInput = document.getElementById('dobInput');
            const ageField = document.getElementById('ageField');
            const dob = dobInput.value;

            if (!dob) {
                ageField.value = '';
                dobInput.setCustomValidity('');
                return;
            }

            const birthDate = new Date(dob);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            if (age < 18) {
                ageField.value = age >= 0 ? age + ' yrs old' : '';
                ageField.classList.add('text-red-600', 'border-red-400', 'bg-red-50');
                dobInput.setCustomValidity('Applicant must be at least 18 years old to apply.');
            } else {
                ageField.value = age + ' yrs old';
                ageField.classList.remove('text-red-600', 'border-red-400', 'bg-red-50');
                dobInput.setCustomValidity('');
            }
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
                    <input type="date" name="training_issue[]" max="${TODAY_DATE}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs">
                    <div>
                        <input type="date" name="training_expiry[]" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                        <label class="flex items-center gap-1 mt-1 text-[11px] font-semibold text-slate-500 cursor-pointer select-none">
                            <input type="checkbox" name="training_no_expiry[]" onchange="toggleTrainingNoExpiry(this)" class="accent-blue-600">
                            No Expiry
                        </label>
                    </div>
                `;

                // Add a remove button, positioned top-right of the row
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.innerHTML = '✕ Remove';
                removeBtn.className = 'absolute top-2 right-3 text-red-600 text-xs font-bold hover:underline';
                removeBtn.onclick = function() { openRemoveConfirmModal(div, 'certificate'); };
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
                const errorMsg = document.getElementById("experienceError");
                if (errorMsg) {
                    errorMsg.classList.add("hidden");
                    errorMsg.textContent = "Please fill out all fields in the current Experience before adding a new one.";
                }

                const lastExperience = container.lastElementChild;
                if (lastExperience) {
                    const fields = lastExperience.querySelectorAll("input, select, textarea");
                    for (const field of fields) {
                        if (field.value.trim() === "") {
                            showExperienceError("Please fill out all fields in the current Experience before adding a new one.");
                            field.focus();
                            return;
                        }
                    }

                    const dateFrom = lastExperience.querySelector('input[name="date_from[]"]');
                    const dateTo = lastExperience.querySelector('input[name="date_to[]"]');
                    const todayStr = (typeof TODAY_DATE !== 'undefined' && TODAY_DATE) ? TODAY_DATE : new Date().toISOString().split('T')[0];
                    if (dateFrom && dateFrom.value > todayStr) {
                        showExperienceError("Future dates are not allowed. Date From cannot be in the future.");
                        dateFrom.focus();
                        return;
                    }
                    if (dateTo && dateTo.value > todayStr) {
                        showExperienceError("Future dates are not allowed. Date To cannot be in the future.");
                        dateTo.focus();
                        return;
                    }
                    if (dateFrom && dateTo && dateFrom.value && dateTo.value && dateTo.value < dateFrom.value) {
                        showExperienceError("Date To cannot be earlier than Date From.");
                        dateTo.focus();
                        return;
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
                            <input type="date" name="date_from[]" max="${TODAY_DATE}" onchange="syncExperienceDateConstraints(this)" oninput="syncExperienceDateConstraints(this)" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">DATE TO</label>
                            <input type="date" name="date_to[]" max="${TODAY_DATE}" onchange="syncExperienceDateConstraints(this)" oninput="syncExperienceDateConstraints(this)" class="w-full p-2.5 rounded-xl border border-slate-300">
                        </div>

                    </div>
                        `;

                        const removeBtn = div.querySelector('.remove-experience-btn');
                        removeBtn.addEventListener('click', function() {
                            openRemoveConfirmModal(div, 'experience');
                        });

                        div.querySelectorAll('input[name="date_from[]"], input[name="date_to[]"]').forEach(input => {
                            input.setAttribute('max', TODAY_DATE);
                            input.addEventListener('change', function() { syncExperienceDateConstraints(this); });
                            input.addEventListener('input', function() { syncExperienceDateConstraints(this); });
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
            // Re-check expiry dates one last time before the final "are you
            // sure" prompt. Expired / near-expiring documents are surfaced again
            // as a reminder, then the applicant continues to the confirmation.
            const { expired, nearExpiring } = collectExpiryWarnings();
            const flagged = expired.concat(nearExpiring);

            if (flagged.length > 0) {
                showExpiryWarnings(flagged, () => {
                    document.getElementById('confirmSubmitModal').classList.remove('hidden');
                });
                return;
            }

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


        // ================= REMOVE CONFIRMATION MODAL =================

        let itemPendingRemoval = null;
        let itemPendingRemovalType = '';

        function openRemoveConfirmModal(element, type) {

            itemPendingRemoval = element;
            itemPendingRemovalType = type;

            const message = document.getElementById('removeConfirmMessage');

            if (type === 'certificate') {
                message.textContent =
                    'Are you sure you want to remove this certificate? This action cannot be undone.';
            } else if (type === 'experience') {
                message.textContent =
                    'Are you sure you want to remove this experience? This action cannot be undone.';
            }

            document.getElementById('removeConfirmModal').classList.remove('hidden');
        }

        function closeRemoveConfirmModal() {

            document.getElementById('removeConfirmModal').classList.add('hidden');

            itemPendingRemoval = null;
            itemPendingRemovalType = '';
        }

        function confirmRemoveItem() {

            if (!itemPendingRemoval) {
                closeRemoveConfirmModal();
                return;
            }

            itemPendingRemoval.remove();

            if (itemPendingRemovalType === 'certificate') {
                checkTrainingLimit();
            }

            if (itemPendingRemovalType === 'experience') {
                renumberExperiences();
            }

            closeRemoveConfirmModal();
        }

        // Forces typed letters to CAPITAL while keeping the cursor in place.
        function forceUppercase(input) {
            const start = input.selectionStart, end = input.selectionEnd;
            input.value = input.value.toUpperCase();
            input.setSelectionRange(start, end);
        }

        // ================= NO EXPIRY TOGGLE =================
        // When "No Expiry" is ticked for a document, its date field is cleared
        // and disabled so it's excluded from both the expiry check below and
        // the submitted form data.
        function toggleNoExpiry(checkbox, dateFieldId) {
            const dateField = document.getElementById(dateFieldId);
            if (!dateField) return;

            dateField.disabled = checkbox.checked;
            dateField.classList.toggle('bg-slate-100', checkbox.checked);
            dateField.classList.toggle('text-slate-400', checkbox.checked);
            if (checkbox.checked) dateField.value = '';
        }

        // Training certificate rows are dynamic, so the paired date field is
        // found relative to the checkbox's own row instead of by a fixed id.
        function toggleTrainingNoExpiry(checkbox) {
            const row = checkbox.closest('.grid');
            const dateField = row ? row.querySelector('input[name="training_expiry[]"]') : null;
            if (!dateField) return;

            dateField.disabled = checkbox.checked;
            dateField.classList.toggle('bg-slate-100', checkbox.checked);
            dateField.classList.toggle('text-slate-400', checkbox.checked);
            if (checkbox.checked) dateField.value = '';
        }

        // ================= DOCUMENT EXPIRY WARNING =================
        // Checked once, right before leaving the Documents step (see
        // goToNextStep), instead of while the applicant is still typing a
        // date. Returns null for a date that's blank, incomplete, or fine;
        // otherwise a short status describing the problem.
        function getExpiryStatus(value) {
            if (!value) return null;

            const expiry = new Date(value + 'T00:00:00');
            if (isNaN(expiry.getTime())) return null;

            // "Today" in Philippine Time, regardless of the device's timezone
            const today = new Date(new Date().toLocaleString('en-US', { timeZone: 'Asia/Manila' }));
            today.setHours(0, 0, 0, 0);

            const oneYearFromNow = new Date(today);
            oneYearFromNow.setFullYear(oneYearFromNow.getFullYear() + 1);

            if (expiry < today) return { statusText: 'has already EXPIRED', isExpired: true };
            if (expiry <= oneYearFromNow) return { statusText: 'is near expiring (within 1 year)', isExpired: false };
            return null;
        }

        // Scans every document expiry field on the Documents step and sorts
        // the flagged ones into "expired" (blocks progress) and "nearExpiring"
        // (just a heads-up). Fields that are blank or marked "No Expiry"
        // (disabled) are skipped entirely.
        function collectExpiryWarnings() {
            const expired = [];
            const nearExpiring = [];

            const addResult = (label, status) => {
                (status.isExpired ? expired : nearExpiring).push({ label, ...status });
            };

            const staticFields = [
                { id: 'passport_expiry', label: 'Passport' },
                { id: 'sirb_expiry', label: "Seaman's Book (SIRB)" },
                { id: 'goc_expiry', label: 'GOC License' },
                { id: 'coc_expiry', label: 'COC / License' },
            ];

            staticFields.forEach(f => {
                const field = document.getElementById(f.id);
                if (!field || field.disabled) return;
                const status = getExpiryStatus(field.value);
                if (status) addResult(f.label, status);
            });

            document.querySelectorAll('#trainingContainer input[name="training_expiry[]"]').forEach(field => {
                if (field.disabled) return;
                const row = field.closest('.grid');
                const nameInput = row ? row.querySelector('input[name="training_name[]"]') : null;
                const label = (nameInput && nameInput.value.trim()) ? nameInput.value.trim() : 'Training Certificate';
                const status = getExpiryStatus(field.value);
                if (status) addResult(label, status);
            });

            return { expired, nearExpiring };
        }

        // Holds whatever navigation should happen once the applicant
        // acknowledges the warning modal (see goToNextStep / closeExpiryAlert).
                let pendingExpiryProceed = null;

        function showExpiryWarnings(warnings, onProceed) {
            const isAnyExpired = warnings.some(w => w.isExpired);

            document.getElementById('expiryAlertTitle').textContent =
                warnings.length === 1
                    ? (warnings[0].isExpired ? 'Document Expired' : 'Document Near Expiry')
                    : 'Document Expiry Warning';

            // Bulleted, left-aligned list (one bullet per document)
            document.getElementById('expiryAlertMessage').innerHTML =
                '<ul class="list-disc pl-5 space-y-1 text-left">' +
                warnings.map(w => `<li><strong>${w.label}</strong> ${w.statusText}.</li>`).join('') +
                '</ul>';

            const icon = document.getElementById('expiryAlertIcon');
            if (isAnyExpired) {
                icon.classList.remove('bg-amber-100', 'text-amber-600');
                icon.classList.add('bg-red-100', 'text-red-600');
                icon.textContent = '✕';
            } else {
                icon.classList.remove('bg-red-100', 'text-red-600');
                icon.classList.add('bg-amber-100', 'text-amber-600');
                icon.textContent = '⚠';
            }

            pendingExpiryProceed = onProceed || null;
            document.getElementById('expiryAlertModal').classList.remove('hidden');
        }

        function closeExpiryAlert() {
            document.getElementById('expiryAlertModal').classList.add('hidden');
            const proceed = pendingExpiryProceed;
            pendingExpiryProceed = null;
            if (typeof proceed === 'function') proceed();
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
                        ${reviewField(
                            'Contact Number',
                            `${formData.get('country_code') || ''} ${formData.get('phone') || ''}`.trim()
                        )}
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

        // ============ SCROLL TO TOP (performance-friendly) ============
        // Instead of running this logic on every single scroll event (which can
        // fire far more often than the screen actually repaints and cause jank),
        // we throttle it with requestAnimationFrame so the check only happens
        // once per animation frame — this keeps scrolling smooth.
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        let scrollTicking = false;

        function handleScrollTick() {
            const scrollTopBtn = document.getElementById('scrollTopBtn');
            if (window.scrollY > 300) {
                scrollTopBtn.classList.remove('hidden');
                scrollTopBtn.classList.add('flex');
            } else {
                scrollTopBtn.classList.add('hidden');
                scrollTopBtn.classList.remove('flex');
            }
            scrollTicking = false;
        }

        window.addEventListener('scroll', function() {
            if (!scrollTicking) {
                window.requestAnimationFrame(handleScrollTick);
                scrollTicking = true;
            }
        }, { passive: true });
    </script>
</body>
</html>