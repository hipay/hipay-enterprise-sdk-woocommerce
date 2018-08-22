<?php
class HipayEnterpriseWooOperation
{

    /**
     * Get details from transaction using order id  
     * @var string
     */
    public static function get_details_by_order($username,$password,$passphrase,$env,$order_id)
    {

		$message = '{
		  "orderid": "' . $order_id . '"
		}';

		try {
		    
		    $curl = curl_init();
		    curl_setopt($curl,CURLOPT_TIMEOUT, 51);
		    curl_setopt($curl,CURLOPT_POST,true);
		    curl_setopt($curl,CURLOPT_USERAGENT,"HiPAY");
		    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 33); 
		    curl_setopt($curl,CURLOPT_URL, $env . "transaction");
		    curl_setopt($curl,CURLOPT_POSTFIELDS, $message);
		    curl_setopt($curl, CURLOPT_HEADER, 0);
		    curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Content-Length: ' . strlen($message))    );  
		    curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);  

		    $result = curl_exec($curl);
		    $info = curl_getinfo($curl);
		    if (curl_errno($curl) > 0) {
   				throw new Exception(__("Hipay connection." ,'hipayenterprise') );
   			}
		    curl_close($curl);
			$result = simplexml_load_string($result);
		   	return $result;

		} catch (Exception $e) {
		    
			throw new Exception(__("Error getting transaction information from order:",'hipayenterprise') . " " . $e->getMessage() );
		}

    }


    /**
     * Get details from transaction using transaction id
     * @var string
     */

    public static function get_details_by_transaction($username,$password,$passphrase,$env,$transaction_id)   
    {
		$message = '{
		  "transaction_reference": "' . $transaction_id . '"
		}';

		try {
		    
		    $curl = curl_init();
		    curl_setopt($curl,CURLOPT_TIMEOUT, 51);
		    curl_setopt($curl,CURLOPT_POST,true);
		    curl_setopt($curl,CURLOPT_USERAGENT,"HiPAY");
		    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 33); 
		    curl_setopt($curl,CURLOPT_URL, $env . "transaction/" . $transaction_id);
		    curl_setopt($curl,CURLOPT_POSTFIELDS, $message);
		    curl_setopt($curl, CURLOPT_HEADER, 0);
		    curl_setopt($curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Content-Length: ' . strlen($message))    );  
		    curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);  

		    $result = curl_exec($curl);
		    $info = curl_getinfo($curl);
		    if (curl_errno($curl) > 0) {
   				throw new Exception(__("Hipay connection." ,'hipayenterprise') );
   			}
		    curl_close($curl);
			$result = simplexml_load_string($result);
		   	return $result;

		} catch (Exception $e) {
		    
			throw new Exception(__("Error getting transaction information:",'hipayenterprise') . " " . $e->getMessage() );
		}

    }    

}