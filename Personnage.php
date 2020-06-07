<?php


abstract class Personnage
{

private $_id;
private $_nom;
private $_forcePerso;
private $_degats;
private $_niveau;	
private $_experience;
private $_coups;
private $_date;
private $_timeEndormi;
protected $_type;
private $_atout;


const CESTMOI = 1; 
const FRAPPE= 2; 
const TUE = 3; 
const ILDORT = 4; 


public function __construct($donnees)
    {
      $this->hydrate($donnees);
    }



public function hydrate(array $donnees) 
{

	foreach ($donnees as $key => $value) 
	{
		$method="set".ucfirst($key);

		if (method_exists($this, $method))
		{
			$this->$method($value);
		}
	}



}

public function id()  		 	{return (int) $this->_id;}
public function nom() 		 	{return  $this->_nom;}
public function forcePerso() 	{return (int) $this->_forcePerso;}
public function degats()	 	{return (int) $this->_degats;}
public function niveau()	 	{return (int) $this->_niveau;}
public function experience() 	{return (int) $this->_experience;}
public function coups()      	{return (int) $this->_coups;}
public function date()       	{return $this->_date;}
public function timeEndormi()   {return $this->_timeEndormi;}
public function type()       	{return $this->_type;}
public function atout()     	{return $this->_atout;}

public function setId($id)
{
	$id = (int) $id;

	if ($id > 0)
	{
		$this->_id = $id;
	}

}
public function setNom($nom)
{
	if(is_string($nom))
	{
		$this->_nom = $nom;
	}

}
public function setForcePerso($forcePerso)
{
	
	$forcePerso = (int) $forcePerso;


	if ($forcePerso >= 0 && $forcePerso <= 100 )
	{
		$this->_forcePerso = $forcePerso;
	}
}

public function setDegats($degats)
{

	$degats = (int) $degats;

	$this->_degats = $degats;

}


public function setNiveau($niveau)
{
	$niveau = (int) $niveau;

	if ($niveau > 0 && $niveau <= 100)
	{
		$this->_niveau = $niveau;		
	}
}


public function setExperience($experience)
{

	$experience = (int) $experience;

	if (($experience >= 0) && ($experience <=10))
	{
		$this->_experience = $experience;
	}
	else
	{

		$this->setExperience(1);
		$this->setNiveau($this->niveau() + 1 );
		$this->setForcePerso($this->forcePerso() +2 );
	}

}

public function setCoups($coups)
{
	$this->_coups = $coups;		
}

public function setFdate($date)
{

	$this->_date = $date;
}

public function setTimeEndormi($timeEndormi)
{
	$this->_timeEndormi = $timeEndormi;
}

public function jeterunsort(Personnage $personnage) 
{
	
	$datejour = new DateTime();
	$datejour->add(new DateInterval('PT2H'));

	$datepersonnage = new DateTime($personnage->timeEndormi());
	

	if ( $datepersonnage->format('U') > $datejour->format('U')  )
	{
		
		$this->setCoups($this->coups() + 1);
 		
		return self::ILDORT;

	}


	elseif ($personnage->id() == $this->_id)
	{

 		return self::CESTMOI;
	}

	    else
    {
		

		$atout=$this->_atout;

		$atout = (int) $atout;

		$temps='PT' . (6 * $atout + 2) . 'H';
		
		$date = new DateTime();

		$date->add(new DateInterval($temps));
 
		$someil = $date->format('Y-m-d H:i:s');

		$personnage->_timeEndormi = $someil;

		$this->setCoups($this->coups() + 1);
 		
 		return self::FRAPPE;
    }
}



public function setAtout($atout)
{
	$this->_atout = $atout;
}

public function setType()
{
	static::ecritureType();
}



public function frapper(Personnage $personnage)
{

	$datejour = new DateTime();
	$datejour->add(new DateInterval('PT2H'));

	$datepersonnage = new DateTime($personnage->timeEndormi());
	

	if ( $datepersonnage->format('U') > $datejour->format('U')  )
	{
		
		$this->setCoups($this->coups() + 1);
 		
		return self::ILDORT;

	}




	if ($personnage->id() == $this->_id)
	{

 		return self::CESTMOI;

	}

	else

	{

		$this->recevoir_degats($personnage);
	
		$this->setExperience($this->experience() + 1 );

		$this->setFdate(date('Y-m-j'));
		
		$this->setCoups($this->coups() + 1);
		
		if ( $personnage->degats() >= 100 )
		{
			return self::TUE;
		}
		else
		{
		    
		    return self::FRAPPE;
		}
	}	
} 

public function recevoir_degats($personnage)
{

  $personnage->setDegats( $personnage->degats() + $this->_forcePerso); 

  switch ($personnage->degats())
    {

     case ($personnage->degats() < 25):
     	  $personnage->setAtout(4);  		 
          break;
     
     case (($personnage->degats() >= 25) && ($personnage->degats() < 50)):
     	  $personnage->setAtout(3);  		 
          break;
    
     case (($personnage->degats() >= 50) && ($personnage->degats() < 75)):
     	  $personnage->setAtout(2);
          break;


     case (($personnage->degats() >= 75) && ($personnage->degats() < 90)):
     	  $personnage->setAtout(1);  		 
          break;

     case ($personnage->degats() >=90):
     	  $personnage->setAtout(0);  		 
          break;
    }      





} 

public function testSomeil() 
{
	
	$datejour = new DateTime();
	$datejour->add(new DateInterval('PT2H'));

	$datepersonnage = new DateTime($this->timeEndormi());
	

	if ( $datepersonnage->format('U') > $datejour->format('U')  )
	{
		return self::ILDORT;
	}

}

} 