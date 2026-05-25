
<?php
session_start();
$servername = "localhost";
$username = "555prouser";
$password = "e2OFVjrRK77ljyfs4z@R";
$dbname = "555prodb";
if(isset($_POST['submit']))
    {
		$mobileno =(isset($_POST['mobileno'])) ? $_POST['mobileno']: null;
		$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit();
}

$sql = "select id from tbl_ledger where mobile='$mobileno'";
$result = $mysqli -> query($sql);

// Numeric array
$row = $result -> fetch_array(MYSQLI_NUM);
//echo '<pre>'; print_r($row); echo '</pre>';
$datacount = 0;
if(!empty($row)){
	$_SESSION["login"]=$row[0];
$_SESSION["user_type"]='ledger';
$datacount = 1;
}

// Free result set
$result -> free_result();
if ($datacount=='0') {
	//echo "Variable 'a' is empty.<br>";
	header("location:Login.html");
}else
{
	header("location:Copy-paste.html");
	
}

$mysqli -> close();
	}


/*
//echo 'dadasdasdas';
if(isset($_POST['submit']))
    {
      
$mobileno =(isset($_POST['mobileno'])) ? $_POST['mobileno']: null;
//$UUpassword =(isset($_POST['password'])) ?  $_POST['password']: null;

 }
//$Upassword = md5($UUpassword);
$servername = "localhost:3306";
$username = "check555";
$password = "Amit@123!@#";
$dbname = "admin_check555";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
 if (!$conn)
    {
        die("Connection failed!" . mysqli_connect_error());
    }
// using sql to create a data entry query
   $sql = "select id from tbl_ledger where mobile='$mobileno'";
//echo $sql;
 $rs = $conn->mysqli_query($sql);
$datacount = 0;

$row = $rs -> fetch_array(MYSQLI_ASSOC);
echo '<pre>'; print_r($row); echo '</pre>';
	while ($row = $conn->mysqli_fetch_array($rs)) {
		
$_SESSION["login"]=$row["id"];
$_SESSION["user_type"]='ledger';		
	//echo '<pre>'; print_r($_SESSION); echo '</pre>';
echo "Data get hua"; die;
$datacount = 1;
}

if (empty($datacount)) {
	//echo "Variable 'a' is empty.<br>";
	header("location:Login.html");
}else
{
	header("location:Copy-paste.html");
	
}
	
	
 mysqli_close($conn);
  
die;
*/
?>
