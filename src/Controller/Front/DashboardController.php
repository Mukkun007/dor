<?php

namespace App\Controller\Front;

use App\Utilities\Constant;
use App\Utilities\PasswordGenerator;
use App\Repository\OrderRepository;
use App\Repository\SettingRepository;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route('/', name: 'dashboard_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        $preOrder = $user->getPreorder();
        $typePaiement = $user->getPaymentMethod();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        switch ($preOrder->getFlagStatus()) {
            case Constant::STATUS_VALID:
            case Constant::STATUS_QUEUE:
            default:
                $currentStep = null;
                break;
            case Constant::STATUS_PAYMENT_WAIT:
            case Constant::STATUS_PAYMENT_WAIT_CHECK_ACCEPTED:
                $currentStep = 'paiement';
                break;
            case Constant::STATUS_PAID:
            case Constant::STATUS_APPOINTMENT:
                $currentStep = 'modeLivraison';
                break;
            case Constant::STATUS_DELIVERED:
                $currentStep = 'livraison';
                break;
        }

        // Vérifier si le segment correspond à un step valide
        $validSteps = ['paiement', 'modeLivraison', 'livraison'];
        if (in_array($currentStep, $validSteps)) {
            return $this->redirectToRoute('dashboard_' . $currentStep);
        }

        // Si le segment n'est pas valide, rediriger vers le premier step
        return $this->render('front/dashboard/index.html.twig', [
            'user' => $user,
            'typePaiement' => $typePaiement,
            'userRT' => Constant::USER_RT,
            'choiceMeeting' => Constant::USER_CHOICE_MEETING,
            'choiceLivraison' => Constant::USER_CHOICE_LIVRAISON,
        ]);
    }

    /**
     * @param Request $request
     * @param SluggerInterface $slugger
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/paiement', name: 'dashboard_paiement')] // Spécifiez la méthode POST pour cette route
    public function paiement(Request $request, SluggerInterface $slugger, OrderRepository $orderRepository): Response
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->redirectToRoute('app_login');
            }

            $preOrder = $user->getPreorder();

            $cheque = $preOrder->getCheque_number();
            $chequeDigits = str_split($cheque);
            $ordreVirement = $preOrder->getOv();
            $cinTierce = $preOrder->getCinTierce();
            $cinTierceDigits = str_split($cinTierce);

            $choices = [
                'USER_CHOICE_LIVRAISON_BANQUE' => Constant::USER_CHOICE_LIVRAISON_BANQUE,
                'USER_CHOICE_LIVRAISON_PLACE' => Constant::USER_CHOICE_LIVRAISON_PLACE,
                'USER_CHOICE_LIVRAISON_TIERCE' => Constant::USER_CHOICE_LIVRAISON_TIERCE,
            ];

            $typePaiement = $user->getPaymentMethod();
            if ($request->isMethod('POST')) {

                // Récupérez les données du formulaire
                $paiementData = $request->get('paiement');
                $dateActuelle = new DateTime();
                $dateFormatee = $dateActuelle->format('Y-m-d H:i:s');
                $preOrder->setDateOv($dateFormatee);

                if (isset($paiementData['cheque'])) {
                    // $cheque = implode('', $paiementData['cheque']);
                    $cheque = $paiementData['cheque'];
                    $preOrder->setCheque_number($cheque);
                }

                if (isset($paiementData['ordreVirement'])) {
                    $ov = $paiementData['ordreVirement'];
                    $preOrder->setOv($ov);
                }


                if (isset($request->files->get('paiement')['fichierOrdreVirement'])) {
                    /** @var UploadedFile $ribFile */

                    $file_ov = $request->files->get('paiement')['fichierOrdreVirement'];

                    $path = $this->getParameter('orders_directory') . $user->getReference();

                    if ($file_ov) {
                        $originalFilename = pathinfo($file_ov->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $file_ov->guessExtension() : $safeFilename . '.' . $file_ov->guessExtension();

                        $file_ov->move($path, $newFilename);
                        $preOrder->setFileOv($path . '/' . $newFilename);
                    }
                }

                if (isset($request->get('paiement')['choixRecuperation'])) {

                    $choiceLivraison = $paiementData['choixRecuperation'];

                    if ($choiceLivraison === 'depotBanque') {
                        $preOrder->setChoiceLivraison(Constant::USER_CHOICE_LIVRAISON_BANQUE);
                        $preOrder->setChoiceMeeting(null);
                        $preOrder->setUserRt(null);
                        $preOrder->setNameTierce("");
                        $preOrder->setFirstnameTierce("");
                        $preOrder->setCinTierce("");
                    } elseif ($choiceLivraison === 'surPlace') {
                        $preOrder->setChoiceLivraison(Constant::USER_CHOICE_LIVRAISON_PLACE);
                        if (isset($request->get('paiement')['choixMeeting'])) {
                            $choiceMeeting = $request->get('paiement')['choixMeeting'];
                            if ($choiceMeeting === "1") {
                                $preOrder->setChoiceMeeting(Constant::USER_CHOICE_MEETING_SIEGE);
                                $preOrder->setUserRt(null);
                            } else if ($choiceMeeting === "2") {
                                $preOrder->setChoiceMeeting(Constant::USER_CHOICE_MEETING_RT);
                                if (isset($request->get('paiement')['userRT'])) {
                                    $preOrder->setUserRt((int)$request->get('paiement')['userRT']);
                                }
                            }

                        }
                        $preOrder->setNameTierce("");
                        $preOrder->setFirstnameTierce("");
                        $preOrder->setCinTierce("");

                    } elseif ($choiceLivraison === 'tierce') {
                        $preOrder->setChoiceLivraison(Constant::USER_CHOICE_LIVRAISON_TIERCE);
                        if (isset($request->get('paiement')['choixMeeting'])) {
                            $choiceMeeting = $request->get('paiement')['choixMeeting'];
                            if ($choiceMeeting === "1") {
                                $preOrder->setChoiceMeeting(Constant::USER_CHOICE_MEETING_SIEGE);
                                $preOrder->setUserRt(null);
                            } else if ($choiceMeeting === "2") {
                                $preOrder->setChoiceMeeting(Constant::USER_CHOICE_MEETING_RT);
                                if (isset($request->get('paiement')['userRT'])) {
                                    $preOrder->setUserRt((int)$request->get('paiement')['userRT']);
                                }
                            }

                        }
                        if (isset($paiementData['nameTierce'])) {
                            $nameTierce = $paiementData['nameTierce'];
                            $preOrder->setNameTierce($nameTierce);
                        }

                        if (isset($paiementData['firstnameTierce'])) {
                            $firstnameTierce = $paiementData['firstnameTierce'];
                            $preOrder->setFirstnameTierce($firstnameTierce);
                        }

                        if (isset($paiementData['cinTierce'])) {
                            $cinTierce = implode('', $paiementData['cinTierce']);
                            $preOrder->setCinTierce($cinTierce);
                        }
                    }
                }

                $orderRepository->save($preOrder, true);

                $request->getSession()->set('paiementData', $paiementData);
                $this->addFlash('success', 'Données reçues !');

                return $this->redirectToRoute('dashboard_paiement', [], Response::HTTP_SEE_OTHER);

            } else {
                // Si la méthode de la requête n'est pas POST, redirigez l'utilisateur ou affichez un message d'erreur
                $content = $this->renderView('front/dashboard/_paiement.html.twig', [
                    'typePaiement' => $typePaiement,
                    'userRT' => Constant::USER_RT,
                    'choiceMeeting' => Constant::USER_CHOICE_MEETING,
                    'choiceLivraison' => Constant::USER_CHOICE_LIVRAISON,
                    'cinTierceDigits' => $cinTierceDigits,
                    'chequeDigits' => $chequeDigits,
                    'choices' => $choices,
                    'ordreVirement' => $ordreVirement,
                    'cheque' => $cheque,
                ]);

                return new Response($content);
            }

        } catch (\Exception $e) {
            // Gérer les erreurs éventuelles
            return new Response("Une erreur s'est produite : " . $e->getMessage(), 200);
        }
    }

    /**
     * @param Request $request
     * @param OrderRepository $orderRepository
     * @param SettingRepository $settingRepository
     * @return JsonResponse
     */
    #[Route('/verifDate', name: 'verification')]
    public function verifDate(Request $request, OrderRepository $orderRepository, SettingRepository $settingRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true); // Decode JSON data
        // Check if the date is present in the request
        if (isset($data['date'])) {
            $formattedDate = $data['date'];
            $existingEntries = $orderRepository->findBy([
                'dateCalendar' => $formattedDate,
            ]);
            $settings = $settingRepository->findBy(['name' => ['QUOTA_RDV_AM', 'QUOTA_RDV_PM']]);
            $maxAM = null;
            $maxPM = null;

            foreach ($settings as $setting) {
                if ($setting->getName() === "QUOTA_RDV_AM") {
                    // $maxAM = $setting->getValue();
                    $maxAM = intval($setting->getValue());
                } elseif ($setting->getName() === "QUOTA_RDV_PM") {
                    $maxPM = intval($setting->getValue());
                }
            }

            if (!isset($maxAM) || !isset($maxPM)) {
                return new JsonResponse(["message" => "Paramètres introuvables"], 404);
            }

            if ((count($existingEntries) < $maxAM + $maxPM) == 1) {
                return new JsonResponse(['message' => 'oui']);
            } else {
                return new JsonResponse(['message' => 'non']);
            }
        }

        return new JsonResponse(['message' => 'Erreur'], 400);
    }


    /**
     * @param Request $request
     * @param OrderRepository $orderRepository
     * @param SettingRepository $settingRepository
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/modeLivraison', name: 'dashboard_modeLivraison')] // Spécifiez la méthode POST pour cette route
    public function modeLivraison(Request $request, OrderRepository $orderRepository, SettingRepository $settingRepository, MailerInterface $mailer): Response
    {
        try {
            $user = $this->getUser();
            $order = $user->getPreorder();

            $dateRDV = $order->getDateCalendar();
            $typeRDV = $order->getTypeCalendar();
            $userRT = $order->getUserRt();

            $settings = $settingRepository->findBy(['name' => ['DEBUT_PLAGE', 'FIN_PLAGE']]);
            $dateDebut = null;
            $dateFin = null;

            $choices = [
                'USER_CHOICE_LIVRAISON_BANQUE' => Constant::USER_CHOICE_LIVRAISON_BANQUE,
                'USER_CHOICE_LIVRAISON_PLACE' => Constant::USER_CHOICE_LIVRAISON_PLACE,
                'USER_CHOICE_LIVRAISON_TIERCE' => Constant::USER_CHOICE_LIVRAISON_TIERCE,
            ];

            foreach ($settings as $setting) {
                if ($setting->getName() === "DEBUT_PLAGE") {
                    $dateDebut = $setting->getValue();
                } elseif ($setting->getName() === "FIN_PLAGE") {
                    $dateFin = $setting->getValue();
                }
            }

            if ($request->isMethod('POST')) {

                // $order->setReference("ORDiso");
                // $dateLivraison = $request->get('dateLivraison');
                if (isset ($request->get('dateLivraison')['startDate']) && isset ($request->get('dateLivraison')['selectTime']))
                    $startDate1 = $request->get('dateLivraison')['startDate'];
                $selectTime1 = $request->get('dateLivraison')['selectTime'];

                $startDate = new DateTime($startDate1);
                $rdvDate = new DateTime();
                $rdvDate1 = $rdvDate->format('Y-m-d H:i:s');
                $startDateString = $startDate->format('Y-m-d');
                $order->setDateCalendar($startDateString);
                $order->setTypeCalendar($selectTime1);
                $order->setDateRdv($rdvDate1);
                $order->setFlagStatus(Constant::STATUS_APPOINTMENT);

                if ($order->getChoiceLivraison() === 3) {
                    $order->setOtp((new PasswordGenerator(6, 'd'))->generateStrongPassword());
                }

                $existingEntries = $orderRepository->findBy([
                    'dateCalendar' => $startDateString,
                    'typeCalendar' => $selectTime1,
                ]);
                $settings = $settingRepository->findBy(['name' => ['QUOTA_RDV_AM', 'QUOTA_RDV_PM']]);
                $maxAM = null;
                $maxPM = null;

                foreach ($settings as $setting) {
                    if ($setting->getName() === "QUOTA_RDV_AM") {
                        $maxAM = $setting->getValue();
                    } elseif ($setting->getName() === "QUOTA_RDV_PM") {
                        $maxPM = $setting->getValue();
                    }
                }

                if (!isset($maxAM) || !isset($maxPM)) {
                    return new JsonResponse(["message" => "Paramètres introuvables"], 404);
                }

                // i&&($selectTime1 === 'AM' && count($existingEntries) < $maxAM) || ($selectTime1 === 'PM' && count($existingEntries) < $maxPM)) {
                if (($selectTime1 === 'AM' && count($existingEntries) < $maxAM) || ($selectTime1 === 'PM' && count($existingEntries) < $maxPM)) {
                    if ($request->get('insert') != null) {
                        $orderRepository->save($order, true);

                        //send mail
                        try {
                            $email = (new Email())
                                ->from('noreply@bfm.mg')
                                ->to($user->getEmail())
                                ->subject('Validation du rendez-vous')
                                ->html($this->renderView('mail/confirmation-rdv.html.twig', [
                                    'civility' => Constant::USER_CIVILITY,
                                    'user' => $user,
                                ]));
                            $mailer->send($email);
                        } catch (TransportExceptionInterface|Exception) {
//                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
                        }

                        if ($order->getChoiceLivraison() === 3) {
                            //send sms
                            try {
                                if (str_starts_with($user->getPhone(), '+261')) {
                                    $phone = ltrim($user->getPhone(), '+');
                                } elseif (str_starts_with($user->getPhone(), '00261')) {
                                    $phone = ltrim($user->getPhone(), '0');
                                } elseif (str_starts_with($user->getPhone(), '261')) {
                                    $phone = $user->getPhone();
                                } else {
                                    $phone = '261' . ltrim($user->getPhone(), '0');
                                }

                                $text = "Voici votre code à usage unique à présenter à la BFM lors de la récupération de votre pièce d'or : " . $order->getOtp();
                                $url = 'https://172.16.2.8:13002/cgi-bin/sendsms?username=user_test&password=azerty_1234&from=261340566555&to=' . urlencode($phone) . '&text=' . urlencode($text);

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $url);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                                curl_exec($ch);
//                if (curl_error($ch)) {
//                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du sms');
//                }
                                curl_close($ch);
                            } catch (Exception) {
//                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du sms');
                            }
                        }
                    }
                } else {
                    return new JsonResponse(["message" => "Vous ne pouvez plus commander pour ce jour"], 401);
                }
            }
        } catch (Exception $e) {
            return new Response("Une erreur s'est produite : " . $e->getMessage());
        }

        $commandes = $orderRepository->findAll();
        return $this->render('front/dashboard/_modeLivraison.html.twig', [
            'commandes' => $commandes,
            'variable2' => 'Valeur de la variable 2',
            'dateRDV' => $dateRDV,
            'typeRDV' => $typeRDV,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'choices' => $choices,
            'userRT'=> $userRT,
        ]);
    }

    /**
     * @param OrderRepository $orderRepository
     * @return JsonResponse
     */
    #[Route('/getOrder', name: 'getOrder')]
    public function getOrder(OrderRepository $orderRepository): JsonResponse
    {
        $orders = $orderRepository->findAll();

        foreach ($orders as $order) {
            $orderData = [
                'idOrder' => $order->getId(),
                'type' => $order->getTypeCalendar(),
                'date' => $order->getDateCalendar(),
            ];

            $data[] = $orderData;
        }
        return new JsonResponse($data);
    }

    /**
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/changeRdv', name: 'app_changeRdv')]
    public function changeRdv(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $order = $orderRepository->findOneBy(['reference' => $user->getReference()]);
        $order->setDateCalendar(null);
        $order->setTypeCalendar(null);
        $order->setFlagStatus(Constant::STATUS_PAID);
        $orderRepository->save($order, true);

        return $this->redirectToRoute('dashboard_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/livraison', name: 'dashboard_livraison')] // Spécifiez la méthode POST pour cette route
    public function livraison(Request $request): Response
    {
        
        return $this->render('front/dashboard/_livraison.html.twig');
    }
}

