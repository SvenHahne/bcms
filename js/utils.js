function veriED()
{
	if(document.pet_email_dir.email.value == "") 
	{
		alert("Please enter your email address");
		document.pet_email_dir.email.focus();
		return false;
	}
	else
		if(document.pet_email_dir.email.value.indexOf('@') == -1) 
		{
			alert("It is not an email address, please check it");
			document.pet_email_dir.email.focus();
			return false;
		}
	
	return true
}

function verificationU()
{
	
	if(document.unsubscriptionForm.email.value == "") {
		alert("Please enter your email address");
		document.unsubscriptionForm.email.focus();
		return false;
	}
	else
		if(document.unsubscriptionForm.email.value.indexOf('@') == -1) {
			alert("It is not an email address, please check it");
			document.unsubscriptionForm.email.focus();
			return false;
		}
	
	return true
}

function changeLang(lang, obj)
{
    var curUrl=document.URL;
    var spltStr=curUrl.split('lang');
    if ( spltStr[0].indexOf("?") == -1 )
    {
        obj.href=spltStr[0]+'?lang='+lang;
    } else {
        obj.href=spltStr[0]+'lang='+lang;
    }
}

function openPadMen()
{
    $('#padMenu').css({"transition":"width 0.4s","width":"160px"});
    $('div.padMenCloseDiv').css({"transition":"width 0.4s","width":"100%"});
}

function closePadMen()
{
    $('#padMenu').css({"transition":"width 0.4s","width":"0px"});
    $('div.padMenCloseDiv').css({"transition":"width 0.4s","width":"0px"});
}


function scaleMenLupe(forceClose)
{
    if ( $("div.headSpacer").attr("title") != "" || forceClose == true )
    {
        $("div.headSpacer").css({"transition":"height 0.4s","height":$("div.headSpacer").attr("title")});
        $("div.headSpacer").prop("title", "");
        $("input.searchfield").css({"display":"none"});
        $("button.conf").hide();
    } else
    {
        $("div.headSpacer").css({"transition":"height 0.4s","height":"100px"});
        $("div.headSpacer").prop("title", $("div.headSpacer").css('height'));
        $("input.searchfield").css({"display":"inline-block"});
        $("button.conf").show(0);
        
        $(document).on('scroll', function(){
                       $("div.headSpacer").css({"transition":"height 0.4s","height":$("div.headSpacer").attr("title")});
                       $("div.headSpacer").prop("title", "");
                       $("input.searchfield").css({"display":"none"});
                       $("button.conf").hide();
                       });
    }
}