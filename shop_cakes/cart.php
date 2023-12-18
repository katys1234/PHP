<?php
require_once ("common/page.php");
require_once ("common/a_content.php");
require_once ("common/db_helper.php");
class cart extends \common\a_content
{
    public function __construct()
    {
        parent::__construct();

        if(isset($_POST['little']))
        {
            $pr_id = htmlspecialchars($_POST['little']);
            if(isset($_SESSION['order_id']))
            {
                $order_id = $_SESSION['order_id'];
            }
            else
            {
                $order_id = rand(10000, 100000);
                $_SESSION['order_id'] = $order_id;
            }

            $login = $_SESSION['user'];
            $user_id = \common\db_helper::get_instance()->get_user($login);
            \common\db_helper::get_instance()->reduce_the_number_of_goods($user_id, $_SESSION['order_id'],$pr_id);
            $tot_ord_num = \common\db_helper::get_instance()->get_product_in_order_count($user_id, $_SESSION['order_id'], $pr_id);
            $tot_good_num = \common\db_helper::get_instance()->get_product_count($pr_id);
            if ($tot_ord_num>$tot_good_num)
            {
                \common\db_helper::get_instance()->correct_quantity($user_id, $_SESSION['order_id'], $pr_id);
            }
        }
        if(isset($_POST['big']))
        {
            $pr_id = htmlspecialchars($_POST['big']);
            if(isset($_SESSION['order_id']))
            {
                $order_id = $_SESSION['order_id'];
            }
            else
            {
                $order_id = rand(10000, 100000);
                $_SESSION['order_id'] = $order_id;
            }

            $login = $_SESSION['user'];
            $user_id = \common\db_helper::get_instance()->get_user($login);
            \common\db_helper::get_instance()->increase_the_number_of_goods($user_id, $_SESSION['order_id'],$pr_id);
            $tot_ord_num = \common\db_helper::get_instance()->get_product_in_order_count($user_id, $_SESSION['order_id'], $pr_id);
            $tot_good_num = \common\db_helper::get_instance()->get_product_count($pr_id);
            if ($tot_ord_num>$tot_good_num)
            {
                \common\db_helper::get_instance()->correct_quantity($user_id, $_SESSION['order_id'], $pr_id);
            }
        }
    }
    function show_cart(int $order_id)
    {
        $login = $_SESSION['user'];
        $user_id = \common\db_helper::get_instance()->get_user($login);
        $result = \common\db_helper::get_instance()->get_order_content($user_id);
        ?>
        <div class="container">
                <div class="row align-items-center">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Ваш заказ №: <?php echo $order_id;?></h5>
                        </div>
                    </div>
                    <p></p>
        <?php
        $total = 0;
        foreach($result as $value)
        {
            $tot_ord_num = \common\db_helper::get_instance()->get_product_in_order_count($user_id, $_SESSION['order_id'], $value['id']);
            $tot_good_num = \common\db_helper::get_instance()->get_product_count($value['id']);
            if ($tot_ord_num>$tot_good_num)
            {
                \common\db_helper::get_instance()->correct_quantity($user_id, $_SESSION['order_id'], $value['id']);
            }
            $total_sum_good = (int)$value['price']*(int)$value['count'];
        ?>
        <div class="col">
        <div class="card" style="width: 20rem; height: 35rem;">
                <img src = <?php echo $value['image'];?> class="card-img-top">
                    <div class="card-body">
                        <p class="card-text" style="font-family: Segoe Script;text-align: center;"><?php echo $value['name'];?></p>
                        <p class="card-text" style="text-align: right;"><b><?php echo $value['price'];?></b></p>
                        <p class="card-text" style="text-align: left;"><?php echo 'Количество в корзине: ';?><strong><?php echo $value['count'];?></strong></p>
                        <div class="container text-center">
                        <div class="row align-items-start">
                        <div class="col">
                        <form action="cart.php" method="post">
                                    <input type="hidden" name="little" value='<?php echo"{$value['id']}";?>'>
                                    <input type="submit" class="form-control-color bg-warning w-40" value="-">
                        </form>
                        </div>
                        <div class="col">
                        <form action="cart.php" method="post">
                                    <input type="hidden" name="big" value='<?php echo"{$value['id']}";?>'>
                                    <input type="submit" class="form-control-color bg-warning w-40" value="+">
                        </form>
                        </div>
                        </div>
                        </div>
                        <p class="card-text" style="text-align: left;"><?php echo 'Стоимость товара: ';?><strong><?php echo $total_sum_good.' руб.';?></strong></p> 
                    </div>
        <p></p>
        </div>
        </div>
        
        <?php
        }

    }

    function show_content(): void
    {
        if (isset($_POST['pay']))
        {?>
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Ваш заказ успешно оформлен.</h5>
                        <p class="card-text">Хотите купить что-нибудь еще?</p>
                </div>
            </div>
            <?php
        }
        if (isset($_SESSION['order_id']))
        {
        $login = $_SESSION['user'];
        $user_id = \common\db_helper::get_instance()->get_user($login);
        if (isset($_POST['pay']))
        {
            \common\db_helper::get_instance()->status_order($user_id, $_SESSION['order_id']);
            $order_id = rand(10000, 100000);
            $_SESSION['order_id'] = $order_id;
        }
        $result = \common\db_helper::get_instance()->get_order_content($user_id);
        $total = 0;
        foreach($result as $value)
        {
            $total_sum_good = (int)$value['price']*(int)$value['count'];
            $total += $total_sum_good;
        }
        if ($total !=0)
        {
            $this->show_cart($_SESSION['order_id']);
            ?>
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Итого к оплате: <?php echo $total.' руб.';?></h5>
                        <p class="card-text">Оплата принимается банковской картой.</p>
                </div>
            </div>
            <form action="cart.php" method="post">
            <div class="row mb-2 mt-4">
                    <div class="col text-center">
                            <input type="hidden" value="pay" name="pay">
                            <input type="submit" class="form-control-color bg-warning w-50" value="Оплатить">
                    </div>
            </div>
            </form>
            <?php
            
        }
    }
}
}

$content = new cart();
new \common\page($content);
