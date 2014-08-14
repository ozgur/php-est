<?php
mb_internal_encoding("utf-8");

class EST {
  private $slug; // akbank, garanti, finansbank, halkbank, isbank, anadolubank
  private $company; // merchant id
  private $name;
  private $password;
  private $debug; // TRUE for debug mode
  private $credentials = null;
  public $raw_response = null;
  public $raw_request = null;

  private static $banksDetails = array("garanti" => array("host" => "ccpos.garanti.com.tr",
                                                          "testhost" => "ccpostest.garanti.com.tr",
                                                          "listOrdersURL" => "/servlet/ozelrapor",
                                                          "detailOrderURL" => "/servlet/cc5ApiServer",
                                                          "cancelOrderURL" => "/servlet/cc5ApiServer",
                                                          "returnOrderURL" => "/servlet/cc5ApiServer",
                                                          "purchaseOrderURL" => "/servlet/cc5ApiServer"),

                                       "akbank" => array("host" => "www.sanalakpos.com",
                                                         "testhost" => "testsanalpos.est.com.tr",
                                                         "listOrdersURL" => "/servlet/listapproved",
                                                         "detailOrderURL" => "/servlet/cc5ApiServer",
                                                         "cancelOrderURL" => "/servlet/cc5ApiServer",
                                                         "returnOrderURL" => "/servlet/cc5ApiServer",
                                                         "purchaseOrderURL" => "/servlet/cc5ApiServer"),

                                       "finansbank" => array("host" => "www.fbwebpos.com",
                                                             "testhost" => "testsanalpos.est.com.tr",
                                                             "listOrdersURL" => "/servlet/listapproved",
                                                             "detailOrderURL" => "/servlet/cc5ApiServer",
                                                             "cancelOrderURL" => "/servlet/cc5ApiServer",
                                                             "returnOrderURL" => "/servlet/cc5ApiServer",
                                                             "purchaseOrderURL" => "/servlet/cc5ApiServer"),

                                       "halkbank" => array("host" => "sanalpos.halkbank.com.tr",
                                                           "testhost" => "testsanalpos.est.com.tr",
                                                           "listOrdersURL" => "/servlet/listapproved",
                                                           "detailOrderURL" => "/servlet/cc5ApiServer",
                                                           "cancelOrderURL" => "/servlet/cc5ApiServer",
                                                           "returnOrderURL" => "/servlet/cc5ApiServer",
                                                           "purchaseOrderURL" => "/servlet/cc5ApiServer"),

                                       "isbank" => array("host" => "spos.isbank.com.tr",
                                                         "testhost" => "testsanalpos.est.com.tr",
                                                         "listOrdersURL" => "/servlet/listapproved",
                                                         "detailOrderURL" => "/servlet/cc5ApiServer",
                                                         "cancelOrderURL" => "/servlet/cc5ApiServer",
                                                         "returnOrderURL" => "/servlet/cc5ApiServer",
                                                         "purchaseOrderURL" => "/servlet/cc5ApiServer"),

                                       "anadolubank" => array("host" => "anadolusanalpos.est.com.tr",
                                                              "testhost" => "testsanalpos.est.com.tr",
                                                              "listOrdersURL" => "/servlet/listapproved",
                                                              "detailOrderURL" => "/servlet/cc5ApiServer",
                                                              "cancelOrderURL" => "/servlet/cc5ApiServer",
                                                              "returnOrderURL" => "/servlet/cc5ApiServer",
                                                              "purchaseOrderURL" => "/servlet/cc5ApiServer"));

  public function __construct($slug, $company, $name, $password, $debug = TRUE) {
    $possibleSlugs = array("akbank", "garanti", "finansbank", "isbank", "anadolubank", "halkbank");
    // If the slug is not among the possible slugs, then immediately throw an exception..
    if(!in_array($slug, $possibleSlugs)) {
      throw new Exception("Geçersiz bir slug seçtiniz.");
    }

    $this->slug = $slug;
    $this->company = $company;
    $this->name = $name;
    $this->password = $password;
    $this->debug = $debug;
    $this->credentials = self::$banksDetails[$this->slug];
  }

  private function __get_credentials() {
    if($this->credentials) return $this->credentials;
    if($this->slug) {
      if(array_key_exists($this->slug, self::$banksDetails)) {
        return self::$banksDetails[$this->slug];
      }
      return null;
    }
    return null;
  }

  private function __connect() {
    if($this->debug)
      return "https://" . $this->credentials["testhost"];
    else return "https://" . $this->credentials["host"];
  }

  public function pay($credit_card_number, $cvv, $month, $year, $amount, $installment, $orderid, $typ = "Auth", $extra = array()) {
    $builder = new XMLBuilder();
    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
    $year = str_pad($year, 2, "0", STR_PAD_LEFT);
    $expires = $month . $year;

    $amount = number_format($amount, 2, '.', '');

    $credentials = $this->__get_credentials();
    $username = $this->name;
    $password = $this->password;
    $clientid = $this->company;

    $email = $this->__get_value($extra, "email");
    $ipaddress = $this->__get_value($extra, "ipaddress");
    $userid = $this->__get_value($extra, "userid");

    $document = new XMLBuilder();
    $elements = array("Name" => $username, "Password" => $password, "ClientId" => $clientid,
                      "Mode" => "P", "OrderId" => $orderid, "Type" => $typ, "Currency" => "949",
                      "GroupId" => "", "TransId" => "", "UserId" => $userid, "Extra" => "",
                      "Taksit" => $installment, "Number" => $credit_card_number, "Expires" => $expires,
                      "Cvv2Val" => $cvv, "Total" => $amount, "Email" => $email, "IPAddress" => $ipaddress
    );
    $domElements = $document->createElementsWithTextNodes($elements);
    $document->appendListOfElementsToElement($document->root(), $domElements);

    $billto = $document->createElement("BillTo");
    $billing_address_name = $this->__get_value($extra, "billing_address_name");
    $billing_address_street1 = $this->__get_value($extra, "billing_address_street1");
    $billing_address_street2 = $this->__get_value($extra, "billing_address_street2");
    $billing_address_street3 = $this->__get_value($extra, "billing_address_street3");
    $billing_address_city = $this->__get_value($extra, "billing_address_city");
    $billing_address_company = $this->__get_value($extra, "billing_address_company");
    $billing_address_postalcode = $this->__get_value($extra, "billing_address_postalcode");
    $billing_address_telvoice = $this->__get_value($extra, "billing_address_telvoice");
    $billing_address_state = $this->__get_value($extra, "billing_address_state");

    $elements = array("Name" => $billing_address_name, "Street1" => $billing_address_street1,
                      "Street2" => $billing_address_street2, "Street3" => $billing_address_street3,
                      "City" => $billing_address_city, "StateProv" => $billing_address_state,
                      "PostalCode" => $billing_address_postalcode, "Country" => "Türkiye",
                      "Company" => $billing_address_company, "TelVoice" => $billing_address_telvoice
    );
    $domElements = $document->createElementsWithTextNodes($elements);
    $document->appendListOfElementsToElement($billto, $domElements);
    $document->root()->appendChild($billto);


    $shipto = $document->createElement("ShipTo");
    $shipping_address_name = $this->__get_value($extra, "shipping_address_name");
    $shipping_address_street1 = $this->__get_value($extra, "shipping_address_street1");
    $shipping_address_street2 = $this->__get_value($extra, "shipping_address_street2");
    $shipping_address_street3 = $this->__get_value($extra, "shipping_address_street3");
    $shipping_address_city = $this->__get_value($extra, "shipping_address_city");
    $shipping_address_company = $this->__get_value($extra, "shipping_address_company");
    $shipping_address_postalcode = $this->__get_value($extra, "shipping_address_postalcode");
    $shipping_address_telvoice = $this->__get_value($extra, "shipping_address_telvoice");
    $shipping_address_state = $this->__get_value($extra, "shipping_address_state");

    $elements = array("Name" => $shipping_address_name, "Street1" => $shipping_address_street1,
                      "Street2" => $shipping_address_street2, "Street3" => $shipping_address_street3,
                      "City" => $shipping_address_city, "StateProv" => $shipping_address_state,
                      "PostalCode" => $shipping_address_postalcode, "Country" => "Türkiye",
                      "Company" => $shipping_address_company, "TelVoice" => $shipping_address_telvoice
    );

    $domElements = $document->createElementsWithTextNodes($elements);
    $document->appendListOfElementsToElement($shipto, $domElements);
    $document->root()->appendChild($shipto);
    $documentString = $document->saveXML();
    $this->raw_request = $documentString;

    /* After the XML request has been created, we should now set the HTTP request using curl library..   */
    $url = $this->__connect() . $this->credentials["purchaseOrderURL"];
    $curl = curl_init();
    $postData = urlencode("DATA") . "=" . urlencode($documentString);
    // Set the url..
    curl_setopt($curl, CURLOPT_URL, $url);
    // Set the HTTP method to POST..
    curl_setopt($curl, CURLOPT_POST, 1);
    // Set the HTTP response header to False not to get the response header..
    curl_setopt ($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // Add the HTTP POST body..
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    // Set the HTTP request header..
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type" => "application/x-www-form-urlencoded"));
    // Execute the request and save the response inside a variable called 'raw_response'..
    $this->raw_response = curl_exec($curl);
    // Close the connection..
    curl_close($curl);

    // After we got the response, we should now parse it using xml library..
    $responseDomObject = new DOMDocument();
    $responseDomObject->loadXML($this->raw_response);
    // The result to be returned will be an array containing the response details..
    $result = array();
    try {
      $orderid = XMLBuilder::get_data($responseDomObject, "OrderId");
      $groupid = XMLBuilder::get_data($responseDomObject, "GroupId");
      $transid = XMLBuilder::get_data($responseDomObject, "TransId");
      $response = XMLBuilder::get_data($responseDomObject, "Response");
      $return_code = XMLBuilder::get_data($responseDomObject, "ProcReturnCode");
      $error_msg = XMLBuilder::get_data($responseDomObject, "ErrMsg");
      $host_msg = XMLBuilder::get_data($responseDomObject, "HOSTMSG");
      $trx_date = XMLBuilder::get_data($responseDomObject, "TRXDATE");
      $auth_code = XMLBuilder::get_data($responseDomObject, "AuthCode");
      $is_successful = FALSE;
      if(intval($return_code) == 0) {
        $is_successful = TRUE;
      }
      $result["orderid"] = $orderid;
      $result["transid"] = $transid;
      $result["groupid"] = $groupid;
      $result["response"] = $response;
      $result["return_code"] = $return_code;
      $result["error_msg"] = $error_msg;
      $result["host_msg"] = $host_msg;
      $result["auth_code"] = $auth_code;
      $result["result"] = $is_successful;
    }
    catch(Exception $e){
      $result["result"] = FALSE;
      $result["exception"] = $e->getMessage();
    }

    if(isset($trx_date)) {
      try {
        $trx_date = explode(".", $trx_date);
        $trx_date = $trx_date[0];
        $trx_date = strptime($trx_date, "%Y%m%d %H:%M:%S");
        $result["transaction_time"] = $trx_date;
      }
      catch(Exception $e) {
        // pass
      }
    }
    return $result;
  }

  public function cancel($orderid, $transid = null) {
    $credentials = $this->__get_credentials();
    $username = $this->name;
    $password = $this->password;
    $clientid = $this->company;

    $document = new XMLBuilder();
    $elements = array("Name" => $username, "Password" => $password, "ClientId" => $clientid,
                      "Mode" => "P", "OrderId" => $orderid, "Type" => "Void", "Currency" => "949"
    );

    // Include the transaction id if the actual parameter for 'transid' is not null..
    if($transid) {
      $elements["TransId"] = $transid;
    }
    $domElements = $document->createElementsWithTextNodes($elements);
    $document->appendListOfElementsToElement($document->root(), $domElements);
    $documentString = $document->saveXML();
    $this->raw_request = $documentString;

    /* After the XML request has been created, we should now set the HTTP request using curl library..   */
    $url = $this->__connect() . $this->credentials["cancelOrderURL"];
    $curl = curl_init();
    $postData = urlencode("DATA") . urlencode("=") . urlencode($documentString);
    // Set the url..
    curl_setopt($curl, CURLOPT_URL, $url);
    // Set the HTTP method to POST..
    curl_setopt($curl, CURLOPT_POST, 1);
    // Set the HTTP response header to False not to get the response header..
    curl_setopt ($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // Add the HTTP POST body..
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    // Set the HTTP request header..
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type" => "application/x-www-form-urlencoded"));
    // Execute the request and save the response inside a variable called 'raw_response'..
    $this->raw_response = curl_exec($curl);
    // Close the connection..
    curl_close($curl);

    // After we got the response, we should now parse it using xml library..
    $responseDomObject = new DOMDocument();
    $responseDomObject->loadXML($this->raw_response);
    // The result to be returned will be an array containing the response details..
    $result = array();
    try {
      $orderid = XMLBuilder::get_data($responseDomObject, "OrderId");
      $groupid = XMLBuilder::get_data($responseDomObject, "GroupId");
      $transid = XMLBuilder::get_data($responseDomObject, "TransId");
      $response = XMLBuilder::get_data($responseDomObject, "Response");
      $return_code = XMLBuilder::get_data($responseDomObject, "ProcReturnCode");
      $error_msg = XMLBuilder::get_data($responseDomObject, "ErrMsg");
      $host_msg = XMLBuilder::get_data($responseDomObject, "HOSTMSG");
      $trx_date = XMLBuilder::get_data($responseDomObject, "TRXDATE");
      $host_ref_num = XMLBuilder::get_data($responseDomObject, "HostRefNum");
      $auth_code = XMLBuilder::get_data($responseDomObject, "AuthCode");
      $is_successful = FALSE;
      if(intval($return_code) == 0) {
        $is_successful = TRUE;
      }
      $result["orderid"] = $orderid;
      $result["transid"] = $transid;
      $result["groupid"] = $groupid;
      $result["response"] = $response;
      $result["return_code"] = $return_code;
      $result["error_msg"] = $error_msg;
      $result["host_msg"] = $host_msg;
      $result["auth_code"] = $auth_code;
      $result["host_ref_num"] = $host_ref_num;
      $result["result"] = $is_successful;
    }
    catch(Exception $e){
      $result["result"] = FALSE;
      $result["exception"] = $e->getMessage();
    }

    if(isset($trx_date)) {
      try {
        $trx_date = explode(".", $trx_date);
        $trx_date = $trx_date[0];
        $trx_date = strptime($trx_date, "%Y%m%d %H:%M:%S");
        $result["transaction_time"] = $trx_date;
      }
      catch(Exception $e) {
        // pass
      }
    }
    return $result;
  }

  public function refund($amount, $orderid) {
    $credentials = $this->__get_credentials();
    $username = $this->name;
    $password = $this->password;
    $clientid = $this->company;

    $amount = number_format($amount, 2);
    $document = new XMLBuilder();
    $elements = array("Name" => $username, "Password" => $password, "ClientId" => $clientid,
                      "Mode" => "P", "OrderId" => $orderid, "Type" => "Credit", "Currency" => "949",
                      "Total" => $amount
    );
    $domElements = $document->createElementsWithTextNodes($elements);
    $document->appendListOfElementsToElement($document->root(), $domElements);
    $documentString = $document->saveXML();
    $this->raw_request = $documentString;

    /* After the XML request has been created, we should now set the HTTP request using curl library..   */
    $url = $this->__connect() . $this->credentials["returnOrderURL"];
    $curl = curl_init();
    $postData = urlencode("DATA") . urlencode("=") . urlencode($documentString);
    // Set the url..
    curl_setopt($curl, CURLOPT_URL, $url);
    // Set the HTTP method to POST..
    curl_setopt($curl, CURLOPT_POST, 1);
    // Set the HTTP response header to False not to get the response header..
    curl_setopt ($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // Add the HTTP POST body..
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    // Set the HTTP request header..
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type" => "application/x-www-form-urlencoded"));
    // Execute the request and save the response inside a variable called 'raw_response'..
    $this->raw_response = curl_exec($curl);
    // Close the connection..
    curl_close($curl);
    // After we got the response, we should now parse it using xml library..
    $responseDomObject = new DOMDocument();
    $responseDomObject->loadXML($this->raw_response);
    // The result to be returned will be an array containing the response details..
    $result = array();
    try {
      $orderid = XMLBuilder::get_data($responseDomObject, "OrderId");
      $groupid = XMLBuilder::get_data($responseDomObject, "GroupId");
      $transid = XMLBuilder::get_data($responseDomObject, "TransId");
      $response = XMLBuilder::get_data($responseDomObject, "Response");
      $return_code = XMLBuilder::get_data($responseDomObject, "ProcReturnCode");
      $error_msg = XMLBuilder::get_data($responseDomObject, "ErrMsg");
      $host_msg = XMLBuilder::get_data($responseDomObject, "HOSTMSG");
      $trx_date = XMLBuilder::get_data($responseDomObject, "TRXDATE");
      $host_ref_num = XMLBuilder::get_data($responseDomObject, "HostRefNum");
      $auth_code = XMLBuilder::get_data($responseDomObject, "AuthCode");
      $is_successful = FALSE;
      if(intval($return_code) == 0) {
        $is_successful = TRUE;
      }
      $result["orderid"] = $orderid;
      $result["transid"] = $transid;
      $result["groupid"] = $groupid;
      $result["response"] = $response;
      $result["return_code"] = $return_code;
      $result["error_msg"] = $error_msg;
      $result["host_msg"] = $host_msg;
      $result["auth_code"] = $auth_code;
      $result["host_ref_num"] = $host_ref_num;
      $result["result"] = $is_successful;
    }
    catch(Exception $e){
      $result["result"] = FALSE;
      $result["exception"] = $e->getMessage();
    }

    if(isset($trx_date)) {
      try {
        $trx_date = explode(".", $trx_date);
        $trx_date = $trx_date[0];
        $trx_date = strptime($trx_date, "%Y%m%d %H:%M:%S");
        $result["transaction_time"] = $trx_date;
      }
      catch(Exception $e) {
        // pass
      }
    }
    return $result;

  }

  public function postAuth($amount, $orderid, $transid = null) {
    $credentials = $this->__get_credentials();
    $username = $this->name;
    $password = $this->password;
    $clientid = $this->company;

    $amount = number_format($amount, 2);
    $document = new XMLBuilder();
    $elements = array("Name" => $username, "Password" => $password, "ClientId" => $clientid,
                      "Mode" => "P", "OrderId" => $orderid, "Type" => "PostAuth",
                      "Total" => $amount, "Extra" => null, "TransId" => $transid
    );
    $domElements = $document->createElementsWithTextNodes($elements);
    $document->appendListOfElementsToElement($document->root(), $domElements);
    $documentString = $document->saveXML();
    $this->raw_request = $documentString;

    /* After the XML request has been created, we should now set the HTTP request using curl library..   */
    $url = $this->__connect() . $this->credentials["purchaseOrderURL"];
    $curl = curl_init();
    $postData = urlencode("DATA") . urlencode("=") . urlencode($documentString);
    // Set the url..
    curl_setopt($curl, CURLOPT_URL, $url);
    // Set the HTTP method to POST..
    curl_setopt($curl, CURLOPT_POST, 1);
    // Set the HTTP response header to False not to get the response header..
    curl_setopt ($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // Add the HTTP POST body..
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    // Set the HTTP request header..
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type" => "application/x-www-form-urlencoded"));
    // Execute the request and save the response inside a variable called 'raw_response'..
    $this->raw_response = curl_exec($curl);
    // Close the connection..
    curl_close($curl);
    // After we got the response, we should now parse it using xml library..
    $responseDomObject = new DOMDocument();
    $responseDomObject->loadXML($this->raw_response);
    // The result to be returned will be an array containing the response details..
    $result = array();
    try {
      $orderid = XMLBuilder::get_data($responseDomObject, "OrderId");
      $groupid = XMLBuilder::get_data($responseDomObject, "GroupId");
      $transid = XMLBuilder::get_data($responseDomObject, "TransId");
      $response = XMLBuilder::get_data($responseDomObject, "Response");
      $return_code = XMLBuilder::get_data($responseDomObject, "ProcReturnCode");
      $error_msg = XMLBuilder::get_data($responseDomObject, "ErrMsg");
      $host_msg = XMLBuilder::get_data($responseDomObject, "HOSTMSG");
      $trx_date = XMLBuilder::get_data($responseDomObject, "TRXDATE");
      $host_ref_num = XMLBuilder::get_data($responseDomObject, "HostRefNum");
      $auth_code = XMLBuilder::get_data($responseDomObject, "AuthCode");
      $is_successful = FALSE;
      if(intval($return_code) == 0) {
        $is_successful = TRUE;
      }
      $result["orderid"] = $orderid;
      $result["transid"] = $transid;
      $result["groupid"] = $groupid;
      $result["response"] = $response;
      $result["return_code"] = $return_code;
      $result["error_msg"] = $error_msg;
      $result["host_msg"] = $host_msg;
      $result["auth_code"] = $auth_code;
      $result["host_ref_num"] = $host_ref_num;
      $result["result"] = $is_successful;
    }
    catch(Exception $e){
      $result["result"] = FALSE;
      $result["exception"] = $e->getMessage();
    }

    if(isset($trx_date)) {
      try {
        $trx_date = explode(".", $trx_date);
        $trx_date = $trx_date[0];
        $trx_date = strptime($trx_date, "%Y%m%d %H:%M:%S");
        $result["transaction_time"] = $trx_date;
      }
      catch(Exception $e) {
        // pass
      }
    }
    return $result;
  }

  public function getDetail($orderid) {
    $credentials = $this->__get_credentials();
    $username = $this->name;
    $password = $this->password;
    $clientid = $this->company;

    $document = new XMLBuilder();
    $elements = array("Name" => $username, "Password" => $password, "ClientId" => $clientid,
                      "Mode" => "P", "OrderId" => $orderid
    );
    $domElements = $document->createElementsWithTextNodes($elements);
    $document->appendListOfElementsToElement($document->root(), $domElements);
    $element = $document->createElement("Extra");
    $statusElement = $document->createElementWithTextNode("ORDERSTATUS", "SOR");
    $element->appendChild($statusElement);
    $document->root()->appendChild($element);
    $documentString = $document->saveXML();
    $this->raw_request = $documentString;

    /* After the XML request has been created, we should now set the HTTP request using curl library..   */
    $url = $this->__connect() . $this->credentials["detailOrderURL"];
    $curl = curl_init();
    $postData = urlencode("DATA") . urlencode("=") . urlencode($documentString);
    // Set the url..
    curl_setopt($curl, CURLOPT_URL, $url);
    // Set the HTTP method to POST..
    curl_setopt($curl, CURLOPT_POST, 1);
    // Set the HTTP response header to False not to get the response header..
    curl_setopt ($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // Add the HTTP POST body..
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    // Set the HTTP request header..
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type" => "application/x-www-form-urlencoded"));
    // Execute the request and save the response inside a variable called 'raw_response'..
    $this->raw_response = curl_exec($curl);
    // Close the connection..
    curl_close($curl);
    // After we got the response, we should now parse it using xml library..
    $responseDomObject = new DOMDocument();
    $responseDomObject->loadXML($this->raw_response);
    // The result to be returned will be an array containing the response details..
    $result = array();

    $transid = XMLBuilder::get_data($responseDomObject, "TransId");
    $return_code = XMLBuilder::get_data($responseDomObject, "ProcReturnCode");
    $err_msg = XMLBuilder::get_data($responseDomObject, "ErrMsg");
    $host_ref_num = XMLBuilder::get_data($responseDomObject, "HOST_REF_NUM");
    $auth_code = XMLBuilder::get_data($responseDomObject, "AUTH_CODE");
    $charge_type = XMLBuilder::get_data($responseDomObject, "CHARGE_TYPE_CD");
    $details = XMLBuilder::get_data($responseDomObject, "ORDERSTATUS");
    $capture_amount = XMLBuilder::get_data($responseDomObject, "CAPTURE_AMT");
    $trx_date = XMLBuilder::get_data($responseDomObject, "CAPTURE_DTTM");

    $result["transid"] = $transid;
    $result["orderid"] = $orderid;
    $result["return_code"] = $return_code;
    $result["host_ref_num"] = $host_ref_num;
    $result["error_msg"] = $err_msg;
    $result["charge_type"] = $charge_type;
    $result["auth_code"] = $auth_code;
    $result["amount"] = "";
    $result["transaction_time"] = "";

    if($trx_date) {
      try {
        $trx_date = explode(".", $trx_date);
        $trx_date = $trx_date[0];
        $trx_date = strptime($trx_date, "%Y-%m-%d %H:%M:%S");
        $result["transaction_time"] = $trx_date;
      }
      catch(Exception $e) { }
    }

    if ($capture_amount) {
      try {
        $capture_amount = intval($capture_amount) / 100.0;
        $result["amount"] = $capture_amount;
      }
      catch(Exception $e) { }
    }
    return $result;

  }

  private function __get_value($array, $key) {
    if(array_key_exists($key, $array)) {
      return $array[$key];
    }
    return null;
  }

  public function __toString() {
    return $this->slug . " sanalpos";
  }

}

class XMLBuilder extends DOMDocument {
  public $rootElement;

  public function __construct($tag = "CC5Request") {
    parent::__construct("1.0");
    $element = $this->createElement($tag);
    $this->rootElement = $element;
    $this->appendChild($this->rootElement);
  }

  public function root() {
    return $this->rootElement;
  }

  public function createElementWithTextNode($tagName, $nodeValue) {
    if($nodeValue == null) {
      $nodeValue = "";
    }
    $element = $this->createElement(strval($tagName));
    $node = $this->createTextNode(strval($nodeValue));
    $element->appendChild($node);
    return $element;
  }

  public function createElementsWithTextNodes($arguments) {
    $resultArray = array();
    foreach($arguments as $k => $v) {
      array_push($resultArray, $this->createElementWithTextNode($k, $v));
    }
    return $resultArray;
  }

  public function appendListOfElementsToElement($element, $elements) {
    /* Appends list of DOM elements to the given DOM element. */
    foreach($elements as $ele) {
      $element->appendChild($ele);
    }
  }

  public function __toString() {
    return $this->saveXML();
  }

  public static function get_data($xmlObj, $tag) {
    $elements = $xmlObj->getElementsByTagName($tag);
    if($elements->length > 0) {
      $item = $elements->item(0);
      $childiren = $item->childNodes;
      if($childiren->length > 0) {
        return $childiren->item(0)->nodeValue;
      }
      return "";
    }
    return "";
  }

}

?>