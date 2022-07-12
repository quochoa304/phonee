<?php

namespace App\Controller;

use App\Entity\Playlist;
use App\Entity\Song;
use App\Form\PlaylistType;
use App\Form\SongType;
use App\Repository\PlaylistRepository;
use App\Repository\SongRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/playlist")
 */
class PlaylistController extends AbstractController
{
    /**
     * @Route("/", name="app_playlist_index", methods={"GET"})
     */
    public function index(SongRepository $songRepository,  Request $request): Response
    {
        // 1. Obtain doctrine manager
        $em = $this->getDoctrine()->getManager();

        // 2. Setup repository of some entity
        $repoArticles = $em->getRepository(Song::class);

        // 3. Query how many rows are there in the Articles table
        $totalArticles = $repoArticles->createQueryBuilder('a')
            // Filter by some parameter if you want
            // ->where('a.published = 1')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // 4. Return a number as response
        // e.g 972
        $Name = $request->query->get('name');
        $selectedAname = $request->query->get('Aname');
        $selectedGenre = $request->query->get('Category');
        $expressionBuilder = Criteria::expr();
        $criteria = new Criteria();
        if (!is_null($selectedGenre)) {
            $criteria->andWhere($expressionBuilder->eq('Category', $selectedGenre));
        }
        if (!is_null($selectedAname)) {
            $criteria->andWhere($expressionBuilder->eq('Aname', $selectedAname));
        }
        if (!is_null($Name) && !empty(($Name))) {
            $criteria->andWhere($expressionBuilder->contains('name', $Name));
        }
        $filteredList = $songRepository->matching($criteria);

        return $this->render('song/index.html.twig', [
            'total' => ($totalArticles),
            'songs' => $filteredList,
            'selectedCat' => $selectedGenre ?: 'edm',
            'selectedAname' => $selectedAname ?: 'Calvin Harris',
        ]);
    }


    /**
     * @Route("/new", name="app_playlist_new", methods={"GET", "POST"})
     */
    public function new(Request $request, SongRepository $songRepository): Response
    {
        $song = new Song();
        $form = $this->createForm(SongType::class, $song);
        $form->handleRequest($request);
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($form->isSubmitted() && $form->isValid()) {
            $songRepository->add($song, true);
            return $this->redirectToRoute('app_song_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('song/new.html.twig', [
            'song' => $song,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_playlist_show", methods={"GET"})
     */
    public function show(Song $song): Response
    {
        return $this->render('song/show.html.twig', [
            'song' => $song,
        ]);
    }
    /**
     * @Route("/{id}/player", name="app_playlist_player", methods={"GET"})
     */
    public function player(Song $song): Response
    {
        return $this->render('song/player.html.twig', [
            'song' => $song,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="app_playlist_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Song $song, SongRepository $songRepository): Response
    {
        $form = $this->createForm(SongType::class, $song);
        $form->handleRequest($request);
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($form->isSubmitted() && $form->isValid()) {
            $songRepository->add($song, true);

            return $this->redirectToRoute('app_song_index', [], Response::HTTP_SEE_OTHER);
        }
        else {
            return $this->renderForm('song/edit.html.twig', [
                'song' => $song,
                'form' => $form,
            ]);
        }

    }

    /**
     * @Route("/{id}", name="app_playlist_delete", methods={"POST"})
     */
    public function delete(Request $request, Song $song, SongRepository $songRepository): Response
    {        if ($this->isCsrfTokenValid('delete'.$song->getId(), $request->request->get('_token'))) {
        $songRepository->remove($song, true);
    }

        return $this->redirectToRoute('app_song_index', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * @Route("/addCart/{id}", name="app_add_cart", methods={"GET"})
     */
    public function addCart(Product $product, Request $request)
    {
        $session = $request->getSession();
        $quantity = (int)$request->query->get('quantity');

        //check if cart is empty
        if (!$session->has('cartElements')) {
            //if it is empty, create an array of pairs (prod Id & quantity) to store first cart element.
            $cartElements = array($product->getId() => $quantity);
            //save the array to the session for the first time.
            $session->set('cartElements', $cartElements);
        } else {
            $cartElements = $session->get('cartElements');
            //Add new product after the first time. (would UPDATE new quantity for added product)
            $cartElements = array($product->getId() => $quantity) + $cartElements;
            //Re-save cart Elements back to session again (after update/append new product to shopping cart)
            $session->set('cartElements', $cartElements);
        }
        return new Response(); //means 200, successful
    }
    /**
     * @Route("/reviewCart", name="app_review_cart", methods={"GET"})
     */
    public function reviewCart(Request $request): Response
    {
        $session = $request->getSession();
        if ($session->has('cartElements')) {
            $cartElements = $session->get('cartElements');
        } else
            $cartElements = [];
        return $this->json($cartElements);
    }
}
