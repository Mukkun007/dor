<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\OrStockRepository;
use App\Repository\RapproRepository;
use App\Repository\UserRepository;
use App\Utilities\Constant;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @param Security $security
     * @return Response
     */
    #[Route('/dispatch', name: 'app_dispatch')]
    public function dispatch(Security $security): Response
    {
        if ($this->isGranted('ROLE_CUSTOMER')) {
            return $this->redirectToRoute('dashboard_index', [], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash('error', 'Accès non autorisé');
            return $security->logout(false);
        }
    }

    /**
     * @return never
     * @throws Exception
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        // controller can be blank: it will never be called!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }

    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route('/admin/login', name: 'admin_login')]
    public function indexAdmin(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/admin.login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @param Security $security
     * @return Response
     */
    #[Route('/admin/dispatch', name: 'admin_dispatch')]
    public function dispatchAdmin(Security $security): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            if ($user->hasMenu(2)) {
                return $this->redirectToRoute('admin_group_index', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(3)) {
                return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(5)) {
                return $this->redirectToRoute('app_or_stock_index_back', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(7)) {
                return $this->redirectToRoute('admin_app_order_index', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(8)) {
                return $this->redirectToRoute('admin_app_order_filter_cancel', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(9)) {
                return $this->redirectToRoute('admin_app_order_queue', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(11)) {
                return $this->redirectToRoute('admin_payment_check', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(13)) {
                return $this->redirectToRoute('admin_rapprochement_check', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(14)) {
                return $this->redirectToRoute('admin_rapprochement_transfer', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(15)) {
                return $this->redirectToRoute('admin_rapprochement_loan', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(17)) {
                return $this->redirectToRoute('admin_livraison_depot_index', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(18)) {
                return $this->redirectToRoute('admin_livraison_siege_index', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(19)) {
                return $this->redirectToRoute('admin_livraison_rt_index', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(21)) {
                return $this->redirectToRoute('admin_setting_index', [], Response::HTTP_SEE_OTHER);
            } elseif ($user->hasMenu(22)) {
                return $this->redirectToRoute('admin_app_pays_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('error', 'Accès non autorisé');
                return $security->logout(false);
            }
        } else {
            $this->addFlash('error', 'Accès non autorisé');
            return $security->logout(false);
        }
    }

    /**
     * @return never
     * @throws Exception
     */
    #[Route('/admin/logout', name: 'admin_logout')]
    public function logoutAdmin(): never
    {
        // controller can be blank: it will never be called!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }

    /**
     * @param RapproRepository $rapproRepository
     * @param OrStockRepository $orStockRepository
     * @return Response
     */
    public function stock(RapproRepository $rapproRepository, OrStockRepository $orStockRepository): Response
    {
        $vir = $rapproRepository->count(['statutRappro' => [2, '2', '', null], 'typeRappro' => 'V']);
        $ppex = $rapproRepository->count(['statutRappro' => [2, '2', '', null], 'typeRappro' => 'P']);
        $orStockVendu = $orStockRepository->count(['estVendu' => 1]);
        $orStockAll = $orStockRepository->count([]);

        return $this->render('admin/_stock.html.twig', [
            'vir' => $vir,
            'ppex' => $ppex,
            'reel' => $orStockAll - $orStockVendu,
            'vendu' => $orStockVendu,
        ]);
    }

    /**
     * @param Order $id
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function bordereau(Order $id, OrderRepository $orderRepository): Response
    {
        return $this->render('admin/bordereau/bordereau.html.twig', [
            'order' => $orderRepository->find($id),
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    /**
     * @param Order $id
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function bordereauCheque(Order $id, OrderRepository $orderRepository): Response
    {
        return $this->render('admin/bordereau/bordereauCheque.html.twig', [
            'order' => $orderRepository->find($id),
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    /**
     * @param Order $id
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function accuseReception(Order $id, OrderRepository $orderRepository): Response
    {
        return $this->render('admin/bordereau/accuseReception.html.twig', [
            'order' => $orderRepository->find($id),
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    /**
     * @param Order $id
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function contratDeDepot(Order $id, OrderRepository $orderRepository): Response
    {
        return $this->render('admin/contratDeDepot/contratDeDepot.html.twig', [
            'order' => $orderRepository->find($id),
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    /**
     * @param Order $id
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function contratDeDepotPpex(Order $id, OrderRepository $orderRepository): Response
    {
        return $this->render('admin/contratDeDepot/contratDeDepotPPEX.html.twig', [
            'order' => $orderRepository->find($id),
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    /**
     * @param Order $id
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function bordereauPrelevement(Order $id, OrderRepository $orderRepository): Response
    {
        return $this->render('admin/bordereau/bordereauPrelevement.html.twig', [
            'order' => $orderRepository->find($id),
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    /**
     * @param Order $id
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function attestationDepot(Order $id, OrderRepository $orderRepository): Response
    {
        return $this->render('admin/attestationDepot/attestationDepot.html.twig', [
            'order' => $orderRepository->find($id),
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    /**
     * @param Order $id
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function facture(Order $id, OrderRepository $orderRepository): Response
    {
        return $this->render('admin/facture/facture.html.twig', [
            'order' => $orderRepository->find($id),
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    /**
     * @param $id
     * @param RapproRepository $rapproRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    public function row($id, RapproRepository $rapproRepository, UserRepository $userRepository): Response
    {
        $rappro = $rapproRepository->find($id);
        if (!$user = $userRepository->findOneBy(['reference' => $rappro->getReference()])) {
            $user = $userRepository->findOneBy(['reference' => $rappro->getNumDossier()]);
        }

        return $this->render('admin/rapprochement/row.html.twig', [
            'rappro' => $rappro,
            'user' => $user,
        ]);
    }
}
