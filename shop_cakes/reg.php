<?php

require_once ("common/page.php");
require_once ("common/a_content.php");
require_once ("common/db_helper.php");
enum error_type{
    case ok;
    case pass_defferent;
    case pass_incorrect_len;
    case pass_incorrect_content;
    case login_incorrect_len;
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
        if (!\common\db_helper::get_instance()->add_user(
            $this->raw_login,
            password_hash($this->raw_password, PASSWORD_DEFAULT),
            $this->raw_username
        )) $this->check_res[] = error_type::reg_error;
        else {
            $_SESSION['user'] = $this->raw_login;
            header("Location: index.php");
        }
    }

    private function is_correct_len(string $s, int $min, int $max):bool
    {
        return $min <= mb_strlen($s) && mb_strlen($s) <= $max;
    }

    private function is_correct_content(string $s):bool{
        return preg_match('/^[A-Z0-9_@.-]+$/iu',$s)===1;
    }

    private function check_user_data():array{
        $res = array();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->raw_username = $user =  (isset($_POST['username']))?htmlspecialchars($_POST['username']):"";
            $this->raw_login = $login = (isset($_POST['login']))?htmlspecialchars($_POST['login']):"";
            $this->raw_password = $pass =  (isset($_POST['password']))?htmlspecialchars($_POST['password']):"";
            $this->raw_password2 = $pass2 = (isset($_POST['password2']))?htmlspecialchars($_POST['password2']):"";
            if (!$this -> is_correct_len($user, 2,50)){
                $res[] = error_type::name_incorrect_len;
            }
            if (!$this -> is_correct_len($login, 4,25)){
                $res[] = error_type::login_incorrect_len;
            }
            if (!$this -> is_correct_content($login)){
                $res[] = error_type::login_incorrect_content;
            }
            if (\common\db_helper::get_instance()->user_exists($login)){
                $res[] = error_type::login_exists;
            }
            if ($pass!==$pass2){
                $res[] = error_type::pass_defferent;
            }
            if (!$this -> is_correct_len($pass, 8,25)){
                $res[] = error_type::pass_incorrect_len;
            }
            if (!$this -> is_correct_content($pass)){
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
            error_type::login_incorrect_len => 'Логин должен содержать от 4 до 25 символов.',
            error_type::login_incorrect_content => 'Логин может содержать только буквы английского алфавита, цифры и знаки ".", "-", "@", "_".',
            error_type::login_exists => 'Пользователь с таким логином уже существует. Придумайте другой логин.',
            error_type::name_incorrect_len => 'Имя пользователя должно содержать от 2 до 50 символов.',
            error_type::pass_defferent => 'Введенные пароли не совпадают.',
            error_type::pass_incorrect_len => 'Пароль должен содержать от 8 до 25 символов.',
            error_type::pass_incorrect_content => 'Пароль может содержать только буквы английского алфавита, цифры и знаки ".", "-", "@", "_".',
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
                        <label for="username" class="text-center">Ваше имя:</label>
                    </div>
                    <div class="col align-self-center">
                        <input class="form-control form-control-md" type="text" value="<?php print $this->raw_username;?>" placeholder="Введите ваше имя" name="username" id="username">
                    </div>
                </div>

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
