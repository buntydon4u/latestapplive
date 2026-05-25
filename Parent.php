<?php 
session_start();
//echo '<pre>'; print_r($_SESSION); echo '</pre>'; die;
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
	if(isset($_POST)&& !empty($_POST)){
		//$_SESSION["login"]=$_POST["login"];
		//$_SESSION["user_type"]='ledger';
		header("location:Entry-page.php?login=".$_POST["ledger_name"]."&user_type=ledger&parentid=".$_GET['parentid']."&name=".$_GET['name']);
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>XCH555</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/css/app.min.css">
  <link rel="stylesheet" href="assets/bundles/jquery-selectric/selectric.css">
  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <!-- Custom style CSS -->
  
  <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />


    
</head>

<body class="background-image-body">
  <div class="loader"></div>
  <div id="app">
      

    <section class="section">
        <div class="container mt-5">
            <!-- Select  Date -->
            <div class="row">
			 <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
                        <div class="card card-auth">
                            <div class="card-header card-header-auth-1">
                                <!--<h4 class="text-white"><a href="Copy-paste.html" style="color:white">  <i class="fas fa-home" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Home</a></h4>  
								<h4 class="text-white">Entry</h4>-->
                            </div>
							
                           
                           
                            <div class="card-body">
				<div class="row">
                
					


                <!-- Select  Date -->
                <!--Select Shift Code -->
 
                
					
                <!--selctshift code end -->
                <!-- Select PArty -->
					
                
					
                <!--Select Party end -->
               

                    <div class="form-group col-12">
                        <div class="card card-auth">
                            
                            <div class="card-body">
							 
                               <div class="row">
                                        <div class="form-group col-12">
										
                                    <?php //echo $_SESSION["parentid"]; die; //foreach() 
									$sql = "select id,ledger_name from tbl_ledger where parent_id='".$_GET["parentid"]."'";
//echo $sql; die;
 $rs = mysqli_query($conn, $sql);

									?>
									<form name="dash" action ="" method="post">
									<fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-sm-2 pt-0">Available Logins</legend>
      <div class="col-sm-10">
	  <div class="form-check">
 
 <input type="radio" class="form-check-input" id="<?=$_GET['parentid']?>" value="<?=$_GET['parentid']?>" name="ledger_name" checked>
          <label class="form-check-label" for="<?=$_GET['parentid']?>">
            <?=$_GET['name']?>
          </label>
        </div>	
												<?php	$i=0;	 while ($row = mysqli_fetch_assoc($rs)) {
												//echo 'ttp'; echo '<pre>'; print_r($row); echo '</pre>';
												?>
 <div class="form-check">
 
 <input type="radio" class="form-check-input" id="<?=$row['id']?>" value="<?=$row['id']?>" name="ledger_name">
          <label class="form-check-label" for="<?=$row['id']?>">
            <?=$row['ledger_name']?>
          </label>
        </div>												
												
												<?php $i++;
												} 
												//unset($_SESSION);
												?>

                                           </div>
    </div>
  </fieldset> 
									<button type="submit" class="btn btn-primary">Sign in</button>
									</form>
									</div>
									</div>
                                   
                                   
                               
                            </div>
                        </div>
                    </div>
				</div>
                </div>
				</div>
				</div>
            </div>
</section>
        </div>
        <!-- General JS Scripts -->
                  <script src="assets/js/app.min.js"></script>
                  <!-- JS Libraies -->
                  <script src="assets/bundles/jquery-pwstrength/jquery.pwstrength.min.js"></script>
                  <script src="assets/bundles/jquery-selectric/jquery.selectric.min.js"></script>
                  <!-- Page Specific JS File -->
                  <script src="assets/js/page/auth-register.js"></script>
                  <!-- Template JS File -->
                  <script src="assets/js/scripts.js"></script>

</body>



</html>