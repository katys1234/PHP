<?php

namespace common;

use mysqli;

class db_helper
{
    private mysqli $ms;
    private static ?db_helper $db = null;
    private function __construct()
    {
        $this->ms = new mysqli("localhost", "root", "", "edu", 3306);
    }

    public static function get_instance(): ?db_helper
    {
        if (self::$db === null)
            self::$db = new db_helper();
        return self::$db;
    }

    public function add_user($login, $password_hash, $name): bool{
        if (!isset($login) || mb_strlen(trim($login))==0){
            return false;
        }
        if (!$this->user_exists($login)){
            try {
                $this->ms->begin_transaction(name:"add_user");
                $stmt = $this->ms->prepare("INSERT INTO `users` (login, password, name) VALUES (?, ?, ?)");
                if ($stmt === false)
                    throw new \Exception("Ошибка подготовки запроса");
                if (!$stmt->bind_param("sss", $login, $password_hash, $name))
                    throw new \Exception("Ошибка связывания параметров");
                if (!$stmt->execute())
                    throw new \Exception("Ошибка выполнения запроса");
                $this->ms->commit(name:"add_user");
                return true;
            } catch (\Exception $e){
                $this->ms->rollback(name:"add_user");
                return false;
            }
        }
        return false;
    }

    public function get_user($login): int|null
    {
        if (!isset($login) || mb_strlen(trim($login))==0){
            return null;
        }
        $stmt = $this->ms->prepare("SELECT `id` FROM `users` WHERE `login`=?");
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_NUM);
        $res = $row[0];
        $result->close();
        $stmt->close();
        return $res;
    }

    public function add_order(int $user_id, int $order_id, int $product_id) : bool
    {
        if(!isset($user_id) || !isset($order_id) || !isset($product_id)){
            return false;
        }
        try {
            $this->ms->begin_transaction(name:"add_order");
            if($this->is_order_content_exists($user_id, $order_id, $product_id))
            {
                $stmt = $this->ms->prepare("UPDATE `orders` SET `count`= ? WHERE `user_id`= ? AND `order_id` = ? AND `product_id` = ?");
                $order_count = $this->get_product_in_order_count($user_id, $order_id, $product_id)+1;
                if ($stmt === false)
                    throw new \Exception("Ошибка подготовки запроса");
                if(!$stmt->bind_param("iiii", $order_count, $user_id, $order_id, $product_id))
                    throw new \Exception("Ошибка связывания параметров");
            }
            else
            {
                $stmt = $this->ms->prepare("INSERT INTO `orders` (user_id, order_id, product_id) VALUES (?, ?, ?)");
                if ($stmt === false)
                    throw new \Exception("Ошибка подготовки запроса");
                if (!$stmt->bind_param("iii", $user_id, $order_id, $product_id))
                    throw new \Exception("Ошибка связывания параметров");
            }
            if (!$stmt->execute())
                throw new \Exception("Ошибка выполнения запроса");
            $this->ms->commit(name:"add_order");
            return true;
        } catch (\Exception $e){
            $this->ms->rollback(name:"add_order");
            return false;
        }
    }

    public function get_order_content(int $user_id) : array
    {
        $stmt = $this->ms->prepare("SELECT `goods`.`id`, `name`, `price`, `image`, `order_id`, `count` FROM `goods` INNER JOIN `orders` ON `goods`.`id` = `product_id` WHERE `user_id` = ? AND `is_open` = 1 AND `count` != 0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $res = array();
        while($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $res[] = $row;
        }
        $result->close();
        $stmt->close();
        return $res;
    }


    public function user_exists($login): bool
    {
        if (!isset($login) || mb_strlen(trim($login))==0){
            return false;
        }
        $stmt = $this->ms->prepare("SELECT COUNT(login) FROM `users` WHERE `login`=?");
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_NUM);
        $res = $row[0];
        $result->close();
        $stmt->close();
        return $res > 0;
    }

    private function get_user_pass($user): string | null {
        $stmt = $this->ms->prepare("SELECT `password` FROM `users` WHERE `login`=?");
        $stmt->bind_param('s', $user);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $res = $row['password'];
        $result->close();
        $stmt->close();
        return $res;
    }

    public function get_amount_records_from_goods_chosen (string $productName, float $price_start, float $price_end): int{
        $productName = '%'.$productName.'%';
        $stmt = $this->ms->prepare("SELECT COUNT(*) FROM `goods`WHERE `name` LIKE ? AND `price`>=? AND `price`<=? ORDER BY price ASC");
        $stmt->bind_param('sdd', $productName, $price_start, $price_end);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_NUM);
        $res = $row[0];
        $result->close();
        $stmt->close();
        return $res;
    }

    public function get_records_according_search (string $productName, float $price_start, float $price_end, string $flag):array {
        $res_total = [];
        $productName = '%'.$productName.'%';
        if ($flag == 'not_chosen')
            $stmt = $this->ms->prepare("SELECT * FROM `goods` WHERE `name` LIKE ? AND `price`>=? AND `price`<=? ORDER BY price ASC");
        else if ($flag == 'up')
            $stmt = $this->ms->prepare("SELECT * FROM `goods` WHERE `name` LIKE ? AND `price`>=? AND `price`<=? ORDER BY rating ASC");
        else
            $stmt = $this->ms->prepare("SELECT * FROM `goods` WHERE `name` LIKE ? AND `price`>=? AND `price`<=? ORDER BY rating DESC");
        $stmt->bind_param('sdd', $productName, $price_start, $price_end);
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_array(MYSQLI_ASSOC)){  
            $res_row = [];
            $res_row['id'] = $row['id'];
            $res_row['name'] = $row['name'];
            $res_row['price'] = $row['price'];
            $res_row ['image'] = $row['image'];
            $res_row['quantity'] = $row['quantity'];
            $res_row['rating'] = $row['rating'];
            $res_row['description'] = $row['description'];
            $res_row ['number_of_purchases'] = $row['number_of_purchases'];
            $res_total[]=$res_row;
            
            }    
        $result->close();
        $stmt->close();
        return $res_total;
    }
    

    public function auth_ok(string $user, string $pass): bool{
        if (!(mb_strlen($user) > 0 && mb_strlen($pass) > 0)) return false;
        if (!$this->user_exists($user)) return false;
        return password_verify($pass, $this->get_user_pass($user) ?? '');
    }

    public function get_products(): array
    {
        $stmt = $this->ms->prepare("SELECT * FROM `goods`");
        $stmt->execute();
        $result = $stmt->get_result();
        $res = array();
        while($row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $res[] = $row;
        }
        $result->close();
        $stmt->close();
        return $res;
    }
    public function is_order_content_exists(int $user_id, int $order_id, int $product_id) : bool
    {
        $stmt = $this->ms->prepare("SELECT COUNT(id) FROM `orders` WHERE `user_id`=? AND `order_id` = ? AND `product_id` = ?");
        $stmt->bind_param('iii', $user_id, $order_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_NUM);
        $res = $row[0];
        $result->close();
        $stmt->close();
        return $res > 0;
    }

    public function get_product_in_order_count(int $user_id, int $order_id, int $product_id) : int
    {
        $stmt = $this->ms->prepare("SELECT `count` FROM `orders` WHERE `user_id` = ? AND `order_id` = ? AND `product_id` = ?");
        $stmt->bind_param("iii", $user_id, $order_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        return $row['count'] ?? 0;
    }

    public function get_product_count(int $id) : int
    {
        $stmt = $this->ms->prepare("SELECT `quantity` FROM `goods` WHERE `id` = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_array(MYSQLI_ASSOC);
        return $row['quantity'] ?? 0;
    }


    public function status_order (int $user_id, int $order_id)
    {
            $stmt = $this->ms->prepare("UPDATE `orders` SET `is_open`= 0 WHERE `user_id`= ? AND `order_id` = ?");
            $stmt->bind_param("ii", $user_id, $order_id);
            $stmt->execute();
    }
    public function reduce_the_number_of_goods (int $user_id, int $order_id, int $product_id) 
    {
        $stmt = $this->ms->prepare("UPDATE `orders` SET `count`= ? WHERE `user_id`= ? AND `order_id` = ? AND `product_id` = ?");
        $order_count = $this->get_product_in_order_count($user_id, $order_id, $product_id)-1;
        if ($order_count<=0)
        {
            $order_count=0;
        }
        $stmt->bind_param("iiii", $order_count, $user_id, $order_id, $product_id);
        $stmt->execute();
    }
    public function increase_the_number_of_goods (int $user_id, int $order_id, int $product_id) 
    {
        $stmt = $this->ms->prepare("UPDATE `orders` SET `count`= ? WHERE `user_id`= ? AND `order_id` = ? AND `product_id` = ?");
        $order_count = $this->get_product_in_order_count($user_id, $order_id, $product_id)+1;
        if ($order_count<=0)
        {
            $order_count=0;
        }
        $stmt->bind_param("iiii", $order_count, $user_id, $order_id, $product_id);
        $stmt->execute();
    }
    public function correct_quantity (int $user_id, int $order_id, int $product_id)
    {
        $stmt = $this->ms->prepare("UPDATE `orders` SET `count`= ? WHERE `user_id`= ? AND `order_id` = ? AND `product_id` = ?");
        $order_count = $this->get_product_count ($product_id);
        $stmt->bind_param("iiii", $order_count, $user_id, $order_id, $product_id);
        $stmt->execute();
    }
}