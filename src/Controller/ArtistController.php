<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Form\ArtistType;
use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/artist")
 */
class ArtistController extends AbstractController
{
 /**
     * @Route("/", name="app_artist_index", methods={"GET"})
     */
    public function index(ArtistRepository $artistRepository,Request $request): Response
    {
        // 1. Obtain doctrine manager
        $em = $this->getDoctrine()->getManager();

        // 2. Setup repository of some entity
        $repoArticles = $em->getRepository(Artist::class);

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
        $selectedBorn = $request->query->get('Born');
        $selectedCountry = $request->query->get('country');
        $expressionBuilder = Criteria::expr();
        $criteria = new Criteria();
        if (!is_null($selectedCountry)) {
            $criteria->andWhere($expressionBuilder->eq('country', $selectedCountry));
        }
        if (!is_null($selectedBorn)) {
            $criteria->andWhere($expressionBuilder->eq('Born', $selectedBorn));
        }
        if (!is_null($Name) && !empty(($Name))) {
            $criteria->andWhere($expressionBuilder->contains('name', $Name));
        }
        $filteredList = $artistRepository->matching($criteria);
        return $this->render('artist/index.html.twig', [
            'artists' => $filteredList,
            'total' => ($totalArticles),
            'selectedCat' => $selectedCountry ?: 'Unite Kingdom',
            'selectedBorn' => $selectedBorn ?: 'Calvin Harris',
        ]);
    }
    /**
     * @Route("/new", name="app_artist_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ArtistRepository $artistRepository): Response
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artistRepository->add($artist, true);

            return $this->redirectToRoute('app_artist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artist/new.html.twig', [
            'artist' => $artist,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_artist_show", methods={"GET"})
     */
    public function show(Artist $artist): Response
    {
        return $this->render('artist/show.html.twig', [
            'artist' => $artist,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_artist_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Artist $artist, ArtistRepository $artistRepository): Response
    {
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $artistRepository->add($artist, true);

            return $this->redirectToRoute('app_artist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('artist/edit.html.twig', [
            'artist' => $artist,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_artist_delete", methods={"POST"})
     */
    public function delete(Request $request, Artist $artist, ArtistRepository $artistRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$artist->getId(), $request->request->get('_token'))) {
            $artistRepository->remove($artist, true);
        }

        return $this->redirectToRoute('app_artist_index', [], Response::HTTP_SEE_OTHER);
    }
}
