<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PlaylistRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Playlist;

final class PlaylistController extends AbstractController
{
    public function __construct(
        private PlaylistRepository $playlistRepository,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/playlists', name: 'playlist_index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $playlists = $this->playlistRepository->findAll();
        } else {
            $playlists = $this->playlistRepository->findBy(['owner' => $user]);
        }

        $data = array_map(fn(Playlist $p) => [
            'id' => $p->getId(),
            'title' => $p->getTitle(),
            'description' => $p->getDescription(),
        ], $playlists);

        return $this->json($data);
    }

    #[Route('/playlists', name: 'playlist_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $data = json_decode($request->getContent(), true) ?? [];
        $title = $data['title'] ?? null;
        if (!$title) {
            return $this->json(['error' => 'title required'], Response::HTTP_BAD_REQUEST);
        }

        $playlist = new Playlist();
        $playlist->setTitle($title)
            ->setDescription($data['description'] ?? null)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setOwner($this->getUser());

        $this->em->persist($playlist);
        $this->em->flush();

        return $this->json(['id' => $playlist->getId()], Response::HTTP_CREATED);
    }
}
