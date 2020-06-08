<?php



function chargerClasse($classe)
{
 require $classe . '.php';
}

spl_autoload_register('chargerClasse');

session_start(); // On appelle session_start() APRÈS avoir enregistré l'autoload.



if (isset($_GET['deconnexion']))
{
  session_destroy();
  header('Location:./minijeu.php');
  exit();
}

 

$db = new PDO('mysql:host=localhost;dbname=personnages;charset=utf8', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

  $manager = new PersonnageManager($db);

if (isset($_SESSION['perso'])) // Si la session perso existe, on restaure l'objet.
{
  $perso = $_SESSION['perso'];
}




$nbcoupsmax = 6;
$result=0;


if (isset($_POST['nom'])  &&  isset($_POST['creer']))
{

          if ($_POST['type'] == 'magicien')
          {
            $typeperso = 'Magicien';  
          } 
          else
          {
            $typeperso = 'Guerrier'; 
          }  


          $perso = new $typeperso([ 'nom' => $_POST['nom'], 'forcePerso' => $_POST['force'], 'niveau' => $_POST['niveau'], 'experience'  => $_POST['experience'], 'type' => $_POST['type'], 'atout' => $_POST['force'] ]);


          if ($manager->exist($_POST['nom']) == TRUE )
          {
            $message = "Le personnage " . $_POST['nom'] . " existe déjà";
            unset($perso);
          }  
          elseif ( $manager->nomvalide($_POST['nom']) == TRUE )
          {
            $message = "Le nom saisi est invalide";
            unset($perso);
          }
          elseif ( $manager->add($perso) == TRUE )
          {
            $message = "Le personnage " . $_POST['nom'] . " a été crée";
            unset($perso);
          }    
          else
          {
            $message = "Problème pendant la création du personnage" . $_POST['nom']; 
          }
}

elseif (isset($_POST['nom'])  &&  isset($_POST['utiliser']))
{


      if ($manager->exist($_POST['nom']) == FALSE )
      {
        $message = "Le personnage " . $_POST['nom'] . " n'existe pas";
        unset($_POST['utiliser']);  
      }

      else
      {
            
            $perso = $manager->get($_POST['nom']); 
            
            $typeperso = $perso->type();

            $result = $perso->testSomeil();

            if ( $result == $perso::ILDORT )
            { 

              $message = $perso->nom() . " est inutilisable, il dort !"; 
              unset($_POST['utiliser']);  
              unset($perso);  
            }  
            elseif (($perso->type() == 'magicien') && ( $perso->atout() < 1))
            {  
              $message = $perso->nom() . " n'a plus d'atouts !"; 
              unset($_POST['utiliser']);  
              unset($perso);  
            }  

            else
            {


                $datejour=date('Y-m-d');
                
                if ( $datejour != $perso->date())
                {

                    $perso->setCoups(0);
                
                    if ( $perso->degats() > 10 )   
                    {
                      $degats=$perso->degats() - 10;
                    } 
                    else
                    {
                      $degats = 0;
                    } 

                    $perso->setDegats($degats); 
                    $perso->setFdate(date('Y-m-d'));
                    $manager->update($perso);
                } 
                elseif ($perso->coups() >= $nbcoupsmax )
                {

                    $message = $perso->nom() . " il a déja utilisé tous les coups pour aujourd'hui !"; 
                    unset($_POST['utiliser']);  
                    unset($perso);  
                }
                else  
                {
                  $perso->setType();
                }  

            }

      }

}  

elseif (isset($_GET['frapper']))
{
 
$typeperso = $perso->type();


 if ($manager->exist((int) $_GET['frapper']))
 {


  if ( $perso->coups() >= $nbcoupsmax )
  {
   $message2 = $perso->nom() . "! Vous avez utilisé tous les coups pour aujourd'hui"; 
   $result = 0;
  }  
  else
  {

   $persoafrapper = $manager->get((int) $_GET['frapper']);

   $result = $perso->frapper($persoafrapper);

    switch ($result)
    {

     case $typeperso::CESTMOI : 
          $message = "Aia porc'accidente !";
          $manager->update($perso);
          $manager->update($persoafrapper);
          break;

     case $typeperso::FRAPPE:
          $message = $persoafrapper->nom() . " a été frappé méchament par " . $perso->nom() . " !"; 
          $manager->update($perso);
          $manager->update($persoafrapper);
          $message2 = $perso->nom() . "! Il vous reste " . ($nbcoupsmax - $perso->coups()) . " coup(s) pour aujourd'hui"; 
          break;

     case $typeperso::TUE:
          $message = $persoafrapper->nom() . " a été tué par " . $perso->nom() . " !"; 
          $manager->delete($persoafrapper);
          $message2 = $perso->nom()  .  "! Il vous reste " . ($nbcoupsmax - $perso->coups()) . " coup(s) pour aujourd'hui"; 
          break;

     case $typeperso::ILDORT:
          $datepersonnage = new DateTime($persoafrapper->timeEndormi());
          $message = $persoafrapper->nom() . " a été ensorcelé, il dort ! On ne frappe pas un personnage qui dort ! "; 
          $message2 = $perso->nom()  .  "! Il vous reste " . ($nbcoupsmax - $perso->coups()) . " coup(s) pour aujourd'hui"; 
          $manager->update($perso);
    }

   } 
         
  }          
}



elseif (isset($_GET['ensorceler']))
{ 
  $typeperso = $perso->type();  


  if ($manager->exist((int) $_GET['ensorceler']))
  {

     if ( $perso->coups() >= $nbcoupsmax )
     {
       $message2 = $perso->nom() . "! Vous avez utilisé tous les possibilités pour aujourd'hui"; 
     }

     else
     {
      $persoaensorceler = $manager->get((int) $_GET['ensorceler']);

      $result = $perso->jeterunsort($persoaensorceler);

      switch ($result)
      {

       case $typeperso::CESTMOI: 
            $message = "Je ne peux pas m'ensorceler moi même !";
            break;

       case $typeperso::FRAPPE:
            $message = $persoaensorceler->nom() . " a été ensorcelé par " . $perso->nom() . " ! Il va dormir comme un cochon pendant " . $perso->atout() * 6 . " heures."; 
            $manager->update($perso);
            $manager->update($persoaensorceler);
            $message2 = $perso->nom() . "! Il vous reste " . ($nbcoupsmax - $perso->coups()) . " tentative(s) pour aujourd'hui"; 
            break;

        case $typeperso::ILDORT:

            $datepersonnage = new DateTime($persoaensorceler->timeEndormi());

            $message = $persoaensorceler->nom() . " a déjà été ensorcelé, il va dormir jusqu'à " . $datepersonnage->format('H:i:s') . " du " . $datepersonnage->format('d/m/Y') ; 
            $message2 = $perso->nom()  .  "! Il vous reste " . ($nbcoupsmax - $perso->coups()) . " coup(s) pour aujourd'hui"; 
            $manager->update($perso);
            
            break;
       } 

     } 
  } 
} 



?>

<!DOCTYPE html>
<html>
  <head>
    <title>TP : Mini jeu de combat</title>
    
    <meta charset="utf-8" />
  </head>
  <body>
    <p> Nombre de personnages crées : <?= $manager->count()?> </p>   

<?php

if (isset($message)) // On a un message à afficher ?
{
  echo '<p>', $message, '</p>'; // Si oui, on l'affiche.
}

if (isset($message2)) // On a un message à afficher ?
{
  echo '<p>', $message2, '</p>'; // Si oui, on l'affiche.
}


if (isset($perso) || isset($_POST['utiliser']) )
{


?>
   <p><a href="?deconnexion=1">Déconnexion</a></p>

    <fieldset>

      <legend> Mes informations </legend>

      <p>

         - Nom : <?=  htmlspecialchars($perso->nom());?> 
         - Force  : <?= $perso->forcePerso();?>
         - Niveau : <?= $perso->niveau();?>
         - Expérience : <?= $perso->experience();?>
         - Nombre de coups du jour : <?=$perso->coups();?>    
         - Nombre de coups encore disponbles : <?=$nbcoupsmax - $perso->coups();?>    
         - Dégats : <?= $perso->degats();?>
         - Date   : <?= $perso->date();?>
         - Type   : <?= $perso->type();?>

       <?php 

         if ($typeperso == 'magicien')
         {
          $action = 'ensorceler';
         }
         else
         {
          $action = 'frapper';
         } 

       ?>
         

  <fieldset>
    
    <legend> Qui <?=$action?> </legend>

   <?php

    $persos = $manager->getList(); 

    if (empty($persos))
    {
      $message =  "Pas de personnages à " . $action ;
    }

    else
    
    {
    
       foreach ($persos as $unperso)
       {
        
        $result = $unperso->testSomeil();

        if ( $result == $typeperso::ILDORT )
        {
          $dateperso = new DateTime($unperso->timeEndormi());


          $etatperso = " endormi, le réveil est prévu à " . $dateperso->format('H:i:s') . " du " . $dateperso->format('d/m/Y') ; 
        } 
        else
        {
          $etatperso = " reveillé";
        }



        echo '<pre>' . '<a href="?' . $action . '=' . $unperso->id() . '&typeperso='. $unperso->type() . '">' . htmlspecialchars($unperso->nom()) . '</a>' . "\t" . '(Type :' . $unperso->type() . ' dégâts: ' . $unperso->degats() . ' Atouts:' . $unperso->atout() . ' état :' . $etatperso . ')</pre>';
       } 
    }
?>
  </fieldset>
<?php
}

else
{
?>
  <!DOCTYPE html>
  <html>
    <head>
      <title>TP : Mini jeu de combat</title>
      
      <meta charset="utf-8" />
    </head>
    <body>
      <form action="" method="post">
        <p>
           Nom :   <input type="text"   name="nom"   maxlength="50" />
         - Force/Atout: <input type="number" name="force" step="1" value="5" min="0" max="5"/>
         - Niveau : <input type="number" name="niveau" step="1" value="1" min="1" max="5"/>
         - Expérience : <input type="number" name="experience" step="1" value="0" min="0" max="100"/>
        
         - Type de personnage :
           <select name="type">
              <option value="magicien">Magicien</option>
              <option value="guerrier">Guerrier</option>
           </select>

          <input type="submit" value="Créer ce personnage" name="creer" /> 
          <input type="submit" value="Utiliser ce personnage" name="utiliser" />

        </p>
      </form>
<?php
}
?>
  </body>
</html>

<?php
if (isset($perso)) // Si on a créé un personnage, on le stocke dans une variable session afin d'économiser une requête SQL.
{
  $_SESSION['perso'] = $perso;
}
