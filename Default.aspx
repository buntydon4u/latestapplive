<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Default.aspx.cs" Inherits="_Default" %>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <title></title>
    <script>
        function Mycode()
        {
            
            var bval = document.getElementById('<%=TextBox1.ClientID%>').value;
            alert(bval);
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
            // document.getElementById("addtrn").deleteRow(0);
            var ttotal = 0;
            for (var i = 0; i < numberList.length; i = i + 2) {
                var newRow = document.getElementById('addtrn').insertRow();
                newRow.innerHTML =
                    '<tr><th><input type="text" class="form-control" name="trn_number[]" value="' +
                    numberList[i] +
                    '" placeholder="Number" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" onkeyup="checkshift(this)"></th> <th><input type="text" class="form-control" value="' +
                    numberList[i + 1] +
                    '" name="trn_amount[]" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent().remove();"> X </span></td></tr>';
                //console.log(numberList[i]);
                //.insertAdjacentHTML('afterend','<tr><th><input type="text" class="form-control" name="trn_number[]" placeholder="Number" onkeyup="checkshift(this)"></th> <th><input type="text" class="form-control" name="trn_amount[]" placeholder="Amount" ></th></tr>');
                ttotal = ttotal+ numberList[i + 1];
            }
            var oldval =  document.getElementById('ttamntt').value;
            document.getElementById('ttamntt').value = parseInt(oldval)+parseInt(ttotal);
            var newRow = document.getElementById('addtrn').insertRow();
            newRow.innerHTML =
                '<tr> <th><input type="text" class="form-control" name="trn_number[]" placeholder="Number" onkeyup="checkshift(this)" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" autocomplete="off"></th><th><input type="text" class="form-control" name="trn_amount[]" placeholder="Amount" autocomplete="off"></th></tr>';
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
            return true;
        }
</script>
</head>
<body>
    <form id="form1" runat="server">
    <div>
       <table id="addtrn">
                                                                                            <thead>
                                                                                                <tr>
                                                                                                    <th><input type="text" class="form-control" name="trn_number[]" maxlength="2" placeholder="Number" onfocus="checkshift(this)"></th>
                                                                                                    <th><input type="text" class="form-control" name="trn_amount[]" maxlength="6" placeholder="Amount" onfocus="checkshift(this)"></th>

                                                                                                </tr>
                                                                                            </thead>
                                                                                        </table>
      

    
        </br>
        <asp:Label ID="Label1" runat="server" Text="" ForeColor="Red" Font-Size="X-Large"></asp:Label>
        </br>
           <asp:Label ID="Label2" runat="server" Text="Enter number and Amount" ForeColor="Blue" Font-Size="X-Large"></asp:Label>
           <asp:DropDownList ID="DropDownList1" runat="server"></asp:DropDownList>
        </br>
        <asp:TextBox ID="TextBox1" runat="server" TextMode="MultiLine" Rows="10" Width="400"></asp:TextBox>
        <br />
        <br />
           <asp:Label ID="Label3" runat="server" Text="Enter number and Amount" ForeColor="Blue" Font-Size="X-Large"></asp:Label>
           <br />
        <asp:TextBox ID="TextBox2" runat="server" TextMode="MultiLine" Rows="10" Width="400" OnTextChanged="TextBox2_TextChanged" AutoPostBack="true"></asp:TextBox>
        <br />
        <br />
        </br>

        <input type ="button" onclick="Mycode()" value="Check Data"/>
        <asp:Button ID="Bntcheck" runat="server" Text="Check" OnClientClick="return Mycode()"  OnClick="Bntcheck_Click"/>
        <asp:Button ID="bntSubmit" runat="server" Text="Submit" OnClick="bntSubmit_Click" />
        </br>
        <asp:GridView ID="GridView1" runat="server"></asp:GridView>
    
    </div>
    </form>
</body>
</html>
