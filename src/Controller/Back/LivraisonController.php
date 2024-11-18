<?php

namespace App\Controller\Back;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\OrStockRepository;
use App\Repository\SettingRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Utilities\Constant;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/livraison')]
class LivraisonController extends AbstractController
{
    /**
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/depot', name: 'admin_livraison_depot_index', methods: ['GET'])]
    public function indexDeposit(OrderRepository $orderRepository): Response
    {
        if (!$this->getUser()->hasMenu(17)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/livraison/index.depot.html.twig', [
            'orders' => $orderRepository->findAll(),
            'status' => Constant::STATUS_TYPE,
        ]);
    }

    /**
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/siege', name: 'admin_livraison_siege_index', methods: ['GET'])]
    public function indexSiege(OrderRepository $orderRepository): Response
    {
        if (!$this->getUser()->hasMenu(18)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/livraison/index.siege.html.twig', [
            'orders' => $orderRepository->findAll(),
            'status' => Constant::STATUS_TYPE,
        ]);
    }

    /**
     * @param OrderRepository $orderRepository
     * @return Response
     */
    #[Route('/rt', name: 'admin_livraison_rt_index', methods: ['GET'])]
    public function indexRt(OrderRepository $orderRepository): Response
    {
        if (!$this->getUser()->hasMenu(19)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/livraison/index.rt.html.twig', [
            'orders' => $orderRepository->findAll(),
            'rt' => Constant::USER_RT,
            'status' => Constant::STATUS_TYPE,
        ]);
    }

    /**
     * @param Request $request
     * @param Order $order
     * @param OrderRepository $orderRepository
     * @param OrStockRepository $orStockRepository
     * @param SettingRepository $settingRepository
     * @param SluggerInterface $slugger
     * @return Response
     */
    #[Route('/depot/{id}', name: 'admin_livraison_depot', methods: ['GET', 'POST'])]
    public function deposit(SettingRepository $settingRepository,Request $request, Order $order, OrderRepository $orderRepository, OrStockRepository $orStockRepository, SluggerInterface $slugger): Response
    {
        $orStockOrder = $orStockRepository->findBy(['referencePreorder' => $order->getReference()]);
        if ($request->isMethod('POST')) {
            ////---ancien code pur une piece
            // if ($request->get('orstock')) {
            //     $orstock = $orStockRepository->find((int)$request->get('orstock'));
            //     $orstock->setEstVendu(1);
            //     $orstock->setDateVente((new DateTime())->format('Y-m-d'));

            //     $order->setOrStock($orstock);
            //     $order->setFlagStatus(Constant::STATUS_DELIVERED);
            //     $order->setDateLivraison((new DateTime())->format('Y-m-d'));

            //     $orStockRepository->save($orstock, true);
            //     $orderRepository->save($order, true);

            //     $this->addFlash('success', 'Pièce attribuée');

            //     return $this->redirectToRoute('admin_livraison_depot', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
            // }
            //----fin

            if ($request->get('orstock')) {
                $orstockIds = $request->get('orstock'); // Ceci renvoie un tableau
                $quantityOr = count($orstockIds);
                $realQuantityOrder = $order->getUser();
                $realQuantity = $realQuantityOrder->getOrQuantity();
                if($quantityOr != $realQuantity ){
                    $this->addFlash('error', "Veuiller Sélectionné ".$realQuantity." pièces");
                    return $this->redirectToRoute('admin_livraison_depot', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);

                }
                else{
                    $settings = $settingRepository->findBy(['name' => ['STOCK_PREORDER','STOCK_VENDU']]);

                    foreach ($orstockIds as $orstockId) {
                        // Trouver chaque pièce par son ID
                        $orstock = $orStockRepository->find((int)$orstockId);
                        if ($orstock) {
                            $orstock->setEstVendu(1);
                            $orstock->setDateVente((new DateTime())->format('Y-m-d'));
    
                            // Associer cette pièce à la commande (optionnel si plusieurs peuvent être liées)
                            $orstock->setReferencePreorder($order->getReference()); //la relation est ManyToMany ou OneToMany
                            $order->setFlagStatus(Constant::STATUS_DELIVERED);
                            $order->setDateLivraison((new DateTime())->format('Y-m-d'));
    
                            // Sauvegarder les modifications de la pièce
                            $orStockRepository->save($orstock, true);
                        }
                    }
                    
                    foreach ($settings as $setting) {
                        if ($setting->getName() === "STOCK_PREORDER") {
                            $actualStockPreorder = $setting->getValue();
                            $setting->setValue(intval($actualStockPreorder) - $quantityOr);
                        }
                        elseif($setting->getName() === "STOCK_VENDU") {
                            $actualStockVendu = $setting->getValue();
                            $setting->setValue(intval($actualStockVendu) + $quantityOr);
                        }
            
                        $settingRepository->save($setting, true);
                    }
    
                    $orderRepository->save($order, true);
    
                    $this->addFlash('success', 'Pièces attribuées');
    
                    return $this->redirectToRoute('admin_livraison_depot', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
                }
                
            }

            if ($depositContractFile = $request->files->get('fileDepositContract')) {
                $depositAttestationFile = $request->files->get('fileDepositAttestation');
                $path = $this->getParameter('orders_directory') . $order->getReference();
                $pathAttestation = $this->getParameter('orders_directory') . $order->getReference();
                $filesystem = new Filesystem();
                $filesystemAttestation = new Filesystem();

                if ($order->getFileDepositContract() && $filesystem->exists($order->getFileDepositContract())) {
                    $filesystem->remove($order->getFileDepositContract());
                }
                
                if ($order->getFileDepositAttestation() && $filesystemAttestation->exists($order->getFileDepositAttestation())) {
                    $filesystemAttestation->remove($order->getFileDepositAttestation());
                }

                $originalFilename = pathinfo($depositContractFile->getClientOriginalName(), PATHINFO_FILENAME);
                $originalFilenameAttestation = pathinfo($depositAttestationFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $safeFilenameAttestation = $slugger->slug($originalFilenameAttestation);
                $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $depositContractFile->guessExtension() : $safeFilename . '.' . $depositContractFile->guessExtension();
                $newFilenameAttestation = (strlen($safeFilenameAttestation) > 20) ? substr($safeFilenameAttestation, 0, 20) . '.' . $depositAttestationFile->guessExtension() : $safeFilenameAttestation . '.' . $depositAttestationFile->guessExtension();

                $depositContractFile->move($path, $newFilename);
                $depositAttestationFile->move($pathAttestation, $newFilenameAttestation);
                $order->setFileDepositContract($path . '/' . $newFilename);
                $order->setFileDepositAttestation($pathAttestation . '/' . $newFilenameAttestation);
                $orderRepository->save($order, true);

                $this->addFlash('success', 'Contrat de dépot enregistré');

                return $this->redirectToRoute('admin_livraison_depot_index', [], Response::HTTP_SEE_OTHER);
            }

        }

        return $this->render('admin/livraison/_deposit.html.twig', [
            'order' => $order,
            'civility' => Constant::USER_CIVILITY,
            'orstock' => $orStockRepository->findBy(['estVendu' => 0]),
            'status' => Constant::STATUS_TYPE,
            'orStockOrder' => $orStockOrder,
        ]);
    }

    /**
     * @param Request $request
     * @param Order $order
     * @param OrderRepository $orderRepository
     * @param SettingRepository $settingRepository
     * @param OrStockRepository $orStockRepository
     * @param SluggerInterface $slugger
     * @return Response
     */
    #[Route('/siege/{id}', name: 'admin_livraison_siege', methods: ['GET', 'POST'])]
    public function siege(SettingRepository $settingRepository,Request $request, Order $order, OrderRepository $orderRepository, OrStockRepository $orStockRepository, SluggerInterface $slugger): Response
    {
        $orStockOrder = $orStockRepository->findBy(['referencePreorder' => $order->getReference()]);
        if ($request->isMethod('POST')) {
            // if ($request->get('orstock')) {
            //     $orstock = $orStockRepository->find((int)$request->get('orstock'));

            //     $orstock->setEstVendu(1);
            //     $orstock->setDateVente((new DateTime())->format('Y-m-d'));

            //     $order->setOrStock($orstock);
            //     $order->setFlagStatus(Constant::STATUS_DELIVERED);
            //     $order->setDateLivraison((new DateTime())->format('Y-m-d'));

            //     if ($request->get('tierceName')) {
            //         $tierceCivility = $request->get('tierceCivility');
            //         $tierceBirthday = $request->get('tierceBirthday');
            //         $tierceAddress = $request->get('tierceAddress');
            //         $tiercePhone = $request->get('tiercePhone');
            //         $tierceName = $request->get('tierceName');
            //         $tierceFirstname = $request->get('tierceFirstname');
            //         $tierceCin = $request->get('tierceCin');

            //         $order->setNameTierce($tierceName);
            //         $order->setFirstnameTierce($tierceFirstname);
            //         $order->setCinTierce($tierceCin);
            //         $order->setTierceCivility($tierceCivility);
            //         $order->setTierceBirthday($tierceBirthday);
            //         $order->setTierceAddress($tierceAddress);
            //         $order->setTiercePhone($tiercePhone);

            //         if ($tierceDuplicata = $request->get('tierceDuplicata')) {
            //             $order->setTierceDuplicata($tierceDuplicata);
            //         }
            //     }

            //     $orStockRepository->save($orstock, true);
            //     $orderRepository->save($order, true);

            //     $this->addFlash('success', 'Pièce attribuée');

            //     return $this->redirectToRoute('admin_livraison_siege', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
            // }
            if ($request->get('orstock')) {
                $orstockIds = $request->get('orstock'); // Ceci renvoie un tableau
                $quantityOr = count($orstockIds);
                $realQuantityOrder = $order->getUser();
                $realQuantity = $realQuantityOrder->getOrQuantity();
                if($quantityOr != $realQuantity ){
                    $this->addFlash('error', "Veuiller Sélectionné ".$realQuantity." pièces");
                    return $this->redirectToRoute('admin_livraison_depot', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);

                }else{
                    $settings = $settingRepository->findBy(['name' => ['STOCK_PREORDER','STOCK_VENDU']]);

                foreach ($orstockIds as $orstockId) {
                    // Trouver chaque pièce par son ID
                    $orstock = $orStockRepository->find((int)$orstockId);

                    if ($orstock) {
                        $orstock->setEstVendu(1);
                        $orstock->setDateVente((new DateTime())->format('Y-m-d'));

                        // Associer cette pièce à la commande (optionnel si plusieurs peuvent être liées)
                        $orstock->setReferencePreorder($order->getReference()); // Assurez-vous que la relation est ManyToMany ou OneToMany

                        $order->setFlagStatus(Constant::STATUS_DELIVERED);
                        $order->setDateLivraison((new DateTime())->format('Y-m-d'));

                        
                        
                        // Sauvegarder les modifications de la pièce
                        $orStockRepository->save($orstock, true);
                    }
                }
                if ($request->get('tierceName')) {
                    $tierceCivility = $request->get('tierceCivility');
                    $tierceBirthday = $request->get('tierceBirthday');
                    $tierceAddress = $request->get('tierceAddress');
                    $tiercePhone = $request->get('tiercePhone');
                    $tierceName = $request->get('tierceName');
                    $tierceFirstname = $request->get('tierceFirstname');
                    $tierceCin = $request->get('tierceCin');

                    $order->setNameTierce($tierceName);
                    $order->setFirstnameTierce($tierceFirstname);
                    $order->setCinTierce($tierceCin);
                    $order->setTierceCivility($tierceCivility);
                    $order->setTierceBirthday($tierceBirthday);
                    $order->setTierceAddress($tierceAddress);
                    $order->setTiercePhone($tiercePhone);

                    if ($tierceDuplicata = $request->get('tierceDuplicata')) {
                        $order->setTierceDuplicata($tierceDuplicata);
                    }
                }

                foreach ($settings as $setting) {
                    if ($setting->getName() === "STOCK_PREORDER") {
                        $actualStockPreorder = $setting->getValue();
                        $setting->setValue(intval($actualStockPreorder) - $quantityOr);
                    }
                    elseif($setting->getName() === "STOCK_VENDU") {
                        $actualStockVendu = $setting->getValue();
                        $setting->setValue(intval($actualStockVendu) + $quantityOr);
                    }
        
                    $settingRepository->save($setting, true);
                }

                $orderRepository->save($order, true);

                $this->addFlash('success', 'Pièces attribuées');

                return $this->redirectToRoute('admin_livraison_siege', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
                }
                
            }


            if ($accuseFile = $request->files->get('fileReception')) {
                $prelevementFile = $request->files->get('filePrelevement');
                $path = $this->getParameter('orders_directory') . $order->getReference();
                $filesystem = new Filesystem();
                $pathPrelevement = $this->getParameter('orders_directory') . $order->getReference();
                $filesystemPrelevement = new Filesystem();


                if ($order->getFileAccuse() && $filesystem->exists($order->getFileAccuse())) {
                    $filesystem->remove($order->getFileAccuse());
                }
                if ($order->getFilePrelevement() && $filesystemPrelevement->exists($order->getFilePrelevement())) {
                    $filesystemPrelevement->remove($order->getFilePrelevement());
                }

                $originalFilename = pathinfo($accuseFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $accuseFile->guessExtension() : $safeFilename . '.' . $accuseFile->guessExtension();

                $originalFilenamePrelevement = pathinfo($prelevementFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilenamePrelevement = $slugger->slug($originalFilenamePrelevement);
                $newFilenamePrelevement = (strlen($safeFilenamePrelevement) > 20) ? substr($safeFilenamePrelevement, 0, 20) . '.' . $prelevementFile->guessExtension() : $safeFilenamePrelevement . '.' . $prelevementFile->guessExtension();

                $accuseFile->move($path, $newFilename);
                $order->setFileAccuse($path . '/' . $newFilename);
                $prelevementFile->move($pathPrelevement, $newFilenamePrelevement);
                $order->setFilePrelevement($pathPrelevement . '/' . $newFilenamePrelevement);

                $orderRepository->save($order, true);

                $this->addFlash('success', 'Accusé de livraison et bordereau de prélèvement enregistrés');

                return $this->redirectToRoute('admin_livraison_siege_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('admin/livraison/_recuperationSiege.html.twig', [
            'order' => $order,
            'civility' => Constant::USER_CIVILITY,
            'orstock' => $orStockRepository->findBy(['estVendu' => 0]),
            'status' => Constant::STATUS_TYPE,
            'orStockOrder' => $orStockOrder,
        ]);
    }

    /**
     * @param Request $request
     * @param Order $order
     * @param OrderRepository $orderRepository
     * @param SettingRepository $settingRepository
     * @param OrStockRepository $orStockRepository
     * @param SluggerInterface $slugger
     * @return Response
     */
    #[Route('/rt/{id}', name: 'admin_livraison_rt', methods: ['GET', 'POST'])]
    public function rt(SettingRepository $settingRepository,Request $request, Order $order, OrderRepository $orderRepository, OrStockRepository $orStockRepository, SluggerInterface $slugger): Response
    {
        $orStockOrder = $orStockRepository->findBy(['referencePreorder' => $order->getReference()]);

        if ($request->isMethod('POST')) {
            // if ($request->get('orstock')) {
            //     $orstock = $orStockRepository->find((int)$request->get('orstock'));

            //     $orstock->setEstVendu(1);
            //     $orstock->setDateVente((new DateTime())->format('Y-m-d'));

            //     $order->setOrStock($orstock);
            //     $order->setFlagStatus(Constant::STATUS_DELIVERED);
            //     $order->setDateLivraison((new DateTime())->format('Y-m-d'));

            //     if ($request->get('tierceName')) {
            //         $tierceCivility = $request->get('tierceCivility');
            //         $tierceBirthday = $request->get('tierceBirthday');
            //         $tierceAddress = $request->get('tierceAddress');
            //         $tiercePhone = $request->get('tiercePhone');
            //         $tierceName = $request->get('tierceName');
            //         $tierceFirstname = $request->get('tierceFirstname');
            //         $tierceCin = $request->get('tierceCin');

            //         $order->setNameTierce($tierceName);
            //         $order->setFirstnameTierce($tierceFirstname);
            //         $order->setCinTierce($tierceCin);
            //         $order->setTierceCivility($tierceCivility);
            //         $order->setTierceBirthday($tierceBirthday);
            //         $order->setTierceAddress($tierceAddress);
            //         $order->setTiercePhone($tiercePhone);

            //         if ($tierceDuplicata = $request->get('tierceDuplicata')) {
            //             $order->setTierceDuplicata($tierceDuplicata);
            //         }
            //     }

            //     $orStockRepository->save($orstock, true);
            //     $orderRepository->save($order, true);

            //     $this->addFlash('success', 'Pièce attribuée');

            //     return $this->redirectToRoute('admin_livraison_rt', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
            // }
            if ($request->get('orstock')) {
                $orstockIds = $request->get('orstock'); // Ceci renvoie un tableau
                $quantityOr = count($orstockIds);
                $realQuantityOrder = $order->getUser();
                $realQuantity = $realQuantityOrder->getOrQuantity();
                if($quantityOr != $realQuantity ){
                    $this->addFlash('error', "Veuiller Sélectionné ".$realQuantity." pièces");
                    return $this->redirectToRoute('admin_livraison_depot', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);

                }else{
                    $settings = $settingRepository->findBy(['name' => ['STOCK_PREORDER','STOCK_VENDU']]);

                foreach ($orstockIds as $orstockId) {
                    // Trouver chaque pièce par son ID
                    $orstock = $orStockRepository->find((int)$orstockId);

                    if ($orstock) {
                        $orstock->setEstVendu(1);
                        $orstock->setDateVente((new DateTime())->format('Y-m-d'));

                        // Associer cette pièce à la commande (optionnel si plusieurs peuvent être liées)
                        $orstock->setReferencePreorder($order->getReference()); // Assurez-vous que la relation est ManyToMany ou OneToMany

                        $order->setFlagStatus(Constant::STATUS_DELIVERED);
                        $order->setDateLivraison((new DateTime())->format('Y-m-d'));

                        // Sauvegarder les modifications de la pièce
                        $orStockRepository->save($orstock, true);
                    }
                }

                if ($request->get('tierceName')) {
                    $tierceCivility = $request->get('tierceCivility');
                    $tierceBirthday = $request->get('tierceBirthday');
                    $tierceAddress = $request->get('tierceAddress');
                    $tiercePhone = $request->get('tiercePhone');
                    $tierceName = $request->get('tierceName');
                    $tierceFirstname = $request->get('tierceFirstname');
                    $tierceCin = $request->get('tierceCin');

                    $order->setNameTierce($tierceName);
                    $order->setFirstnameTierce($tierceFirstname);
                    $order->setCinTierce($tierceCin);
                    $order->setTierceCivility($tierceCivility);
                    $order->setTierceBirthday($tierceBirthday);
                    $order->setTierceAddress($tierceAddress);
                    $order->setTiercePhone($tiercePhone);

                    if ($tierceDuplicata = $request->get('tierceDuplicata')) {
                        $order->setTierceDuplicata($tierceDuplicata);
                    }
                } 

                foreach ($settings as $setting) {
                    if ($setting->getName() === "STOCK_PREORDER") {
                        $actualStockPreorder = $setting->getValue();
                        $setting->setValue(intval($actualStockPreorder) - $quantityOr);
                    }
                    elseif($setting->getName() === "STOCK_VENDU") {
                        $actualStockVendu = $setting->getValue();
                        $setting->setValue(intval($actualStockVendu) + $quantityOr);
                    }
        
                    $settingRepository->save($setting, true);
                }
                
                $orderRepository->save($order, true);

                $this->addFlash('success', 'Pièces attribuées');

                return $this->redirectToRoute('admin_livraison_rt', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
                }
                
            }

            if ($accuseFile = $request->files->get('fileReception')) {
                $path = $this->getParameter('orders_directory') . $order->getReference();
                $filesystem = new Filesystem();

                if ($order->getFileAccuse() && $filesystem->exists($order->getFileAccuse())) {
                    $filesystem->remove($order->getFileAccuse());
                }

                $originalFilename = pathinfo($accuseFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $accuseFile->guessExtension() : $safeFilename . '.' . $accuseFile->guessExtension();

                $accuseFile->move($path, $newFilename);
                $order->setFileAccuse($path . '/' . $newFilename);
                $orderRepository->save($order, true);

                $this->addFlash('success', 'Accusé de livraison enregistré');

                return $this->redirectToRoute('admin_livraison_siege_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('admin/livraison/_recuperationRt.html.twig', [
            'order' => $order,
            'civility' => Constant::USER_CIVILITY,
            'rt' => Constant::USER_RT,
            'orstock' => $orStockRepository->findBy(['estVendu' => 0]),
            'status' => Constant::STATUS_TYPE,
            'orStockOrder' => $orStockOrder,
        ]);
    }
}
