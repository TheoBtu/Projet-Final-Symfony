<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Form\FicheProduitType;
use App\Entity\Panier;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class GrumpyPizzaController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request,EntityManagerInterface $entityManager)
    {
        $getPanier = $this->getDoctrine()->getRepository(Panier::class)->findAll();

        return $this->render('grumpy_pizza/index.html.twig', [
            'paniers' => $getPanier,
        ]);
    }

    /**
     * @Route("/produit", name="produit")
     */
    public function produit(Request $request,EntityManagerInterface $entityManager)           //creer la variable pour envoyer la donnée
    {
        $produit = new Produit();

        $produits = $this->getDoctrine()->getRepository(Produit::class)->findAll();             //Avoir accès a la bdd puis a la table Produit (toute)

        $formProduit =$this->createForm(ProduitType::class, $produit);          //formProduit créé un form et récupérer les données de ProduitType avec les données de $produit
        $formProduit->handleRequest($request);          //Envoyer la donnée

        if($formProduit->isSubmitted() && $formProduit->isValid()){
            $produit= $formProduit->getData();

            $image = $produit->getPhoto();          // on récupère que la 'photo' dans ce qu'a upload l'utilisateur (vu qu'il a toutes les data)
            $imageName = md5(uniqid()).'-'.$image->guessExtension();            // on créé un variable crypté dans une variable imageName
            $image->move($this->getParameter('upload_files'), $imageName);          //on l'ajoute dans le dossier upload_files
            $produit ->setPhoto($imageName);            //on donne à $produit le $imageName

            $entityManager->persist($produit);          //Récupères données de produit dans Entity
            $entityManager->flush();            // Envoies a la bdd
        }

        return $this->render('grumpy_pizza/produit.html.twig', [
            'produits' => $produits,
            'formProduits' => $formProduit->createView(),           //Montrer les infos
        ]);
    }

    /**
     * @Route("/ficheProduit/{id}", name="ficheProduit")
     */
    public function ficheProduit($id, Request $request,  EntityManagerInterface $entityManager){

        $panier = new Panier();

        $ficheProduit = $this->getDoctrine()->getRepository(Produit::class)->find($id);

        $formficheProduit =$this->createForm(FicheProduitType::class);
        $formficheProduit->handleRequest($request);


        if($formficheProduit->isSubmitted() && $formficheProduit->isValid()){
            $panier= $formficheProduit->getData();
            $panier ->setProduit($ficheProduit);
            $panier ->setDateAjout(new \DateTime());
            $panier ->setEtat(false);

            $entityManager->persist($panier);
            $entityManager->flush();
        }

        return $this->render('grumpy_pizza/ficheProduit.html.twig', [
            'ficheProduits' => $ficheProduit,                   //Montrer les infos
            'formficheProduits' => $formficheProduit->createView(),
        ]);
    }


    /**
     * @Route("ficheProduit/deleteProduit/{id}", name="deleteProduit")
     */
    public function delete($id, Request $request,  EntityManagerInterface $entityManager){

        $delete = $this->getDoctrine()->getRepository(Produit::class)->find($id);
        $entityManager->remove($delete);
        $entityManager->flush();
        return $this->redirectToRoute('ficheProduit');
    }
}