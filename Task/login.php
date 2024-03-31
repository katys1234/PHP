<?php

require_once ("common/page.php");
require_once ("common/a_content.php");
require_once ("common/db_helper.php");
class the_content extends \common\a_content {

    public function __construct(){
        $this->isProtected = false;
        parent::__construct();
        $this->check_user_data();
    }

    private bool $try_login = false;
    private string $raw_user = '';
    private string $raw_password = '';

    private function identify(): bool{
        return \common\db_helper::get_instance()->user_exists($this->raw_user) &&
                \common\db_helper::get_instance()->auth_ok($this->raw_user, $this->raw_password);
    }
    private function check_user_data(): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['logout'])){
                unset($_SESSION['user']);
            } else {
                if (isset($_POST['login'])) {
                    $this->try_login = true;
                    $user = $_POST['login'];
                    $this->raw_user = htmlspecialchars($user);
                    if (isset($_POST['password']))
                        $this->raw_password = htmlspecialchars($_POST['password']);
                    if ($this->identify()) {
                        $_SESSION['user'] = htmlspecialchars($user);
                        $fp = fopen('access_token.json', 'w');
                        $message = array("user_id"=>$_SESSION['id'],"code"=>200);
                        fwrite($fp, json_encode($message, JSON_PRETTY_PRINT));
                        fclose($fp);
                        header("Location: index.php");
                    }
                }
            }
        }
    }

    private function show_login_error(){
        $fp = fopen('access_token.json', 'w');
        $message = array("code"=>"unauthorized");
        fwrite($fp, json_encode($message, JSON_PRETTY_PRINT));
        fclose($fp);
        $json = file_get_contents("access_token.json");
        $obj = json_decode($json,true);
       
        ?>
        <div class="alert alert-danger fw-bold text-center">
        <?php
            print('Неверное имя пользователя или пароль! Ошибка: '.$obj['code']);
        ?>
        </div>
        <?php
    }
    public function show_content(): void
    {
        if (!isset($_SESSION['user'])){
            if ($this->try_login)
                $this->show_login_error();
            ?>
            <div class="m-auto card p-2" style="width: 500px;">
            <form action="login.php" method="post">
                <div class="row p-2 mb-2">
                    <div class="col-2 align-self-center">
                        <label for="login" class="text-center">Логин:</label>
                    </div>
                    <div class="col align-self-center">
                        <input class="form-control form-control-md" type="text" value="<?php print $this->raw_user;?>" placeholder="Введите логин" name="login" id="login">
                    </div>
                </div>

                <div class="row p-2 mb-2">
                    <div class="col-2 align-self-center">
                        <label for="password" class="text-center">Пароль:</label>
                    </div>
                    <div class="col align-self-center">
                        <input class="form-control form-control-md" type="password" value="<?php print $this->raw_password;?>" placeholder="Введите пароль" name="password" id="password">
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
        } else {
            ?>
            <form action="login.php" method="post">
                <div class="m-auto card p-2" style="width: 500px;">
                    <div class="alert alert-success text-center fw-bold">
                        <?php print $_SESSION['user']; ?>, нажмите кнопку ниже, чтобы выйти
                    </div>
                    <div class="row mb-2 mt-4">
                        <div class="col text-center">
                            <input type="hidden" value="logout" name="logout">
                            <input type="submit" class="form-control-color bg-warning w-50" value="Выход">
                        </div>
                    </div>
                </div>
            </form>
            <?php
        }
    }
}

$content = new the_content();
new \common\page($content);
