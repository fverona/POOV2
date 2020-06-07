<?php


class PersonnageManager
{

	private $_db; // inqtance de PDO

	const existe = ": ce personnage existe bien ";
	const nexiste_pas = ": ce personnage n'existe pas du tout ";

	public function __construct($db)
	{

		$this->setDb($db);

	}


	public function setDb(PDO $db)
	{

		$this->_db = $db;
	}


	public function add(Personnage $perso)
	{

		$q = $this->_db->prepare('INSERT INTO personnages_v2(nom, forcePerso, degats, niveau, experience, type, atout, fdate, coups) VALUES(:nom, :forcePerso, :degats, :niveau, :experience, :type, :atout, fdate = NOW(), coups = 0)');


	    $q->bindValue(':nom', $perso->nom());
	    $q->bindValue(':forcePerso', $perso->forcePerso(), PDO::PARAM_INT);
	    $q->bindValue(':atout', $perso->forcePerso(), PDO::PARAM_INT);
	    $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
	    $q->bindValue(':niveau', $perso->niveau(), PDO::PARAM_INT);
	    $q->bindValue(':experience', $perso->experience(), PDO::PARAM_INT);
	    $q->bindValue(':type', $perso->type());


	    return (bool) $q->execute();

	    $q->closeCursor();


	}

	public function delete(Personnage $perso)
	{

		 $this->_db->exec('DELETE FROM personnages_v2 WHERE id = '.$perso->id());
	    
	}


	public function get($id)
	{

		if (is_int($id))
		{
			$id = (int) $id;

   			$q = $this->_db->query('SELECT id, nom, forcePerso, degats, niveau, experience, coups, fdate, timeEndormi, type, atout FROM personnages_v2 WHERE id = '. $id);
    	}	
	  
    	else
    	{
   			$q = $this->_db->query('SELECT id, nom, forcePerso, degats, niveau, experience, coups, fdate, timeEndormi, type, atout FROM personnages_v2 WHERE nom = "'.$id.'"');
    	}

    	$donnees = $q->fetch(PDO::FETCH_ASSOC);

	    $q->closeCursor();


	        if ($donnees['type'] == 'magicien')
    		{
    			$typeperso = 'Magicien';
    		}	
    		else
    		{
    			$typeperso = 'Guerrier';
    		}

    	
    	return new $typeperso($donnees);

	}


	public function getlist()
	{

		$persos = [];

    	$q = $this->_db->query('SELECT id, nom, forcePerso, degats, niveau, experience, coups, fdate, type, atout, timeEndormi FROM personnages_v2 ORDER BY nom');

    	while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    	{
      	
    		if ($donnees['type'] == 'magicien')
    		{
    			$typeperso = 'Magicien';
    		}	
    		else
    		{
    			$typeperso = 'Guerrier';
    		}

      		$persos[] = new $typeperso($donnees);
      	     
      	}

	    $q->closeCursor();
   		
   		return $persos;

	}

	public function update(Personnage $perso)
	{

		$q = $this->_db->prepare('UPDATE personnages_v2 SET forcePerso = :forcePerso, degats = :degats, niveau = :niveau, experience = :experience, coups = :coups, type = :type, timeEndormi = :timeEndormi, atout = :atout, fdate = NOW() WHERE id = :id');

	    $q->bindValue(':forcePerso', $perso->forcePerso(), PDO::PARAM_INT);
	    $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
	    $q->bindValue(':niveau', $perso->niveau(), PDO::PARAM_INT);
	    $q->bindValue(':experience', $perso->experience(), PDO::PARAM_INT);
	    $q->bindValue(':id', $perso->id(), PDO::PARAM_INT);
	    $q->bindValue(':coups', $perso->coups(), PDO::PARAM_INT);
	    $q->bindValue(':type', $perso->type());
	    $q->bindValue(':timeEndormi', $perso->timeEndormi());
	    $q->bindValue(':atout', $perso->atout());


	    $q->execute();

	    $q->closeCursor();
	}

	public function count()
	{

		$q = $this->_db->prepare('select count(*) from personnages_v2');

		$q->execute();

		return  $q->fetchColumn();
	}	


	public function exist($idperso)
	{

		if (is_int($idperso))
		{

			$q = $this->_db->prepare('select count(*) from personnages_v2 where id = :id ');
			
	    	$q->bindValue(':id', $idperso, PDO::PARAM_INT);

		}

		else

		{
			$q = $this->_db->prepare('select count(*) from personnages_v2 where nom = :nom ');

	    	$q->bindValue(':nom', $idperso);

		}

		$q ->execute();

		return (bool) $q ->fetchColumn();

	}	

	public function nomvalide($nom)
	{
		return (bool) empty($nom);
	}

}