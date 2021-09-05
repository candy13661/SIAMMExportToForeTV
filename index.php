<?php

// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//Exception message
function exception_handler($exception)
{
    echo "Uncaught exception: ", $exception->getMessage(), "\n";
}

set_exception_handler('exception_handler');

// Only allow POST requests
if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
    throw new Exception('Only POST requests are allowed');
}

//Function to read request header
function getRequestHeaders()
{
    $headers = array();
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header           = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    return $headers;
}

// get posted data
$data = json_decode(file_get_contents("php://input"));

// make sure data is not empty
if (!empty($data->FileContent)) {
    //Read request header
    $headers        = getRequestHeaders();
    $filename       = $headers['Filename'];
    $token          = $headers['Token'];
    //echo $token;
    //echo $filename;
    $filenameString = substr($filename, 0, strpos($filename, "_"));
    //echo $filenameString;
    
    //Save XML content into file form API request
    $xmlString               = $data->FileContent;
    $dom                     = new DOMDocument;
    $dom->preserveWhiteSpace = FALSE;
    $dom->loadXML($xmlString);
    $dom->xmlVersion    = '1.0';
    $dom->encoding      = 'UTF-8';
    $dom->xmlStandalone = FALSE;
    $dom->formatOutput  = TRUE;
    //echo $xmlString;
    
    if ($filenameString == "Planning Schedule") {
        //Save XML as a file in PlanningSchedule
        $dom->save('D:\Export\SIAMM\Live\PlanningSchedule/' . $headers['Filename']);
    } else {
        //Save XML as a file in EPGMetadata
        $dom->save('D:\Export\SIAMM\Live\EPGMetadata/' . $headers['Filename']);
    }
    
    // set response code - 201 created
    http_response_code(201);
    
    // tell the user
    echo json_encode(array(
        "responseMessage" => "Success"
    ));
    //echo $data->FileContent;
    //echo $data->token;        
    
}

// tell the user data is incomplete
else {
    
    // set response code - 400 bad request
    http_response_code(400);
    
    // tell the user
    echo json_encode(array(
        "responseMessage" => "Invalid API body."
    ));
}
?>
