<?php

/**
 * @author Danilo "DMZK" Metzker
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL v.2
 * @version 1.0
 */


/* Tables > Columns
users >
id(A.I | P.K) | email(varchar[255]) | username(varchar[255]) | password(varchar[255]) | token(varchar[255])

*/


/****************************************/

class AuthSystem{

	private $mysql_host;
	private $mysql_user;
	private $mysql_pass;
	private $mysql_db;
	private $nexus;

	# Static variables:

	var $salt = "3KEGvjggIPxkTvV"; //static salt for hash (change it only once)


	/* Open mysql connection */
	public function __construct($mysql_host, $mysql_user, $mysql_pass, $mysql_db){
		$this->mysql_host = $mysql_host;
		$this->mysql_user = $mysql_user;
		$this->mysql_pass = $mysql_pass;
		$this->mysql_db   = $mysql_db;

		$this->nexus = mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
	}

	public function __destruct(){
		if(is_object($this->nexus)){
			$this->nexus->close();
		}
	}


	/* Register a user (sign up) */
	/* Registra usuário */
	public function userRegister($email, $name, $pass){
		$email = mysqli_real_escape_string($this->nexus, $email);
		$name = mysqli_real_escape_string($this->nexus, $name);
		$pass = mysqli_real_escape_string($this->nexus, $pass);



    //verify existing user
		$sql = "SELECT * FROM users WHERE email = '$email' or username = '$name'";
		$query = mysqli_query($this->nexus, $sql);

		if(mysqli_num_rows($query) > 0){
			return false;
		}else{

			$hashpass = $this->salt . sha1(md5($pass));
			$random1 = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
			$random2 = md5(time());

			$token = $random1.$random2;

			// a chance in a million of 2 users has same token, so, returns false for prevention
			// há uma chance em um milhão de 2 usuarios pegarem o mesmo token, então o registro é cancelado por prevenção
			$query2 = mysqli_query($this->nexus, "SELECT token FROM users WHERE token = '$token'");
			if(mysqli_num_rows($query2) == 0){

				$sql_insert = "INSERT INTO users(email, username, password, token)
						VALUES ('$email', '$name', '$hashpass', '$token')";
				$insert_data = mysqli_query($this->nexus, $sql_insert);

				if($insert_data){
					return true;
				}else{
					return false;
				}

			}else{
				return false;
			}
		}

	}

	/* Logs a user (sign in) */
	/* Efetua login de usuário */
	public function userLogin($name, $pass, $mode = false){
			@session_start();

			$name = mysqli_real_escape_string($this->nexus, $name);
			$pass = mysqli_real_escape_string($this->nexus, $pass);

			$hashpass = $this->salt . sha1(md5($pass));

			$sql = "SELECT * FROM users WHERE username = '$name' and password = '$hashpass'";
			$query = mysqli_query($this->nexus, $sql);

			$hashname = base64_encode($name);

			if(mysqli_num_rows($query) > 0){
				if($mode == true){
					/* Login with cookies */
					setcookie("user", $hashname, time()+3600*24*30);
					return true;
				}else{
					/* Login with sessions */
					$_SESSION["user"] = $hashname;
					return true;
				}
			}else{
				return false;
			}
	}

	/* Is logged verification */
	/* Verifica se usuário está logado */
	public function isLogged(){
		@session_start();
		if(isset($_SESSION['user']) || isset($_COOKIE['user'])){
            return true;
        }else{
            return false;
        }
	}

	/* Logout a user */
	/* Desloga o usuário */
	public function logout(){
		@session_start();
        if(isset($_SESSION['user'])){
            session_destroy();
            return true;
        }elseif(isset($_COOKIE['user'])){
            setcookie('user', null, time() - 0);
            return true;
        }else{
            return false;
        }
    }

    /* This returns username (if $mode = true : return username with echo */
    /* Exibe o nome de usuário (se $modo for true irá retornar o nome via echo) */
    public function getName($mode = false){
    	if(isset($_SESSION['user'])){
    		$name = base64_decode($_SESSION['user']);
    		if($mode === true){
    			echo $name;
    		}else{
            	return $name;
            }
        }elseif(isset($_COOKIE['user'])){
        	$name = base64_decode($_COOKIE['user']);
            if($mode === true){
    			echo $name;
    		}else{
            	return $name;
            }
        }else{
            return false;
        }
    }

}
?>
