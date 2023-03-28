<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Form\EmployeType;
use App\Form\FormationType;
use App\Form\MoisYearType;
use App\Entity\Employe;
use App\Entity\Formation;
use App\Entity\Inscription;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Form\NewEmployeType;

class FormationController extends AbstractController
{
    #[Route('/formation', name: 'app_formation')]
    public function index(): Response
    {
        return $this->render('formation/index.html.twig', [
            'controller_name' => 'FormationController',
        ]);
    }

    #[Route('/', name:'app')]
    public function test(){
        return $this->render('base.html.twig');
    }

    #[Route('/identification', name:'app_identification')]
    public function identification(Request $request,ManagerRegistry $doctrine){
        $form = $this->createForm(EmployeType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $login = $form["login"]->getData();
            $mdp = $form["mdp"]->getData();
            $employe= $doctrine->getManager()->getRepository(Employe::class)->findByLogin($login);
            if(password_verify($mdp, $employe->getMdp())){
                $session= new Session();
                $session->set('employeId', $employe->getId());
                $session->set('connecte', true);
                $session->set('statut', $employe->getStatut());
                $id=$employe->getId();
                return $this->redirectToRoute('app_verifIdentification', array('id'=>$id));
            }
            else{
                $message = "Vos identifiants ou mot de passe sont incorrects";
                echo "<script type='text/javascript'>alert('$message');</script>";
                header("Refresh:0");
            }
        }
        return $this->render('formation/identification.html.twig',
                        array('form'=>$form->createView()));
    }
    #[Route('/inscription', name:'app_inscription_user')]
    public function inscription(Request $request, ManagerRegistry $doctrine, Session $session)
    {
        $employe = new Employe();
        $form = $this->createForm(NewEmployeType::class, $employe);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid())
        {
            $mdpHash = password_hash($employe->getMdp(),PASSWORD_DEFAULT);
            $employe->setMdp($mdpHash);
            $em = $doctrine->getManager();
            $em->persist($employe);
            $em->flush();
            return $this->redirectToRoute('app_identification');
        }
        return $this->render('formation/identification.html.twig', array('form'=>$form->createView()));
    }

    #[Route('/plateforme/{id}', name:'app_verifIdentification')]
    public function verifIdentification($id,Request $request, ManagerRegistry $doctrine, Session $session){
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        if($session->get("statut")==1){
            return $this->redirectToRoute('app_servicePersonnel',array('id'=>$id));
        }
        
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findByIdEmployeNotInscrit($session->get('employeId'));
        return $this->render('employe/unEmploye.html.twig', array('inscriptions'=>$inscriptions));
    }

    #[Route('/servicePersonnel/{id}', name:'app_servicePersonnel')]
    public function affServicePerso($id, Request $request, ManagerRegistry $doctrine, Session $session){
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($id);
        return $this->render('employe/employeServicePerso.html.twig', array('employe'=>$employe));

    }

    #[Route('/inscriptionFormation/{id}', name:'app_inscription')]
    public function inscriptionFormation($id, Request $request, ManagerRegistry $doctrine, Session $session){
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        $employe=$doctrine->getManager()->getRepository(Employe::class)->find($session->get("employeId"));
        $formation= $doctrine->getManager()->getRepository(Formation::class)->find($id);
        $statut="en cours";
        $inscription = new Inscription();
        $doublon= $doctrine->getManager()->getRepository(Inscription::class)->findByFormationEtEmploye($formation->getId(),$employe->getId());
        if($doublon){
            $message= "Vous êtes déja inscrit";
        }
        else{
            $message=null;
            $inscription->setStatut("en cours");
            $inscription->setFormation($formation);
            $inscription->setEmploye($employe);
            $insert= $doctrine->getManager();
            $insert->persist($inscription);
            $insert->flush();
        }
        return $this->render('formation/inscription.html.twig', array('message'=>$message));
    }

    #[Route('/suppFormation/{id}', name: 'app_formation_sup')]
    public function suppFormationAction($id, ManagerRegistry $doctrine,Session $session)
    {
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        $formation = $doctrine->getManager()->getRepository(Formation::class)->find($id);
        $entityManager = $doctrine->getManager();
        $entityManager->remove($formation);
        $entityManager->flush();
        return $this->redirectToroute('app_aff');
    }

    #[Route('/afficheLesFormations', name: 'app_aff')]
    public function afficheLesFormations(ManagerRegistry $doctrine, Session $session)
    {  
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        $formations = $doctrine->getManager()->getRepository(Formation::class)->findall();
        if (!$formations) {
            $message = "Pas de formations";
        }
        else {
            $message = null;
        }
        return $this->render('formation/listeformation.html.twig',array('ensFormations'=>$formations, 'message'=>$message));
    }
    #[Route('/ajoutFormation', name: 'app_ajout_Formation')]
    public function ajoutFormation(Request $request, ManagerRegistry $doctrine, Session $session)
    {
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid())
        {
            $em = $doctrine->getManager();
            $em->persist($formation);
            $em->flush();
            return $this->redirectToRoute('app_aff');
        }
        return $this->render('formation/ajoutFormation.html.twig', array('form'=>$form->createView()));
    }
    #[Route('/inscriptionEnCours', name: 'app_inscriptionEnCours_Formation')]
    public function inscriptionEnCours(ManagerRegistry $doctrine,Request $request, Session $session){
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        $statut = 'en cours';
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findByStatut($statut);
        return $this->render('formation/listeInscriptions.html.twig',array('ensInscriptions'=>$inscriptions));
  }
  #[Route('/statutFormation/{id}/{statut}', name: 'app_statut_Formation')]
    public function statutFormation(ManagerRegistry $doctrine,Request $request,$statut ,$id, Session $session){
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->find($id);
        $inscriptions->setStatut($statut);
        $update=$doctrine->getManager();
        $update->persist($inscriptions);
        $update->flush();
        return $this->render('formation/statutlisteInscriptions.html.twig');
  }
  #[Route('saisitMonthAndYear', name:'app_saisitMonthAndYear')]
  public function saisitMonthAndYear(ManagerRegistry $doctrine,Request $request, Session $session){
    if($session->get("connecte")==false){
        return $this->redirectToRoute('app_identification');
    }
    $form = $this->createForm(MoisYearType::class);
    $form->handleRequest($request);
    if($form->isSubmitted()&& $form->isValid()){
        $month = $form["Mois"]->getData();
        $year = $form["Annee"]->getData();
        return $this->redirectToRoute('app_monthAndYear_formation', array('month'=>$month,'year'=>$year));
        }
    return $this->render('formation/saisitMoisYear.html.twig',
                        array('form'=>$form->createView()));
    }


  #[Route('formationByMonthAndYear/{month}/{year}', name: 'app_monthAndYear_formation')]
  public function formationByMonthAndYear(ManagerRegistry $doctrine,Request $request, Session $session, $month, $year){
    if($session->get("connecte")==false){
        return $this->redirectToRoute('app_identification');
    }
    $formations = $doctrine->getManager()->getRepository(Formation::class)->findByMonthAndYear($month, $year);
    return $this->render('formation/monthAndYear.html.twig', array('formations'=>$formations));
  }
  #[Route('formationCount', name:'app_count_formation')]
  public function formationCount(ManagerRegistry $doctrine,Request $request, Session $session){
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        $inscriptions= $doctrine->getManager()->getRepository(Inscription::class)->findByFormationCount();
        print_r($inscriptions[0]);
        return $this->render('formation/formationCount.html.twig', array('inscriptions'=>$inscriptions));
  }

  #[Route('afficheAllEmploye', name:'app_employe_aff')]
    public function afficheAllEmploye(ManagerRegistry $doctrine, Request $request, Session $session){
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        $employes = $doctrine->getManager()->getRepository(Employe::class)->findByStatut(0);
        return $this->render('employe/afficheAllEmploye.html.twig', array('employes'=>$employes));
  }
  #[Route('afficheFormationEmploye/{id}',name:'app_employeFormation_aff')]
    public function afficheFormationEmploye($id,ManagerRegistry $doctrine, Request $request, Session $session){
        if($session->get("connecte")==false){
            return $this->redirectToRoute('app_identification');
        }
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findByIdEmploye($id);
        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($id);
        return $this->render('employe/employeFormations.html.twig', array('inscriptions'=>$inscriptions, 'employe'=>$employe));
  }
}
    
