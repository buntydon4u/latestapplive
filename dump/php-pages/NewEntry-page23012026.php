<?php
session_start();
$SITEURL = 'http://' . $_SERVER['HTTP_HOST'] . '/Login.html';
//echo '<pre>'; print_r($_SESSION); echo '</pre>';
//echo '<pre>'; print_r($_SESSION); echo '</pre>';
//if(!isset($_GET["user_type"])&&(empty($_GET["user_type"]))){
//header('Location: ' . $SITEURL, true, 301);
//exit();
//}
$_SESSION['login'] = $_GET['login']; 
$_SESSION['user_type'] = $_GET['user_type'];
if (isset($_GET['parentid'])) {
    $home = 'https://new.555xch.pro/appdemo/Parent.php?parentid=' . $_GET["parentid"] . '&name=' . $_GET["name"];
    $entry = 'Entry-page.php?login=' . $_GET["login"] . '&user_type=ledger&parentid=' . $_GET["parentid"] . '&name=' . $_GET["name"];
    $view = '?login=' . $_GET["login"] . '&user_type=ledger&parentid=' . $_GET["parentid"] . '&name=' . $_GET["name"];
} else {

    $home = '#'; 
    $entry = 'Entry-page.php?login=' . $_GET["login"] . '&user_type=ledger';
    $view = '?login=' . $_GET["login"] . '&user_type=ledger';
}
?>
<!DOCTYPE html>
<html lang="en">
<style>
    .card .card-header h4 {
        padding-right: 20px !important;
    }

    .card .card-header h4 i {
        margin-right: 5px !important;
    }

    /*#addtrn tr:last-child {
    display: none;
}*/
</style>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>XCH555</title>
    <!-- General CSS Files -->
    <link rel="stylesheet" href="assets/css/app.min.css">
    <link rel="stylesheet" href="assets/bundles/jquery-selectric/selectric.css">
    <!-- Template CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="assets/css/components.css">
    <link rel="stylesheet" href="assets/bundles/flag-icon-css/css/flag-icon.min.css" rel="stylesheet" />
    <!-- Custom style CSS -->

    <link href="assets/css/daterangepicker.css" rel="stylesheet">
    <link rel='shortcut icon' type='image/x-icon' href='assets/img/favicon.ico' />
    <style>
        @media(max-width:768px) {
            textarea.form-control {
                height: 210px !important;
            }
        }
    </style>
    <style type="text/css">
        span span#aaa {
            color: red;
            font-size: 18px;
            font-style: italic;
        }

        #D_data {
            display: none;
        }
    </style>
    <script language="javascript">
        function checkInput(ob) {
            var invalidChars = /[a-zA-Z]/gi
            if (invalidChars.test(ob.value)) {
                ob.value = ob.value.replace(invalidChars, "");
            }
        }
    </script>
    <script>
        function cleanWhatsAppJunk(rawText) {
            let lines = rawText.split("\n");
            let cleaned = [];

            for (let line of lines) {

                // Remove WhatsApp timestamp + name (including weird unicode names)
                line = line.replace(
                    /^\[\d{1,2}:\d{2}\s*(am|pm),\s*\d{1,2}\/\d{1,2}\/\d{4}\]\s*.*?:\s*/i,
                    ""
                );

                // Remove arrows / garbage like >>>>
                line = line.replace(/>{2,}/g, "");

                // Normalize separators
                line = line.replace(/[\/\.]/g, ",");

                // Remove multiple stars
                line = line.replace(/\*{2,}/g, "*");

                if (line.trim()) cleaned.push(line.trim());
            }

            return cleaned.join("\n");
        }

        function isValidPattern(line) {
            line = line.trim().toUpperCase();

            // Normalize operators
            line = line.replace(/\b(INTO|INTU|IN|X|×|=)\b/g, "*");
            line = line.replace(/\s+/g, " ");

            /*
              VALID NUMBER PART:
              - 1 or 2 digit number (11)
              - 4 digit number (1111 / 9999)
              - A1 / 1A
              - B1 / 1B
            */
            const numberToken = "(?:\\d{1,2}|\\d{4}|A\\d|\\dA|B\\d|\\dB)";
            const numberList = `${numberToken}(?:\\s*,\\s*${numberToken})*`;

            // VALID AMOUNT
            const amount = "\\d+";

            // FULL VALID PATTERN
            const fullPattern = new RegExp(
                `^${numberList}\\s*(?:\\(|\\*)\\s*${amount}\\s*\\)?$`
            );

            return fullPattern.test(line);
        }

        var gnumamtlist = "",
            gfinalamount = 0;
        let RAW_INPUT = "";
        let ERROR_LIST = [];
        let VALIDATION_ONLY = false;

        function addError(originalLine, reason) {
            ERROR_LIST.push({
                line: originalLine,
                reason: reason
            });
        }

        function renderErrors() {
            let html = "<ul style='padding-left:15px'>";

            ERROR_LIST.forEach((err, index) => {
                html += `
                <li style="color:red; cursor:pointer; margin-bottom:6px"
                    onclick="restoreForCorrection(${index})">
                    ❌ ${err.line}
                    <small style="color:#555">(${err.reason})</small>
                </li>`;
            });

            html += "</ul>";
            document.getElementById("errorList").innerHTML = html;
        }

        function restoreForCorrection(index) {
            const textarea = document.getElementById("TextBox1");
            textarea.value = RAW_INPUT;
            textarea.focus();

            // Scroll to error line
            let lines = RAW_INPUT.split("\n");
            let pos = 0;

            for (let i = 0; i < lines.length; i++) {
                if (lines[i].includes(ERROR_LIST[index].line)) break;
                pos += lines[i].length + 1;
            }

            textarea.setSelectionRange(pos, pos + ERROR_LIST[index].line.length);
        }

        function disableSubmit() {
            const btn = document.getElementById("submitbtn");
            btn.disabled = true;
            btn.style.backgroundColor = "#c43140";
        }

        function enableSubmit() {
            const btn = document.getElementById("submitbtn");
            btn.disabled = false;
            btn.style.backgroundColor = "#28a745";
        }

        function CheckAkhar() {
            if (!RAW_INPUT) {
                RAW_INPUT = document.getElementById("TextBox1").value;
            }
            ERROR_LIST = [];
            //window.value= document.getElementById("TextBox1").value;

            document.getElementById("s_data").innerHTML = "";
            document.getElementById('addtrn').innerHTML = "";
            gnumamtlist = "";
            gfinalamount = 0;
            document.getElementById("TextBox1").value = cleanWhatsAppJunk(document.getElementById("TextBox1").value);
            // Only clean WhatsApp junk - no additional replacements

            // ❌ DO NOT overwrite RAW here

            renderErrors();
        }

        function cleanAfterAsteriskUntilDigitNotFound(input) {
            const index = input.indexOf('*');
            if (index === -1) return input; // no * found

            let cleaned = input.slice(0, index + 1); // include *
            let rest = input.slice(index + 1);

            let foundDigit = false;
            for (let i = 0; i < rest.length; i++) {
                if (/\d/.test(rest[i])) {
                    cleaned += rest.slice(i);
                    foundDigit = true;
                    break;
                }
            }

            return foundDigit ? cleaned : input;
        }

        function cleanLineAfterAsterisk(inputText) {
            const lines = inputText.split('\n');
            //alter(lines);
            const cleaned = [];

            for (let i = 0; i < lines.length; i++) {
                str = cleanAfterAsteriskUntilDigitNotFound(lines[i]);
                const index = str.indexOf('*');
                if (index != -1) {
                    for (k = index + 1; k < str.length; k++) {
                        if (!/\d/.test(str[k])) {
                            //    alert("non Digit =============  "+ str[k])
                            str = str.substring(0, k);

                        } else {
                            // alert("Digit =============  "+ str[k]);
                        }
                    }
                    //alert(str);
                    lines[i] = str;
                    //lines[i] = lines[i].replace(/^,/, '');
                    //alert(lines[i]);
                }

                //alert(lines);
            }

            //alter("chek");
            return lines.join('\n');
        }

        function FillData() {
            document.getElementById("TextBox1").value =
                document.getElementById("D_data").value;

            document.getElementById("TextBox1").focus();
        }



        function IsNonDigit(str, i, akharchar) {
            var ascode = str.charCodeAt(i);

            if (ascode < 48 || ascode > 57 || isNaN(ascode)) {
                return true;
                //alert(ascode);
            } else {
                var charcount = 0;

                for (j = 0; j < str.length; j++) {
                    var asco = str.charCodeAt(j);
                    var asco1 = akharchar.charCodeAt(0);
                    if (asco >= 48 && asco <= 57) {
                        if (asco != ascode)
                            return true;
                    } else if (asco1 == asco)
                        charcount++;
                    else
                        return true;

                }
                if (charcount > 1) return true;
            }

            return false;

        }

        function Mycode() {
            ERROR_LIST = [];
            document.getElementById('errorList').innerHTML = "";
            document.getElementById('addtrn').innerHTML = "";

            let lines = document.getElementById("TextBox1").value.split("\n");

            // ---------- PASS 1: VALIDATION ----------
            VALIDATION_ONLY = true;

            for (let i = 0; i < lines.length; i++) {
                if (!lines[i].trim()) continue;
                Mycode1(lines[i]); // validate only
            }

            // If errors → STOP here
            if (ERROR_LIST.length > 0) {
                renderErrors();
                disableSubmit();
                VALIDATION_ONLY = false;
                return;
            }

            // ---------- PASS 2: COMMIT ----------
            VALIDATION_ONLY = false;
            document.getElementById('addtrn').innerHTML = "";

            for (let i = 0; i < lines.length; i++) {
                if (!lines[i].trim()) continue;
                Mycode1(lines[i]); // insert rows
            }

            enableSubmit();
            RAW_INPUT = ""; // clear only when fully approved
            document.getElementById("TextBox1").value = "";
        }

        function heighlight(str) {
            var txtstr = document.getElementById("D_data").value;

            let newText = txtstr.replace(
                str,
                "<a href='#' onclick='FillData(); return false;'>" +
                "<span id='aaa'>" + str + "</span></a>"
            );

            document.getElementById("s_data").innerHTML = newText;
        }



        function Mycode1(p_str) {

            // normalize internally ONLY
            var bval = p_str.toUpperCase();
            bval = bval.replace(/INTO|INTU|IN|X|×|=/g, "*");
            bval = bval.replace(/[._\-\/\s|]/g, ",");
            bval = bval.replace(/,+/g, ",");

            var msg;
            var finalnumlist = "";
            const numberList1 = (bval
                .split("*"));
            console.log(numberList1);
            //console.log(numberList1);return false;
            if (numberList1.length == 1) {
                addError(bval, "Amount Not Found against those number");
                heighlight(bval);
                return false;
            }
            if (numberList1.length > 2) {
                let msg = "Multiple Amount ( ";
                for (var i = 1; i < numberList1.length; i = i + 1)
                    msg = msg + numberList1[i] + ", ";
                msg = msg + " ) against those number";
                addError(numberList1[0], msg);
                heighlight(numberList1[0]);
                return false;
            }

            var correctamount = "";
            for (var j = 0; j < numberList1[1].length; j++) // remove non digit from amount
            {
                var ascode = numberList1[1].charCodeAt(j);


                if (ascode >= 48 && ascode <= 57) {
                    correctamount = correctamount + numberList1[1][j];
                }

            }

            let numlistamounts = parseInt(correctamount);
            if (isNaN(numlistamounts)) {
                addError(numberList1[0], "Not able to get amount");
                heighlight(numberList1[0]);
                return;
            }

            numberList1[1] = numlistamounts;

            var numlist = numberList1[0];
            var nums = numlist.split(",");
            var amtlist = "";
            //console.log(nums);return false;

            // making list of number and amouunt in order with coma seprate


            for (var i = 0; i < nums.length; i = i + 1) {

                var str = nums[i];
                if (str.trim() != "") {
                    if (str.length > 2 || str.includes("A") || str.includes("B")) {
                        var ind;
                        var newstr;
                        if (str == '100') {
                            str = '00';
                        }
                        if (str.includes("A")) {
                            ind = str.indexOf("A");
                            if (ind == 0) {

                                if (IsNonDigit(str, 1, "A")) {
                                    addError(str, "Invalid number format");
                                    heighlight(str);
                                    return false;
                                }
                                newstr = str[1] + str[1] + str[1] + str[1];
                                nums[i] = newstr;

                            } else {
                                if (IsNonDigit(str, 0, "A")) {
                                    addError(str, "Invalid number format");
                                    heighlight(str);
                                    return false;
                                }
                                newstr = str[0] + str[0] + str[0] + str[0];
                                nums[i] = newstr;
                            }
                        } else if (str.includes("B")) {
                            ind = str.indexOf("B");
                            if (ind == 0) {
                                if (IsNonDigit(str, 1, "B")) {
                                    addError(str, "Invalid B pattern");
                                    heighlight(str);
                                    return false;
                                }
                                newstr = str[1] + str[1] + str[1];
                                nums[i] = newstr;
                            } else {
                                if (IsNonDigit(str, 0, "B")) {
                                    addError(str, "Invalid B pattern");
                                    heighlight(str);
                                    return false;
                                }
                                newstr = str[0] + str[0] + str[0];
                                nums[i] = newstr;
                            }
                        } else {
                            // must be numeric
                            if (!/^\d+$/.test(str)) {
                                addError(str, "Non-numeric value");
                                heighlight(str);
                                return false;
                            }

                            // allow only 1 or 2 digit numbers
                            if (str.length > 2) {
                                addError(str, "Number must be 1 or 2 digits");
                                heighlight(str);
                                return false;
                            }
                        }


                    } // end if str.length > 2 || str.includes("A") || str.includes("B")
                    else {
                        // Check in number is any char or other Value
                        for (var j = 0; j < str.length; j = j + 1) {
                            var ascode = str.charCodeAt(j);
                            if (ascode < 48 || ascode > 57) {
                                alert(str + " is Not A valid Number");
                                heighlight(str); //window.location = '/Entry-page.php?param='+window.value;
                                return false;
                            }
                        }
                    }
                    //alert(i); return false;
                    //----------------------------- adding number and amount in diffretn list
                    if (!VALIDATION_ONLY) {
                        gnumamtlist = gnumamtlist + nums[i] + "( " + numberList1[1] + " ) || ";
                        gfinalamount += parseInt(numberList1[1]);
                    }

                    if (i == nums.length - 1) {
                        amtlist = amtlist + numberList1[1];
                        finalnumlist = finalnumlist + nums[i];

                        if (!VALIDATION_ONLY) {
                            var newRow = document.getElementById('addtrn').insertRow();
                            newRow.innerHTML =
                                '<tr><th><input type="text" pattern="[0-9]*"  class="form-control" name="trn_number[]" maxlength="2" value="' +
                                nums[i] +
                                '" placeholder="Number"  onkeyup="checkInput(this)"></th> <th><input type="text" onkeyup="checkInput(this)"  class="form-control" value="' +
                                numberList1[1] +
                                '" name="trn_amount[]" pattern="[0-9]*"   placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent()"> X </span></td></tr>';
                        }

                    } else {
                        amtlist = amtlist + numberList1[1] + ",";
                        finalnumlist = finalnumlist + nums[i] + ",";

                        if (!VALIDATION_ONLY) {
                            var newRow = document.getElementById('addtrn').insertRow();
                            newRow.innerHTML =
                                '<tr><th><input type="text" pattern="[0-9]*"   class="form-control" name="trn_number[]" maxlength="2" value="' +
                                nums[i] +
                                '" placeholder="Number"  onkeyup="checkInput(this)"></th> <th><input type="text" class="form-control" value="' +
                                numberList1[1] +
                                '" name="trn_amount[]" pattern="[0-9]*" onkeyup="checkInput(this)"   placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent().remove();"> X </span></td></tr>';
                        }

                    }





                } // end of if str.trim() != ""

            } // End of Loop var i = 0 ; i < nums.length; i = i + 1





            //  document.getElementById("TextBox1").value="";






            /*if((nums.length)!='1'){
            var newRow = document.getElementById('addtrn').insertRow();
            newRow.innerHTML =
                '<tr><th><input type="text" pattern="[0-9]*"   class="form-control" name="trn_number[]" maxlength="2" value="' +
               finalnumlist +
                '" placeholder="Number"  onkeyup="checkInput(this)"></th> <th><input type="text" class="form-control" value="' +
             amtlist +
                '" name="trn_amount[]" pattern="[0-9]*" onkeyup="checkInput(this)"   placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent()"> X </span></td></tr>';
			}*/

            return true;
        }

        function keypresshandler(event) {
            var charCode = event.keyCode;
            //Non-numeric character range
            if (charCode > 31 && (charCode < 48 || charCode > 57))
                return false;
        }

        //--------------------------------------------------from to Amount

        function IscorrectDigit(str) {
            for (var j = 0; j < str.length; j = j + 1) {
                var ascode = str.charCodeAt(j);
                if (ascode < 48 || ascode > 57) {
                    return true;
                }
            }
            return false;
        }

        function MyAkhar() {
            document.getElementById('addtrn').innerHTML = "";

            var fr = document.getElementById("frmtxt").value;
            var to = document.getElementById("totxt").value;
            var amt = document.getElementById("amttxt").value;

            if (to == "00") {
                to = "100";
            }

            if (IscorrectDigit(fr)) {
                alert(fr + "From number is not Valid");
                return;
            }
            if (IscorrectDigit(to)) {
                alert(to + "To number is not Valid");
                return;
            }
            if (IscorrectDigit(amt)) {
                alert(amt + "amount is not Valid");
                return;
            }
            fr = parseInt(fr);
            to = parseInt(to);

            if (fr > to) {
                alert("From Number is Greater Then To Number");
                return;
            }
            var fromtonumberlist = "";
            var amountlist = ""
            for (var j = fr; j <= to; j = j + 1) {
                if (j != 100) {
                    fromtonumberlist = fromtonumberlist + j + ",";
                    amountlist = amountlist + amt + ",";
                    gnumamtlist = gnumamtlist + j + "( " + amt + " ) || ";
                    gfinalamount += parseInt(amt);
                    var newRow = document.getElementById('addtrn').insertRow();
                    newRow.innerHTML =
                        '<tr><th><input type="number" pattern="[0-9]*" class="form-control" name="trn_number[]" maxlength="2" value="' +
                        j +
                        '" placeholder="Number" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" onkeyup="checkInput(this)"></th> <th><input type="number" class="form-control" value="' +
                        amt +
                        '" pattern="[0-9]*" onkeyup="checkInput(this)" name="trn_amount[]" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent()"> X </span></td></tr>';

                } else {
                    fromtonumberlist = fromtonumberlist + "00" + ",";
                    amountlist = amountlist + amt + ",";
                    gnumamtlist = gnumamtlist + "00" + "( " + amt + " ) || ";
                    gfinalamount += parseInt(amt);
                    var newRow = document.getElementById('addtrn').insertRow();
                    newRow.innerHTML =
                        '<tr><th><input type="number" pattern="[0-9]*" class="form-control" name="trn_number[]" maxlength="2" value="' +
                        "00" +
                        '" placeholder="Number" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" onkeyup="checkInput(this)"></th> <th><input type="number" class="form-control" value="' +
                        amt +
                        '" pattern="[0-9]*" onkeyup="checkInput(this)" name="trn_amount[]" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent()"> X </span></td></tr>';

                }


            }

            document.getElementById("frmtxt").value = "";
            document.getElementById("totxt").value = "";
            document.getElementById("amttxt").value = "";

            var newRow = document.getElementById('addtrn').insertRow();
            newRow.innerHTML =
                '<tr><th><input type="number" pattern="[0-9]*" class="form-control" name="trn_number[]" maxlength="2" value="' +
                fromtonumberlist +
                '" placeholder="Number" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" onkeyup="checkInput(this)"></th> <th><input type="number" class="form-control" value="' +
                amountlist +
                '" pattern="[0-9]*" name="trn_amount[]" onkeyup="checkInput(this)" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" placeholder="Amount" ></th></tr>';
            //<!--  <td><span class="delete" onClick="$(this).parent().parent().remove();"> X </span></td> -->
        }
        //------------------------------------------------end of from To


        //--------------------------------------------------Cross Number
        function Mycros() {
            document.getElementById('addtrn').innerHTML = "";
            var crnum1 = document.getElementById("crnum1").value;
            var crnum2 = document.getElementById("crnum2").value;
            var amt = document.getElementById("cramt").value;


            if (IscorrectDigit(crnum1)) {
                alert(crnum1 + "Cross number 1 is not Valid");
                return;
            }
            if (IscorrectDigit(crnum2)) {
                alert(crnum2 + "Cross number 2 is not Valid");
                return;
            }
            if (IscorrectDigit(amt)) {
                alert(amt + "amount is not Valid");
                return;
            }
            var crnumlist = "";
            var cramtlist = "";
            var joda = document.getElementById("joda").value;
            //  alert(joda);
            for (var i = 0; i < crnum1.length; i = i + 1) {
                for (var j = 0; j < crnum2.length; j = j + 1) {


                    if (joda == "Yes") {
                        crnumlist = crnumlist + crnum1[i] + crnum2[j] + ",";
                        cramtlist = cramtlist + amt + ",";
                        gnumamtlist = gnumamtlist + crnum1[i] + crnum2[j] + "( " + amt + " ) || ";
                        gfinalamount += parseInt(amt);
                        var newRow = document.getElementById('addtrn').insertRow();
                        newRow.innerHTML =
                            '<tr><th><input type="text" pattern="[0-9]*" class="form-control" name="trn_number[]" maxlength="2" value="' +
                            crnum1[i] + crnum2[j] +
                            '" placeholder="Number" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" onkeyup="checkInput(this)"></th> <th><input type="text" class="form-control" value="' +
                            amt +
                            '" name="trn_amount[]" onkeyup="checkInput(this)" pattern="[0-9]*" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent().remove();"> X </span></td></tr>';

                    } else {
                        //            alert(joda); alert(crnum1[i]); alert(crnum2[j]);
                        if (crnum1[i] != crnum2[j]) {
                            crnumlist = crnumlist + crnum1[i] + crnum2[j] + ",";
                            cramtlist = cramtlist + amt + ",";
                            gnumamtlist = gnumamtlist + crnum1[i] + crnum2[j] + "( " + amt + " ) || ";
                            gfinalamount += parseInt(amt);

                            var newRow = document.getElementById('addtrn').insertRow();
                            newRow.innerHTML =
                                '<tr><th><input type="text" pattern="[0-9]*" class="form-control" name="trn_number[]" maxlength="2" value="' +
                                crnum1[i] + crnum2[j] +
                                '" placeholder="Number" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" onkeyup="checkInput(this)"></th> <th><input type="text" class="form-control" value="' +
                                amt +
                                '" name="trn_amount[]" onkeyup="checkInput(this)" pattern="[0-9]*" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent().remove();"> X </span></td></tr>';

                        }

                    }
                }
            }
            document.getElementById("crnum1").value = "";
            document.getElementById("crnum2").value = "";
            document.getElementById("cramt").value = "";

            //var newRow = document.getElementById('addtrn').insertRow();
            //newRow.innerHTML =
            //   '<tr><th><input type="text" pattern="[0-9]*" class="form-control" name="trn_number[]" maxlength="2" value="' +
            // crnumlist +
            // '" placeholder="Number" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" onkeyup="checkInput(this)"></th> <th><input type="text" class="form-control" value="' +
            //cramtlist +
            //  '" name="trn_amount[]" pattern="[0-9]*" onkeyup="checkInput(this)" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent()"> X </span></td></tr>';

            //crnum1
        }

        //---------------------------------------------------End Cros

        function ShowAkharParten(x) {
            //alert("Show");
            if (x == 1) {
                document.getElementById('numsms').style.display = "";
                document.getElementById('hidebtn').style.display = "";
                document.getElementById('showbtn').style.display = "none";
            }
            if (x == 2) {
                document.getElementById('fnumsms').style.display = "";
                document.getElementById('fhidebtn').style.display = "";
                document.getElementById('fshowbtn').style.display = "none";
            }
            if (x == 3) {
                document.getElementById('cnumsms').style.display = "";
                document.getElementById('chidebtn').style.display = "";
                document.getElementById('cshowbtn').style.display = "none";
            }
        }

        function HideAkharParten(x) {
            //alert("hide");
            if (x == 1) {
                document.getElementById('numsms').style.display = "none";
                document.getElementById('showbtn').style.display = "";
                document.getElementById('hidebtn').style.display = "none";
            }
            if (x == 2) {
                document.getElementById('fnumsms').style.display = "none";
                document.getElementById('fshowbtn').style.display = "";
                document.getElementById('fhidebtn').style.display = "none";
            }
            if (x == 3) {
                document.getElementById('cnumsms').style.display = "none";
                document.getElementById('cshowbtn').style.display = "";
                document.getElementById('chidebtn').style.display = "none";
            }

        }

        function viewtable() {
            document.getElementById('view').style.display = "";
            document.getElementById('bnthide').style.display = "";
            document.getElementById('bntview').style.display = "none";
            //document.getElementById('finalresult').style.display = "";

            //alert(gnumamtlist);
            //alert(gfinalamount);
        }

        function hidetable() {
            document.getElementById('view').style.display = "none";
            document.getElementById('bnthide').style.display = "none";
            document.getElementById('bntview').style.display = "";



        }

        function totalviewtable() {
            //    document.getElementById('totalview').style.display = "";
            //   document.getElementById('bnttotalhide').style.display = "";
            //    document.getElementById('bnttotalview').style.display = "none";
            document.getElementById('totalview').style.display = "gnumamtlist";

            alert(gnumamtlist + " FINAL AMOUNT :-" + gfinalamount);

        }

        function totalhidetable() {
            document.getElementById('totalview').style.display = "none";
            document.getElementById('bnttotalhide').style.display = "none";
            document.getElementById('bnttotalview').style.display = "";


            //addtrn
        }
    </script>
    <!-- BILINGUAL NOTICE CSS -->
    <style>
        .notice-banner {
            width: 100%;
            position: relative;
            /* relative so it sits inline above coin balance */
            z-index: 1100;
            background: linear-gradient(90deg, #ffefef, #fffaf0);
            border: 1px solid rgba(0, 0, 0, 0.06);
            padding: 10px 12px;
            box-sizing: border-box;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .notice-text {
            flex: 1;
            line-height: 1.3;
            color: #222;
        }

        .notice-lang {
            display: none;
        }

        .notice-lang.active {
            display: block;
        }

        .notice-controls {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .notice-btn {
            border: 1px solid rgba(0, 0, 0, 0.08);
            background: #fff;
            padding: 6px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        .notice-btn.active {
            background: #c43140;
            color: #fff;
        }

        .notice-close {
            background: transparent;
            border: none;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            padding: 2px 6px;
        }

        @media(max-width:767px) {
            .notice-banner {
                font-size: 13px;
                padding: 10px;
            }
        }
    </style>
    <style>
        /* Header responsive */
        .card-header {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-header h4 {
            flex: 1 1 auto;
            font-size: 14px;
            margin: 5px 0;
        }

        /* Form responsiveness */
        .form-group {
            width: 100%;
        }

        /* Buttons block fix */
        .check-btn {
            flex-wrap: wrap;
            gap: 10px;
        }

        .check-btn .col,
        .check-btn .col-6 {
            flex: 1 1 100%;
        }

        /* Textarea sizing */
        @media(max-width:768px) {
            textarea.form-control {
                height: 160px !important;
                font-size: 14px;
            }
        }

        /* Tables responsive */
        table {
            width: 100% !important;
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }

        /* Notice banner improvements */
        .notice-banner {
            flex-wrap: wrap;
        }

        .notice-controls {
            width: 100%;
            justify-content: flex-start;
        }

        /* Keep Done + Submit on one line, but with spacing */
        /* FINAL FIX FOR ALL DEVICES INCLUDING DESKTOP MODE */
        .check-btn {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .check-btn>div {
            flex: 1 1 auto !important;
        }

        /* Force a guaranteed gap between buttons everywhere */
        .check-btn>div:first-child {
            margin-right: 12px !important;
        }

        .check-btn a,
        .check-btn input {
            width: 100%;
        }
    </style>

</head>

<body class="background-image-body">
    <div class="loader"></div>

    <?php
    date_default_timezone_set('Asia/Kolkata');
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['submitpost'])) {

            //print_r($_SESSION);
            $Shift = $_POST['shift'];
            // echo $Shift ;
            $Party = $_POST['party'];
            //echo $Party ;
            $new_date = date('Y-m-d', strtotime($_POST['dateoftrn']));
            //echo $_SESSION['login'];

            $servername = "localhost";
            $username = "555prouser";
            $password = "e2OFVjrRK77ljyfs4z@R";
            $dbname = "555prodb";
            $conn = new mysqli($servername, $username, $password, $dbname);
            if (!$conn) {
                die("Connection failed!" . mysqli_connect_error());
            }

            $sql = "INSERT INTO 'tbl_master_transaction'('shift_id', 'party_id', 'created_by', 't_date', 'total_number_amount', 'total_akhar_amount', 'status') VALUES ('shiftid','partyid','createdby','TRDate','TAmount','0.0','1')";;
        }
    }
    ?>

    <?php //echo 'ttp'; 
    //var_dump($_GET['status']);
    if (isset($_GET['status']) && ($_GET['status'] == '1')) {
        echo '<script>alert("transaction recorded successfully");</script>';
    }
    if (isset($_GET['status']) && $_GET['status'] == '0') {
        echo '<script>alert("Invalid Entry. Please try again!!");</script>';
    }
    if (isset($_GET['parentid'])) {
        $home = 'https://new.555xch.pro/appdemo/Parent.php?parentid=' . $_GET["parentid"] . '&name=' . $_GET["name"];
    } else {
        $home = '#';
    }

    ?>
    <?php                       // 155
    $start_date = date('Y-m-01', strtotime('-1 month'));           // 1st of current month
    $end_date    = date('Y-m-d');                 // today's date
    ?>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-8 offset-xl-2">
                        <div class="card card-auth">
                            <div class="card-header card-header-auth-1">
                                <h4 class="text-white"><a href="<?= $home ?>" style="color:white"><i class="fas fa-home" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Home</a></h4>
                                <h4 class="text-white"><a onclick="showentry()" style="color:white"><i class="fas fa-pen" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i>Entry</a></h4> ,
                                <h4 class="text-white"><a onclick="showfromto()" style="color:white"><i class="fas fa-pen" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i>From To</a></h4> ,
                                <h4 class="text-white"><a onclick="showcross()" style="color:white"><i class="fas fa-pen" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i>Cross</a></h4> ,
                                <!--<h4 class="text-white"><a onclick="showfromto()" href="#numamnt"style="color:white"><i class="fas fa-pen" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i>Number Amount</a></h4> , -->
                                <h4 class="text-white"><a href="Date-shift.php<?= $view ?>" style="color:white"><i class="fas fa-book" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> View Transaction</a></h4>
                                <h4 class="text-white"><a href="hisablist.php<?= $view ?>" style="color:white"><i class="fas fa-book" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Hisab</a></h4>
                                <h4 class="text-white"> <a href="statement.php/<?= $_GET['login'] ?>?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" style="color:white">
                                        <i class="fas fa-book" style="color:white !important" id="showbtn"></i> Statement
                                    </a></h4>
                                <h4 class="text-white"><a href="https://new.555xch.pro/appdemo/Login.html" style="color:white"><i class="fas fa-book" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Logout</a></h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- BILINGUAL NOTICE (place immediately above Coin Balance echo) -->
                                    <!-- <div id="bilingual-notice" class="notice-banner" role="region" aria-live="polite" aria-label="Site notice">
  <div class="notice-text">
    <div id="notice-en" class="notice-lang">
      <strong>Notice:</strong> 20/10 se gaziyabad, Gali or Disawer ka time change ho jayega 

Gaziyabad.........8.40 pm
Gali..................10.40 pm
Disawer..............4.10 Am
    </div>
    <div id="notice-hi" class="notice-lang active">
      <strong>सूचना:</strong> 20/10 से गाजियाबाद, गली या दिसावेर का समय बदल जाएगा 

गाजियाबाद.........रात 8.40 बजे
गली.................रात 10.40 बजे
दिसावेर...........4.10 पूर्वाह्न
    </div>
  </div>

  <div class="notice-controls">
    <button type="button" id="toggle-en" class="notice-btn" aria-pressed="true">EN</button>
    <button type="button" id="toggle-hi" class="notice-btn active" aria-pressed="false">HI</button>
    <button type="button" id="dismiss-notice" class="notice-close" aria-label="Dismiss notice">&times;</button>
  </div>
</div> -->
                                    <div class="form-group col-6">


                                        <?php
                                        // Database connection (update with your credentials)
                                        $servername = "localhost";
                                        $username = "555prouser";
                                        $password = "e2OFVjrRK77ljyfs4z@R";
                                        $database = "555prodb";

                                        $conn = new mysqli($servername, $username, $password, $database);

                                        // Check connection
                                        if ($conn->connect_error) {
                                            die("Connection failed: " . $conn->connect_error);
                                        }

                                        // Replace $user_id with the actual user ID
                                        $user_id = 1;

                                        // Write the SQL query
                                        //$sql = "SELECT coin_balance FROM tbl_ledger WHERE id = ".$_GET['login']." LIMIT 1";
                                        //$sqlamt = "SELECT opening_coin_bal as coin_balance, final_hisab as amount FROM tbl_final_hisab WHERE ledger_id = ".$_GET['login']." ORDER BY `tbl_final_hisab`.`id` DESC LIMIT 1";
                                        // $sqlamt = "SELECT sum(today_hisab) as amount  FROM `tbl_final_hisab` WHERE `ledger_id` = ".$_GET['login']." AND STR_TO_DATE(date, '%d-%m-%Y') BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01')
                                        //                                                     AND LAST_DAY(CURDATE());"; 
                                        // $sql = "SELECT sum(amount) as coin_balance FROM `coin_transactions` WHERE `receiver_id` = ".$_GET['login'];
                                        //  //$sql2 = "SELECT amount FROM `coin_transactions` WHERE `receiver_id` = ".$_GET['login'];
                                        // $sqlamt1 = "SELECT sum(amount) as amount FROM coin_transactions WHERE sender_id = ".$_GET['login']." AND status=1 AND created_at >= CONCAT(CURDATE(), ' 06:00:00')";
                                        // // Execute the query
                                        // $result = $conn->query($sql);
                                        // $reciveramt = $conn->query($sqlamt1);
                                        // $amtresult = $conn->query($sqlamt);
                                        // $amount = 0; // Default amount

                                        // if ($amtresult->num_rows > 0) {
                                        //     // $row = $amtresult->fetch_assoc();
                                        //     // $amount = $row['amount'];
                                        //     while ($row = $amtresult->fetch_assoc()) {

                                        //         $amount += $row['amount']; // Summing up all amounts 
                                        //     }
                                        // }
                                        // //echo $amount; die;

                                        // // Fetch the coin_balance into a variable
                                        // if ($result && $result->num_rows > 0) {
                                        //     $row = $result->fetch_assoc();
                                        //     $receiverow = $reciveramt->fetch_assoc();
                                        //    // echo '<pre>'; print_r($receiverow); echo $amount;  echo '</pre>'; 
                                        //     $coin_balance = $row['coin_balance'];
                                        //     $receive_bal = $receiverow['amount'];
                                        //     //$amnt = $row['amount'];
                                        // //     if($_GET['login'] == '382'){
                                        // //     echo $coin_balance.' '.$amount.' '.$receive_bal;
                                        // // }
                                        //     echo "<h5>Coin Balance: <span style='color:red'>" . ($coin_balance-$amount-$receive_bal)."</span></h5>";
                                        // } else {
                                        //     echo "No record found or invalid user ID.";
                                        // }

                                        // Close the connection

                                        $ledger_id = (int) $_GET['login']; // make sure it's integer-safe

                                        // Date range: start = 1st of this month at 12:00 PM, end = tomorrow 6:00 AM
                                        $start_datetime = '2025-08-01';
                                        $end_datetime = date('Y-m-d 06:00:00', strtotime('+1 day'));

                                        // 1. Fetch coin_transactions affecting the ledger
                                        $sql = "
    SELECT amount, sender_id, receiver_id, status, type, created_at
    FROM coin_transactions
    WHERE (
        receiver_id = $ledger_id
        OR (
            sender_id = $ledger_id
            AND type = 'spend'
        )
    )
    AND created_at >= '$start_datetime'
    AND created_at < '$end_datetime'
";
                                        $result = $conn->query($sql);
                                        if ($ledger_id == '157') {
                                            //echo $sql; die;
                                        }
                                        $balance = 0;

                                        if ($result && $result->num_rows > 0) {
                                            while ($tx = $result->fetch_assoc()) {
                                                if ($tx['receiver_id'] == $ledger_id) {
                                                    $balance += (float) $tx['amount'];
                                                } elseif ($tx['sender_id'] == $ledger_id && $tx['status'] == 1) {
                                                    $balance -= (float) $tx['amount'];
                                                }
                                            }
                                        }

                                        // 2. Fetch P/L from tbl_final_hisab
                                        $sql2 = "
    SELECT date, today_hisab AS final_hisab
    FROM tbl_final_hisab
    WHERE ledger_id = $ledger_id
    AND STR_TO_DATE(date, '%d-%m-%Y') >= '" . date('Y-m-d', strtotime($start_datetime)) . "'
    AND STR_TO_DATE(date, '%d-%m-%Y') < '" . date('Y-m-d', strtotime($end_datetime)) . "'
";
                                        $res2 = $conn->query($sql2);

                                        if ($res2 && $res2->num_rows > 0) {
                                            while ($row = $res2->fetch_assoc()) {
                                                $pl = (float) $row['final_hisab'];
                                                if ($pl < 0) {
                                                    $balance += abs($pl); // Loss: add back to balance
                                                } else {
                                                    $balance -= $pl; // Profit: subtract from balance
                                                }
                                            }
                                        }
                                        $sql3 = "
    SELECT SUM(amount) AS deduct_amount
    FROM coin_transactions
    WHERE shift_id IS NOT NULL
      AND deposite_byto_master = 0
      AND type = 'allocation'
      AND status = 1
      AND sender_id = $ledger_id
      AND created_at >= '$start_datetime'
      AND created_at < '$end_datetime'
";
                                        $res3 = $conn->query($sql3);

                                        if ($res3) {
                                            $row3 = $res3->fetch_assoc();
                                            $deduct_amount = (float) ($row3['deduct_amount'] ?? 0);
                                            $balance -= $deduct_amount;
                                        }
                                        if ($ledger_id == 157) {
                                            // echo "Balance: $balance<br>";
                                            // echo "Deducted Special Allocation: $deduct_amount<br>";
                                            //  echo $sql3; // if you want to debug the query
                                        }
                                        // Output the final balance
                                        echo "<h5>Coin Balance: <span style='color:red'>" . number_format($balance, 2) . "</span></h5>";

                                        $conn->close();
                                        ?>

                                    </div>
                                </div>
                                <form id="demo-form2" action="https://new.555xch.pro/tbl_transactions/add_transaction_final_app" method="POST" data-parsley-validate
                                    class="form-horizontal form-label-left" onsubmit="checkFields(event)">

                                    <!-- Select  Date -->
                                    <div class="row"> <!-- Select PArty -->
                                        <div class="form-group col-6">
                                            <?php //echo '<pre>'; print_r($party); echo '</pre>'; 
                                            ?>
                                            <h4 style="margin-right:10px;"><b>Party</b></h4>
                                            <div class="btn-group" style="height: 36px; margin-right: 10px;width:100%">
                                                <select name="party" id="party" class="form-control" required>
                                                    <option value="0" disabled>Choose Party</option>
                                                    <?php
                                                    $servername = "localhost";
                                                    $username = "555prouser";
                                                    $password = "e2OFVjrRK77ljyfs4z@R";
                                                    $dbname = "555prodb";
                                                    // Create connection
                                                    $mysqli = new mysqli($servername, $username, $password, $dbname);

                                                    if ($mysqli->connect_errno) {
                                                        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                                                        exit();
                                                    }
                                                    //echo '<pre>'; print_r($_SESSION); echo '</pre>';
                                                    $sql = "select * from tbl_ledger where Status = 1  ORDER BY ledger_name ASC";
                                                    //echo $sql;
                                                    $result = $mysqli->query($sql);

                                                    // Numeric array
                                                    //$roww = $result -> fetch_array(MYSQLI_NUM);
                                                    //echo '<pre>'; echo 'ttpt'; print_r($roww); echo '</pre>'; die;
                                                    //die;
                                                    $datacount = 0;
                                                    while ($row = mysqli_fetch_array($result)) {  //echo '<pre>'; print_r($_SESSION); echo '</pre>'; die;
                                                        if (($_GET["user_type"] == 'ledger') && ($_GET["login"] == $row['id'])) {
                                                            echo '<option value=' . $row['id'] . ' selected>' . $row['ledger_name'] . '</option>';
                                                            //echo '<option value="$row['id']">$row['shift_name']</option>';
                                                        } else if (($_GET["user_type"] == 'admin')) {
                                                            echo '<option value=' . $row['id'] . '>' . $row['ledger_name'] . '</option>';
                                                        }
                                                    }
                                                    /**/
                                                    ?>
                                                </select>


                                            </div>
                                        </div>
                                        <!--Select Party end -->
                                        <div class="form-group col-6">
                                            <input type="hidden" name="userid" value="<?php echo $_GET['login']; ?>" />
                                            <input type="hidden" name="entryval" value="<?php echo $entry; ?>" />
                                            <input type="hidden" name="updated_by" value="<?php echo $_SESSION['updated_by']; ?>" />
                                            <h4 style="margin-right:10px;"><b>Date</b></h4>
                                            <input id="birthday" name="dateoftrnforapponly" class=" form-control" type="input" value="<?php echo date('d-m-Y', strtotime('-7 hours')) ?>" style="pointer-events:none"
                                                autocomplete="off" readonly>
                                            <input id="birthday" name="dateoftrn" class=" form-control" type="hidden" value="<?php echo date('Y-m-d', strtotime('-7 hours')) ?>" style="pointer-events:none"
                                                autocomplete="off" readonly>
                                        </div>



                                        <!-- Select  Date -->
                                        <!--Select Shift Code -->

                                        <div class="form-group col">

                                            <h4 style="margin-right:10px;"><b>Shift</b></h4>
                                            <!-- Split button -->
                                            <div class="btn-group" style="height: 36px; margin-right: 10px;;width:100%">

                                                <select name="shift" id="shift" onchange="gettime()" class="form-control"
                                                    required>
                                                    <?php

                                                    $ttime = time();
                                                    //die;
                                                    //$url = "https://555xch.pro/tbl_shift/getallshifts";
                                                    //$parts = parse_url($url);
                                                    //$output = [];
                                                    //parse_str($parts['query'], $output);
                                                    //echo $output['Array']; // 12
                                                    $servername = "localhost";
                                                    $username = "555prouser";
                                                    $password = "e2OFVjrRK77ljyfs4z@R";
                                                    $dbname = "555prodb";
                                                    // Create connection
                                                    $mysqli = new mysqli($servername, $username, $password, $dbname);
                                                    $newTimestamp = time() + (12 * 60 * 60); // Add 12 hours to the current time (12 hours * 60 minutes * 60 seconds)
                                                    $todate = date('Y-m-d', $newTimestamp);
                                                    $fromdate = date('Y-m-d', time());
                                                    //$sql = "SELECT `tbl_shift`.*, `tbl_user_login`.`user_name` FROM `tbl_shift` JOIN `tbl_user_login` ON `tbl_user_login`.`id` = `tbl_shift`.`updated_by` order by `tbl_shift`.orderby ";
                                                    $sql = "SELECT `tbl_shift`.`id` AS `tbl_shift_id`, `tbl_shift`.*, `user_shift_timings`.`id` AS `user_shift_timing_id`, `user_shift_timings`.*, `tbl_shift`.`open_date` AS `tbl_shift_open_date`, 
                                    `user_shift_timings`.`open_date` AS `user_shift_open_date` FROM `user_shift_timings` 
                                    LEFT JOIN `tbl_shift` ON `user_shift_timings`.`shift_id` = `tbl_shift`.`id` 
                                    WHERE `user_shift_timings`.`updated_by` = '" . $_SESSION['updated_by'] . "' 
                                    AND `user_shift_timings`.`open_date` >= '" . $fromdate . "' AND `user_shift_timings`.`open_date` <= '" . $todate . "' ORDER BY `user_shift_timings`.`open_date` ASC, `user_shift_timings`.`master` ASC";
                                                    //echo $sql; die;
                                                    //$sql = "select * from tbl_shift ORDER BY shift_name ASC";
                                                    $rs = $mysqli->query($sql);
                                                    $datacount = 0;
                                                    //echo 'ttpt'; print_r($_SESSION['user_type']); die;
                                                    while ($row = mysqli_fetch_array($rs)) {
                                                        if ($_GET['user_type'] == 'admin') {
                                                            //echo $val['open_date'].' '.date("H:i", strtotime($val['super_admin'])); //die;
                                                            $time = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date("H:i", strtotime($row['super_admin'])));
                                                        }
                                                        if ($_GET['user_type'] == 'ledger') {
                                                            $time = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date("H:i", strtotime($row['app_time'])));
                                                        }
                                                        if (($ttime < $time)) {

                                                            echo '<option value=' . $row['id'] . ' data-foo="' . date("h:i A", strtotime($row['app_time'])) . '">' . $row['shift_name'] . '</option>';
                                                        }
                                                        //echo '<option value='.$row['id'].'>'.$row['shift_name'].'</option>';
                                                    }
                                                    ?>

                                                </select>
                                                <div id="alert" class="alert blink" style="color:red; font-size:10px"></div>
                                                <div class="alert" style="display:none">Please Select Shift First</div>
                                            </div>

                                        </div>
                                        <!--selctshift code end -->









                                        <!-- <div class="row">-->



                                        <div class="form-group col-12" id="numak">
                                            <label for="frist_name">
                                                Num-Akhar
                                                <i class="fas fa-eye" style="color:black !important" title="" name="ShowkaharIcon" id="showbtn" onclick="ShowAkharParten(1)"></i>
                                                <i class="fas fa-eye-slash" style="color:black !important; display:none" title="" name="HidekaharIcon" id="hidebtn" onclick="HideAkharParten(1)"></i>
                                            </label>
                                            <textarea name="TextBox1" id="TextBox1" class="form-control required " rows="5" onchange="CheckAkhar()"></textarea><br>
                                            <div id="errorList" style="margin-top:10px;"></div>
                                            <textarea id="D_data" name="D_data" class="form-control required " rows="5" onchange="FillData()"></textarea>
                                            <span id="s_data" name="s_data"></span>
                                            <div class="form-group col-12 d-flex check-btn">
                                                <div class="col"><a href="#" onclick="Mycode()" class=" btn btn-auth-color btn-lg btn-flex">Done</a></div>
                                                <div class="col-6">
                                                    <input type="submit" id="submitbtn" disabled name="submitpost" onclick="this.disabled=true;this.value=&#39;Sending, please wait...&#39;;this.form.submit();" style="background-color:#c43140;color:white;" class="btn btn-auth-color btn-lg btn-flex">

                                                </div>
                                            </div>
                                        </div>

                                        <span name="numsms" id="numsms" style="display: none; color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; ">

                                            <table style="border:solid 1px black" class="table table-responsive">

                                                <tr>
                                                    <td>
                                                        रकम डालने के लिए ,into , in , = ,(),*, का प्रयोग करें <br />उदाहरन के लिए

                                                        <br />
                                                        <ul>
                                                            <li>11, 22, 33, 44, 55(100)</li>
                                                            <!--<li>11, 22, 33, 44, 55 into 100</li>
                                                            <li>11, 22, 33, 44, 55 =100</li>
                                                            <li>11, 22, 33, 44, 55 in 100</li>-->
                                                            <li>11, 22, 33, 44, 55 * 100</li>
                                                        </ul>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        आखर डालने के लिए उदाहरन

                                                        <br />
                                                        <ul>
                                                            <li>A1 ,1A ,1111 ,111 ,B1 ,1B(100)</li>

                                                        </ul>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        आखर हां नंबर एक साथ एंटर कर सकते हैं। <br />उदाहरन के लिए
                                                        <ul>
                                                            <li>11, 12, 13, A1, 2A, B4, 5B, 9999 = 100</li>

                                                        </ul>


                                                    </td>
                                                </tr>





                                            </table>


                                        </span>

                                        <!-------------------------From to                               =================================-->
                                        <div class="form-group col-12" id="fromto" style="display:none">
                                            <h6>
                                                From-To
                                                <i class="fas fa-eye" style="color:black !important" title="" name="ShowkaharIcon" id="fshowbtn" onclick="ShowAkharParten(2)"></i>
                                                <i class="fas fa-eye-slash" style="color:black !important; display:none" title="" name="HidekaharIcon" id="fhidebtn" onclick="HideAkharParten(2)"></i>
                                            </h6>

                                            <div class="form-group col-12">
                                                <label for="email">From</label>
                                                <input name="frmtxt" id="frmtxt" maxlength='2' pattern="[0-9]*" onkeyup="checkInput(this)" type="text" class="form-control" />
                                            </div>
                                            <div class="form-group col-12">
                                                <label for="email">To</label>
                                                <input name="totxt" id="totxt" maxlength='2' pattern="[0-9]*" onkeyup="checkInput(this)" type="text" class="form-control" />
                                            </div>
                                            <div class="form-group col-12">
                                                <label for="email">Amount</label>
                                                <input name="amttxt" id="amttxt" pattern="[0-9]*" onkeyup="checkInput(this)" type="text" class="form-control" />
                                            </div>
                                            <div class="form-group col-12 d-flex check-btn">
                                                <div class="col"><a href="#" onclick="MyAkhar()" class="btn btn-auth-color btn-lg btn-flex">Done</a></div>
                                                <div class="col-6">
                                                    <input type="submit" name="submitpost" onclick="this.disabled=true;this.value='Sending, please wait...';this.form.submit();" style="background-color:#c43140;color:white;" class="btn btn-auth-color btn-lg btn-flex" onclick="" />

                                                </div>
                                            </div>
                                        </div>
                                        <span name="numsms" id="fnumsms" style="display: none; color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; ">

                                            <table style="border:solid 1px black" class="table table-responsive">
                                                <thead>
                                                    <tr>
                                                        <th style="border:solid 1px black"> From - TO instruction </th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td style="border:solid 1px black">Form का नंबर To से कम होना चाहिएr</td>

                                                    </tr>
                                                    <tr>

                                                        <td style="border:solid 1px black">केवल अंक का प्रयोग करें। कृपया किसी भी अल्फाबेट या स्पाईसेल सिम का प्रयोग न करें</td>

                                                    </tr>
                                                </tbody>
                                            </table>


                                        </span>
                                        <!--------------------------------------------------          CROSS       -->
                                        <br />
                                        <div class="form-group col-12" id="Cross" style="display:none">
                                            <h6>
                                                Cross
                                                <i class="fas fa-eye" style="color:black !important" title="" name="cShowkaharIcon" id="cshowbtn" onclick="ShowAkharParten(3)"></i>
                                                <i class="fas fa-eye-slash" style="color:black !important; display:none" title="" name="cHidekaharIcon" id="chidebtn" onclick="HideAkharParten(3)"></i>
                                            </h6>

                                            <div class="form-group col-12">
                                                <label for="email">Number</label>
                                                <input type="text" onkeyup="checkInput(this)" pattern="[0-9]*" name="crnum1" maxlength="10" id="crnum1" class="form-control" />
                                            </div>
                                            <div class="form-group col-12">
                                                <label for="email">Number</label>
                                                <input type="text" onkeyup="checkInput(this)" pattern="[0-9]*" name="crnum2" maxlength="10" id="crnum2" class="form-control" />
                                            </div>
                                            <div class="form-group col-12">
                                                <label for="email">Amount</label>
                                                <input type="text" onkeyup="checkInput(this)" pattern="[0-9]*" name="cramt" id="cramt" class="form-control" />
                                            </div>
                                            <div class="form-group col-12">
                                                <label for="email">Joda</label>
                                                <select name="joda" id="joda">
                                                    <option value="Yes">yes</option>
                                                    <option value="No">no</option>

                                                </select>
                                            </div>
                                            <div class="form-group col-12 d-flex check-btn">
                                                <div class="col-6"><a href="#" onclick="Mycros()" class="btn btn-auth-color btn-lg btn-flex">Done</a></div>
                                                <div class="col-6">
                                                    <input type="submit" name="submitpost" onclick="this.disabled=true;this.value='Sending, please wait...';this.form.submit();" style="background-color:#c43140;color:white;" class="btn btn-auth-color btn-lg btn-flex" onclick="" />

                                                </div>
                                            </div>
                                        </div>


                                        <div class="row" id="numamnt">

                                            <span id="view" name="view" style=" color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; ">
                                                <table id="addtrn1">
                                                    <thead>
                                                        <!--<tr>
                                                        <th><input type="text" class="form-control" name="trn_number[]" maxlength="2" maxlength="2" placeholder="Number" onfocus="checkInput(this)"></th>
                                                        <th><input type="text" class="form-control" name="trn_amount[]" maxlength="6" placeholder="Amount" onfocus="checkInput(this)"></th>
                                                    </tr>-->
                                                    </thead>
                                                </table>
                                            </span>

                                            <div id="totalview" name="totalview" style="display: none; color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; ">

                                            </div>

                                        </div>
                                        <div class="form-group col-12 d-flex check-btn">



                                            <i class="fa fa-table" style="color:black !important" title="" name="bntview" id="bntview" onclick="viewtable()"></i>
                                            <i class="fa fa-minus-circle" style="color:black !important; display:none" title="" name="bnthide" id="bnthide" onclick="hidetable()"></i>

                                            <i class="fa fa-shower" style="color: black !important; margin-left: 90%;" title="" name="bnttotalview" id="bnttotalview" onclick="totalviewtable()"></i>
                                            <!-- <i class="fa fa-minus-circle" style="color: black !important; margin-left: 90%; display: none" title="" name="bnttotalhide" id="bnttotalhide" onclick="totalhidetable()"></i>-->
                                        </div>




                                        <span name="cnumsms" id="cnumsms" style="display: none; color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; ">

                                            <table style="border:solid 1px black" class="table table-responsive">
                                                <thead>
                                                    <tr>
                                                        <th style="border:solid 1px black"> Cross number instruction </th>

                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <tr>

                                                        <td style="border:solid 1px black">केवल अंक का प्रयोग करें। कृपया किसी भी अल्फाबेट या स्पाईसेल सिम का प्रयोग न करें</td>

                                                    </tr>
                                                </tbody>
                                            </table>


                                        </span>

                                        <!--  <span id="view" name="view" style=" color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; ">
                                        <table id="amittable">
                                            <thead>
                                                <tr>
                                                    <th><input type="text" class="form-control" name="trn_number[]" maxlength="2" maxlength="2" placeholder="Number" onfocus="checkInput(this)"></th>
                                                    <th><input type="text" class="form-control" name="trn_amount[]" maxlength="6" placeholder="Amount" onfocus="checkInput(this)"></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </span>-->
                                        <span id="view" name="view" style=" color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; ">
                                            <table id="addtrn">
                                                <thead>
                                                    <!--<tr>
                                                    <th><input type="text" class="form-control" name="trn_number[]" maxlength="2" maxlength="2" placeholder="Number" onfocus="checkInput(this)"></th>
                                                    <th><input type="text" class="form-control" name="trn_amount[]" maxlength="6" placeholder="Amount" onfocus="checkInput(this)"></th>
                                                </tr>-->
                                                </thead>
                                            </table>
                                        </span>
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
    <script src="assets/js/daterangepicker.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script>
        function showentry() {
            document.getElementById('numak').style.display = "block";
            document.getElementById('fromto').style.display = "none";
            document.getElementById('Cross').style.display = "none";
        }

        function showfromto() {
            document.getElementById('numak').style.display = "none";
            document.getElementById('fromto').style.display = "block";
            document.getElementById('Cross').style.display = "none";
        }

        function showcross() {
            document.getElementById('numak').style.display = "none";
            document.getElementById('fromto').style.display = "none";
            document.getElementById('Cross').style.display = "block";
        }

        function enableshift() {
            var date = new Date();
            var time = date.getHours() + date.getMinutes();
            if (date.getHours() >= '5' && date.getHours() < '16') {
                document.getElementById('shift').value = '16';
            }
            if (date.getHours() >= '16' && date.getHours() < '17') {
                document.getElementById('shift').value = '17';
            }
            if (date.getHours() >= '17' && date.getHours() < '18') {
                document.getElementById('shift').value = '18';
            }
            if (date.getHours() >= '18' && date.getHours() < '21') {
                document.getElementById('shift').value = '12';
            }
            if (date.getHours() >= '21' && date.getHours() < '24') {
                document.getElementById('shift').value = '13';
            }
            if (date.getHours() >= '24' && date.getHours() < '4') {
                document.getElementById('shift').value = '14';
            }
        }
        //enableshift();
    </script>
    <script>
        document.getElementById("submitbtn").addEventListener("click", function() {
            this.style.backgroundColor = "#28a745"; // green
            this.style.borderColor = "#28a745";
            this.style.color = "white";
            this.value = "Sending, please wait...";
            this.disabled = true;
        });
    </script>
    <style>
        /* Blinking effect for the alert message */
        .blink {
            animation: blink-animation 1.5s steps(5, start) infinite;
            color: darkred;
        }

        /* Blink animation */
        @keyframes blink-animation {
            from {
                opacity: 1;
            }

            to {
                opacity: 0.4;
            }
        }

        /* Disabled button style */
        .btn-disabled {
            background-color: grey !important;
            cursor: not-allowed;
        }
    </style>
    <script>
        let countdownInterval;

        function gettime() {
            // Clear any existing countdown interval
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }

            // Enable the button initially
            // const submitBtn = document.getElementById('submitbtn');
            // submitBtn.disabled = false;
            // submitBtn.classList.remove('btn-disabled');

            // Get the selected option
            var sel = document.getElementById('shift');
            var selected = sel.options[sel.selectedIndex];
            var shiftTime = selected.getAttribute('data-foo'); // e.g., "02:55 PM"

            // Parse the shift time (02:55 PM) into a Date object
            var now = new Date(); // Current date and time
            var shiftDate = new Date(now.toDateString() + ' ' + shiftTime); // Combine current date with shift time

            // Start the countdown
            countdownInterval = setInterval(() => {
                // Get the current time
                var now = new Date();

                // Calculate the time difference
                var diff = shiftDate - now;

                // If the countdown is over
                if (diff <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('alert').innerHTML =
                        `<span class="blink">Shift time expired!</span>`;

                    // Disable the submit button
                    submitBtn.disabled = true;
                    submitBtn.classList.add('btn-disabled');
                    return;
                }

                // Calculate hours, minutes, and seconds
                var hours = Math.floor(diff / (1000 * 60 * 60));
                var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((diff % (1000 * 60)) / 1000);

                // Update the alert element with the remaining time
                document.getElementById('alert').innerHTML =
                    `Time Remaining: ${hours}h ${minutes}m ${seconds}s`;
            }, 1000);
        }

        // Initialize the countdown on page load
        gettime();
    </script>
    <!-- BILINGUAL NOTICE JS -->
    <script>
        (function() {
            const banner = document.getElementById('bilingual-notice');
            if (!banner) return;
            const en = document.getElementById('notice-en');
            const hi = document.getElementById('notice-hi');
            const btnEn = document.getElementById('toggle-en');
            const btnHi = document.getElementById('toggle-hi');
            const closeBtn = document.getElementById('dismiss-notice');

            function showLang(lang) {
                if (lang === 'en') {
                    en.classList.add('active');
                    hi.classList.remove('active');
                    btnEn.classList.add('active');
                    btnEn.setAttribute('aria-pressed', 'true');
                    btnHi.classList.remove('active');
                    btnHi.setAttribute('aria-pressed', 'false');
                    sessionStorage.setItem('notice-lang', 'en');
                } else {
                    hi.classList.add('active');
                    en.classList.remove('active');
                    btnHi.classList.add('active');
                    btnHi.setAttribute('aria-pressed', 'true');
                    btnEn.classList.remove('active');
                    btnEn.setAttribute('aria-pressed', 'false');
                    sessionStorage.setItem('notice-lang', 'hi');
                }
            }

            function dismissNotice() {
                banner.style.display = 'none';
                sessionStorage.setItem('notice-closed', '1');
            }

            //   if(sessionStorage.getItem('notice-closed') !== '1') {
            //     banner.style.display = 'none';
            //   } else {
            //     const saved = sessionStorage.getItem('notice-lang') || 'en';
            //     showLang(saved);
            //   }

            btnEn.addEventListener('click', () => showLang('en'));
            btnHi.addEventListener('click', () => showLang('hi'));
            closeBtn.addEventListener('click', dismissNotice);
        })();
    </script>

</body>

</html>