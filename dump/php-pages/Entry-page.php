<?php
session_start();
$SITEURL = 'http://' . $_SERVER['HTTP_HOST'] . '/Login.html';
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

    <link rel="stylesheet" href="assets/css/app.min.css">
    <link rel="stylesheet" href="assets/bundles/jquery-selectric/selectric.css">

    <link rel="stylesheet" href="assets/css/style.css">
    <link href="assets/css/components.css">
    <link rel="stylesheet" href="assets/bundles/flag-icon-css/css/flag-icon.min.css" rel="stylesheet" />
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
                line = line.replace(
                    /^\[\d{1,2}:\d{2}\s*(am|pm),\s*\d{1,2}\/\d{1,2}\/\d{4}\]\s*.*?:\s*/i,
                    ""
                );
                line = line.replace(/>{2,}/g, "");
                line = line.replace(/[\/\.]/g, ",");
                line = line.replace(/\*{2,}/g, "*");
                line = line.replace(/[^0-9AB,\*\(\)= ]/gi, "");
                if (line.trim()) cleaned.push(line.trim());
            }
            return cleaned.join("\n");
        }

        function isValidPattern(line) {
            line = line.trim().toUpperCase();
            line = line.replace(/\b(INTO|INTU|IN|X|×|=)\b/g, "*");
            line = line.replace(/\s+/g, "");
            /*
              VALID NUMBER TOKEN:
              - 12, 99
              - 111, 1111
              - A1, 1A
              - B1, 1B
            */
            const token = "(?:\\d{1,2}|\\d{3}|\\d{4}|A\\d|\\dA|B\\d|\\dB)";
            const list = `${token}(?:,${token})*`;
            const amt = "\\d+";
            /*
              Accept:
              - 12,13,14(100)
              - 12,13,14*100
            */
            const pattern = new RegExp(
                `^${list}(?:\\(${amt}\\)|\\*${amt})$`
            );
            return pattern.test(line);
        }

        function showCommonError(msg) {
            document.getElementById("s_data").innerHTML =
                `<div style="color:red;font-weight:600;margin-top:6px">❌ ${msg}</div>`;
        }

        function enableSubmit() {
            let btn = document.getElementById('submitbtn');
            btn.disabled = false;
            btn.style.setProperty("background-color", "#28a745", "important");
            btn.style.setProperty("border-color", "#28a745", "important");
            btn.style.setProperty("color", "#ffffff", "important");
        }

        function disableSubmit() {
            let btn = document.getElementById('submitbtn');
            btn.disabled = true;
            btn.style.setProperty("background-color", "#c43140", "important");
            btn.style.setProperty("border-color", "#c43140", "important");
            btn.style.setProperty("color", "#ffffff", "important");
        }
        function whatsappfunction() {
            //  alert("start");
            var whtext = document.getElementById("TextBox1").value;
            while (whtext.includes("!!"))
                whtext = whtext.replace("!!", "!");
            while (whtext.includes("@@"))
                whtext = whtext.replace("@@", "@");
            while (whtext.includes("##"))
                whtext = whtext.replace("##", "#");
            while (whtext.includes("$$"))
                whtext = whtext.replace("$$", "$");
            while (whtext.includes("%%"))
                whtext = whtext.replace("%%", "%");
            while (whtext.includes("^^"))
                whtext = whtext.replace("^^", "^");
            while (whtext.includes("&&"))
                whtext = whtext.replace("&&", "&");
            while (whtext.includes("--"))
                whtext = whtext.replace("--", "-");
            while (whtext.includes("__"))
                whtext = whtext.replace("__", "_");
            while (whtext.includes("  "))
                whtext = whtext.replace("  ", " ");
            while (whtext.includes("//"))
                whtext = whtext.replace("//", "/");
            while (whtext.includes("||"))
                whtext = whtext.replace("||", "|");
            while (whtext.includes("aa"))
                whtext = whtext.replace("aa", "a");
            while (whtext.includes("bb"))
                whtext = whtext.replace("bb", "b");

            while (whtext.includes(".."))
                whtext = whtext.replace("..", ".");
            while (whtext.includes("=="))
                whtext = whtext.replace("==", "=");
            while (whtext.includes(",,"))
                whtext = whtext.replace(",,", ",");
            while (whtext.includes(")"))
                whtext = whtext.replace(")", " \n");
            while (whtext.includes("["))
                whtext = whtext.replace("[", " \n");





            var whstr = whtext.split("\n");
            var finalstr = "";
            for (var i = 0; i < whstr.length; i++) {
                let text = whstr[i];
                var idx = text.indexOf(": ");
                if (idx != -1) {
                    text = text.substring(idx + 2, text.length);
                    whstr[i] = text;
                    finalstr = finalstr + whstr[i] + "\n";
                } else {
                    if (text.length > 3) {
                        text = text.substring(0, text.length);
                        whstr[i] = text;
                        finalstr = finalstr + whstr[i] + "\n";
                    }

                }


            }
            // alert(finalstr);
            document.getElementById("TextBox1").value = finalstr;

        }
        /*function whatsappfunction() {            var whtext = rawText;
            var whstr = whtext.split("\n");
            var finalstr = "";
            for (var i = 0; i < whstr.length; i++) {
                let text = whstr[i];
                var idx = text.indexOf(": ");
				if(idx >= 0)
                text = text.substring(idx + 2, text.length - 1);
				else
				 text = text.substring(0, text.length );
                
				whstr[i] = text;
                finalstr = finalstr + whstr[i] + "\n";            }            return finalstr;        }*/
        var gnumamtlist = "",
            gfinalamount = 0;
        var ALL_LINES_VALID = false;

       function CheckAkhar() {
            //window.value= document.getElementById("TextBox1").value;

            document.getElementById("s_data").innerHTML = "";
            document.getElementById('addtrn').innerHTML = "";
            gnumamtlist = "";
            gfinalamount = 0;
            whatsappfunction();

            var bval = document.getElementById("TextBox1").value;
            bval = bval.toUpperCase();
            while (bval.includes("("))
                bval = bval.replace("(", "*");
            while (bval.includes("INTO"))
                bval = bval.replace("INTO", "*");
            while (bval.includes("IN"))
                bval = bval.replace("IN", "*");
            while (bval.includes("="))
                bval = bval.replace("=", "*");
            while (bval.includes("INTU"))
                bval = bval.replace("INTU", "*");
            while (bval.includes("×"))
                bval = bval.replace("×", "*");
            while (bval.includes("X"))
                bval = bval.replace("X", "*");
            while (bval.includes("!"))
                bval = bval.replace("!", ",");
            while (bval.includes("@"))
                bval = bval.replace("@", ",");
            while (bval.includes("#"))
                bval = bval.replace("#", ",");
            while (bval.includes("$"))
                bval = bval.replace("$", ",");
            while (bval.includes("%"))
                bval = bval.replace("%", ",");
            while (bval.includes("^"))
                bval = bval.replace("^", ",");
            while (bval.includes("&"))
                bval = bval.replace("&", ",");
            while (bval.includes("-"))
                bval = bval.replace("-", ",");
            while (bval.includes("_"))
                bval = bval.replace("_", ",");
            while (bval.includes(" "))
                bval = bval.replace(" ", ",");
            while (bval.includes("/"))
                bval = bval.replace("/", ",");
            while (bval.includes("|"))
                bval = bval.replace("|", ",");
            while (bval.includes("a"))
                bval = bval.replace("a", "A");
            while (bval.includes("b"))
                bval = bval.replace("b", "B");
            while (bval.includes("."))
                bval = bval.replace(".", ",");

            bval = cleanLineAfterAsterisk(bval);
            //alert(bval); 
            document.getElementById("TextBox1").value = bval;
            document.getElementById("D_data").innerHTML = bval;

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
            document.getElementById("TextBox1").value = document.getElementById("D_data").value;
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

        function Mycode()

        {
            document.getElementById('addtrn').innerHTML = "";
            var bval = document.getElementById("TextBox1").value;

            var l_list = bval.split("\n");
            //alert(l_list.length); return false;
            for (var i = 0; i < l_list.length; i++) { //alert(l_list[i]); return false;
                if (l_list[i].length > 0)
                    Mycode1(l_list[i]);
            }
            //-----------------------------------------------------------------------------------------------------submit enable
            //alert(document.getElementById('aaa').innerHTML.length); return false;
            var textlen = (document.getElementById('s_data').innerHTML) + "";
            //alert(textlen.length);
            if (textlen.length == 0) {
                let btn = document.getElementById('submitbtn');
                btn.disabled = false;

                // apply green color with !important
                btn.style.setProperty("background-color", "#28a745", "important");
                btn.style.setProperty("border-color", "#28a745", "important");
                btn.style.setProperty("color", "#ffffff", "important");

            } else {
                let btn = document.getElementById('submitbtn');
                btn.disabled = true;

                // apply red color with !important
                btn.style.setProperty("background-color", "#c43140", "important");
                btn.style.setProperty("border-color", "#c43140", "important");
                btn.style.setProperty("color", "#ffffff", "important");
            }

            document.getElementById("TextBox1").value = "";

        }

        function heighlight(str) {
            var txtstr = document.getElementById("D_data").value;
            let newText = txtstr.replace(str, "<a href='#' onclick='FillData()'><span id='aaa'>" + str + "</span></a>");
            document.getElementById("D_data").innerHTML = txtstr;
            document.getElementById("s_data").innerHTML = newText;
        }

        function Mycode1(p_str) {

            var bval = p_str; // document.getElementById("TextBox1").value;//document.getElementById('<%=TextBox1.ClientID%>').value;
            var msg;
            var finalnumlist = "";
            const numberList1 = (bval
                .split("*"));
            console.log(numberList1);
            //console.log(numberList1);return false;
            if (numberList1.length == 1) {
                msg = "Amount Not Found against those number :- " + bval;

                alert(msg);
                heighlight(bval);
                //window.location = '/Entry-page.php?param='+window.value;
                return false;
            }
            if (numberList1.length > 2) {
                msg = "Multiple Amount ( ";
                for (var i = 1; i < numberList1.length; i = i + 1)
                    msg = msg + numberList1[i] + ", ";

                msg = msg + " ) against those number :-" + numberList1[0];
                alert(msg);
                heighlight(numberList1[0]);
                //window.location = '/Entry-page.php?param='+window.value;
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
                alert("Not able to get amount From " + numberList1[0]);
                heighlight(numberList1[0]);
                // window.location = '/Entry-page.php?param='+window.value;
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
                                    alert(str + " is Not A valid Number");
                                    //window.location = '/Entry-page.php?param='+window.value;
                                    heighlight(str);
                                    return false;
                                }
                                newstr = str[1] + str[1] + str[1] + str[1];
                                nums[i] = newstr;

                            } else {
                                if (IsNonDigit(str, 0, "A")) {
                                    alert(str + " is Not A valid Number");
                                    //window.location = '/Entry-page.php?param='+window.value; 
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
                                    alert(str + " is Not A valid Number");
                                    heighlight(str);
                                    //window.location = '/Entry-page.php?param='+window.value;
                                    return false;
                                }
                                newstr = str[1] + str[1] + str[1];
                                nums[i] = newstr;
                            } else {
                                if (IsNonDigit(str, 0, "B")) {
                                    alert(str + " is Not A valid Number");
                                    heighlight(str);
                                    //    window.location = '/Entry-page.php?param='+window.value; 
                                    return false;
                                }
                                newstr = str[0] + str[0] + str[0];
                                nums[i] = newstr;
                            }
                        } else {
                            var cr_code = str.charCodeAt(0);
                            if (cr_code < 48 || cr_code > 57) {
                                alert(str + " Number is Not Valid");
                                heighlight(str);
                                heighlight(str);

                                //window.location = '/Entry-page.php?param='+window.value; 
                                return;
                            }
                            for (var k = 0; k < str.length; k++) {
                                if (str[0] != str[k]) {
                                    //alert(window.value); 
                                    alert(str + " Number is Not Valid");
                                    heighlight(str);
                                    heighlight(str);

                                    //-------------------------------------------------------------------------------------call function for heighlight funection

                                    //location.href = location.href + "&parameter=" + window.value;
                                    // window.location = '/Entry-page.php?param='+window.value;
                                    return false;
                                }
                            }
                            //}
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
                    if (i == nums.length - 1) {
                        amtlist = amtlist + numberList1[1];
                        finalnumlist = finalnumlist + nums[i];
                        gnumamtlist = gnumamtlist + nums[i] + "( " + numberList1[1] + " ) || ";
                        gfinalamount += parseInt(numberList1[1]);

                        var newRow = document.getElementById('addtrn').insertRow();
                        newRow.innerHTML =
                            '<tr><th><input type="text" pattern="[0-9]*"  class="form-control" name="trn_number[]" maxlength="2" value="' +
                            nums[i] +
                            '" placeholder="Number"  onkeyup="checkInput(this)"></th> <th><input type="text" onkeyup="checkInput(this)"  class="form-control" value="' +
                            numberList1[1] +
                            '" name="trn_amount[]" pattern="[0-9]*"   placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent()"> X </span></td></tr>';

                    } else {
                        amtlist = amtlist + numberList1[1] + ",";
                        finalnumlist = finalnumlist + nums[i] + ",";
                        gnumamtlist = gnumamtlist + nums[i] + "( " + numberList1[1] + " ) || ";
                        gfinalamount += parseInt(numberList1[1]);

                        var newRow = document.getElementById('addtrn').insertRow();
                        newRow.innerHTML =
                            '<tr><th><input type="text" pattern="[0-9]*"   class="form-control" name="trn_number[]" maxlength="2" value="' +
                            nums[i] +
                            '" placeholder="Number"  onkeyup="checkInput(this)"></th> <th><input type="text" class="form-control" value="' +
                            numberList1[1] +
                            '" name="trn_amount[]" pattern="[0-9]*" onkeyup="checkInput(this)"   placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent().remove();"> X </span></td></tr>';

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
                if (charCode > 31 && (charCode < 48 || charCode > 57))
                    return false;
            }

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
                        fromtonumberlist = fromtonumberlist + 00 + ",";
                        amountlist = amountlist + amt + ",";
                        gnumamtlist = gnumamtlist + 00 + "( " + amt + " ) || ";
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
            }

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
                                '" name="trn_amount[]" onkeyup="checkInput(this)" pattern="[0-9]*" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" placeholder="Amount" ></th><td><button type="button"  class="reset-btn delete" onClick="$(this).parent().parent().remove();">Delete</button></td></tr>';
                        } else {
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
                                    '" name="trn_amount[]" onkeyup="checkInput(this)" pattern="[0-9]*" onkeypress="return event.charCode &gt;= 48 &amp;&amp; event.charCode &lt;= 57" placeholder="Amount" ></th><td><button type="button" class="reset-btn delete" onClick="$(this).parent().parent().remove();">Delete</button></td></tr>';
                            }
                        }
                    }
                }
                document.getElementById("crnum1").value = "";
                document.getElementById("crnum2").value = "";
                document.getElementById("cramt").value = "";
            }

            function ShowAkharParten(x) {
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
            }

            function hidetable() {
                document.getElementById('view').style.display = "none";
                document.getElementById('bnthide').style.display = "none";
                document.getElementById('bntview').style.display = "";
            }

            function totalviewtable() {
                document.getElementById('totalview').style.display = "gnumamtlist";
                alert(gnumamtlist + " FINAL AMOUNT :-" + gfinalamount);
            }

            function totalhidetable() {
                document.getElementById('totalview').style.display = "none";
                document.getElementById('bnttotalhide').style.display = "none";
                document.getElementById('bnttotalview').style.display = "";
            }
    </script>
</head>
        
        <style>

        

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

        .form-group {
            width: 100%;
        }

        .check-btn {
            flex-wrap: wrap;
            gap: 10px;
        }

        .check-btn .col,
        .check-btn .col-6 {
            flex: 1 1 100%;
        }

        @media(max-width:768px) {
            textarea.form-control {
                height: 160px !important;
                font-size: 14px;
            }
        }

        table {
            width: 100% !important;
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }

        .notice-banner {
            flex-wrap: wrap;
        }

        .notice-controls {
            width: 100%;
            justify-content: flex-start;
        }

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

        .check-btn>div:first-child {
            margin-right: 12px !important;
        }

        .check-btn a,
        .check-btn input {
            width: 100%;
        }
    </style>
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
            /*padding: 15px;*/
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
                /*padding: 2px !important;*/
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
<body class="background-image-body">
     <?php
                                date_default_timezone_set('Asia/Kolkata');
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    if (isset($_POST['submitpost'])) {
                                        $Shift = $_POST['shift'];
                                        $Party = $_POST['party'];
                                        $new_date = date('Y-m-d', strtotime($_POST['dateoftrn']));
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

                                ?> <?php //echo 'ttp';     
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
                                }    ?>
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
                                <h4 class="text-white"><a onclick="showjantri()" style="color:white"><i class="fas fa-pen" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i>Jantri</a></h4> ,

                                <h4 class="text-white"><a href="Date-shift.php<?= $view ?>" style="color:white"><i class="fas fa-book" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> View Transaction</a></h4>
                                <h4 class="text-white"><a href="hisablist.php<?= $view ?>" style="color:white"><i class="fas fa-book" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Hisab</a></h4>
                                <h4 class="text-white"> <a href="statement.php/<?= $_GET['login'] ?>?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" style="color:white">
                                        <i class="fas fa-book" style="color:white !important" id="showbtn"></i> Statement
                                    </a></h4>
                                <h4 class="text-white"><a href="https://new.555xch.pro/appdemo/Login.html" style="color:white"><i class="fas fa-book" style="color:white !important" title="" name="ShowkaharIcon" id="showbtn"></i> Logout</a></h4>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    <!-- <div id="bilingual-notice" class="notice-banner" role="region" aria-live="polite" aria-label="Site notice">
  <div class="notice-text">
    <div id="notice-en" class="notice-lang">
      <strong>Notice:</strong> 20/10 se gaziyabad, Gali or Disawer ka time change ho jayega Gaziyabad.........8.40 pm
Gali..................10.40 pm
Disawer..............4.10 Am
    </div>
    <div id="notice-hi" class="notice-lang active">
      <strong>सूचना:</strong> 20/10 से गाजियाबाद, गली या दिसावेर का समय बदल जाएगा गाजियाबाद.........रात 8.40 बजे
गली.................रात 10.40 बजे
दिसावेर...........4.10 पूर्वाह्न
    </div>
  </div>  <div class="notice-controls">
    <button type="button" id="toggle-en" class="notice-btn" aria-pressed="true">EN</button>
    <button type="button" id="toggle-hi" class="notice-btn active" aria-pressed="false">HI</button>
    <button type="button" id="dismiss-notice" class="notice-close" aria-label="Dismiss notice">&times;</button>
  </div>
</div> -->
                                    <div class="form-group col-6">
                                        <?php $servername = "localhost";
                                        $username = "555prouser";
                                        $password = "e2OFVjrRK77ljyfs4z@R";
                                        $database = "555prodb";
                                        $conn = new mysqli($servername, $username, $password, $database);
                                        if ($conn->connect_error) {
                                            die("Connection failed: " . $conn->connect_error);
                                        }
                                        $user_id = 1;
                                        $ledger_id = (int) $_GET['login']; // make sure it's integer-safe
                                        $start_datetime = '2025-08-01';
                                        $end_datetime = date('Y-m-d 06:00:00', strtotime('+1 day'));
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
                                        }
                                        echo "<h5>Coin Balance: <span style='color:red'>" . number_format($balance, 2) . "</span></h5>";
                                        $conn->close();
                                        ?> </div>
                                </div>
                                <form id="demo-form2" action="https://new.555xch.pro/tbl_transactions/add_transaction_final_app" method="POST" data-parsley-validate
                                    class="form-horizontal form-label-left">
                                    <div class="row">
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
                                                    $mysqli = new mysqli($servername, $username, $password, $dbname);
                                                    if ($mysqli->connect_errno) {
                                                        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                                                        exit();
                                                    }
                                                    $sql = "select * from tbl_ledger where Status = 1  ORDER BY ledger_name ASC";
                                                    $result = $mysqli->query($sql);
                                                    $datacount = 0;
                                                    while ($row = mysqli_fetch_array($result)) {  //echo '<pre>'; print_r($_SESSION); echo '</pre>'; die;
                                                        if (($_GET["user_type"] == 'ledger') && ($_GET["login"] == $row['id'])) {
                                                            echo '<option value=' . $row['id'] . ' selected>' . $row['ledger_name'] . '</option>';
                                                        } else if (($_GET["user_type"] == 'admin')) {
                                                            echo '<option value=' . $row['id'] . '>' . $row['ledger_name'] . '</option>';
                                                        }
                                                    }

                                                    ?>
                                                </select>
                                            </div>
                                        </div>

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
                                        <div class="form-group col-6">
                                            <h4 style="margin-right:10px;"><b>Shift</b></h4>

                                            <div class="btn-group" style="height: 36px; margin-right: 10px;;width:100%"> <select name="shift" id="shift" onchange="gettime()" class="form-control"
                                                    required>
                                                    <?php $ttime = time();
                                                    $servername = "localhost";
                                                    $username = "555prouser";
                                                    $password = "e2OFVjrRK77ljyfs4z@R";
                                                    $dbname = "555prodb";
                                                    $mysqli = new mysqli($servername, $username, $password, $dbname);
                                                    $newTimestamp = time() + (12 * 60 * 60); // Add 12 hours to the current time (12 hours * 60 minutes * 60 seconds)
                                                    $todate = date('Y-m-d', $newTimestamp);
                                                    $fromdate = date('Y-m-d', time());
                                                    $sql = "SELECT `tbl_shift`.`id` AS `tbl_shift_id`, `tbl_shift`.*, `user_shift_timings`.`id` AS `user_shift_timing_id`, `user_shift_timings`.*, `tbl_shift`.`open_date` AS `tbl_shift_open_date`, 
                                    `user_shift_timings`.`open_date` AS `user_shift_open_date` FROM `user_shift_timings` 
                                    LEFT JOIN `tbl_shift` ON `user_shift_timings`.`shift_id` = `tbl_shift`.`id` 
                                    WHERE `user_shift_timings`.`updated_by` = '" . $_SESSION['updated_by'] . "' 
                                    AND `user_shift_timings`.`open_date` >= '" . $fromdate . "' AND `user_shift_timings`.`open_date` <= '" . $todate . "' ORDER BY `user_shift_timings`.`open_date` ASC, `user_shift_timings`.`master` ASC";
                                                    $rs = $mysqli->query($sql);
                                                    $datacount = 0;
                                                    while ($row = mysqli_fetch_array($rs)) {
                                                        if ($_GET['user_type'] == 'admin') {
                                                            $time = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date("H:i", strtotime($row['super_admin'])));
                                                        }
                                                        if ($_GET['user_type'] == 'ledger') {
                                                            $time = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date("H:i", strtotime($row['app_time'])));
                                                        }
                                                        if (($ttime < $time)) {
                                                            echo '<option value=' . $row['id'] . ' data-foo="' . date("h:i A", strtotime($row['app_time'])) . '">' . $row['shift_name'] . '</option>';
                                                        }
                                                    }
                                                    ?> </select>
                                                <div id="alert" class="alert blink" style="color:red; font-size:10px"></div>
                                                <div class="alert" style="display:none">Please Select Shift First</div>
                                            </div>
                                        </div>
                                        <div class="form-group col-12" id="numak">
                                            <label for="frist_name">
                                                Num-Akhar
                                                <i class="fas fa-eye" style="color:black !important" title="" name="ShowkaharIcon" id="showbtn" onclick="ShowAkharParten(1)"></i>
                                                <i class="fas fa-eye-slash" style="color:black !important; display:none" title="" name="HidekaharIcon" id="hidebtn" onclick="HideAkharParten(1)"></i>
                                            </label>
                                            <textarea name="TextBox1" id="TextBox1" class="form-control required " rows="5" onchange="CheckAkhar()"></textarea><br>
                                            <textarea id="D_data" name="D_data" class="form-control required " rows="5" onchange="FillData()"></textarea>
                                            <span id="s_data" name="s_data"></span>
                                            <div class="form-group col-12 d-flex check-btn">
                                                <div class="col"><a href="#" onclick="Mycode()" class=" btn btn-auth-color btn-lg btn-flex">Done</a></div>
                                                <div class="col-6">
                                                    <input type="submit" id="submitbtn" disabled name="submitpost" onclick="this.disabled=true;this.value=&#39;Sending, please wait...&#39;;this.form.submit();" style="background-color:#c43140;color:white;" class="btn btn-auth-color btn-lg btn-flex">
                                                </div>
                                            </div>
                                        </div> <span name="numsms" id="numsms" style="display: none; color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; ">
                                            <table style="border:solid 1px black" class="table table-responsive">
                                                <tr>
                                                    <td>
                                                        रकम डालने के लिए ,into , in , = ,(),*, का प्रयोग करें <br />उदाहरन के लिए <br />
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
                                                        आखर डालने के लिए उदाहरन <br />
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

                                        <div class="form-group col-12" id="jantri" style="display:none">
                                            <div class="page-wrapper">
                                                <div class="container-jantri"> <!-- SHIFT + DATE + RESET BUTTON -->
                                                    <input type="hidden" name="ttamntt" id="ttamntt" value="0">
                                                    <input type="hidden" name="gtotal" id="gtotal" value="0">
                                                    <div class="numbers-container-wrapper">
                                                        <div id="numbers-container">
                                                            <table class="numbers-table">
                                                                <tbody> <?php $start_num = 1;
                                                                        for ($row = 1; $row <= 10; $row++) {
                                                                            echo "<tr class='data-row' data-row-id='row-$row'>";
                                                                            for ($col = 0; $col < 10; $col++) {
                                                                                echo "<td>$start_num<br>"; ?> <input type="text" class="row-input" name="trn_amount[]" data-row="row-<?php echo $row; ?>" data-col="<?php echo $col; ?>" style="width: 100%;border: none;"> <?php echo "</td>";
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
                                                                        echo "</tr>"; ?> </tbody>
                                                            </table>
                                                        </div>
                                                    </div> <!-- SECOND TABLE: B and A sections -->
                                                    <div class="numbers-container-wrapper" style="margin-top: 20px;">
                                                        <div id="numbers-container">
                                                            <table class="numbers-table">
                                                                <thead>
                                                                    <tr style='background-color: #e8e8e8;'>
                                                                        <td style='font-weight: bold; text-align: center; background-color: #e8e8e8;'></td> <?php for ($col = 1; $col <= 10; $col++) {
                                                                                                                                                                echo "<td style='font-weight: bold; text-align: center; background-color: #e8e8e8; color: #333;'>$col</td>";
                                                                                                                                                            } ?> <td style='font-weight: bold; text-align: center; background-color: #e8e8e8;'>Total</td>
                                                                    </tr>
                                                                </thead>
                                                                <tbody> <?php echo "<tr class='section-row' style='background-color: #f5f5f5;'>";
                                                                        echo "<td style='font-weight: bold; text-align: center; background-color: #f5f5f5;'>B</td>";
                                                                        for ($col = 0; $col < 10; $col++) {
                                                                            echo "<td><input type='text' class='b-input' name='b[]' data-col='$col' style='width: 100%;border: none;'></td>";
                                                                        }
                                                                        echo "<td class='total-cell' data-section-total='section-b'>0</td>";
                                                                        echo "</tr>";
                                                                        echo "<tr class='section-row' style='background-color: #f5f5f5;'>";
                                                                        echo "<td style='font-weight: bold; text-align: center; background-color: #f5f5f5;'>A</td>";
                                                                        for ($col = 0; $col < 10; $col++) {
                                                                            echo "<td><input type='text' class='a-input' name='a[]' data-col='$col' style='width: 100%;border: none;'></td>";
                                                                        }
                                                                        echo "<td class='total-cell' data-section-total='section-a'>0</td>";
                                                                        echo "</tr>"; ?> </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="footer-section">
                                                        <div class="footer-grand-total"> <span class="footer-grand-total-label">Grand Total</span> <span class="footer-grand-total-value" id="footer-grand-total">0</span> </div>
                                                        <div class="footer-submit"> <button class="submit-btn">Submit</button> </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" id="numamnt"> <span id="view" name="view" style=" color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; ">
                                                <table id="addtrn1">
                                                    <thead>
                                                        <!--<tr>
                                                        <th><input type="text" class="form-control" name="trn_number[]" maxlength="2" maxlength="2" placeholder="Number" onfocus="checkInput(this)"></th>
                                                        <th><input type="text" class="form-control" name="trn_amount[]" maxlength="6" placeholder="Amount" onfocus="checkInput(this)"></th>
                                                    </tr>-->
                                                    </thead>
                                                </table>
                                            </span>
                                            <div id="totalview" name="totalview" style="display: none; color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; "> </div>
                                        </div>
                                        <div class="form-group col-12 d-flex check-btn"> <i class="fa fa-table" style="color:black !important" title="" name="bntview" id="bntview" onclick="viewtable()"></i>
                                            <i class="fa fa-minus-circle" style="color:black !important; display:none" title="" name="bnthide" id="bnthide" onclick="hidetable()"></i> <i class="fa fa-shower" style="color: black !important; margin-left: 90%;" title="" name="bnttotalview" id="bnttotalview" onclick="totalviewtable()"></i>

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
                                        </span> <!--  <span id="view" name="view" style=" color: #0066FF; font-family: arial; font-size: 16px; margin: 0 0 10px 100px; ">
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

    <script src="assets/js/app.min.js"></script>

    <script src="assets/bundles/jquery-pwstrength/jquery.pwstrength.min.js"></script>
    <script src="assets/bundles/jquery-selectric/jquery.selectric.min.js"></script>

    <script src="assets/js/page/auth-register.js"></script>

    <script src="assets/js/scripts.js"></script>
    <script src="assets/js/daterangepicker.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script>
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

        function initJantriInputs() {

            /* ---------------- ROW INPUTS ---------------- */
            document.querySelectorAll('.row-input').forEach(input => {

                input.addEventListener('input', function() {
                    const rowId = this.dataset.row;
                    const colNum = this.dataset.col;
                    calculateRowTotal(rowId);
                    calculateColumnTotal(colNum);
                    calculateGrandTotal();
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === 'NumpadEnter' || e.keyCode === 13) {
                        e.preventDefault();

                        const row = this.dataset.row;
                        const col = Number(this.dataset.col);

                        let next;
                        if (col < 9) {
                            next = document.querySelector(`.row-input[data-row="${row}"][data-col="${col + 1}"]`);
                        } else {
                            const r = Number(row.split('-')[1]);
                            next = r < 10 ?
                                document.querySelector(`.row-input[data-row="row-${r + 1}"][data-col="0"]`) :
                                document.querySelector('.b-input[data-col="0"]');
                        }

                        next && next.focus();
                    }
                });
            });

            /* ---------------- B INPUTS ---------------- */
            document.querySelectorAll('.b-input').forEach(input => {

                input.addEventListener('input', function() {
                    calculateSectionTotal('section-b');
                    calculateFooterGrandTotal();
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === 'NumpadEnter' || e.keyCode === 13) {
                        e.preventDefault();

                        const col = Number(this.dataset.col);
                        const next = col < 9 ?
                            document.querySelector(`.b-input[data-col="${col + 1}"]`) :
                            document.querySelector('.a-input[data-col="0"]');

                        next && next.focus();
                    }
                });
            });

            /* ---------------- A INPUTS ---------------- */
            document.querySelectorAll('.a-input').forEach(input => {

                input.addEventListener('input', function() {
                    calculateSectionTotal('section-a');
                    calculateFooterGrandTotal();
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === 'NumpadEnter' || e.keyCode === 13) {
                        e.preventDefault();

                        const col = Number(this.dataset.col);
                        const next = col < 9 ?
                            document.querySelector(`.a-input[data-col="${col + 1}"]`) :
                            null;

                        next && next.focus();
                    }
                });
            });
        }   

        function disableNormalInputs() {
    // Num-Akhar
    document.querySelectorAll('#numak input, #numak textarea, #numak select')
        .forEach(el => el.disabled = true);

    // From-To
    document.querySelectorAll('#fromto input, #fromto textarea, #fromto select')
        .forEach(el => el.disabled = true);

    // Cross
    document.querySelectorAll('#Cross input, #Cross textarea, #Cross select')
        .forEach(el => el.disabled = true);
}

function enableNormalInputs() {
    document.querySelectorAll('#numak input, #numak textarea, #numak select')
        .forEach(el => el.disabled = false);

    document.querySelectorAll('#fromto input, #fromto textarea, #fromto select')
        .forEach(el => el.disabled = false);

    document.querySelectorAll('#Cross input, #Cross textarea, #Cross select')
        .forEach(el => el.disabled = false);
}

function disableJantriInputs() {
    document.querySelectorAll('#jantri input, #jantri textarea, #jantri select')
        .forEach(el => el.disabled = true);
}

function enableJantriInputs() {
    document.querySelectorAll('#jantri input, #jantri textarea, #jantri select')
        .forEach(el => el.disabled = false);
}
        function showentry() {
            document.getElementById('numak').style.display = "block";
            document.getElementById('fromto').style.display = "none";
            document.getElementById('Cross').style.display = "none";
            document.getElementById('jantri').style.display = "none";
            enableNormalInputs();
    disableJantriInputs();
    setFormAction(false);
        }

        function showfromto() {
            document.getElementById('numak').style.display = "none";
            document.getElementById('fromto').style.display = "block";
            document.getElementById('Cross').style.display = "none";
            document.getElementById('jantri').style.display = "none";
            enableNormalInputs();
    disableJantriInputs();
    setFormAction(false);
        }

        function showcross() {
            document.getElementById('numak').style.display = "none";
            document.getElementById('fromto').style.display = "none";
            document.getElementById('Cross').style.display = "block";
            document.getElementById('jantri').style.display = "none";
            enableNormalInputs();
    disableJantriInputs();
    setFormAction(false);
        }

        function showjantri() {
            document.getElementById('numak').style.display = "none";
            document.getElementById('fromto').style.display = "none";
            document.getElementById('Cross').style.display = "none";
            document.getElementById('jantri').style.display = "block";
            setFormAction(true);
           // initJantriInputs(); // 👈 REQUIRED
            disableNormalInputs();
    enableJantriInputs();
    setFormAction(true);
    initJantriInputs();
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
        .blink {
            animation: blink-animation 1.5s steps(5, start) infinite;
            color: darkred;
        }

        @keyframes blink-animation {
            from {
                opacity: 1;
            }

            to {
                opacity: 0.4;
            }
        }

        .btn-disabled {
            background-color: grey !important;
            cursor: not-allowed;
        }
    </style>
    <script>
        let countdownInterval;

        function gettime() {
            var submitBtn = document.getElementById('submitbtn');

            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            var sel = document.getElementById('shift');
            var selected = sel.options[sel.selectedIndex];
            var shiftTime = selected.getAttribute('data-foo'); // e.g., "02:55 PM"
            var now = new Date(); // Current date and time
            var shiftDate = new Date(now.toDateString() + ' ' + shiftTime); // Combine current date with shift time
            countdownInterval = setInterval(() => {
                var now = new Date();
                var diff = shiftDate - now;
                if (diff <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('alert').innerHTML =
                        `<span class="blink">Shift time expired!</span>`;
                    submitBtn.disabled = true;
                    submitBtn.classList.add('btn-disabled');
                    return;
                }
                var hours = Math.floor(diff / (1000 * 60 * 60));
                var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((diff % (1000 * 60)) / 1000);
                document.getElementById('alert').innerHTML =
                    `Time Remaining: ${hours}h ${minutes}m ${seconds}s`;
            }, 1000);
        }
        gettime();
    </script>

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
            btnEn.addEventListener('click', () => showLang('en'));
            btnHi.addEventListener('click', () => showLang('hi'));
            closeBtn.addEventListener('click', dismissNotice);
        })();
    </script>
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
                    if (e.key === 'Enter' || e.key === 'NumpadEnter' || e.keyCode === 13) {
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
                    if (e.key === 'Enter' || e.key === 'NumpadEnter' || e.keyCode === 13) {
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
                    if (e.key === 'Enter' || e.key === 'NumpadEnter' || e.keyCode === 13) {
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


        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("demo-form2");

            if (!form) return;

            form.addEventListener("submit", function() {
                document.getElementById('gtotal').value =
                    document.getElementById('footer-grand-total').textContent.trim() || 0;
            });
        });
        document.addEventListener("DOMContentLoaded", function () {
            disableJantriInputs();
        });
    </script>

    <script>
        const DEFAULT_ACTION =  "https://new.555xch.pro/tbl_transactions/add_transaction_final_app";

        const JANTRI_ACTION =   "https://new.555xch.pro/tbl_jantri/add_jantri_form_app";
    </script>
    <script>
        function setFormAction(isJantri) {
            const form = document.getElementById("demo-form2");
            if (!form) return;

            form.action = isJantri ? JANTRI_ACTION : DEFAULT_ACTION;
        }
    </script>
</body>

</html>