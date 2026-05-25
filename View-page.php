<?php
session_start();
$SITEURL = 'http://' . $_SERVER['HTTP_HOST'] . '/Login.html';
//echo '<pre>'; print_r($_SESSION); echo '</pre>';
//echo '<pre>'; print_r($_SESSION); echo '</pre>';
//if(!isset($_SESSION["user_type"])&&(empty($_SESSION["user_type"]))){
//header('Location: ' . $SITEURL, true, 301);

//exit();
//}
if (isset($_GET['parentid'])) {
    $home = 'https://app.555xch.pro/Parent.php?parentid=' . $_GET["parentid"] . '&name=' . $_GET["name"];
    $entry = 'Entry-page.php?login=' . $_GET["login"] . '&user_type=ledger&parentid=' . $_GET["parentid"] . '&name=' . $_GET["name"];
    $view = '&login=' . $_GET["login"] . '&user_type=ledger&parentid=' . $_GET["parentid"] . '&name=' . $_GET["name"];
} else {

    $home = '#';
    $entry = 'Entry-page.php?login=' . $_GET["login"] . '&user_type=ledger';
    $view = '&login=' . $_GET["login"] . '&user_type=ledger';
}

?>
<?php                       // 155
$start_date = date('Y-m-01', strtotime('-1 month'));           // 1st of current month
$end_date    = date('Y-m-d');                 // today's date
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
        .view-btn:hover {
            color: white !important;
        }

        .check-btn a {
            margin-left: 0px;
            margin-bottom: 20px;
        }

        .card .card-body {
            padding-top: 14px;
            padding-bottom: 2px;
        }

        .text-shift {
            width: 100%;
            height: 40px;
        }

        .sum td {
            font-weight: 800;
        }

        <style>

        /* ---------------------------------
   HEADER MENU – RESPONSIVE FIX
---------------------------------- */
        .card-header.card-header-auth-1 {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
        }

        .card-header.card-header-auth-1 h4 {
            margin: 0;
            padding: 0;
            font-size: 14px;
        }

        .card-header.card-header-auth-1 h4 a {
            color: white !important;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        @media(max-width: 576px) {
            .card-header.card-header-auth-1 {
                gap: 6px;
            }

            .card-header.card-header-auth-1 h4 {
                flex: 1 1 48%;
                text-align: left;
            }
        }


        /* ---------------------------------
   TABLE – RESPONSIVE / MOBILE VIEW
---------------------------------- */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            white-space: nowrap;
        }

        @media(max-width: 768px) {

            /* hide table header */
            .table thead {
                display: none;
            }

            .table,
            .table tbody,
            .table tr,
            .table td {
                display: block;
                width: 100%;
            }

            .table tr {
                margin-bottom: 15px;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 6px;
                background: #fff;
            }

            .table td {
                border: none !important;
                padding-left: 110px !important;
                position: relative;
                text-align: left !important;
                font-size: 15px;
                padding-top: 8px;
                padding-bottom: 8px;
            }

            .table td::before {
                content: attr(data-label);
                position: absolute;
                top: 8px;
                left: 10px;
                font-weight: bold;
                font-size: 14px;
                color: #333;
            }
        }


        /* ---------------------------------
   TEXT / LAYOUT / SPACING FIXES
---------------------------------- */

        body {
            overflow-x: hidden;
        }

        .card-body,
        .card-header {
            padding: 12px 16px !important;
        }

        .form-group {
            width: 100%;
        }

        label {
            font-weight: 600;
        }

        .sum td {
            font-weight: 800;
        }

        textarea.form-control {
            min-height: 130px;
        }

        @media(max-width: 768px) {
            textarea.form-control {
                min-height: 170px;
            }
        }


        /* ---------------------------------
   FIX FOR MOBILE BROWSER DESKTOP MODE
---------------------------------- */
        @media (max-width: 1024px) {
            table {
                white-space: normal;
            }
        }

        /* FIRST TD LEFT, SECOND TD CENTER — ALWAYS */
        #save-stage.table td:first-child {
            text-align: left !important;
        }

        #save-stage.table td:nth-child(2) {
            text-align: center !important;
        }

        /* Keep both cells in one row even on mobile */
        #save-stage.table {
            white-space: nowrap !important;
        }

        #save-stage.table tr {
            display: table-row !important;
        }

        #save-stage.table td {
            display: table-cell !important;
            padding: 8px 10px !important;
            vertical-align: middle;
        }

        /* MOBILE FIX: SHOW BOTH VALUES WITHOUT SCROLL */
        @media(max-width: 480px) {

            #save-stage.table {
                width: 100% !important;
                table-layout: fixed;
                /* shrink to fit */
            }

            #save-stage.table tr {
                display: flex !important;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }

            #save-stage.table td {
                display: block !important;
                flex: 1;
                padding: 8px 6px !important;
                white-space: normal !important;
                /* text wraps instead of forcing scroll */
            }

            /* first column left */
            #save-stage.table td:first-child {
                text-align: left !important;
            }

            /* second column center */
            #save-stage.table td:nth-child(2) {
                text-align: center !important;
            }
        }
        /* FIX: prevent table from forcing scroll */
#save-stage, 
#save-stage * {
    min-width: 0 !important;
}

/* MOBILE: keep both columns on the same screen */
@media(max-width: 600px) {

    #save-stage {
        width: 100% !important;
        table-layout: fixed !important;
    }

    #save-stage tr {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
    }

    #save-stage td {
        flex: 1 !important;
        display: block !important;
        padding: 8px 6px !important;
        white-space: normal !important; /* text wraps normally */
    }

    #save-stage td:first-child {
        text-align: left !important;
    }

    #save-stage td:nth-child(2) {
        text-align: center !important;
    }
}
@media (max-width: 575.98px) {
    .table-responsive table {
        min-width: unset !important;
    }
}

    </style>

    </style>
</head>

<body class="background-image-body">
    <div class="loader"></div>
    <?php //echo 'ttp'; 
    //var_dump($_GET['status']);
    if (isset($_GET['status']) && ($_GET['status'] == '1')) {
        echo '<script>alert("transaction recorded successfully");</script>';
    }
    if (isset($_GET['status']) && $_GET['status'] == '0') {
        echo '<script>alert("Invalid Entry. Please try again!!");</script>';
    }
    if (isset($_GET['parentid'])) {
        $home = 'https://new.555xch.pro/Parent.php?parentid=' . $_GET["parentid"] . '&name=' . $_GET["name"];
    } else {
        $home = '#';
    }
    ?>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
                        <div class="card card-auth">
                            <div class="card-header card-header-auth-1">
                                <h4 class="text-white"><a href="<?= $home ?>" style="color:white"><i class="fas fa-home" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Home</a>
                                </h4>
                                <h4 class="text-white"><a href="<?= $entry ?>" style="color:white"><i class="fas fa-pen" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Entry</a></h4>
                                <h4 class="text-white"><a href="Date-shift.php<?= $view ?>"><i class="fas fa-book" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> View Transaction</a></h4>
                                <h4 class="text-white"><a href="https://new.555xch.pro/appdemo/Login.html" style="color:white"><i class="fas fa-pen" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Logout</a></h4>
                            </div>
                            <div class="card-body">
                                <?php
                                $servername = "localhost";
                                $username = "555prouser";
                                $password = "e2OFVjrRK77ljyfs4z@R";
                                $dbname = "555prodb";
                                $userid = $_SESSION['login'];
                                $id = $_GET['id'];
                                // Create connection
                                $mysqli = new mysqli($servername, $username, $password, $dbname);
                                $sql = "SELECT `tbl_master_transaction`.`id`,  `tbl_agent`.`agent_name`, `tbl_trans_numbers`.`created_date` as `createddate`, `tbl_trans_numbers`.`modified_date` as `modifieddate`, `tbl_master_transaction`.`t_date`, `tbl_master_transaction`.`total_number_amount`, `tbl_master_transaction`.`created_date`, `tbl_master_transaction`.`party_id`, `tbl_shift`.`shift_name` as `shift_name`, `tbl_shift`.`open_date`, `tbl_shift`.`super_admin`, `tbl_shift`.`data_entry_operator`, `tbl_shift`.`id` as `shift_id`, `tbl_ledger`.`ledger_name`, `tbl_trans_numbers`.`number` as `trnno`, `tbl_trans_numbers`.`amount` as `trn_amt` FROM `tbl_master_transaction` JOIN `user_shift_timings` ON `user_shift_timings`.`id` = `tbl_master_transaction`.`shift_id` JOIN `tbl_shift` ON `tbl_shift`.`id` = `user_shift_timings`.`shift_id` JOIN `tbl_trans_numbers` ON `tbl_trans_numbers`.`master_id` = `tbl_master_transaction`.`id` JOIN `tbl_ledger` ON `tbl_ledger`.`id` = `tbl_master_transaction`.`party_id` LEFT JOIN `tbl_agent` ON `tbl_ledger`.`agent_id` = `tbl_agent`.`id`  
				WHERE `tbl_master_transaction`.`id` = '" . $id . "'";

                                $rs = $mysqli->query($sql);
                                //while ($row = mysqli_fetch_assoc($rs)) {
                                $row = mysqli_fetch_assoc($rs);
                                ?>
                                <form method="POST">
                                    <div class="row">
                                        <div class="form-group col-6">
                                            <label for=""><?= $row['shift_name'] ?></label>
                                        </div>
                                        <div class="form-group col-6">
                                            <label for="email"><?= date('d M, Y', strtotime($row['createddate'])) ?></label><br />
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <script type="text/javascript">
                                                        //var JQ = jQuery.noConflict();
                                                        // Basic example
                                                        $(document).ready(function() {
                                                            $('#dtBasicExample').DataTable();

                                                        });
                                                    </script>
                                                    <asp:HiddenField ID="hfId" runat="server" Value="0" />
                                                    <table class="table table-striped table-hover" id="save-stage" style="width: 100%;">

                                                        <thead>
                                                            <tr>

                                                                <th>Num</th>
                                                                <th>Amt</th>
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            <?php


                                                            ///echo '<pre>'; print_r($row); echo '</pre>'; //die;
                                                            $num = explode(',', $row['trnno']);
                                                            $amt = explode(',', $row['trn_amt']);
                                                            //echo '<pre>'; print_r($num); echo '</pre>';
                                                            foreach ($num as $key => $val) {
                                                                if ($val >= 0) {
                                                                    //echo $val.'<br>';
                                                            ?>
                                                                    <tr class="sum">
                                                                        <td><?= $val ?></td>
                                                                        <td>
                                                                            <?= $amt[$key]; ?>
                                                                        </td>
                                                                    </tr>
                                                            <?php
                                                                }
                                                            }
                                                            ?>


                                                            <tr class="sum">
                                                                <td>Total:</td>
                                                                <td>
                                                                    <?= array_sum($amt); ?>
                                                                </td>
                                                            </tr>
                                                            <?php //} 
                                                            ?>
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