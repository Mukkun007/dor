<?php

namespace App\Controller\Back;

use App\Entity\Rappro;
use App\Repository\OrderRepository;
use App\Repository\OrStockRepository;
use App\Repository\RapproRepository;
use App\Repository\SettingRepository;
use App\Repository\UserRepository;
use App\Utilities\Constant;
use DateInterval;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/rapprochement')]
class RapprochementController extends AbstractController
{
    /**
     * @param RapproRepository $rapproRepository
     * @param OrderRepository $orderRepository
     * @param OrStockRepository $orStockRepository
     * @param SettingRepository $settingRepository
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/checks', name: 'admin_rapprochement_check', methods: ['GET'])]
    public function checks(SettingRepository $settingRepository,RapproRepository $rapproRepository, OrderRepository $orderRepository, OrStockRepository $orStockRepository, MailerInterface $mailer): Response
    {
        $temp = null;

        if (!$this->getUser()->hasMenu(13)) {
            throw new AccessDeniedException();
        }

        foreach ($checks = $rapproRepository->findBy(['typeRappro' => 'C'], ['reference' => 'ASC']) as $check) {
            if (($preorder = $orderRepository->findOneBy(['reference' => $check->getReference()])) !== null && $check->getNumDossier() === null) {
                $check->setNumDossier($preorder->getReference());
            }

            if ($check->getNumDossier() !== null) {
                $preorder = $orderRepository->findOneBy(['reference' => $check->getNumDossier()]);
                if ($preorder !== null && $check->getStatutChqVir() === 'R' && ($check->getStatutRappro() !== 0 && $check->getStatutRappro() !== 1) && $preorder->getFlagStatus() === Constant::STATUS_PAYMENT_WAIT_CHECK_ACCEPTED) {
                    $check->setStatutRappro(0);
                    $check->setDateTraitement((new DateTimeImmutable())->format('Y-m-d'));

                    $preorder->setFlagStatus(Constant::STATUS_CANCEL);
                    $preorder->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
                    if ($preorder->getOrStock() !== null && ($orStock = $orStockRepository->findOneBy(['id' => $preorder->getOrStock()->getId()])) !== null) {
                        $orStock->setIsPreOrder(0);
                        $orStock->setDateUpdate((new DateTimeImmutable())->format('Y-m-d'));
                        $orStockRepository->save($orStock, true);
                    }
                    $preorder->setOrStock(null);
                    $orderRepository->save($preorder, true);
                } elseif ($preorder !== null && $check->getStatutChqVir() === 'N' && ($check->getStatutRappro() !== 0 && $check->getStatutRappro() !== 1) && $preorder->getFlagStatus() === Constant::STATUS_PAYMENT_WAIT_CHECK_ACCEPTED) {
                    $dateComp = DateTimeImmutable::createFromFormat('Y/m/d', $check->getDateRappro());
                    $datePos = $dateComp->add(DateInterval::createFromDateString('3 day'));
                    $dateNow = new DateTimeImmutable();
                    if ($datePos <= $dateNow) {
                        $check->setStatutRappro(1);
                        $check->setDateTraitement((new DateTimeImmutable())->format('Y-m-d'));
                        $preorder->setFlagStatus(Constant::STATUS_PAID);
                        $preorder->setDatePaiement((new DateTimeImmutable())->format('Y-m-d'));
                        $preorder->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
                        $orderRepository->save($preorder, true);

                        //send mail14
                        try {
                            $email = (new Email())
                                ->from('noreply@bfm.mg')
                                ->to($preorder->getUser()->getEmail())
                                ->subject('Validation du paiement')
                                ->html($this->renderView('mail/paiement-accepte.html.twig', [
                                    'civility' => Constant::USER_CIVILITY,
                                    'user' => $preorder->getUser(),
                                ]));
                            $mailer->send($email);
                        } catch (TransportExceptionInterface|Exception) {
//                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
                        }
                    }
                }
            }

            $rapproRepository->save($check, true);
        }

        return $this->render('admin/rapprochement/checks.html.twig', [
            'checks' => $checks,
        ]);
    }

    /**
     * @param Request $request
     * @param Rappro $rappro
     * @param UserRepository $userRepository
     * @param SettingRepository $settingRepository
     * @param OrderRepository $orderRepository
     * @param RapproRepository $rapproRepository
     * @return Response
     */
    #[Route('/checks/{id}/edit', name: 'admin_rapprochement_check_edit', methods: ['GET', 'POST'])]
    public function editCheck(SettingRepository $settingRepository,Request $request, Rappro $rappro, UserRepository $userRepository, OrderRepository $orderRepository, RapproRepository $rapproRepository): Response
    {
        if (!$this->getUser()->hasMenu(13)) {
            throw new AccessDeniedException();
        }

        if (!$user = $userRepository->findOneBy(['reference' => $rappro->getReference()])) {
            $user = $userRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
        }

        if ($request->isMethod('POST')) {
            $order = $orderRepository->find((int)$request->get('attribution'));
            $rappro->setNumDossier($order?->getReference());

    
            //attribution piece
            $setting = $settingRepository->findOneBy(['name' => ['STOCK_PREORDER']]);
            // $userRappro = $orderRepository->find((int)$request->get('attribution'));
            $userRappro = $userRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
            $orQuantity = $userRappro->getOrQuantity();
            $quantityPreorderActual = $setting->getValue();
            // $referencePreorder =  $user->getReference();
            $quantityPreorderNext =  intval($quantityPreorderActual) + $orQuantity;
            $setting->setValue($quantityPreorderNext);
            $settingRepository->save($setting, true);

            $rapproRepository->save($rappro, true);
            $this->addFlash('success', 'Précommande liée');

            return $this->redirectToRoute('admin_rapprochement_check_edit', ['id' => $rappro->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rapprochement/edit-check.html.twig', [
            'rappro' => $rappro,
            'user' => $user,
            'civility' => Constant::USER_CIVILITY,
            'orders' => $orderRepository->findBy(['flagStatus' => [Constant::STATUS_PAYMENT_WAIT, Constant::STATUS_PAYMENT_WAIT_CHECK_ACCEPTED]]),
        ]);
    }

    /**
     * @param RapproRepository $rapproRepository
     * @return Response
     */
    #[Route('/transfers', name: 'admin_rapprochement_transfer', methods: ['GET'])]
    public function transfers(RapproRepository $rapproRepository): Response
    {
        if (!$this->getUser()->hasMenu(14)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/rapprochement/transfers.html.twig', [
            'transfers' => $rapproRepository->findBy(['typeRappro' => 'V']),
        ]);
    }

    /**
     * @param Request $request
     * @param Rappro $rappro
     * @param UserRepository $userRepository
     * @param OrderRepository $orderRepository
     * @param RapproRepository $rapproRepository
     * @return Response
     */
    #[Route('/transfers/{id}/edit', name: 'admin_rapprochement_transfer_edit', methods: ['GET', 'POST'])]
    public function editTransfer(Request $request, Rappro $rappro, UserRepository $userRepository, OrderRepository $orderRepository, RapproRepository $rapproRepository): Response
    {
        if (!$this->getUser()->hasMenu(15)) {
            throw new AccessDeniedException();
        }

        if (!$user = $userRepository->findOneBy(['reference' => $rappro->getReference()])) {
            $user = $userRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
        }

        if ($request->isMethod('POST')) {
            $order = $orderRepository->find((int)$request->get('attribution'));
            $rappro->setNumDossier($order?->getReference());

            //attribution piece
            $setting = $settingRepository->findOneBy(['name' => ['STOCK_PREORDER']]);
            // $userRappro = $orderRepository->find((int)$request->get('attribution'));
            $userRappro = $userRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
            $orQuantity = $userRappro->getOrQuantity();
            $quantityPreorderActual = $setting->getValue();
            // $referencePreorder =  $user->getReference();
            $quantityPreorderNext =  intval($quantityPreorderActual) + $orQuantity;
            $setting->setValue($quantityPreorderNext);
            $settingRepository->save($setting, true);

            $rapproRepository->save($rappro, true);
            $this->addFlash('success', 'Précommande liée');

            return $this->redirectToRoute('admin_rapprochement_transfer_edit', ['id' => $rappro->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rapprochement/edit-transfer.html.twig', [
            'rappro' => $rappro,
            'user' => $user,
            'civility' => Constant::USER_CIVILITY,
            'orders' => $orderRepository->findBy(['flagStatus' => Constant::STATUS_PAYMENT_WAIT]),
        ]);
    }

    /**
     * @param Rappro $rappro
     * @param RapproRepository $rapproRepository
     * @return Response
     */
    #[Route('/transfers/{id}/edit/preorder', name: 'admin_rapprochement_transfer_edit_preorder', methods: ['GET'])]
    public function editPreorderTransfer(Rappro $rappro, RapproRepository $rapproRepository): Response
    {
        if (!$this->getUser()->hasMenu(14)) {
            throw new AccessDeniedException();
        }

        $rappro->setNumDossier(null);
        $rapproRepository->save($rappro, true);
        $this->addFlash('success', 'Précommande déliée');

        return $this->redirectToRoute('admin_rapprochement_transfer_edit', ['id' => $rappro->getId()], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Rappro $rappro
     * @param OrderRepository $orderRepository
     * @param RapproRepository $rapproRepository
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/transfers/{id}/accept', name: 'admin_rapprochement_transfer_accept', methods: ['GET', 'POST'])]
    public function acceptTransfer(Rappro $rappro, OrderRepository $orderRepository, RapproRepository $rapproRepository, MailerInterface $mailer): Response
    {
        if (!$this->getUser()->hasMenu(14)) {
            throw new AccessDeniedException();
        }

        $rappro->setStatutRappro(1);
        $rappro->setDateTraitement((new DateTimeImmutable())->format('Y-m-d'));

        if (!$order = $orderRepository->findOneBy(['reference' => $rappro->getReference()])) {
            $order = $orderRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
        }

        $order->setFlagStatus(Constant::STATUS_PAID);
        $order->setDatePaiement((new DateTimeImmutable())->format('Y-m-d'));
        $order->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
        $orderRepository->save($order, true);
        $rapproRepository->save($rappro, true);

        //send mail
        try {
            $email = (new Email())
                ->from('noreply@bfm.mg')
                ->to($order->getUser()->getEmail())
                ->subject('Validation du paiement')
                ->html($this->renderView('mail/paiement-accepte.html.twig', [
                    'civility' => Constant::USER_CIVILITY,
                    'user' => $order->getUser(),
                ]));
            $mailer->send($email);
        } catch (TransportExceptionInterface|Exception) {
//                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
        }

        $this->addFlash('success', 'Virement accepté');

        return $this->redirectToRoute('admin_rapprochement_transfer', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Rappro $rappro
     * @param OrderRepository $orderRepository
     * @param OrStockRepository $orStockRepository
     * @param RapproRepository $rapproRepository
     * @return Response
     */
    #[Route('/transfers/{id}/cancel', name: 'admin_rapprochement_transfer_cancel', methods: ['GET', 'POST'])]
    public function cancelTransfer(Rappro $rappro, OrderRepository $orderRepository, OrStockRepository $orStockRepository, RapproRepository $rapproRepository): Response
    {
        if (!$this->getUser()->hasMenu(14)) {
            throw new AccessDeniedException();
        }

        $rappro->setStatutRappro(0);
        $rappro->setDateTraitement((new DateTimeImmutable())->format('Y-m-d'));

        if (!$order = $orderRepository->findOneBy(['reference' => $rappro->getReference()])) {
            $order = $orderRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
        }

        $order->setFlagStatus(Constant::STATUS_CANCEL);
        $order->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
        $orStock = $orStockRepository->findOneBy(['id' => $order->getOrStock()->getId()]);
        if ($orStock->getId() != 0 || $orStock->getId() != null) {
            $orStock->setIsPreOrder(0);
            $orStock->setDateUpdate((new DateTimeImmutable())->format('Y-m-d'));
            $orStockRepository->save($orStock, true);
        }
        $order->setOrStock(null);
        $orderRepository->save($order, true);
        $rapproRepository->save($rappro, true);
        $this->addFlash('success', 'Virement refusé');

        return $this->redirectToRoute('admin_rapprochement_transfer', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param RapproRepository $rapproRepository
     * @return Response
     */
    #[Route('/loans', name: 'admin_rapprochement_loan', methods: ['GET'])]
    public function loans(RapproRepository $rapproRepository): Response
    {
        if (!$this->getUser()->hasMenu(15)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/rapprochement/loans.html.twig', [
            'loans' => $rapproRepository->findBy(['typeRappro' => 'P']),
        ]);
    }

    /**
     * @param Request $request
     * @param Rappro $rappro
     * @param UserRepository $userRepository
     * @param OrderRepository $orderRepository
     * @param RapproRepository $rapproRepository
     * @return Response
     */
    #[Route('/loans/{id}/edit', name: 'admin_rapprochement_loan_edit', methods: ['GET', 'POST'])]
    public function editLoan(Request $request, Rappro $rappro, UserRepository $userRepository, OrderRepository $orderRepository, RapproRepository $rapproRepository): Response
    {
        if (!$this->getUser()->hasMenu(15)) {
            throw new AccessDeniedException();
        }

        if (!$user = $userRepository->findOneBy(['reference' => $rappro->getReference()])) {
            $user = $userRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
        }

        if ($request->isMethod('POST')) {
            $order = $orderRepository->find((int)$request->get('attribution'));
            $rappro->setNumDossier($order?->getReference());

            //attribution piece
            $setting = $settingRepository->findOneBy(['name' => ['STOCK_PREORDER']]);
            // $userRappro = $orderRepository->find((int)$request->get('attribution'));
            $userRappro = $userRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
            $orQuantity = $userRappro->getOrQuantity();
            $quantityPreorderActual = $setting->getValue();
            // $referencePreorder =  $user->getReference();
            $quantityPreorderNext =  intval($quantityPreorderActual) + $orQuantity;
            $setting->setValue($quantityPreorderNext);
            $settingRepository->save($setting, true);
            
            $rapproRepository->save($rappro, true);
            $this->addFlash('success', 'Précommande liée');

            return $this->redirectToRoute('admin_rapprochement_loan_edit', ['id' => $rappro->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rapprochement/edit-loan.html.twig', [
            'rappro' => $rappro,
            'user' => $user,
            'civility' => Constant::USER_CIVILITY,
            'orders' => $orderRepository->findBy(['flagStatus' => Constant::STATUS_PAYMENT_WAIT]),
        ]);
    }

    /**
     * @param Rappro $rappro
     * @param RapproRepository $rapproRepository
     * @return Response
     */
    #[Route('/loans/{id}/edit/preorder', name: 'admin_rapprochement_loan_edit_preorder', methods: ['GET'])]
    public function editPreorderLoan(Rappro $rappro, RapproRepository $rapproRepository): Response
    {
        if (!$this->getUser()->hasMenu(15)) {
            throw new AccessDeniedException();
        }

        $rappro->setNumDossier(null);
        $rapproRepository->save($rappro, true);
        $this->addFlash('success', 'Précommande déliée');

        return $this->redirectToRoute('admin_rapprochement_loan_edit', ['id' => $rappro->getId()], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Rappro $rappro
     * @param OrderRepository $orderRepository
     * @param RapproRepository $rapproRepository
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/loans/{id}/accept', name: 'admin_rapprochement_loan_accept', methods: ['GET', 'POST'])]
    public function acceptLoan(Rappro $rappro, OrderRepository $orderRepository, RapproRepository $rapproRepository, MailerInterface $mailer): Response
    {
        if (!$this->getUser()->hasMenu(15)) {
            throw new AccessDeniedException();
        }

        $rappro->setStatutRappro(1);
        $rappro->setDateTraitement((new DateTimeImmutable())->format('Y-m-d'));

        if (!$order = $orderRepository->findOneBy(['reference' => $rappro->getReference()])) {
            $order = $orderRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
        }

        $order->setFlagStatus(Constant::STATUS_PAID);
        $order->setIsPpex(true);
        $order->setDatePaiement((new DateTimeImmutable())->format('Y-m-d'));
        $order->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
        $orderRepository->save($order, true);
        $rapproRepository->save($rappro, true);

        //send mail
        try {
            $email = (new Email())
                ->from('noreply@bfm.mg')
                ->to($order->getUser()->getEmail())
                ->subject('Validation du paiement')
                ->html($this->renderView('mail/paiement-accepte.html.twig', [
                    'civility' => Constant::USER_CIVILITY,
                    'user' => $order->getUser(),
                ]));
            $mailer->send($email);
        } catch (TransportExceptionInterface|Exception) {
//                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
        }

        $this->addFlash('success', 'Virement accepté');

        return $this->redirectToRoute('admin_rapprochement_loan', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Rappro $rappro
     * @param OrderRepository $orderRepository
     * @param OrStockRepository $orStockRepository
     * @param RapproRepository $rapproRepository
     * @return Response
     */
    #[Route('/loans/{id}/cancel', name: 'admin_rapprochement_loan_cancel', methods: ['GET', 'POST'])]
    public function cancelLoan(Rappro $rappro, OrderRepository $orderRepository, OrStockRepository $orStockRepository, RapproRepository $rapproRepository): Response
    {
        if (!$this->getUser()->hasMenu(15)) {
            throw new AccessDeniedException();
        }
        
        $rappro->setStatutRappro(0);
        $rappro->setDateTraitement((new DateTimeImmutable())->format('Y-m-d'));

        if (!$order = $orderRepository->findOneBy(['reference' => $rappro->getReference()])) {
            $order = $orderRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
        }

        $order->setFlagStatus(Constant::STATUS_CANCEL);
        $order->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
        $orStock = $orStockRepository->findOneBy(['id' => $order->getOrStock()->getId()]);
        if ($orStock->getId() != 0 || $orStock->getId() != null) {
            $orStock->setIsPreOrder(0);
            $orStock->setDateUpdate((new DateTimeImmutable())->format('Y-m-d'));
            $orStockRepository->save($orStock, true);
        }
        $order->setOrStock(null);
        $orderRepository->save($order, true);
        $rapproRepository->save($rappro, true);
        $this->addFlash('success', 'Virement refusé');

        return $this->redirectToRoute('admin_rapprochement_loan', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Rappro $rappro
     * @param UserRepository $userRepository
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/confirmation/{id}', name: 'admin_rapprochement_send_mail', methods: ['GET', 'POST'])]
    public function resendMail(Rappro $rappro, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        if (!$user = $userRepository->findOneBy(['reference' => $rappro->getReference()])) {
            $user = $userRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
        }

        //send mail
        try {
            $email = (new Email())
                ->from('noreply@bfm.mg')
                ->to($user->getEmail())
                ->subject('Validation du paiement')
                ->html($this->renderView('mail/paiement-accepte.html.twig', [
                    'civility' => Constant::USER_CIVILITY,
                    'user' => $user,
                ]));
            $mailer->send($email);
        } catch (TransportExceptionInterface|Exception) {
//                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
        }

        $this->addFlash('success', 'Mail de confirmation envoyé');
        if ($rappro->getTypeRappro() == 'C') {
            return $this->redirectToRoute('admin_rapprochement_check', [], Response::HTTP_SEE_OTHER);
        } elseif ($rappro->getTypeRappro() == 'V') {
            return $this->redirectToRoute('admin_rapprochement_transfer', [], Response::HTTP_SEE_OTHER);
        } else {
            return $this->redirectToRoute('admin_rapprochement_loan', [], Response::HTTP_SEE_OTHER);
        }
    }
}
