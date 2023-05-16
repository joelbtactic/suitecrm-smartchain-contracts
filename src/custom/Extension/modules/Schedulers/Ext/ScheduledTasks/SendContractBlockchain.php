<?php
$job_strings[] = 'sendContractBlockchain';



function sendContractBlockchain()
{
    // The initial URL needs to be changed to the one you communicate to Blockchain network.
    // Example: https://your.example.com
    $blockchain_url = ''; 

    // The url for HTTP POST method.
    // Example: /register
    $register_url = $blockchain_url . '';

    $ch = curl_init();

    $beans = BeanFactory::getBean('AOS_Contracts');
    $contracts = $beans->get_full_list();

    // Example: 6d20c5e0-73a9-4dea-9494-dc319fcea742
    $api_key = '';

    foreach ($contracts as $contract){
        $currentValue = $contract->states_c;
        
        if ($currentValue == 'tobesigned'){

            $data_contract = array(
                "contract_name" => $contract->name,
                "contract_status" => $contract->status,
                "contract_type" => $contract->contract_type
            );

            $hash_data = $data_contract['contract_name'] . "_" . $data_contract['contract_status'] . "_" . $data_contract['contract_type'];
            $hash_value = hash('sha256', $hash_data);

            $postStr = json_encode(array(
                'contract_id' => $contract->id,
                "new_status" => "Active CRM",
                "description" => $hash_value
            ));
        
            $header = array(
                'Content-type: application/json',
                'APIKeyHeader: ' . $api_key,
                'Content-Length: ' . strlen($postStr)
            );
        
            curl_setopt($ch, CURLOPT_URL, $register_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            $output = curl_exec($ch);
            $auth_out = json_decode($output,true);
            $hash_transaction = $auth_out['transaction_hash'];
            $contract->description = $hash_transaction;
            $contract->states_c = "signed";
            curl_close($ch);
        }
        $contract->save();
    }
    return true;
}
