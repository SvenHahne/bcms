<?php
	
	class Point {
		var $x;
		var $y;
		
		function Point ( $_x, $_y ) 
		{			
			$this->x = $_x;
			$this->y = $_y;
		}
		
		function getX() 
		{
			return $this->x;
		}

		function getY() 
		{
			return $this->y;
		}
		
	}

	class Line {
		var $p0;
		var $p1;
		
		// expects Points as Inputs
		function Line ( $_p0, $_p1 ) 
		{			
			$this->p0 = $_p0;
			$this->p1 = $_p1;
		}
		
		function getX ( $inY ) 
		{
			return ( $inY - $this->p0->y ) 
				* ( ( $this->p1->x - $this->p0->x ) / ( $this->p1->y - $this->p0->y ) )
				+ $this->p0->x;
		}
		
		function getPoints () 
		{
			print_r($this->p0);
			print ":";
			print_r($this->p1);
			print "<br>";
		}
	}
	
?>