<?php

namespace App\Controller;

use App\Entity\Song;
use App\Form\SongType;
use App\Repository\SongRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/song")
 */
class SongController extends AbstractController
{
    /**
     * @Route("/", name="app_song_index", methods={"GET"})
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
        $filteredList = $songRepository->matching($criteria);

        return $this->render('song/index.html.twig', [
            'total' => ($totalArticles),
            'songs' => $filteredList,
            'selectedCat' => $selectedGenre ?: 'edm',
            'selectedAname' => $selectedAname ?: 'Calvin Harris',
        ]);
    }


    /**
     * @Route("/new", name="app_song_new", methods={"GET", "POST"})
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
     * @Route("/{id}", name="app_song_show", methods={"GET"})
     */
    public function show(Song $song): Response
    {
        return $this->render('song/show.html.twig', [
            'song' => $song,
        ]);
    }
    /**
     * @Route("/{id}/player", name="app_song_player", methods={"GET"})
     */
    public function player(Song $song): Response
    {
        return $this->render('song/player.html.twig', [
            'song' => $song,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="app_song_edit", methods={"GET", "POST"})
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
     * @Route("/{id}", name="app_song_delete", methods={"POST"})
     */
    public function delete(Request $request, Song $song, SongRepository $songRepository): Response
    {        if ($this->isCsrfTokenValid('delete'.$song->getId(), $request->request->get('_token'))) {
        $songRepository->remove($song, true);
    }

        return $this->redirectToRoute('app_song_index', [], Response::HTTP_SEE_OTHER);
    }
}
