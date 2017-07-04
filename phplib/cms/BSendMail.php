<?php
    
//    require_once ("Mail.php");
//    require_once ("Mail/mime.php");
    
    class BSendMail
    {
        protected $body;
        protected $headers;
        protected $subject;
        protected $sender_email;
        protected $html;
        protected $max_allowed_file_size = 15000; // size in KB
        protected $mime;
        protected $path_of_uploaded_file;
        protected $smtpinfo;
        protected $text;
        protected $wasUploaded = false;
        
        
        function __construct($recipients, $from, $returnPath, $subject, $message)
        {
            // mail setup recipients, subject etc
            $this->recipients = $recipients;
            
            $headers = array('From'          => $from,
                             'Return-Path'   => $returnPath,
                             'Subject'       => $subject,
                             'Content-Type'  => 'text/html; charset=UTF-8'
                             );

            $mime_params = array(
                                'text_encoding' => '7bit',
                                'text_charset'  => 'UTF-8',
                                'html_charset'  => 'UTF-8',
                                'head_charset'  => 'UTF-8'
                                );
            
            //$this->mime = new Mail_mime();
            //$this->html = '<html><body>'.$message.'</body></html>';
            $this->text = $message;
            $this->subject = $subject;
            $this->sender_email = $from;            
            
            /*
            $this->mime->setTXTBody($this->text);
            $this->mime->setHTMLBody($this->html);
            $this->body = $this->mime->get($mime_params);
            $this->headers = $this->mime->headers($headers);
            */
        }
        
        
        function setSmtpInfo($host, $port, $auth, $userName, $passw)
        {
            // SMTP server name, port, user/passwd
            $this->smtpinfo["host"] = $host;
            $this->smtpinfo["port"] = $port;
            $this->smtpinfo["auth"] = $auth;
            $this->smtpinfo["username"] = $userName;
            $this->smtpinfo["password"] = $passw;
        }
        
        
        function getAttachment($formName, $tmpUplPath)
        {
        	/*
            // attachment
            //Get the uploaded file information
            $name_of_uploaded_file = basename($_FILES[$formName]['name']);
            
            //get the file extension of the file
            $type_of_uploaded_file = substr($name_of_uploaded_file, strrpos($name_of_uploaded_file, '.') + 1);
            $size_of_uploaded_file = $_FILES[$formName]["size"]/1024;//size in KBs
            
            // Validations
            if($size_of_uploaded_file > $this->max_allowed_file_size )
                print "<script type='text/javascript'>alert('Size of file should be less than $this->max_allowed_file_size');</script>";
            
            //copy the temp. uploaded file to uploads folder
            $this->path_of_uploaded_file = $tmpUplPath."/".$name_of_uploaded_file;
            $tmp_path = $_FILES[$formName]["tmp_name"];
            
            if (is_uploaded_file($tmp_path))
                if (!copy($tmp_path,$$this->path_of_uploaded_file))
                    $errors .= '\n error while copying the uploaded file';
            
            chmod( $this->path_of_uploaded_file, 0775 );
            
            $this->mime->addAttachment($this->path_of_uploaded_file);
            $this->wasUploaded = true;
            */
        }
        
        
        function send($echoState=true)
        {
        	/*
            $mail_object =& Mail::factory('smtp', $this->smtpinfo);
            $success = $mail_object->send($this->recipients, $this->headers, $this->body);
            */

        	$headers = "From: ".$this->sender_email;

            if (mail($this->recipients, $this->subject, $this->text, $headers))
            {
            	print "<script type='text/javascript'>alert('Vielen Dank! Ihre Email wurde versendet!');</script>";
            } else {
                print "<script type='text/javascript'>alert('Error!');</script>";
            }
            
//            if ($this->wasUploaded)
//                unlink($this->path_of_uploaded_file);

        }
    }
?>
