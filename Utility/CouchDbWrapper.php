<?php

class CouchDbWrapper
{
	public function createDocument($object)
	{
		$ch = curl_init();

		$documentID = $object->name;
		curl_setopt($ch, CURLOPT_URL, 'http://192.168.56.101:5984/clover/'.$documentID);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-type: application/json',
		    'Accept: */*'
		));
		$response = json_decode(curl_exec($ch));
		$object->_rev = $response->{'_rev'};
		

		$payload = json_encode($object);
		curl_setopt($ch, CURLOPT_URL, 'http://192.168.56.101:5984/clover/'.$documentID);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); /* or PUT */
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-type: application/json',
		    'Accept: */*'
		));
		$response = curl_exec($ch);
		print $response;
		curl_close($ch);
		return $response;
	}
}