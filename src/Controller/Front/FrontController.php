<?php

namespace App\Controller\Front;

use App\Entity\Order;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\OrderRepository;
use App\Repository\PaysRepository;
use App\Repository\RoleRepository;
use App\Repository\SettingRepository;
use App\Repository\UserRepository;
use App\Repository\OrStockRepository;
use App\Utilities\Constant;
use App\Utilities\PasswordGenerator;
use DateTime;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

#[Route('/')]
class FrontController extends AbstractController
{
    /**
     * @param SettingRepository $settingRepository
     * @return Response
     */
    #[Route('/', name: 'app_index', methods: ['GET'])]
    public function index(SettingRepository $settingRepository): Response
    {
        $show = true;

        if ($setting = $settingRepository->findOneBy(['name' => Constant::NAME_CAMPAIGN_END_DATE])) {
            $campaign = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $setting->getValue() . ' 00:00:00');
            $now = new DateTimeImmutable();
            $show = $now > $campaign;
        }

        return $this->render('front/index.html.twig', [
            'campaign' => $show
        ]);
    }

    /**
     * @param SettingRepository $settingRepository
     * @return Response
     */
    #[Route('/more', name: 'app_more', methods: ['GET'])]
    public function more(SettingRepository $settingRepository): Response
    {
        $show = true;

        if ($setting = $settingRepository->findOneBy(['name' => Constant::NAME_CAMPAIGN_END_DATE])) {
            $campaign = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $setting->getValue() . ' 00:00:00');
            $now = new DateTimeImmutable();
            $show = $now > $campaign;
        }

        return $this->render('front/more.html.twig', [
            'price' => $settingRepository->findOneBy(['name' => 'PRIX_UNITAIRE_OR'])->getValue(),
            'campaign' => $show
        ]);
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @param SluggerInterface $slugger
     * @return Response
     */
    #[Route('/customer/myProfil', name: 'update_user', methods: ['GET', 'POST'])]
    public function updateUser(Request $request, UserRepository $userRepository, SluggerInterface $slugger): Response
    {
        $user = $userRepository->findOneBy(['reference' => $this->getUser()->getReference()]);
        $cin = $user->getCin();
        $rib = $user->getRib();
        $civility = Constant::USER_CIVILITY[$user->getCivility()];
        $maritalStatus = Constant::USER_MARITAL_STATUS[$user->getMaritalStatus()];
        $account = Constant::USER_ACCOUNT[$user->getAccount()];
        $country = $user->getCountry();
        $currentPassport = $user->getPassport();
        $currentPassportExp = $user->getPassportExp();
        $fileRib = $user->getFileRib();
        $fileAffiliation = $user->getFileAffiliation();
        $fileIban = $user->getFileIban();
        $fileCin = $user->getFileCin();
        $filePassport = $user->getFilePassport();
        $groupSizes = [5, 5, 11, 2];
        $start = 0;
        $paymentMethod = $user->getPaymentMethod();

        foreach ($groupSizes as $groupSize) {
            $start += $groupSize;
        }

        $cinDigits = str_split($cin);
        $ribDigits = str_split($rib);

        $birthday = $user->getBirthday();
        if ($birthday && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $user->getBirthday())) {
            $expBirth = explode('/', $user->getBirthday());
            $birthday = $expBirth[2] . '-' . $expBirth[1] . '-' . $expBirth[0];
        }

        $passeportExp = $user->getPassportExp();
        if ($passeportExp && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $user->getPassportExp())) {
            $expPass = explode('/', $user->getPassportExp());
            $passeportExp = $expPass[2] . '-' . $expPass[1] . '-' . $expPass[0];
        }

       

        // update information de l'utilisateur
        if ($request->isMethod('POST')) {
            $userData = $request->get('user');
            $userFile = $request->files->get('user');

            if (isset($userData['name'])) {
                $user->setName($userData['name']);
            }
            if (isset($userData['firstname'])) {
                $user->setFirstname($userData['firstname']);
            }
            if (isset($userData['address'])) {
                $user->setAddress($userData['address']);
            }
            if (isset($userData['city'])) {
                $user->setCity($userData['city']);
            }
            if (isset($userData['email'])) {
                $user->setAddress($userData['email']);
            }
            if (isset($userData['phone'])) {
                $user->setPhone($userData['phone']);
            }

            $allowedStatuses = [
                Constant::STATUS_WAIT,
                Constant::STATUS_VALID,
                Constant::STATUS_QUEUE,
                Constant::STATUS_PAYMENT_WAIT,
                Constant::STATUS_PAYMENT_WAIT_CHECK_ACCEPTED,
                Constant::STATUS_PAYMENT_WAIT_CHECK_REFUSED,
            ];
            
            $preorderUser = $user->getPreorder();
            if (in_array($preorderUser->getFlagStatus(), $allowedStatuses, true)) {
                $user->setOrQuantity($userData['orQuantity']);
            } else {
                // Ajouter un message flash si la modification n'est pas possible
                $this->addFlash('error', 'Modification non possible pour ce statut.');
                return $this->redirectToRoute('update_user', [], Response::HTTP_SEE_OTHER); // Redirection vers la page d'accueil après la mise à jour
            }

            $path = $this->getParameter('orders_directory') . $user->getReference();
            $filesystem = new Filesystem();

            if (isset($userData['account']) && (int)$userData['account'] !== $user->getAccount()) {
                // Supprimer les données associées au type de compte précédent
                switch ($user->getAccount()) {
                    case 1: // Ancien compte : RIB
                        // Supprimer les données du RIB
                        $user->setRib(null);
                        $user->setFileRib(null);
                        break;
                    case 2: // Ancien compte : IMF
                        // Supprimer les données de l'IMF
                        $user->setAffiliation(null);
                        $user->setFileAffiliation(null);
                        break;
                    case 3: // Ancien compte : Banque extérieure
                        // Supprimer les données de l'IBAN et du SWIFT
                        $user->setIban(null);
                        $user->setSwift(null);
                        $user->setFileIban(null);
                        break;
                    // Ajoutez d'autres cas si nécessaire pour d'autres types de compte
                }

                // Mettre à jour le nouveau compte de l'utilisateur
                $user->setAccount($userData['account']);

                // Ajouter les nouvelles données associées au nouveau type de compte
                switch ($userData['account']) {
                    case 1: // Nouveau compte : RIB
                        $user->setRib($userData['rib']);
                        break;
                    case 2: // Nouveau compte : IMF
                        $user->setAffiliation($userData['affiliation']);
                        break;
                    case 3: // Nouveau compte : Banque extérieure
                        $user->setIban($userData['iban']);
                        $user->setSwift($userData['swift']);
                        break;
                    // Ajoutez d'autres cas si nécessaire pour d'autres types de compte
                }
            } elseif (isset($userData['account']) && (int)$userData['account'] === $user->getAccount()) {
                // Mettre à jour le nouveau compte de l'utilisateur
                $user->setAccount($userData['account']);

                // Ajouter les nouvelles données associées au nouveau type de compte
                switch ($userData['account']) {
                    case 1: // Nouveau compte : RIB
                        $user->setRib($userData['rib']);
                        try {
                            /** @var UploadedFile $ribFile */
                            $ribFile = $userFile['ribFile'];
                            if ($ribFile) {
                                if ($user->getFileRib() && $filesystem->exists($user->getFileRib())) {
                                    $filesystem->remove($user->getFileRib());
                                }

                                $originalFilename = pathinfo($ribFile->getClientOriginalName(), PATHINFO_FILENAME);
                                $safeFilename = $slugger->slug($originalFilename);
                                $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $ribFile->guessExtension() : $safeFilename . '.' . $ribFile->guessExtension();

                                $ribFile->move($path, $newFilename);
                                $user->setFileRib($path . '/' . $newFilename);
                            }
                        } catch (FileException|Exception $e) {
                            var_dump($e->getMessage());
                            exit;
//                    $this->addFlash('error', 'Une erreur est survenue lors du téléversement du RIB');
                        }
                        break;
                    case 2: // Nouveau compte : IMF
                        $user->setAffiliation($userData['affiliation']);
                        try {
                            /** @var UploadedFile $affiliationFile */
                            $affiliationFile = $userFile['affiliationFile'];
                            if ($affiliationFile) {
                                if ($user->getFileAffiliation() && $filesystem->exists($user->getFileAffiliation())) {
                                    $filesystem->remove($user->getFileAffiliation());
                                }

                                $originalFilename = pathinfo($affiliationFile->getClientOriginalName(), PATHINFO_FILENAME);
                                $safeFilename = $slugger->slug($originalFilename);
                                $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $affiliationFile->guessExtension() : $safeFilename . '.' . $affiliationFile->guessExtension();

                                $affiliationFile->move($path, $newFilename);
                                $user->setFileAffiliation($path . '/' . $newFilename);
                            }
                        } catch (FileException|Exception) {
//                        $this->addFlash('error', 'Une erreur est survenue lors du téléversement de l\'affiliation');
                        }
                        break;
                    case 3: // Nouveau compte : Banque extérieure
                        $user->setIban($userData['iban']);
                        $user->setSwift($userData['swift']);
                        try {
                            /** @var UploadedFile $ibanFile */
                            $ibanFile = $userFile['ibanFile'];
                            if ($ibanFile) {
                                if ($user->getFileIban() && $filesystem->exists($user->getFileIban())) {
                                    $filesystem->remove($user->getFileIban());
                                }

                                $originalFilename = pathinfo($ibanFile->getClientOriginalName(), PATHINFO_FILENAME);
                                $safeFilename = $slugger->slug($originalFilename);
                                $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $ibanFile->guessExtension() : $safeFilename . '.' . $ibanFile->guessExtension();

                                $ibanFile->move($path, $newFilename);
                                $user->setFileIban($path . '/' . $newFilename);
                            }
                        } catch (FileException|Exception) {
//                        $this->addFlash('error', 'Une erreur est survenue lors du téléversement de l\'iban');
                        }
                        break;
                    // Ajoutez d'autres cas si nécessaire pour d'autres types de compte
                }
            }

            if (isset($userData['typePaiement']) && $userData['typePaiement'] !== $user->getPaymentMethod()) {
                if ($userData['typePaiement'] === 'virement') {
                    $user->setPaymentMethod(Constant::USER_PAYMENT_METHOD_VIREMENT);
                } elseif ($userData['typePaiement'] === 'cheque') {
                    $user->setPaymentMethod(Constant::USER_PAYMENT_METHOD_CHEQUE);
                }
            }

            // Vérifier si le champ "passport" a été modifié
            if (isset($userData['passport']) && isset($userData['passportExp'])) {
                // Vérifie si les champs "passport" et "passportExp" ont été modifiés
                if ($userData['passport'] !== $currentPassport || $userData['passportExp'] !== $currentPassportExp) {
                    // Redirection uniquement si les deux champs ont été modifiés
                    $user->setPassport($userData['passport']);
                    $user->setPassportExp($userData['passportExp']);
                }
            } elseif (isset($userData['passport']) || isset($userData['passportExp'])) {
                // Si l'un des champs est modifié mais pas l'autre, affichez un message d'erreur
                $this->addFlash('error', 'Vous devez modifier à la fois le passeport et sa date d\'expiration.');
                return $this->redirectToRoute('update_user', [], Response::HTTP_SEE_OTHER);
            }

            try {
                /** @var UploadedFile $passportFile */
                $passportFile = $userFile['passportFile'];
                if ($passportFile) {
                    if ($user->getFilePassport() && $filesystem->exists($user->getFilePassport())) {
                        $filesystem->remove($user->getFilePassport());
                    }

                    $originalFilename = pathinfo($passportFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $passportFile->guessExtension() : $safeFilename . '.' . $passportFile->guessExtension();

                    $passportFile->move($path, $newFilename);
                    $user->setFilePassport($path . '/' . $newFilename);
                }
            } catch (FileException|Exception) {
//                        $this->addFlash('error', 'Une erreur est survenue lors du téléversement du passeport');
            }

            try {
                /** @var UploadedFile $cinFile */
                $cinFile = $userFile['cinFile'];
                if ($cinFile) {
                    if ($user->getFileCin() && $filesystem->exists($user->getFileCin())) {
                        $filesystem->remove($user->getFileCin());
                    }

                    $originalFilename = pathinfo($cinFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $cinFile->guessExtension() : $safeFilename . '.' . $cinFile->guessExtension();

                    $cinFile->move($path, $newFilename);
                    $user->setFileCin($path . '/' . $newFilename);
                }
            } catch (FileException|Exception) {
//                        $this->addFlash('error', 'Une erreur est survenue lors du téléversement de la CIN');
            }

            $userRepository->save($user, true);

            $this->addFlash('success', 'Modification enregistrée !');
            return $this->redirectToRoute('update_user', [], Response::HTTP_SEE_OTHER); // Redirection vers la page d'accueil après la mise à jour
        }

        return $this->render('front/myProfil/myProfil.html.twig', [
            'cinDigits' => $cinDigits,
            '_birthday' => $birthday,
            '_passportExp' => $passeportExp,
            'rib' => $rib,
            'ribDigits' => $ribDigits,
            'civility' => $civility,
            'maritalStatus' => $maritalStatus,
            'account' => $account,
            'country' => $country,
            'accountOptions' => Constant::USER_ACCOUNT,
            'civilityOptions' => Constant::USER_CIVILITY,
            'maritalStatusOptions' => Constant::USER_MARITAL_STATUS,
            'fileRib' => $fileRib,
            'fileAffiliation' => $fileAffiliation,
            'fileIban' => $fileIban,
            'fileCin' => $fileCin,
            'filePassport' => $filePassport,
            'paymentMethod' => $paymentMethod,
        ]);
    }

//    /**
//     * @param Request $request
//     * @param UserPasswordHasherInterface $passwordHasher
//     * @param ValidatorInterface $validator
//     * @param UserRepository $userRepository
//     * @param MailerInterface $mailer
//     * @return Response
//     */
//    #[Route('/forgotPassword', name: 'user_mdpForgot')]
//    public function forgotPassword(Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, UserRepository $userRepository, MailerInterface $mailer): Response
//    {
//        //send mail
//        if ($request->isMethod('POST')) {
//            $reference = $request->get('user')['reference'];
//            if ($reference) {
//                // Trouver l'utilisateur par la référence
//                $user = $userRepository->findOneBy(['reference' => $reference]);
//
//                if ($user) {
//                    // Générer un mot de passe temporaire
//                    $temporaryPassword = 'ChangeMoi123456!'; // Vous pouvez rendre ce mot de passe plus complexe
//
//                    // Encoder le mot de passe
//                    $encodedPassword = $passwordHasher->hashPassword($user, $temporaryPassword);
//                    $user->setPlainPassword($temporaryPassword);
//                    $user->setPassword($encodedPassword);
//                    $user->setPasswordChanged(false);
//                    $userRepository->save($user, true);
//
//                    //send mail
//                    try {
//                        $email = (new Email())
//                            ->from('noreply@bfm.mg')
//                            ->to($user->getEmail())
//                            ->subject('Réinitialisation de mot de passe')
//                            ->html($this->renderView('mail/mail-forgotPassword.html.twig', [
//                                'civility' => Constant::USER_CIVILITY,
//                                'user' => $user,
//                            ]));
//                        $mailer->send($email);
//                    } catch (TransportExceptionInterface|Exception) {
//                        // $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
//                    }
//
//                    $this->addFlash('success', 'Votre demande de réinitialisation de mot de passe a été prise en compte. Votre identification et mot de passe seront transmis à l\'adresse email: ' . $user->getEmail());
//
//                    return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER); // Rediriger vers la page d'accueil ou une autre page
//                } else {
//                    $this->addFlash('error', 'Numéro dossier incorrect.');
//                }
//            } else {
//                $this->addFlash('error', 'Veuillez entrer un numéro dossier valide.');
//            }
//        }
//
//        return $this->render('front/myProfil/forgotPassword.html.twig');
//    }

    /**
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordHasher
     * @param ValidatorInterface $validator
     * @param UserRepository $userRepository
     * @param MailerInterface $mailer
     * @return Response
     */

    #[Route('/customer/changePassword', name: 'user_mdp')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        $passwordChanged = $user->getPasswordChanged();

        if ($request->isMethod('POST')) {
            $oldPassword = $request->get('user')['oldPassword'];
            $newPassword = $request->get('user')['newPassword'];
            $confirmNewPassword = $request->get('user')['confirmNewPassword'];

            // Vérification si le nouveau mot de passe respecte les critères
            $errors = [];
            $constraintLength = new Length(['min' => 10]);
            $violations = $validator->validate($newPassword, $constraintLength);

            if (count($violations) > 0) {
                $errors[] = 'Le mot de passe doit comporter au moins 10 caractères.';
            }

            $constraintRegex = new Regex([
                'pattern' => '/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_])/',
                'message' => 'Le mot de passe doit inclure au moins une lettre minuscule, une lettre majuscule, un chiffre et un symbole.'
            ]);
            $violations = $validator->validate($newPassword, $constraintRegex);

            if (count($violations) > 0) {
                $this->addFlash('error', 'Le mot de passe doit inclure au moins une lettre minuscule, une lettre majuscule, un chiffre et un symbole.');
                return $this->redirectToRoute('user_mdp', [], Response::HTTP_SEE_OTHER);
            }

            // Vérification si le mot de passe actuel est correct
            if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel saisi est incorrect');
                return $this->redirectToRoute('user_mdp', [], Response::HTTP_SEE_OTHER);
            }

            // Vérification si le mot de passe actuel est correct
            if ($newPassword !== $confirmNewPassword) {
                $this->addFlash('error', 'Les 2 mots de passe ne sont pas identiques');
                return $this->redirectToRoute('user_mdp', [], Response::HTTP_SEE_OTHER);
            }

            if (empty($errors)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPlainPassword($newPassword);
                $user->setPassword($hashedPassword);
                $user->setPasswordChanged(true);

                $userRepository->save($user, true);

                //send mail
                try {
                    $email = (new Email())
                        ->from('noreply@bfm.mg')
                        ->to($user->getEmail())
                        ->subject('Confirmation de changement de mot de passe')
                        ->html($this->renderView('mail/confirmation-changement-mot-de-passe.html.twig', [
                            'civility' => Constant::USER_CIVILITY,
                            'user' => $user,
                        ]));
                    $mailer->send($email);
                } catch (TransportExceptionInterface|Exception) {
//                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
                }

                $this->addFlash('success', 'Votre mot de passe a été changé avec succès.');
                return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->redirectToRoute('user_mdp', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('front/myProfil/changePassword.html.twig', [
            'passwordChanged' => $passwordChanged,
        ]);
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @param OrderRepository $orderRepository
     * @param OrStockRepository $orStockRepository
     * @param PaysRepository $paysRepository
     * @param SettingRepository $settingRepository
     * @param UserPasswordHasherInterface $passwordHasher
     * @param RoleRepository $roleRepository
     * @param SluggerInterface $slugger
     * @param MailerInterface $mailer
     * @return Response
     */
    #[Route('/signup', name: 'app_signup', methods: ['POST', 'GET'])]
    public function signup(Request $request, UserRepository $userRepository, OrderRepository $orderRepository, OrStockRepository $orStockRepository, PaysRepository $paysRepository, SettingRepository $settingRepository, UserPasswordHasherInterface $passwordHasher, RoleRepository $roleRepository, SluggerInterface $slugger, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $nationality = $settingRepository->findOneBy(['name' => Constant::NAME_CHECKBOX_NATIONALITE]);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification de la date de naissance
            $userBirthdayInput = $request->get('user')['birthday'];

            // Si la date est vide (le champ n'est pas requis)
            if (empty($userBirthdayInput)) {
                $user->setBirthday("");
            } else {
                if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $userBirthdayInput)) {
                    $userBirthday = DateTime::createFromFormat('Y-m-d', $userBirthdayInput);
                } else {
                    $userBirthday = DateTime::createFromFormat('d/m/Y', $userBirthdayInput);
                }
    
                $today = new DateTime();
              
    
                // Vérification si la date de naissance est au bon format (AAAA-MM-JJ)
                if (!$userBirthday || ($userBirthday->format('d/m/Y') !== $userBirthdayInput && $userBirthday->format('Y-m-d') !== $userBirthdayInput) || $userBirthday > $today) {
                    return $this->render('front/signup.html.twig', [
                        '_error' => 'Veuillez entrer une date de naissance valide. Merci !',
                        'user' => $user,
                        'form' => $form->createView(),
                        'civility' => Constant::USER_CIVILITY,
                        'marital_status' => Constant::USER_MARITAL_STATUS,
                        'nationality' => $nationality,
                        'account' => Constant::USER_ACCOUNT,
                        'countries' => $paysRepository->findAll(),
                        'cgv' => false,
                        'setting_value' => $settingRepository->findOneBy(['name' => Constant::NAME_CGV])->getValue(),
                        'setting_price' => $settingRepository->findOneBy(['name' => 'PRIX_UNITAIRE_OR'])->getValue(),
                        '_nationality' => array_key_exists('nationality', $request->get('user')) ? $request->get('user')['nationality'] : 0,
                        '_cin' => array_key_exists('cin', $request->get('user')) ? $request->get('user')['cin'] : '',
                        '_cin0' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 0, 1) : '',
                        '_cin1' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 1, 1) : '',
                        '_cin2' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 2, 1) : '',
                        '_cin3' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 3, 1) : '',
                        '_cin4' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 4, 1) : '',
                        '_cin5' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 5, 1) : '',
                        '_cin6' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 6, 1) : '',
                        '_cin7' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 7, 1) : '',
                        '_cin8' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 8, 1) : '',
                        '_cin9' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 9, 1) : '',
                        '_cin10' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 10, 1) : '',
                        '_cin11' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 10, 1) : '',
                        '_passport' => array_key_exists('passport', $request->get('user')) ? $request->get('user')['passport'] : '',
                        '_passportExp' => array_key_exists('passportExp', $request->get('user')) ? $request->get('user')['passportExp'] : '',
                        '_country' => array_key_exists('country', $request->get('user')) ? $request->get('user')['country'] : '',
                        '_rib' => array_key_exists('rib', $request->get('user')) ? $request->get('user')['rib'] : '',
                        '_rib0' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 0, 1) : '',
                        '_rib1' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 1, 1) : '',
                        '_rib2' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 2, 1) : '',
                        '_rib3' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 3, 1) : '',
                        '_rib4' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 4, 1) : '',
                        '_rib5' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 5, 1) : '',
                        '_rib6' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 6, 1) : '',
                        '_rib7' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 7, 1) : '',
                        '_rib8' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 8, 1) : '',
                        '_rib9' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 9, 1) : '',
                        '_rib10' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 10, 1) : '',
                        '_rib11' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 11, 1) : '',
                        '_rib12' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 12, 1) : '',
                        '_rib13' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 13, 1) : '',
                        '_rib14' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 14, 1) : '',
                        '_rib15' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 15, 1) : '',
                        '_rib16' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 16, 1) : '',
                        '_rib17' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 17, 1) : '',
                        '_rib18' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 18, 1) : '',
                        '_rib19' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 19, 1) : '',
                        '_rib20' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 20, 1) : '',
                        '_rib21' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 21, 1) : '',
                        '_rib22' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 22, 1) : '',
                        '_affiliation' => array_key_exists('affiliation', $request->get('user')) ? $request->get('user')['affiliation'] : '',
                        '_iban' => array_key_exists('iban', $request->get('user')) ? $request->get('user')['iban'] : '',
                        '_swift' => array_key_exists('swift', $request->get('user')) ? $request->get('user')['swift'] : '',
                    ]);
                }
    
                // Calcul de l'âge
                $age = $today->diff($userBirthday)->y;
    
                // Vérification si l'utilisateur a 18 ans ou plus
                if ($age < 18) {
                    $this->addFlash('error', 'Vous n’avez pas l\'âge minimal requis. Merci !');
                    return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
                }
            }
            

            $orQuantity = $request->get('user')['orQuantity'];
            $user->setOrQuantity($orQuantity);

            $paymentMethod = $request->get('user')['typePaiement'];
            if ($paymentMethod === 'virement') {
                $user->setPaymentMethod(Constant::USER_PAYMENT_METHOD_VIREMENT);
            } elseif ($paymentMethod === 'cheque') {
                $user->setPaymentMethod(Constant::USER_PAYMENT_METHOD_CHEQUE);
            }
            
            if ($nationality !== null && (int)$nationality->getValue() === 1 && (int)$request->get('user')['nationality'] === 1) {
                $userPassportExpInput = $request->get('user')['passportExp'];
                if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $userPassportExpInput)) {
                    $userPassportExp = DateTime::createFromFormat('Y-m-d', $userPassportExpInput);
                } else {
                    $userPassportExp = DateTime::createFromFormat('d/m/Y', $userPassportExpInput);
                }

                if (!$userPassportExp || ($userPassportExp->format('d/m/Y') !== $userPassportExpInput && $userPassportExp->format('Y-m-d') !== $userPassportExpInput) || $userPassportExp < $today) {
                    return $this->render('front/signup.html.twig', [
                        '_error' => 'Veuillez entrer un passeport valide. Merci !',
                        'user' => $user,
                        'form' => $form->createView(),
                        'civility' => Constant::USER_CIVILITY,
                        'marital_status' => Constant::USER_MARITAL_STATUS,
                        'nationality' => $nationality,
                        'account' => Constant::USER_ACCOUNT,
                        'countries' => $paysRepository->findAll(),
                        'cgv' => false,
                        'setting_value' => $settingRepository->findOneBy(['name' => Constant::NAME_CGV])->getValue(),
                        'setting_price' => $settingRepository->findOneBy(['name' => 'PRIX_UNITAIRE_OR'])->getValue(),
                        '_nationality' => array_key_exists('nationality', $request->get('user')) ? $request->get('user')['nationality'] : 0,
                        '_cin' => array_key_exists('cin', $request->get('user')) ? $request->get('user')['cin'] : '',
                        '_cin0' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 0, 1) : '',
                        '_cin1' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 1, 1) : '',
                        '_cin2' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 2, 1) : '',
                        '_cin3' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 3, 1) : '',
                        '_cin4' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 4, 1) : '',
                        '_cin5' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 5, 1) : '',
                        '_cin6' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 6, 1) : '',
                        '_cin7' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 7, 1) : '',
                        '_cin8' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 8, 1) : '',
                        '_cin9' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 9, 1) : '',
                        '_cin10' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 10, 1) : '',
                        '_cin11' => array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 10, 1) : '',
                        '_passport' => array_key_exists('passport', $request->get('user')) ? $request->get('user')['passport'] : '',
                        '_passportExp' => array_key_exists('passportExp', $request->get('user')) ? $request->get('user')['passportExp'] : '',
                        '_country' => array_key_exists('country', $request->get('user')) ? $request->get('user')['country'] : '',
                        '_rib' => array_key_exists('rib', $request->get('user')) ? $request->get('user')['rib'] : '',
                        '_rib0' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 0, 1) : '',
                        '_rib1' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 1, 1) : '',
                        '_rib2' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 2, 1) : '',
                        '_rib3' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 3, 1) : '',
                        '_rib4' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 4, 1) : '',
                        '_rib5' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 5, 1) : '',
                        '_rib6' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 6, 1) : '',
                        '_rib7' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 7, 1) : '',
                        '_rib8' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 8, 1) : '',
                        '_rib9' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 9, 1) : '',
                        '_rib10' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 10, 1) : '',
                        '_rib11' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 11, 1) : '',
                        '_rib12' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 12, 1) : '',
                        '_rib13' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 13, 1) : '',
                        '_rib14' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 14, 1) : '',
                        '_rib15' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 15, 1) : '',
                        '_rib16' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 16, 1) : '',
                        '_rib17' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 17, 1) : '',
                        '_rib18' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 18, 1) : '',
                        '_rib19' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 19, 1) : '',
                        '_rib20' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 20, 1) : '',
                        '_rib21' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 21, 1) : '',
                        '_rib22' => array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 22, 1) : '',
                        '_affiliation' => array_key_exists('affiliation', $request->get('user')) ? $request->get('user')['affiliation'] : '',
                        '_iban' => array_key_exists('iban', $request->get('user')) ? $request->get('user')['iban'] : '',
                        '_swift' => array_key_exists('swift', $request->get('user')) ? $request->get('user')['swift'] : '',
                    ]);
                }
            }

            $nextval_user = $nextval_preorder = 0;
            if (count($seq_user = $userRepository->findBy([], ['id' => 'DESC'])) > 0) {
                $nextval_user = $seq_user[0]->getId() + 1;
            } else {
                $nextval_user++;
            }

            if (count($seq_preorder = $orderRepository->findBy([], ['id' => 'DESC'])) > 0) {
                $nextval_preorder = $seq_preorder[0]->getId() + 1;
            } else {
                $nextval_preorder++;
            }

            //CIN déjà utilisé ou passport déjà utilisé

            // if (array_key_exists('cin', $request->get('user')) && $request->get('user')['cin'] && $userRepository->findOneBy(['cin' => $request->get('user')['cin'], 'birthday' => $userBirthdayInput])) {
            //     $this->addFlash('error', 'Cette CIN a déjà été utilisée. Merci !');
            //     return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
            // } elseif (array_key_exists('passport', $request->get('user')) && $request->get('user')['passport'] && $userRepository->findOneBy(['passport' => $request->get('user')['passport'], 'birthday' => $userBirthdayInput])) {
            //     $this->addFlash('error', 'Ce passeport a déjà été utilisé. Merci !');
            //     return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
            // }

            $user->setId($nextval_user);

            $plaintextPassword = (new PasswordGenerator(12, 'lud'))->generateStrongPassword();
            $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
            $user->setPlainPassword($plaintextPassword);
            $user->setPassword($hashedPassword);

            $user->setRole($role = $roleRepository->find(2));
            $user->setRoles((array)$role->getRole());

            if ($nationality !== null && (int)$nationality->getValue() === 1 && (int)$request->get('user')['nationality'] === 1 && array_key_exists('country', $request->get('user')) && $request->get('user')['country']) {
                $user->setCountry($paysRepository->find((int)$request->get('user')['country']));
            }

            if (array_key_exists('cin', $request->get('user')) && $request->get('user')['cin']) {
                $user->setCin($request->get('user')['cin']);
                $user->setPassport(null);
            }

            if (array_key_exists('passport', $request->get('user')) && $request->get('user')['passport']) {
                $user->setCin(null);
                $user->setPassport($request->get('user')['passport']);
            }

            if (array_key_exists('passportExp', $request->get('user')) && $request->get('user')['passportExp']) {
                $user->setPassportExp($request->get('user')['passportExp']);
            }

            if (array_key_exists('rib', $request->get('user')) && $request->get('user')['rib']) {
                $user->setRib($request->get('user')['rib']);
                $user->setAffiliation(null);
                $user->setIban(null);
                $user->setSwift(null);
            }

            if (array_key_exists('affiliation', $request->get('user')) && $request->get('user')['affiliation']) {
                $user->setRib(null);
                $user->setAffiliation($request->get('user')['affiliation']);
                $user->setIban(null);
                $user->setSwift(null);
            }

            if (array_key_exists('iban', $request->get('user')) && $request->get('user')['iban'] && array_key_exists('swift', $request->get('user')) && $request->get('user')['swift']) {
                $user->setRib(null);
                $user->setAffiliation(null);
                $user->setIban($request->get('user')['iban']);
                $user->setSwift($request->get('user')['swift']);
            }

            $reference = 'OR2024P' . sprintf('%05d', $user->getId());

            $path = $this->getParameter('orders_directory') . $reference;
            $filesystem = new Filesystem();
            if (!$filesystem->exists($path)) {
                $filesystem->mkdir($path, 0755);
            }

            if ($nationality !== null && (int)$nationality->getValue() === 1) {
                if ((int)$request->get('user')['nationality'] === 1) {
                    try {
                        /** @var UploadedFile $passportFile */
                        $passportFile = $form->get('passportFile')->getData();
                        if ($passportFile) {
                            $originalFilename = pathinfo($passportFile->getClientOriginalName(), PATHINFO_FILENAME);
                            $safeFilename = $slugger->slug($originalFilename);
                            $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $passportFile->guessExtension() : $safeFilename . '.' . $passportFile->guessExtension();

                            $passportFile->move($path, $newFilename);
                            $user->setFilePassport($path . '/' . $newFilename);
                        }
                    } catch (FileException|Exception) {
//                        $this->addFlash('error', 'Une erreur est survenue lors du téléversement du passeport');
                    }
                } else {
                    try {
                        /** @var UploadedFile $cinFile */
                        $cinFile = $form->get('cinFile')->getData();
                        if ($cinFile) {
                            $originalFilename = pathinfo($cinFile->getClientOriginalName(), PATHINFO_FILENAME);
                            $safeFilename = $slugger->slug($originalFilename);
                            $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $cinFile->guessExtension() : $safeFilename . '.' . $cinFile->guessExtension();

                            $cinFile->move($path, $newFilename);
                            $user->setFileCin($path . '/' . $newFilename);
                        }
                    } catch (FileException|Exception) {
//                        $this->addFlash('error', 'Une erreur est survenue lors du téléversement de la CIN');
                    }
                }
            } else {
                try {
                    /** @var UploadedFile $cinFile */
                    $cinFile = $form->get('cinFile')->getData();
                    if ($cinFile) {
                        $originalFilename = pathinfo($cinFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $cinFile->guessExtension() : $safeFilename . '.' . $cinFile->guessExtension();

                        $cinFile->move($path, $newFilename);
                        $user->setFileCin($path . '/' . $newFilename);
                    }
                } catch (FileException|Exception) {
//                    $this->addFlash('error', 'Une erreur est survenue lors du téléversement de la CIN');
                }
            }

            if ((int)$request->get('user')['account'] === 1) {
                try {
                    /** @var UploadedFile $ribFile */
                    $ribFile = $form->get('ribFile')->getData();
                    if ($ribFile) {
                        $originalFilename = pathinfo($ribFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $ribFile->guessExtension() : $safeFilename . '.' . $ribFile->guessExtension();

                        $ribFile->move($path, $newFilename);
                        $user->setFileRib($path . '/' . $newFilename);
                    }
                } catch (FileException|Exception) {
//                    $this->addFlash('error', 'Une erreur est survenue lors du téléversement du RIB');
                }
            } elseif ((int)$request->get('user')['account'] === 2) {
                try {
                    /** @var UploadedFile $affiliationFile */
                    $affiliationFile = $form->get('affiliationFile')->getData();
                    if ($affiliationFile) {
                        $originalFilename = pathinfo($affiliationFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $affiliationFile->guessExtension() : $safeFilename . '.' . $affiliationFile->guessExtension();

                        $affiliationFile->move($path, $newFilename);
                        $user->setFileAffiliation($path . '/' . $newFilename);
                    }
                } catch (FileException|Exception) {
//                    $this->addFlash('error', 'Une erreur est survenue lors du téléversement de l\'affiliation');
                }
            } elseif ((int)$request->get('user')['account'] === 3) {
                try {
                    /** @var UploadedFile $ibanFile */
                    $ibanFile = $form->get('ibanFile')->getData();
                    if ($ibanFile) {
                        $originalFilename = pathinfo($ibanFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = (strlen($safeFilename) > 20) ? substr($safeFilename, 0, 20) . '.' . $ibanFile->guessExtension() : $safeFilename . '.' . $ibanFile->guessExtension();

                        $ibanFile->move($path, $newFilename);
                        $user->setFileIban($path . '/' . $newFilename);
                    }
                } catch (FileException|Exception) {
//                    $this->addFlash('error', 'Une erreur est survenue lors du téléversement de l\'iban');
                }
            }

            $user->setReference($reference);
            $userRepository->save($user, true);

            $order = new Order();
            $order->setId($nextval_preorder);
            $order->setUser($user);

            $preordersAll = $orderRepository->count(['flagStatus' => [Constant::STATUS_WAIT, Constant::STATUS_VALID, Constant::STATUS_PAYMENT_WAIT, Constant::STATUS_PAID, Constant::STATUS_APPOINTMENT]]);
            $orStockVendu = $orStockRepository->count(['estVendu' => 1]);
            $orStockAll = $orStockRepository->count([]);

            if ($orStockAll - $orStockVendu - $preordersAll > 0) {
                $order->setFlagStatus(Constant::STATUS_VALID);
            } else {
                $order->setFlagStatus(Constant::STATUS_QUEUE);
            }

            $order->setReference($reference);
            $orderRepository->save($order, true);
            $this->addFlash('success', 'Votre demande a été prise en compte. Votre identification et mot de passe seront transmis à l\'adresse email: ' . $user->getEmail());

            //send mail
            try {
                $email = (new Email())
                    ->from('noreply@bfm.mg')
                    ->to($user->getEmail())
                    ->subject('Confirmation d\'inscription')
                    ->html($this->renderView('mail/confirmation-inscription.html.twig', [
                        'civility' => Constant::USER_CIVILITY,
                        'user' => $user,
                    ]));
                $mailer->send($email);
            } catch (TransportExceptionInterface|Exception) {
//                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du mail');
            }

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

                $text = "Merci pour votre inscription ! Votre demande a bien été recue. Votre identifiant et mot de passe vous ont été envoyés sur l'adresse e-mail que vous avez communiquée.";
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

            return $this->redirectToRoute('app_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/signup.html.twig', [
            '_error' => null,
            'user' => $user,
            'form' => $form->createView(),
            'civility' => Constant::USER_CIVILITY,
            'marital_status' => Constant::USER_MARITAL_STATUS,
            'nationality' => $nationality,
            'account' => Constant::USER_ACCOUNT,
            'countries' => $paysRepository->findAll(),
            'cgv' => true,
            'setting_value' => $settingRepository->findOneBy(['name' => Constant::NAME_CGV])->getValue(),
            'setting_price' => $settingRepository->findOneBy(['name' => 'PRIX_UNITAIRE_OR'])->getValue(),
            '_nationality' => $request->get('user') && array_key_exists('nationality', $request->get('user')) ? $request->get('user')['nationality'] : 0,
            '_cin' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? $request->get('user')['cin'] : '',
            '_cin0' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 0, 1) : '',
            '_cin1' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 1, 1) : '',
            '_cin2' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 2, 1) : '',
            '_cin3' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 3, 1) : '',
            '_cin4' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 4, 1) : '',
            '_cin5' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 5, 1) : '',
            '_cin6' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 6, 1) : '',
            '_cin7' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 7, 1) : '',
            '_cin8' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 8, 1) : '',
            '_cin9' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 9, 1) : '',
            '_cin10' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 10, 1) : '',
            '_cin11' => $request->get('user') && array_key_exists('cin', $request->get('user')) ? substr($request->get('user')['cin'], 10, 1) : '',
            '_passport' => $request->get('user') && array_key_exists('passport', $request->get('user')) ? $request->get('user')['passport'] : '',
            '_passportExp' => $request->get('user') && array_key_exists('passportExp', $request->get('user')) ? $request->get('user')['passportExp'] : '',
            '_country' => $request->get('user') && array_key_exists('country', $request->get('user')) ? $request->get('user')['country'] : '',
            '_rib' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? $request->get('user')['rib'] : '',
            '_rib0' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 0, 1) : '',
            '_rib1' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 1, 1) : '',
            '_rib2' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 2, 1) : '',
            '_rib3' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 3, 1) : '',
            '_rib4' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 4, 1) : '',
            '_rib5' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 5, 1) : '',
            '_rib6' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 6, 1) : '',
            '_rib7' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 7, 1) : '',
            '_rib8' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 8, 1) : '',
            '_rib9' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 9, 1) : '',
            '_rib10' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 10, 1) : '',
            '_rib11' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 11, 1) : '',
            '_rib12' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 12, 1) : '',
            '_rib13' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 13, 1) : '',
            '_rib14' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 14, 1) : '',
            '_rib15' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 15, 1) : '',
            '_rib16' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 16, 1) : '',
            '_rib17' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 17, 1) : '',
            '_rib18' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 18, 1) : '',
            '_rib19' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 19, 1) : '',
            '_rib20' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 20, 1) : '',
            '_rib21' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 21, 1) : '',
            '_rib22' => $request->get('user') && array_key_exists('rib', $request->get('user')) ? substr($request->get('user')['rib'], 22, 1) : '',
            '_affiliation' => $request->get('user') && array_key_exists('affiliation', $request->get('user')) ? $request->get('user')['affiliation'] : '',
            '_iban' => $request->get('user') && array_key_exists('iban', $request->get('user')) ? $request->get('user')['iban'] : '',
            '_swift' => $request->get('user') && array_key_exists('swift', $request->get('user')) ? $request->get('user')['swift'] : '',
        ]);
    }

    /**
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $passwordHasher
     * @return Response
     */
    #[Route('/auth', name: 'app_auth')]
    public function auth(Request $request, AuthenticationUtils $authenticationUtils, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Check if the user is already authenticated
        if ($this->getUser()) {
            // $this->addFlash('info', 'Utilisateur déjà authentifié'); 
            return $this->redirectToRoute('dashboard_index');
        }

        // Get the last authentication error, if any
        $error = $authenticationUtils->getLastAuthenticationError();

        // Get the last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Check if the form was submitted
        if ($request->isMethod('POST')) {
            // Retrieve email and password from the request
            $email = $request->request->get('_email');
            $password = $request->request->get('_password');

            // Find the user in the database by email
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Identifiant non trouvé');
                return $this->redirectToRoute('app_auth', [], Response::HTTP_SEE_OTHER);
                // throw new CustomUserMessageAuthenticationException('Email ou mot de passe incorrect.');
            }

            // Check if the password is correct
            if (!$passwordHasher->isPasswordValid($user, $password)) {
                // throw new BadCredentialsException('Email ou mot de passe incorrect.');
                $this->addFlash('error', 'mot de passe invalide');
                return $this->redirectToRoute('app_auth', [], Response::HTTP_SEE_OTHER);
            }
            // Check if the user has the ROLE_USER role
            $roles = $user->getRoles();
            if (!in_array('ROLE_USER', $roles)) {
                $this->addFlash('error', 'Action non autorisée');
                return $this->redirectToRoute('app_auth', [], Response::HTTP_SEE_OTHER);
            }

            // Authentication succeeded, redirect to the dashboard
            return $this->redirectToRoute('dashboard_index');
        }

        // Render the authentication page
        return $this->render('front/auth.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }


    /**
     * @param SettingRepository $settingRepository
     * @return Response
     */
    #[Route('/cgv', name: 'app_cgv', methods: ['GET'])]
    public function cgv(SettingRepository $settingRepository): Response
    {
        return $this->render('front/cgv.html.twig', [
            'setting_value' => $settingRepository->findOneBy(['name' => Constant::NAME_CGV])->getValue(),
            'setting_price' => $settingRepository->findOneBy(['name' => 'PRIX_UNITAIRE_OR'])->getValue(),
        ]);
    }

    /**
     * @param SettingRepository $settingRepository
     * @return Response
     */
    #[Route('/faq', name: 'app_faq', methods: ['GET'])]
    public function faq(SettingRepository $settingRepository): Response
    {
        return $this->render('front/faq.html.twig', [
            'setting_value' => $settingRepository->findOneBy(['name' => Constant::NAME_FAQ])->getValue(),
        ]);
    }

    /**
     * @param User $user
     * @return Response
     */
    #[Route('/{id}/email/confirmation-inscription', name: 'app_email_confirmation_inscription', methods: ['GET'])]
    public function confiramtionInscription(User $user): Response
    {
        return $this->render('mail/confirmation-inscription.html.twig', [
            'user' => $user,
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    #[Route('/{id}/email/mail_code_livraison', name: 'app_email_mail_code_livraison', methods: ['GET'])]
    public function codeLivraison(User $user): Response
    {
        return $this->render('mail/mail_code_livraison.html.twig', [
            'user' => $user,
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    #[Route('/{id}/email/mail_pj', name: 'app_email_mail_pj', methods: ['GET'])]
    public function mailPJ(User $user): Response
    {
        return $this->render('mail/mail_pj.html.twig', [
            'user' => $user,
            'civility' => Constant::USER_CIVILITY,
        ]);
    }

    #[Route('/{id}/email/mail_type_inscription', name: 'app_email_mail_type_inscription', methods: ['GET'])]
    public function typeInscription(User $user): Response
    {
        return $this->render('mail/mail_type_inscription.html.twig', [
            'user' => $user,
            'civility' => Constant::USER_CIVILITY,
        ]);
    }
}
