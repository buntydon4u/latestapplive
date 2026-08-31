<?php 
session_start();
$SITEURL = 'http://'.$_SERVER['HTTP_HOST'].'/Login.html';
//echo '<pre>'; print_r($_SESSION); echo '</pre>';
//echo '<pre>'; print_r($_SESSION); echo '</pre>';
//if(!isset($_SESSION["user_type"])&&(empty($_SESSION["user_type"]))){
	//header('Location: ' . $SITEURL, true, 301);

    //exit();
//}
 if(isset($_GET['parentid'])){
	$home = 'https://app.555xch.pro/Parent.php?parentid='.$_GET["parentid"].'&name='.$_GET["name"];   
	$entry = 'Entry-page.php?login='.$_GET["login"].'&user_type=ledger&parentid='.$_GET["parentid"].'&name='.$_GET["name"];
	$view = '&login='.$_GET["login"].'&user_type=ledger&parentid='.$_GET["parentid"].'&name='.$_GET["name"];
   }
   else{
	  
   $home = '#';
   $entry = 'Entry-page.php?login='.$_GET["login"].'&user_type=ledger';
   $view = '&login='.$_GET["login"].'&user_type=ledger';
   } 
?>
<!DOCTYPE html>
<html lang="en">


<!-- Mirrored from radixtouch.in/templates/snkthemes/grexsan/source/light/auth-register.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Nov 2020 07:23:58 GMT -->
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
    <style>
	#exTab1 .tab-content {
  color : white;
  background-color: #428bca;
  padding : 5px 15px;
}

#exTab2 h3 {
  color : white;
  background-color: #428bca;
  padding : 5px 15px;
}

/* remove border radius for the tab */

#exTab1 .nav-pills > li > a {
  border-radius: 0;
}

/* change border radius for the tab , apply corners on top*/

#exTab3 .nav-pills > li > a {
  border-radius: 4px 4px 0 0 ;
}

#exTab3 .tab-content {
  color : white;
  background-color: #428bca;
  padding : 5px 15px;
}
        .view-btn:hover{
            color:white !important;
        }
        .check-btn a{
            margin-left:0px;
            margin-bottom:20px;
        }
        .card .card-body {
            padding-top: 14px;
            padding-bottom: 2px;
        }
        .text-shift{
            width:100%;
            height:40px;
        }
    </style>
	<style>
        .view-btn:hover{
            color:white !important;
        }
        .check-btn a{
            margin-left:0px;
            margin-bottom:20px;
        }
        .card .card-body {
            padding-top: 14px;
            padding-bottom: 2px;
        }
        .text-shift{
            width:100%;
            height:40px;
        }
        label {
            font-weight: 500;
        }
                   /*-------------------------------------------------------------------- table CSS ---------------------->*/
        .table {
            width: 100%;
            border-collapse: collapse;
        }

            .table td, .table th {
                padding: 12px 15px;
                border: 1px solid #ddd;
                text-align: center;
                font-size: 16px;
            }

            .table th {
                color: black;
            }

            .table tbody tr {
                background-color: #ffffff;
            }

        .head-1 {
            background: white !important;
        }
        .card .card-body, .card .card-footer, .card .card-header {
            background-color: transparent;
            padding: 10px 13px;
        }
        @media(max-width:768px) {
            .table thead {
                display: none;
            }

            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }

                .table tr {
                    margin-bottom: 20px;
                }

                .table td {
                    text-align: left;
                    padding-left: 100px !important;
                    text-align: left;
                }

                    .table td::before {
                        content: attr(data-label);
                        position: absolute;
                        left: 0;
                        width: 50%;
                        padding-left: 20px;
                        font-size: 15px;
                        font-weight: bold;
                        text-align: left;
                    }
        }

        /*//----------------------------------------------End Of CSS*/
    
    </style>
</head>

<body class="background-image-body">
  <div class="loader"></div>
  <?php //echo 'ttp'; 
   //var_dump($_GET['status']);
   if(isset($_GET['status']) && ($_GET['status']=='1')){
	   echo '<script>alert("transaction recorded successfully");</script>';
   }
   if(isset($_GET['status']) && $_GET['status']=='0'){
	   echo '<script>alert("Invalid Entry. Please try again!!");</script>';
   }
   if(isset($_GET['parentid'])){
	$home = 'https://new.bull99exch.com/Parent.php?parentid='.$_GET["parentid"].'&name='.$_GET["name"];   
   }
   else{
   $home = '#';
   }   
   ?>
  <div id="app"> 
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
           <div class="card card-auth">
              <!--<div class="card-header card-header-auth-1">
                <h4 class="text-white">Tanu SD</h4>
              </div>-->
			  <div class="card-header card-header-auth-1">
                               <h4 class="text-white"><a href="<?=$home?>" style="color:white"><i class="fas fa-home" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Home</a>
							   </h4> 
							   <h4 class="text-white"><a href="<?=$entry?>" style="color:white"><i class="fas fa-pen" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Entry</a></h4>
                            <h4 class="text-white"><i class="fas fa-book" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> View Transaction</h4>
							</div>
              <div class="card-body">
                <form method="POST">
                 <!--<div class="row">
                       <div class="form-group col-6">
                            <label for="">Date</label>
                           <input id="todate" class="birthday form-control hasDatepicker" type="datetime-local" name="todate" value="" placeholder="To Date" autocomplete="off">
                        </div>
                        <div class="form-group col-6">
                            <label for="email">Shift</label><br />
                            <select name="shift" id="shift"  class="form-control"
                                    required>
                                    <option value="0">Choose Shift</option>
									<?php
									date_default_timezone_set('Asia/Kolkata');
								$ttime = time()+43200;
									//die;
									//$url = "https://555xch.pro/tbl_shift/getallshifts";
									//$parts = parse_url($url);
									//$output = [];
									//parse_str($parts['query'], $output);
									//echo $output['Array']; // 12
										$servername = "localhost";
                                        $username = "555prouser";
                                        $password = "e2OFVjrRK77ljyfs4z@R";
                                        $dbname = "555prouser";
									// Create connection
									$mysqli = new mysqli($servername, $username, $password, $dbname);
									
									$sql = "SELECT `tbl_shift`.*, `tbl_user_login`.`user_name` FROM `tbl_shift` JOIN `tbl_user_login` ON `tbl_user_login`.`id` = `tbl_shift`.`updated_by`";
									//$sql = "select * from tbl_shift ORDER BY shift_name ASC";
									$rs = $mysqli -> query($sql);
									$datacount = 0;
									while ($row = mysqli_fetch_array($rs)) { //echo '<pre>'; print_r($row); die;
										if($_SESSION['user_type']=='admin'){
											//echo $val['open_date'].' '.date("H:i", strtotime($val['super_admin'])); //die;
											$time = strtotime(date('d-m-Y',strtotime($row['open_date'])).' '.date("H:i", strtotime($row['super_admin'])));
										}
										if($_SESSION['user_type']=='ledger'){
											$time = strtotime(date('d-m-Y',strtotime($row['open_date'])).' '.date("H:i", strtotime($row['data_entry_operator'])));
										}
										
										if($ttime<$time && (date('Y-m-d',$ttime) == date('Y-m-d', $time))){
										//echo '<pre>'; print_r($row); die;
										echo '<option value='.$row['id'].'>'.$row['shift_name'].'</option>';
											
										}
										//echo '<option value='.$row['id'].'>'.$row['shift_name'].'</option>';
									}
									?>
                                    
                                </select>
                        </div>
                     <div class="col-6 check-btn"><a href="#" class="btn btn-auth-color btn-lg btn-flex">Submit</a></div>
                     </div>-->
                <div class="col-12">
                        <div class="card">
                           <div class="card-body">
                                <div class="table-responsive">
                                    <script type="text/javascript">

                                        //var JQ = jQuery.noConflict();
                                        // Basic example
                                        $(document).ready(function () {
                                            $('#dtBasicExample').DataTable();

                                        });
                                    </script>
                                    <asp:HiddenField ID="hfId" runat="server" Value="0" />
									<div id="exTab1" class="container">	

  <!--<input type="radio" id="activesession" name="fav_language" value="CSS" checked>
  <label for="css">Active Session</label>
  <input type="radio" id="oldsession" name="fav_language" value="JavaScript">
  <label for="javascript">Old Session</label>-->
</div>
                                    <table class="table table-striped table-hover" id="save-stage" style="width: 100%;">

                                        <thead>
                                            <tr>
                                               
                                                <th class="head-1">Date</th>
                                                <th class="head-1">Shift Name</th>
                                                <th class="head-1">Amt</th>
                                                <th colspan="2" style="text-align: center;" class="head-1">Action</th>
                                            </tr>
                                       </thead>

                                        <tbody>
										<?php 
										$servername = "localhost";
                                        $username = "555prouser";
                                        $password = "e2OFVjrRK77ljyfs4z@R";
                                        $dbname = "555prouser";
$userid = $_SESSION['login'];
//$date = date('Y-m-d');
$date = date('Y-m-d',strtotime('-7 hours'));
$ttime = time();
//echo '<pre>'; print_r($date); die;
									// Create connection
									$mysqli = new mysqli($servername, $username, $password, $dbname);
				$sql = "SELECT `tbl_master_transaction`.`id`,  `tbl_agent`.`agent_name`, `tbl_trans_numbers`.`created_date` as `createddate`, `tbl_trans_numbers`.`modified_date` as `modifieddate`, `tbl_master_transaction`.`t_date`, `tbl_master_transaction`.`total_number_amount`, `tbl_master_transaction`.`created_date`, `tbl_master_transaction`.`party_id`, `tbl_shift`.id as shiftid,`user_shift_timings`.app_time,`tbl_shift`.`shift_name` as `shift_name`, `user_shift_timings`.`open_date`, `tbl_shift`.`super_admin`, `tbl_shift`.`data_entry_operator`, `tbl_shift`.`id` as `shift_id`, `tbl_ledger`.`ledger_name`, `tbl_trans_numbers`.`number` as `trnno`, `tbl_trans_numbers`.`amount` as `trn_amt` FROM `tbl_master_transaction` JOIN `user_shift_timings` ON `user_shift_timings`.`id` = `tbl_master_transaction`.`shift_id` JOIN `tbl_shift` ON `tbl_shift`.`id` = `user_shift_timings`.`shift_id` JOIN `tbl_trans_numbers` ON `tbl_trans_numbers`.`master_id` = `tbl_master_transaction`.`id` JOIN `tbl_ledger` ON `tbl_ledger`.`id` = `tbl_master_transaction`.`party_id` LEFT JOIN `tbl_agent` ON `tbl_ledger`.`agent_id` = `tbl_agent`.`id`  
				WHERE `tbl_master_transaction`.`t_date` >= NOW() - INTERVAL 30 DAY AND `tbl_master_transaction`.`created_by` = '".$userid."' ORDER BY `tbl_master_transaction`.`id` DESC";					
								//echo $sql; die;	
									$rs = $mysqli -> query($sql);
									//echo '<pre>';print_r($_SESSION['user_type']); print_r(mysqli_fetch_array($rs)); echo '</pre>'; die;
									$datacount = 0;
									while ($row = mysqli_fetch_array($rs)) { $count = 0;
										//echo '<pre>'; print_r($row); echo '</pre>';
										if($_SESSION['user_type']=='admin'){
											//echo $val['open_date'].' '.date("H:i", strtotime($val['super_admin'])); //die;
											$time = strtotime(date('d-m-Y',strtotime($row['open_date'])).' '.date("H:i", strtotime($row['super_admin'])));
										}
										if($_SESSION['user_type']=='ledger'){
											$time = strtotime(date('d-m-Y',strtotime($row['open_date'])).' '.date("H:i", strtotime($row['app_time'])));
										     // $time = strtotime($row['app_time']);
                                        }
                                        //echo $ttime.' '.$time.' '.date('Y-m-d',$ttime).' '.date('Y-m-d', strtotime($row['t_date'])); die;
										$t_date = date('Y-m-d', strtotime($row['t_date']));
                                        if($row['shiftid'] == 11){
                                            $t_date = date('Y-m-d', strtotime($row['t_date'] . ' +1 day'));
                                        }
                                        if($ttime<$time && (date('Y-m-d',$ttime) == $t_date)){
                                            //if($ttime<$time){
											$count++;
										 ?><tr id="aall">
                                                       <td data-label="Date"><?=date('d M, Y', strtotime($row['t_date']))?></td>
                                                        <td data-label="Shift Name"><?=$row['shift_name']?></td>
                                                        <td data-label="Amt"><?=array_sum(explode(',',$row['trn_amt']))?></td>
                                                        <td data-label="Action">
                                                            <a href="View-page.php?id=<?=$row['id'].$view?>" class="badge badge-primary view-btn">View</a>
                                                       
                                                        <!-- </td>
														<td data-lable="Date"> -->
														<?php //if($ttime<$time && (date('Y-m-d',$ttime) == date('Y-m-d', strtotime($row['t_date'])))){ ?>
														<a href="https://admin.555xch.pro/tbl_transactions/remove_app/<?=$row['id']?>/<?=$userid?>" onclick="return confirm('Are you sure you want to delete?')" class="badge badge-danger view-btn">Delete</a></td>
														<?php //}?>
													</tr>
									<?php }
									else{
										?>
										<tr id="restt">
                                                       <td data-label="Date"><?=date('d M, Y', strtotime($row['t_date']))?></td>
                                                        <td data-label="Shift Name"><?=$row['shift_name']?></td>
                                                        <td data-label="Amt"><?=array_sum(explode(',',$row['trn_amt']))?></td>
                                                        <td data-label="Action">
                                                            <a href="View-page.php?id=<?=$row['id'].$view?>" class="badge badge-primary view-btn">View</a>
                                                       </td>
													</tr>
										<?php
									}
									}
									
										?>
										
                                          <!--<tr>
                                                       <td>10 Feb,2023</td>
                                                        <td>Faridabad</td>
                                                        <td>18,480</td>
                                                        <td>
                                                            <a href="View-page.html" class="badge badge-primary view-btn">View</a>
                                                        </td>
                                                    </tr>-->
                                          

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                 </form>

              </div>
             </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <!-- General JS Scripts -->
  <script src="assets/js/app.min.js"></script>
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
  <!-- JS Libraies -->
  <script src="assets/bundles/jquery-pwstrength/jquery.pwstrength.min.js"></script>
  <script src="assets/bundles/jquery-selectric/jquery.selectric.min.js"></script>
  <!-- Page Specific JS File -->
  <script src="assets/js/page/auth-register.js"></script>
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  
</body>


<!-- Mirrored from radixtouch.in/templates/snkthemes/grexsan/source/light/auth-register.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 23 Nov 2020 07:23:59 GMT -->
</html>
