using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

public partial class _Default : System.Web.UI.Page
{
    List <string> amtlist;// = new Dictionary<int, decimal>();
    protected void Page_Load(object sender, EventArgs e)
    {
        amtlist = new List<String>();
    }

    protected void TextBox1_TextChanged(object sender, EventArgs e)
    {
        
        string inputstr = TextBox1.Text;
        inputstr = inputstr.ToLower();
        inputstr=inputstr.Replace("into", "*");
        inputstr = inputstr.Replace("in", "*");
        inputstr = inputstr.Replace("(", "*");
        inputstr = inputstr.Replace(")", "");
        inputstr = inputstr.Replace("=", "*");
        inputstr = inputstr.Replace(".", ",");
        inputstr = inputstr.Replace(".", ",");
        inputstr = inputstr.Replace("/", ",");
        inputstr = inputstr.Replace("!", ",");
        inputstr = inputstr.Replace("^", ",");
        inputstr = inputstr.Replace(" ", ",");
        inputstr = inputstr.Replace("'", ",");
        TextBox1.Text = inputstr;
        
        
    }
    void ConvertIntDic()
    {
        char[] ch = { '\n', '\r' };
        char[] chstar = { '*',',' };
        string[] mainList = TextBox1.Text.Split(ch);
        
        foreach (string str in mainList)
        {
            if (str != "")
            {
                string []numarray = str.Split(chstar);
                int i = 0,num=0;
                decimal amt=0;
                if(!decimal.TryParse(numarray[numarray.Length-1],out amt))
                {
                    Label1.Text=numarray[numarray.Length-1] + " Amount is not valid";
                }
                for(i=0;i<numarray.Length-1;i++)
                {
                    if(!int.TryParse(numarray[i],out num))
                    {
                        Label1.Text=numarray[i]+" not valid number";
                    }
                    try
                    {
                        if(numarray[i]!="")
                        amtlist.Add(num.ToString()+","+ amt.ToString());
                    }
                    catch(Exception e)
                    {
                        Label1.Text = "One number only one Time" + num.ToString() + " multiple entry";
                    }
                }

            }


        }
    }


    
    bool CheckEntryIsValid(string inputstr)
    {
        char[] ch = { '\n', '\r' };
        char[] chstar = { '*' };
        char[] chcoma = { ',' };
        decimal amount = 0;
        int Linenumber = 0;
        Label1.Text = "";
        
        string[] mainList = TextBox1.Text.Split(ch);
        DropDownList1.Items.Clear();
        foreach (string str in mainList)
        {
            if (str != "")
            {
                Linenumber++;
                string[] striner = str.Split(chstar);
                int length = striner.Length;
                if (length > 2)
                {
                    ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('[" + "str" + "] is not vaid Entey \n Line number "+Linenumber.ToString()+"')", true);
                    Label1.Text =str + "] is not vaid Entey \n Line number "+Linenumber.ToString();
                    return false;
                }
                                                                                        //-------------------------------------------------Each number length should be maximum 2 digit


                string[] inernumberList = striner[0].Split(chcoma);
                foreach(string num in inernumberList)
                {
                    if(num.Length>2)
                        
                        {
                            ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('[" + num.ToString() + "] is not vaid Entey more then 2 digit  Line Number:-"+Linenumber.ToString()+"')", true);
                            Label1.Text = num.ToString() + "] is not vaid Entey more then 2 digit  Line Number:-" + Linenumber.ToString();
                            return false;
                        }

                }


                //-------------------------------------------------------------------------------------------------------------------------End of Section number length should be maximum 2 digit



                string stramount = striner[length - 1];
                if (!checkAmountIsValid(stramount))
                {
                    ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('[" + stramount.ToString() + "] Not Valid number \n Line Number :-" + Linenumber.ToString() + "')", true);
                         
              //      ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('" + stramount + "Not Valid number \n Line Number :-"+Linenumber.ToString()+"')", true);
                    Label1.Text = stramount + "Not Valid number \n Line Number :-" + Linenumber.ToString();
                    return false; ;
                }
                decimal.TryParse(stramount, out amount);
                if (amount > 10000)
                {
                    ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('[" + amount.ToString() + "] not allow Maximum amount limit 10,000 only \n line number :-" + Linenumber.ToString() + "')", true);
                   // ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('" + amount.ToString() + "not allow Maximum amount limit 10,000 only \n line number :- "+Linenumber.ToString()+"')", true);
                    Label1.Text = amount.ToString() + "not allow Maximum amount limit 10,000 only \n line number :- " + Linenumber.ToString();
                    return false; 
                }
            }
        }
        return true;
    }

    bool checkAmountIsValid(string str)
    {
        char[] chstar = { ',' };
        string[] amtstring = str.Split(chstar);
        if (amtstring.Length > 1)
            return false;
        return true;
    }
    protected void Bntcheck_Click(object sender, EventArgs e)
    {
        string inputstr = TextBox1.Text;
       
        CheckEntryIsValid(inputstr);
        CheckAksahrValidate();
    }
    protected void bntSubmit_Click(object sender, EventArgs e)
    {
        ConvertIntDic();
        ConvertIntDicAkshar();
        GridView1.DataSource = amtlist;
        GridView1.DataBind();

    }

    //----------------------------------------------------------------------------------------------------------------------------------------------------akhar
    protected void TextBox2_TextChanged(object sender, EventArgs e)
    {
        string inputstr = TextBox2.Text;
        inputstr = inputstr.ToLower();
        inputstr = inputstr.Replace("into", "*");
        inputstr = inputstr.Replace("in", "*");
        inputstr = inputstr.Replace("(", "*");
        inputstr = inputstr.Replace(")", "");
        inputstr = inputstr.Replace("=", "*");
        inputstr = inputstr.Replace(".", ",");
        inputstr = inputstr.Replace(".", ",");
        inputstr = inputstr.Replace("/", ",");
        inputstr = inputstr.Replace("!", ",");
        inputstr = inputstr.Replace("^", ",");
        inputstr = inputstr.Replace(" ", ",");
        inputstr = inputstr.Replace("'", ",");
        inputstr = inputstr.ToUpper();
        TextBox2.Text = inputstr;


    }

    bool CheckAksahrValidate()
    {
        string str1 = TextBox2.Text;

         char[] ch = { '\n', '\r' };
        char[] chstar = { '*' };
        char[] chcoma = { ',' };
        decimal amount = 0;
        int Linenumber = 0;
        Label1.Text = "";

        string[] mainList = str1.Split(ch);
        DropDownList1.Items.Clear();
        foreach (string str in mainList)
        {
            if (str != "")
            {
                Linenumber++;
                string []striner = str.Split(chstar);
                int length = striner.Length;
                if (length > 2)
                {
                    ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('[" + "str" + "] is not vaid Entey \n Line number " + Linenumber.ToString() + "')", true);
                    Label1.Text = str + "] is not vaid Entey \n Line number " + Linenumber.ToString();
                    return false;
                }
                string[] inernumberList = striner[0].Split(chcoma);
                foreach(string Akshar in inernumberList)
                {
                    if(Akshar.Length==2)
                    {
                        string alpharesult="";
                        if (!Ifalpabatefound(Akshar, ref alpharesult))
                        {

                         str1= str1.Replace(Akshar, alpharesult);
                        }

                    }
                   else if(Akshar.Length==3)
                    {
                       if(!IsAnyNonDigit(Akshar))
                       {
                           ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('[" + "Akshar" + "] is not vaid Entey \n Line number " + Linenumber.ToString() + "')", true);
                           Label1.Text = Akshar + "] is not vaid Entey \n Line number " + Linenumber.ToString();
                           return false;
                       }
                    }
                    else if(Akshar.Length==4)
                    {
                        if (!IsAnyNonDigit(Akshar))
                        {
                            ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('[" + "Akshar" + "] is not vaid Entey \n Line number " + Linenumber.ToString() + "')", true);
                            Label1.Text = Akshar + "] is not vaid Entey \n Line number " + Linenumber.ToString();
                            return false;
                        }

                    }
                    else
                    {
                        ScriptManager.RegisterClientScriptBlock(this, this.GetType(), "alertMessage", "alert('[" + "Akshar" + "] is not vaid Entey \n Line number " + Linenumber.ToString() + "')", true);
                        Label1.Text = Akshar + "] is not vaid Entey \n Line number " + Linenumber.ToString();
                        return false;
                    }
                }
            }

          
               
        }
        TextBox2.Text = str1;
        return true;
    }

    bool IsAnyNonDigit(string str)
    {
        foreach(char c in str)
        {
            int asval = (int)(c);
            if (c < 48 || c > 57)
                return false;

        }

        return true;
    }


    bool Ifalpabatefound(string str, ref string alpharesult)
    {
        int ind=-12;
        string newstr="";
        ind = str.IndexOf('A');
        if(ind!=-1)
        {
           
            if(ind == 0)
            {
                alpharesult = str[1].ToString() + str[1].ToString() + str[1].ToString(); 
            }
            else
            {
                alpharesult = str[0].ToString() + str[0].ToString() + str[0].ToString(); 
            }
            return false;
        }
        ind = str.IndexOf('B');
        if(ind!=-1)
        {
            if (ind == 0)
            {
                alpharesult = str[1].ToString() + str[1].ToString() + str[1].ToString() + str[1].ToString();
            }
            else
            {
                alpharesult = str[0].ToString() + str[0].ToString() + str[0].ToString() + str[0].ToString();
            }
            return false;
        }
       
        return true;
    }

    void ConvertIntDicAkshar()
    {
        char[] ch = { '\n', '\r' };
        char[] chstar = { '*', ',' };
        string[] mainList = TextBox2.Text.Split(ch);

        foreach (string str in mainList)
        {
            if (str != "")
            {
                string[] numarray = str.Split(chstar);
                int i = 0, num = 0;
                decimal amt = 0;
                if (!decimal.TryParse(numarray[numarray.Length - 1], out amt))
                {
                    Label1.Text = numarray[numarray.Length - 1] + " Amount is not valid";
                }
                for (i = 0; i < numarray.Length - 1; i++)
                {
                    if (!int.TryParse(numarray[i], out num))
                    {
                        Label1.Text = numarray[i] + " not valid number";
                    }
                    try
                    {
                        if (numarray[i] != "")
                            amtlist.Add(num.ToString() + "," + amt.ToString());
                    }
                    catch (Exception e)
                    {
                        Label1.Text = "One number only one Time" + num.ToString() + " multiple entry";
                    }
                }

            }


        }
    }



}