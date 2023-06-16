<?php include('Crypto.php')?>
<?php

	$Method=$_POST['ccavenue'];
	$working_key=$_POST['working_key'];
	$Plain=$_POST['plain'];
	
	
	if($Method=="enc"){
	
	$encrypted_data=encrypt($Plain,$working_key);
	echo $encrypted_data;
	
	}else if($Method=="decr"){
	
	$encrypted_data=decrypt($Plain,$working_key);
	echo $encrypted_data;
	
	}
		
?>
