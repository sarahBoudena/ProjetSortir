<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class   SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_index')]
    public function index(
        SortieRepository $repository,
        SiteRepository $siteRepository,
        ParticipantRepository $participantRepository,
        Request $request,
    ): Response
    {
        $sites = $siteRepository->findAll();

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->getUser()){
            $pseudo=$this->getUser()->getUserIdentifier();
            $user=$participantRepository->findOneBy(array('pseudo' => $pseudo));
            $idSite=$user->getSite()->getId();
        }

        if ($request->request->get("boutonRechercher")){
            $siteFiltre = $request->request->get('selectSite');
            $nomFiltre = '%'.$request->request->get('inputRecherche').'%';
            $dateDebutFiltre = $request->request->get('inputDateDebut');
            $dateFinFiltre = $request->request->get('inputDateFin');

            if($request->request->get('checkBoxOrganisateur')=='coche'){
                $organisateurFiltre = $user->getId();
            }else{
                $organisateurFiltre=null;
            }

            if($request->request->get('checkBoxInscrit')=='coche'){
                $inscritFiltre = $user->getId();
            }else{
                $inscritFiltre=null;
            }

            if($request->request->get('checkBoxNonInscrit')=='coche'){
                $nonInscritFiltre = $user->getPseudo();
            }else{
                $nonInscritFiltre=null;
            }

            if($request->request->get('checkBoxPasse')=='coche'){
                $passeFiltre = 5;
            }else{
                $passeFiltre=null;
            }

            $sorties = $repository->findByFilter($siteFiltre, $nomFiltre,$dateDebutFiltre,$dateFinFiltre,$organisateurFiltre,$inscritFiltre,$nonInscritFiltre,$passeFiltre);

            return $this->render('sortie/index.html.twig', [
                "sites"=>$sites,
                "sorties"=>$sorties,
            ]);
        }
        $sorties = $repository->findBy(array('site'=> $idSite, 'etat'=>array(2,3,4,5,7)));

        return $this->render('sortie/index.html.twig', [
            "sites"=>$sites,
            "sorties"=>$sorties,
        ]);
    }


    #[Route('/ajout', name: 'sortie_ajout')]
    public function ajout(
        EtatRepository $etatRepository,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $etat = $etatRepository->findOneBy(array('id'=>1));
            if (!$this->getUser()) {
                return $this->redirectToRoute('app_login');
            }
            $pseudo = $this->getUser()->getUserIdentifier();
            $organisateur = $participantRepository->findOneBy(array('pseudo'=>$pseudo));
            $sortie->setOrganisateur($organisateur);
            $sortie->setSite($organisateur->getSite());
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);

            /*$uploadedFile = ($form['imageFile']->getData());
            if($uploadedFile) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/img/sorties';
                $newFilename = 'img/sorties/'.$sortie->getNomSortie().'.'.$uploadedFile->guessExtension();
                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $ancienneImage = $sortie->getUrlPhoto();

                if ($ancienneImage != 'img/sorties/sortie_lieu_defaut.png'){
                    $filesystem = new Filesystem();
                    $filesystem->remove('path/to/file/file.pdf');$filesystem->remove($ancienneImage);
                }
            }
            if(!$uploadedFile && $sortie->getUrlPhoto()==null){
                $newFilename="img/sorties/sortie_lieu_defaut.png";
            }
            $sortie->setUrlPhoto($newFilename);*/



            $entityManager->flush();
            return $this->redirectToRoute('sortie_index', array('sortieModifiee' => $sortie));
        }
        return $this->render('sortie/ajout.html.twig', [
            "form"=>$form->createView()
        ]);
    }

    #[Route('/details/{id}', name: 'details_index',
                             requirements: ['id' => '\d+']
    )]


    public function details(
        Sortie $id
    ): Response
    {
        return $this->render('sortie/details.html.twig', [
            "sortie" =>$id
        ]);
    }
}
