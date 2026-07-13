<?php 
session_start();
//echo '<pre>'; print_r($_SESSION); echo '</pre>';
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


    <script>
        function bulkinss() {
            var bval = document.getElementById('bulkins').value;
           
            //someString = 'the cat looks like a cat';
            //anotherString = someString.replace(/cat/g, 'dog');
            bval = bval.replace(/A111/g, '1111');
            bval = bval.replace(/A222/g, '2222');
            bval = bval.replace(/A333/g, '3333');
            bval = bval.replace(/A444/g, '4444');
            bval = bval.replace(/A555/g, '5555');
            bval = bval.replace(/A666/g, '6666');
            bval = bval.replace(/A777/g, '7777');
            bval = bval.replace(/A888/g, '8888');
            bval = bval.replace(/A999/g, '9999');
            bval = bval.replace(/A000/g, '0000');
            const numberList = (bval
                    .split(
                        /\-\d+/
                        ) // - split at "'$' followed by one or more numbers".
                    .join('') // - join array of split results into string again.
                    .match(/\d+/g) || []
                ) // - match any number-sequence or fall back to empty array.
                .map(str => +str); // - typecast string into number.
            //.map(str => parseInt(str, 10)); // parse string into integer.
            var elem = document.getElementById
            console.log('numberList : ', numberList);
            //document.getElementById("addtrn").deleteRow(0);
            var table = document.getElementById("addtrn");
            var rowCount = table.rows.length;

            table.deleteRow(rowCount -1);
            var ttotal = 0;
            for (var i = 0; i < numberList.length; i = i + 2) {
                var newRow = document.getElementById('addtrn').insertRow();
                newRow.innerHTML =
                    '<tr><th><input type="text" class="form-control" name="trn_number[]" value="' +
                    numberList[i] +
                    '" placeholder="Number" onkeyup="checkshift()"></th> <th><input type="text" class="form-control" value="' +
                    numberList[i + 1] +
                    '" name="trn_amount[]" placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent().remove();"> X </span></td></tr>';
                console.log(numberList[i]);
                //.insertAdjacentHTML('afterend','<tr><th><input type="text" class="form-control" name="trn_number[]" placeholder="Number" onkeyup="checkshift()"></th> <th><input type="text" class="form-control" name="trn_amount[]" placeholder="Amount" ></th></tr>');
                ttotal = ttotal+ numberList[i + 1];
            }
            var oldval =  document.getElementById('ttamntt').value;
            document.getElementById('ttamntt').value = parseInt(oldval)+parseInt(ttotal);
            var newRow = document.getElementById('addtrn').insertRow();
            newRow.innerHTML =
                '<tr> <th><input type="text" class="form-control" name="trn_number[]" placeholder="Number" onkeyup="checkshift()" autocomplete="off"></th><th><input type="text" class="form-control" name="trn_amount[]" placeholder="Amount" autocomplete="off"></th></tr>';
            //  document.getElementById('addtrnhead').insertAdjacentHTML('afterend',
            //    document.getElementById('addtrn').innerHTML);
            //  var matches = bval.match(/\((.*?)\)/);
            /*	 var regExp = /^[^-]*[^ -]a/g;
                                     let regex = /^\d0-\(10\),$/i;
        console.log( regex.test(bval));
    var matches = bval.split("-");
    //console.log(matches);
    for (var i = 0; i < matches.length; i++) {
        var str = matches[i];
       console.log(str.substring(1, str.length - 1));
    }

                                     var regExp = /\(([^)]+)\)/g;
    var matches = bval.match(regExp);
    for (var i = 0; i < matches.length; i++) {
        var str = matches[i];
       // console.log(str.substring(1, str.length - 1));
    } */
            document.getElementById('bulkins').value = '';
        }
    </script>


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
                                <h4 class="text-white"><a href="Copy-paste.html" style="color:white">  <i class="fas fa-home" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Home</a></h4>  
								<h4 class="text-white">Entry</h4>
                            </div>
							
                           
                           
                            <div class="card-body">
				<form id="demo-form2" action ="https://admin.555xch.pro/tbl_transactions/add_transaction_final_app"  method = "POST" data-parsley-validate
                                      class="form-horizontal form-label-left" onsubmit="checkFields(event)">
					 <div class="row">
                <div class="form-group col-12">
                    <input type="hidden" name="userid" value="<?php  echo $_SESSION['login'];?>" />
                    <h2 style="margin-right:10px;"><b>Date</b></h2>
                    <input id="birthday" name="dateoftrn" class=" form-control" type="date"
                           autocomplete="off" required>
                </div>
					


                <!-- Select  Date -->
                <!--Select Shift Code -->
 
                <div class="form-group col-12">

                    <h2 style="margin-right:10px;"><b>Shift</b></h2>
                    <!-- Split button -->
                    <div class="btn-group" style="height: 36px; margin-right: 10px;;width:100%">

                        <select name="shift" id="shift" class="form-control"
                                required>
                            <option value="0">Choose Shift</option>
                            <?php
							date_default_timezone_set('Asia/Kolkata');
								$ttime = time();
                            //die;
                            //$url = "https://555xch.pro/tbl_shift/getallshifts";
                            //$parts = parse_url($url);
                            //$output = [];
                            //parse_str($parts['query'], $output);
                            //echo $output['Array']; // 12
                           $servername = "localhost";
$username = "555prodb";
$password = "/a<!F*t/J1_-";
$dbname = "555prodb";
$userid = $_SESSION['login'];
$id = $_GET['id'];
									// Create connection
									$mysqli = new mysqli($servername, $username, $password, $dbname);
				$sql = "SELECT `tbl_shift`.*, `tbl_user_login`.`user_name` FROM `tbl_shift` JOIN `tbl_user_login` ON `tbl_user_login`.`id` = `tbl_shift`.`updated_by`";;					
									
									$rs = $mysqli -> query($sql);
									while ($row = mysqli_fetch_array($rs)) {
										if($_SESSION['user_type']=='admin'){
											//echo $val['open_date'].' '.date("H:i", strtotime($val['super_admin'])); //die;
											$time = strtotime(date('d-m-Y',strtotime($row['open_date'])).' '.date("H:i", strtotime($row['super_admin'])));
										}
										if($_SESSION['user_type']=='ledger'){
											$time = strtotime(date('d-m-Y',strtotime($row['open_date'])).' '.date("H:i", strtotime($row['data_entry_operator'])));
										}
										if($ttime<$time){
										
										echo '<option value='.$row['id'].'>'.$row['shift_name'].'</option>';
											
										}
									}

                           /*  $sql = "select * from tbl_shift ORDER BY shift_name ASC";
                            $rs = mysqli_query($conn, $sql);
                            $datacount = 0;
                            while ($row = mysqli_fetch_array($rs)) {
                            echo '
                            <option value='.$row[' id'].'>'.$row['shift_name'].'</option>';
                            } */
                            ?>

                        </select>
                        <div class="alert" style="display:none">Please Select Shift First</div>
                    </div>

                </div>
					
                <!--selctshift code end -->
                <!-- Select PArty -->
					
                <div class="form-group col-12">
                    <?php //echo '<pre>'; print_r($party); echo '</pre>'; ?>
                    <h2 style="margin-right:10px;"><b>Party</b></h2>
                    <div class="btn-group" style="height: 36px; margin-right: 10px;width:100%">
                        <select name="party" id="party" class="form-control" required>
                            <option value="0">Choose Party</option>
                            <?php
                             $servername = "localhost";
$username = "555prodb";
$password = "/a<!F*t/J1_-";
$dbname = "555prodb";
                            // Create connection
                            $conn = new mysqli($servername, $username, $password, $dbname);
                            if (!$conn)
                            {
                            die("Connection failed!" . mysqli_connect_error());
                            }

                            $sql = "select * from tbl_ledger where Status = 1  ORDER BY ledger_name ASC";
                            //echo $sql;
                            $rs = mysqli_query($conn, $sql);
                            //die;
                            $datacount = 0;
                            while ($row = mysqli_fetch_array($rs)) {
                            echo '<option value='.$row[' id'].'>'.$row['ledger_name'].'</option>';
                            }
                            ?>
                        </select>


                    </div>
                </div>
					
                <!--Select Party end -->
               

                    <div class="form-group col-12">
                        <div class="card card-auth">
                            
                            <div class="card-body">
							 <div class="row">
                                        <div class="form-group col-12">
											
											
										
                                            <input type="button" id="bulkinsbtn" name="insert" value="insert" onclick="return bulkinss()" class="btn btn-auth-color btn-lg btn-flex">
											<input type="submit" name = "submitpost" class="btn btn-auth-color btn-lg btn-flex" onclick=""/>
										
                                        </div>
                                    </div>
                               <div class="row">
                                        <div class="form-group col-12">
                                    <table id="addtrn">
                                        <thead>
                                            <tr>
                                                <th><input type="text" class="form-control" name="trn_number[]" maxlength="2" placeholder="Number" onfocus="checkshift(this)"></th>
                                                <th><input type="text" class="form-control" name="trn_amount[]" maxlength="6" placeholder="Amount" onfocus="checkshift(this)"></th>

                                            </tr>
                                        </thead>
                                    </table>
									</div>
									</div>
                                    <div class="row">
                                        <div class="form-group col-12">

                                            <textarea name="bulkins" id="bulkins" onkeyup="enablewassap()" class="form-control required " rows="5"></textarea>

                                        </div>
                                    </div>
                                   
                               
                            </div>
                        </div>
                    </div>
				</div>
				</form>
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