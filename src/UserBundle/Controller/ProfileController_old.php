<?php
namespace UserBundle\Controller;

use AlgoliaSearch\Json;
use DateTime;
use LN\LnBundle\Entity\Specialization;
use LN\LnBundle\Repository\InstituteRepository;
use LN\LnBundle\Repository\SpecializationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\Profile;
use UserBundle\Entity\User;
use UserBundle\Form\ProfileType;
use Imagick;
/**
 * Profile controller.
 */
class ProfileController extends Controller
{

//, requirements={"id": "[a-zA-Z0_9]+"}

    /**
     *
     * @Route("/study-table", name="view_study_table")
     * @Route("/me", name="my_study_table")
     * @Route("/user/{id}", name="user_profile_show")
     * @Route("/u/{id}", name="user_profile_show1")
     *
     */
    public function studyTableAction($id=null, Request $request)
    {

        $em = $this->getDoctrine()
            ->getManager();
        //if user tries to access study-table or me
        if($id == null ){
            //check if user is logged in
            if(!$this->get('security.authorization_checker')
                ->isGranted('ROLE_USER')){
                //take him to login page
                return $this->redirect($this->generateUrl('login') . '?redirect_url=/study-table');
            }else{
            $user = $this->get('security.token_storage')
                    ->getToken()
                    ->getUser();
                $id= $user->getHashId();
            }
        }

        $profile = $em->getRepository("UserBundle:Profile")
            ->getProfileByUsernameOrHashId(
                $id,
                $this->get('global.hash_generator')
                    ->decode($id)
            );
        if (is_null($profile)) {
            throw $this->createNotFoundException();
        }

        $allowEdit = false;
        if ($this->get('security.authorization_checker')
            ->isGranted('ROLE_USER')
        ) {
            $allowEdit = $this->getUser()
                    ->getId() == $profile->getUser()
                    ->getId();
        }

        if ($this->get('security.authorization_checker')
            ->isGranted('ROLE_ADMIN')
        ) {
            $allowEdit = true;
        }

        //get all subscriptions details
        $subscription_list = $this->getDoctrine()
            ->getRepository('LnBundle:Subscription')
            ->findBy(['subscriber' => $profile->getUser()->getId(), 'active' => 1]);

        $pyq_list = [];

        $temp_subject_list = [];

        //find pyq list for all subscription
        //todo: make a single query for this
        foreach($subscription_list as $item){
            if(in_array($item->getNote()->getSubject()->getId(), $temp_subject_list)){
                continue;
            }else{
                $temp_subject_list[] = $item->getNote()->getSubject()->getId();
            }

            $pyq_list[] = $this->getDoctrine()
                ->getRepository('PYQBundle:PyqSets')
                ->findBy([
                    'subject' => $item->getNote()->getSubject()->getId(),
                    'active' => 1,
                    'deleted' => 0,
                    'approved' => 1
                ], ['yearOfExam' => 'DESC']);
        }

        //get user uploads
        $myUploads = $this->getDoctrine()->getRepository('LnBundle:Note')
            ->findBy(['creator' => $profile->getUser(), 'deleted' => 0]);

            $i=0;
            foreach($myUploads as $item){
                if($allowEdit) {
                    $download_details = $em->createQuery('
                        SELECT count(dc.id) as downloadCount, count(DISTINCT(dc.user)) as uniqueDownloadCount,
                        (
                            SELECT count(s.id)
                            FROM LnBundle:Subscription AS s
                            WHERE s.note = ?1 AND s.active = 1
                        ) as subscriberCount,
                        (
                            SELECT sum(t.pagesRead)
                            FROM LnBundle:Topic AS t
                            WHERE t.note = ?1
                        ) as pagesRead
                        FROM LnBundle:Download as dc
                        WHERE dc.note = ?1
                    ')
                        ->setParameter(1, $item->getId())
                        ->getSingleResult()
                    ;
                    $myUploads[$i]->downloadCount = $download_details['downloadCount'];
                    $myUploads[$i]->uniqueDownloadCount = $download_details['uniqueDownloadCount'];
                    $myUploads[$i]->subscriptionCount = $download_details['subscriberCount'];
                    $myUploads[$i]->pagesRead = $download_details['pagesRead'];
                }else{
                    $download_details = $em->createQuery('
                        SELECT count(dc.id) as downloadCount
                        FROM LnBundle:Download as dc
                        WHERE dc.note = ?1
                    ')
                        ->setParameter(1, $item->getId())
                        ->getSingleResult()
                    ;
                    $myUploads[$i]->downloadCount = $download_details['downloadCount'];
                }
                $i++;
            }

        //todo: change these things to load on jquery request
        
        $specialization = $this->getDoctrine()
            ->getRepository('LnBundle:Specialization')
            ->findAll();

//        getting all uploaded pyq-solution list
        $myPyqSolutions = $em->getRepository('PYQBundle:PyqSolutionSets')
                                ->findBy(['creator' => $profile->getUser(), 'active' => 1, 'approved' => 1, 'deleted' => 0]);

//        $visitingCardDetails = $em->getRepository('AdminBundle:VisitingCardConfirmation')
//                                    ->findOneBy(['user'=>$profile->getUser()->getId()]);
        return $this->render('UserBundle:Profile:studyTable.html.twig',[
            'subscription_list'=>$subscription_list,
            'profile'=>$profile,
            'allSpecialization'=>$specialization,
            'pyq_list' => $pyq_list,
            'allowEdit' => $allowEdit,
            'myUpload_list' => $myUploads,
//            'visitingCardDetails'=> $visitingCardDetails,
            'myPyqSolution_list' => $myPyqSolutions
        ]);
    }
    //@Method("POST")
    /**
     * Displays a form to edit an existing Profile entity.
     * @Security("has_role('ROLE_USER')")
     * @Route("/user/{hashId}/update", name="user_profile_updates")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param         $hashId
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request, $hashId)
    {
        //check for csrf token before proceeding
        if ($request->request->get('csrf') !== $this->get('security.csrf.token_manager')->getToken('update')->getValue()) {
            if($request->isXmlHttpRequest()){
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid/Expired Request. Refresh and try again',
                ]);
            }
        }

        $em = $this->getDoctrine()
            ->getManager();

        $id = $this->get('global.hash_generator')->decode($hashId);

        $profile = $em->getRepository("UserBundle:Profile")
            ->findOneBy(['user' => $id[0]]);

        if (is_null($profile)) {
            //$this->createNotFoundException();
            throw $this->createNotFoundException();
        }

        //dont allow edit of admin profile

        $authChecker = $this->get('security.authorization_checker');
        if ($authChecker->isGranted('ROLE_USER') &&
            $this->getUser()
                ->getId() == $profile->getUser()
                ->getId() ||    //can edit own profiles
            $authChecker->isGranted('ROLE_ADMIN')     //admin can edit profile
        ) {
            //field to be edited
            $type = $request->request->get('type');

//            return new JsonResponse(['type'=>$type]);
            switch ($type){
                case 'name':
                    $profile->getUser()->setFirstName($request->request->get('firstName'));
                    $profile->getUser()->setLastName($request->request->get('lastName'));
                    $em->persist($profile);
                    $em->flush();
                    return new JsonResponse(['status' => 'success', 'message' => 'Saved']);
                    break;
                case 'gender':
                    $profile->setGender($request->request->get('gender'));
                    $em->persist($profile);
                    $em->flush();
                    return new JsonResponse(['status' => 'success']);
                    break;
                case 'phone':
                    $profile->setPhone($request->request->get('phone'));
                    $em->persist($profile);
                    $em->flush();
                    return new JsonResponse(['status' => 'success']);
                    break;
                case 'specialization':
                    $specialization = $em->getRepository('LnBundle:Specialization')
                        ->find($request->request->get('specialization'));
                    $profile->setSpecialization($specialization);
                    $em->persist($profile);
                    $em->flush();
                    return new JsonResponse(['status' => 'success']);
                    break;
                case 'institution':
                    $institution = $em->getRepository('LnBundle:Institute')
                        ->find($request->request->get('institution'));
                    $profile->setPresentInstitution($institution);
                    $em->persist($profile);
                    $em->flush();
                    return new JsonResponse([
                        'status' => 'success'
                    ]);
                break;
                case 'role':
                    switch ($request->request->get('role')){
                        case 0:
                            //student
                            $role = 1;
                            break;
                        case 1:
                            $role = 7;
                            break;
                        default:
                            $role = 1;
                    }
                    $profile->getUser()->setRole($role);
                    $em->persist($profile);
                    $em->flush();
                    
                    return new JsonResponse(['status' => 'success','role'=> $role]);
                    break;
                case 'dob':
                    $dob = $request->request->get('dob');

                    $time = DateTime::createFromFormat('d/m/Y', $dob)->format('Y-m-d h:i:s');

//                    var_dump();
                    //$dob = date('Y-m-d h:i:s',$time);
                    //var_dump($dob);
                    $profile->setDateOfBirth(new \DateTime($time));
                    $em->persist($profile);
                    $em->flush();
                    return new JsonResponse(['status' => 'success', 'message' => 'Changed Succesfully']);
                    break;
                case 'about':
                    $about = $request->request->get('about');
                    $profile->setAbout($about);
                    $em->persist($profile);
                    $em->flush();
                    return new JsonResponse(['status' => 'success', 'message' => 'Saved']);
                    break;
                case 'designation':
                    $designation = $request->request->get('designation');
                    $profile->setDesignation($designation);
                    $em->persist($profile);
                    $em->flush();
                    return new JsonResponse(['status' => 'success', 'message' => 'Saved']);
                    break;
            }

        }
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Invalid Request. Refresh and try again.'
        ]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     * @Route("/user/dp/change-dp", name="change_user_pic")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeProfilePictureAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')
            ->getToken()
            ->getUser();
        $profile = $em->getRepository('UserBundle:Profile')->findOneBy(['user'=>$user->getId()]);

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('change_user_pic'))
            ->add('Image', FileType::class, [
                'mapped' => false,
                'attr'  => ['class' => 'form-control']
            ])
            ->add('submit', SubmitType::class,[
                'attr' => [
                    'class' => 'change-dp-submit btn btn-primary'
                ]
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dp = $form['Image']->getData();
            $check = getimagesize($dp);
//            var_dump($check);
            if(!$check[0]){
                $this->addFlash('error', 'Our system couldn\'t process the file. Please upload a valid JPG, PNG or BMP file');
            } else {
                $picture = $dp->getRealPath();
                $filename = md5(uniqid()) . '.jpg';
                $targetDir = $this->getParameter('root_dir') . $this->getParameter('avatar_dir') . DIRECTORY_SEPARATOR
                    . substr($filename, 0, 1) . DIRECTORY_SEPARATOR
                    . substr($filename, 0, 2) . DIRECTORY_SEPARATOR;

                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0750, true);
                }
                if($this->container->getParameter('kernel.environment') == 'dev'){
                    $dp->move(
                        $targetDir,
                        $filename
                    );
                }else{
                    $this->get('global.image_transformer')
                        ->resize($picture, 400, 400, [
                            'raw_input'   => false,
                            'output_file' => $targetDir.$filename,
                            'return'      => false,
                            'resize_mode' => 'fixed_canvas'
                        ]);
                }

//                $picture->move($targetDir, $filename);
//check if an old image exist? and delete the old one
                if($profile->getAvatar()){
                    $old_filename = $profile->getAvatar();
                    $path = $this->getParameter('root_dir') . $this->getParameter('avatar_dir') . DIRECTORY_SEPARATOR
                        . substr($old_filename, 0, 1) . DIRECTORY_SEPARATOR
                        . substr($old_filename, 0, 2) . DIRECTORY_SEPARATOR
                        . $old_filename;
                    if(file_exists($path)){
                        unlink($path);
                    }
                }

                $profile->setAvatar($filename);
                $this->addFlash('success', 'added');
                $em->persist($profile);
                $em->flush();

                if($request->isXmlHttpRequest()){
                    return new JsonResponse([
                        'status' => 'success',
                        'message' => 'Profile Picture Changed',
                        'html'      => 'Profile Picture Changed'
                    ]);
                }else{
                    return $this->redirectToRoute('my_study_table');
                }
            }
        }
        if($request->isXmlHttpRequest()){
            $html = $this->renderView('@User/Me/change_user_pic.html.twig',
                [
                    'form' => $form->createView(),
                ]);
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid Details',
                'html' => $html,
            ]);
        }else{

            return $this->render(
                'UserBundle:Me:change_user_pic.html.twig',
                [
                    'form' => $form->createView(),
                    'user' => $user,
                    'profile'  => $profile
                ]
            );
        }
    }

    /**
     * @Security("has_role('ROLE_USER')")
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("account/complete-your-profile", name="complete_your_profile")
     */
    public function profileCompleteAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')
            ->getToken()
            ->getUser();
        $userProfile = $em->getRepository('UserBundle:Profile')
                            ->getProfileByUserId($user->getId());

        $form = $this->createFormBuilder($userProfile)
                    ->add('gender', ChoiceType::class, [
                        'choices'           => [
                            'user.gender.unspecified' => Profile::GENDER_UNSPECIFIED,
                            'user.gender.male'        => Profile::GENDER_MALE,
                            'user.gender.female'      => Profile::GENDER_FEMALE,
                            'user.gender.others'      => Profile::GENDER_OTHERS,
                        ],
                        'choices_as_values' => true,
                        'invalid_message' => 'User a valid Gender from options',
                        'attr' => ['class' => 'form-control'],
                    ])
                    ->add('specialization', EntityType::class, [
                        'class' => 'LN\LnBundle\Entity\Specialization',
                        'label' => 'Specialization/Department',
                        'empty_value' => 'Choose your Department/Specialization/Branch',
                        'query_builder' => function(SpecializationRepository $er)
                        {
                            return $er->createQueryBuilder('u')
                                ->where('u.deleted = 0')
                                ->orderBy('u.id', 'ASC');
                        },
  //                      'preferred_choices' => array($userProfile->getSpecialization()),
                        'attr' => ['class' => 'form-control'],
                    ])
                    ->add('presentInstitution', EntityType::class,  [
                        'class' => 'LN\LnBundle\Entity\Institute',
                        'label' => 'Current Institution of Study',
                        'empty_value' => 'Choose your institute',
                        'query_builder' => function(InstituteRepository $er)
                        {
                            return $er->createQueryBuilder('u')
                                ->where('u.deleted = 0')
                                ->orderBy('u.name', 'ASC');
                        },
//                        'preferred_choices' => array($userProfile->getPresentInstitution()),
                        'attr' => ['class' => 'form-control institution-option select2-drop']
                    ])

                    ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {

//            $specialization = $form['specialization']->getData();
//            $institute = $form['presentInstitution']->getData();
//            $gender = $form['gender']->getData();
//            $profile = $em->getRepository('UserBundle:Profile')
//                ->findOneBy(['user'=>$user->getId()]);
//            $profile->setPresentInstitution($institute);
//            $profile->setSpecialization($specialization);
//            $profile->setGender(intval($gender));

            $em->persist($userProfile);
            $em->flush();

            if($request->isXmlHttpRequest()){
                return new JsonResponse([
                    'status' => 'success'
                ]);
            }
        }
        return $this->render('@User/Profile/profile_complete.html.twig',
            [
                'form'=>$form->createView(),
                'userProfile'=>$userProfile
            ]);
    }

    /**
     * //for visiting card printing
     * @param Request $request
     * @param $userHash
     * @return Response
     * @Route("/faculty-profile-completion/{userHash}", name="faculty_profile_completion")
     */
    public function facultyProfileCompletionAction(Request $request, $userHash){

        $firstName = $request->request->get('firstName');
        $lastName = $request->request->get('lastName');
        $username = $request->request->get('username');
        $designation = $request->request->get('designation');
        $phone = $request->request->get('phone');
        $presentInstitution = $request->request->get('presentInstitution');
        $city = $request->request->get('city');
        $specialization = $request->request->get('specialization');


        $em = $this->getDoctrine()->getManager();
        $userId = $this->get('global.hash_generator')->decode($userHash);

        $allSpecialization = $em->createQuery('
            SELECT s.id, s.name AS specialization
            FROM LnBundle:Specialization AS s
            WHERE s.active = 1 AND s.approved = 1 AND s.deleted = 0
        ')
                                ->getResult();
        
        $allInstitute = $em->createQuery('
            SELECT con.name, i.city, con.id
            FROM LnBundle:Institute AS i
            LEFT JOIN GlobalBundle:Content AS con WITH con.id = i.id
            WHERE con.active = 1 AND con.approved = 1 AND con.deleted = 0
        ')
                            ->getResult();

        $allowEdit = false;
        if ($this->get('security.authorization_checker')
            ->isGranted('ROLE_USER')
        ) {
             if($this->getUser()->getId() == $userId){
                 $allowEdit = true;
             }
        }

        if ($this->get('security.authorization_checker')
            ->isGranted('ROLE_ADMIN')
        ) {
            $allowEdit = true;
        }

        return $this->render('@User/Profile/faculty_profile_completion.html.twig',
                                [   'firstName'=>$firstName,
                                    'lastName' => $lastName,
                                    'username'=> $username,
                                    'designation'=> $designation,
                                    'phone'=> $phone,
                                    'presentInstitution'=> $presentInstitution,
                                    'city'=> $city,
                                    'specialization'=> $specialization,
                                    'allowEdit' => $allowEdit,
                                    'allSpecialization' => $allSpecialization,
                                    'allInstitute'  => $allInstitute
                                ]
                            );
    }

}
