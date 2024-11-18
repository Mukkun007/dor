<?php

namespace App\Controller\Back;

use App\Entity\Setting;
use App\Form\SettingType;
use App\Repository\SettingRepository;
use App\Utilities\Constant;
use DateTimeImmutable;
use App\Repository\OrStockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/setting')]
class SettingController extends AbstractController
{
    /**
     * @param SettingRepository $settingRepository
     * @param OrStockRepository $orStockRepository
     * @return Response
     */
    #[Route('/', name: 'admin_setting_index', methods: ['GET'])]
    public function index(SettingRepository $settingRepository,OrStockRepository $orStockRepository): Response
    {
        if (!$this->getUser()->hasMenu(21)) {
            throw new AccessDeniedException();
        }
        $settings = $settingRepository->findBy(['name' => ['STOCK_ACTUEL','STOCK_PREORDER','STOCK_VENDU']]);
        $orStockAll = $orStockRepository->count([]);
        $orStockVendu = $orStockRepository->count(['estVendu' => 1]);
        $orStockActual = $orStockAll - $orStockVendu;

        $actualStock = null;
        $orPreorder = null;
      

        foreach ($settings as $setting) {
            if ($setting->getName() === "STOCK_ACTUEL") {
                // $maxAM = $setting->getValue();
                $setting->setValue($orStockActual);
                
            } //elseif ($setting->getName() === "STOCK_PREORDER") {
            //     $setting->getValue();
            // }
            elseif($setting->getName() === "STOCK_VENDU") {
                $setting->setValue($orStockVendu);
            }

            $settingRepository->save($setting, true);
        }

       

        return $this->render('admin/setting/index.html.twig', [
            'settings' => $settingRepository->findAll(),
            'orStockVendu' =>$orStockVendu,
            'actualStock' =>$actualStock,
        ]);
    }

    /**
     * @param Request $request
     * @param SettingRepository $settingRepository
     * @return Response
     */
    #[Route('/nationality', name: 'admin_setting_nationality', methods: ['GET', 'POST'])]
    public function nationality(Request $request, SettingRepository $settingRepository): Response
    {
        if (!$this->getUser()->hasMenu(21)) {
            throw new AccessDeniedException();
        }

        $setting = new Setting();
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $check = $form->get('value')->getData();
            $verifSetting = $settingRepository->findSettingByName(Constant::NAME_CHECKBOX_NATIONALITE, $check);
            $verifSetting != null ? $this->addFlash('success', 'Paramètre mise à jour') : $this->addFlash('error', 'Une erreur s\'est produite');
            return $this->redirectToRoute('admin_setting_nationality', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/setting/nationality.html.twig', [
            'setting' => $setting,
            'valeur' => $settingRepository->findOneBy(['name' => Constant::NAME_CHECKBOX_NATIONALITE]),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param SettingRepository $settingRepository
     * @return Response
     */
    #[Route('/campaign', name: 'admin_setting_campaign', methods: ['GET', 'POST'])]
    public function campaign(Request $request, SettingRepository $settingRepository): Response
    {
        if (!$this->getUser()->hasMenu(21)) {
            throw new AccessDeniedException();
        }

        $setting = new Setting();
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $check = $form->get('value')->getData();
            $verifSetting = $settingRepository->findSettingByName(Constant::NAME_CAMPAIGN_END_DATE, $check);
            $verifSetting != null ? $this->addFlash('success', 'Paramètre mise à jour') : $this->addFlash('error', 'Une erreur s\'est produite');
            return $this->redirectToRoute('admin_setting_campaign', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/setting/campaign.html.twig', [
            'setting' => $setting,
            'valeur' => $settingRepository->findOneBy(['name' => Constant::NAME_CAMPAIGN_END_DATE]),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param SettingRepository $settingRepository
     * @return Response
     */
    #[Route('/cgv', name: 'admin_setting_cgv', methods: ['GET', 'POST'])]
    public function cgv(Request $request, SettingRepository $settingRepository): Response
    {
        if (!$this->getUser()->hasMenu(21)) {
            throw new AccessDeniedException();
        }

        $setting = new Setting();
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $check = $form->get('value')->getData();
            $verifSetting = $settingRepository->findSettingByName(Constant::NAME_CGV, $check);
            $verifSetting != null ? $this->addFlash('success', 'Paramètre mise à jour') : $this->addFlash('error', 'Une erreur s\'est produite');
            return $this->redirectToRoute('admin_setting_cgv', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/setting/page.html.twig', [
            'setting' => $setting,
            'title' => Constant::NAME_CGV,
            'valeur' => $settingRepository->findOneBy(['name' => Constant::NAME_CGV]),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param SettingRepository $settingRepository
     * @return Response
     */
    #[Route('/faq', name: 'admin_setting_faq', methods: ['GET', 'POST'])]
    public function faq(Request $request, SettingRepository $settingRepository): Response
    {
        if (!$this->getUser()->hasMenu(21)) {
            throw new AccessDeniedException();
        }

        $setting = new Setting();
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $check = $form->get('value')->getData();
            $verifSetting = $settingRepository->findSettingByName(Constant::NAME_FAQ, $check);
            $verifSetting != null ? $this->addFlash('success', 'Paramètre mise à jour') : $this->addFlash('error', 'Une erreur s\'est produite');
            return $this->redirectToRoute('admin_setting_faq', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/setting/page.html.twig', [
            'setting' => $setting,
            'title' => Constant::NAME_FAQ,
            'valeur' => $settingRepository->findOneBy(['name' => Constant::NAME_FAQ]),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Setting $setting
     * @param SettingRepository $settingRepository
     * @return Response
     */
    #[Route('/{id}/edit', name: 'admin_setting_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Setting $setting, SettingRepository $settingRepository): Response
    {
        if (!$this->getUser()->hasMenu(21)) {
            throw new AccessDeniedException();
        }
        
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $setting->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));

            $settingRepository->save($setting, true);

            $this->addFlash('success', 'Paramètre mise à jour');
            return $this->redirectToRoute('admin_setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/setting/edit.html.twig', [
            'setting' => $setting,
            'form' => $form->createView(),
        ]);
    }
}
