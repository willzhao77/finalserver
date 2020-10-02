<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Redirect;
class OssisTestController extends Controller
{
  public function index()
  {
    $filename='token.txt';
    $token = file_get_contents($filename);

    if($token == ''){
      $testreturn = new \stdClass();
      $testreturn -> State = "ok";
      $testreturn -> session = "false";
      return json_encode($testreturn);

    }
  // config(['global.workflowtoken' => 'testGtoken']);
  // $globals['workflowtoken'] = 'test token';
  // dd($GLOBALS['workflowtoken']);

// //   $xml=simplexml_load_string($data) or die("Error: Cannot create object");
// //   dd ($xml->Job->Type);




      // get data from workflowmax
      session_start();
      // dd(!isset($_SESSION['oauth2']['token']));

      // if( !isset($_GET["token"])){
      //   // return Redirect::to('/');
      //   $testreturn = new \stdClass();
      //   $testreturn -> State = "ok";
      //   $testreturn -> session = "false";
      //
      //
      //
      //   return json_encode($testreturn);
      // }



      //request basic info
      $responseBasic = Http::withHeaders([
          'xero-tenant-id' => 'b01c0f54-45c5-439b-b103-97ef6ab6f588',
          'Authorization' => 'Bearer ' . $token,
      ])->get('https://api.xero.com/workflowmax/3.0/job.api/get/60540');


      //request custom fields
      $responseCustom = Http::withHeaders([
          'xero-tenant-id' => 'b01c0f54-45c5-439b-b103-97ef6ab6f588',
          'Authorization' => 'Bearer ' . $token,
      ])->get('https://api.xero.com/workflowmax/3.0/job.api/get/60540/customfield');



      //
      // //SimpleXML is an extension that allows us to easily manipulate and get XML data.
      // // https://www.w3schools.com/php/php_ref_simplexml.asp
      $xmlBasic=simplexml_load_string($responseBasic) or die("Error: Cannot create object");
      $xmlCustom=simplexml_load_string($responseCustom) or die("Error: Cannot create object");

      // foreach($xml->products->item as $item)
      // {
      //     echo (string)$item->product_id;
      //     echo (string)$item->model;
      // }


      // dd($xmlBasic);
      $array = (array)$xmlCustom->CustomFields;
      // $arraydata = (array)$array['CustomField'][0];
      $arrayfields = (array)$array['CustomField'];

      //customfield
      // $customfields = new \stdClass();

      for( $x = 0; $x < count($arrayfields); $x++){
        $arraydata = (array)$array['CustomField'][$x];
        $valueType = 0; // if 0, means key, if 1 means value
        $key = '';
        $value = '';

        foreach($arraydata as $d => $d_value) {
          if($d == 'UUID')
          continue;
          if($valueType == 0){
            $key = $d_value;

          }else {
            $value = $d_value;
          }
          $valueType = 1;
        }
        //create Associative Arrays
        // https://www.w3schools.com/php/php_arrays_associative.asp
        $customfields[$key] = $value;
      }


      // dd($customfields);



      //return JSON php
      // https://www.w3schools.com/js/js_json_php.asp
      // need to declare $result as an object of stdClass in the global namespace:
      $result = new \stdClass();
      $result -> ID = strval($xmlBasic->Job->ID);
      $result -> Client = strval($xmlBasic->Job->Client->Name);
      $result -> State = strval($xmlBasic->Job->State);

      $result -> PatientName = $customfields['Patient Name'];
      $result -> DateOfBirth = $customfields['Date Of Birth'];
      $result -> Hospital = $customfields['Hospital'];
      $result -> DeviceType = $customfields['Device Type'];
      $result -> Anatomy = $customfields['Anatomy'];
      $result -> Pathology = $customfields['Pathology'];
      //https://wiki.php.net/rfc/isset_ternary
      $result -> SurgicalApproach = $customfields['Surgical Approach']??'';
      $result -> SurgeryDate = $customfields['Surgery Date']??'';

      return json_encode($result);

  }
}
