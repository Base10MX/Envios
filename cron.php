<?php
	$waylbills = array();
	$response = array();
	$guias = array();
	$url = 'https://trackingqa.estafeta.com/Service.asmx?wsdl';
	$file_name = "Set300Guias.txt";
	$trackingNumberCount=0;

	$fp = fopen($file_name, "r");
	while (!feof($fp)){
	    $line = fgets($fp);
	    $waylbills[$trackingNumberCount] = $line;
	    $trackingNumberCount++;
	}
	fclose($fp);
	
	$client = new SoapClient($url);
	// Arreglo de guías a consultar
	
	//$waylbills[0] = '8055150593100720001498';
	//$waylbills[1] = '8055150593100720001498';
	//$waylbills[2] = '7055186697550720032294';
	//$waylbills[1] = '0011011300120581214574';
	//$waylbills[2] = '0009999999104930235766';

	// Se llena Objeto WaybillRange
	$WaybillRange = new StdClass();
	$WaybillRange -> initialWaybill = '';
	$WaybillRange -> finalWaybill = '';
	// Se llena objeto WaybillList, se trata guías de 22 dígitos
	$WaybillList = new StdClass();
	$WaybillList -> waybillType = 'G';
	$WaybillList -> waybills = $waylbills;
	// Se llena objeto SearchType, se indica que se trata de una lista de guías
	$SearchType = new StdClass();
	$SearchType -> waybillRange = $WaybillRange;
	$SearchType -> waybillList = $WaybillList;
	$SearchType -> type = 'L';
	// Se llena objeto HistoryConfiguration, se indica que se requiere toda la historia de las guías
	$HistoryConfiguration = new StdClass;
	$HistoryConfiguration -> includeHistory = 1;
	$HistoryConfiguration -> historyType = 'ALL';
	// Se llena objeto Filter, se indica que no se requiere el filtro por estado actual de las guías
	$Filter = new StdClass;
	$Filter -> filterInformation = 0;
	$Filter -> filterType = 'DELIVERED';
	// Se llena objeto SearchConfiguration, se indican parámetros adicionales a la búsqueda
	$SearchConfiguration = new StdClass();
	$SearchConfiguration -> includeDimensions = 1;
	$SearchConfiguration -> includeWaybillReplaceData = 0;
	$SearchConfiguration -> includeReturnDocumentData = 0;
	$SearchConfiguration -> includeMultipleServiceData = 0;
	$SearchConfiguration -> includeInternationalData = 0;
	$SearchConfiguration -> includeSignature = 0;
	$SearchConfiguration -> includeCustomerInfo = 1;
	$SearchConfiguration -> historyConfiguration = $HistoryConfiguration;
	$SearchConfiguration -> filterType= $Filter;
	// Se instancía al método del web service para consulta de guías

	try{

		$result = $client->ExecuteQuery(array(
			'suscriberId'=>25,
			'login'=>'Usuario1',
			'password'=> '1GCvGIu$',
			'searchType' => $SearchType,
			'searchConfiguration' => $SearchConfiguration
		)
		);

		if($result->ExecuteQueryResult->errorCode==0){//no hubo error

			if(!is_array($result->ExecuteQueryResult->trackingData->TrackingData))
				array_push($guias, $result->ExecuteQueryResult->trackingData->TrackingData);
			else
				$guias = $result->ExecuteQueryResult->trackingData->TrackingData;
			
			foreach ($guias as $value) {
				
				if(isset($value->waybill) ){

					$information = array();
					$information['waybill'] = $value->waybill;
					$information['shortWaybillId'] = isset($value->shortWaybillId)?$value->shortWaybillId:"";
					$information['serviceId'] = isset($value->serviceId)?$value->serviceId:"";
					$information['serviceDescriptionSPA'] = isset($value->serviceDescriptionSPA)?$value->serviceDescriptionSPA:"";
					$information['serviceDescriptionENG'] = isset($value->serviceDescriptionENG)?$value->serviceDescriptionENG:"";
					$information['customerNumber'] = isset($value->customerNumber)?$value->customerNumber:"";
					$information['packageType'] = isset($value->packageType)?$value->packageType:"";
					$information['additionalInformation'] = isset($value->additionalInformation)?$value->additionalInformation:"";
					$information['statusSPA'] = isset($value->statusSPA)?$value->statusSPA:"";
					$information['statusENG'] = isset($value->statusENG)?$value->statusENG:"";
					$information['destinationAcronym'] = isset($value->deliveryData->destinationAcronym)?$value->deliveryData->destinationAcronym:"";
					$information['destinationName'] = isset($value->deliveryData->destinationName)?$value->deliveryData->destinationName:"";
					$information['deliveryDateTime'] = isset($value->deliveryData->deliveryDateTime)?$value->deliveryData->deliveryDateTime:"";
					$information['zipCode'] = isset($value->deliveryData->zipCode)?$value->deliveryData->zipCode:"";
					$information['receiverName'] = isset($value->deliveryData->receiverName)?$value->deliveryData->receiverName:"";

					$response[$value->waybill] = $information;

				}
			}

			echo "<pre>";
			print_r($response);
			echo "</pre>";
		
		}else{
			echo 'Ocurrio un error, codigo de error: ' . $result->ExecuteQueryResult->errorCode.' Mensaje:'.$result->ExecuteQueryResult->errorCodeDescriptionSPA. "\n";
		}

	} catch (Exception $e) {
	    echo 'Excepcion capturada: ' . $e->getMessage(). "\n";
	}