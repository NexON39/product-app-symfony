<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Reviews;
use App\Entity\User;
use App\Form\ProductAddType;
use App\Form\ProductEditType;
use App\Form\ProductOpinionType;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\PriceGenerator;
use App\Service\ProductUserValidation;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class ProductController extends AbstractController
{
    public function formNotNullValidate($productName)
    {
        if ($productName == NULL) {
            return true;
        }
    }

    public function getCurrentUser()
    {
        try {
            $currentUser = $this->getUser()->getUserIdentifier();
            if (isset($currentUser)) {
                return $currentUser;
            } else {
                throw new Exception('User ' . $currentUser . ' not found');
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    #[Route('/product', name: 'app_product')]
    public function index(Request $request, ManagerRegistry $doctrine, ProductRepository $productRepository, PriceGenerator $priceGenerator, TranslatorInterface $translator, UserRepository $userRepository)
    {
        //get user email
        $userName = $this->getCurrentUser();

        // product init
        $product = new Product;

        // find user
        $user = $userRepository->findOneBy([
            'email' => $userName
        ]);

        // build form and handle request
        $form = $this->createForm(ProductAddType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // check if null
            if ($this->formNotNullValidate($form->get('productName')->getData())) {

                //result message
                $this->addFlash(
                    'succes',
                    $translator->trans('Input can not be null')
                );

                //redirect to route
                return $this->redirect($this->generateUrl('app_product'));
            }

            // entity init
            $entityManager = $doctrine->getManager();

            // add product
            $product->setOwnerName($userName);
            $product->setProductName($form->get('productName')->getData());
            $product->setPrice($priceGenerator->getProductPrice($form->get('productName')->getData()));
            $product->setUserproduct($user);

            // execute
            $entityManager->persist($product);
            $entityManager->flush();

            //result message
            $this->addFlash(
                'succes',
                $translator->trans('Your product was added')
            );

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // get all product list
        $products = $productRepository->findAll();

        // render form and products
        return $this->render('product/product.html.twig', [
            'form' => $form->createView(),
            'products' => $products
        ]);
    }

    #[Route('/product/edit/{id}', name: 'app_product_edit')]
    public function edit(Request $request, ManagerRegistry $doctrine, ProductRepository $productRepository, LoggerInterface $logger, ProductUserValidation $productUserValidation, TranslatorInterface $translator)
    {
        // get id from route param
        $id = $request->get('id');

        //get user email
        $userName = $this->getCurrentUser();

        // product validation
        $product = $productRepository->find($id);
        if (!$product) {
            //result message
            $this->addFlash(
                'succes',
                $translator->trans('Product not found')
            );

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        if ($productUserValidation->productEditValidate($product->getOwnerName(), $userName) == false) {
            //result message
            $this->addFlash(
                'succes',
                $translator->trans('You cannot edit a product that is not yours')
            );

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // build form and handle request
        $form = $this->createForm(ProductEditType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // check if null
            if ($this->formNotNullValidate($form->get('productName')->getData()) == true || $this->formNotNullValidate($form->get('price')->getData()) == true) {

                //result message
                $this->addFlash(
                    'succes',
                    $translator->trans('Input can not be null')
                );

                //redirect to route
                return $this->redirect($this->generateUrl('app_product'));
            }

            // check if not negative number
            if ($form->get('price')->getData() < 0) {
                //result message
                $this->addFlash(
                    'succes',
                    $translator->trans('Input can not be negative number')
                );

                //redirect to route
                return $this->redirect($this->generateUrl('app_product'));
            }

            // entity init
            $entityManager = $doctrine->getManager();

            // edit product
            $product->setProductName($form->get('productName')->getData());
            $product->setPrice($form->get('price')->getData());

            // execute
            $entityManager->persist($product);
            $entityManager->flush();

            //result message
            $this->addFlash(
                'succes',
                $translator->trans('Your product was edited')
            );

            //log
            $logger->info("$userName edited product id $id");

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // form render
        return $this->render('product/product_edit.html.twig', [
            'form' => $form->createView(),
            'product_id' => $id
        ]);
    }

    #[Route('/product/opinion/{id}', name: 'app_product_opinion')]
    public function opinion(Request $request, ManagerRegistry $doctrine, ProductRepository $productRepository, LoggerInterface $logger, ValidatorInterface $validator, ProductUserValidation $productUserValidation, TranslatorInterface $translator)
    {
        // get id from route param
        $id = $request->get('id');

        //get user email
        $userName = $this->getCurrentUser();

        // product validation
        $product = $productRepository->find($id);
        if (!$product) {
            //result message
            $this->addFlash(
                'succes',
                $translator->trans('Product not found')
            );

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // product user validate
        if ($productUserValidation->productOpinionValidate($product->getOwnerName(), $userName) == false) {
            //result message
            $this->addFlash(
                'succes',
                $translator->trans('You cannot add a review to your product')
            );

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // build form and handle request
        $form = $this->createForm(ProductOpinionType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // entity init
            $entityManager = $doctrine->getManager();

            // add review to product
            $review = new Reviews;
            $review->setReview((string)$form->get('opinions')->getData());
            $review->setContent($product);

            // execute
            $entityManager->persist($review);
            $entityManager->flush();

            //log
            $logger->info("$userName added reviews to product id $id");

            //result message
            $this->addFlash(
                'succes',
                $translator->trans('Successfully added reviews')
            );

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // form render
        return $this->render('product/product_opinion.html.twig', [
            'form' => $form->createView(),
            'product_id' => $id
        ]);
    }

    #[Route('/product/opinion/view/{id}', name: 'app_product_opinion_view')]
    public function opinionShow(Request $request, ProductRepository $productRepository, TranslatorInterface $translator)
    {
        // get id from route param
        $id = $request->get('id');

        // product validation
        $product = $productRepository->find($id);
        if (!$product) {
            //result message
            $this->addFlash(
                'succes',
                $translator->trans('Product not found')
            );

            //redirect to route
            return $this->redirect($this->generateUrl('app_product'));
        }

        // get reviews from product
        $reviews = $product->getReviews();

        // form render
        return $this->render('product/product_opinion_view.html.twig', [
            'product_id' => $id,
            'reviews' => $reviews
        ]);
    }
}
