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

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <title>XCH555</title>

    <link rel="stylesheet" href="assets/css/app.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/bundles/flag-icon-css/css/flag-icon.min.css">

    <style>
        body {
            background: #f5f5f5;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* ---------------------------
           DESKTOP LAYOUT
        ----------------------------*/
        .page-wrapper {
            max-width: 1140px;
            margin: 0 auto;
            padding: 15px;
        }

        /* ---------------------------
           MOBILE + TABLET: REMOVE ALL SPACING
        ----------------------------*/
        @media (max-width: 1139px) {
            body {
                padding: 0 !important;
                margin: 0 !important;
            }

            .page-wrapper {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .container-jantri {
                border-radius: 0 !important;
                box-shadow: none !important;
                padding: 2px !important;
                margin: 0 !important;
            }

            .card-body {
                padding: 0 !important;
            }

            .container,
            .row,
            .col-12,
            .col-sm-10,
            .col-md-8,
            .col-lg-8 {
                padding: 0 !important;
                margin: 0 !important;
            }
        }

        .container-jantri {
            background: white;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .top-filter {
            display: flex;
            flex-direction: row;
            gap: 15px;
            flex-wrap: nowrap;
            width: 100%;
            margin-bottom: 15px;
        }

        .top-filter select,
        .top-filter input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            min-width: 0;
        }

        .filter-label {
            font-weight: bold;
            margin-bottom: 6px;
            display: block;
            font-size: 16px;
        }

        /* RESET BUTTON STYLES */
        .reset-btn {
            width: 100%;
            padding: 10px;
            background: #c41e3a;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-btn:hover {
            background: #a01729;
        }

        .reset-btn:active {
            background: #8a1220;
        }

        .reset-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* ========== TABLE STYLES ========== */
        .numbers-container-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            margin-bottom: 15px;
            -webkit-overflow-scrolling: touch;
        }

        .numbers-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .numbers-table td {
            border: 1px solid #ddd;
            padding: 8px 4px;
            text-align: center;
            font-size: 14px;
            vertical-align: middle;
        }

        .numbers-table input {
            width: 100%;
            padding: 6px 4px;
            border: none;
            text-align: center;
            font-size: 13px;
            box-sizing: border-box;
            font-family: 'Courier New', monospace;
        }

        .numbers-table input:focus {
            outline: 1px solid #0a66c2;
            background: #f0f8ff;
        }

        .numbers-table td.total-cell {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #c41e3a;
            min-width: 70px;
        }

        .numbers-table tr.summary-row {
            background-color: #e8e8e8;
            font-weight: bold;
        }

        .numbers-table tr.summary-row td {
            background-color: #e8e8e8;
            color: #333;
            border-top: 2px solid #999;
        }

        .numbers-table tr.summary-row td.grand-total-cell {
            background-color: #d0d0d0;
            color: #c41e3a;
            font-size: 15px;
        }

        @media (min-width: 1400px) {
            .page-wrapper {
                max-width: 1200px;
            }
        }

        /* Remove number spinner */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Grand Total */
        .grand-total-box {
            background: #f0f0f0;
            border: 2px solid #999;
            padding: 12px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            color: #c41e3a;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #0a66c2;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
            font-size: 16px;
        }

        .submit-btn:hover {
            background: #084399;
        }

        .footer-section {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            align-items: center;
        }

        .footer-grand-total {
            flex: 0 0 70%;
            background: #f5f5f5;
            padding: 15px 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-grand-total-label {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .footer-grand-total-value {
            font-size: 18px;
            font-weight: bold;
            color: #c41e3a;
        }

        .footer-submit {
            flex: 0 0 30%;
            display: flex;
            justify-content: flex-end;
        }

        .footer-submit .submit-btn {
            width: 100%;
            margin-top: 0;
        }

        .card-auth .card-header {
            background: #6d0a0a;
            padding: 20px;
        }

        .card-header h4 a {
            color: white !important;
        }

        /* Mobile optimization - Responsive table */
        @media (max-width: 768px) {
            .numbers-table td {
                padding: 0px;
                font-size: 12px;
            }

            .numbers-table td.total-cell {
                min-width: 60px;
            }

            .numbers-table input {
                font-size: 11px;
                padding: 3px 2px;
            }

            .footer-section {
                gap: 10px;
            }

            .footer-grand-total {
                flex: 0 0 65%;
                padding: 10px 15px;
            }

            .footer-submit {
                flex: 0 0 35%;
            }

            .footer-submit .submit-btn {
                width: 100%;
            }

            .top-filter {
                gap: 10px;
            }

            .reset-btn {
                padding: 8px;
                font-size: 12px;
                height: 40px;
            }
        }

        /* Tablet */
        @media (max-width: 600px) {
            .numbers-table td {
                padding: 0px;
                font-size: 11px;
            }

            .numbers-table td.total-cell {
                min-width: 50px;
            }

            .numbers-table input {
                font-size: 10px;
                padding: 2px 1px;
            }

            .footer-section {
                gap: 8px;
            }

            .footer-grand-total {
                flex: 0 0 65%;
                padding: 10px 12px;
            }

            .footer-grand-total-label {
                font-size: 14px;
            }

            .footer-grand-total-value {
                font-size: 16px;
            }

            .footer-submit {
                flex: 0 0 35%;
            }

            .footer-submit .submit-btn {
                width: 100%;
            }

            .top-filter {
                gap: 8px;
            }

            .reset-btn {
                padding: 6px;
                font-size: 11px;
                height: 38px;
            }
        }

        /* Extra small phones */
        @media (max-width: 480px) {
            .numbers-table td {
                padding: 0px;
                font-size: 10px;
            }

            .numbers-table td.total-cell {
                min-width: 45px;
                font-size: 9px;
            }

            .numbers-table input {
                font-size: 9px;
                padding: 0px;
            }

            .footer-section {
                gap: 8px;
            }

            .footer-grand-total {
                flex: 0 0 65%;
                padding: 8px 10px;
            }

            .footer-grand-total-label {
                font-size: 13px;
            }

            .footer-grand-total-value {
                font-size: 14px;
            }

            .footer-submit {
                flex: 0 0 35%;
            }

            .footer-submit .submit-btn {
                width: 100%;
            }

            .top-filter {
                gap: 5px;
            }

            .reset-btn {
                padding: 4px;
                font-size: 10px;
                height: 36px;
            }
        }

        .row-input {
            color: #000;
            /* typed text will be dark */
            font-weight: 900;
        }

        .row-input::placeholder {
            color: #ccc;
            /* placeholder stays light */
        }
    </style>
</head>

<body class="background-image-body">

    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">

                        <div class="card card-auth">
                            <!-- <div class="card-header card-header-auth-1">
                                <h4><a href="#"><i class="fas fa-home"></i> Home</a></h4>
                                <h4><a onclick="showentry()"><i class="fas fa-pen"></i> Entry</a></h4>
                                <h4><a onclick="showfromto()"><i class="fas fa-pen"></i> From-To</a></h4>
                                <h4><a onclick="showcross()"><i class="fas fa-pen"></i> Cross</a></h4>
                                <h4><a href="#"><i class="fas fa-book"></i> View Transaction</a></h4>
                                <h4><a href="#"><i class="fas fa-book"></i> Hisab</a></h4>
                                <h4><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></h4>
                            </div> -->
                            <div class="card-header card-header-auth-1">
                               <h4 class="text-white"><a href="<?=$home?>" style="color:white"><i class="fas fa-home" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Home</a>
							   </h4> 
							   <h4 class="text-white"><a href="<?=$entry?>" style="color:white"><i class="fas fa-pen" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Entry</a></h4>
                               <h4 class="text-white"><a style="color:white !important" href="Date-shift.php?login=<?=$_GET['login']?>&user_type=ledger"><i class="fas fa-book" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> View Transaction</a></h4>
                               <h4 class="text-white"> <a href="statement.php/<?=  $_GET['login'] ?>?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" style="color:white">
                                        <i class="fas fa-book" style="color:white !important" id="showbtn"></i> Statement
                                    </a></h4>
                               <h4 class="text-white"><a href="https://new.bull99exch.com/appdemo/Login.html" style="color:white"><i class="fas fa-book" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Logout</a></h4>
							</div>

                            <div class="card-body">
                                <div class="page-wrapper">
                                    <div class="container-jantri">

                                        <!-- SHIFT + DATE + RESET BUTTON -->
                                        <div class="top-filter">
                                            <div style="flex:1;">
                                                <label class="filter-label">Shift</label>
                                                <select name="shift" id="shiftSelect" class="form-control" required>
                                                    <option value="">Select Shift</option>

                                                    <?php
                                                    $conn = new mysqli("localhost", "555prouser", "e2OFVjrRK77ljyfs4z@R", "555prodb");

                                                    if ($conn->connect_error) {
                                                        die("Database connection failed: " . $conn->connect_error);
                                                    }

                                                    $sql = "SELECT id, shift_name FROM tbl_shift ORDER BY shift_name ASC";
                                                    $result = $conn->query($sql);

                                                    while ($row = $result->fetch_assoc()) {
                                                        echo '<option value="' . $row['id'] . '">' . $row['shift_name'] . '</option>';
                                                    }

                                                    $conn->close();
                                                    ?>
                                                </select>
                                            </div>

                                            <div style="flex:1;">
                                                <label class="filter-label">Date</label>
                                                <input id="dateField" type="text" readonly>
                                            </div>

                                            <div style="flex:0.6; display:flex; align-items:flex-end;">
                                                <button type="button" class="reset-btn" onclick="resetForm()" title="Reset all values">Reset</button>
                                            </div>
                                        </div>

                                        <!-- NUMBER TABLE -->
                                        <div class="numbers-container-wrapper">
                                            <div id="numbers-container">
                                                <table class="numbers-table">
                                                    <tbody>
                                                        <?php
                                                        $start_num = 1;
                                                        for ($row = 1; $row <= 10; $row++) {
                                                            echo "<tr class='data-row' data-row-id='row-$row'>";
                                                            for ($col = 0; $col < 10; $col++) {
                                                                echo "<td>$start_num<br>";
                                                        ?>
                                                                <input type="text" class="row-input" data-row="row-<?php echo $row; ?>" data-col="<?php echo $col; ?>" style="width: 100%;border: none;">
                                                        <?php
                                                                echo "</td>";
                                                                $start_num++;
                                                            }
                                                            echo "<td class='total-cell' data-row-total='row-$row'>0</td>";
                                                            echo "</tr>";
                                                        }

                                                        echo "<tr class='summary-row'>";
                                                        for ($col = 0; $col < 10; $col++) {
                                                            echo "<td class='col-total' data-col-total='col-$col'>0</td>";
                                                        }
                                                        echo "<td class='grand-total-cell' id='grand-total'>0</td>";
                                                        echo "</tr>";
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- SECOND TABLE: B AND A SECTIONS -->
                                        <div class="numbers-container-wrapper" style="margin-top: 20px;">
                                            <div id="numbers-container">
                                                <table class="numbers-table">
                                                    <thead>
                                                        <tr style='background-color: #e8e8e8;'>
                                                            <td style='font-weight: bold; text-align: center; background-color: #e8e8e8;'></td>
                                                            <?php
                                                            for ($col = 1; $col <= 10; $col++) {
                                                                echo "<td style='font-weight: bold; text-align: center; background-color: #e8e8e8; color: #333;'>$col</td>";
                                                            }
                                                            ?>
                                                            <td style='font-weight: bold; text-align: center; background-color: #e8e8e8;'>Total</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        echo "<tr class='section-row' style='background-color: #f5f5f5;'>";
                                                        echo "<td style='font-weight: bold; text-align: center; background-color: #f5f5f5;'>B</td>";
                                                        for ($col = 0; $col < 10; $col++) {
                                                            echo "<td><input type='text' class='b-input' data-col='$col' style='width: 100%;border: none;'></td>";
                                                        }
                                                        echo "<td class='total-cell' data-section-total='section-b'>0</td>";
                                                        echo "</tr>";

                                                        echo "<tr class='section-row' style='background-color: #f5f5f5;'>";
                                                        echo "<td style='font-weight: bold; text-align: center; background-color: #f5f5f5;'>A</td>";
                                                        for ($col = 0; $col < 10; $col++) {
                                                            echo "<td><input type='text' class='a-input' data-col='$col' style='width: 100%;border: none;'></td>";
                                                        }
                                                        echo "<td class='total-cell' data-section-total='section-a'>0</td>";
                                                        echo "</tr>";
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="footer-section">
                                            <div class="footer-grand-total">
                                                <span class="footer-grand-total-label">Grand Total</span>
                                                <span class="footer-grand-total-value" id="footer-grand-total">0</span>
                                            </div>
                                            <div class="footer-submit">
                                                <button class="submit-btn">Submit</button>
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Set today's date
            let today = new Date();
            let dd = String(today.getDate()).padStart(2, '0');
            let mm = String(today.getMonth() + 1).padStart(2, '0');
            let yyyy = today.getFullYear();
            document.getElementById("dateField").value = dd + "-" + mm + "-" + yyyy;

            // Add event listeners to all row inputs for real-time total calculation
            const inputs = document.querySelectorAll('.row-input');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    const rowId = this.getAttribute('data-row');
                    const colNum = this.getAttribute('data-col');
                    calculateRowTotal(rowId);
                    calculateColumnTotal(colNum);
                    calculateGrandTotal();
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const currentRow = this.getAttribute('data-row');
                        const currentCol = parseInt(this.getAttribute('data-col'));
                        let nextInput;

                        if (currentCol < 9) {
                            nextInput = document.querySelector(`.row-input[data-row="${currentRow}"][data-col="${currentCol + 1}"]`);
                        } else {
                            const currentRowNum = parseInt(currentRow.split('-')[1]);
                            if (currentRowNum < 10) {
                                const nextRowId = `row-${currentRowNum + 1}`;
                                nextInput = document.querySelector(`.row-input[data-row="${nextRowId}"][data-col="0"]`);
                            } else {
                                nextInput = document.querySelector('.b-input[data-col="0"]');
                            }
                        }

                        if (nextInput) {
                            nextInput.focus();
                            nextInput.select();
                        }
                    }
                });
            });

            // Add event listeners for B section inputs
            const bInputs = document.querySelectorAll('.b-input');
            bInputs.forEach(input => {
                input.addEventListener('input', function() {
                    calculateSectionTotal('section-b');
                    calculateFooterGrandTotal();
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const currentCol = parseInt(this.getAttribute('data-col'));
                        let nextInput;

                        if (currentCol < 9) {
                            nextInput = document.querySelector(`.b-input[data-col="${currentCol + 1}"]`);
                        } else {
                            nextInput = document.querySelector('.a-input[data-col="0"]');
                        }

                        if (nextInput) {
                            nextInput.focus();
                            nextInput.select();
                        }
                    }
                });
            });

            // Add event listeners for A section inputs
            const aInputs = document.querySelectorAll('.a-input');
            aInputs.forEach(input => {
                input.addEventListener('input', function() {
                    calculateSectionTotal('section-a');
                    calculateFooterGrandTotal();
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const currentCol = parseInt(this.getAttribute('data-col'));
                        let nextInput;

                        if (currentCol < 9) {
                            nextInput = document.querySelector(`.a-input[data-col="${currentCol + 1}"]`);
                        }

                        if (nextInput) {
                            nextInput.focus();
                            nextInput.select();
                        }
                    }
                });
            });

            function calculateRowTotal(rowId) {
                const rowInputs = document.querySelectorAll(`.row-input[data-row="${rowId}"]`);
                let total = 0;

                rowInputs.forEach(input => {
                    const value = input.value.trim();
                    if (value && !isNaN(value)) {
                        total += parseInt(value, 10);
                    }
                });

                const totalCell = document.querySelector(`[data-row-total="${rowId}"]`);
                if (totalCell) {
                    totalCell.textContent = total;
                }
            }

            function calculateColumnTotal(colNum) {
                let columnTotal = 0;

                for (let row = 1; row <= 10; row++) {
                    const input = document.querySelector(`.row-input[data-row="row-${row}"][data-col="${colNum}"]`);
                    if (input) {
                        const value = input.value.trim();
                        if (value && !isNaN(value)) {
                            columnTotal += parseInt(value, 10);
                        }
                    }
                }

                const colTotalCell = document.querySelector(`[data-col-total="col-${colNum}"]`);
                if (colTotalCell) {
                    colTotalCell.textContent = columnTotal;
                }
            }

            function calculateGrandTotal() {
                let grandTotal = 0;
                const inputs = document.querySelectorAll('.row-input');

                inputs.forEach(input => {
                    const value = input.value.trim();
                    if (value && !isNaN(value)) {
                        grandTotal += parseInt(value, 10);
                    }
                });

                const grandTotalCell = document.getElementById('grand-total');
                if (grandTotalCell) {
                    grandTotalCell.textContent = grandTotal;
                }

                calculateFooterGrandTotal();
            }

            function calculateSectionTotal(sectionId) {
                let sectionTotal = 0;

                if (sectionId === 'section-b') {
                    const bInputs = document.querySelectorAll('.b-input');
                    bInputs.forEach(input => {
                        const value = input.value.trim();
                        if (value && !isNaN(value)) {
                            sectionTotal += parseInt(value, 10);
                        }
                    });
                } else if (sectionId === 'section-a') {
                    const aInputs = document.querySelectorAll('.a-input');
                    aInputs.forEach(input => {
                        const value = input.value.trim();
                        if (value && !isNaN(value)) {
                            sectionTotal += parseInt(value, 10);
                        }
                    });
                }

                const sectionTotalCell = document.querySelector(`[data-section-total="${sectionId}"]`);
                if (sectionTotalCell) {
                    sectionTotalCell.textContent = sectionTotal;
                }
            }

            function calculateFooterGrandTotal() {
                let footerTotal = 0;

                const grandTotalCell = document.getElementById('grand-total');
                if (grandTotalCell) {
                    const grandTotalValue = parseInt(grandTotalCell.textContent, 10) || 0;
                    footerTotal += grandTotalValue;
                }

                const sectionBCell = document.querySelector('[data-section-total="section-b"]');
                if (sectionBCell) {
                    const sectionBValue = parseInt(sectionBCell.textContent, 10) || 0;
                    footerTotal += sectionBValue;
                }

                const sectionACell = document.querySelector('[data-section-total="section-a"]');
                if (sectionACell) {
                    const sectionAValue = parseInt(sectionACell.textContent, 10) || 0;
                    footerTotal += sectionAValue;
                }

                const footerGrandTotalCell = document.getElementById('footer-grand-total');
                if (footerGrandTotalCell) {
                    footerGrandTotalCell.textContent = footerTotal;
                }
            }
        });

        // RESET FUNCTION
        function resetForm() {
            // Confirm before resetting
            if (!confirm('Are you sure you want to reset all values? This cannot be undone.')) {
                return;
            }

            // Clear all row inputs
            const rowInputs = document.querySelectorAll('.row-input');
            rowInputs.forEach(input => {
                input.value = '';
            });

            // Clear all B section inputs
            const bInputs = document.querySelectorAll('.b-input');
            bInputs.forEach(input => {
                input.value = '';
            });

            // Clear all A section inputs
            const aInputs = document.querySelectorAll('.a-input');
            aInputs.forEach(input => {
                input.value = '';
            });

            // Reset all row totals
            for (let row = 1; row <= 10; row++) {
                const totalCell = document.querySelector(`[data-row-total="row-${row}"]`);
                if (totalCell) {
                    totalCell.textContent = '0';
                }
            }

            // Reset all column totals
            for (let col = 0; col < 10; col++) {
                const colTotalCell = document.querySelector(`[data-col-total="col-${col}"]`);
                if (colTotalCell) {
                    colTotalCell.textContent = '0';
                }
            }

            // Reset section totals
            const sectionBCell = document.querySelector('[data-section-total="section-b"]');
            if (sectionBCell) {
                sectionBCell.textContent = '0';
            }

            const sectionACell = document.querySelector('[data-section-total="section-a"]');
            if (sectionACell) {
                sectionACell.textContent = '0';
            }

            // Reset grand totals
            const grandTotalCell = document.getElementById('grand-total');
            if (grandTotalCell) {
                grandTotalCell.textContent = '0';
            }

            const footerGrandTotalCell = document.getElementById('footer-grand-total');
            if (footerGrandTotalCell) {
                footerGrandTotalCell.textContent = '0';
            }

            // Focus on first input for convenience
            const firstInput = document.querySelector('.row-input[data-row="row-1"][data-col="0"]');
            if (firstInput) {
                firstInput.focus();
            }

            // Show success message
            alert('All values have been reset successfully!');
        }
    </script>

</body>

</html>
