<?php

namespace App\Controller\Back;

use App\Repository\OrStockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/or/stock')]
class OrStockController extends AbstractController
{
    /**
     * @param OrStockRepository $orStockRepository
     * @return Response
     */
    #[Route('/', name: 'app_or_stock_index_back', methods: ['GET'])]
    public function index(OrStockRepository $orStockRepository): Response
    {
        if (!$this->getUser()->hasMenu(5)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/or_stock/index.html.twig', [
            'orStock' => $orStockRepository->findAll(),
        ]);
    }

//   #[Route('/new', name: 'app_or_stock_new_back', methods: ['GET', 'POST'])]
//   public function new(Request $request,OrStockRepository $orStockRepository): Response
//   {
//       $id = 0;
//       $orStock = new OrStock();
//       $form = $this->createForm(OrStockType::class, $orStock);
//       $form->handleRequest($request);
//       if ($form->isSubmitted() && $form->isValid()) {
//           $file = $request->files->get('excel_file');
//           $data = Util::getDataExcel($file);
//           if(count($data) > 0) {
//               foreach ($data as $value) {
//                   $or = new OrStock();
//                   $lastId = $orStockRepository->findOneBy([],['id'=> 'DESC']);
//                   $lastId != null || $lastId != 0 ? $id = $lastId->getId() : $id;
//                   $or->setId(Util::getLastId($id));
//                   $or->setRef($value[0]);
//                   $ref = $orStockRepository->findByRef($value[0]);
//                   $ref->getId() == null ? $orStockRepository->save($or,true) : null;
//               }
//           }
//           $this->addFlash('success', 'L\'insertion a été un succès');
//           return $this->redirectToRoute('app_or_stock_new_back', [], Response::HTTP_SEE_OTHER);
//       }
//       return $this->render('admin/or_stock/new.html.twig', [
//           'or_stock' => $orStock,
//           'form' => $form,
//       ]);
//   }
//
//    #[Route('/{id}/edit', name: 'app_or_stock_edit', methods: ['GET', 'POST'])]
//    public function edit(Request $request, OrStock $orStock, EntityManagerInterface $entityManager): Response
//    {
//        $form = $this->createForm(OrStockType::class, $orStock);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $entityManager->flush();
//
//            return $this->redirectToRoute('app_or_stock_index_back', [], Response::HTTP_SEE_OTHER);
//        }
//
//        return $this->render('admin/or_stock/edit.html.twig', [
//            'or_stock' => $orStock,
//            'form' => $form,
//        ]);
//    }
}
