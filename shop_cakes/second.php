<?php
require_once ("common/page.php");
require_once ("common/a_content.php");
require_once ("common/db_helper.php");

class pagination {

    private $objects_count;
    private $objects_per_page;
    public function __construct(int $objects_count, int $objects_per_page) {
        $this->objects_count=$objects_count;
        $this->objects_per_page=$objects_per_page;
    }

    public function get_objects_idx_by(int $page_num):array | null {
        $start=min(($page_num-1)*$this->objects_per_page, $this->objects_count);
        $end=min($start+$this->objects_per_page, $this->objects_count)-1;
        if ($start<0 || $start>$end) {
            return null;
        }
        return array($start,$end);
    }

    public function get_page_count(): int{
        return ceil($this->objects_count / $this->objects_per_page);
    }

    public function get_pages(int $current_page): array{
        $max_pages = $this->get_page_count();
        $result = array();
        $start_array = array(1,2,3,"...");
        $end_array = array ("...", $max_pages-2, $max_pages-1, $max_pages);
        $exceptional_array_start = array(3,4,5,6);
        $exceptional_array_end = array($max_pages-5, $max_pages-4, $max_pages-3, $max_pages-2);
        if ($max_pages < 12)
        {
            for($i=1; $i<=$max_pages; $i++)
            {
                $result[] = $i;
            }
        }
        if ($max_pages >= 12) 
        {
             if ($current_page == 1 or $current_page == 2 or $current_page == $max_pages-1 or $current_page == $max_pages)
            {
                array_push($result, 1, 2, 3, "...", $max_pages-2, $max_pages-1, $max_pages);
            }
            else if (in_array($current_page, $exceptional_array_start))
            {
                for($i=1; $i<=$current_page+1; $i++)
                {
                    $result[] = $i;
                }
                $result = array_merge($result, $end_array);    
            }
            else if (in_array($current_page, $exceptional_array_end))
            {
                for($j=$current_page-1; $j<=$max_pages; $j++)
                {
                    $result[] = $j;
                }
                $result = array_merge($start_array, $result);    
            }
            else
            {
                array_push($result, $current_page-1, $current_page, $current_page+1);
                $result = array_merge($start_array, $result, $end_array); 
            }
        }
        return $result;
    }

    public function get_data (int $start, int $finish, string $productName, float $price_start, float $price_end, string $flag): void {
        $t = common\db_helper::get_instance()->get_records_according_search($productName, $price_start, $price_end, $flag);
        for($i=$start; $i<=$finish; $i++){
            $check[$i]='';  
            $login = $_SESSION['user'];
            $user_id = \common\db_helper::get_instance()->get_user($login);
            $tot_ord_num = \common\db_helper::get_instance()->get_product_in_order_count($user_id, $_SESSION['order_id'], $t[$i]['id']);
            $tot_good_num = \common\db_helper::get_instance()->get_product_count($t[$i]['id']);
            if ($tot_ord_num >= $tot_good_num)
            {
                $check[$i] = 'disabled';
            }
        ?> 
        <div class="col">
        <div class="card" style="width: 28rem; height: 42rem;">
        <div style = "position: absolute; text-align: center; opacity: 0.6; background: #FFFFFF;"><?php echo $t[$i]['description'];?></div>
                <img src = <?php echo $t[$i]['image'];?> class="card-img-top">
                    <div class="card-body">
                        <p class="card-text" style="font-family: Segoe Script;text-align: center;"><?php echo $t[$i]['name'];?></p>
                        <p class="card-text" style="text-align: right;"><b><?php echo $t[$i]['price'];?></b></p>
                        <p class="card-text" style="text-align: left;"><?php echo 'В наличии: ';?><strong><?php echo $t[$i]['quantity'];?></strong></p>
                        <p class="card-text" style="text-align: left;"><?php echo 'Рейтинг: '.str_repeat('⭐',(int)$t[$i]['rating']);?></p>
                        <form action="second.php" method="post">
                            <input type="hidden" name="product_id" value="<?php print"{$t[$i]['id']}"?>">
                            <input type="submit" class="form-control-color bg-warning w-100" value="В корзину"<?php echo $check[$i];?> >
                        </form>
                    </div>
        </div> 
        <p></p>
        </div>
        <?php }
    }

}


class second extends \common\a_content {

    public function __construct()
    {
        parent::__construct();
        if(isset($_SESSION['order_id']))
            {
                $order_id = $_SESSION['order_id'];
            }
            else
            {
                $order_id = rand(10000, 100000);
                $_SESSION['order_id'] = $order_id;
            }
        if(isset($_POST['product_id']))
        {
            $product_id = htmlspecialchars($_POST['product_id']);
            $_SESSION['product_id'] = $product_id;
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
            $tot_ord_num = \common\db_helper::get_instance()->get_product_in_order_count($user_id, $_SESSION['order_id'], $product_id);
            $tot_good_num = \common\db_helper::get_instance()->get_product_count($product_id);
            if ($tot_ord_num < $tot_good_num)
            {
                \common\db_helper::get_instance()->add_order($user_id, $order_id, $product_id);
            }
        }
        
    }
    
    public function show_content(): void {
        if (isset($_POST['product_name']))
            {
                $_SESSION['product_name'] = htmlspecialchars($_POST['product_name']);
                $name_of_good = $_SESSION['product_name'];
            }
        else if (isset($_SESSION['product_name']))
            {
                $name_of_good = $_SESSION['product_name'];
            }
        else
            {
                $_SESSION['product_name'] = '';
                $name_of_good = '';
            }
        if (isset($_POST['price_start']) and is_numeric($_POST['price_start']))
            {
                $_SESSION['price_start'] = (float) htmlspecialchars($_POST['price_start']);
                $first_price = $_SESSION['price_start'];
            }
        else if (isset($_SESSION['price_start']))
            {
                $first_price = (float) $_SESSION['price_start'];
            }
        else
            {
                $_SESSION['price_start'] = 0.0;
                $first_price = 0.0;
            }
        if (isset($_POST['price_end']) and is_numeric($_POST['price_end']))
            {
                $_SESSION['price_end'] = (float) htmlspecialchars($_POST['price_end']);
                $second_price = $_SESSION['price_end'];
            }
        else if (isset($_SESSION['price_end']))
            {
                $second_price = (float) $_SESSION['price_end'];
            }
        else
            {
                $_SESSION['price_end'] = 500000.0;
                $second_price = 500000.0;
            }
        if (!isset($_SESSION['status_check_up']))
        {
            $_SESSION['status_check_up'] = '';
        }
        if (!isset($_SESSION['status_check_down']))
        {
            $_SESSION['status_check_down'] = '';
        }
        if (!isset($_SESSION['status_check_not_chosen']))
        {
            $_SESSION['status_check_not_chosen'] = '';
        }
        if (isset($_POST['buy_type']))
                {
                    $_SESSION['buy_type'] = htmlspecialchars($_POST['buy_type']);
                }
                else if (isset($_SESSION['buy_type']))
                {
                    $buy_type = $_SESSION['buy_type'];
                }
                else
                {
                    $_SESSION['buy_type'] = 'not_chosen';
                    $buy_type = 'not_chosen';
                }
                if ($_SESSION['buy_type'] == 'not_chosen')
                    {
                        $flag = 'not_chosen';
                        $_SESSION['status_check_up'] = '';
                        $_SESSION['status_check_down'] = '';
                        $_SESSION['status_check_not_chosen'] = 'checked';
                    }
                else if ($_SESSION['buy_type'] == 'up')
                    {
                        $flag = 'up';
                        $_SESSION['status_check_up']= 'checked';
                        $_SESSION['status_check_down'] = '';
                        $_SESSION['status_check_not_chosen'] = '';
                    }
                else 
                    {
                        $flag = 'down';
                        $_SESSION['status_check_up'] = '';
                        $_SESSION['status_check_down'] = 'checked';
                        $_SESSION['status_check_not_chosen'] = '';
                    }
        ?>
        <form class="d-flex" role="search" name="search" method="post" action="second.php">
            <div class="col">
				<input class="form-control me-2" type="search" placeholder="Я ищу..." aria-label="Search" name="product_name" value="<?php echo $name_of_good;?>">
            </div>
            <div class="col">
				<input type="submit" style="font-size: 1em; background-color: #f5f5f5; border-radius: 10%; padding:8px 15px; width: 150px;" value="Найти"></input>
            </div>
		</form>
        <p></p>
        <form action="second.php" method="post">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col">
                        <div><label style="font-size: middle;" for = ""><b>Цена с:</b><input type="text" style="margin-left: 10px; border: 3pt ridge lightgrey; border-radius: 5px; padding:10px 30px;" name="price_start" placeholder="Введите цену" value = "<?php echo $first_price;?>" required></label></div>
                            <div><br></div>
                        <div><label style="font-size: middle;" for = ""><b>Цена до:</b><input type="text" style="margin-left: 10px; border: 3pt ridge lightgrey; border-radius: 5px; padding:10px 30px;" name="price_end" placeholder="Введите цену" value = "<?php echo $second_price;?>" required></label></div>
                            <div><br></div>
                    </div>
                    <div class="col">
                        <p><b>Сортировать рейтинг товаров:</b></p>
                        <p><input name="buy_type" type="radio" value="up" <?php echo $_SESSION['status_check_up'];?>> По возрастанию</p>
                        <p><input name="buy_type" type="radio" value="down" <?php echo $_SESSION['status_check_down'];?>> По убыванию</p>
                        <p><input name="buy_type" type="radio" value="not_chosen" <?php echo $_SESSION['status_check_not_chosen'];?>> Не выбрано</p>
                    </div>
                    <div class="col">
                        <input type="submit" style="font-size: 1em; background-color: #f5f5f5; border-radius: 10%; padding:8px 15px; width: 150px;" value="ОК" name="done">
                    </div>
                    </div>
                    </div>  
        </form>



        <?php
        ?>
            <div class="row align-items-center">
                <?php
                $amount_records = \common\db_helper::get_instance()->get_amount_records_from_goods_chosen($_SESSION['product_name'], $_SESSION['price_start'], $_SESSION['price_end']);
                if ($amount_records != 0){
                //Здесь можно поменять количество отображаемых элементов на странице
                $page1 = new pagination($amount_records, 3);
                $total_num_pages = $page1->get_page_count();
                $current_page = 1;
                $index_1 = $page1->get_objects_idx_by(1);
                if (array_key_exists('page', $_GET))
                {
                    $current_page = (int)$_GET['page'];
                    try{
                        $index_1 = $page1->get_objects_idx_by($current_page);
                        if ($index_1 == NULL or gettype($current_page)=="string")
                            {
                                throw new ErrorException ('Все плохо');
                            }
                    } catch (Exception $e) {$current_page = 1; $index_1 = $page1->get_objects_idx_by($current_page);}
                } 
                $page1->get_data($index_1[0],$index_1[1], $_SESSION['product_name'], (float)$_SESSION['price_start'], (float)$_SESSION['price_end'], $flag);  
                ?>
                <div class="container text-center">
                <div class="row justify-content-md-center">
                <div class="col-8 col-md-4">
                <ul class='pagination text-center' id="pagination">
                <?php 
                if(!empty($total_num_pages)):
                $list_pagination = $page1->get_pages($current_page);
                for($j=0; $j<=count($list_pagination)-1; $j++):
                    $list_pagination = $page1->get_pages($current_page);
                    if ($list_pagination[$j] == $current_page)
                    {
                        $list_pagination[$j] = '<b>'.(string)$list_pagination[$j].'</b>';
                    }
                    if($list_pagination[$j]==1){
                        ?>
                        <li class='active' id="<?php echo $list_pagination[$j];?>">
                            <a href='second.php?page=<?php echo $list_pagination[$j];?> '><?php echo $list_pagination[$j];?></a>
                        </li> 
                    <?php } else {
                            if (is_numeric($list_pagination[$j])) {?>
                            <li id="<?php echo $list_pagination[$j];?>">
                                <a href='second.php?page=<?php echo $list_pagination[$j];?>'><?php echo $list_pagination[$j];?></a>
                            </li>
                    <?php } else {?> 
                            <li>
                                <a><?php echo $list_pagination[$j];?></a>
                            </li>    
            <?php }} endfor;endif;?>
            </ul>  
        </div>
        </div>
        </div>
    <?php 
    }
    else{
        ?>
                    <div class="container-sm px-6 text-center">
                        <ul class="list-group">
                            <li class="list-group-item list-group-item-danger" style = "font-size: large;"><?php echo 'По данному запросу ничего не найдено.';?></li>
                        </ul>
                        <br>
                    </div>
                <?php 
    }
}
    
}


$content = new second();
new \common\page($content);