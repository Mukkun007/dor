<?php

namespace App\Controller\Back;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\OrStockRepository;
use App\Utilities\Constant;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/payment')]
class PaymentController extends AbstractController
{
    /**
     * @param Request $request
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/checks', name: 'admin_payment_check', methods: ['GET', 'POST'])]
    public function checks(Request $request, OrderRepository $orderRepository): Response
    {
        if (!$this->getUser()->hasMenu(11)) {
            throw new AccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $order = $orderRepository->findOneBy([
                'reference' => $request->get('referenceCommande'),
                'flagStatus' => Constant::STATUS_PAYMENT_WAIT,
            ]);

            if (null === $order || $order->getUser()->getPaymentMethod() === 1) {
                $this->addFlash('error', 'Référence invalide');
                return $this->redirectToRoute('admin_payment_check', [], Response::HTTP_SEE_OTHER);
            }

            if ($check = $request->get('check')) {
                $order->setCheque_number($check);
                $order->setComments($request->get('comments'));
                $orderRepository->save($order, true);
                $this->addFlash('success', 'Numéro chèque ajouté');
            }

            return $this->render('admin/payment/checks.html.twig', [
                'civility' => Constant::USER_CIVILITY,
                'status' => Constant::STATUS_TYPE,
                'order' => $order,
            ]);
        }

        return $this->render('admin/payment/checks.html.twig');
    }

    /**
     * @param Order $order
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/checks/{id}/accept', name: 'admin_payment_check_accept', methods: ['GET', 'POST'])]
    public function accept(Order $order, OrderRepository $orderRepository): Response
    {
        $order->setFlagStatus(Constant::STATUS_PAYMENT_WAIT_CHECK_ACCEPTED);
        $order->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
        $orderRepository->save($order, true);
        $this->addFlash('success', 'Chèque accepté');

        return $this->redirectToRoute('admin_payment_check', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Order $order
     * @param OrderRepository $orderRepository
     * @param OrStockRepository $orStockRepository
     * @return Response
     */
    #[Route('/checks/{id}/cancel', name: 'admin_payment_check_cancel', methods: ['GET', 'POST'])]
    public function cancel(Order $order, OrderRepository $orderRepository, OrStockRepository $orStockRepository): Response
    {
        $order->setFlagStatus(Constant::STATUS_CANCEL);
        $order->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
        $orderRepository->save($order, true);
        $this->addFlash('success', 'Chèque refusé');

        $preordersAll = $orderRepository->count(['flagStatus' => [Constant::STATUS_WAIT, Constant::STATUS_VALID, Constant::STATUS_PAYMENT_WAIT, Constant::STATUS_PAID, Constant::STATUS_APPOINTMENT]]);
        $orStockVendu = $orStockRepository->count(['estVendu' => 1]);
        $orStockAll = $orStockRepository->count([]);

        if ($orStockAll - $orStockVendu - $preordersAll > 0 && ($firstQueue = $orderRepository->findOneBy(['flagStatus' => Constant::STATUS_QUEUE], ['createdAt' => 'ASC'])) !== null) {
            $firstQueue->setFlagStatus(Constant::STATUS_VALID);
            $orderRepository->save($firstQueue, true);
        }

        return $this->redirectToRoute('admin_payment_check', [], Response::HTTP_SEE_OTHER);
    }
}
