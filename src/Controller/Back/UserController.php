<?php

namespace App\Controller\Back;

use App\Entity\UserBack;
use App\Form\UserBackType;
use App\Repository\RoleRepository;
use App\Repository\UserBackRepository;
use App\Utilities\PasswordGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin/user')]
class UserController extends AbstractController
{
    /**
     * @param UserBackRepository $userRepository
     * @return Response
     */
    #[Route('/', name: 'admin_user_index', methods: ['GET'])]
    public function index(UserBackRepository $userRepository): Response
    {
        if (!$this->getUser()->hasMenu(3)) {
            throw new AccessDeniedException();
        }

        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @param Request $request
     * @param UserBackRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param UserPasswordHasherInterface $passwordHasher
     * @return Response
     */
    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserBackRepository $userRepository, RoleRepository $roleRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        if (!$this->getUser()->hasMenu(3)) {
            throw new AccessDeniedException();
        }

        $user = new UserBack();
        $form = $this->createForm(UserBackType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $nextval_user = 0;
            if (count($seq_user = $userRepository->findBy([], ['id' => 'DESC'])) > 0) {
                $nextval_user = $seq_user[0]->getId() + 1;
            } else {
                $nextval_user++;
            }

            $user->setId($nextval_user);

            $plaintextPassword = (new PasswordGenerator(12, 'lud'))->generateStrongPassword();
            $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
            $user->setPlainPassword($plaintextPassword);
            $user->setPassword($hashedPassword);
            
            $user->setName($request->get('user')['name']);
            $user->setFirstname($request->get('user')['firstname']);
            $user->setEmail($request->get('user')['email']);

            $user->setRole($role = $roleRepository->find(1));
            $user->setRoles((array)$role->getRole());

            $userRepository->save($user, true);
            $this->addFlash('success', 'L\'insertion a été un succès');
            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('admin/user/new.html.twig', [
            'form' => $form,
        ]);
   }
}
