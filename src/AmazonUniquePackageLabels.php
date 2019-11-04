<?php namespace Sonnenglas\AmazonMws;

use Sonnenglas\AmazonMws\AmazonInboundCore;

/**
 * Copyright 2013 CPI Group, LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 *
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Submits a shipment to Amazon or updates it.
 *
 * This Amazon Inbound Core object submits a request to create an inbound
 * shipment with Amazon. It can also update existing shipments. In order to
 * create or update a shipment, information from a Shipment Plan is required.
 * Use the AmazonShipmentPlanner object to retrieve this information.
 */
class AmazonUniquePackageLabels extends AmazonInboundCore
{
    private $shipmentId;
    private $PdfDocument;


    /**
     * AmazonShipment ubmits a shipment to Amazon or updates it.
     *
     * The parameters are passed to the parent constructor, which are
     * in turn passed to the AmazonCore constructor. See it for more information
     * on these parameters and common methods.
     * @param string $s <p>Name for the store you want to use.</p>
     * @param boolean $mock [optional] <p>This is a flag for enabling Mock Mode.
     * This defaults to <b>FALSE</b>.</p>
     * @param array|string $m [optional] <p>The files (or file) to use in Mock Mode.</p>
     * @param string $config [optional] <p>An alternate config file to set. Used for testing.</p>
     */
    public function __construct($s, $mock = false, $m = null)
    {
        parent::__construct($s, $mock, $m);

        //$this->options['InboundShipmentHeader.ShipmentStatus'] = 'WORKING';
    }

    public function setCachekey($cacheKey = ''){
        if ($cacheKey){
            $this->cacheKey = $cacheKey;
        } else {
            return false;
        }
    }

    /**
     * Returns whether or not a token is available.
     * @return boolean
     */
    public function hasToken()
    {
        return $this->tokenFlag;
    }

    /**
     * Sets whether or not the object should automatically use tokens if it receives one.
     *
     * If this option is set to <b>TRUE</b>, the object will automatically perform
     * the necessary operations to retrieve the rest of the list using tokens. If
     * this option is off, the object will only ever retrieve the first section of
     * the list.
     * @param boolean $b [optional] <p>Defaults to <b>TRUE</b></p>
     * @return boolean <b>FALSE</b> if improper input
     */
    public function setUseToken($b = true)
    {
        if (is_bool($b)) {
            $this->tokenUseFlag = $b;
        } else {
            return false;
        }
    }
    /**
     * Sets the PageType. (Required)
     * @param string $s <p>"PackageLabel_Letter_2", "PackageLabel_Letter_6"</p>
     * @return boolean <b>FALSE</b> if improper input
     */

    public function setPageType($s)
    {
        if (is_string($s) && $s) {
            if ($s == 'PackageLabel_Letter_2' || $s == 'PackageLabel_Letter_6') {
                $this->options['PageType'] = $s;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Sets the NumberOfPackages. (Required)
     * @param integer $s
     * @return boolean <b>FALSE</b> if improper input
     */


    public function setPackageLabelsToPrint($s)
    {
        if (is_integer($s) && $s  ) {
            for ($x=1; $x<=$s; $x++) {
                $this->options['PackageLabelsToPrint.member.'.$x] = $x;
            }
        } else {
            return false;
        }
    }

    /**
     * Sets the shipment ID. (Required)
     * @param string $s <p>Shipment ID</p>
     * @return boolean <b>FALSE</b> if improper input
     */
    public function setShipmentId($s)
    {
        if (is_string($s) && $s) {
            $this->options['ShipmentId'] = $s;
        } else {
            return false;
        }
    }


    /**
     * Sends a request to Amazon to GetPackageLabels .
     *
     * Submits a <i>GetPackageLabels</i> request to Amazon.
     * @return boolean <b>TRUE</b> if success, <b>FALSE</b> if something goes wrong
     */
    public function getPackageLabels()
    {
        $this->options['Action'] = 'GetUniquePackageLabels';

        $url = $this->urlbase . $this->urlbranch;

        $query = $this->genQuery();

        $path = $this->options['Action'] . 'Result';
        if ($this->mockMode) {
            $xml = $this->fetchMockFile()->$path;
        } else {
            $response = $this->sendRequest($url, array('Post' => $query));

            if (!$this->checkResponse($response,$this->cacheKey)) {
                return false;
            }

            $xml = simplexml_load_string($response['body'])->$path;


        }
        $this->parseXML($xml);

    }

    /**
     * Parses XML response into array.
     *
     * This is what reads the response XML and converts it into an array.
     * @param SimpleXMLObject $xml <p>The XML response from Amazon.</p>
     * @return boolean <b>FALSE</b> if no XML data is found
     */
    protected function parseXML($xml)
    {
        if (!$xml) {
            return false;
        }

        $a=array();
        if(isset($xml->TransportDocument)){

            if (isset($xml->TransportDocument->PdfDocument)) {
                $a['PdfDocument'] = (string)$xml->TransportDocument->PdfDocument;
            }
            if (isset($xml->TransportDocument->Checksum)) {
                $a['Checksum'] = (string)$xml->TransportDocument->Checksum;
            }
        }else{
            return false;
        }
        $this->PdfDocument = $a;
    }

    /**
     * Returns the shipment ID of the newly created/modified order.
     * @return string|boolean single value, or <b>FALSE</b> if Shipment ID not fetched yet
     */
    public function getPdfDocument()
    {
        if (isset($this->PdfDocument)) {
            return $this->PdfDocument;
        } else {
            return false;
        }
    }

}

?>
