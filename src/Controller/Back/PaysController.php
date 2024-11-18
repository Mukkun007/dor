<?php

namespace App\Controller\Back;

use App\Entity\Pays;
use App\Form\PaysType;
use App\Repository\PaysRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTimeImmutable;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/pays')]
class PaysController extends AbstractController
{
    
    /**
     * @param PaysRepository $paysRepository
     * @return Response
     */
    #[Route('/', name: 'admin_app_pays_index', methods: ['GET'])]
    public function index(PaysRepository $paysRepository): Response
    {
        if (!$this->getUser()->hasMenu(22)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/pays/index.html.twig', [
            'pays' => $paysRepository->findAll(),
        ]);
    }

    /**
     * @param Request $request
     * @param PaysRepository $paysRepository
     * @return Response
     */
    #[Route('/new', name: 'admin_app_pays_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PaysRepository $paysRepository): Response
    {
        if (!$this->getUser()->hasMenu(22)) {
            throw new AccessDeniedException();
        }

        $pay = new Pays();
        $form = $this->createForm(PaysType::class, $pay);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paysRepository->save($pay, true);
            $this->addFlash('success', 'L\'insertion a été un succès');
            return $this->redirectToRoute('admin_app_pays_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/pays/new.html.twig', [
            'pay' => $pay,
            'form' => $form,
        ]);
    }

    /**
     * @param Request $request
     * @param Pays $pay
     * @param PaysRepository $paysRepository
     * @return Response
     */
    #[Route('/{id}/edit', name: 'admin_app_pays_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pays $pay, PaysRepository $paysRepository): Response
    {
        if (!$this->getUser()->hasMenu(22)) {
            throw new AccessDeniedException();
        }
        
        $form = $this->createForm(PaysType::class, $pay);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pay->setDateModif((new DateTimeImmutable())->format('Y-m-d'));
            $paysRepository->save($pay, true);
            $this->addFlash('success', 'Paramètre mise à jour');
            return $this->redirectToRoute('admin_app_pays_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/pays/edit.html.twig', [
            'pay' => $pay,
            'form' => $form,
        ]);
    }
}
