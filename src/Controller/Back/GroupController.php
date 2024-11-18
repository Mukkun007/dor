<?php

namespace App\Controller\Back;

use App\Entity\Group;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use App\Repository\MenuRepository;
use App\Repository\UserBackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/group')]
class GroupController extends AbstractController
{
    /**
     * @param GroupRepository $groupRepository
     * @return Response
     */
    #[Route('/', name: 'admin_group_index', methods: ['GET'])]
    public function index(GroupRepository $groupRepository): Response
    {
        if (!$this->getUser()->hasMenu(2)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/group/index.html.twig', [
            'groups' => $groupRepository->findAll(),
        ]);
    }

    /**
     * @param Request $request
     * @param GroupRepository $groupRepository
     * @param UserBackRepository $userRepository
     * @param MenuRepository $menuRepository
     * @return Response
     */
    #[Route('/new', name: 'admin_group_new', methods: ['GET', 'POST'])]
    public function new(Request $request, GroupRepository $groupRepository, UserBackRepository $userRepository, MenuRepository $menuRepository): Response
    {
        if (!$this->getUser()->hasMenu(2)) {
            throw new AccessDeniedException();
        }

        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $nextval_group = 0;
            if (count($seq_group = $groupRepository->findBy([], ['id' => 'DESC'])) > 0) {
                $nextval_group = $seq_group[0]->getId() + 1;
            } else {
                $nextval_group++;
            }

            $group->setId($nextval_group);

            $group->setLibelle($request->get('group')['libelle']);

			if (array_key_exists('users', $request->get('group'))) {
				foreach ($request->get('group')['users'] as $_user) {
					$group->addUser($userRepository->find((int) $_user));
				}
			}

            if (array_key_exists('menus', $request->get('group'))) {
				foreach ($request->get('group')['menus'] as $_menu) {
					$group->addMenu($menu = $menuRepository->find((int) $_menu));
					$menu->addGroup($group);
				}
			}

            $groupRepository->save($group, true);
            if (array_key_exists('menus', $request->get('group')) && isset($menu)) {
				$menuRepository->save($menu, true);
			}
            $this->addFlash('success', 'L\'insertion a été un succès');
            return $this->redirectToRoute('admin_group_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('admin/group/new.html.twig', [
            'group' => $group,
            'form' => $form,
            'users' => $userRepository->findAll(),
            'menus' => $menuRepository->findAll(),
        ]);
    }

    /**
     * @param Request $request
     * @param Group $group
     * @param GroupRepository $groupRepository
     * @param UserBackRepository $userRepository
     * @param MenuRepository $menuRepository
     * @return Response
     */
    #[Route('/{id}/edit', name: 'admin_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Group $group, GroupRepository $groupRepository, UserBackRepository $userRepository, MenuRepository $menuRepository): Response
    {
        if (!$this->getUser()->hasMenu(2)) {
            throw new AccessDeniedException();
        }
        
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
            $group->setLibelle($request->get('group')['libelle']);

            foreach ($group->getUsers() as $gUser) {
                $group->removeUser($gUser);
            }
            if (array_key_exists('users', $request->get('group'))) {
				foreach ($request->get('group')['users'] as $_user) {
					$group->addUser($userRepository->find((int) $_user));
				}
			}

            foreach ($group->getMenus() as $gMenu) {
                $group->removeMenu($gMenu);
                $gMenu->removeGroup($group);
            }
            if (array_key_exists('menus', $request->get('group'))) {
				foreach ($request->get('group')['menus'] as $_menu) {
					$group->addMenu($menu = $menuRepository->find((int) $_menu));
					$menu->addGroup($group);
				}
			}

            $groupRepository->save($group, true);
            if (array_key_exists('menus', $request->get('group')) && isset($menu)) {
				$menuRepository->save($menu, true);
			}
            $this->addFlash('success', 'L\'édition a été un succès');
            return $this->redirectToRoute('admin_group_index', [], Response::HTTP_SEE_OTHER);
        }
 
        return $this->render('admin/group/edit.html.twig', [
            'group' => $group,
            'form' => $form,
            'users' => $userRepository->findAll(),
            'menus' => $menuRepository->findAll(),
        ]);
    }
}
