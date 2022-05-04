<?php
	$path_to_root = '..';
	$page_security = 'SA_OPEN';
	include_once( "/includes/session.inc");
	$user =  $_SESSION["wa_current_user"]->user;
	$company_name = $_SESSION["wa_current_user"]->name;
	if($_GET['link']){
		$link_active = 1;
		$link = $_GET['link'];
	}
	else{
		$link_active = 0;
	}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>NGICON ERP</title>
        <link href='themes/icon/images/favicon.png' rel='icon' type='image/x-icon'>	
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script> 
        <style type="text/css">
            .login-form {
                width: 340px;
                margin: 50px auto;
            }
            .login-form form {
                margin-bottom: 15px;
                background: #f7f7f7;
                box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
                padding: 30px;
            }
            .login-form h2 {
                margin: 0 0 15px;
            }
            .form-control, .btn {
                min-height: 38px;
                border-radius: 2px;
            }
            .btn {        
                font-size: 15px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <br/><br/><br/>
        <div class="login-form">
            <form name="form" method="POST" action="" class="form-horizontal" >
                <h2 class="text-center">Phone Number</h2>       
                <div class="form-group">
                    <input type="text" name="pNumber"  id="pNumber" class="form-control" placeholder="Phone Number" required="required">
                </div>

                <div class="form-group">                    
                    <input type="submit" class="btn btn-primary btn-block" name="submitBTN" id="submitBTN" value="Submit Button">
                </div>

            </form>
            <?php
             session_start();
           // error_reporting(0);
             $con = mysqli_connect("localhost", "root", "", "db");
             //$con = mysqli_connect("localhost", "myabm_back_user", "Appsit.org1", "myabm_mktgaliv_backoffice");
            if (mysqli_connect_errno()) {
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }

            if (isset($_POST['submitBTN'])) {            // code goes here.
                
                $tbl='useraccount';
                $pNUmber = $_POST['pNumber'];
                $qry = "SELECT * FROM $tbl  WHERE phone_number='$pNUmber'";
				//for user account table.
				$qry = "SELECT * FROM $tbl  WHERE phone='$pNUmber'";
                $excQry = mysqli_query($con, $qry);
                $row = mysqli_fetch_array($excQry);
                $num_rows = mysqli_num_rows($excQry);
                $cName = $row['shop_name'];
                $dbName = $row['db_name'];
                // $info = array('$cName', '$dbName');

                if (!empty($num_rows)) {
                    $_SESSION['token']='allow';                              
                    header('Location: index.php?dbName=' . $dbName . '&cName=' . $cName . '');
                   // header('Location: ../access/login.php?dbName=' . $dbName . '&cName=' . $cName . '');
                    /*
                      $ch = curl_init('test/index.php');
                      curl_setopt($ch, CURLOPT_POST, true);
                      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                      header('Content-Type: text/html');
                      echo curl_exec($ch);
                      curl_close ($ch);
                      header('Location: test/index.php?test=' . $info . '');

                     */
                } else {
                    echo '<div class="alert alert-danger alert-dismissible fade in">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong>Sorry!</strong> You are not a registered user of NGICON ERP. 
					If you are already a registered user, please input your registered mobile number.
                  </div>';
                }
            }
            ?>
        </div>

    </body>
</html>   