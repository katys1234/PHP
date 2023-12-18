<?php

require_once ("common/page.php");
require_once ("common/a_content.php");

class rebus {
    private array $perm;
    private string $rebus_str;
    public function __construct(string $str)
    {
        $this->perm=range(0,9);
        $this->rebus_str=mb_strtoupper(str_replace("=","==",$str));

    }
    private function nextPermutation(): bool{
        $i= count($this->perm)-2;
        while ($i>=0 && $this->perm[$i] >= $this->perm[$i+1]){
            $i--;
        }
        if($i<0) return false;
        $j=count($this->perm)-1;
        while ( $this->perm[$j]<=$this->perm[$i]){
            $j--;
        }
        $this->swap($i,$j);

        $this->reverse($i+1);
        return true;

    }
    private function reverse ($from_index){
        $l=$from_index;
        $r=count($this->perm)-1;
        while ($l<$r){
            $this->swap($l,$r);
            $l++;
            $r--;
        }
    }
    private function swap($i,$j){
        $temp=$this->perm[$i];
        $this->perm[$i]=$this->perm[$j];
        $this->perm[$j]=$temp;
    }
    public function solve(): array{
        //Шаг1. Соответствие формату
        $flag=mb_ereg_match("[+-]?[A-ZА-Я]+(?:[-+*\/][A-ZА-Я]+)*==[+-]?[A-ZА-Я]+(?:[-+*\/][A-ZА-Я]+)*",$this->rebus_str);
        if (!$flag){
            throw new ErrorException('Строка имеет неверный формат');
        }
        //Шаг2. Проверка на допустимое количество уникальных букв

        $alphabet=array_unique( mb_str_split( mb_ereg_replace("[^A-ZА-Я]", "", $this->rebus_str)));
        $n=count($alphabet);
        if ($n>10||$n<1){
            throw new ErrorException('В строке слишком много различных букв');
        }
        //Шаг3. Решение Ребуса
        $result=array();
        $dict=array();
        do  {
            $old_dict=$dict;
            $dict=array_combine($alphabet,array_slice($this->perm,0,$n));
            if($old_dict===$dict) continue;
            $rebus_digits=strtr($this->rebus_str,$dict);
            $rebus_digits=mb_ereg_replace('\b(?:0+)([1-9]\d*)','\1',$rebus_digits);
            //print ("return $rebus_digits;");
            if(eval("return $rebus_digits;")){
                 $result[]=$rebus_digits;
            }

        } while ($this->nextPermutation());
        return $result;

    }
}

class rebus_page extends \common\a_content {
    public function show_content(): void{
        $r= new rebus('ЛисА+Волк=Звери');
        $result = $r->solve();
        if (count($result)>0) print_r ($result);
    }
}

$content = new rebus_page();
new \common\page($content);