<?php
session_start();
if(isset($_POST['submit']))
    {
      
$user_name =(isset($_POST['username'])) ? $_POST['username']: null;
$UUpassword =(isset($_POST['password'])) ?  $_POST['password']: null;

 }
$Upassword = $UUpassword;
$servername = "localhost";
$username = "555prouser";
$password = "e2OFVjrRK77ljyfs4z@R";
$dbname = "555prodb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
 if (!$conn)
    {
        die("Connection failed!" . mysqli_connect_error());
    }
// using sql to create a data entry query
   //$sql = "select id from tbl_user_login where user_name='$user_name' and password='$Upassword'";
$sql = "select id,ledger_name,updated_by from tbl_ledger where username='$user_name' and password='$Upassword' and is_master='0' and status='1'";
//echo $sql; die;
 $rs = mysqli_query($conn, $sql);
$datacount = 0;
	while ($row = mysqli_fetch_array($rs)) {
		
//echo '<pre>'; print_r($row); echo '</pre>'; die;
		$sql = "select id from tbl_ledger where parent_id='".$row["id"]."'";
//echo $sql; die;
$_SESSION['user_id'] = $row["id"];
		$_SESSION['updated_by'] = $row["updated_by"];
 $rs = mysqli_query($conn, $sql);
  $row1 = mysqli_num_rows($rs);
          //var_dump($row1); die; 
           if ((int)$row1!=0)
              { //echo 'here'; die;
		//$_SESSION["login"]=$row["id"];
		//$_SESSION["user_type"]='ledger';
		
			$_SESSION['last_timestamp'] = time(); // Set the last activity timestamp
		$redirect = 'Parent.php?parentid='.$row["id"].'&name='.$row["ledger_name"];
header("location:$redirect"); die;								
			  }
			  else{ 
			$_SESSION['last_timestamp'] = time(); // Set the last activity timestamp
$redirect = 'Entry-page.php?login='.$row["id"]."&user_type=ledger";	
header("location:$redirect");				
			  }
	//echo '<pre>'; print_r($_SESSION); echo '</pre>';
//die;//echo "Data get hua"; die;
$datacount = 1;
}
if (empty($datacount)) {
	?>
	<script>alert('Invalid User name or Password!!');
	window.location.href = "https://new.bull99exch.com/appdemo/Login.html";
	</script>
	<?php
	//header("location:Login.html");
}
/*if (empty($datacount)) {
	//echo "Variable 'a' is empty.<br>";
	header("location:Login.html");
}else
{
	header("location:$redirect");
	
}*/
	
	
 mysqli_close($conn);
  
die;

?>
