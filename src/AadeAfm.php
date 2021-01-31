<?php
namespace Iggi;

class AadeAfm
{

    protected $uri = "https://www1.gsis.gr/webtax2/wsgsis/RgWsPublic/RgWsPublicPort";
    protected $username;
    protected $password;
    protected $afm;
    protected $proxy = null;
    protected $debug = 0;
    protected $curl;

    const ERRORS = [
        "RG_WS_PUBLIC_AFM_CALLED_BY_BLOCKED" => "Ο χρήστης που καλεί την υπηρεσία έχει προσωρινά αποκλειστεί από τη χρήση της.",
        "RG_WS_PUBLIC_AFM_CALLED_BY_NOT_FOUND" => "Ο Α.Φ.Μ. για τον οποίο γίνεται η κλήση δε βρέθηκε στους έγκυρους Α.Φ.Μ. του Μητρώου TAXIS.",
        "RG_WS_PUBLIC_EPIT_NF" => "O Α.Φ.Μ. για τον οποίο ζητούνται πληροφορίες δεν ανήκει και δεν ανήκε ποτέ σε νομικό πρόσωπο, νομική οντότητα, ή φυσικό πρόσωπο με εισόδημα από επιχειρηματική δραστηριότητα.",
        "RG_WS_PUBLIC_FAILURES_TOLERATED_EXCEEDED" => "Υπέρβαση μέγιστου επιτρεπτού ορίου πρόσφατων αποτυχημένων κλήσεων. Προσπαθήστε εκ νέου σε μερικές ώρες.",
        "RG_WS_PUBLIC_MAX_DAILY_USERNAME_CALLS_EXCEEDED" => "Υπέρβαση μέγιστου επιτρεπτού ορίου ημερήσιων κλήσεων ανά χρήστη (ανεξαρτήτως εξουσιοδοτήσεων).",
        "RG_WS_PUBLIC_MONTHLY_LIMIT_EXCEEDED" => "Υπέρβαση του Μέγιστου Επιτρεπτού Μηνιαίου Ορίου Κλήσεων.",
        "RG_WS_PUBLIC_MSG_TO_TAXISNET_ERROR" => "Δημιουργήθηκε πρόβλημα κατά την ενημέρωση των εισερχόμενων μηνυμάτων στο MyTAXISnet.",
        "RG_WS_PUBLIC_NO_INPUT_PARAMETERS" => "Δε δόθηκαν υποχρεωτικές παράμετροι εισόδου για την κλήση της υπηρεσίας.",
        "RG_WS_PUBLIC_SERVICE_NOT_ACTIVE" => "Η υπηρεσία δεν είναι ενεργή.",
        "RG_WS_PUBLIC_TAXPAYER_NF" => "O Α.Φ.Μ. για τον οποίο ζητούνται πληροφορίες δε βρέθηκε στους έγκυρους Α.Φ.Μ. του Μητρώου TAXIS.",
        "RG_WS_PUBLIC_TOKEN_AFM_BLOCKED" => "Ο χρήστης (ή ο εξουσιοδοτημένος τρίτος) που καλεί την υπηρεσία έχει προσωρινά αποκλειστεί από τη χρήση της.",
        "RG_WS_PUBLIC_TOKEN_AFM_NOT_AUTHORIZED" => "Ο τρέχον χρήστης δεν έχει εξουσιοδοτηθεί από τον Α.Φ.Μ. για χρήση της υπηρεσίας.",
        "RG_WS_PUBLIC_TOKEN_AFM_NOT_FOUND" => "Ο Α.Φ.Μ. του τρέχοντος χρήστη δε βρέθηκε στους έγκυρους Α.Φ.Μ. του Μητρώου TAXIS.",
        "RG_WS_PUBLIC_TOKEN_AFM_NOT_REGISTERED" => "Ο τρέχον χρήστης δεν έχει εγγραφεί για χρήση της υπηρεσίας.",
        "RG_WS_PUBLIC_TOKEN_USERNAME_NOT_ACTIVE" => "Ο κωδικός χρήστη (username) που χρησιμοποιήθηκε έχει ανακληθεί.",
        "RG_WS_PUBLIC_TOKEN_USERNAME_NOT_AUTHENTICATED" => "Ο συνδυασμός χρήστη/κωδικού πρόσβασης που δόθηκε δεν είναι έγκυρος.",
        "RG_WS_PUBLIC_TOKEN_USERNAME_NOT_DEFINED" => "Δεν ορίσθηκε ο χρήστης που καλεί την υπηρεσία.",
        "RG_WS_PUBLIC_TOKEN_USERNAME_TOO_LONG" => "Διαπιστώθηκε υπέρβαση του μήκους του ονόματος του χρήστη (username) της υπηρεσίας",
        "RG_WS_PUBLIC_WRONG_AFM" => "O Α.Φ.Μ. για τον οποίο ζητούνται πληροφορίες δεν είναι έγκυρος.",
    ];

    public function __construct($username, $password, $afm = "", $proxy = null, $debug = 0)
    {
        $this->username = $username;
        $this->password = $password;
        $this->afm = $afm;
        $this->proxy = $proxy;
        $this->debug = $debug;
        $this->curl = curl_init();
    }

    public static function xmlToJSON($xmlstr = "") {
        if (strlen($xmlstr) <= 0)
            return array();
        $doc = new \DOMDocument();
        $doc->loadXML($xmlstr);
        $root = $doc->documentElement;
        $output = self::domnode_to_array($root);
        $output['@root'] = $root->tagName;
        return $output;
    }

    public static function domnode_to_array($node)
    {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = self::domnode_to_array($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        if (!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    } elseif ($v || $v === '0') {
                        $output = (string)$v;
                    }
                }
                if ($node->attributes->length && !is_array($output)) { //Has attributes but isn't an array
                    $output = array('@content' => $output); //Change output into an array.
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string)$attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }

    protected function _toArray($array) {
        if (!isset($array[0])) {
            return [$array];
        }
        return $array;
    }

    protected function _errorHandler($reason, $debug = null)
    {
        $data = [];
        $data["success"] = false;
        $data["reason"] = $reason;
        $data["debug"] = $debug;
        return $data;
    }

    protected function _post($headers, $body = null) {
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt_array($this->curl, array(
                CURLOPT_URL => $this->uri,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTPHEADER => $headers
            )
        );
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        if(!empty($this->proxy)) {
            curl_setopt($this->curl, CURLOPT_PROXY, $this->proxy);
        }else{
            curl_setopt($this->curl, CURLOPT_PROXY, ""); // explicitly disables proxy
        }
        if (!empty($this->debug)) {
            curl_setopt($this->curl, CURLOPT_VERBOSE, true);
        }
        $response = curl_exec($this->curl);
        $err = curl_error($this->curl);
        $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        return [
            "code" => $code,
            "body" => $response,
            "error" => $err
        ];
    }

    protected function _request($body)
    {
        $data = [];
        try {
            $headers = [
                "Content-Type: text/xml",
                "Connection: Close",
            ];
            $response = $this->_post($headers, $body);
            if (!empty($response["error"])) {
                return $this->_errorHandler("Error: ".$response["error"]);
            }
            $data["success"] = true;
            $data["data"] = self::xmlToJSON($response["body"]);
        } catch (\Exception $e) {
            $data["success"] = false;
            $data["reason"] = $e->getMessage();
        }
        return $data;
    }


    /** Methods */

    /**
     * @param $afm
     * @return bool
     */
    public function validate($afm)
    {
        if (!preg_match("/^\d{9}$/", $afm) || $afm === "000000000") {
            return false;
        }
        $m = 1;
        $sum = 0;
        for ($i = 7; $i >= 0; $i--) {
            $m *= 2;
            $sum += $afm[$i] * $m;
        }
        return ($sum % 11) % 10 === (int)($afm[8]);
    }

    /**
     * Returns the API Version
     * @return array
     */
    public function version()
    {
        $body = <<<EOL
<?xml version="1.0" encoding="UTF-8"?>
<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
              xmlns:ns="http://gr/gsis/rgwspublic/RgWsPublic.wsdl"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <env:Header/>
    <env:Body>
        <ns:rgWsPublicVersionInfo/>
    </env:Body>
</env:Envelope>
EOL;
        $response = $this->_request($body);
        if (empty($response["success"])) {
            return $response;
        }
        $data = [];
        if (!isset($response["data"], $response["data"]["env:Body"], $response["data"]["env:Body"]["m:rgWsPublicVersionInfoResponse"], $response["data"]["env:Body"]["m:rgWsPublicVersionInfoResponse"]["result"])) {
            return $this->_errorHandler("Failed to get version information");
        }
        $data["success"] = true;
        $data["message"] = $response["data"]["env:Body"]["m:rgWsPublicVersionInfoResponse"]["result"];
        return $data;
    }

    /**
     * @param $afm
     * @return array
     */
    public function info($afm)
    {
        if (!$this->validate($afm)) {
            return $this->_errorHandler("Μη έγκυρο Α.Φ.Μ.");
        }
        $body = <<<EOL
<?xml version="1.0" encoding="UTF-8"?>
		<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"
                      xmlns:ns="http://gr/gsis/rgwspublic/RgWsPublic.wsdl"
                      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                      xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                      xmlns:ns1="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
		    <env:Header>
                <ns1:Security>
                    <ns1:UsernameToken>
                        <ns1:Username>{$this->username}</ns1:Username>
                        <ns1:Password>{$this->password}</ns1:Password>
                    </ns1:UsernameToken>
                </ns1:Security>
            </env:Header>
		    <env:Body>
                <ns:rgWsPublicAfmMethod>
                    <RgWsPublicInputRt_in xsi:type="ns:RgWsPublicInputRtUser">
                        <ns:afmCalledBy>{$this->afm}</ns:afmCalledBy>
                        <ns:afmCalledFor>{$afm}</ns:afmCalledFor>
                    </RgWsPublicInputRt_in>
                    <RgWsPublicBasicRt_out xsi:type="ns:RgWsPublicBasicRtUser">
                        <ns:afm xsi:nil="true"/>
                        <ns:stopDate xsi:nil="true"/>
                        <ns:postalAddressNo xsi:nil="true"/>
                        <ns:doyDescr xsi:nil="true"/>
                        <ns:doy xsi:nil="true"/>
                        <ns:onomasia xsi:nil="true"/>
                        <ns:legalStatusDescr xsi:nil="true"/>
                        <ns:registDate xsi:nil="true"/>
                        <ns:deactivationFlag xsi:nil="true"/>
                        <ns:deactivationFlagDescr xsi:nil="true"/>
                        <ns:postalAddress xsi:nil="true"/>
                        <ns:firmFlagDescr xsi:nil="true"/>
                        <ns:commerTitle xsi:nil="true"/>
                        <ns:postalAreaDescription xsi:nil="true"/>
                        <ns:INiFlagDescr xsi:nil="true"/>
                        <ns:postalZipCode xsi:nil="true"/>
                    </RgWsPublicBasicRt_out>
                    <arrayOfRgWsPublicFirmActRt_out xsi:type="ns:RgWsPublicFirmActRtUserArray"/>
                    <pCallSeqId_out xsi:type="xsd:decimal">0</pCallSeqId_out>
                    <pErrorRec_out xsi:type="ns:GenWsErrorRtUser">
                        <ns:errorDescr xsi:nil="true"/>
                        <ns:errorCode xsi:nil="true"/>
                    </pErrorRec_out>
                </ns:rgWsPublicAfmMethod>
            </env:Body>
		</env:Envelope>
EOL;
        $response = $this->_request($body);
        if (empty($response["success"])) {
            return $response;
        }
        $afmResponse = $response["data"]["env:Body"]["m:rgWsPublicAfmMethodResponse"];
        $data = [];
        if (isset($afmResponse["pErrorRec_out"], $afmResponse["pErrorRec_out"]["m:errorCode"]) && !isset($afmResponse["pErrorRec_out"]["m:errorCode"]["@attributes"])) {
            $data["success"] = false;
            $data["reason"] = $afmResponse["pErrorRec_out"]["m:errorDescr"];
            if ($afmResponse["pErrorRec_out"]["m:errorCode"] === "RG_WS_PUBLIC_EPIT_NF") {
                $data["isNotBusiness"] = true;
            }
            return $data;
        }
        $data["success"] = true;
        $data["business"] = [
            "kad" => "",
            "drastiriotita" => "",
        ];
        $afmData = isset($afmResponse["RgWsPublicBasicRt_out"]) ? $afmResponse["RgWsPublicBasicRt_out"] : array();
        foreach ($afmData as $key => $value) {
            $key = substr($key, 2);
            $data["business"][$key] = is_array($value) ? null : $value;
        }
        $data["business"]["drastiriotites"] = [];
        $firmActs = array();
        if (isset($afmResponse["arrayOfRgWsPublicFirmActRt_out"], $afmResponse["arrayOfRgWsPublicFirmActRt_out"]["m:RgWsPublicFirmActRtUser"])) {
            $firmActs = $this->_toArray($afmResponse["arrayOfRgWsPublicFirmActRt_out"]["m:RgWsPublicFirmActRtUser"]);
        }
        foreach ($firmActs as $firmAct) {
            if ($firmAct["m:firmActKind"] === "1") {
                $data["business"]["kad"] = $firmAct["m:firmActCode"];
                $data["business"]["drastiriotita"] = $firmAct["m:firmActDescr"];
            }
            $data["business"]["drastiriotites"][] = array(
                "type" => $firmAct["m:firmActKind"],
                "typeName" => $firmAct["m:firmActKindDescr"],
                "kad" => $firmAct["m:firmActCode"],
                "drastiriotita" => $firmAct["m:firmActDescr"],
            );
        }
        return $data;
    }
}