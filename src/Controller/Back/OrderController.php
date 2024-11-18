<?php

namespace App\Controller\Back;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\OrStockRepository;
use App\Repository\UserRepository;
use App\Utilities\Constant;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/order')]
class OrderController extends AbstractController
{
    /**
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/', name: 'admin_app_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        if (!$this->getUser()->hasMenu(7)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
            'constant' => Constant::STATUS_TYPE,
            'payment' => Constant::USER_PAYMENT_METHOD
        ]);
    }

    /**
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/filterCancel', name: 'admin_app_order_filter_cancel', methods: ['GET'])]
    public function filtreCancel(OrderRepository $orderRepository): Response
    {
        if (!$this->getUser()->hasMenu(8)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/order/filter_cancel.html.twig', [
            'orders' => $orderRepository->findByFlagStatus(Constant::STATUS_CANCEL),
            'payment' => Constant::USER_PAYMENT_METHOD
        ]);
    }

    /**
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/queue', name: 'admin_app_order_queue', methods: ['GET'])]
    public function queue(OrderRepository $orderRepository): Response
    {
        if (!$this->getUser()->hasMenu(9)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/order/queue.html.twig', [
            'orders' => $orderRepository->findByFlagStatus(Constant::STATUS_QUEUE),
            'payment' => Constant::USER_PAYMENT_METHOD
        ]);
    }

    /**
     * @param OrStockRepository $orStockRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/filter', name: 'admin_app_order_filter', methods: ['GET'])]
    public function filtre(OrStockRepository $orStockRepository, UserRepository $userRepository): Response
    {
        $stock = $orStockRepository->getStock();

        $listeUser = $userRepository->findBy(['marital_status' => 2], ['id' => 'ASC']);

        return $this->render('admin/order/filter.html.twig', [
//            'orders' => $orderRepository->findOrderByAgeMoin30AndCelibataire(),
            'users' => $listeUser,
            'initial' => $stock->initial,
            'actuel' => $stock->actuel,
            'constant' => Constant::STATUS_TYPE,
            'cancel' => Constant::STATUS_CANCEL,
            'validate' => Constant::STATUS_VALID
        ]);
    }

    /**
     * @param Request $request
     * @param OrderRepository $orderRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/validateMulti', name: 'admin_app_order_validateMulti', methods: ['GET', 'POST'])]
    public function validateMultiple30Celib(Request $request, OrderRepository $orderRepository, UserRepository $userRepository): Response
    {
        $postData = json_decode($request->getContent(), true);
        $message = [];
        if (count($postData) > 0) {
            foreach ($postData as $data) {
                $preorder = $orderRepository->findOneBy(['id' => $data]);
                if ($preorder->getId() != null) {
                    $preorder->setFlagStatus(Constant::STATUS_VALID);
                    $preorder->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
                    $orderRepository->save($preorder, true);
                    $message = (object)['isSuccess' => true];
                }
            }
        }
        return new JsonResponse($message);
        //return new JsonResponse(['Success' => true]);
        /*$listeUser = $userRepository->findBy(['marital_status' => 2], ['id' => 'ASC']);

        $listeOrder_ = [];
        foreach ($listeUser as $user) {
            if ($user->getPreorder() !== null && $user->getPreorder()->getOrStock() !== null && $user->getAge() < 30 && !$user->getPreorder()->isDeleted()) {
                $listeOrder_[] = $user->getPreorder();
            }
        }

        $res = $orderRepository->validOrderMoins30AndCelibataire($listeOrder_);
        $res == 1 ? $this->addFlash('success', 'Validation effectuée') : $this->addFlash('error', 'Une erreur s\'est produite');
        return $this->redirectToRoute('admin_app_order_filter', [], Response::HTTP_SEE_OTHER);*/
    }

    /**
     * @param Order $order
     * @return Response
     */
    #[Route('/{id}', name: 'admin_app_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
            'civility' => Constant::USER_CIVILITY,
            'marital' => Constant::USER_MARITAL_STATUS,
            'etat' => Constant::STATUS_TYPE,
            'payment' => Constant::USER_PAYMENT_METHOD,
            'livraison' => Constant::USER_CHOICE_LIVRAISON_LABEL,
            'meeting' => Constant::USER_CHOICE_MEETING,
            'rt' => Constant::USER_RT,
        ]);
    }

    /**
     * @param Order $order
     * @param OrderRepository $orderRepository
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/{id}/validate', name: 'admin_app_order_validate', methods: ['GET', 'POST'])]
    public function validate(Order $order, OrderRepository $orderRepository, MailerInterface $mailer): Response
    {
        $order->setFlagStatus(Constant::STATUS_PAYMENT_WAIT);
        $order->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
        $orderRepository->save($order, true);
        $this->addFlash('success', 'Validation effectuée');

        //send mail
        try {
            $email = (new Email())
                ->from('noreply@bfm.mg')
                ->to($order->getUser()->getEmail())
                ->subject('Validation d\'inscription')
                ->html($this->renderView('mail/validation-inscription.html.twig', [
                    'civility' => Constant::USER_CIVILITY,
                    'user' => $order->getUser(),
                ]));
            $mailer->send($email);
        } catch (TransportExceptionInterface|Exception) {
//                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
        }
        return $this->redirectToRoute('admin_app_order_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Order $order
     * @param OrderRepository $orderRepository
     * @param OrStockRepository $orStockRepository
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/{id}/revalidate', name: 'admin_app_order_revalidate', methods: ['GET', 'POST'])]
    public function reValidate(Order $order, OrderRepository $orderRepository, OrStockRepository $orStockRepository, MailerInterface $mailer): Response
    {
        $preordersAll = $orderRepository->count(['flagStatus' => [Constant::STATUS_WAIT, Constant::STATUS_VALID, Constant::STATUS_PAYMENT_WAIT, Constant::STATUS_PAID, Constant::STATUS_APPOINTMENT]]);
        $orStockVendu = $orStockRepository->count(['estVendu' => 1]);
        $orStockAll = $orStockRepository->count([]);

        if ($orStockAll - $orStockVendu - $preordersAll > 0) {
            $order->setFlagStatus(Constant::STATUS_VALID);
        } else {
            $order->setFlagStatus(Constant::STATUS_QUEUE);
        }

        $order->setFlagStatus(Constant::STATUS_PAYMENT_WAIT);
        $order->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
        $orderRepository->save($order, true);
        $this->addFlash('success', 'Validation effectuée');

        //send mail
        try {
            $email = (new Email())
                ->from('noreply@bfm.mg')
                ->to($order->getUser()->getEmail())
                ->subject('Validation d\'inscription')
                ->html($this->renderView('mail/validation-inscription.html.twig', [
                    'civility' => Constant::USER_CIVILITY,
                    'user' => $order->getUser(),
                ]));
            $mailer->send($email);
        } catch (TransportExceptionInterface|Exception) {
//                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
        }

        if ($orStockAll - $orStockVendu - $preordersAll > 0) {
            return $this->redirectToRoute('admin_app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('admin_app_order_queue', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Order $order
     * @param $type
     * @param OrderRepository $orderRepository
     * @param OrStockRepository $orStockRepository
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/{id}/{type}/cancel', name: 'admin_app_order_cancel', methods: ['GET', 'POST'])]
    public function cancel(Order $order, $type, OrderRepository $orderRepository, OrStockRepository $orStockRepository, MailerInterface $mailer): Response
    {
        $order->setFlagStatus(Constant::STATUS_CANCEL);
        $order->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
        $orderRepository->save($order, true);
        $this->addFlash('success', 'Annulation effectuée');

        $preordersAll = $orderRepository->count(['flagStatus' => [Constant::STATUS_WAIT, Constant::STATUS_VALID, Constant::STATUS_PAYMENT_WAIT, Constant::STATUS_PAID, Constant::STATUS_APPOINTMENT]]);
        $orStockVendu = $orStockRepository->count(['estVendu' => 1]);
        $orStockAll = $orStockRepository->count([]);

        if ($orStockAll - $orStockVendu - $preordersAll > 0 && ($firstQueue = $orderRepository->findOneBy(['flagStatus' => Constant::STATUS_QUEUE], ['createdAt' => 'ASC'])) !== null) {
            $firstQueue->setFlagStatus(Constant::STATUS_VALID);
            $orderRepository->save($firstQueue, true);
        }

        if ($type == 2) {
            $renderView = $this->renderView('mail/rejet-inscription-reinscription.html.twig', [
                'civility' => Constant::USER_CIVILITY,
                'user' => $order->getUser(),
            ]);
        } else {
            $renderView = $this->renderView('mail/rejet-inscription.html.twig', [
                'civility' => Constant::USER_CIVILITY,
                'user' => $order->getUser(),
            ]);
        }

        //send mail
        try {
            $email = (new Email())
                ->from('noreply@bfm.mg')
                ->to($order->getUser()->getEmail())
                ->subject('Rejet d\'inscription')
                ->html($renderView);
            $mailer->send($email);
        } catch (TransportExceptionInterface|Exception) {
//                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
        }

        return $this->redirectToRoute('admin_app_order_filter_cancel', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Order $order
     * @param $type
     * @return BinaryFileResponse
     */
    #[Route('/{id}/{type}/download', name: 'admin_app_order_download', methods: ['GET', 'POST'])]
    public function download(Order $order, $type): BinaryFileResponse
    {
        switch ($type) {
            case 1:
            default:
                return new BinaryFileResponse($order->getUser()->getFileCin());
            case 2:
                return new BinaryFileResponse($order->getUser()->getFilePassport());
            case 3:
                return new BinaryFileResponse($order->getUser()->getFileRib());
            case 4:
                return new BinaryFileResponse($order->getUser()->getFileAffiliation());
            case 5:
                return new BinaryFileResponse($order->getUser()->getFileIban());
            case 6:
                return new BinaryFileResponse($order->getFileOV());
            case 7:
                return new BinaryFileResponse($order->getFileAccuse());
            case 8:
                return new BinaryFileResponse($order->getFilePrelevement());
            case 9:
                return new BinaryFileResponse($order->getFileDepositContract());
        }
    }
}
