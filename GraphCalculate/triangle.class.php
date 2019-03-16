<?php
	class Triangle extends Shape {
		private $side1=0;
		private $side2=0;
		private $side3=0;

		function __construct(){
			if($this->validate($_POST['side1'], '三角形的第一个边')){
				$this->side1=$_POST["side1"];
			}

			if($this->validate($_POST['side2'], '三角形的第二个边')){
				$this->side2=$_POST["side2"];

			}
			if($this->validate($_POST['side3'], '三角形的第三个边')){
				$this->side3=$_POST["side3"];
			}

			if(!$this->validateSum()){
				echo '<font color="red">三角形的两边之和必须大于第三边</font>';
				exit;
			}
		}
		//海伦公式
		function area(){
			$s=( $this->side1+$this->side2+$this->side3 )/2;

			return sqrt( $s * ($s - $this->side1) * ($s - $this->side2) * ($s - $this->side3) );
		}

		function perimeter(){
			return $this->side1+$this->side2+$this->side3;
		}

		private function validateSum(){
			$condition1=($this->side1 + $this->side2) > $this->side3;
			$condition2=($this->side1 + $this->side3) > $this->side2;
			$condition3=($this->side2 + $this->side3) > $this->side1;

			if($condition1 && $condition2 && $condition3) {
				return true;
			}else{
				return false;
			}
		}
	}
