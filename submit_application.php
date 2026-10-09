<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['message' => 'Method not allowed.']);
}

$application = json_decode((string) ($_POST['application'] ?? ''), true);
if (!is_array($application)) {
    respond(400, ['message' => 'The submitted application data is invalid.']);
}

$requiredFields = ['firstName', 'lastName', 'dob', 'pob', 'email', 'phone', 'address', 'appliedRank'];
foreach ($requiredFields as $field) {
    if (trim((string) ($application[$field] ?? '')) === '') {
        respond(422, ['message' => 'Please complete all required application fields.']);
    }
}
if (!filter_var($application['email'], FILTER_VALIDATE_EMAIL)) {
    respond(422, ['message' => 'Enter a valid email address.']);
}

$uploadDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'crystal-private-uploads';
$allowedMimeTypes = [
    'application/pdf' => 'pdf',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
];
$pendingUploads = [];
foreach (['passport', 'seamanBook', 'stcw'] as $field) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        continue;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
        respond(422, ['message' => 'Each document must be no larger than 5 MB. Please select the files again.']);
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowedMimeTypes[$mimeType])) {
        respond(422, ['message' => 'Documents must be PDF, JPG, or PNG files.']);
    }

    $pendingUploads[$field] = [
        'temporaryPath' => $file['tmp_name'],
        'storedName' => bin2hex(random_bytes(16)) . '.' . $allowedMimeTypes[$mimeType],
    ];
}

$db = null;
$savedFiles = [];
try {
    require_once __DIR__ . '/config.php';

    $lastName = trim((string) $application['lastName']);
    $firstName = trim((string) $application['firstName']);
    $birthday = (string) $application['dob'];
    $mobileNo = trim((string) $application['phone']);
    $email = trim((string) $application['email']);

    $duplicateCheck = $db->prepare(
        'SELECT App_ID FROM applicant_info
         WHERE App_LName = ? AND App_FName = ? AND App_Bday = ? AND App_MobileNo = ? AND App_EmailAdd = ?'
    );
    $duplicateCheck->bind_param('sssss', $lastName, $firstName, $birthday, $mobileNo, $email);
    $duplicateCheck->execute();
    if ($duplicateCheck->get_result()->num_rows > 0) {
        $duplicateCheck->close();
        respond(409, ['message' => 'An application with these details has already been submitted.']);
    }
    $duplicateCheck->close();

    if ($pendingUploads && !is_dir($uploadDirectory)
        && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
        throw new RuntimeException('Could not create the application upload directory.');
    }

    $details = [
        'nationality' => $application['nationality'] ?? '',
        'gender' => $application['gender'] ?? '',
        'vesselTypePreference' => $application['vesselTypePreference'] ?? '',
        'availableDate' => $application['availableDate'] ?? '',
        'srnNumber' => $application['srnNumber'] ?? '',
        'yearsExperience' => $application['yearsExperience'] ?? '',
        'lastVesselName' => $application['lastVesselName'] ?? '',
        'lastCompany' => $application['lastCompany'] ?? '',
        'attachments' => [],
    ];

    $db->begin_transaction();
    $insert = $db->prepare(
        'INSERT INTO applicant_info
            (App_PositionApplied, App_LName, App_FName, App_MName, App_Bday, App_BPlace,
             App_EmailAdd, App_MobileNo, App_DateApplied, App_Address, App_CivilStat,
             App_WifeName, App_Facebook, App_Viber, App_WhatsApp, App_Skype, App_Status,
             App_Image, App_AddDetails, App_Consent, App_Source)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $position = trim((string) $application['appliedRank']);
    $middleName = trim((string) ($application['middleName'] ?? '')) ?: null;
    $birthPlace = trim((string) $application['pob']);
    $dateApplied = date('Y-m-d');
    $address = trim((string) $application['address']);
    $civilStatus = (string) ($application['civilStatus'] ?? '');
    $emptyValue = null;
    $status = 'Applicant';
    $imageName = '';
    $consent = 'YES';
    $source = 'React application form';
    $detailsJson = '';
    $insert->bind_param(
        'sssssssssssssssssssss',
        $position,
        $lastName,
        $firstName,
        $middleName,
        $birthday,
        $birthPlace,
        $email,
        $mobileNo,
        $dateApplied,
        $address,
        $civilStatus,
        $emptyValue,
        $emptyValue,
        $emptyValue,
        $emptyValue,
        $emptyValue,
        $status,
        $imageName,
        $detailsJson,
        $consent,
        $source
    );

    foreach ($pendingUploads as $field => $upload) {
        $destination = $uploadDirectory . '/' . $upload['storedName'];
        if (!move_uploaded_file($upload['temporaryPath'], $destination)) {
            throw new RuntimeException('Could not store an uploaded application document.');
        }
        $savedFiles[] = $destination;
        $details['attachments'][$field] = $upload['storedName'];
    }

    $detailsJson = json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($detailsJson === false) {
        throw new RuntimeException('Could not encode application details.');
    }
    $insert->execute();
    $insert->close();
    $appId = (int) $db->insert_id;
    $db->commit();

    respond(201, ['referenceNumber' => 'CSI-' . str_pad((string) $appId, 6, '0', STR_PAD_LEFT)]);
} catch (Throwable $error) {
    if ($db instanceof mysqli && $db->thread_id) {
        try {
            $db->rollback();
        } catch (Throwable) {
        }
    }
    foreach ($savedFiles as $savedFile) {
        if (is_file($savedFile)) {
            unlink($savedFile);
        }
    }
    error_log('React application submission failed: ' . $error->getMessage());
    respond(500, ['message' => 'We could not save your application right now. Please try again later.']);
}