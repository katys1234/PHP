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
        ?>
        <div class="col text-center">
            <p style="background-color: #ffffff; color: black;"><br>Какой праздник без десерта? Конечно, никакой! И взрослые, и дети искренне любят сладости. <br> Но любите ли вы их готовить самостоятельно? <b> Msweety Rabbits </b> с радостью возьмет на себя все хлопоты, связанные с приготовлением кульминации вашего вечера: авторского торта. <br> Представьте, какие яркие эмоции вы испытаете, когда на Вашем праздничном столе окажется уникальный торт на заказ.<br></br></p>
        </div>
            <div class="row align-items-center">
    <div class="col">
    <div class="card" style="width: 28rem;">
							<img src="mai_1.jpg" class="card-img-top">
						<div class="card-body">
							<p class="card-text" style="font-family: Segoe Script;text-align: center;">Вкусно</p>
						</div>
						</div> 
    </div>
    <div class="col">
    <div class="card" style="width: 28rem;">
							<img src="mai4_1.jpg" class="card-img-top">
						<div class="card-body">
							<p class="card-text" style="font-family: Segoe Script; text-align: center;">Качественно</p>
						</div>
						</div>
    </div>
    <div class="col">
    <div class="card" style="width: 28rem;">
							<img src="mai3_1.jpg" class="card-img-top">
						<div class="card-body">
							<p class="card-text" style="font-family: Segoe Script; text-align: center;">С любовью ♥</p>
						</div>
						</div>
    </div>
  </div>                                                                 

        <?php
    }
}

$content = new index();
new \common\page($content);