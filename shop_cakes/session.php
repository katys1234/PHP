<?php

require_once ("common/page.php");
require_once ("common/a_content.php");

class the_content extends \common\a_content {

    public function __construct(){
        parent::__construct();
        $this->get_user_data();
    }
    private function get_user_data(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['clear'])){
                unset($_SESSION['some_text']);
            } else {
                if (isset($_POST['some_text'])) $data = $_POST['some_text']; else $data = "";
                $_SESSION['some_text'] = htmlspecialchars($data);
            }
        }
    }
    public function show_content(): void
    {
        $data = '';
        if (isset($_SESSION['some_text'])) $data = $_SESSION['some_text'];
        $hash1 = password_hash($data, PASSWORD_DEFAULT);
        $hash2 = password_hash($data, PASSWORD_DEFAULT);
        print($hash1);
        print "<br/>";
        print($hash2);
        print "<br/>";
        print("1: ".password_verify($data, $hash1));
        print "<br/>";
        print("2: ".password_verify($data, $hash2));
        print "<br/>";
        print("3: ".password_verify("1234567", $hash1));
        print "<br/>";
        ?>
        <form action="session.php" method="post">
            <label>
                <input type="text" value="<?php print $data;?>" placeholder="Введите тут что-нибудь" name="some_text">
            </label>
            <input type="submit">
        </form>
        <form method="post" action="session.php">
            <input type="hidden" name="clear" value="1">
            <input type="submit" value="Очистить">
        </form>
        <?php
    }
}

$content = new the_content();
new \common\page($content);
