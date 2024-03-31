<?php

require_once ("common/page.php");
require_once ("common/a_content.php");
require_once ("common/db_helper.php");
enum error_type{
    case ok;
    case pass_defferent;
    case pass_incorrect_len;
    case pass_incorrect_content;
    case login_exists;
    case login_incorrect_content;
    case name_incorrect_len;
    case reg_error;
}

class the_content extends \common\a_content {

    private string $raw_login = '';
    private string $raw_password = '';
    private string $raw_password2 = '';
    private string $raw_username = '';

    private array $check_res;

    public function __construct(){
        $this->isProtected = false;
        parent::__construct();
        $this->check_res = $this->check_user_data();
        if (count($this->check_res) === 1 && $this->check_res[0] === error_type::ok){
            $this->do_reg();
        }
    }

    private function do_reg(){
        $length = strlen($this->raw_password);
        if ($length>=8 && $length<=12)
        {
            $status = 'good';
        }
        if ($length>12)
        {
            $status = 'perfect';
        }
        if (!\common\db_helper::get_instance()->add_user(
            $this->raw_login,
            password_hash($this->raw_password, PASSWORD_DEFAULT),
            $this->raw_username,
            $status

        )) $this->check_res[] = error_type::reg_error;
        else {
            $_SESSION['user'] = $this->raw_login;
            $t = \common\db_helper::get_instance()->get_user_id($_SESSION['user']);
            $_SESSION['id'] = $t;
            $t1 = \common\db_helper::get_instance()->get_user_status($_SESSION['user']);
            $_SESSION['status'] = $t1;
            $fp = fopen('access_token.json', 'w');
            $message = array("user_id"=>$_SESSION['id'],"code"=>200);
            fwrite($fp, json_encode($message, JSON_PRETTY_PRINT));
            fclose($fp);
            header("Location: index.php");
        }
    }

    private function is_correct_len(string $s, int $min, int $max):bool
    {
        return $min <= mb_strlen($s) && mb_strlen($s) <= $max;
    }

    private function is_correct_content(string $s):bool{
        $pattern = '/^\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,4}\b$/';
        return preg_match($pattern,$s)===1;

    }
    private function is_correct_content_passwd(string $s):bool{
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,20}$/',$s)===1;

    }

    private function check_user_data():array{
        $res = array();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->raw_login = $login = (isset($_POST['login']))?htmlspecialchars($_POST['login']):"";
            $this->raw_password = $pass =  (isset($_POST['password']))?htmlspecialchars($_POST['password']):"";
            $this->raw_password2 = $pass2 = (isset($_POST['password2']))?htmlspecialchars($_POST['password2']):"";
            if (!$this -> is_correct_content($login)){
                $res[] = error_type::login_incorrect_content;
            }
            if (\common\db_helper::get_instance()->user_exists($login)){
                $res[] = error_type::login_exists;
            }
            if ($pass!==$pass2){
                $res[] = error_type::pass_defferent;
            }
            if (!$this -> is_correct_len($pass, 8,20)){
                $res[] = error_type::pass_incorrect_len;
            }
            if (!$this -> is_correct_content_passwd($pass)){
                $res[] = error_type::pass_incorrect_content;
            }
            if (count($res) == 0) {
                $res[] = error_type::ok;
            }
        }
        return $res;
    }

    private function show_error_text(string $msg){
        ?>
        <div class="alert alert-danger fw-bold text-center">
            <?php print $msg;?>
        </div>
        <?php
    }


    private function show_error(error_type $err){
        $msg = match ($err){
            error_type::login_incorrect_content => 'Используйте электронную почту в качестве логина.',
            error_type::login_exists => 'Пользователь с таким логином уже существует. Придумайте другой логин.',
            error_type::pass_defferent => 'Введенные пароли не совпадают.',
            error_type::pass_incorrect_len => 'Пароль должен содержать от 8 до 20 символов.',
            error_type::pass_incorrect_content => 'Пароль должен быть введен английскими буквами. Пароль должен содержать хотя бы одну строчную букву, одну заглавную букву, одну цифру, один специальный символ из: !@#$%^&*.',
            error_type::reg_error => 'Не удалось зарегистрировать пользователя. ;(',
            default => ''
        };
        $this->show_error_text($msg);
    }

    public function show_content(): void
    {
        foreach ($this->check_res as $error){
            if ($error === error_type::ok) continue;
            $this->show_error($error);
        }
        ?>
        <div class="m-auto card p-2" style="width: 500px;">
            <form action="reg.php" method="post">


                <div class="row p-2 mb-2">

                    <div class="col-3 align-self-center">
                        <label for="login" class="text-center">Логин:</label>
                    </div>
                    <div class="col align-self-center">
                        <input class="form-control form-control-md" type="text" value="<?php print $this->raw_login;?>" placeholder="Введите логин" name="login" id="login">
                    </div>
                </div>

                <div class="row p-2 mb-2">
                    <div class="col-3 align-self-center">
                        <label for="password" class="text-center">Пароль:</label>
                    </div>
                    <div class="col align-self-center">
                        <input class="form-control form-control-md" type="password" value="<?php print $this->raw_password;?>" placeholder="Введите пароль" name="password" id="password">
                    </div>
                </div>
                <div class="row p-2 mb-2">
                    <div class="col-3 align-self-center">
                        <label for="password2" class="text-center">Повторите пароль:</label>
                    </div>
                    <div class="col align-self-center">
                        <input class="form-control form-control-md" type="password" value="<?php print $this->raw_password2;?>" placeholder="Введите пароль повторно" name="password2" id="password2">
                    </div>
                </div>

                <div class="row mb-2 mt-4">
                    <div class="col">
                        <input type="submit" class="form-control-color bg-warning w-100">
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
}

$content = new the_content();
new \common\page($content);
