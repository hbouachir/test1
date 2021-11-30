<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\AgenceRepository;
use App\Repository\CommandeRepository;
use App\Repository\ImageProduitRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{

    /**
     * @Route("/panier/add/{id}", name="cart_add")
     */
    public function add($id, SessionInterface $session, ProduitRepository $produitRepository)
    {
        $panier = $session->get('panier', []);
        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }
        $session->set('panier', $panier);
        $panierwithdata = [];
        foreach ($panier as $id => $quantite) {
            $panierwithdata[] = [
                'produit' => $produitRepository->find($id),
                'quantite' => $quantite
            ];
        }
        $total = 0;
        $totalqte = 0;
        foreach ($panierwithdata as $produit) {
            $totalproduit = $produit['produit']->getPrix() * $produit['quantite'];
            $total += $totalproduit;
            $totalqte += $produit['quantite'];
        }
        $session->set('panierwithdata', $panierwithdata);
        $session->set('totalqte', $totalqte);
        $session->set('total', $total);
        return $this->redirectToRoute('produit_index');
    }

    /**
     * @Route("/panier/ajout/{id}", name="cart_ajout")
     */
    public function ajout($id, SessionInterface $session, ProduitRepository $produitRepository)
    {
        $panier = $session->get('panier', []);
        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }
        $session->set('panier', $panier);
        $panierwithdata = [];
        foreach ($panier as $id => $quantite) {
            $panierwithdata[] = [
                'produit' => $produitRepository->find($id),
                'quantite' => $quantite
            ];
        }
        $total = 0;
        $totalqte = 0;
        foreach ($panierwithdata as $produit) {
            $totalproduit = $produit['produit']->getPrix() * $produit['quantite'];
            $total += $totalproduit;
            $totalqte += $produit['quantite'];
        }
        $session->set('panierwithdata', $panierwithdata);
        $session->set('totalqte', $totalqte);
        $session->set('total', $total);
        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/panier/min/{id}", name="cart_minus")
     */
    public function min($id, SessionInterface $session, ProduitRepository $produitRepository)
    {
        $panier = $session->get('panier', []);
        if (!empty($panier[$id])) {
            $panier[$id]--;
        } else {
            $panier[$id] = 1;
        }
        $session->set('panier', $panier);
        $panierwithdata = [];
        foreach ($panier as $id => $quantite) {
            $panierwithdata[] = [
                'produit' => $produitRepository->find($id),
                'quantite' => $quantite-1
            ];
        }
        $total = 0;
        $totalqte = 0;
        foreach ($panierwithdata as $produit) {
            $totalproduit = $produit['produit']->getPrix() * $produit['quantite'];
            $total += $totalproduit;
            $totalqte += $produit['quantite'];
        }
        $session->set('panierwithdata', $panierwithdata);
        $session->set('totalqte', $totalqte);
        $session->set('total', $total);
        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/panier", name="cart_index")
     */
    public function index(CommandeRepository $commandeRepository, SessionInterface $session, ProduitRepository $produitRepository)
    {

        $session = $commandeRepository->produitPanier($session, $produitRepository);

        return $this->render('boutiqueEnLigne/panier.html.twig', [
            'produits' => $session->get('panierwithdata'),
            'total' => $session->get('total'),

        ]);
    }

    /**
     * @Route("/panier/supprimer/{id}", name="cart_supp")
     */
    public function remove($id, SessionInterface $session)
    {
        $panier = $session->get('panier', []);
        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }
        $session->set('panier', $panier);
        return $this->redirectToRoute('cart_index');
    }


    /**
     * @Route("/commande", name="commander")
     */
    public function commande(CommandeRepository $commandeRepository, SessionInterface $session, ProduitRepository $produitRepository)
    {

        if (isset($_POST['commander'])) {
            $session = $commandeRepository->produitPanier($session, $produitRepository);
            return $this->render('commande/livraison.html.twig', [
                'produits' => $session->get('panierwithdata'),
                'total' => $session->get('total'),
            ]);
        }
    }

    /**
     * @Route("/commandeConfirmer", name="commandeConfirmer")
     */
    public function commandeConfirmer(CommandeRepository $commandeRepository, SessionInterface $session, ProduitRepository $produitRepository)
    {
        $session = $commandeRepository->produitPanier($session, $produitRepository);
        $commande = new Commande();
        $commande->setDate(new \DateTime());
        $commande->setPrixTotal($session->get('total'));
        $commande->setQuantite($session->get('totalqte'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($commande);
        $entityManager->flush();
        foreach ($session->get('panierwithdata') as $prod) {
            $ligneCommande = new LigneCommande();
            $ligneCommande->setQuantite($prod['quantite']);

            $ligneCommande->setProduit($prod['produit']);
            $ligneCommande->setCommande($commande);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ligneCommande);
            $entityManager->flush();
        }


        return $this->redirectToRoute('commande_index', [], Response::HTTP_SEE_OTHER);


    }

}
