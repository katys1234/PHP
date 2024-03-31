<?php
require_once ("common/page.php");
require_once ("common/a_content.php");

class index extends \common\a_content {
    public function __construct()
    {
        $this->isProtected = false;
        parent::__construct();
    }

    public function show_content(): void{
        if (isset($_SESSION['user']) && isset($_SESSION['status']))
        {
            $json = file_get_contents("access_token.json");
            $obj = json_decode($json,true);
            print('Вы пользователь с id равным '.$obj['user_id'].' и статусом '.$_SESSION['status']);
        }
    }
}

$content = new index();
new \common\page($content);