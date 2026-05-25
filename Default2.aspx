<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Default2.aspx.cs" Inherits="Default2" %>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <title></title>
    <script>
        var akharnum1;
        function CheckAkhar(arr)
        {
            alert(arr);
        }
        
        function Mycode()
        {
            
            var bval = document.getElementById('<%=TextBox1.ClientID%>').value;
            var msg;
            var finalnumlist="";
            const numberList1 = (bval
                    .split("*"
           
                      )
                      );
            
            if(numberList1.length==1)
            {
                msg="Amount Not Found against those number "+bval;
                alert(msg);
                return false;
            }
            if(numberList1.length>2)
            {
                msg="Multiple Amount ( ";
                for (var i = 1; i < numberList1.length; i =i+1)
                    msg =msg + numberList1[i] + ",";

                msg=msg +") against those number" + numberList1[0];   
                alert(msg);
                return false;
            }
           
            

            var numlist = numberList1[0];
            var nums = numlist.split(",");
            var amtlist ="";

            for (var i = 0 ; i < nums.length; i = i + 1)
            {

                var str = nums[i];
                

                if (str.length > 2 || str.includes("A") || str.includes("B"))
                {
                    alert(str);
                    var ind;
                    var newstr;
                    if(str.includes("A"))
                    {
                        ind = str.indexOf("A");
                        if(ind==0)
                        {
                            newstr = str[1]+str[1]+str[1];
                            nums[i] = newstr;
                            
                        }
                        else
                        {
                            newstr = str[0] + str[0] + str[0];
                            nums[i] = newstr;
                           

                        }
    
                    }
                    if(str.includes("B"))
                    {
                        ind = str.indexOf("B");
                        if (ind == 0) {
                            newstr = str[1] + str[1] + str[1]+ str[1];
                            nums[i] = newstr;
                          
                        }
                        else {
                            newstr = str[0] + str[0] + str[0]+ str[0];
                            nums[i] = newstr;
                            
                        }
                    }
                }
                
                if (i == nums.length - 1)
                {
                    amtlist = amtlist + numberList1[1];
                    finalnumlist = finalnumlist + nums[i];
                }
                else
                {
                    amtlist = amtlist + numberList1[1] + ",";
                    finalnumlist = finalnumlist + nums[i] + ",";
                }
            }



           


            var newRow = document.getElementById('addtrn').insertRow();
            newRow.innerHTML =
                '<tr><th><input type="text" class="form-control" name="trn_number[]" value="' +
                finalnumlist +
                '" placeholder="Number" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" onkeyup="checkshift(this)"></th> <th><input type="text" class="form-control" value="' +
               amtlist +
                '" name="trn_amount[]" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" placeholder="Amount" ></th><td><span class="delete" onClick="$(this).parent().parent().remove();"> X </span></td></tr>';

            
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
      

    
        <br />
        <asp:TextBox ID="TextBox1" runat="server" TextMode="MultiLine" Rows="10" Width="400" ></asp:TextBox>

        <br />
       

        <input type ="button" onclick="Mycode()" value="Check Data"/>
        <asp:Button ID="Bntcheck" runat="server" Text="Check" OnClientClick="return Mycode()"  />
       
       
        <asp:GridView ID="GridView1" runat="server"></asp:GridView>
    
    </div>
    </form>
</body>
</html>
