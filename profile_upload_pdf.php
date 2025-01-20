<?php

$g['mobile_redirect_off'] = true;
$area = "login";
include("./_include/core/main_start.php");
require_once '_include/current/CustomAi.php';

global $g_user;

if(!empty($_FILES["file"]["name"])){ 
    $user_id = $g_user['user_id'];

    $e_user_id = get_param('e_user_id', 0);
    if($e_user_id)
        $user_id = $e_user_id;
    isset($user_id) ? _isAuthID($user_id) : _unAuthenticate();

    $fileType = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
    $fileName =  md5($user_id).'.'.$fileType;
    $targetDir = $g['path']['dir_files'].'pdf/';
    $targetFilePath = $targetDir . $fileName;

    // FILE SIZE
    $file_size = $_FILES["file"]["size"]; // File size in bytes
    $maxSize = 1 * 1024 * 1024; // 1 MB in bytes

    $text = '';

    if($fileType == "pdf" || $fileType == "docx") {

        if($fileType == "pdf") {
            $PARSE_SUCCESS = false;

            // READ PDF
            include '_server/read-pdf/vendor/autoload.php';
            $parser = new \Smalot\PdfParser\Parser();

            try {
                // PARSE FILE
                $pdf = $parser->parseFile($_FILES["file"]["tmp_name"]);

                try {
                    $text = $pdf->getText();
                    $PARSE_SUCCESS = true;
                } catch (Exception $e) {

                }
            } catch (Exception $e) {
                // echo 'Error parsing PDF: ',  $e->getMessage();
            }

            if ($file_size < $maxSize) {
                
                // UPLOADED
                $uploadCV = move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath);

                if($PARSE_SUCCESS == false || strlen($text) < 100 && $uploadCV){
                    try {
                        $text = unReadAblePDFtoText($targetFilePath);
                    } catch (Exception $e) {

                    }
                }
            }

        } else { // docx

            if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){

                require_once '_include/current/CustomReadDocx.php';
                $CustomReadDocx = new CustomReadDocx();

                try {
                    $text = $CustomReadDocx->ConvertDocxToText($targetFilePath);
                } catch (Exception $e) {

                }
            }
        }

        $pdfText = sanitizeAndValidateString($text);

        
        if(strlen($pdfText) > 100) {

            $_data = [
                'error'     => 0,
                'success'   => 1,
                'message'   => l('document_not_readable_for_biodata').' But '.l('biodata_uploaded_successfully'),
            ];

            // upload biodata
            $insertData = [
                'profile_pdf'       => $fileName,
                'pdfAiData'         => '',
                'aiDataAdded_on'    => date("Y-m-d H:i:s"),
            ];

            $customAi = new CustomAi();

            try {
                $pdfAiData = $customAi->BiodataToJSON($pdfText);
            } catch (Exception $e) {
                $pdfAiData = '';
            }

            if(isJson($pdfAiData)) {
                $insertData['pdfAiData'] = $pdfAiData;

                // finally save
                try {
                    $customAi->saveAiData($user_id, $pdfAiData);
                } catch (Exception $e) {

                }

                // override the message
                $_data['message'] = l('biodata_uploaded_successfully');
            }

            // save data into db
            DB::update('userinfo', $insertData, '`user_id` = ' . to_sql($user_id));


        } else {
            $_data = [
                'error'     => 1,
                'success'   => 0,
                'message'   => l('document_not_readable_for_biodata'),
            ];
        }
    } else {
        $_data = [
            'error'     => 1,
            'success'   => 0,
            'message'   => l('format_incorrect'),
        ];
    }
} else {
    $_data = [
        'error'     => 1,
        'success'   => 0,
        'message'   => l('biodata_uploaded_failed'),
    ];
}

echo json_encode($_data);
die;
?>