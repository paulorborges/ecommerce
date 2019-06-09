<?php

    namespace Hcode;
    //require_once("vendor/autoload.php");

    use Rain\Tpl;
  
    class Mailer {
        
        const USERNAME = "yyy";
        const PASSWORD = "yyy";
        const NAME_FROM = "yy";

        private $mail;

        public function __construct ($toAddress, $toName, $subject, $tplName, $data = array()){
            /* para criação do template que será utilizado na recuperação de senha, podemos utilizar o processo de 
            inicialização presente na classe page */
           
            // config
			$config = array(
				"tpl_dir" => $_SERVER["DOCUMENT_ROOT"]."/views/email/",
				"cache_dir" => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
				"debug" => true // set to false to improve the speed
			 );

			Tpl::configure( $config );
            $tpl = new Tpl;
            
            foreach ($data as $key => $value) {
                $tpl->assign($key, $value);
            }

            $html = $tpl -> draw($tplName, true);
            
            //Create a new PHPMailer instance. Por estar no escopo principal, necessário adiconar a contra barra
            $this->mail = new \PHPMailer;
            //$this->mail = new \PHPMailer\PHPMailer\PHPMailer();

            //Tell PHPMailer to use SMTP
            $this->mail->isSMTP();

            //Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
            $this->mail->SMTPDebug = 2;

            //Ask the HTML-friendly debug output
            $this->mail->Debugoutput = 'html';

            //Set the hostname of the mail server
            $this->mail->Host = 'smtp.gmail.com';
            // use
            // $mail->Host = gethostbyname('smtp.gmail.com');
            // if your network does not support SMTP over IPv6

            //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
            $this->mail->Port = 587;

            //Set the encryption system to use - ssl (deprecated) or tls
            $this->mail->SMTPSecure = 'tls';

            //Whether to use SMTP authentication
            $this->mail->SMTPAuth = true;

            //Username to use for SMTP authentication - use full email address for gmail
            $this->mail->Username = Mailer::USERNAME;

            //Password to use for SMTP authentication
            $this->mail->Password = Mailer::PASSWORD;

            //Set who the message is to be sent from
            $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

            //Set an alternative reply-to address
            //$mail->addReplyTo('frisacomunicacao@gmail.com', 'Frisa Comunicação');

            //Set who the message is to be sent to
            $this->mail->addAddress($toAddress, $toName);
            $this->mail->addAddress('frisacomunicacao@gmail.com', 'Frisa Comunicação');

            //Set the subject line
            $this->mail->Subject = $subject;

            //Read an HTML message body from an external file, convert referenced images to embedded,
            //convert HTML into a basic plain-text alternative body
            $this->mail->msgHTML($html);

            //Replace the plain text body with one created manually
            $this->mail->AltBody = 'Texto alternativo em caso do contents não funcionar';
            /*
            if (!$mail->send()){
                echo "Mailer error: ". $mail->ErrorInfo;
            } else {
                echo "Message sent!";
            }*/
        }
        
        public function send(){
            return $this->mail->send();
        }
    }
?>