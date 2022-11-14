<?php

ini_set('max_execution_time', 0);
$sk = '';

for ($i=0; $i < 100; $i++) { 
    $ch1 = curl_init();
    
    curl_setopt($ch1, CURLOPT_URL, 'https://api.stripe.com/v1/customers');
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch1, CURLOPT_USERPWD, $sk. ':' . '');
    curl_setopt($ch1, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    
    $customers = curl_exec($ch1);
    $customers = json_decode($customers, true);
    
    curl_close($ch1);
    
    foreach ($customers['data'] as $customer) {
        $ch2 = curl_init();
    
        curl_setopt($ch2, CURLOPT_URL, 'https://api.stripe.com/v1/customers/' . $customer['id']);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_USERPWD, $sk. ':' . '');
        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    
        $result = curl_exec($ch2);
        $result = json_decode($result, true);
    
        curl_close($ch2);
    
        if ($result['deleted']) {
            echo 'The customer ' . $customer['email'] . ' was deleted with success!</br>';
        } else {
            echo 'The customer ' . $customer['email'] . ' was deleted without success!</br>';
        }
    }
}

?>